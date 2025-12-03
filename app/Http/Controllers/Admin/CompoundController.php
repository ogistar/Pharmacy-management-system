<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Compound;
use App\Models\CompoundItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompoundController extends Controller
{
    public function index()
    {
        $title = 'compounds';
        $compounds = Compound::with('items.product')->latest()->get();
        $products = Product::with('purchase')->get();
        return view('admin.compounds.index', compact('title','compounds','products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'service_fee' => 'nullable|numeric|min:0',
            'markup_percent' => 'nullable|numeric|min:0',
            'price_override' => 'nullable|numeric|min:0',
            'items' => 'nullable|array',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.quantity' => 'required_with:items.*.product_id|integer|min:1',
        ]);

        DB::transaction(function () use ($data) {
            $compound = Compound::create([
                'name' => $data['name'],
                'service_fee' => $data['service_fee'] ?? 0,
                'markup_percent' => $data['markup_percent'] ?? 0,
                'price_override' => $data['price_override'] ?? null,
            ]);

            foreach ($data['items'] ?? [] as $item) {
                if (empty($item['product_id'])) {
                    continue;
                }
                CompoundItem::create([
                    'compound_id' => $compound->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'] ?? 1,
                ]);
            }
        });

        return back()->with(notify('Racikan tersimpan'));
    }

    public function destroy(Compound $compound)
    {
        $compound->delete();
        return back()->with(notify('Racikan dihapus'));
    }
}
