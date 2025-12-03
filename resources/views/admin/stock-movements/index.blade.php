@extends('admin.layouts.app')

<x-assets.datatables />

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
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="text-uppercase text-muted small">Log Stok</div>
            <h3 class="mb-1">Pergerakan Stok</h3>
            <p class="text-muted mb-0">Pantau stok masuk/keluar, penyesuaian, dan retur.</p>
        </div>
        <div class="pill">
            <span class="fe fe-layers"></span>
            <span>{{ $movements->total() }} log</span>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="form-row">
                <div class="form-group col-md-2">
                    <label>Jenis</label>
                    <select class="form-control" name="type">
                        <option value="">Semua</option>
                        @foreach($types as $k=>$v)
                            <option value="{{ $k }}" {{ request('type')===$k?'selected':'' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Produk / Batch</label>
                    <select class="form-control select2" id="product_filter" name="purchase_id" data-placeholder="Cari produk/batch">
                        @if(request('purchase_id') && request('product_label'))
                            <option value="{{ request('purchase_id') }}" selected>{{ request('product_label') }}</option>
                        @endif
                    </select>
                    <input type="hidden" name="product_label" id="product_label" value="{{ request('product_label') }}">
                </div>
                <div class="form-group col-md-2">
                    <label>Dari</label>
                    <input type="date" class="form-control" name="from" value="{{ request('from') }}">
                </div>
                <div class="form-group col-md-2">
                    <label>Sampai</label>
                    <input type="date" class="form-control" name="to" value="{{ request('to') }}">
                </div>
                <div class="form-group col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary mr-2">Filter</button>
                    <a href="{{ route('stock-movements.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="datatable table table-striped table-bordered table-hover table-center mb-0">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Jenis</th>
                            <th>Qty</th>
                            <th>Produk / Batch</th>
                            <th>User</th>
                            <th>Ref</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $m)
                        <tr>
                            <td>{{ $m->created_at }}</td>
                            <td>{{ $types[$m->type] ?? $m->type }}</td>
                            <td>{{ $m->quantity }}</td>
                            <td>
                                {{ $m->purchase->product ?? '-' }}
                                @if(!empty($m->purchase->batch_no))
                                    <div class="text-muted small">Batch: {{ $m->purchase->batch_no }}</div>
                                @endif
                            </td>
                            <td>{{ $m->user->name ?? '-' }}</td>
                            <td>{{ $m->reference_type }} @if($m->reference_id)#{{ $m->reference_id }}@endif</td>
                            <td>{{ $m->note }}</td>
                        </tr>
                        @endforeach
                        @if($movements->isEmpty())
                        <tr><td colspan="7">Tidak ada data.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
            {{ $movements->links() }}
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
    $(document).ready(function() {
        $('.datatable').DataTable({
            paging: false,
            info: false
        });

        // Select2 for product/batch filter
        $('#product_filter').select2({
            placeholder: $('#product_filter').data('placeholder'),
            allowClear: true,
            width: '100%',
            minimumInputLength: 1,
            ajax: {
                url: "{{ route('purchases.search') }}",
                dataType: 'json',
                delay: 150,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                data: params => ({ q: params.term }),
                processResults: data => ({
                    results: (data || []).map(it => ({
                        id: it.id ?? it.purchase_id,
                        text: it.text ?? `${it.product ?? ''} | Batch ${it.batch_no ?? '-'} | Stok ${it.quantity ?? 0}`
                    }))
                }),
                transport: function(params, success, failure){
                    const req = $.ajax(params);
                    req.then(success).fail(function(){
                        // fallback ke endpoint POS jika search purchases bermasalah
                        $.ajax({
                            url: "{{ route('pos.products') }}",
                            dataType: 'json',
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                            data: { q: params.data.q },
                        }).then(function(data){
                            success((data || []).map(it => ({
                                id: it.purchase_id,
                                text: `${it.product} | Batch ${it.batch_no ?? '-'} | Stok ${it.quantity ?? 0}`
                            })));
                        }).fail(failure);
                    });
                    return req;
                }
            }
        }).on('select2:select', function(e){
            $('#product_label').val(e.params.data.text);
        }).on('select2:clear', function(){
            $('#product_label').val('');
        });
    });
</script>
@endpush
