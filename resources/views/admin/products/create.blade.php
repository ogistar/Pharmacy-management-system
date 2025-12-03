@extends('admin.layouts.app')

@push('page-css')
    
@endpush    

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Add Product</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Add Product</li>
	</ul>
</div>
@endpush


@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
                <!-- Add Product -->
                <form method="post" enctype="multipart/form-data" id="update_service" action="{{route('products.store')}}">
                    @csrf
                    <div class="service-fields mb-3">
                        <div class="row">
                            
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Product <span class="text-danger">*</span></label>
                                    <select class="select2 form-select form-control" name="product"> 
                                        @foreach ($purchases as $purchase)
                                            <option value="{{$purchase->id}}">{{$purchase->product}}</option>
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
                                        <input class="form-control money-input" type="text" value="{{ old('price_retail') }}" data-name="price_retail" placeholder="0">
                                        <input type="hidden" name="price_retail" class="money-hidden" value="{{ old('price_retail') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Discount (%)<span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="discount" value="0">
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
                                        <input class="form-control money-input" type="text" value="{{ old('price_wholesale') }}" data-name="price_wholesale" placeholder="0">
                                        <input type="hidden" name="price_wholesale" class="money-hidden" value="{{ old('price_wholesale') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Harga Asuransi/BPJS</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">{{ AppSettings::get('app_currency_symbol','Rp') }}</span></div>
                                        <input class="form-control money-input" type="text" value="{{ old('price_insurance') }}" data-name="price_insurance" placeholder="0">
                                        <input type="hidden" name="price_insurance" class="money-hidden" value="{{ old('price_insurance') }}">
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
                                    <input class="form-control" type="text" name="promo_name" value="{{old('promo_name')}}" placeholder="Contoh: Promo Akhir Pekan">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Diskon Promo (%)</label>
                                    <input class="form-control" type="number" step="0.01" name="promo_percent" value="{{old('promo_percent',0)}}">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Bundle Qty</label>
                                    <input class="form-control" type="number" min="1" name="bundle_qty" value="{{old('bundle_qty')}}" placeholder="Misal 3">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Bundle Price</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">{{ AppSettings::get('app_currency_symbol','Rp') }}</span></div>
                                        <input class="form-control money-input" type="text" value="{{ old('bundle_price') }}" data-name="bundle_price" placeholder="0">
                                        <input type="hidden" name="bundle_price" class="money-hidden" value="{{ old('bundle_price') }}">
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
                                    <textarea class="form-control service-desc" name="description">{{old('description')}}</textarea>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    
                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn" type="submit" name="form_submit" value="submit">Submit</button>
                    </div>
                </form>
                <!-- /Add Product -->
			</div>
		</div>
	</div>			
</div>
@endsection

@push('page-js')
	
@endpush
