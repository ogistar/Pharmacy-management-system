<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- csrf token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ucfirst(AppSettings::get('app_name', 'App'))}} - {{ucfirst($title ?? '')}}</title>
    <script>
        (function() {
            const saved = localStorage.getItem('theme') || 'light';
            document.documentElement.dataset.theme = saved;
            document.documentElement.classList.add('theme-' + saved);
        })();
    </script>
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/img/logo-small.png') }}">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{asset('assets/plugins/fontawesome/css/fontawesome.min.css')}}">
    <!-- Feathericon CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/feathericon.min.css')}}">

    <link rel="stylesheet" href="{{asset('assets/css/icons.min.css')}}">
    <!-- Snackbar CSS -->
	<link rel="stylesheet" href="{{asset('assets/plugins/snackbar/snackbar.min.css')}}">
    <!-- Sweet Alert css -->
    <link rel="stylesheet" href="{{asset('assets/plugins/sweetalert2/sweetalert2.min.css')}}">
    <!-- Snackbar Css -->
    <link rel="stylesheet" href="{{asset('assets/plugins/snackbar/snackbar.min.css')}}">
    <!-- Select2 Css -->
    <link rel="stylesheet" href="{{asset('assets/plugins/select2/css/select2.min.css')}}">
    <!-- Main CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <!-- Modern font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0b1224;
            --surface: rgba(255,255,255,0.04);
            --card: #0f172a;
            --text: #e5e7eb;
            --muted: #94a3b8;
            --border: rgba(255,255,255,0.08);
            --accent-1: #22c55e;
            --accent-2: #0ea5e9;
            --shadow: 0 20px 60px rgba(0,0,0,0.35);
            --body-gradient: radial-gradient(circle at 10% 20%, rgba(34,197,94,0.07) 0, transparent 25%), radial-gradient(circle at 80% 10%, rgba(14,165,233,0.06) 0, transparent 25%), linear-gradient(135deg, #0b1224, #0a1020);
            --sidebar-width: 260px;
            --icon-bg: rgba(255,255,255,0.06);
            --nav-hover: rgba(14,165,233,0.18);
            --nav-active: rgba(34,197,94,0.16);
        }
        :root[data-theme="light"] {
            --bg: #f4f7fb;
            --surface: #ffffff;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --border: #e2e8f0;
            --accent-1: #0ea5e9;
            --accent-2: #22c55e;
            --shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
            --body-gradient: radial-gradient(circle at 14% 18%, rgba(14,165,233,0.08) 0, transparent 25%), radial-gradient(circle at 82% 12%, rgba(34,197,94,0.08) 0, transparent 24%), linear-gradient(135deg, #f8fbff, #e2e8f0);
            --icon-bg: #eef2ff;
            --nav-hover: rgba(14,165,233,0.12);
            --nav-active: rgba(34,197,94,0.12);
        }
        body {
            background: var(--bg);
            color: var(--text);
            transition: background 0.3s ease, color 0.3s ease;
            font-family: 'Space Grotesk', system-ui, -apple-system, sans-serif;
            background-image: var(--body-gradient);
        }
        a, a:visited { color: var(--text); }
        a:hover { color: var(--accent-2); }
        .header, .sidebar, .sidebar-inner, .sidebar-menu { background: var(--card); color: var(--text); }
        .header { border-bottom: 1px solid var(--border); box-shadow: var(--shadow); }
        /* Sidebar refresh */
        .sidebar {
            width: var(--sidebar-width);
            border-right: 1px solid var(--border);
            box-shadow: var(--shadow);
        }
        body.mini-sidebar .sidebar { width: 80px; }
        .sidebar-inner { padding: 16px 14px 24px; }
        .sidebar-menu > ul { padding: 0; }
        .sidebar-menu .menu-title span {
            display: block;
            padding: 10px 14px 6px;
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
            opacity: 0.85;
        }
        .sidebar ul li { margin-bottom: 6px; }
        .sidebar ul li a {
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid transparent;
            transition: all 0.2s ease;
            background: transparent;
        }
        .sidebar ul li a .menu-arrow { margin-left: auto; color: var(--muted); font-size: 12px; }
        .sidebar ul li a i,
        .sidebar ul li a .material-icons {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--icon-bg);
            color: var(--accent-2);
            font-size: 16px;
        }
        .sidebar .submenu ul {
            padding-left: 10px;
            margin-top: 6px;
        }
        .sidebar .submenu ul li a {
            color: var(--muted);
            padding: 10px 12px;
            border-radius: 10px;
            /* Default (inactive) submenu items use surface (usually white in light theme)
               per requested swap: white when NOT selected */
            background: var(--surface);
            border-color: transparent;
        }
        .sidebar .submenu ul li a i,
        .sidebar .submenu ul li a .material-icons {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: transparent;
            font-size: 14px;
            color: var(--muted);
        }
        /* Invert visual treatment: by default show highlighted background for inactive items,
           and make the explicitly 'active' item more muted/transparent */
        
        .sidebar ul li a:hover {
            filter: brightness(0.96);
        }
        /* Make the parent link when submenu is open (.subdrop) more transparent/clear */
        .sidebar ul li a.subdrop {
            background: transparent !important;
            border-color: transparent !important;
            box-shadow: none !important;
            color: var(--text) !important;
        }
        .sidebar ul li a.subdrop i,
        .sidebar ul li a.subdrop .material-icons {
            background: transparent !important;
            color: var(--accent-2) !important;
        }
        /* Active menu becomes muted/transparent to visually invert colors */
        .sidebar ul li.active > a {
            background: transparent;
            border-color: transparent;
            box-shadow: none;
            color: var(--muted);
            font-weight: 600;
        }
        .sidebar ul li.active > a i,
        .sidebar ul li.active > a .material-icons {
            background: var(--icon-bg);
            color: var(--muted);
        }
        .sidebar .submenu ul li a:hover {
            color: var(--accent-2);
            background: color-mix(in srgb, var(--surface) 80%, var(--accent-2) 2%);
        }
        /* Active submenu item becomes transparent/muted (inverted behavior) */
        .sidebar .submenu ul li.active > a,
        .sidebar .submenu ul li a.active {
            color: var(--accent-2);
            background: transparent;
            border-color: transparent;
        }
        .sidebar .submenu ul li.active > a i,
        .sidebar .submenu ul li a.active i {
            color: var(--accent-2);
        }
        .sidebar ul li.active > a { font-weight: 600; }
        .card, .modal-content, .table, .glass-card, .form-control, .select2-container .select2-selection {
            background: var(--card);
            color: var(--text);
            border-color: var(--border);
            box-shadow: var(--shadow);
        }
        .form-control, .select2-selection { color: var(--text); }
        .form-control::placeholder { color: var(--muted); }
        .table thead th {
            border-color: var(--border);
            color: var(--muted);
            background: var(--surface);
        }
        .table tbody tr {
            color: var(--text);
            border-color: var(--border);
        }
        .table.table-modern {
            background: var(--card);
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 14px;
            overflow: hidden;
        }
        .table.table-modern thead th { border-bottom: 1px solid var(--border); }
        .table.table-modern tbody tr:hover { background: rgba(255,255,255,0.04); }
        .btn-primary {
            background: linear-gradient(120deg, var(--accent-1), var(--accent-2));
            border: none;
            color: #fff;
        }
        .btn-primary:hover { filter: brightness(1.05); }
        .btn-outline-light { color: var(--text); border-color: var(--border); }
        .btn-outline-light:hover { background: var(--surface); color: var(--accent-2); }
        .breadcrumb a { color: var(--muted); }
        .page-wrapper { background: var(--bg); }
        /* Sidebar toggle responsiveness */
        .page-wrapper { margin-left: var(--sidebar-width); transition: margin 0.2s ease; }
        body.mini-sidebar .page-wrapper { margin-left: 80px; }
        @media (max-width: 991px) {
            .page-wrapper { margin-left: 0; }
        }

        /* Buttons palette */
        .btn-secondary { background: var(--surface); border: 1px solid var(--border); color: var(--text); }
        .btn-secondary:hover { color: var(--accent-2); border-color: var(--accent-2); }
        .btn-danger { background: linear-gradient(120deg, #ef4444, #f97316); border: none; }
        .btn-success { background: linear-gradient(120deg, #22c55e, #10b981); border: none; }
        .btn-outline-primary { color: var(--accent-2); border-color: var(--accent-2); }
        .btn-outline-primary:hover { background: var(--accent-2); color: #fff; }
        .btn, .form-control, .select2-selection, .card, .modal-content {
            border-radius: 12px;
        }
        .glass-card {
            background: var(--card);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            border-radius: 16px;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text);
            font-size: 12px;
        }

        /* Tables */
        .table-striped tbody tr:nth-of-type(odd) { background: var(--surface); }
        .table-hover tbody tr:hover { background: rgba(255,255,255,0.05); }
        :root[data-theme="light"] .table-hover tbody tr:hover { background: #f1f5f9; }

        /* Forms */
        .form-control:focus, .select2-selection:focus {
            border-color: var(--accent-2);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent-2) 30%, transparent);
        }
        .form-control, .select2-selection {
            background: var(--card);
            border: 1px solid var(--border);
        }

        /* Badges & pills */
        .badge, .badge-pill {
            background: var(--surface);
            color: var(--text);
        }
        .badge-success { background: #16a34a; }
        .badge-danger { background: #ef4444; }
        .badge-warning { background: #f59e0b; color: #0f172a; }

        /* Dropdowns & modals */
        .dropdown-menu, .modal-header, .modal-footer {
            background: var(--card);
            color: var(--text);
            border-color: var(--border);
        }
        .dropdown-item { color: var(--text); }
        .dropdown-item:hover { background: var(--surface); color: var(--accent-2); }

        /* Cards & headers */
        .card-header, .card-footer {
            background: var(--surface);
            border-color: var(--border);
        }
        .page-header h3, .page-title { color: var(--text); }
        .page-header { background: transparent; border: none; margin-bottom: 8px; }
        .content.container-fluid { padding: 20px; }
        .card { border: 1px solid var(--border); box-shadow: var(--shadow); }

        /* Icons */
        i, .fe, .fas, .fa { color: var(--text); }
        a:hover i, a:hover .fe, a:hover .fas, a:hover .fa { color: var(--accent-2); }

        /* Custom tables */
        .table-modern {
            background: var(--card);
            color: var(--text);
            border-color: var(--border);
        }
        .table-modern thead th { background: var(--surface); color: var(--muted); border-color: var(--border); }
        .table-modern td, .table-modern th { border-color: var(--border); }
        .table-modern tr:last-child td { border-bottom: none; }

        /* Theme toggle visibility */
        .theme-toggle-btn {
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .theme-toggle-btn:hover { color: var(--accent-2); border-color: var(--accent-2); }
        /* Toasts */
        .elevated-toast {
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }
    </style>
    <!-- Page CSS -->
    @stack('page-css')
    <!--[if lt IE 9]>
        <script src="assets/js/html5shiv.min.js"></script>
        <script src="assets/js/respond.min.js"></script>
    <![endif]-->
</head>
<body>

    <!-- Main Wrapper -->
    <div class="main-wrapper">

        <!-- Header -->
        @include('admin.includes.header')
        <!-- /Header -->

        <!-- Sidebar -->
        @include('admin.includes.sidebar')
        <!-- /Sidebar -->

        <!-- Page Wrapper -->
        <div class="page-wrapper">

            <div class="content container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div class="row">
                        @stack('page-header')
                    </div>
                </div>
                <!-- /Page Header -->
                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <x-alerts.danger :error="$error" />
                    @endforeach
                @endif

                @yield('content')
            </div>
        </div>
        <!-- /Page Wrapper -->

    </div>
    <!-- /Main Wrapper -->
    
</body>
<!-- jQuery -->
<script src="{{asset('assets/plugins/jquery/jquery.min.js')}}"></script>

<!-- Bootstrap Core JS -->
<script src="{{asset('assets/js/popper.min.js')}}"></script>
<script src="{{asset('assets/js/bootstrap.min.js')}}"></script>
<script src="{{asset('assets/plugins/slimscroll/jquery.slimscroll.min.js')}}"></script>
<!-- Sweet Alert Js -->
<script src="{{asset('assets/plugins/sweetalert2/sweetalert2.min.js')}}"></script>
<!-- Snackbar Js -->
<script src="{{asset('assets/plugins/snackbar/snackbar.min.js')}}"></script>
<!-- Select2 JS -->
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<!-- Custom JS -->
<script src="{{asset('assets/js/script.js')}}"></script>
<script>
    $(document).ready(function(){
        $('body').on('click','#deletebtn',function(){
            var id = $(this).data('id');
            var route = $(this).data('route');
            swal.queue([
                {
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: '<i class="fe fe-trash mr-1"></i> Delete!',
                    cancelButtonText: '<i class="fa fa-times mr-1"></i> Cancel!',
                    confirmButtonClass: "btn btn-success mt-2",
                    cancelButtonClass: "btn btn-danger ml-2 mt-2",
                    buttonsStyling: !1,
                    preConfirm: function(){
                        return new Promise(function(){
                            $.ajax({
                                url: route,
                                type: "DELETE",
                                data: {"id": id},
                                success: function(){
                                    swal.insertQueueStep(
                                        Swal.fire({
                                            title: "Deleted!",
                                            text: "Resource has been deleted.",
                                            type: "success",
                                            showConfirmButton: !1,
                                            timer: 1500,
                                        })
                                    )
                                    $('.datatable').DataTable().ajax.reload();
                                }
                            })

                        })
                    }
                }
            ]).catch(swal.noop);
        }); 
    });
    @php
        $flashQueue = array_values(array_filter([
            Session::has('message') ? ['type' => Session::get('alert-type', 'info'), 'text' => Session::get('message')] : null,
            Session::has('success') ? ['type' => 'success', 'text' => Session::get('success')] : null,
            Session::has('error') ? ['type' => 'error', 'text' => Session::get('error')] : null,
            Session::has('warning') ? ['type' => 'warning', 'text' => Session::get('warning')] : null,
            Session::has('info') ? ['type' => 'info', 'text' => Session::get('info')] : null,
            Session::has('status') ? ['type' => 'success', 'text' => Session::get('status')] : null,
        ]));
    @endphp
    const flashQueue = @json($flashQueue);

    const showFlashToast = ({ type, text }) => {
        const theme = document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light';
        const palette = theme === 'dark'
            ? { bg: '#0f172a', text: '#e2e8f0', border: '#1e293b' }
            : { bg: '#ffffff', text: '#0f172a', border: '#e2e8f0' };
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info',
            title: text,
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            background: palette.bg,
            color: palette.text,
            customClass: {
                popup: 'elevated-toast'
            },
            didOpen: (toast) => {
                toast.style.borderColor = palette.border;
            }
        });
    };
    flashQueue.forEach(showFlashToast);
</script>
<script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('theme-toggle');
            const icon = document.getElementById('theme-toggle-icon');
            const setTheme = (mode) => {
                localStorage.setItem('theme', mode);
                document.documentElement.dataset.theme = mode;
                document.documentElement.classList.remove('theme-dark','theme-light');
                document.documentElement.classList.add('theme-' + mode);
                if(icon){
                icon.className = mode === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
                icon.style.color = 'inherit';
                icon.parentElement?.classList.toggle('btn-outline-light', mode === 'dark');
                icon.parentElement?.classList.toggle('btn-outline-dark', mode === 'light');
                }
            };
        const saved = localStorage.getItem('theme') || 'light';
        setTheme(saved);
        toggle?.addEventListener('click', () => {
            const current = document.documentElement.dataset.theme === 'light' ? 'light' : 'dark';
            setTheme(current === 'light' ? 'dark' : 'light');
        });
    });
</script>
<!-- Page JS -->
<script>
    (function(){
        const symbol = @json(settings('app_currency_symbol','Rp'));
        function normalizeNumberString(s){
            s = (s||'').toString().trim();
            if(!s) return '';
            // keep only digits and separators
            s = s.replace(/[^0-9.,]/g,'');
            const hasDot = s.indexOf('.') !== -1;
            const hasComma = s.indexOf(',') !== -1;

            // If both separators exist, assume the last one is the decimal separator
            if(hasDot && hasComma){
                const lastDot = s.lastIndexOf('.');
                const lastComma = s.lastIndexOf(',');
                const lastSep = Math.max(lastDot, lastComma);
                const intPart = s.slice(0, lastSep).replace(/[.,]/g,'');
                const decPart = s.slice(lastSep+1).replace(/[.,]/g,'');
                return intPart + (decPart ? '.' + decPart : '');
            }

            // If only dots exist, decide by the size of the trailing group:
            // - trailing length == 3  => likely thousand separator (remove dots)
            // - trailing length <=2  => likely decimal separator
            if(hasDot && !hasComma){
                const parts = s.split('.');
                const trailing = parts[parts.length-1];
                if(trailing.length === 3){
                    return parts.join(''); // remove all dots (thousands separators)
                }
                // treat last dot as decimal separator, remove other dots
                const intPart = parts.slice(0, -1).join('');
                const decPart = trailing;
                return intPart + (decPart ? '.' + decPart : '');
            }

            // If only commas exist, apply same heuristic
            if(hasComma && !hasDot){
                const parts = s.split(',');
                const trailing = parts[parts.length-1];
                if(trailing.length === 3){
                    return parts.join('');
                }
                const intPart = parts.slice(0, -1).join('');
                const decPart = trailing;
                return intPart + (decPart ? '.' + decPart : '');
            }

            // no separators
            return s;
        }
        function formatMoneyInput(el){
            const original = (el.value || '').toString();
            const raw = normalizeNumberString(original);
            if(raw === ''){ // leave visible input empty, use placeholder
                el.value = '';
                return;
            }
            const num = parseFloat(raw);
            if(isNaN(num)){
                el.value = '';
                return;
            }
            // detect if user provided a decimal separator
            const hasDecimal = /[.,]/.test(original);
            // determine decimals count from normalized raw
            const decLen = raw.includes('.') ? Math.min(2, raw.split('.')[1].length) : 0;
            try{
                if(hasDecimal && decLen > 0){
                    el.value = new Intl.NumberFormat(undefined, { minimumFractionDigits: decLen, maximumFractionDigits: decLen }).format(num);
                } else if(hasDecimal && decLen === 0){
                    // user typed a separator but no decimals - show no decimals
                    el.value = new Intl.NumberFormat(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(Math.trunc(num));
                } else {
                    el.value = new Intl.NumberFormat(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(Math.trunc(num));
                }
            }catch(e){
                // fallback formatting
                if(hasDecimal && decLen > 0){
                    const parts = num.toFixed(decLen).split('.');
                    const intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    el.value = intPart + (parts[1] ? '.' + parts[1] : '');
                } else {
                    const intStr = String(Math.trunc(num)).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    el.value = intStr;
                }
            }
        }
        document.addEventListener('DOMContentLoaded', ()=>{
            document.querySelectorAll('.money-input').forEach(el=>{
                // set placeholder to 0 if empty
                if(!el.placeholder) el.placeholder = '0';
                el.addEventListener('change', ()=> formatMoneyInput(el));
                el.addEventListener('blur', ()=> formatMoneyInput(el));
                formatMoneyInput(el);
            });
            // On form submit, ensure corresponding hidden inputs receive normalized numeric values
            document.addEventListener('submit', function(evt){
                const form = evt.target;
                if(!(form instanceof HTMLFormElement)) return;
                form.querySelectorAll('.money-input').forEach(el=>{
                    const hidden = form.querySelector('input.money-hidden[name]') || el.closest('.input-group')?.querySelector('input.money-hidden[name]');
                    const raw = normalizeNumberString(el.value);
                    const value = raw === '' ? '0' : raw;
                    if(hidden){ hidden.value = value; }
                    else if(el.dataset.name){
                        const inp = document.createElement('input'); inp.type='hidden'; inp.name = el.dataset.name; inp.className='money-hidden'; inp.value = value; form.appendChild(inp);
                    }
                });
            }, true);
        });
    })();
</script>
@stack('page-js')
</html>
