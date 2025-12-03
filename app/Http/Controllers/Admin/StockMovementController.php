<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Purchase;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-stock-movement');
    }

    public function index(Request $request)
    {
        $title = 'stock movements';

        $query = StockMovement::with(['purchase' => function ($q) {
            $q->select('id', 'product', 'batch_no');
        }, 'user' => function ($q) {
            $q->select('id', 'name');
        }])->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('product')) {
            $query->whereHas('purchase', function ($q) use ($request) {
                $q->where('product', 'like', '%' . $request->product . '%');
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $movements = $query->paginate(20)->withQueryString();
        $types = [
            'in' => 'Stok Masuk',
            'out' => 'Stok Keluar',
            'return_in' => 'Retur Masuk',
            'return_out' => 'Retur Keluar',
            'adjust' => 'Penyesuaian',
        ];

        return view('admin.stock-movements.index', compact('title', 'movements', 'types'));
    }
}
