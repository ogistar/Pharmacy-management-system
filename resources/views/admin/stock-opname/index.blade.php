@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="text-uppercase text-muted small">{{ __('menu.stock_opname') }}</div>
            <h3 class="mb-1">Stock Opname &amp; Transfer Rak</h3>
            <p class="text-muted mb-0">Catat audit stok fisik, perpindahan rak/batch, dan pantau log pergerakan stok.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6" id="transfer">
            <div class="card glass-card">
                <div class="card-body">
                    <h6 class="mb-2">Stock Opname</h6>
                    <form method="POST" action="{{ route('stock-opnames.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Batch / Produk</label>
                            <select class="select2 form-control" name="purchase_id" required>
                                <option value="">Pilih batch</option>
                                @foreach($purchases as $p)
                                    <option value="{{ $p->id }}">
                                        {{ $p->product }} | Batch: {{ $p->batch_no ?? '-' }} | Exp: {{ $p->expiry_date ?? '-' }} | Stok: {{ $p->quantity }} | Rak: {{ $p->rack_location ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Hasil hitung fisik</label>
                            <input type="number" min="0" class="form-control" name="counted_quantity" placeholder="Qty fisik" required>
                        </div>
                        <div class="form-group">
                            <label>Catatan</label>
                            <input type="text" class="form-control" name="note" placeholder="Opsional">
                        </div>
                        <button class="btn btn-primary">Simpan Opname</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card glass-card">
                <div class="card-body">
                    <h6 class="mb-2">Transfer Rak/Batch</h6>
                    <form method="POST" action="{{ route('stock-transfers.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Batch / Produk</label>
                            <select class="select2 form-control" name="purchase_id" required>
                                <option value="">Pilih batch</option>
                                @foreach($purchases as $p)
                                    <option value="{{ $p->id }}">
                                        {{ $p->product }} | Batch: {{ $p->batch_no ?? '-' }} | Rak: {{ $p->rack_location ?? '-' }} | Stok: {{ $p->quantity }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Rak tujuan</label>
                            <input type="text" class="form-control" name="to_rack" placeholder="Contoh: Rak B1 - Lajur 2" required>
                        </div>
                        <div class="form-group">
                            <label>Catatan</label>
                            <input type="text" class="form-control" name="note" placeholder="Opsional">
                        </div>
                        <button class="btn btn-info text-white">Simpan Transfer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Riwayat Stock Opname (30 terbaru)</h6>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Batch</th>
                                    <th>Sebelum</th>
                                    <th>Fisik</th>
                                    <th>Delta</th>
                                    <th>User</th>
                                    <th>Catatan</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($opnames as $o)
                                <tr>
                                    <td>{{ $o->purchase->product ?? '-' }}</td>
                                    <td>{{ $o->purchase->batch_no ?? '-' }}</td>
                                    <td>{{ $o->system_quantity }}</td>
                                    <td>{{ $o->counted_quantity }}</td>
                                    <td class="{{ $o->delta < 0 ? 'text-danger' : ($o->delta > 0 ? 'text-success' : '') }}">{{ $o->delta }}</td>
                                    <td>{{ $o->user->name ?? '-' }}</td>
                                    <td>{{ $o->note ?? '-' }}</td>
                                    <td>{{ $o->created_at }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="8">{{ __('app.table_empty') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Riwayat Transfer Rak (30 terbaru)</h6>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Batch</th>
                                    <th>Dari</th>
                                    <th>Ke</th>
                                    <th>Qty</th>
                                    <th>User</th>
                                    <th>Catatan</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transfers as $t)
                                <tr>
                                    <td>{{ $t->purchase->product ?? '-' }}</td>
                                    <td>{{ $t->purchase->batch_no ?? '-' }}</td>
                                    <td>{{ $t->from_rack ?? '-' }}</td>
                                    <td>{{ $t->to_rack }}</td>
                                    <td>{{ $t->quantity_snapshot }}</td>
                                    <td>{{ $t->user->name ?? '-' }}</td>
                                    <td>{{ $t->note ?? '-' }}</td>
                                    <td>{{ $t->created_at }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="8">{{ __('app.table_empty') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Log Stok (30 terbaru)</h6>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Jenis</th>
                                    <th>Qty</th>
                                    <th>Produk / Batch</th>
                                    <th>User</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movements as $m)
                                <tr>
                                    <td>{{ $m->created_at }}</td>
                                    <td>{{ $m->type }}</td>
                                    <td>{{ $m->quantity }}</td>
                                    <td>
                                        {{ $m->purchase->product ?? '-' }}
                                        @if(!empty($m->purchase->batch_no))
                                            <div class="text-muted small">Batch: {{ $m->purchase->batch_no }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $m->user->name ?? '-' }}</td>
                                    <td>{{ $m->note ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6">{{ __('app.table_empty') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted small mb-0 mt-2">Untuk filter lengkap, buka laporan Log Stok.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
