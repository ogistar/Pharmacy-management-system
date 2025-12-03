<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'delta' => 'required|integer|not_in:0',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $request) {
            $purchase = Purchase::whereKey($data['purchase_id'])->lockForUpdate()->firstOrFail();
            $newQty = $purchase->quantity + $data['delta'];
            if ($newQty < 0) {
                throw new \Exception('Adjustment would make stock negative');
            }

            $purchase->quantity = $newQty;
            $purchase->save();

            StockAdjustment::create([
                'purchase_id' => $purchase->id,
                'user_id' => $request->user()->id ?? null,
                'delta' => $data['delta'],
                'reason' => $data['reason'] ?? null,
            ]);

            StockMovement::create([
                'purchase_id' => $purchase->id,
                'user_id' => $request->user()->id ?? null,
                'type' => 'adjust',
                'quantity' => abs($data['delta']),
                'reference_type' => 'stock_adjustment',
                'reference_id' => $purchase->id,
                'note' => $data['reason'] ?? 'Penyesuaian manual',
            ]);
        });

        return back()->with(notify('Stock adjusted'));
    }
}
