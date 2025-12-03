<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $request) {
            $purchase = Purchase::whereKey($data['purchase_id'])->lockForUpdate()->firstOrFail();
            if ($data['quantity'] > $purchase->quantity) {
                throw new \Exception('Return quantity exceeds available stock');
            }

            $purchase->quantity = $purchase->quantity - $data['quantity'];
            $purchase->save();

            PurchaseReturn::create([
                'purchase_id' => $purchase->id,
                'user_id' => $request->user()->id ?? null,
                'quantity' => $data['quantity'],
                'reason' => $data['reason'] ?? null,
            ]);

            StockMovement::create([
                'purchase_id' => $purchase->id,
                'user_id' => $request->user()->id ?? null,
                'type' => 'return_out',
                'quantity' => $data['quantity'],
                'reference_type' => 'purchase_return',
                'reference_id' => $purchase->id,
                'note' => $data['reason'] ?? 'Retur ke supplier',
            ]);
        });

        return back()->with(notify('Purchase return recorded'));
    }
}
