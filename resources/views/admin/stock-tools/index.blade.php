@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h4>Retur &amp; Penyesuaian Stok</h4>

    <div class="row">
        <div class="col-md-4">
            <h6>Retur Penjualan</h6>
            <form method="POST" action="{{ route('sale-returns.store') }}" class="glass-card card p-3">
                @csrf
                <select name="sale_item_id" id="sale_item_id" class="form-control mb-2 select2" data-placeholder="Cari item/invoice" required></select>
                <input name="quantity" type="number" min="1" class="form-control mb-2" placeholder="Qty" required>
                <input name="reason" class="form-control mb-2" placeholder="Alasan">
                <button type="button" class="btn btn-link p-0 mb-2" id="preview-sale-btn">Preview faktur</button>
                <button class="btn btn-primary btn-block">Catat Retur</button>
            </form>
        </div>
        <div class="col-md-4">
            <h6>Retur Pembelian</h6>
            <form method="POST" action="{{ route('purchase-returns.store') }}">
                @csrf
                <select name="purchase_id" id="purchase_id" class="form-control mb-2 select2" data-placeholder="Cari batch/produk" required></select>
                <input name="quantity" type="number" min="1" class="form-control mb-2" placeholder="Qty" required>
                <input name="reason" class="form-control mb-2" placeholder="Alasan">
                <button type="button" class="btn btn-link p-0 mb-2" id="preview-purchase-btn">Preview pembelian</button>
                <button class="btn btn-warning btn-block">Kembalikan ke Supplier</button>
            </form>
        </div>
        <div class="col-md-4">
            <h6>Penyesuaian Stok</h6>
            <form method="POST" action="{{ route('stock-adjustments.store') }}">
                @csrf
                <select name="purchase_id" id="adjust_purchase_id" class="form-control mb-2 select2" data-placeholder="Cari batch/produk" required></select>
                <input name="delta" type="number" class="form-control mb-2" placeholder="(+/-) Qty" required>
                <input name="reason" class="form-control mb-2" placeholder="Alasan">
                <button class="btn btn-info btn-block">Sesuaikan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@include('admin.stock-tools.preview-modal')

@push('page-js')
<script>
$(document).ready(function(){
    function initPurchaseSelect($el){
        $el.select2({
            placeholder: $el.data('placeholder'),
            width: '100%',
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: "{{ route('purchases.search') }}",
                dataType: 'json',
                delay: 150,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                data: params => ({ q: params.term }),
                processResults: data => ({ results: data })
            }
        });
    }
    initPurchaseSelect($('#purchase_id'));
    initPurchaseSelect($('#adjust_purchase_id'));

    $('#sale_item_id').select2({
        placeholder: $('#sale_item_id').data('placeholder'),
        width: '100%',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: "{{ route('sale-items.search') }}",
            dataType: 'json',
            delay: 150,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            data: params => ({ q: params.term }),
            processResults: data => ({
                results: data.map(it => ({
                    id: it.id,
                    text: it.text,
                    max_qty: it.max_qty
                }))
            })
        }
    }).on('select2:select', function(e){
        const max = e.params.data.max_qty || 1;
        $(this).closest('form').find('input[name="quantity"]').attr('max', max).val(max);
    });

    // Preview faktur dari sale item
    $('#preview-sale-btn').on('click', async function(){
        const itemId = $('#sale_item_id').val();
        if(!itemId){ alert('Pilih item terlebih dahulu'); return; }
        $('#previewTitle').text('Preview Faktur POS');
        $('#previewBody').html('<div class="text-muted">Memuat...</div>');
        $('#previewModal').modal('show');
        try{
            const res = await fetch("{{ url('sale-items') }}/"+itemId+"/preview", { headers:{'X-Requested-With':'XMLHttpRequest'} });
            if(!res.ok) throw new Error('Gagal memuat faktur');
            const html = await res.text();
            $('#previewBody').html(html);
        }catch(err){
            $('#previewBody').html('<div class="text-danger">'+err.message+'</div>');
        }
    });

    // Preview pembelian dari batch
    $('#preview-purchase-btn').on('click', async function(){
        const purchaseId = $('#purchase_id').val();
        if(!purchaseId){ alert('Pilih batch terlebih dahulu'); return; }
        $('#previewTitle').text('Preview Pembelian');
        $('#previewBody').html('<div class="text-muted">Memuat...</div>');
        $('#previewModal').modal('show');
        try{
            const res = await fetch("{{ url('purchases') }}/"+purchaseId+"/preview", { headers:{'X-Requested-With':'XMLHttpRequest'} });
            if(!res.ok) throw new Error('Gagal memuat pembelian');
            const html = await res.text();
            $('#previewBody').html(html);
        }catch(err){
            $('#previewBody').html('<div class="text-danger">'+err.message+'</div>');
        }
    });
});
</script>
@endpush
