<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\PrescriptionItemComponent;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Compound;
use App\Models\ControlledDrugLog;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrescriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-prescription')->only(['index']);
        $this->middleware('permission:create-prescription')->only(['store']);
        $this->middleware('permission:edit-prescription')->only(['update']);
        $this->middleware('permission:destroy-prescription')->only(['destroy']);
        $this->middleware('permission:approve-prescription')->only(['approve']);
        $this->middleware('permission:dispense-prescription')->only(['dispense']);
    }

    public function index(Request $request)
    {
        $title = 'prescriptions';
        $prescriptions = Prescription::with(['patient','items'])
            ->latest()
            ->paginate(20);
        $patients = Patient::orderBy('name')->get();
        $products = Product::with('purchase')->get();
        $compounds = Compound::with('items')->get();
        return view('admin.prescriptions.index', compact('title', 'prescriptions', 'patients','products','compounds'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'patient_name' => 'nullable|string|max:200',
            'patient_phone' => 'nullable|string|max:100',
            'patient_dob' => 'nullable|date',
            'doctor_name' => 'nullable|string|max:200',
            'diagnosis' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'prescribed_at' => 'nullable|date',
            'items' => 'nullable|array',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.compound_id' => 'nullable|exists:compounds,id',
            'items.*.product_name' => 'nullable|string|max:200',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.dosage' => 'nullable|string|max:200',
            'items.*.is_controlled' => 'nullable|boolean',
            'items.*.is_compound' => 'nullable|boolean',
            'items.*.compound_note' => 'nullable|string',
            'items.*.label_note' => 'nullable|string',
            'items.*.components' => 'nullable|array',
            'items.*.components.*.product_id' => 'nullable|exists:products,id',
            'items.*.components.*.product_name' => 'nullable|string|max:200',
            'items.*.components.*.quantity' => 'required_with:items.*.components|integer|min:1',
            'items.*.is_full_pack' => 'nullable|boolean',
            'items.*.components.*.is_full_pack' => 'nullable|boolean',
        ]);

        DB::transaction(function () use (&$data, $request) {
            if (empty($data['patient_id'])) {
                $data['patient_id'] = $this->resolvePatientId(
                    $request->input('patient_name'),
                    $request->input('patient_phone'),
                    $request->input('patient_dob')
                );
            }

            $prescription = Prescription::create([
                'patient_id' => $data['patient_id'] ?? null,
                'doctor_name' => $data['doctor_name'] ?? null,
                'diagnosis' => $data['diagnosis'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'prescribed_at' => $data['prescribed_at'] ?? now(),
            ]);

            foreach ($data['items'] ?? [] as $item) {
                $name = $item['product_name'] ?? null;
                if (!$name && !empty($item['compound_id'])) {
                    $name = Compound::find($item['compound_id'])->name ?? null;
                }
                if (!$name) {
                    throw new \Exception('Nama obat/racikan wajib diisi jika tidak memilih template');
                }
                $prescriptionItem = PrescriptionItem::create([
                    'prescription_id' => $prescription->id,
                    'product_id' => $item['product_id'] ?? null,
                    'compound_id' => $item['compound_id'] ?? null,
                    'product_name' => $name,
                    'quantity' => $item['quantity'],
                    'dosage' => $item['dosage'] ?? null,
                    'is_controlled' => !empty($item['is_controlled']),
                    'is_compound' => !empty($item['is_compound']),
                    'compound_note' => $item['compound_note'] ?? null,
                    'label_note' => $item['label_note'] ?? null,
                    'is_full_pack' => !empty($item['is_full_pack']),
                ]);

                // store components if present
                if (!empty($item['components']) && is_array($item['components'])) {
                    foreach ($item['components'] as $comp) {
                        PrescriptionItemComponent::create([
                            'prescription_item_id' => $prescriptionItem->id,
                            'product_id' => $comp['product_id'] ?? null,
                            'product_name' => $comp['product_name'] ?? null,
                            'quantity' => $comp['quantity'] ?? ($comp['qty'] ?? 1),
                            'is_full_pack' => !empty($comp['is_full_pack']),
                        ]);
                    }
                }
            }
        });

        return back()->with(notify('Prescription saved'));
    }

    public function update(Request $request, Prescription $prescription)
    {
        $data = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'doctor_name' => 'nullable|string|max:200',
            'diagnosis' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'prescribed_at' => 'nullable|date',
        ]);

        $prescription->update($data);
        return back()->with(notify('Prescription updated'));
    }

    public function destroy(Prescription $prescription)
    {
        $prescription->delete();
        return response()->json(['success' => true]);
    }

    public function approve(Prescription $prescription, Request $request)
    {
        $prescription->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id ?? null,
            'approved_at' => now(),
        ]);

        $this->logControlled($prescription, 'approved', $request->user()->id ?? null);

        return back()->with(notify('Resep disetujui'));
    }

    public function dispense(Prescription $prescription, Request $request)
    {
        DB::transaction(function () use ($prescription, $request) {
            $prescription->load(['items.components']);

            foreach ($prescription->items as $item) {
                // Non-racikan: kurangi stok langsung
                if (empty($item->is_compound)) {
                    $this->consumeStock($item->product_name, $item->product_id, $item->quantity, (bool) $item->is_full_pack);
                    continue;
                }

                // Racikan: gunakan komponen di resep; jika kosong tapi ada template, pakai template
                $components = $item->components;
                if ($components->isEmpty() && $item->compound_id) {
                    $compound = Compound::with('items.product.purchase')->find($item->compound_id);
                    if ($compound) {
                        $components = $compound->items->map(function ($c) {
                            return (object) [
                                'product_id' => $c->product_id,
                                'product_name' => $c->product->purchase->product ?? null,
                                'quantity' => $c->quantity,
                            ];
                        });
                    }
                }

                foreach ($components as $comp) {
                    $name = $comp->product_name ?? null;
                    $id = $comp->product_id ?? null;
                    $qty = (int) ($comp->quantity ?? 1) * (int) $item->quantity;
                    $this->consumeStock($name, $id, $qty, (bool) ($comp->is_full_pack ?? false));
                }
            }

            $prescription->update([
                'status' => 'dispensed',
                'dispensed_by' => $request->user()->id ?? null,
                'dispensed_at' => now(),
            ]);

            $this->logControlled($prescription, 'dispensed', $request->user()->id ?? null);
        });

        return back()->with(notify('Resep telah didispense & stok diperbarui'));
    }

    private function logControlled(Prescription $prescription, string $action, ?int $userId)
    {
        $items = $prescription->items()->where('is_controlled', true)->get();
        foreach ($items as $item) {
            ControlledDrugLog::create([
                'prescription_item_id' => $item->id,
                'user_id' => $userId,
                'action' => $action,
                'note' => $prescription->diagnosis,
            ]);
        }
    }

    /**
     * Kurangi stok menggunakan FIFO berdasarkan product_id atau nama produk.
     */
    private function consumeStock(?string $productName, ?int $productId, int $qty, bool $fullPack = false): void
    {
        if ($qty <= 0) {
            return;
        }

        $query = Purchase::where('quantity', '>', 0)
            ->orderByRaw("CASE WHEN expiry_date IS NULL OR expiry_date = '' THEN '9999-12-31' ELSE expiry_date END ASC")
            ->orderBy('created_at', 'asc');

        if ($productId) {
            $query->whereHas('purchaseProduct', function ($q) use ($productId) {
                $q->where('id', $productId);
            });
        } elseif ($productName) {
            $query->where('product', $productName);
        } else {
            throw new \Exception('Nama produk tidak tersedia untuk pengurangan stok');
        }

        $batches = $query->lockForUpdate()->get();
        if ($batches->isEmpty()) {
            $label = $productName ?? ('produk #' . $productId);
            throw new \Exception("Stok tidak cukup untuk {$label}");
        }

        $factor = 1;
        if ($fullPack) {
            $first = $batches->first();
            $factor = $first->conversion_factor ?? $first->unit_size ?? 1;
            if ($factor <= 0) {
                $factor = 1;
            }
        }

        $remainingUnits = $qty * $factor;

        foreach ($batches as $batch) {
            if ($remainingUnits <= 0) {
                break;
            }
            $take = min($remainingUnits, $batch->quantity);
            $remainingUnits -= $take;
            $batch->quantity = $batch->quantity - $take;
            $batch->save();

            StockMovement::create([
                'purchase_id' => $batch->id,
                'user_id' => auth()->id(),
                'type' => 'out',
                'quantity' => $take,
                'reference_type' => 'prescription_dispense',
                'reference_id' => $batch->id,
                'note' => "Dispense {$productName}",
            ]);
        }

        if ($remainingUnits > 0) {
            $label = $productName ?? ('produk #' . $productId);
            throw new \Exception("Stok tidak cukup untuk {$label}");
        }
    }

    private function resolvePatientId(?string $name, ?string $phone, ?string $dob): ?int
    {
        $name = trim((string) $name);
        $phone = trim((string) $phone);
        $dob = $dob ? trim((string) $dob) : null;

        if ($phone) {
            $existingByPhone = Patient::where('phone', $phone)->first();
            if ($existingByPhone) {
                if ($dob && empty($existingByPhone->dob)) {
                    $existingByPhone->update(['dob' => $dob]);
                }
                return $existingByPhone->id;
            }
        }

        if ($name) {
            $lowerName = strtolower($name);
            $existingByName = Patient::whereRaw('LOWER(name) = ?', [$lowerName])->first();
            if ($existingByName) {
                if ($phone && empty($existingByName->phone)) {
                    $existingByName->update(['phone' => $phone]);
                }
                if ($dob && empty($existingByName->dob)) {
                    $existingByName->update(['dob' => $dob]);
                }
                return $existingByName->id;
            }

            $patient = Patient::create([
                'name' => $name,
                'phone' => $phone ?: null,
                'dob' => $dob ?: null,
            ]);
            return $patient->id;
        }

        return null;
    }
}
