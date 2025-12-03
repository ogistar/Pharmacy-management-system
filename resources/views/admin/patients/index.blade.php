@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    
@endpush

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="text-uppercase text-muted small">{{ __('menu.patients') }}</div>
            <h3 class="mb-1">{{ __('app.patients') }}</h3>
            <p class="text-muted mb-0">{{ __('app.search') }} &amp; {{ __('app.add') }} pasien/pelanggan.</p>
        </div>
        <div class="pill">
            <span class="fe fe-user-check"></span>
            <span>{{ $patients->total() }} {{ __('app.patients') }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('patients.store') }}" class="mb-3 glass-card p-3">
        @csrf
        <div class="row">
            <div class="col-md-3"><input name="name" class="form-control" placeholder="{{ __('app.name') }}" required></div>
            <div class="col-md-2"><input name="phone" class="form-control" placeholder="{{ __('app.phone') }}"></div>
            <div class="col-md-2"><input name="email" class="form-control" placeholder="{{ __('app.email') }}"></div>
            <div class="col-md-2"><input type="date" name="dob" class="form-control" placeholder="{{ __('app.dob') }}"></div>
            <div class="col-md-3"><input name="address" class="form-control" placeholder="{{ __('app.address') }}"></div>
        </div>
        <div class="row mt-2">
            <div class="col-md-9"><input name="notes" class="form-control" placeholder="{{ __('app.notes') }}"></div>
            <div class="col-md-3"><button class="btn btn-primary btn-block">{{ __('app.add_patient') }}</button></div>
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="patient-table" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                    <thead><tr><th>{{ __('app.patients') }}</th><th>{{ __('app.contact') }}</th><th>Tanggal Lahir</th><th>{{ __('app.address') }}</th><th>{{ __('app.notes') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
                    <tbody>
                        @foreach($patients as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td>{{ $p->phone }}<br>{{ $p->email }}</td>
                            <td>{{ $p->dob ?? '-' }}</td>
                            <td>{{ $p->address }}</td>
                            <td>{{ $p->notes }}</td>
                            <td>
                                <form method="POST" action="{{ route('patients.update',$p) }}" class="d-inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="name" value="{{ $p->name }}">
                                    <input type="hidden" name="phone" value="{{ $p->phone }}">
                                    <input type="hidden" name="email" value="{{ $p->email }}">
                                    <input type="hidden" name="dob" value="{{ $p->dob }}">
                                    <input type="hidden" name="address" value="{{ $p->address }}">
                                    <input type="hidden" name="notes" value="{{ $p->notes }}">
                                    <button class="btn btn-sm btn-outline-primary">{{ __('app.update') }}</button>
                                </form>
                                <form method="POST" action="{{ route('patients.destroy',$p) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus pasien?')">{{ __('app.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        @if($patients->isEmpty())
                        <tr><td colspan="5">{{ __('app.table_empty') }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    {{ $patients->links() }}
</div>
@endsection

@push('page-js')
<script>
    $(document).ready(function() {
        // Simple DataTable for local sorting/search on current page only
        $('#patient-table').DataTable({
            paging: false,
            info: false
        });
    });
</script> 
@endpush
