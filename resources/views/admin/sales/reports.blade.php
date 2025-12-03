@extends('admin.layouts.app')

<x-assets.datatables />


@push('page-css')
    
@endpush

@php
    $currencySymbol = settings('app_currency_symbol', 'Rp');
    $currencyDecimal = settings('app_currency_decimal', ',');
    $currencyThousand = settings('app_currency_thousand', '.');
    $formatAmount = function ($value) use ($currencyDecimal, $currencyThousand) {
        $formatted = number_format((float) ($value ?? 0), 2, $currencyDecimal, $currencyThousand);
        return rtrim(rtrim($formatted, '0'), $currencyDecimal);
    };
@endphp

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">POS Sales Reports</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Generate POS Sales Reports</li>
	</ul>
</div>
<div class="col-sm-5 col d-flex justify-content-end align-items-start flex-wrap" style="gap:8px;">
	<div class="flex-grow-1" style="min-width:220px;">
		<select class="form-control select2" id="invoice_search" data-placeholder="Cari invoice/pasien"></select>
	</div>
	<a href="#sale_return_modal" data-toggle="modal" class="btn btn-outline-warning mt-2">Retur Penjualan</a>
	<a href="#generate_report" data-toggle="modal" class="btn btn-primary mt-2">Generate Report</a>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
	
		@isset($invoices)
            <!--  Sales Report -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="sales-table" class="datatable table table-hover table-center mb-0">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Patient</th>
                                    <th>Total ({{ $currencySymbol }})</th>
                                    <th>Paid ({{ $currencySymbol }})</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $inv)
                                    <tr>
                                    <td><a href="{{ route('sales.show',$inv) }}">{{$inv->invoice_no}}</a></td>
                                        <td>{{$inv->patient->name ?? '-'}}</td>
                                        <td>{{ $formatAmount($inv->total_amount) }}</td>
                                        <td>{{ $formatAmount($inv->paid_amount) }}</td>
                                        <td>{{$inv->status}}</td>
                                        <td>{{date_format(date_create($inv->created_at),"d M, Y")}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- / sales Report -->
        @endisset
       
		
	</div>
</div>

<!-- Generate Modal -->
<div class="modal fade" id="generate_report" aria-hidden="true" role="dialog">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Generate Report</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form method="post" action="{{route('sales.report')}}">
					@csrf
					<div class="row form-row">
						<div class="col-12">
							<div class="row">
								<div class="col-6">
									<div class="form-group">
										<label>From</label>
										<input type="date" name="from_date" class="form-control from_date" value="{{ ($from ?? now()->subDays(29))->toDateString() }}">
									</div>
								</div>
								<div class="col-6">
									<div class="form-group">
										<label>To</label>
										<input type="date" name="to_date" class="form-control to_date" value="{{ ($to ?? now())->toDateString() }}">
									</div>
								</div>
							</div>
						</div>
					</div>
					<button type="submit" class="btn btn-primary btn-block submit_report">Submit</button>
				</form>
			</div>
		</div>
</div>
</div>
<!-- /Generate Modal -->

<!-- Sale Return Modal (per item) -->
<div class="modal fade" id="sale_return_modal" aria-hidden="true" role="dialog">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Retur Penjualan</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form method="POST" action="{{ route('sale-returns.store') }}">
					@csrf
					<div class="form-group">
						<label>ID Item Penjualan</label>
						<input name="sale_item_id" class="form-control" placeholder="Masukkan Sale Item ID" required>
						<small class="text-muted">Ambil dari detail invoice POS.</small>
					</div>
					<div class="form-group">
						<label>Quantity diretur</label>
						<input name="quantity" type="number" min="1" class="form-control" required>
					</div>
					<div class="form-group">
						<label>Alasan</label>
						<input name="reason" class="form-control" placeholder="Opsional">
					</div>
					<button class="btn btn-warning btn-block">Proses Retur</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- /Sale Return Modal -->
@endsection

@push('page-js')
<script>
    $(document).ready(function(){
        $('#sales-table').DataTable({
			dom: 'Bfrtip',		
			buttons: [
				{
				extend: 'collection',
				text: 'Export Data',
				buttons: [
					{
						extend: 'pdf',
						exportOptions: {
							columns: "thead th:not(.action-btn)"
						}
					},
					{
						extend: 'excel',
						exportOptions: {
							columns: "thead th:not(.action-btn)"
						}
					},
					{
						extend: 'csv',
						exportOptions: {
							columns: "thead th:not(.action-btn)"
						}
					},
					{
						extend: 'print',
						exportOptions: {
							columns: "thead th:not(.action-btn)"
						}
					}
				]
				}
			]
		});

        // Invoice search (Select2)
        $('#invoice_search').select2({
            placeholder: $('#invoice_search').data('placeholder'),
            allowClear: true,
            width: '100%',
            minimumInputLength: 1,
            ajax: {
                url: "{{ route('sales.search') }}",
                dataType: 'json',
                delay: 150,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                data: params => ({ q: params.term }),
                processResults: data => ({ results: data })
            }
        }).on('select2:select', function(e){
            const id = e.params.data.id;
            if(id){
                window.location = "{{ url('sales') }}/" + id;
            }
        });
    });
</script>
@endpush
