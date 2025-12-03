@extends('admin.layouts.app')

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Edit Product</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Edit Product</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
				

			<!-- Edit Product -->
				<form method="post" enctype="multipart/form-data" id="update_service" action="{{route('products.update',$product)}}">
					@csrf
                    @method("PUT")
					<div class="service-fields mb-3">
						<div class="row">
							
							<div class="col-lg-12">
								<div class="form-group">
									<label>Product <span class="text-danger">*</span></label>
									<select class="select2 form-select form-control" name="product"> 
                                        @foreach ($purchases as $purchase)
                                            @if(!empty($product->purchase))
                                            <option {{($product->purchase->id == $purchase->id) ? 'selected': ''}} value="{{$purchase->id}}">{{$purchase->product}}</option>
                                            @endif
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
									<label>Harga Ecer (Retail)<span class="text-danger">*</span></label>
									<div class="input-group">
										<div class="input-group-prepend"><span class="input-group-text">{{ AppSettings::get('app_currency_symbol','Rp') }}</span></div>
										<input class="form-control money-input" type="text" value="{{$product->price_retail ?? $product->price}}" data-name="price_retail" placeholder="0">
										<input type="hidden" name="price_retail" class="money-hidden" value="{{$product->price_retail ?? $product->price}}">
									</div>
								</div>
							</div>
	
							<div class="col-lg-6">
								<div class="form-group">
									<label>Discount (%)<span class="text-danger">*</span></label>
									<input class="form-control" value="{{$product->discount}}" type="text" name="discount" value="{{old('discount')}}">
								</div>
							</div>
							
						</div>
					</div>

					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label>Harga Grosir (Wholesale)</label>
									<div class="input-group">
										<div class="input-group-prepend"><span class="input-group-text">{{ AppSettings::get('app_currency_symbol','Rp') }}</span></div>
										<input class="form-control money-input" type="text" value="{{$product->price_wholesale}}" data-name="price_wholesale" placeholder="0">
										<input type="hidden" name="price_wholesale" class="money-hidden" value="{{$product->price_wholesale}}">
									</div>
								</div>
							</div>
							<div class="col-lg-6">
								<div class="form-group">
									<label>Harga Asuransi/BPJS</label>
									<div class="input-group">
										<div class="input-group-prepend"><span class="input-group-text">{{ AppSettings::get('app_currency_symbol','Rp') }}</span></div>
										<input class="form-control money-input" type="text" value="{{$product->price_insurance}}" data-name="price_insurance" placeholder="0">
										<input type="hidden" name="price_insurance" class="money-hidden" value="{{$product->price_insurance}}">
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-4">
								<div class="form-group">
									<label>Nama Promo</label>
									<input class="form-control" type="text" name="promo_name" value="{{$product->promo_name}}" placeholder="Contoh: Promo Akhir Pekan">
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label>Diskon Promo (%)</label>
									<input class="form-control" type="number" step="0.01" name="promo_percent" value="{{$product->promo_percent}}">
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label>Bundle Qty</label>
									<input class="form-control" type="number" min="1" name="bundle_qty" value="{{$product->bundle_qty}}" placeholder="Misal 3">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label>Bundle Price</label>
									<div class="input-group">
										<div class="input-group-prepend"><span class="input-group-text">{{ AppSettings::get('app_currency_symbol','Rp') }}</span></div>
										<input class="form-control money-input" type="text" value="{{$product->bundle_price}}" data-name="bundle_price" placeholder="0">
										<input type="hidden" name="bundle_price" class="money-hidden" value="{{$product->bundle_price}}">
									</div>
								</div>
							</div>
						</div>
					</div>
	
									
					
					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-12">
								<div class="form-group">
									<label>Descriptions <span class="text-danger">*</span></label>
									<textarea class="form-control service-desc" value="{{$product->description}}" name="description">{{$product->description}}</textarea>
								</div>
							</div>
							
						</div>
					</div>					
					
					<div class="submit-section">
						<button class="btn btn-primary submit-btn" type="submit" name="form_submit" value="submit">Submit</button>
					</div>
				</form>
			<!-- /Edit Product -->
			</div>
		</div>
	</div>			
</div>
@endsection


@push('page-js')
	
@endpush
