<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\StockOpname;
use App\Models\StockTransfer;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index()
    {
        $title = 'stock_opname';
        $purchases = Purchase::orderBy('product')->limit(100)->get();
        $opnames = StockOpname::with(['purchase', 'user'])->latest()->limit(30)->get();
        $transfers = StockTransfer::with(['purchase', 'user'])->latest()->limit(30)->get();
        $movements = StockMovement::with(['purchase','user'])->latest()->limit(30)->get();

        return view('admin.stock-opname.index', compact(
            'title',
            'purchases',
            'opnames',
            'transfers',
            'movements'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'counted_quantity' => 'required|integer|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $request) {
            $purchase = Purchase::whereKey($data['purchase_id'])->lockForUpdate()->firstOrFail();

            $systemQty = (int) $purchase->quantity;
            $counted = (int) $data['counted_quantity'];
            $delta = $counted - $systemQty;

            $purchase->quantity = $counted;
            $purchase->save();

            StockOpname::create([
                'purchase_id' => $purchase->id,
                'user_id' => $request->user()->id ?? null,
                'system_quantity' => $systemQty,
                'counted_quantity' => $counted,
                'delta' => $delta,
                'note' => $data['note'] ?? null,
            ]);

            if ($delta !== 0) {
                StockMovement::create([
                    'purchase_id' => $purchase->id,
                    'user_id' => $request->user()->id ?? null,
                    'type' => 'adjust',
                    'quantity' => abs($delta),
                    'reference_type' => 'stock_opname',
                    'reference_id' => $purchase->id,
                    'note' => 'Opname stok: ' . ($data['note'] ?? ''),
                ]);
            }
        });

        return back()->with(notify('Stock opname saved'));
    }
}
