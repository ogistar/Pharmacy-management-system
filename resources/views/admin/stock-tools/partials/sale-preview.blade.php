@php
    $inv = $saleItem->invoice;
@endphp
<div>
    <div class="d-flex justify-content-between">
        <div>
            <div class="text-muted small">Invoice</div>
            <div class="h6 mb-1">{{ $inv->invoice_no ?? '-' }}</div>
            <div class="text-muted small">Pasien: {{ $inv->patient->name ?? '-' }}</div>
        </div>
        <div class="text-right">
            <div class="text-muted small">Tanggal</div>
            <div>{{ $inv->created_at ?? '-' }}</div>
        </div>
    </div>
    <hr>
    <div class="text-muted small mb-1">Item</div>
    <div><strong>{{ $saleItem->purchase->product ?? 'Item #'.$saleItem->id }}</strong></div>
    <div>Batch: {{ $saleItem->purchase->batch_no ?? '-' }} | Qty terjual: {{ $saleItem->quantity }}</div>
    <div>Harga/unit: {{ number_format($saleItem->unit_price,2,',','.') }}</div>
</div>
