<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PosReportController extends Controller
{
    public function index()
    {
        $title = 'sales reports';
        [$from, $to] = $this->resolveRange(request());
        $invoices = Invoice::with(['user','patient'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$from->toDateString(), $to->toDateString()])
            ->orderBy('created_at','desc')
            ->get();
        $items = SaleItem::with(['invoice','product'])
            ->whereIn('invoice_id', $invoices->pluck('id'))
            ->get()
            ->groupBy('invoice_id');

        return view('admin.sales.reports', compact('title', 'invoices', 'items', 'from', 'to'));
    }

    public function generateReport(Request $request)
    {
        $this->validate($request, [
            'from_date' => 'required|date',
            'to_date' => 'required|date'
        ]);
        $title = 'sales reports';
        [$from, $to] = $this->resolveRange($request);
        $invoices = Invoice::with(['user','patient'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$from->toDateString(), $to->toDateString()])
            ->get();
        // Aggregate sale items per invoice
        $items = SaleItem::with(['invoice','product'])
            ->whereIn('invoice_id', $invoices->pluck('id'))
            ->get()
            ->groupBy('invoice_id');

        return view('admin.sales.reports', compact('title', 'invoices', 'items', 'from', 'to'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['user','patient']);
        $items = SaleItem::with(['purchase','product'])
            ->where('invoice_id', $invoice->id)
            ->get();
        $title = 'invoice '.$invoice->invoice_no;
        $currencySymbol = settings('app_currency_symbol', 'Rp');
        return view('admin.sales.show', compact('title','invoice','items','currencySymbol'));
    }

    /**
     * Search invoices by invoice_no or patient name (for Select2).
     */
    public function search(Request $request)
    {
        $q = $request->query('q', '');
        if ($q === '') {
            return response()->json([]);
        }
        $rows = Invoice::with('patient')
            ->where(function($qry) use ($q){
                $qry->where('invoice_no','like',"%{$q}%");
                $qry->orWhereHas('patient', function($p) use ($q){
                    $p->where('name','like',"%{$q}%");
                });
            })
            ->orderBy('created_at','desc')
            ->limit(20)
            ->get()
            ->map(function($inv){
                return [
                    'id' => $inv->id,
                    'text' => $inv->invoice_no . ' | ' . ($inv->patient->name ?? '-')
                ];
            });
        return response()->json($rows);
    }

    /**
     * List items for a given invoice (Select2 in return modal).
     */
    public function items(Invoice $invoice)
    {
        $items = SaleItem::with('purchase')
            ->where('invoice_id', $invoice->id)
            ->get()
            ->map(function($it){
                return [
                    'id' => $it->id,
                    'text' => ($it->purchase->product ?? 'Item #'.$it->id) . ' | Qty: '.$it->quantity,
                    'max_qty' => $it->quantity,
                ];
            });
        return response()->json($items);
    }

    /**
     * Resolve date range with sane defaults (last 30 days).
     */
    private function resolveRange(Request $request): array
    {
        $fromInput = $request->input('from_date');
        $toInput = $request->input('to_date');
        try {
            $to = $toInput ? Carbon::parse($toInput)->endOfDay() : Carbon::now()->endOfDay();
            $from = $fromInput ? Carbon::parse($fromInput)->startOfDay() : (clone $to)->subDays(29)->startOfDay();
        } catch (\Exception $e) {
            $to = Carbon::now()->endOfDay();
            $from = (clone $to)->subDays(29)->startOfDay();
        }
        return [$from, $to];
    }
}
