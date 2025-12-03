<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create-stock-transfer')->only(['store']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'to_rack' => 'required|string|max:100',
            'note' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $request) {
            $purchase = Purchase::whereKey($data['purchase_id'])->lockForUpdate()->firstOrFail();

            $fromRack = $purchase->rack_location;
            $purchase->rack_location = $data['to_rack'];
            $purchase->save();

            StockTransfer::create([
                'purchase_id' => $purchase->id,
                'user_id' => $request->user()->id ?? null,
                'from_rack' => $fromRack,
                'to_rack' => $data['to_rack'],
                'quantity_snapshot' => $purchase->quantity ?? 0,
                'note' => $data['note'] ?? null,
            ]);
        });

        return back()->with(notify('Transfer rak dicatat'));
    }
}
