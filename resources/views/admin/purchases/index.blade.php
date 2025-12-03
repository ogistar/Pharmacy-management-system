@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Purchase</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Purchase</li>
	</ul>
</div>
<div class="col-sm-5 col">
	<a href="{{route('purchases.create')}}" class="btn btn-primary float-right mt-2">Add New</a>
</div>
@endpush

@php
    $currencySymbol = settings('app_currency_symbol', 'Rp');
    $currencyDecimal = settings('app_currency_decimal', ',');
    $currencyThousand = settings('app_currency_thousand', '.');
@endphp

@section('content')
<div class="row">
	<div class="col-md-12">
	
		<!-- Recent Orders -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table id="purchase-table" class="datatable table table-hover table-center mb-0">
						<thead>
							<tr>
								<th>Medicine Name</th>
								<th>Category</th>
								<th>Supplier</th>
								<th>Purchase Cost ({{ $currencySymbol }})</th>
								<th>Quantity</th>
								<th>Expire Date</th>
								<th class="action-btn">Action</th>
							</tr>
						</thead>
						<tbody>
														
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- /Recent Orders -->
		
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

        var table = $('#purchase-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{route('purchases.index')}}",
            columns: [
                {data: 'product', name: 'product'},
                {data: 'category', name: 'category'},
                {data: 'supplier', name: 'supplier'},
                {data: 'cost_price', name: 'cost_price', render: data => formatAmount(data)},
                {data: 'quantity', name: 'quantity'},
				{data: 'expiry_date', name: 'expiry_date'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
        
        // open purchase return modal
        $(document).on('click', '.purchase-return-btn', function(){
            const id = $(this).data('id');
            $('#return_purchase_id').val(id);
            $('#purchaseReturnModal').modal('show');
        });
        // open adjustment modal
        $(document).on('click', '.adjust-btn', function(){
            const id = $(this).data('id');
            $('#adjust_purchase_id').val(id);
            $('#stockAdjustModal').modal('show');
        });
    });
</script> 
@endpush

@push('modals')
<!-- Purchase Return Modal -->
<div class="modal fade" id="purchaseReturnModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Retur Pembelian</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="POST" action="{{ route('purchase-returns.store') }}">
            @csrf
            <input type="hidden" name="purchase_id" id="return_purchase_id">
            <div class="form-group">
                <label>Qty dikembalikan</label>
                <input type="number" min="1" class="form-control" name="quantity" required>
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

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockAdjustModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Penyesuaian Stok</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="POST" action="{{ route('stock-adjustments.store') }}">
            @csrf
            <input type="hidden" name="purchase_id" id="adjust_purchase_id">
            <div class="form-group">
                <label>Delta stok (+/-)</label>
                <input type="number" class="form-control" name="delta" placeholder="Misal: 5 atau -3" required>
            </div>
            <div class="form-group">
                <label>Alasan</label>
                <input class="form-control" name="reason" placeholder="Opsional">
            </div>
            <button class="btn btn-info text-white">Simpan Penyesuaian</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endpush
