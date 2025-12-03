@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">Piutang Penjualan</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
        <li class="breadcrumb-item active">Piutang</li>
    </ul>
</div>
@endpush

@php
    $currencySymbol = settings('app_currency_symbol', 'Rp');
    $currencyDecimal = settings('app_currency_decimal', ',');
    $currencyThousand = settings('app_currency_thousand', '.');
@endphp

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="receivable-table" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Pasien</th>
                                <th>Total ({{ $currencySymbol }})</th>
                                <th>Terbayar ({{ $currencySymbol }})</th>
                                <th>Status</th>
                                <th>Jatuh tempo</th>
                                <th class="text-center action-btn">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
    $(document).ready(function() {
        const decimalSeparator = @json($currencyDecimal);
        const thousandSeparator = @json($currencyThousand);
        const formatAmount = (value) => {
            const num = Number(value ?? 0);
            if (Number.isNaN(num)) return value ?? '';
            const parts = num.toFixed(2).split('.');
            const intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);
            const decimalPart = parts[1] === '00' ? '' : decimalSeparator + parts[1];
            return `${intPart}${decimalPart}`;
        };

        var table = $('#receivable-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('receivables.index') }}",
            columns: [
                {data: 'invoice_no', name: 'invoice_no'},
                {data: 'patient', name: 'patient'},
                {data: 'total_due', name: 'total_due', render: data => formatAmount(data)},
                {data: 'paid_amount', name: 'paid_amount', render: data => formatAmount(data)},
                {data: 'status', name: 'status'},
                {data: 'due_date', name: 'due_date'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    });
</script>
@endpush
