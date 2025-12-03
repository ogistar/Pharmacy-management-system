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
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Products</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Products</li>
	</ul>
</div>
<div class="col-sm-5 col">
	<a href="{{route('products.create')}}" class="btn btn-primary float-right mt-2">Add Product</a>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
	
		<!-- Products -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table id="product-table" class="datatable table table-hover table-center mb-0">
						<thead>
							<tr>
								<th>Product Name</th>
								<th>Category</th>
								<th>Price ({{ $currencySymbol }})</th>
								<th>Quantity</th>
								<th>Discount</th>
								<th>Expiry Date</th>
								<th class="action-btn">Action</th>
							</tr>
						</thead>
						<tbody>

														
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- /Products -->
		
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

        var table = $('#product-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{route('products.index')}}",
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
