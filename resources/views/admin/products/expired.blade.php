@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
	
@endpush

@php
	$currencySymbol = settings('app_currency_symbol', 'Rp');
	$currencyDecimal = settings('app_currency_decimal', ',');
	$currencyThousand = settings('app_currency_thousand', '.');
@endphp

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Expired</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('products.index')}}">Products</a></li>
		<li class="breadcrumb-item active">Expired</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
	
		<!-- Recent Orders -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table id="expired-product" class="datatable table table-striped table-bordered table-hover table-center mb-0">
						<thead>
							<tr>
								<th>Brand Name</th>
								<th>Category</th>
								<th>Price ({{ $currencySymbol }})</th>
								<th>Quantity</th>
								<th>Discount</th>
								<th>Expire</th>
								<th class="action-btn">Action</th>
							</tr>
						</thead>
						<tbody>
							
							
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- /Expired Products -->
		
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

        var table = $('#expired-product').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{route('expired')}}",
            columns: [
                {data: 'product', name: 'product'},
                {data: 'category', name: 'category'},
                {data: 'price', name: 'price', render: data => formatAmount(data)},
                {data: 'quantity', name: 'quantity'},
                {data: 'discount', name: 'discount'},
				{data: 'expiry_date', name: 'expiry_date'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
        
    });
</script> 
@endpush
