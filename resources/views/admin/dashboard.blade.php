@extends('admin.layouts.app')

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Welcome {{auth()->user()->name}}!</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item active">Dashboard</li>
	</ul>
</div>
@endpush

@php
    $currencySymbol = settings('app_currency_symbol', 'Rp');
    $currencyDecimal = settings('app_currency_decimal', ',');
    $currencyThousand = settings('app_currency_thousand', '.');
    $formatAmount = function ($value) use ($currencyDecimal, $currencyThousand) {
        $formatted = number_format((float) ($value ?? 0), 2, $currencyDecimal, $currencyThousand);
        return rtrim(rtrim($formatted, '0'), $currencyDecimal);
    };
    $formatCurrency = function ($value) use ($currencySymbol, $formatAmount) {
        $formatted = $formatAmount($value);
        return $currencySymbol . ' ' . $formatted;
    };
@endphp

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form class="form-inline" method="GET" action="{{ route('dashboard') }}">
            <div class="form-group mr-2 mb-2">
                <label class="mr-2">Dari</label>
                <input type="date" class="form-control" name="start_date" value="{{ $startDate->toDateString() }}">
            </div>
            <div class="form-group mr-2 mb-2">
                <label class="mr-2">Sampai</label>
                <input type="date" class="form-control" name="end_date" value="{{ $endDate->toDateString() }}">
            </div>
            <div class="form-group mr-2 mb-2">
                <label class="mr-2">Hari terakhir</label>
                <input type="number" class="form-control" name="days" min="1" value="{{ $days }}">
            </div>
            <button class="btn btn-primary mb-2">Filter</button>
            <div class="ml-auto text-muted">Rentang: {{ $rangeLabel }}</div>
        </form>
    </div>
</div>
<div class="row">
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-primary border-primary">
                        <i class="fe fe-money"></i>
                    </span>
                    <div class="dash-count">
                        <h3>{{ $formatCurrency($today_sales) }}</h3>
                    </div>
                </div>
                <div class="dash-widget-info">
                    <h6 class="text-muted">Penjualan ({{ $rangeLabel }})</h6>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-primary w-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-success">
                        <i class="fe fe-credit-card"></i>
                    </span>
                    <div class="dash-count">
                        <h3>{{$total_categories}}</h3>
                    </div>
                </div>
                <div class="dash-widget-info">
                    
                    <h6 class="text-muted">Product Categories</h6>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-success w-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-danger border-danger">
                        <i class="fe fe-folder"></i>
                    </span>
                    <div class="dash-count">
                        <h3>{{$total_expired_products}}</h3>
                    </div>
                </div>
                <div class="dash-widget-info">
                    
                    <h6 class="text-muted">Expired Products</h6>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-danger w-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-warning border-warning">
                        <i class="fe fe-users"></i>
                    </span>
                    <div class="dash-count">
                        <h3>{{\DB::table('users')->count()}}</h3>
                    </div>
                </div>
                <div class="dash-widget-info">
                    
                    <h6 class="text-muted">System Users</h6>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-warning w-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-lg-6">
        <div class="card card-table p-3">
            <div class="card-header">
                <h4 class="card-title ">Penjualan ({{ $rangeLabel }})</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-modern table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Patient</th>
                                <th>Total ({{ $currencySymbol }})</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latest_sales as $inv)
                            <tr>
                                <td>{{ $inv->invoice_no }}</td>
                                <td>{{ $inv->patient->name ?? '-' }}</td>
                                <td>{{ $formatAmount($inv->total_amount) }}</td>
                                <td>{{ $inv->created_at }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4">Tidak ada invoice pada rentang ini</td></tr>
                            @endforelse                                                                                     
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 col-lg-6">
                    
        <!-- Pie Chart -->
        <div class="card card-chart">
            <div class="card-header">
                <h4 class="card-title text-center">Resources</h4>
            </div>
            <div class="card-body">
                <div style="">
                    {!! $pieChart->render() !!}
                </div>
            </div>
        </div>
        <!-- /Pie Chart -->
        
    </div>	
    
    
</div>

@endsection

@push('page-js')
<script src="{{asset('assets/plugins/chart.js/Chart.bundle.min.js')}}"></script>
@endpush
