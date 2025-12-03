@extends('admin.layouts.app')

@php
    $formatAmount = function($v){ return number_format((float)$v,2,',','.'); };
@endphp

@push('page-header')
<div class="col-sm-8 col-auto">
	<h3 class="page-title">Invoice {{ $invoice->invoice_no }}</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{ route('sales.report') }}">Sales Reports</a></li>
		<li class="breadcrumb-item active">Detail</li>
	</ul>
</div>
@endpush

@section('content')
<div class="container">
    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between">
            <div>
                <div class="text-muted small">Pasien</div>
                <div>{{ $invoice->patient->name ?? '-' }}</div>
                <div class="text-muted small">Kasir: {{ $invoice->user->name ?? '-' }}</div>
            </div>
            <div>
                <div class="text-muted small">Total</div>
                <div class="h5 mb-0">{{ $currencySymbol }} {{ $formatAmount($invoice->total_amount) }}</div>
                <div class="text-muted small">Dibayar: {{ $currencySymbol }} {{ $formatAmount($invoice->paid_amount) }}</div>
                <div class="badge badge-{{ $invoice->status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($invoice->status) }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6 class="mb-3">Item Penjualan</h6>
            <div class="mb-2">
                <button class="btn btn-sm btn-outline-warning" data-toggle="modal" data-target="#saleReturnModal">Retur Item (pilih)</button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Batch</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                            <th>Retur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $it)
                        <tr>
                            <td>{{ $it->purchase->product ?? $it->product->name ?? '-' }}</td>
                            <td>{{ $it->purchase->batch_no ?? '-' }}</td>
                            <td>{{ $it->quantity }}</td>
                            <td>{{ $currencySymbol }} {{ $formatAmount($it->unit_price) }}</td>
                            <td>{{ $currencySymbol }} {{ $formatAmount($it->total_price) }}</td>
                            <td>
                                <form method="POST" action="{{ route('sale-returns.store') }}" class="form-inline">
                                    @csrf
                                    <input type="hidden" name="sale_item_id" value="{{ $it->id }}">
                                    <input type="number" name="quantity" min="1" max="{{ $it->quantity }}" value="{{ $it->quantity }}" class="form-control form-control-sm mr-1" style="width:80px;">
                                    <input type="text" name="reason" class="form-control form-control-sm mr-1" placeholder="Alasan">
                                    <button class="btn btn-sm btn-warning">Retur</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        @if($items->isEmpty())
                        <tr><td colspan="6" class="text-muted">Tidak ada item.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal retur dengan pencarian item -->
<div class="modal fade" id="saleReturnModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Retur Item</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="POST" action="{{ route('sale-returns.store') }}">
            @csrf
            <div class="form-group">
                <label>Pilih item</label>
                <select class="form-control select2" id="return_item_select" name="sale_item_id" data-placeholder="Cari item invoice" required></select>
            </div>
            <div class="form-group">
                <label>Qty diretur</label>
                <input type="number" min="1" class="form-control" name="quantity" id="return_qty" required>
            </div>
            <div class="form-group">
                <label>Alasan</label>
                <input class="form-control" name="reason" placeholder="Opsional">
            </div>
            <button class="btn btn-warning">Proses Retur</button>
        </form>
      </div>
    </div>
  </div>
</div>

@push('page-js')
<script>
$(document).ready(function(){
    $('#return_item_select').select2({
        placeholder: $('#return_item_select').data('placeholder'),
        allowClear: true,
        width: '100%',
        ajax: {
            url: "{{ route('sales.items', $invoice) }}",
            dataType: 'json',
            delay: 150,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            processResults: data => ({
                results: data.map(it => ({
                    id: it.id,
                    text: it.text,
                    max_qty: it.max_qty
                }))
            })
        }
    }).on('select2:select', function(e){
        const max = e.params.data.max_qty || 1;
        $('#return_qty').attr('max', max).val(max);
    }).on('select2:clear', function(){
        $('#return_qty').val('');
    });
});
</script>
@endpush
@endsection
