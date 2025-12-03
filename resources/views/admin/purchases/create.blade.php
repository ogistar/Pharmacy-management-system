@extends('admin.layouts.app')

@push('page-css')
	<!-- Datetimepicker CSS -->
	<link rel="stylesheet" href="{{asset('assets/css/bootstrap-datetimepicker.min.css')}}">
@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Add Purchase</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Add Purchase</li>
	</ul>
</div>
@endpush


@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
				
				<!-- Add Medicine -->
				<form method="post" enctype="multipart/form-data" autocomplete="off" action="{{route('purchases.store')}}">
					@csrf
					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-4">
								<div class="form-group">
									<label>Medicine Name<span class="text-danger">*</span></label>
									<input class="form-control" type="text" name="product" >
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label>Category <span class="text-danger">*</span></label>
									<select class="select2 form-select form-control" name="category"> 
										@foreach ($categories as $category)
											<option value="{{$category->id}}">{{$category->name}}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label>Supplier <span class="text-danger">*</span></label>
									<select class="select2 form-select form-control" name="supplier"> 
										@foreach ($suppliers as $supplier)
											<option value="{{$supplier->id}}">{{$supplier->name}}</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>
					</div>
					
					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label>Cost Price<span class="text-danger">*</span></label>
									<div class="input-group">
										<div class="input-group-prepend"><span class="input-group-text">{{ AppSettings::get('app_currency_symbol','Rp') }}</span></div>
										<input class="form-control money-input" type="text" value="" data-name="cost_price" placeholder="0">
										<input type="hidden" name="cost_price" class="money-hidden" value="">
									</div>
								</div>
							</div>
							<div class="col-lg-6">
								<div class="form-group">
									<label>Quantity<span class="text-danger">*</span></label>
									<input class="form-control" type="text" name="quantity">
								</div>
							</div>
						</div>
					</div>

					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-4">
								<div class="form-group">
									<label>Harga Retail</label>
									<div class="input-group">
										<div class="input-group-prepend"><span class="input-group-text">{{ AppSettings::get('app_currency_symbol','Rp') }}</span></div>
										<input class="form-control money-input" type="text" data-name="price_retail" placeholder="Kosongkan = pakai cost">
										<input type="hidden" name="price_retail" class="money-hidden" value="">
									</div>
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label>Harga Grosir</label>
									<div class="input-group">
										<div class="input-group-prepend"><span class="input-group-text">{{ AppSettings::get('app_currency_symbol','Rp') }}</span></div>
										<input class="form-control money-input" type="text" data-name="price_wholesale" placeholder="Optional">
										<input type="hidden" name="price_wholesale" class="money-hidden" value="">
									</div>
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label>Harga Asuransi/BPJS</label>
									<div class="input-group">
										<div class="input-group-prepend"><span class="input-group-text">{{ AppSettings::get('app_currency_symbol','Rp') }}</span></div>
										<input class="form-control money-input" type="text" data-name="price_insurance" placeholder="Optional">
										<input type="hidden" name="price_insurance" class="money-hidden" value="">
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label>Promo (%)</label>
									<input class="form-control" type="number" name="promo_percent" min="0" max="100" step="any" placeholder="Contoh: 10">
								</div>
							</div>
							<div class="col-lg-6">
								<div class="form-group">
									<label>Nama Promo</label>
									<input class="form-control" type="text" name="promo_name" maxlength="200" placeholder="Contoh: Diskon Lebaran">
								</div>
							</div>
						</div>
					</div>

					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label>Bundle Qty</label>
									<input class="form-control" type="number" name="bundle_qty" min="1" placeholder="Contoh: 3">
								</div>
							</div>
							<div class="col-lg-6">
								<div class="form-group">
									<label>Bundle Price</label>
									<div class="input-group">
										<div class="input-group-prepend"><span class="input-group-text">{{ AppSettings::get('app_currency_symbol','Rp') }}</span></div>
										<input class="form-control money-input" type="text" data-name="bundle_price" placeholder="Harga paket bundle">
										<input type="hidden" name="bundle_price" class="money-hidden" value="">
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label>Expire Date<span class="text-danger">*</span></label>
									<input class="form-control" type="date" name="expiry_date">
								</div>
							</div>
							<div class="col-lg-6">
								<div class="form-group">
									<label>Medicine Image</label>
									<input type="file" name="image" class="form-control">
								</div>
							</div>
						</div>
					</div>

					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label>Batch / Lot No.<span class="text-danger">*</span></label>
									<input class="form-control" type="text" name="batch_no" placeholder="Contoh: B2301-A">
								</div>
							</div>
							<div class="col-lg-6">
								<div class="form-group">
									<label>Lokasi Rak</label>
									<input class="form-control" type="text" name="rack_location" placeholder="Contoh: Rak A2 - Lajur 3">
								</div>
							</div>
						</div>
					</div>
					
					
					<div class="submit-section">
						<button class="btn btn-primary submit-btn" type="submit" >Submit</button>
					</div>
				</form>
				<!-- /Add Medicine -->

			</div>
		</div>
	</div>			
</div>
@endsection

@push('page-js')
	<!-- Datetimepicker JS -->
	<script src="{{asset('assets/js/moment.min.js')}}"></script>
	<script src="{{asset('assets/js/bootstrap-datetimepicker.min.js')}}"></script>	
@endpush

