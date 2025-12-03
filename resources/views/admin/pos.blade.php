@extends('admin.layouts.app')

@push('page-css')
<style>
    .pos-wrapper { background: var(--bg); color: var(--text); min-height: 100vh; padding: 16px; }
    .pos-panel { background: var(--card); border: 1px solid var(--border); border-radius: 16px; box-shadow: var(--shadow); padding: 16px; }
    .pos-title { text-transform: uppercase; letter-spacing: .12em; color: var(--muted); font-size: 12px; }
    .pos-highlight { font-size: 28px; font-weight: 700; }
    .pos-input { width: 100%; background: rgba(255,255,255,0.06); border: 1px solid var(--border); color: var(--text); padding: 10px 12px; border-radius: 10px; }
    .pos-input:focus { outline: none; border-color: var(--accent-2); box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent-2) 25%, transparent); }
    .pos-results { display: grid; grid-template-columns: repeat(auto-fill,minmax(220px,1fr)); gap: 10px; margin-top: 10px; max-height: 360px; overflow-y: auto; }
    .pos-result-item { border: 1px solid var(--border); border-radius: 12px; padding: 10px; background: var(--card); cursor: pointer; transition: border-color .15s ease; }
    .pos-result-item:hover { border-color: var(--accent-2); }
    .pos-meta { color: var(--muted); font-size: 12px; margin: 2px 0; }
    .pos-chip { display:inline-block; padding: 4px 8px; border-radius: 12px; background: var(--surface); border: 1px solid var(--border); font-size: 11px; }
    .pos-btn { border: none; border-radius: 12px; padding: 10px 12px; font-weight: 600; cursor: pointer; }
    .pos-btn-accent { background: linear-gradient(120deg, var(--accent-1), var(--accent-2)); color: #fff; }
    .pos-btn-ghost { background: var(--surface); color: var(--text); border: 1px solid var(--border); }
    table.pos-table { width: 100%; border-collapse: collapse; }
    table.pos-table th, table.pos-table td { padding: 10px; border-bottom: 1px solid var(--border); }
    .qty-input { width: 70px; text-align: center; }
    @media (max-width: 991px) { .pos-grid { grid-template-columns: 1fr; } }
    .modal-backdrop-lite { position:fixed; inset:0; background:rgba(0,0,0,0.4); display:none; align-items:center; justify-content:center; z-index:2000; }
    .modal-card { background: #fff; color:#111; border-radius: 12px; padding: 16px; width: 600px; max-width: 95vw; }
    .modal-close { border:none; background:none; font-size:20px; cursor:pointer; }
</style>
@endpush

@section('content')
<div class="pos-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <div class="pos-title">Kasir</div>
            <h3 class="mb-1">Point of Sale</h3>
            <div class="pos-meta">Cari produk, masukkan keranjang, lalu proses pembayaran.</div>
        </div>
        <div class="pos-panel" style="display:flex; gap:12px; align-items:center;">
            <div class="pos-chip">Kasir</div>
            <div>
                <div class="pos-meta">Total Bayar</div>
                <div class="pos-highlight" id="total-display">Rp 0</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="pos-panel mb-3">
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-2">
                    <label class="pos-title mb-1">Cari Produk / Batch</label>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <span class="pos-meta">Tier harga</span>
                        <select id="price_mode" class="pos-input" style="max-width:180px;">
                            <option value="retail">Ecer</option>
                            <option value="wholesale">Grosir</option>
                            <option value="insurance">Asuransi/BPJS</option>
                        </select>
                    </div>
                </div>
                <input id="search" class="pos-input" placeholder="Ketik nama produk atau scan barcode..." autofocus>
                <div id="results" class="pos-results"></div>
            </div>

            <div class="pos-panel">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <div class="pos-title mb-1">Keranjang</div>
                        <div class="pos-meta">Ubah qty atau hapus item sebelum bayar.</div>
                    </div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <button id="add-compound" type="button" class="pos-btn pos-btn-ghost">Tambah Racikan</button>
                        <button id="clear-cart" class="pos-btn pos-btn-ghost">Bersihkan</button>
                    </div>
                </div>
                <div style="overflow-x:auto;">
                    <table class="pos-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th style="width:120px;">Qty</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cart-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="pos-panel">
                <div class="pos-title mb-1">Pembayaran</div>
                <div class="mb-2">
                    <label class="pos-meta mb-1">Metode bayar</label>
                    <select id="payment_method" class="pos-input">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="pos-meta mb-1">Jumlah dibayar</label>
                    <input id="paid_amount" class="pos-input" placeholder="0">
                    <div class="mt-2" style="display:flex; gap:8px; flex-wrap:wrap;">
                        <button type="button" class="pos-btn pos-btn-ghost quick-pay" data-amt="10000">+10k</button>
                        <button type="button" class="pos-btn pos-btn-ghost quick-pay" data-amt="20000">+20k</button>
                        <button type="button" class="pos-btn pos-btn-ghost quick-pay" data-amt="50000">+50k</button>
                        <button type="button" class="pos-btn pos-btn-ghost quick-pay" data-amt="100000">+100k</button>
                        <button type="button" class="pos-btn pos-btn-ghost" id="clear-paid">Clear</button>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="pos-meta mb-1">Pasien / Pelanggan (opsional)</label>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <input id="patient_id" class="pos-input" style="max-width:160px;" placeholder="ID (opsional)">
                        <input list="patient_datalist" id="patient_name" class="pos-input" style="flex:1;" placeholder="Nama pasien (pilih / tambah baru)">
                        @if(isset($patients))
                        <datalist id="patient_datalist">
                            @foreach($patients as $p)
                                <option value="{{ $p->name }}" data-id="{{ $p->id }}" data-phone="{{ $p->phone }}" data-dob="{{ $p->dob }}"></option>
                            @endforeach
                        </datalist>
                        @endif
                    </div>
                    <div id="patient-suggestions" style="position:relative; margin-top:6px; z-index:30;"></div>
                    <div style="margin-top:8px; display:flex; gap:8px; flex-wrap:wrap;">
                        <input id="patient_phone" class="pos-input" style="max-width:220px;" placeholder="No. HP pasien (contoh: 62812...)">
                        <input id="patient_dob" type="date" class="pos-input" style="max-width:200px;" placeholder="Tanggal lahir" readonly>
                    </div>
                    <div class="pos-meta" style="color:#f97316;">Jika pasien wajib, pastikan pilih/isi salah satu.</div>
                </div>
                <div class="mb-2">
                    <label class="pos-meta mb-1">Jatuh tempo (opsional)</label>
                    <input id="due_date" type="date" class="pos-input">
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <div class="pos-meta mb-1">Total</div>
                        <div class="pos-highlight" id="total-side">Rp 0</div>
                        <div class="pos-meta" id="change-display">Kembalian: Rp 0</div>
                    </div>
                    <button id="checkout" class="pos-btn pos-btn-accent">Proses Pembayaran</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Racikan modal -->
<div id="compound-backdrop" class="modal-backdrop-lite">
    <div class="modal-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Tambah Racikan ke POS</h5>
            <button id="compound-close" class="modal-close">&times;</button>
        </div>
        <form id="compound-form">
            <div class="form-group">
                <label>Pilih template racikan</label>
                <select id="compound-template" class="form-control">
                    <option value="">-- pilih --</option>
                </select>
                <small class="text-muted" id="compound-expected-price"></small>
            </div>
            <div class="form-group">
                <label>Nama racikan (boleh diubah)</label>
                <input id="compound-name" class="form-control" placeholder="Contoh: Racikan Batuk Anak" required>
            </div>
            <div class="form-group">
                <label>Jumlah racikan</label>
                <input id="compound-qty" class="form-control" type="number" min="1" value="1">
            </div>
            <div class="d-flex justify-content-end" style="gap:8px;">
                <button type="button" id="compound-cancel" class="btn btn-light">Batal</button>
                <button type="submit" class="btn btn-primary">Tambah ke keranjang</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('page-js')
<script>
const resultsEl = document.getElementById('results');
const searchEl = document.getElementById('search');
const cart = [];
let priceMode = 'retail';
const paidInput = document.getElementById('paid_amount');
const patientIdInput = document.getElementById('patient_id');
const patientNameInput = document.getElementById('patient_name');
const dueInput = document.getElementById('due_date');
const patientPhoneInput = document.getElementById('patient_phone');
const patientDobInput = document.getElementById('patient_dob');
const patientSuggestions = document.getElementById('patient-suggestions');
const priceModeSelect = document.getElementById('price_mode');
let lastResults = [];
let searchBusy = false;
const currencyCode = @json(settings('app_currency_code','IDR'));
const currencySymbol = @json(settings('app_currency_symbol','Rp'));
const decimalSeparator = @json(settings('app_currency_decimal', ','));
const thousandSeparator = @json(settings('app_currency_thousand', '.'));
const hideDecimals = currencyCode === 'JPY';
const formatCurrency = (n) => {
    const num = Number(n) || 0;
    if (hideDecimals) {
        return currencySymbol + ' ' + num.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
    try {
        return new Intl.NumberFormat(undefined, {
            style:'currency',
            currency: currencyCode,
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(num);
    } catch(e){
        const parts = num.toFixed(2).split('.');
        const intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);
        const decPart = parts[1];
        return currencySymbol + ' ' + (hideDecimals ? intPart : intPart + decimalSeparator + decPart);
    }
};

const parseCurrency = (val) => {
    if(!val) return 0;
    const clean = val.replace(new RegExp('[^0-9'+decimalSeparator+']','g'),'').replace(decimalSeparator,'.');
    return parseFloat(clean) || 0;
};

function getTierPrice(item){
    const mode = priceMode;
    if(mode === 'wholesale' && item.price_wholesale){ return Number(item.price_wholesale); }
    if(mode === 'insurance' && item.price_insurance){ return Number(item.price_insurance); }
    if(item.price_retail){ return Number(item.price_retail); }
    return Number(item.cost_price || 0);
}

function computeLine(item){
    if(item.type === 'compound'){
        const unit = Number(item.unit_price || 0);
        return { unit, total: unit * (item.quantity||0) };
    }
    if(item.type === 'compound_template'){
        const unit = Number(item.unit_price || 0);
        return { unit, total: unit * (item.quantity||0) };
    }
    const base = getTierPrice(item);
    const promoPercent = Number(item.promo_percent || 0);
    const discountedUnit = base * (1 - (promoPercent/100));
    const qty = item.quantity || 0;
    let total = discountedUnit * qty;
    if(item.bundle_qty && item.bundle_price && item.bundle_qty > 1){
        const bundleQty = Number(item.bundle_qty);
        const bundlePrice = Number(item.bundle_price);
        const bundleCount = Math.floor(qty / bundleQty);
        const remainder = qty % bundleQty;
        total = (bundleCount * bundlePrice) + (remainder * discountedUnit);
    }
    return { unit: discountedUnit, total };
}

function renderTotals() {
    const total = cart.reduce((s,it)=> {
        const line = computeLine(it);
        return s + line.total;
    }, 0);
    document.getElementById('total-display').textContent = formatCurrency(total);
    document.getElementById('total-side').textContent = formatCurrency(total);
    const paid = parseCurrency(paidInput.value);
    const change = Math.max(0, paid - total);
    document.getElementById('change-display').textContent = 'Kembalian: ' + formatCurrency(change);
}

function setPatientFields(payload){
    if(!payload) return;
    patientIdInput.value = payload.id || '';
    patientNameInput.value = payload.name || '';
    patientPhoneInput.value = payload.phone || '';
    patientDobInput.value = payload.dob || '';
}

function syncPatientFromDatalist(){
    const dl = document.getElementById('patient_datalist');
    if(!dl) return;
    const opt = Array.from(dl.options || []).find(o => o.value === patientNameInput.value);
    if(opt){
        setPatientFields({ id: opt.dataset.id, name: opt.value, phone: opt.dataset.phone, dob: opt.dataset.dob });
    }
}

patientNameInput.addEventListener('change', syncPatientFromDatalist);

function renderResults(list){
    lastResults = list;
    resultsEl.innerHTML = '';
    if(!list.length){
        resultsEl.innerHTML = '<div class="pos-meta">Tidak ada hasil</div>';
        return;
    }
    list.forEach(b => {
        const el = document.createElement('div');
        el.className = 'pos-result-item';
        const price = getTierPrice(b);
        const promoTag = b.promo_percent && Number(b.promo_percent) > 0 ? `<span class="pos-chip" style="color:#f97316;">Promo ${Number(b.promo_percent)}%</span>` : '';
        const bundleTag = b.bundle_qty && b.bundle_price ? `<span class="pos-chip">Bundle ${b.bundle_qty} @ ${formatCurrency(b.bundle_price)}</span>` : '';
        el.innerHTML = `
            <div style="font-weight:700;">${b.product}</div>
            <div class="pos-meta">Harga (${priceMode}): ${formatCurrency(price)}</div>
            <div class="pos-meta">Batch: ${b.batch_no ?? 'N/A'} | Exp: ${b.expiry_date ?? '-'}</div>
            <div class="pos-meta">Stok: ${b.quantity}</div>
            <div class="pos-meta" style="display:flex; gap:6px; flex-wrap:wrap;">
                ${promoTag} ${bundleTag}
            </div>
        `;
        el.onclick = ()=> addToCart(b);
        resultsEl.appendChild(el);
    });
}

// Patients LOV (autocomplete)
let patientTimeout = null;
async function searchPatients(q, mode = 'all'){
    if(!q){ patientSuggestions.innerHTML=''; return; }
    try{
        const res = await fetch(`/patients/search?q=${encodeURIComponent(q)}&mode=${mode}`, { credentials: 'same-origin', headers:{ 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' } });
        if(!res.ok) throw new Error('Gagal mencari pasien');
        const data = await res.json();
        patientSuggestions.innerHTML = '';
        if(!(data && data.length)){
            const addEl = document.createElement('div');
            addEl.className = 'pos-result-item';
            addEl.textContent = `Tidak ditemukan. Tambah pasien baru dengan ${mode === 'phone' ? 'nomor ini' : 'nama ini'}?`;
            addEl.style.cursor = 'pointer';
            addEl.onclick = ()=> promptAddPatient(mode === 'phone' ? '' : q, mode === 'phone' ? q : '');
            patientSuggestions.appendChild(addEl);
            return;
        }
        data.forEach(p=>{
            const el = document.createElement('div');
            el.className = 'pos-result-item';
            el.textContent = p.name + (p.phone ? ' - ' + p.phone : '') + (p.dob ? ` | ${p.dob}` : '');
            el.style.cursor = 'pointer';
            el.onclick = ()=>{ setPatientFields({ id: p.id, name: p.name, phone: p.phone, dob: p.dob }); patientSuggestions.innerHTML=''; };
            patientSuggestions.appendChild(el);
        });
    } catch(err){
        patientSuggestions.innerHTML = `<div class="pos-meta" style="color:#f87171;">${err.message}</div>`;
    }
}

patientNameInput.addEventListener('input', (e)=>{
    clearTimeout(patientTimeout);
    const q = e.target.value.trim();
    patientTimeout = setTimeout(()=> searchPatients(q, 'name'), 300);
});
patientPhoneInput.addEventListener('input', (e)=>{
    clearTimeout(patientTimeout);
    const q = e.target.value.trim();
    patientTimeout = setTimeout(()=> searchPatients(q, 'phone'), 300);
});

function isValidDateInput(val){
    if(!val) return true;
    return /^\d{4}-\d{2}-\d{2}$/.test(val);
}

async function promptAddPatient(defaultName = '', defaultPhone = '', defaultDob = ''){
    const name = window.prompt('Nama pasien baru:', defaultName);
    if(!name) return;
    const phone = window.prompt('Nomor telepon (wajib, format 62/0 tanpa spasi):', defaultPhone);
    if(!phone || !validatePhoneFormat(phone)){
        alert('Nomor telepon wajib dan harus diawali 62 atau 0, tanpa spasi.');
        return;
    }
    const dob = window.prompt('Tanggal lahir (YYYY-MM-DD, opsional):', defaultDob);
    if(dob && !isValidDateInput(dob)){
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
                'X-Requested-With':'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ name, phone, dob: dob || null })
        });
        if(!res.ok){
            const txt = await res.text();
            throw new Error(txt.substring ? txt.substring(0,300) : 'Gagal simpan pasien');
        }
        const json = await res.json();
        if(json && json.success && json.id){
            setPatientFields({ id: json.id, name, phone, dob });
            patientSuggestions.innerHTML = '';
            alert('Pasien baru ditambahkan.');
        } else {
            // fallback: try to read id if returned as patient.id
            const newId = json.id || (json.patient ? json.patient.id : null);
            if(newId){
                const newDob = (json.patient ? json.patient.dob : null) || dob || '';
                setPatientFields({ id: newId, name, phone, dob: newDob });
                patientSuggestions.innerHTML = '';
                alert('Pasien baru ditambahkan.');
            } else {
                throw new Error(json.message || 'Gagal menambahkan pasien');
            }
        }
    } catch(err){
        alert('Gagal menambahkan pasien: ' + err.message);
    }
}

function validatePhoneFormat(phone){
    if(!phone) return false;
    // Accept numbers starting with 62 or 0, followed by 8-12 digits
    const re = /^(62|0)\d{8,12}$/;
    return re.test(phone.replace(/\s+/g, ''));
}

// Hide suggestions when clicking outside
document.addEventListener('click', (ev)=>{
    if(!patientSuggestions.contains(ev.target) && ev.target !== patientNameInput && ev.target !== patientPhoneInput){
        patientSuggestions.innerHTML = '';
    }
});

async function search(q){
    if(!q) { resultsEl.innerHTML=''; lastResults=[]; return; }
    if(searchBusy) return;
    searchBusy = true;
    try{
        const res = await fetch(`/pos/products?q=${encodeURIComponent(q)}`);
        if(!res.ok) throw new Error('Gagal memuat produk');
        const data = await res.json();
        renderResults(data);
    } catch(err){
        resultsEl.innerHTML = `<div class="pos-meta" style="color:#f87171;">${err.message}</div>`;
    } finally {
        searchBusy = false;
    }
}

let searchTimeout = null;
searchEl.addEventListener('input', (e)=>{
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(()=> search(e.target.value.trim()), 200);
});
searchEl.addEventListener('keydown', (e)=>{
    if(e.key === 'Enter'){
        e.preventDefault();
        if(lastResults.length){ addToCart(lastResults[0]); }
    }
});

priceModeSelect.addEventListener('change', (e)=>{
    priceMode = e.target.value;
    renderResults(lastResults || []);
    renderCart();
});

function addToCart(batch){
    if(!batch) return;
    const existing = cart.find(c=>c.purchase_id===batch.purchase_id);
    if(existing){ existing.quantity += 1; }
    else {
        cart.push({
            purchase_id: batch.purchase_id,
            product: batch.product,
            quantity: 1,
            price_retail: batch.price_retail ?? batch.cost_price,
            price_wholesale: batch.price_wholesale,
            price_insurance: batch.price_insurance,
            promo_percent: batch.promo_percent ?? 0,
            promo_name: batch.promo_name,
            bundle_qty: batch.bundle_qty,
            bundle_price: batch.bundle_price,
            cost_price: batch.cost_price,
            type: 'batch'
        });
    }
    renderCart();
}

function renderCart(){
    const tbody = document.getElementById('cart-body');
    tbody.innerHTML = '';
    cart.forEach((it, idx)=>{
        const pricing = computeLine(it);
        const subtotal = pricing.total;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${it.product}
                <div class="pos-meta">
                    ${it.type === 'compound' || it.type === 'compound_template' ? '<span class="pos-chip">Racikan</span>' : `Batch: ${it.purchase_id}`}
                </div>
            </td>
            <td>
                <input value="${it.quantity}" data-idx="${idx}" class="pos-input qty-input">
                <div class="mt-1" style="display:flex; gap:4px;">
                    <button type="button" class="pos-btn pos-btn-ghost btn-minus" data-idx="${idx}" style="padding:4px 8px;">-</button>
                    <button type="button" class="pos-btn pos-btn-ghost btn-plus" data-idx="${idx}" style="padding:4px 8px;">+</button>
                </div>
            </td>
            <td>${formatCurrency(pricing.unit)}</td>
            <td>${formatCurrency(subtotal)}</td>
            <td><button data-idx="${idx}" class="pos-btn pos-btn-ghost remove" style="padding:6px 10px;">Hapus</button></td>
        `;
        tbody.appendChild(tr);
    });
    tbody.querySelectorAll('.remove').forEach(btn=>btn.addEventListener('click', e=>{
        cart.splice(e.target.dataset.idx,1); renderCart();
    }));
    tbody.querySelectorAll('.qty-input').forEach(inp=>inp.addEventListener('change', e=>{
        const i = e.target.dataset.idx; cart[i].quantity = Math.max(1, parseInt(e.target.value)||1); renderCart();
    }));
    tbody.querySelectorAll('.btn-plus').forEach(btn=>btn.addEventListener('click', e=>{ const i=e.target.dataset.idx; cart[i].quantity++; renderCart(); }));
    tbody.querySelectorAll('.btn-minus').forEach(btn=>btn.addEventListener('click', e=>{ const i=e.target.dataset.idx; cart[i].quantity = Math.max(1, cart[i].quantity-1); renderCart(); }));
    renderTotals();
}

document.getElementById('clear-cart').addEventListener('click', ()=>{ cart.length=0; renderCart(); });
document.getElementById('clear-paid').addEventListener('click', ()=>{ paidInput.value=''; renderTotals(); });

// format paid amount on blur/input
['blur','change'].forEach(evt=>{
    paidInput.addEventListener(evt, ()=>{
        paidInput.value = formatCurrency(parseCurrency(paidInput.value));
        renderTotals();
    });
});
paidInput.addEventListener('input', ()=>{ renderTotals(); });

document.querySelectorAll('.quick-pay').forEach(btn=>{
    btn.addEventListener('click', ()=>{
        const amt = Number(btn.dataset.amt||0);
        const current = parseCurrency(paidInput.value);
        paidInput.value = formatCurrency(current + amt);
        renderTotals();
    });
});

// Prefill cart from prescription when ?rx=ID
async function prefillFromPrescription() {
    const params = new URLSearchParams(window.location.search);
    const rx = params.get('rx');
    if (!rx) return;
    try {
        const res = await fetch(`/pos/from-prescription/${rx}`, { headers:{'Accept':'application/json'} });
        if (!res.ok) throw new Error('Gagal memuat resep');
        const data = await res.json();
        if (data.patient) {
            patientIdInput.value = data.patient.id || '';
            patientNameInput.value = data.patient.name || '';
            patientPhoneInput.value = data.patient.phone || '';
            patientDobInput.value = data.patient.dob || '';
        }
        (data.items || []).forEach(it => {
            if(it.missing){ return; }
            const existing = cart.find(c=>c.purchase_id===it.purchase_id);
            if(existing){ existing.quantity += it.quantity; }
            else { cart.push({ ...it }); }
        });
        renderCart();
        if ((data.items||[]).some(it=>it.missing)) {
            alert('Beberapa item racikan/resep tidak punya stok dan dilewati.');
        }
    } catch (e) {
        console.error(e);
        alert('Tidak bisa memuat resep ke POS: ' + e.message);
    }
}

prefillFromPrescription();

// Racikan template modal handlers
const compoundBackdrop = document.getElementById('compound-backdrop');
const compoundForm = document.getElementById('compound-form');
const compoundSelect = document.getElementById('compound-template');
const compoundPriceDisplay = document.getElementById('compound-expected-price');
document.getElementById('add-compound').addEventListener('click', ()=>{ compoundBackdrop.style.display='flex'; });
document.getElementById('compound-close').addEventListener('click', ()=>{ compoundBackdrop.style.display='none'; });
document.getElementById('compound-cancel').addEventListener('click', ()=>{ compoundBackdrop.style.display='none'; });

async function loadCompounds(){
    try{
        const res = await fetch('/pos/compounds', { headers:{'Accept':'application/json'} });
        if(!res.ok) throw new Error('Gagal memuat racikan');
        const data = await res.json();
        compoundSelect.innerHTML = '<option value=\"\">-- pilih --</option>';
        data.forEach(c=>{
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name;
            opt.dataset.price_override = c.price_override || '';
            opt.dataset.service_fee = c.service_fee || 0;
            opt.dataset.markup = c.markup_percent || 0;
            opt.dataset.items = JSON.stringify(c.items || []);
            compoundSelect.appendChild(opt);
        });
    }catch(e){
        compoundSelect.innerHTML = '<option value=\"\">Gagal memuat</option>';
    }
}
loadCompounds();

compoundSelect.addEventListener('change', ()=>{
    const opt = compoundSelect.selectedOptions[0];
    if(!opt){ compoundPriceDisplay.textContent=''; return; }
    const priceOverride = opt.dataset.price_override;
    const service = parseFloat(opt.dataset.service_fee||0);
    const markup = parseFloat(opt.dataset.markup||0);
    if(priceOverride){
        compoundPriceDisplay.textContent = 'Harga override: ' + formatCurrency(parseFloat(priceOverride));
    } else {
        compoundPriceDisplay.textContent = `Harga dihitung: (HPP bahan + ${formatCurrency(service)}) x (1+${markup}% ).`;
    }
    if(!document.getElementById('compound-name').value){
        document.getElementById('compound-name').value = opt.textContent;
    }
});

compoundForm.addEventListener('submit', (e)=>{
    e.preventDefault();
    const opt = compoundSelect.selectedOptions[0];
    if(!opt || !opt.value){
        alert('Pilih template racikan');
        return;
    }
    const name = document.getElementById('compound-name').value.trim() || opt.textContent;
    const qty = Math.max(1, parseInt(document.getElementById('compound-qty').value)||1);
    const items = JSON.parse(opt.dataset.items || '[]');
    cart.push({
        type:'compound_template',
        compound_id: parseInt(opt.value),
        product: name,
        quantity: qty,
        unit_price: opt.dataset.price_override ? parseFloat(opt.dataset.price_override) : undefined,
        components: items
    });
    compoundBackdrop.style.display='none';
    compoundForm.reset();
    compoundSelect.value = '';
    compoundPriceDisplay.textContent = '';
    renderCart();
});

document.getElementById('checkout').addEventListener('click', async ()=>{
    if(cart.length===0){ alert('Keranjang kosong'); return; }
    const items = cart.map(it=>{
        const base = {
            product: it.product,
            purchase_id: it.purchase_id || null,
            quantity: it.quantity,
            type: it.type || 'batch'
        };
        if(base.type === 'compound'){
            base.unit_price = it.unit_price || it.price_retail || 0;
            base.components = it.components || [];
        }
        if(base.type === 'compound_template'){
            base.compound_id = it.compound_id;
            if(it.unit_price){ base.unit_price = it.unit_price; }
        }
        return base;
    });
    if(!patientIdInput.value && !patientNameInput.value){
        alert('Isi atau pilih pasien terlebih dahulu jika diwajibkan.');
    }
    const payload = {
        items,
        payment_method: document.getElementById('payment_method').value,
        paid_amount: parseCurrency(paidInput.value),
        price_mode: priceMode,
        patient_id: patientIdInput.value || null,
        patient_name: patientNameInput.value || null,
        patient_phone: patientPhoneInput.value || null,
        patient_dob: patientDobInput.value || null,
        due_date: dueInput.value || null
    };
    // client-side validation: if creating new patient (no id), require valid phone
    if(!payload.patient_id && payload.patient_name){
        if(!payload.patient_phone || !validatePhoneFormat(payload.patient_phone)){
            alert('Nomor telepon pasien wajib dan harus diawali 62 atau 0, tanpa spasi. Contoh: 62812xxxx');
            patientPhoneInput.focus();
            return;
        }
    }

    try{
        const res = await fetch('/pos/checkout', {
            method:'POST',
            credentials: 'same-origin',
            headers:{
                'Content-Type':'application/json',
                'Accept':'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });
        const ct = (res.headers.get('content-type')||'');
        if(ct.indexOf('application/json') === -1){
            const text = await res.text();
            throw new Error('Server returned non-JSON response: ' + (text.substring ? text.substring(0,1000) : text));
        }
        const json = await res.json();
        if(json.success){
            alert('Transaksi sukses | ID: '+json.invoice_id);
            cart.length=0; paidInput.value=''; patientIdInput.value=''; patientNameInput.value=''; patientPhoneInput.value=''; patientDobInput.value=''; dueInput.value='';
            renderCart();
        } else {
            alert('Gagal: '+ (json.message||'error'));
        }
    } catch(err){
        alert('Error saat proses pembayaran: ' + err.message);
    }
});

// awal render
renderCart();
</script>
@endpush
