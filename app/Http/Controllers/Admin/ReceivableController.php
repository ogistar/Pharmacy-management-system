<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ReceivableController extends Controller
{
    public function index()
    {
        $title = 'receivables';
        if(request()->ajax()){
            $query = Receivable::with(['patient','invoice'])->select('receivables.*');
            return DataTables::of($query)
                ->addColumn('invoice_no', function($row){
                    return $row->invoice->invoice_no ?? $row->invoice_id;
                })
                ->addColumn('patient', function($row){
                    return $row->patient->name ?? '-';
                })
                ->addColumn('total_due', function($row){
                    return $row->total_due;
                })
                ->addColumn('paid_amount', function($row){
                    return $row->paid_amount;
                })
                ->addColumn('due_date', function($row){
                    return $row->due_date;
                })
                ->addColumn('action', function($row){
                    $view = '<a href="'.route('receivables.show',$row->id).'" class="btn btn-sm btn-primary">View</a>';
                    return $view;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.receivables.index', compact('title'));
    }

    public function show(Receivable $receivable)
    {
        $receivable->load(['patient', 'invoice', 'payments']);
        $title = 'receivable detail';
        return view('admin.receivables.show', compact('title', 'receivable'));
    }

    public function pay(Request $request, Receivable $receivable)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($data, $receivable, $request) {
            $locked = Receivable::whereKey($receivable->id)->lockForUpdate()->firstOrFail();
            $newPaid = $locked->paid_amount + $data['amount'];
            $status = $newPaid >= $locked->total_due ? 'closed' : 'open';

            $locked->update([
                'paid_amount' => $newPaid,
                'status' => $status,
            ]);

            ReceivablePayment::create([
                'receivable_id' => $locked->id,
                'user_id' => $request->user()->id ?? null,
                'amount' => $data['amount'],
                'paid_at' => now(),
            ]);

            if ($status === 'closed' && $locked->invoice) {
                $locked->invoice->status = 'paid';
                $locked->invoice->save();
            }
        });

        return back()->with(notify('Payment recorded'));
    }
}
