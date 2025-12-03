<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashSessionController extends Controller
{
    public function index(Request $request)
    {
        $title = 'cash sessions';
        $sessions = CashSession::with(['user','invoices:id,cash_session_id,total_amount,paid_amount'])
            ->latest()
            ->paginate(20);

        // decorate totals per session
        $sessions->getCollection()->transform(function ($s) {
            $s->sales_total = $s->invoices->sum('total_amount');
            $s->paid_total = $s->invoices->sum('paid_amount');
            $s->unpaid_total = $s->sales_total - $s->paid_total;
            $s->expected_cash = ($s->opening_balance ?? 0) + $s->paid_total;
            $s->diff_cash = $s->closing_balance !== null ? ($s->closing_balance - $s->expected_cash) : null;
            return $s;
        });
        return view('admin.cash-sessions.index', compact('title', 'sessions'));
    }

    public function open(Request $request)
    {
        $data = $request->validate([
            'opening_balance' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);
        $userId = $request->user()->id ?? null;

        DB::transaction(function () use ($data, $userId) {
            $existing = CashSession::where('user_id', $userId)->where('status', 'open')->lockForUpdate()->first();
            if ($existing) {
                throw new \Exception('Masih ada sesi kasir yang terbuka');
            }

            CashSession::create([
                'user_id' => $userId,
                'opening_balance' => $data['opening_balance'],
                'status' => 'open',
                'note' => $data['note'] ?? null,
                'opened_at' => now(),
            ]);
        });

        return back()->with(notify('Sesi kasir dibuka'));
    }

    public function close(Request $request, CashSession $cashSession)
    {
        $data = $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $cashSession) {
            $locked = CashSession::whereKey($cashSession->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'open') {
                throw new \Exception('Sesi sudah tertutup');
            }

            $locked->update([
                'closing_balance' => $data['closing_balance'],
                'closed_at' => now(),
                'status' => 'closed',
                'note' => $data['note'] ?? $locked->note,
            ]);
        });

        return back()->with(notify('Sesi kasir ditutup'));
    }
}
