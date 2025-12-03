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

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="text-uppercase text-muted small">{{ __('menu.cash_sessions') }}</div>
            <h3 class="mb-1">{{ __('app.cash_sessions') }}</h3>
            <p class="text-muted mb-0">{{ __('app.add') }} &amp; monitor sesi kasir.</p>
        </div>
        <div class="pill">
            <span class="fe fe-clock"></span>
            <span>{{ $sessions->total() }} {{ __('menu.cash_sessions') }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('cash-sessions.open') }}" class="form-inline mb-3 card p-3 glass-card">
        @csrf
        <div class="input-group mr-2" style="max-width:220px;">
            <div class="input-group-prepend"><span class="input-group-text">{{ $currencySymbol }}</span></div>
            <input type="text" class="form-control money-input" data-target="#opening_balance_hidden" placeholder="{{ __('app.opening_balance') }}" required>
            <input type="hidden" name="opening_balance" id="opening_balance_hidden" value="">
        </div>
        <input name="note" class="form-control mr-2" placeholder="{{ __('app.notes') }}">
        <button class="btn btn-primary">{{ __('app.open_session') }}</button>
    </form>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="cash-table" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                    <thead><tr><th>{{ __('app.user') }}</th><th>{{ __('app.opening_balance') }} ({{ $currencySymbol }})</th><th>Total Penjualan</th><th>Dibayar</th><th>Piutang</th><th>Ekspektasi Kas</th><th>{{ __('app.closing_balance') }} ({{ $currencySymbol }})</th><th>Selisih</th><th>{{ __('app.opened_at') }}</th><th>{{ __('app.closed_at') }}</th><th>{{ __('app.status') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
                    <tbody>
                        @foreach($sessions as $s)
                        <tr>
                            <td>{{ $s->user->name ?? '-' }}</td>
                            <td>{{ $formatAmount($s->opening_balance) }}</td>
                            <td>{{ $formatAmount($s->sales_total ?? 0) }}</td>
                            <td>{{ $formatAmount($s->paid_total ?? 0) }}</td>
                            <td>{{ $formatAmount($s->unpaid_total ?? 0) }}</td>
                            <td>{{ $formatAmount($s->expected_cash ?? 0) }}</td>
                            <td>{{ $s->closing_balance !== null ? $formatAmount($s->closing_balance) : '-' }}</td>
                            <td>
                                @if($s->diff_cash !== null)
                                    <span class="{{ $s->diff_cash == 0 ? 'text-success' : 'text-danger' }}">{{ $formatAmount($s->diff_cash) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $s->opened_at }}</td>
                            <td>{{ $s->closed_at }}</td>
                            <td>{{ $s->status }}</td>
                            <td>
                                @if($s->status === 'open')
                                <form method="POST" action="{{ route('cash-sessions.close',$s) }}" class="form-inline">
                                    @csrf
                                    <div class="input-group input-group-sm mr-2">
                                        <div class="input-group-prepend"><span class="input-group-text">{{ $currencySymbol }}</span></div>
                                        <input type="text" class="form-control form-control-sm money-input" data-target="#closing_balance_{{ $s->id }}" placeholder="{{ __('app.closing_balance') }}" required>
                                        <input type="hidden" name="closing_balance" id="closing_balance_{{ $s->id }}" value="">
                                    </div>
                                    <input name="note" class="form-control form-control-sm mr-2" placeholder="{{ __('app.notes') }}">
                                    <button class="btn btn-sm btn-success">{{ __('app.close_session') }}</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @if($sessions->isEmpty())
                        <tr><td colspan="7">{{ __('app.table_empty') }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{ $sessions->links() }}
</div>
@endsection

@push('page-js')
<script>
    $(document).ready(function() {
        // Simple DataTable for local sorting/search on current page only
        $('#cash-table').DataTable({
            paging: false,
            info: false
        });

        // Basic money input sync + formatting
        const decimal = '{{ $currencyDecimal }}' || ',';
        const thousand = '{{ $currencyThousand }}' || '.';
        const formatter = new Intl.NumberFormat('id-ID');

        function parseMoney(str){
            if(!str) return 0;
            const clean = str.replace(new RegExp('\\' + thousand, 'g'), '').replace(new RegExp('\\' + decimal), '.');
            const num = parseFloat(clean);
            return isNaN(num) ? 0 : num;
        }
        function formatMoney(num){
            return formatter.format(num);
        }

        document.querySelectorAll('.money-input').forEach(function(inp){
            const targetSel = inp.dataset.target;
            const target = document.querySelector(targetSel);
            const sync = ()=>{
                const val = parseMoney(inp.value);
                if(target){ target.value = val.toFixed(2); }
                inp.value = formatMoney(val);
            };
            inp.addEventListener('blur', sync);
            inp.addEventListener('change', sync);
            inp.addEventListener('focus', function(){
                const val = parseMoney(inp.value);
                inp.setSelectionRange(0, inp.value.length);
                if(target){ target.value = val.toFixed(2); }
            });
        });
    });
</script> 
@endpush
