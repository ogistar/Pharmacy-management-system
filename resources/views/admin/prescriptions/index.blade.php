@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
<style>
    .rx-row {
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 14px 12px;
        background: linear-gradient(135deg, rgba(14,165,233,0.06), rgba(34,197,94,0.03));
    }
    .rx-row + .rx-row { margin-top: 10px; }
    .rx-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 12px;
        background: var(--surface);
        border: 1px solid var(--border);
        font-size: 13px;
        white-space: nowrap;
    }
    .rx-pill input { margin-right: 6px; }
    .rx-options {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        align-items: center;
        gap: 8px;
    }
    .rx-components {
        background: rgba(14,165,233,0.05);
        border: 1px dashed var(--border);
        border-radius: 10px;
        padding: 12px;
    }
    .rx-meta {
        color: var(--muted);
        font-size: 12px;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="text-uppercase text-muted small">{{ __('menu.prescriptions') }}</div>
            <h3 class="mb-1">Resep</h3>
            <p class="text-muted mb-0">Catat resep dokter, tandai racikan/obat terbatas, lalu kirim ke POS.</p>
        </div>
        <div class="pill">
            <span class="fe fe-activity"></span>
            <span>{{ $prescriptions->total() }} resep</span>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="mb-3">Tambah Resep</h6>
            <form id="prescription-form" method="POST" action="{{ route('prescriptions.store') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Pasien</label>
                        <input type="hidden" name="patient_id" id="patient_id">
                        <input type="hidden" name="patient_name" id="patient_name">
                        <input type="hidden" name="patient_phone" id="patient_phone">
                        <input type="hidden" name="patient_dob" id="patient_dob">
                        <select class="form-control patient-name-search mb-1" placeholder="Nama pasien (cari/add)"></select>
                        <select class="form-control patient-phone-search" placeholder="No. telepon (cari/add)"></select>
                        <input type="date" class="form-control mt-2" id="patient_dob_display" placeholder="Tanggal lahir" readonly>
                        <small class="text-muted">Cari nama atau nomor telepon. Klik tambah jika tidak ditemukan. Tanggal lahir hanya-baca.</small>
                        <button type="button" class="btn btn-link p-0 mt-1" id="add-patient-btn">+ Tambah pasien</button>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Dokter</label>
                        <input name="doctor_name" class="form-control" placeholder="Nama dokter (opsional)">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Diagnosis</label>
                        <input name="diagnosis" class="form-control" placeholder="Diagnosis/keluhan">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Waktu Resep</label>
                        <input type="datetime-local" name="prescribed_at" class="form-control" value="{{ now()->format('Y-m-d\\TH:i') }}">
                    </div>
                </div>

                <div id="item-rows" class="mt-3">
                    <div class="row align-items-end mb-2 item-row rx-row">
                        <div class="col-md-4">
                            <label class="d-flex justify-content-between align-items-center">
                                <span>Nama racikan/obat</span>
                            </label>
                            <input class="form-control product-name" name="items[0][product_name]" placeholder="Isi nama racikan atau pilih stok">
                        </div>
                        <div class="col-md-2">
                            <label>Qty</label>
                            <input class="form-control" type="number" min="1" name="items[0][quantity]" value="1">
                        </div>
                        <div class="col-md-2">
                            <label>Template racikan</label>
                            <select class="form-control compound-select" name="items[0][compound_id]">
                                <option value="">(Opsional)</option>
                                @foreach($compounds as $c)
                                <option value="{{ $c->id }}" data-items='{{ $c->items->map(function($i){ return ["id"=>$i->product_id,"name"=>$i->product->purchase->product ?? "Produk #".$i->product_id,"qty"=>$i->quantity,"is_full_pack"=>false]; })->values()->toJson() }}' data-fee="{{ $c->service_fee }}" data-markup="{{ $c->markup_percent }}" data-override="{{ $c->price_override }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted compound-preview d-block small"></small>
                        </div>
                        <div class="col-md-2">
                            <label>Dosis</label>
                            <input class="form-control" name="items[0][dosage]" placeholder="Aturan pakai">
                        </div>
                        <div class="col-md-2">
                            <label class="mb-1">Opsi</label>
                            <div class="rx-options">
                                <label class="mb-0 rx-pill"><input type="checkbox" name="items[0][is_compound]" value="1" class="is-compound"> Racik</label>
                                <label class="mb-0 rx-pill"><input type="checkbox" name="items[0][is_controlled]" value="1" class="is-controlled"> Obat terbatas</label>
                            </div>
                        </div>
                        <div class="col-md-12 mt-2">
                            <input class="form-control" name="items[0][compound_note]" placeholder="Catatan racikan (opsional)">
                        </div>
                        <div class="col-md-12 mt-3 components-container rx-components" style="display:none;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="font-weight-bold">Komponen racikan</div>
                                <button type="button" class="btn btn-sm btn-outline-secondary add-component">Tambah komponen</button>
                            </div>
                            <div class="components-list">
                                <div class="form-row component-row mb-2">
                                    <div class="col-md-6">
                                        <select class="form-control component-search" data-name-prefix="items[0][components][0]">
                                            <option value="">Cari obat</option>
                                        </select>
                                        <input type="hidden" name="items[0][components][0][product_id]" value="">
                                        <input class="form-control form-control-sm mt-1 component-name" name="items[0][components][0][product_name]" placeholder="Nama komponen (auto/opsional)" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <input class="form-control" type="number" min="1" name="items[0][components][0][quantity]" value="1">
                                        <div class="d-flex align-items-center mt-1 pack-toggle" style="gap:10px;">
                                            <label class="mb-0"><input type="radio" name="items[0][components][0][is_full_pack]" value="0" checked> Satuan</label>
                                            <label class="mb-0"><input type="radio" name="items[0][components][0][is_full_pack]" value="1"> Full pack</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-center">
                                        <button type="button" class="btn btn-sm btn-link text-danger remove-component">&times;</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 text-right mt-2">
                            <button type="button" class="btn btn-sm btn-link text-danger remove-row">&times; Hapus</button>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="add-item">Tambah item</button>
                    <button class="btn btn-primary">{{ __('app.save_prescription') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="prescriptions-table" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                    <thead><tr><th>{{ __('app.patients') }}</th><th>{{ __('app.doctor') }}</th><th>Items</th><th>{{ __('app.diagnosis') }}</th><th>{{ __('app.status') }}</th><th>Waktu</th><th>{{ __('app.actions') }}</th></tr></thead>
                    <tbody>
                        @foreach($prescriptions as $rx)
                        <tr>
                            <td>
                                <div>{{ $rx->patient->name ?? '-' }}</div>
                                <div class="text-muted small">DOB: {{ $rx->patient->dob ?? '-' }}</div>
                                <div class="text-muted small">HP: {{ $rx->patient->phone ?? '-' }}</div>
                            </td>
                            <td>{{ $rx->doctor_name }}</td>
                            <td>
                                @forelse($rx->items as $it)
                                    <div class="pos-meta" style="margin-bottom:4px;">
                                        <strong>{{ $it->product_name }}</strong> x{{ $it->quantity }}
                                        @if($it->is_compound)<span class="badge badge-info ml-1">Racik</span>@endif
                                        @if($it->is_controlled)<span class="badge badge-danger ml-1">Obat terbatas</span>@endif
                                        @if($it->dosage)<div class="text-muted small">Dosis: {{ $it->dosage }}</div>@endif
                                        @if($it->compound_note)<div class="text-muted small">Racik: {{ $it->compound_note }}</div>@endif
                                        @if($it->label_note)<div class="text-muted small">Label: {{ $it->label_note }}</div>@endif
                                    </div>
                                @empty
                                    <span class="text-muted">-</span>
                                @endforelse
                            </td>
                            <td>
                                {{ $rx->diagnosis }}
                                @if($rx->items->where('is_controlled', true)->count())
                                    <span class="badge badge-danger ml-1">Obat terbatas</span>
                                @endif
                                @if($rx->items->where('is_compound', true)->count())
                                    <span class="badge badge-info ml-1">Racikan</span>
                                @endif
                            </td>
                            <td>{{ ucfirst($rx->status) }}</td>
                            <td>{{ $rx->prescribed_at }}</td>
                            <td>
                                <div class="btn-group mb-1">
                                    <form method="POST" action="{{ route('prescriptions.approve',$rx) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-primary" {{ $rx->status === 'approved' || $rx->status === 'dispensed' ? 'disabled' : '' }}>Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('prescriptions.dispense',$rx) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success" {{ $rx->status === 'dispensed' ? 'disabled' : '' }}>Dispense</button>
                                    </form>
                                </div>
                                <a class="btn btn-sm btn-outline-info mb-1" href="{{ route('pos.index', ['rx' => $rx->id]) }}">Kirim ke POS</a>
                                <form method="POST" action="{{ route('prescriptions.destroy',$rx) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus resep?')">{{ __('app.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        @if($prescriptions->isEmpty())
                        <tr><td colspan="7">{{ __('app.table_empty') }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{ $prescriptions->links() }}
</div>
@endsection

@push('page-js')
<script>
    $(document).ready(function() {
        $('#prescriptions-table').DataTable({
            paging: false,
            info: false
        });

        const productSearchUrl = "{{ route('pos.products') }}";
        const patientSearchUrl = "{{ route('patients.search') }}";
        const $patientDob = $('#patient_dob');
        const $patientDobDisplay = $('#patient_dob_display');

        function componentRowTemplate(itemIdx, compIdx, data = {}) {
            const qtyVal = data.qty ?? 1;
            const productName = data.name ?? '';
            const productId = data.id ?? data.product_id ?? '';
            const isFullPack = data.is_full_pack ? '1' : '0';
            const unitChecked = isFullPack === '1' ? '' : 'checked';
            const packChecked = isFullPack === '1' ? 'checked' : '';
            const optionLabel = productName ? productName : 'Cari obat';
            const selectedOption = productId ? `<option value="${productId}" selected>${optionLabel}</option>` : '<option value="">Cari obat</option>';
            return `
                <div class="form-row component-row mb-2">
                    <div class="col-md-6">
                        <select class="form-control component-search" data-name-prefix="items[${itemIdx}][components][${compIdx}]">
                            ${selectedOption}
                        </select>
                        <input type="hidden" name="items[${itemIdx}][components][${compIdx}][product_id]" value="${productId}">
                        <input class="form-control form-control-sm mt-1 component-name" name="items[${itemIdx}][components][${compIdx}][product_name]" placeholder="Nama komponen (auto/opsional)" value="${productName}" readonly>
                    </div>
                    <div class="col-md-3">
                        <input class="form-control" type="number" min="1" name="items[${itemIdx}][components][${compIdx}][quantity]" value="${qtyVal}">
                        <div class="d-flex align-items-center mt-1 pack-toggle" style="gap:10px;">
                            <label class="mb-0"><input type="radio" name="items[${itemIdx}][components][${compIdx}][is_full_pack]" value="0" ${unitChecked}> Satuan</label>
                            <label class="mb-0"><input type="radio" name="items[${itemIdx}][components][${compIdx}][is_full_pack]" value="1" ${packChecked}> Full pack</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-center">
                        <button type="button" class="btn btn-sm btn-link text-danger remove-component">&times;</button>
                    </div>
                </div>
            `;
        }

        function itemRowTemplate(idx) {
            const namePrefix = `items[${idx}]`;
            return `
                <div class="row align-items-end mb-2 item-row rx-row">
                    <div class="col-md-4">
                        <label class="d-flex justify-content-between align-items-center">
                            <span>Nama racikan/obat</span>
                        </label>
                        <input class="form-control product-name" name="items[${idx}][product_name]" placeholder="Isi nama racikan atau pilih stok">
                    </div>
                    <div class="col-md-2">
                        <label>Qty</label>
                        <input class="form-control" type="number" min="1" name="items[${idx}][quantity]" value="1">
                    </div>
                    <div class="col-md-2">
                        <label>Template racikan</label>
                        <select class="form-control compound-select" name="items[${idx}][compound_id]">
                            <option value="">(Opsional)</option>
                            @foreach($compounds as $c)
                            <option value="{{ $c->id }}" data-items='{{ $c->items->map(function($i){ return ["id"=>$i->product_id,"name"=>$i->product->purchase->product ?? "Produk #".$i->product_id,"qty"=>$i->quantity,"is_full_pack"=>false]; })->values()->toJson() }}' data-fee="{{ $c->service_fee }}" data-markup="{{ $c->markup_percent }}" data-override="{{ $c->price_override }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted compound-preview d-block small"></small>
                    </div>
                    <div class="col-md-2">
                        <label>Dosis</label>
                        <input class="form-control" name="items[${idx}][dosage]" placeholder="Aturan pakai">
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1">Opsi</label>
                        <div class="rx-options">
                            <label class="mb-0 rx-pill"><input type="checkbox" name="items[${idx}][is_compound]" value="1" class="is-compound"> Racik</label>
                            <label class="mb-0 rx-pill"><input type="checkbox" name="items[${idx}][is_controlled]" value="1" class="is-controlled"> Obat terbatas</label>
                        </div>
                    </div>
                    <div class="col-md-12 mt-2">
                        <input class="form-control" name="items[${idx}][compound_note]" placeholder="Catatan racikan (opsional)">
                    </div>
                    <div class="col-md-12 mt-3 components-container rx-components" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="font-weight-bold">Komponen racikan</div>
                            <button type="button" class="btn btn-sm btn-outline-secondary add-component">Tambah komponen</button>
                        </div>
                        <div class="components-list">
                            ${componentRowTemplate(idx, 0)}
                        </div>
                    </div>
                    <div class="col-md-12 text-right mt-2">
                        <button type="button" class="btn btn-sm btn-link text-danger remove-row">&times; Hapus</button>
                    </div>
                </div>
            `;
        }

        function getItemIndex(itemRow){
            const nameAttr = itemRow.find('input[name^="items["]').first().attr('name') || '';
            const match = nameAttr.match(/items\[(\d+)\]/);
            return match ? match[1] : 0;
        }
        function syncComponents(itemRow){
            const shouldShow = itemRow.find('.is-compound').prop('checked') || itemRow.find('.is-controlled').prop('checked');
            itemRow.find('.components-container').toggle(shouldShow);
        }

        let itemIndex = $('#item-rows .item-row').length;

        $('#add-item').on('click', function(){
            $('#item-rows').append(itemRowTemplate(itemIndex++));
            const lastRow = $('#item-rows .item-row').last();
            initProductSearch(lastRow.find('.product-search'));
            initComponentSearch(lastRow.find('.component-search'));
        });

        $(document).on('click','.remove-row', function(){
            $(this).closest('.item-row').remove();
        });

        $(document).on('click', '.add-component', function(){
            const itemRow = $(this).closest('.item-row');
            const componentsList = itemRow.find('.components-list');
            const compIdx = componentsList.find('.component-row').length;
            const row = $(componentRowTemplate(getItemIndex(itemRow), compIdx));
            componentsList.append(row);
            initComponentSearch(row.find('.component-search'));
        });

        $(document).on('click', '.remove-component', function(){
            $(this).closest('.component-row').remove();
        });

        $(document).on('change','.is-compound', function(){
            syncComponents($(this).closest('.item-row'));
        });
        $(document).on('change','.is-controlled', function(){
            const row = $(this).closest('.item-row');
            if(this.checked){
                row.find('.is-compound').prop('checked', true);
            }
            syncComponents(row);
        });

        $(document).on('change','.compound-select', function(){
            const opt = this.selectedOptions[0];
            const itemRow = $(this).closest('.item-row');
            const preview = $(this).closest('.col-md-2').find('.compound-preview');
            if(!opt || !opt.value){
                preview.text('');
                syncComponents(itemRow);
                return;
            }
            const items = opt.dataset.items ? JSON.parse(opt.dataset.items) : [];
            const fee = opt.dataset.fee || 0;
            const markup = opt.dataset.markup || 0;
            const override = opt.dataset.override || '';
            const listText = items.map(it=>`${it.name} (${it.qty})`).join(', ');
            const priceInfo = override ? `Override: ${override}` : `Fee: ${fee}, Markup: ${markup}%`;
            preview.text(listText ? `${listText} | ${priceInfo}` : priceInfo);

            const nameInput = itemRow.find('input[name*="product_name"]');
            if(nameInput && !nameInput.val()){
                nameInput.val(opt.textContent);
            }

            const componentsContainer = itemRow.find('.components-container');
            const componentsList = componentsContainer.find('.components-list');
            componentsList.empty();

            if(items.length){
                const itemIdx = getItemIndex(itemRow);
                items.forEach((comp, i)=>{
                    const row = $(componentRowTemplate(itemIdx, i, { qty: comp.qty ?? 1, name: comp.name ?? '', id: comp.id ?? '', is_full_pack: comp.is_full_pack ?? false }));
                    componentsList.append(row);
                    initComponentSearch(row.find('.component-search'));
                });
                itemRow.find('.is-compound').prop('checked', true);
                syncComponents(itemRow);
            }
        });

        function initProductSearch($el){
            if(!$el.length) return;
            $el.select2({
                placeholder: 'Cari obat',
                width: '100%',
                ajax: {
                    url: productSearchUrl,
                    dataType: 'json',
                    delay: 150,
                    data: params => ({ q: params.term }),
                    processResults: data => ({
                        results: data.map(it => ({
                            id: it.purchase_id,
                            product_id: it.product_id,
                            text: `${it.product} | Batch ${it.batch_no ?? '-'} | Stok ${it.quantity}`
                        }))
                    })
                }
            }).on('select2:select', function(e){
                const data = e.params.data;
                const prefix = $(this).data('namePrefix');
                const row = $(this).closest('.item-row');
                row.find(`input[name="${prefix}[product_id]"]`).val(data.product_id || '');
                row.find(`input[name="${prefix}[product_name]"]`).val(data.text.split('|')[0].trim());
            });
        }

        function initComponentSearch($el){
            if(!$el.length) return;
            $el.select2({
                placeholder: 'Cari obat',
                width: '100%',
                ajax: {
                    url: productSearchUrl,
                    dataType: 'json',
                    delay: 150,
                    data: params => ({ q: params.term }),
                    processResults: data => ({
                        results: data.map(it => ({
                            id: it.purchase_id,
                            product_id: it.product_id,
                            text: `${it.product} | Batch ${it.batch_no ?? '-'} | Stok ${it.quantity}`
                        }))
                    })
                }
            }).on('select2:select', function(e){
                const data = e.params.data;
                const prefix = $(this).data('namePrefix');
                const row = $(this).closest('.component-row');
                row.find(`input[name="${prefix}[product_id]"]`).val(data.product_id || '');
                row.find(`input[name="${prefix}[product_name]"]`).val(data.text.split('|')[0].trim());
            });
        }

        initProductSearch($('.product-search'));
        initComponentSearch($('.component-search'));
        function initPatientSearch($el, mode){
            $el.select2({
                placeholder: mode === 'phone' ? 'Cari no. telepon' : 'Cari nama pasien',
                width: '100%',
                minimumInputLength: 1,
                allowClear: true,
                ajax: {
                    url: patientSearchUrl,
                    dataType: 'json',
                    delay: 150,
                    data: params => ({ q: params.term, mode }),
                    processResults: data => ({
                        results: data.map(p => ({
                            id: p.id,
                            text: mode === 'phone'
                                ? (p.phone || '-')
                                : (p.name || '-'),
                            phone: p.phone,
                            name: p.name,
                            dob: p.dob
                        }))
                    })
                }
            }).on('select2:select', function(e){
                const data = e.params.data;
                $('#patient_id').val(data.id);
                $('#patient_name').val(data.name || data.text || '');
                $('#patient_phone').val(data.phone || '');
                $patientDob.val(data.dob || '');
                $patientDobDisplay.val(data.dob || '');
                // sync both dropdowns to selected option so user sees value
                if (!$('.patient-name-search').find(`option[value="${data.id}"]`).length) {
                    const optName = new Option(data.name || data.text || '', data.id, true, true);
                    optName.dataset.dob = data.dob || '';
                    $('.patient-name-search').append(optName);
                }
                if (!$('.patient-phone-search').find(`option[value="${data.id}"]`).length) {
                    const optPhone = new Option(data.phone || data.text || '', data.id, true, true);
                    optPhone.dataset.dob = data.dob || '';
                    $('.patient-phone-search').append(optPhone);
                }
                $('.patient-name-search').val(data.id).trigger('change.select2');
                $('.patient-phone-search').val(data.id).trigger('change.select2');
            }).on('select2:clear', function(){
                $('#patient_id').val('');
                $('#patient_name').val('');
                $('#patient_phone').val('');
                $patientDob.val('');
                $patientDobDisplay.val('');
            });
        }
        initPatientSearch($('.patient-name-search'), 'name');
        initPatientSearch($('.patient-phone-search'), 'phone');
        const $patientName = $('.patient-name-search');
        const $patientPhone = $('.patient-phone-search');
        const $patientId = $('#patient_id');
        const $patientNameVal = $('#patient_name');
        const $patientPhoneVal = $('#patient_phone');

        $('#prescription-form').on('submit', function(){
            const chosen = ($patientName.select2('data')[0]) || ($patientPhone.select2('data')[0]);
            if (chosen && chosen.id) {
                $patientId.val(chosen.id);
                $patientNameVal.val(chosen.name || '');
                $patientPhoneVal.val(chosen.phone || '');
                $patientDob.val(chosen.dob || $patientDob.val());
                $patientDobDisplay.val($patientDob.val());
            } else {
                // ensure manual typing kept if select2 cleared
                $patientNameVal.val($patientName.find('option:selected').text() || '');
                $patientPhoneVal.val($patientPhone.find('option:selected').text() || '');
            }
        });

        function isValidDate(val){
            if(!val) return true;
            return /^\d{4}-\d{2}-\d{2}$/.test(val);
        }

        async function promptAddPatient(defaultName = '', defaultPhone = '', defaultDob = ''){
            const name = window.prompt('Nama pasien baru:', defaultName);
            if(!name) return;
            const phone = window.prompt('Nomor telepon (format 62/0 tanpa spasi):', defaultPhone);
            if(!phone){ alert('Nomor telepon wajib.'); return; }
            const dob = window.prompt('Tanggal lahir (YYYY-MM-DD, opsional):', defaultDob);
            if(dob && !isValidDate(dob)){
                alert('Format tanggal lahir harus YYYY-MM-DD.');
                return;
            }
            try{
                const res = await fetch('/patients', {
                    method:'POST',
                    credentials:'same-origin',
                    headers:{
                        'Content-Type':'application/json',
                        'Accept':'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ name, phone, dob: dob || null })
                });
                if(!res.ok){
                    const txt = await res.text();
                    throw new Error(txt.substring ? txt.substring(0,300) : 'Gagal simpan pasien');
                }
                const json = await res.json();
                const newId = json.id || (json.patient ? json.patient.id : null);
                if(!newId) throw new Error(json.message || 'Gagal menambah pasien');
                $('#patient_id').val(newId);
                $patientNameVal.val(name);
                $patientPhoneVal.val(phone);
                const newDob = (json.patient ? json.patient.dob : null) || dob || '';
                $patientDob.val(newDob);
                $patientDobDisplay.val(newDob);
                // inject option to both selects to show immediately
                const optionName = new Option(name, newId, true, true);
                optionName.dataset.dob = newDob;
                $patientName.append(optionName).trigger('change');
                const optionPhone = new Option(phone, newId, true, true);
                optionPhone.dataset.dob = newDob;
                $patientPhone.append(optionPhone).trigger('change');
                alert('Pasien baru ditambahkan.');
            } catch(err){
                alert('Gagal menambah pasien: ' + err.message);
            }
        }
        $('#add-patient-btn').on('click', ()=> promptAddPatient($('.patient-name-search').val(), $('.patient-phone-search').val(), $patientDob.val()));
    });
</script>
@endpush
