<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create-sale-return')->only(['store','search','preview']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sale_item_id' => 'required|exists:sale_items,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $request) {
            $item = SaleItem::with('purchase')->whereKey($data['sale_item_id'])->lockForUpdate()->firstOrFail();
            $purchase = Purchase::whereKey($item->purchase_id)->lockForUpdate()->first();
            $qty = $data['quantity'];

            if ($qty > $item->quantity) {
                throw new \Exception('Return quantity exceeds sold quantity');
            }

            $refundAmount = $item->unit_price * $qty;

            if ($purchase) {
                $purchase->quantity = $purchase->quantity + $qty;
                $purchase->save();

                StockMovement::create([
                    'purchase_id' => $purchase->id,
                    'user_id' => $request->user()->id ?? null,
                    'type' => 'return_in',
                    'quantity' => $qty,
                    'reference_type' => 'sale_return',
                    'reference_id' => $item->id,
                    'note' => $data['reason'] ?? 'Retur penjualan',
                ]);
            }

            SaleReturn::create([
                'invoice_id' => $item->invoice_id,
                'sale_item_id' => $item->id,
                'user_id' => $request->user()->id ?? null,
                'quantity' => $qty,
                'refund_amount' => $refundAmount,
                'reason' => $data['reason'] ?? null,
            ]);
        });

        return back()->with(notify('Sale return recorded'));
    }

    /**
     * Search sale items for Select2 (by product or invoice number).
     */
    public function search(Request $request)
    {
        $q = $request->query('q', '');
        if ($q === '') {
            return response()->json([]);
        }

        $rows = SaleItem::with(['purchase','invoice.patient'])
            ->where(function($qry) use ($q){
                $qry->whereHas('purchase', function($p) use ($q){
                    $p->where('product','like',"%{$q}%");
                });
                $qry->orWhereHas('invoice', function($inv) use ($q){
                    $inv->where('invoice_no','like',"%{$q}%");
                    $inv->orWhereHas('patient', function($pat) use ($q){
                        $pat->where('name','like',"%{$q}%");
                    });
                });
            })
            ->orderBy('created_at','desc')
            ->limit(20)
            ->get()
            ->map(function($it){
                $product = $it->purchase->product ?? 'Item #'.$it->id;
                $batch = $it->purchase->batch_no ?? '-';
                $inv = $it->invoice->invoice_no ?? '-';
                $patient = $it->invoice->patient->name ?? '-';
                return [
                    'id' => $it->id,
                    'text' => "{$product} | Batch {$batch} | Invoice {$inv} | {$patient} | Qty {$it->quantity}",
                    'max_qty' => $it->quantity,
                ];
            });

        return response()->json($rows);
    }

    /**
     * Preview sale item + invoice for quick verification.
     */
    public function preview(SaleItem $saleItem)
    {
        $saleItem->load(['invoice.patient','purchase']);
        return view('admin.stock-tools.partials.sale-preview', compact('saleItem'));
    }
}
