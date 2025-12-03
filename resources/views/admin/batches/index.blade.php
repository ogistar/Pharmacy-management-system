@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="text-uppercase text-muted small">{{ __('menu.batches') }}</div>
            <h3 class="mb-1">Batch &amp; FEFO</h3>
            <p class="text-muted mb-0">Pantau expiry alert, rekomendasi picking FEFO, dan lokasi rak per batch/lot.</p>
        </div>
        <div class="pill">
            <span class="fe fe-layers"></span>
            <span>{{ $batches->count() }} batch</span>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card glass-card p-3">
                <div class="text-uppercase text-muted small">Expired</div>
                <div class="h4 mb-0 text-danger">{{ $expiredCount }}</div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card glass-card p-3">
                <div class="text-uppercase text-muted small">Expiring &lt;= 30 hari</div>
                <div class="h4 mb-0 text-warning">{{ $nearExpiryCount }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card glass-card p-3">
                <div class="text-uppercase text-muted small">FEFO picks</div>
                <div class="h4 mb-0 text-info">{{ $batches->where('fefo_pick', true)->count() }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-center mb-0">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Batch/Lot</th>
                            <th>Qty</th>
                            <th>Expiry</th>
                            <th>Sisa Hari</th>
                            <th>Status</th>
                            <th>Lokasi Rak</th>
                            <th>Supplier</th>
                            <th>Kategori</th>
                            <th>FEFO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                        @php
                            $badgeClass = 'badge-secondary';
                            $statusLabel = 'Unknown';
                            if ($batch->status === 'expired') {
                                $badgeClass = 'badge-danger';
                                $statusLabel = 'Expired';
                            } elseif ($batch->status === 'near') {
                                $badgeClass = 'badge-warning';
                                $statusLabel = 'Expiring soon';
                            } elseif ($batch->status === 'ok') {
                                $badgeClass = 'badge-success';
                                $statusLabel = 'OK';
                            }
                        @endphp
                        <tr>
                            <td>{{ $batch->product }}</td>
                            <td>{{ $batch->batch_no ?? '-' }}</td>
                            <td>{{ $batch->quantity }}</td>
                            <td>{{ $batch->expiry_at ? $batch->expiry_at->format('d M Y') : '-' }}</td>
                            <td>
                                @if(!is_null($batch->days_left))
                                    {{ $batch->days_left }} hari
                                @else
                                    -
                                @endif
                            </td>
                            <td><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                            <td>{{ $batch->rack_location ?? '-' }}</td>
                            <td>{{ $batch->supplier->name ?? '-' }}</td>
                            <td>{{ $batch->category->name ?? '-' }}</td>
                            <td>
                                @if($batch->fefo_pick)
                                <span class="badge badge-info">Pick first</span>
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10">{{ __('app.table_empty') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
