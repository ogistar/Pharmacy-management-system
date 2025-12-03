<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\SaleItem;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Patient;
use App\Models\Receivable;
use App\Models\Prescription;
use App\Models\Compound;
use App\Models\CashSession;
use App\Models\StockMovement;

class PosController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-sales')->only(['index','searchProducts','fromPrescription','compounds']);
        $this->middleware('permission:create-sale')->only(['checkout']);
    }

    public function index()
    {
        $patients = Patient::orderBy('name')->select('id','name','phone','dob')->get();
        return view('admin.pos', compact('patients'));
    }

    /**
     * AJAX search for products / batches available for sale.
     * Query param: q
     */
    public function searchProducts(Request $request)
    {
        $q = $request->query('q');

        $results = Purchase::with('purchaseProduct')
            ->where('product', 'like', "%{$q}%")
            ->where('quantity', '>', 0)
            ->orderByRaw("CASE WHEN expiry_date IS NULL OR expiry_date = '' THEN '9999-12-31' ELSE expiry_date END ASC")
            ->orderBy('created_at', 'asc')
            ->limit(30)
            ->get()
            ->map(function ($p) {
                $prod = $p->purchaseProduct;
                return [
                    'purchase_id' => $p->id,
                    'product_id' => $prod->id ?? null,
                    'product' => $p->product,
                    'quantity' => $p->quantity,
                    'cost_price' => $p->cost_price,
                    'expiry_date' => $p->expiry_date,
                    'batch_no' => $p->batch_no,
                    'price_retail' => $prod->price_retail ?? $prod->price ?? null,
                    'price_wholesale' => $prod->price_wholesale ?? null,
                    'price_insurance' => $prod->price_insurance ?? null,
                    'promo_name' => $prod->promo_name ?? null,
                    'promo_percent' => $prod->promo_percent ?? 0,
                    'bundle_qty' => $prod->bundle_qty ?? null,
                    'bundle_price' => $prod->bundle_price ?? null,
                ];
            });

        return response()->json($results);
    }

    public function compounds()
    {
        $compounds = Compound::with('items.product.purchase')->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'service_fee' => $c->service_fee,
                'markup_percent' => $c->markup_percent,
                'price_override' => $c->price_override,
                'items' => $c->items->map(function ($it) {
                    return [
                        'product_id' => $it->product_id,
                        'product_name' => $it->product->purchase->product ?? 'Produk #'.$it->product_id,
                        'quantity' => $it->quantity,
                    ];
                })->values(),
            ];
        });

        return response()->json($compounds);
    }

    /**
     * Prefill POS cart from a prescription. It will map each item to the earliest available batch.
     */
    public function fromPrescription(Prescription $prescription)
    {
        $prescription->load(['patient', 'items.product.purchase']);
        $items = [];
        foreach ($prescription->items as $item) {
            if ($item->compound_id) {
                $items[] = [
                    'type' => 'compound_template',
                    'compound_id' => $item->compound_id,
                    'product' => $item->product_name,
                    'quantity' => $item->quantity,
                ];
                continue;
            }
            // Find batch by product_id first, then by product_name
            $batchQuery = Purchase::where('quantity', '>', 0)
                ->orderByRaw("CASE WHEN expiry_date IS NULL OR expiry_date = '' THEN '9999-12-31' ELSE expiry_date END ASC")
                ->orderBy('created_at', 'asc');

            if ($item->product_id) {
                $batchQuery->whereHas('purchaseProduct', function ($q) use ($item) {
                    $q->where('id', $item->product_id);
                });
            } else {
                $batchQuery->where('product', $item->product_name);
            }

            $batch = $batchQuery->first();
            if (!$batch) {
                // Skip if no stock found; front-end can warn user.
                $items[] = [
                    'product' => $item->product_name,
                    'missing' => true,
                    'message' => 'Stok tidak tersedia',
                ];
                continue;
            }

            $prod = $batch->purchaseProduct;

            $items[] = [
                'type' => 'batch',
                'product' => $batch->product,
                'purchase_id' => $batch->id,
                'quantity' => $item->quantity,
                'price_retail' => $prod->price_retail ?? $prod->price ?? $batch->cost_price,
                'price_wholesale' => $prod->price_wholesale ?? null,
                'price_insurance' => $prod->price_insurance ?? null,
                'promo_percent' => $prod->promo_percent ?? 0,
                'bundle_qty' => $prod->bundle_qty ?? null,
                'bundle_price' => $prod->bundle_price ?? null,
                'batch_no' => $batch->batch_no,
                'expiry_date' => $batch->expiry_date,
            ];
        }

        $patient = $prescription->patient ? [
            'id' => $prescription->patient->id,
            'name' => $prescription->patient->name,
            'phone' => $prescription->patient->phone,
            'dob' => $prescription->patient->dob,
        ] : null;

        return response()->json(['items' => $items, 'patient' => $patient]);
    }

    /**
     * Checkout endpoint implementing FIFO stock consumption with row locks.
     * Request payload should be:
     * {
     *   items: [ { product: "Product name", quantity: 2 }, ... ],
     *   payment_method: 'cash',
     *   paid_amount: 100
     * }
     */
    public function checkout(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product' => 'required|string',
            'items.*.type' => 'nullable|string|in:batch,compound,compound_template',
            'items.*.purchase_id' => 'nullable|exists:purchases,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'nullable|string',
            'paid_amount' => 'nullable|numeric|min:0',
            'payments' => 'nullable|array|min:1',
            'payments.*.method' => 'required_with:payments|string',
            'payments.*.amount' => 'required_with:payments|numeric|min:0',
            'patient_id' => 'nullable|exists:patients,id',
            'patient_name' => 'nullable|string|max:200',
            // patient_phone is required_with patient_name when creating new patient; accept starting with 62 or 0
            'patient_phone' => ['nullable','regex:/^(62|0)\\d{8,12}$/'],
            'patient_dob' => 'nullable|date',
            'due_date' => 'nullable|date',
            'price_mode' => 'nullable|in:retail,wholesale,insurance',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.components' => 'nullable|array|min:1',
            'items.*.components.*.purchase_id' => 'required_with:items.*.components|exists:purchases,id',
            'items.*.components.*.quantity' => 'required_with:items.*.components|integer|min:1',
            'items.*.compound_id' => 'nullable|exists:compounds,id',
        ]);

        $userId = $request->user()->id ?? null;
        $paidTotal = 0;
        if (!empty($data['payments'])) {
            foreach ($data['payments'] as $p) {
                $paidTotal += $p['amount'];
            }
            $data['payment_method'] = 'split';
        } else {
            $paidTotal = $data['paid_amount'] ?? 0;
        }

        $priceMode = $data['price_mode'] ?? 'retail';

        DB::beginTransaction();
        try {
            $openSession = CashSession::where('user_id', $userId)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();
            if (!$openSession) {
                throw new \Exception('Buka sesi kasir terlebih dahulu sebelum checkout.');
            }

            // Create patient on the fly if only name is provided
            if (empty($data['patient_id']) && !empty($data['patient_name'])) {
                $name = trim($data['patient_name']);
                // Try to reuse existing patient by exact name (case-insensitive)
                $existing = Patient::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
                if ($existing) {
                    $data['patient_id'] = $existing->id;
                    if (empty($existing->dob) && !empty($data['patient_dob'])) {
                        $existing->update(['dob' => $data['patient_dob']]);
                    }
                } else {
                    $patientPayload = ['name' => $name];
                    // Require phone when creating new patient from POS
                    if (!empty($data['patient_phone'])) { $patientPayload['phone'] = $data['patient_phone']; }
                    else {
                        throw new \Exception('Nomor telepon pasien wajib jika menambah pasien baru');
                    }
                    if (!empty($data['patient_email'])) { $patientPayload['email'] = $data['patient_email']; }
                    if (!empty($data['patient_dob'])) { $patientPayload['dob'] = $data['patient_dob']; }
                    $patient = Patient::create($patientPayload);
                    $data['patient_id'] = $patient->id;
                }
            }

            $total = 0;

            // Create invoice placeholder
            $invoice = Invoice::create([
                'invoice_no' => 'INV-' . strtoupper(Str::random(8)),
                'user_id' => $userId,
                'cash_session_id' => $openSession->id,
                'patient_id' => $data['patient_id'] ?? null,
                'total_amount' => 0,
                'paid_amount' => $paidTotal,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'status' => 'paid'
            ]);

            foreach ($data['items'] as $it) {
                $itemType = $it['type'] ?? 'batch';
                $productName = $it['product'];
                $need = (int)$it['quantity'];

                if ($itemType === 'compound') {
                    $unitPrice = $it['unit_price'] ?? 0;
                    if ($unitPrice <= 0) {
                        throw new \Exception('Harga racikan harus diisi');
                    }
                    $lineTotal = $unitPrice * $need;

                    // Deduct components stock if provided
                    foreach ($it['components'] ?? [] as $comp) {
                        $componentQty = (int)$comp['quantity'] * $need;
                        $component = Purchase::where('id', $comp['purchase_id'])->lockForUpdate()->first();
                        if (!$component || $component->quantity < $componentQty) {
                            throw new \Exception("Stok tidak cukup untuk komponen racikan ID {$comp['purchase_id']}");
                        }
                        $component->quantity = $component->quantity - $componentQty;
                        $component->save();
                    }

                    SaleItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => null,
                        'purchase_id' => null,
                        'quantity' => $need,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                    ]);

                    $total += $lineTotal;
                    continue;
                }

                if ($itemType === 'compound_template') {
                    $compound = Compound::with('items.product.purchase')->findOrFail($it['compound_id']);
                    $serviceFee = $compound->service_fee ?? 0;
                    $markup = $compound->markup_percent ?? 0;
                    $priceOverride = $compound->price_override;
                    $subtotal = 0;

                    foreach ($compound->items as $compItem) {
                        $componentQty = (int)$compItem->quantity * $need;
                        $productName = $compItem->product->purchase->product ?? null;

                        if (!$productName) {
                            throw new \Exception("Produk komponen racikan tidak valid");
                        }

                        $batches = Purchase::where('product', $productName)
                            ->where('quantity', '>', 0)
                            ->orderByRaw("CASE WHEN expiry_date IS NULL OR expiry_date = '' THEN '9999-12-31' ELSE expiry_date END ASC")
                            ->orderBy('created_at', 'asc')
                            ->lockForUpdate()
                            ->get();

                        $remaining = $componentQty;
                        foreach ($batches as $batch) {
                            if ($remaining <= 0) break;
                            $take = min($remaining, $batch->quantity);
                            $remaining -= $take;
                            $batch->quantity = $batch->quantity - $take;
                            $batch->save();
                            $subtotal += ($batch->cost_price ?? 0) * $take;
                        }

                        if ($remaining > 0) {
                            throw new \Exception("Stok tidak cukup untuk komponen racikan {$productName}");
                        }
                    }

                    $lineTotal = $priceOverride ?? (($subtotal + $serviceFee) * (1 + ($markup/100)));
                    $unitPrice = $lineTotal / $need;

                    SaleItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => null,
                        'purchase_id' => null,
                        'quantity' => $need,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                    ]);

                    $total += $lineTotal;
                    continue;
                }

                $purchaseId = $it['purchase_id'];
                if (!$purchaseId) {
                    throw new \Exception("purchase_id wajib untuk item non-racikan");
                }

                // Lock only the requested batch
                $batch = Purchase::where('id', $purchaseId)
                    ->where('product', $productName)
                    ->where('quantity', '>', 0)
                    ->lockForUpdate()
                    ->first();

                if (!$batch) {
                    throw new \Exception("Batch tidak ditemukan atau stok habis untuk produk: {$productName}");
                }

                if ($batch->quantity < $need) {
                    throw new \Exception("Stok tidak cukup untuk batch {$batch->batch_no} ({$productName})");
                }

                // Determine price based on tier + promo/bundle
                $product = Product::where('purchase_id', $batch->id)->first();
                $basePrice = $batch->cost_price ?? 0;
                if ($product) {
                    if ($priceMode === 'wholesale' && $product->price_wholesale) {
                        $basePrice = $product->price_wholesale;
                    } elseif ($priceMode === 'insurance' && $product->price_insurance) {
                        $basePrice = $product->price_insurance;
                    } elseif ($product->price_retail) {
                        $basePrice = $product->price_retail;
                    } else {
                        $basePrice = $product->price ?? $basePrice;
                    }
                }

                $promoPercent = $product->promo_percent ?? 0;
                $bundleQty = $product->bundle_qty ?? null;
                $bundlePrice = $product->bundle_price ?? null;

                $discountedUnit = $basePrice * (1 - ($promoPercent/100));
                $lineTotal = $discountedUnit * $need;
                if ($bundleQty && $bundleQty > 1 && $bundlePrice && $bundlePrice > 0) {
                    $bundleCount = intdiv($need, $bundleQty);
                    $remainder = $need % $bundleQty;
                    $lineTotal = ($bundleCount * $bundlePrice) + ($remainder * $discountedUnit);
                }

                // Create sale item
                SaleItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id ?? null,
                    'purchase_id' => $batch->id,
                    'quantity' => $need,
                    'unit_price' => $discountedUnit,
                    'total_price' => $lineTotal,
                ]);

                // Decrement batch quantity (stored as integer)
                $batch->quantity = $batch->quantity - $need;
                $batch->save();

                StockMovement::create([
                    'purchase_id' => $batch->id,
                    'user_id' => $userId,
                    'type' => 'out',
                    'quantity' => $need,
                    'reference_type' => 'invoice',
                    'reference_id' => $invoice->id,
                    'note' => "POS checkout {$productName}",
                ]);

                $total += $lineTotal;
            }

            // update invoice total
            $invoice->total_amount = $total;
            $invoice->status = $paidTotal >= $total ? 'paid' : 'partial';
            $invoice->save();

            if ($invoice->status !== 'paid') {
                Receivable::create([
                    'invoice_id' => $invoice->id,
                    'patient_id' => $data['patient_id'] ?? null,
                    'total_due' => $total,
                    'paid_amount' => $data['paid_amount'],
                    'due_date' => $data['due_date'] ?? now()->addDays(30)->toDateString(),
                    'status' => 'open',
                ]);
            }

            DB::commit();

            return response()->json(['success' => true, 'invoice_id' => $invoice->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
