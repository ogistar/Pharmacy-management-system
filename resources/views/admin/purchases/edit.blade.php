@extends('admin.layouts.app')

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Edit Purchase</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Edit Purchase</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
			
			<!-- Edit Supplier -->
			<form method="post" enctype="multipart/form-data" autocomplete="off" action="{{route('purchases.update',$purchase)}}">
				@csrf
				@method("PUT")
				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-4">
							<div class="form-group">
								<label>Medicine Name<span class="text-danger">*</span></label>
								<input class="form-control" type="text" value="{{$purchase->product}}" name="product" >
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Category <span class="text-danger">*</span></label>
								<select class="select2 form-select form-control" name="category"> 
									@foreach ($categories as $category)
										<option {{($purchase->category->id == $category->id) ? 'selected': ''}} value="{{$category->id}}">{{$category->name}}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Supplier <span class="text-danger">*</span></label>
								<select class="select2 form-select form-control" name="supplier"> 
									@foreach ($suppliers as $supplier)
										<option @if($purchase->supplier->id == $supplier->id) selected @endif value="{{$supplier->id}}">{{$supplier->name}}</option>
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
									<input class="form-control money-input" type="text" value="{{$purchase->cost_price}}" data-name="cost_price" placeholder="0">
									<input type="hidden" name="cost_price" class="money-hidden" value="{{$purchase->cost_price}}">
								</div>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<label>Quantity<span class="text-danger">*</span></label>
								<input class="form-control" value="{{$purchase->quantity}}" type="text" name="quantity">
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
									<input class="form-control money-input" type="text" value="{{$purchase->purchaseProduct->price_retail ?? ''}}" data-name="price_retail" placeholder="Kosongkan = pakai cost">
									<input type="hidden" name="price_retail" class="money-hidden" value="{{$purchase->purchaseProduct->price_retail ?? ''}}">
								</div>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Harga Grosir</label>
								<div class="input-group">
									<div class="input-group-prepend"><span class="input-group-text">{{ AppSettings::get('app_currency_symbol','Rp') }}</span></div>
									<input class="form-control money-input" type="text" value="{{$purchase->purchaseProduct->price_wholesale ?? ''}}" data-name="price_wholesale" placeholder="Optional">
									<input type="hidden" name="price_wholesale" class="money-hidden" value="{{$purchase->purchaseProduct->price_wholesale ?? ''}}">
								</div>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Harga Asuransi/BPJS</label>
								<div class="input-group">
									<div class="input-group-prepend"><span class="input-group-text">{{ AppSettings::get('app_currency_symbol','Rp') }}</span></div>
									<input class="form-control money-input" type="text" value="{{$purchase->purchaseProduct->price_insurance ?? ''}}" data-name="price_insurance" placeholder="Optional">
									<input type="hidden" name="price_insurance" class="money-hidden" value="{{$purchase->purchaseProduct->price_insurance ?? ''}}">
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
								<input class="form-control" type="number" name="promo_percent" min="0" max="100" step="any" value="{{$purchase->purchaseProduct->promo_percent ?? ''}}">
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<label>Nama Promo</label>
								<input class="form-control" type="text" name="promo_name" maxlength="200" value="{{$purchase->purchaseProduct->promo_name ?? ''}}">
							</div>
						</div>
					</div>
				</div>

				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label>Bundle Qty</label>
								<input class="form-control" type="number" name="bundle_qty" min="1" value="{{$purchase->purchaseProduct->bundle_qty ?? ''}}">
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<label>Bundle Price</label>
								<div class="input-group">
									<div class="input-group-prepend"><span class="input-group-text">{{ AppSettings::get('app_currency_symbol','Rp') }}</span></div>
									<input class="form-control money-input" type="text" value="{{$purchase->purchaseProduct->bundle_price ?? ''}}" data-name="bundle_price" placeholder="Harga paket bundle">
									<input type="hidden" name="bundle_price" class="money-hidden" value="{{$purchase->purchaseProduct->bundle_price ?? ''}}">
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
								<input class="form-control" value="{{$purchase->expiry_date}}" type="date" name="expiry_date">
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<label>Medicine Image</label>
								<input type="file" name="image" value="{{$purchase->image}}" class="form-control">
							</div>
						</div>
					</div>
				</div>

				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label>Batch / Lot No.<span class="text-danger">*</span></label>
								<input class="form-control" type="text" name="batch_no" value="{{$purchase->batch_no}}" placeholder="Contoh: B2301-A">
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<label>Lokasi Rak</label>
								<input class="form-control" type="text" name="rack_location" value="{{$purchase->rack_location}}" placeholder="Contoh: Rak A2 - Lajur 3">
							</div>
						</div>
					</div>
				</div>
				
				
				<div class="submit-section">
					<button class="btn btn-primary submit-btn" type="submit" >Submit</button>
				</div>
			</form>
			<!-- /Edit Supplier -->

			</div>
		</div>
	</div>			
</div>
@endsection	



@push('page-js')
	<!-- Select2 JS -->
	<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
@endpush




