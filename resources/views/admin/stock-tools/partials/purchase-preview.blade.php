<div>
    <div class="d-flex justify-content-between">
        <div>
            <div class="text-muted small">Produk</div>
            <div class="h6 mb-1">{{ $purchase->product }}</div>
            <div class="text-muted small">Batch: {{ $purchase->batch_no ?? '-' }}</div>
            <div class="text-muted small">Supplier: {{ $purchase->supplier->name ?? '-' }}</div>
        </div>
        <div class="text-right">
            <div class="text-muted small">Stok</div>
            <div class="h6 mb-0">{{ $purchase->quantity }}</div>
            <div class="text-muted small">Rak: {{ $purchase->rack_location ?? '-' }}</div>
        </div>
    </div>
    <hr>
    <div class="text-muted small">Harga beli</div>
    <div>{{ number_format($purchase->cost_price,2,',','.') }}</div>
    @if($purchase->expiry_date)<div class="text-muted small mt-1">Exp: {{ $purchase->expiry_date }}</div>@endif
</div>
