@extends('admin.layouts.app')

@php
    $currencySymbol = settings('app_currency_symbol', 'Rp');
    $currencyDecimal = settings('app_currency_decimal', ',');
    $currencyThousand = settings('app_currency_thousand', '.');
    $formatAmount = function ($value) use ($currencyDecimal, $currencyThousand) {
        $formatted = number_format((float) ($value ?? 0), 2, $currencyDecimal, $currencyThousand);
        return rtrim(rtrim($formatted, '0'), $currencyDecimal);
    };
@endphp

@section('content')
<div class="container">
    <h4>Detail Piutang</h4>
    <div>Invoice: {{ $receivable->invoice->invoice_no ?? $receivable->invoice_id }}</div>
    <div>Pasien: {{ $receivable->patient->name ?? '-' }}</div>
    <div>Total: {{ $formatAmount($receivable->total_due) }}</div>
    <div>Terbayar: {{ $formatAmount($receivable->paid_amount) }}</div>
    <div>Status: {{ $receivable->status }}</div>
    <div>Jatuh tempo: {{ $receivable->due_date }}</div>

    <h5 class="mt-3">Pembayaran</h5>
    <table class="table table-sm">
        <thead><tr><th>Tanggal</th><th>Jumlah ({{ $currencySymbol }})</th><th>Petugas</th></tr></thead>
        <tbody>
        @foreach($receivable->payments as $p)
            <tr>
                <td>{{ $p->paid_at }}</td>
                <td>{{ $formatAmount($p->amount) }}</td>
                <td>{{ $p->user->name ?? '-' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <form method="POST" action="{{ route('receivables.pay',$receivable) }}">
        @csrf
        <div class="form-row">
            <div class="col-md-3">
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text">{{ AppSettings::get('app_currency_symbol','Rp') }}</span></div>
                    <input class="form-control money-input" type="text" placeholder="0" data-name="amount">
                    <input type="hidden" name="amount" class="money-hidden" value="">
                </div>
            </div>
            <div class="col-md-2"><button class="btn btn-primary">Bayar</button></div>
        </div>
    </form>
</div>
@endsection
