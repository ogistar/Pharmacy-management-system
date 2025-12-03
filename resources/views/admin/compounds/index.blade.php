@extends('admin.layouts.app')

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
            <div class="text-uppercase text-muted small">{{ __('menu.compounds') }}</div>
            <h3 class="mb-1">Racikan</h3>
            <p class="text-muted mb-0">Buat template racikan (BOM) untuk ditarik ke POS.</p>
        </div>
        <div class="pill">
            <span class="fe fe-layers"></span>
            <span>{{ $compounds->count() }} racikan</span>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="mb-2">Tambah Racikan</h6>
            <form method="POST" action="{{ route('compounds.store') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Nama</label>
                        <input name="name" class="form-control" required placeholder="Contoh: Racikan Batuk Anak">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Jasa Racik</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">{{ AppSettings::get('app_currency_symbol','Rp') }}</span></div>
                            <input class="form-control money-input" type="text" value="" placeholder="0" data-name="service_fee">
                            <input type="hidden" name="service_fee" class="money-hidden" value="0">
                        </div>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Markup (%)</label>
                        <input name="markup_percent" class="form-control" type="number" step="0.01" value="0">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Harga override</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">{{ AppSettings::get('app_currency_symbol','Rp') }}</span></div>
                            <input class="form-control money-input" type="text" placeholder="0" data-name="price_override">
                            <input type="hidden" name="price_override" class="money-hidden" value="">
                        </div>
                    </div>
                </div>
                <div id="compound-items">
                    <div class="form-row align-items-end mb-2 compound-row">
                        <div class="form-group col-md-6">
                            <label>Obat (cari stok)</label>
                            <select name="items[0][product_id]" class="form-control product-search">
                                <option value="">Ketik nama/batch</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Qty per 1 racikan</label>
                            <input name="items[0][quantity]" class="form-control" type="number" min="1" value="1">
                        </div>
                        <div class="form-group col-md-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary add-row">Tambah baris</button>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary">Simpan racikan</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6 class="mb-3">Daftar Racikan</h6>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Komponen</th>
                            <th>Jasa ({{ $currencySymbol }})</th>
                            <th>Markup</th>
                            <th>Harga override ({{ $currencySymbol }})</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($compounds as $c)
                        <tr>
                            <td>{{ $c->name }}</td>
                            <td>
                                @foreach($c->items as $it)
                                    <div class="text-muted small">- {{ $it->product->purchase->product ?? 'Produk #'.$it->product_id }} ({{ $it->quantity }})</div>
                                @endforeach
                            </td>
                            <td>{{ $formatAmount($c->service_fee) }}</td>
                            <td>{{ $c->markup_percent }}%</td>
                            <td>{{ $c->price_override !== null ? $formatAmount($c->price_override) : '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('compounds.destroy',$c) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus racikan?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6">{{ __('app.table_empty') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
    const container = document.getElementById('compound-items');
    container.addEventListener('click', function(e){
        if(e.target.classList.contains('add-row')){
            e.preventDefault();
            const idx = container.querySelectorAll('.compound-row').length;
            const tmpl = `
            <div class="form-row align-items-end mb-2 compound-row">
                <div class="form-group col-md-6">
                    <select name="items[${idx}][product_id]" class="form-control product-search">
                        <option value="">Ketik nama/batch</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <input name="items[${idx}][quantity]" class="form-control" type="number" min="1" value="1">
                </div>
                <div class="form-group col-md-2">
                    <button type="button" class="btn btn-sm btn-link text-danger remove-row">&times;</button>
                </div>
            </div>`;
            container.insertAdjacentHTML('beforeend', tmpl);
            initProductSearch(container.querySelectorAll('.product-search'));
        } else if(e.target.classList.contains('remove-row')){
            e.preventDefault();
            e.target.closest('.compound-row').remove();
        }
    });

    const productSearchUrl = "{{ route('pos.products') }}";
    function initProductSearch(nodeList){
        nodeList.forEach(function(el){
            if (el.dataset.enhanced) return;
            $(el).select2({
                placeholder: 'Cari obat (stok)',
                width: '100%',
                ajax: {
                    url: productSearchUrl,
                    dataType: 'json',
                    delay: 150,
                    data: params => ({ q: params.term }),
                    processResults: data => ({
                        results: data.map(it => ({
                            id: it.product_id || it.purchase_id,
                            text: `${it.product} | Batch ${it.batch_no ?? '-'} | Stok ${it.quantity}`
                        }))
                    })
                }
            });
            el.dataset.enhanced = '1';
        });
    }
    initProductSearch(container.querySelectorAll('.product-search'));
</script>
@endpush
