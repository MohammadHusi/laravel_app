<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | Absensi SMK Mawaddah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        :root{--ink:#142a42;--navy:#0f3d2c;--blue:#1a8f4c;--mint:#d8f0e2;--paper:#f3f6f8;--line:#e4eaee;--muted:#728394;--font-body:'DM Sans',sans-serif;--font-heading:'Manrope',sans-serif}
        *{letter-spacing:0}body{background:var(--paper);color:var(--ink);font-family:var(--font-body);font-size:14px}h1,h2,h3,h4,h5,.brand{font-family:var(--font-heading)}
        .app-sidebar{position:fixed;inset:0 auto 0 0;width:250px;background:var(--navy);color:#fff;padding:6px 16px 25px;z-index:1030;display:flex;flex-direction:column}
        .brand{display:flex;flex-direction:column;align-items:center;text-align:center;gap:8px;color:#fff;text-decoration:none;font-weight:800;font-size:16px;line-height:1.3;padding:0 8px 14px}.brand>span:last-child{white-space:nowrap}.brand>span:last-child small{font-size:13px}.brand-mark{width:92px;height:92px;display:grid;place-items:center;background:transparent;border-radius:0;overflow:visible;flex:none}.brand-mark img{width:88px;height:88px;object-fit:contain}
        .nav-caption{font-size:10px;font-weight:700;color:#9bb4c4;text-transform:uppercase;padding:0 12px;margin:8px 0}.sidebar-divider{border:0;border-top:1px solid #2a5544;opacity:1;margin:4px 8px 14px}.side-link{display:flex;align-items:center;gap:12px;padding:11px 12px;margin:2px 0;border-radius:8px;color:#d3e0e8;text-decoration:none;font-weight:500;transition:background .18s,color .18s}.side-link i{width:18px;text-align:center;font-size:15px}.side-link:hover,.side-link.active{background:#175d43;color:#fff}.side-link.active{box-shadow:inset 3px 0 #7fd6a4}.side-bottom{margin-top:auto;border-top:1px solid #2a5544;padding-top:15px}.role-label{font-size:11px;color:#b4c9d6;text-transform:capitalize}
        .app-main{margin-left:250px;min-height:100vh}.topbar{height:72px;background:#fff;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 34px;position:sticky;top:0;z-index:1010}.topbar-title{font-size:14px;font-weight:700}.topbar-user{display:flex;align-items:center;gap:10px}.avatar{width:36px;height:36px;border-radius:50%;background:var(--mint);color:var(--navy);display:grid;place-items:center;font-weight:700}.page-wrap{max-width:1440px;margin:auto;padding:30px 34px 45px}.page-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:24px}.eyebrow{font-size:11px;color:var(--muted);margin-bottom:6px}.page-heading h1{font-size:25px;font-weight:800;margin:0 0 5px}.page-heading p{color:var(--muted);margin:0}.stat-card,.panel{background:white;border:1px solid var(--line);border-radius:9px}.stat-card{height:100%;padding:18px 20px;position:relative;overflow:hidden;animation:rise .38s both}.stat-card:after{content:'';position:absolute;right:-25px;bottom:-35px;width:105px;height:105px;border:18px solid #f2f7f6;border-radius:50%}.stat-label{color:var(--muted);font-size:12px;font-weight:600}.stat-value{font-family:var(--font-heading);font-size:27px;font-weight:800;margin:10px 0 3px}.stat-icon{width:38px;height:38px;border-radius:9px;display:grid;place-items:center;background:#e6f3ef;color:#176b61}.stat-note{color:var(--muted);font-size:11px}.panel{padding:20px}.panel-title{font:700 15px var(--font-heading);margin:0}.panel-subtitle{color:var(--muted);font-size:12px;margin:5px 0 0}.table{--bs-table-bg:transparent;vertical-align:middle}.table thead th{background:#f7f9fa;color:#687b89;font-size:10px;text-transform:uppercase;font-weight:700;border-bottom:1px solid var(--line);white-space:nowrap;padding:12px}.table td{padding:13px 12px;border-color:#edf1f3}.badge-status{font-size:10px;font-weight:700;padding:6px 9px;border-radius:20px;text-transform:capitalize}.status-hadir{background:#e2f3ec;color:#18734f}.status-izin{background:#fff1d8;color:#996a12}.status-sakit{background:#e7eff9;color:#426a9c}.status-alpa{background:#fce7e7;color:#af4545}.btn{border-radius:7px;font-weight:600}.btn-primary{background:var(--blue);border-color:var(--blue)}.btn-primary:hover{background:#136b39;border-color:#136b39}.form-control,.form-select{border-color:#dbe3e8;border-radius:7px;min-height:42px}.form-label{font-weight:600;font-size:12px;color:#42596b}.empty-state{text-align:center;padding:40px 20px;color:var(--muted)}.empty-state i{font-size:24px;color:#9aabb6;margin-bottom:10px}.mobile-brand{display:none}.table-responsive{border-radius:7px}.breadcrumb{font-size:11px;margin-bottom:15px}.breadcrumb-item a{color:var(--blue);text-decoration:none}
        .data-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:16px}.data-toolbar-left{display:flex;align-items:center;gap:8px;font-size:12px;color:var(--muted)}.data-toolbar-left .form-select{width:auto;min-height:34px;padding:4px 30px 4px 10px}.data-toolbar-right{display:flex;align-items:center;gap:8px}.data-search{position:relative}.data-search i{position:absolute;top:50%;left:11px;transform:translateY(-50%);color:#8597a3;font-size:12px}.data-search .form-control{min-height:36px;padding-left:32px;min-width:230px}.table-data{border-collapse:collapse}.table-data thead th{background:#0f3d2c;color:#fff;border:1px solid #9aa5ac;font-size:11px;text-transform:uppercase;font-weight:700;letter-spacing:.3px;padding:13px 14px}.table-data td{padding:13px 14px;border:1px solid #c9d0d5;vertical-align:middle}.table-data tbody tr:hover{background:#f7faf8}.btn-action{width:34px;height:34px;display:inline-grid;place-items:center;border:0;border-radius:8px;color:#fff;font-size:13px;text-decoration:none;transition:filter .15s;vertical-align:middle}.btn-action:hover{filter:brightness(.92);color:#fff}.btn-view{background:#2f6fed}.btn-edit{background:#1a8f4c}.btn-delete{background:#d64545}.data-footer{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-top:16px}.data-info{font-size:12px;color:var(--muted)}.data-pagination{margin-left:auto}.data-pagination .pagination{margin:0}
        @keyframes rise{from{opacity:0;transform:translateY(7px)}to{opacity:1;transform:translateY(0)}}
        @media(max-width:991.98px){.app-sidebar{display:none}.app-main{margin-left:0}.topbar{height:64px;padding:0 18px}.mobile-brand{display:flex;align-items:center;gap:9px;color:var(--navy);font-family:var(--font-heading);font-size:13px;font-weight:800}.page-wrap{padding:24px 20px 36px}}
        @media(max-width:575.98px){.page-wrap{padding:20px 14px 30px}.page-heading h1{font-size:21px}.topbar{padding:0 14px}.hide-mobile{display:none!important}.stat-value{font-size:24px}.panel{padding:16px}}
        #mobileMenu .offcanvas-header{align-items:flex-start}
        #mobileMenu .offcanvas-header .btn-close{margin:2px 0 0 auto;flex:none}
        #mobileMenu .offcanvas-header .brand{padding-bottom:12px}
    </style>
    @stack('styles')
</head>
<body>
    @php
        $role = auth()->user()->role;
        $navigation = match ($role) {
            'admin' => [
                ['Dashboard', 'admin.dashboard', 'fa-gauge-high', null],
                ['Data Guru', 'admin.data.index', 'fa-chalkboard-user', 'guru'], ['Data Murid', 'admin.data.index', 'fa-user-graduate', 'murid'],
                ['Data Kelas', 'admin.data.index', 'fa-school', 'kelas'], ['Tahun Ajaran', 'admin.data.index', 'fa-calendar-days', 'tahun-ajaran'], ['Jadwal Pelajaran', 'admin.data.index', 'fa-clock', 'jadwal'],
                ['Kelola Absensi', 'admin.kelola-absensi', 'fa-clipboard-check', null],
            ],
            'guru' => [
                ['Dashboard', 'guru.dashboard', 'fa-gauge-high', null], ['Absensi Siswa', 'guru.absensi.index', 'fa-clipboard-check', null],
                ['Jadwal Pelajaran', 'guru.jadwal.index', 'fa-clock', null],
            ],
            default => [
                ['Dashboard', 'murid.dashboard', 'fa-gauge-high', null],
                ['Jadwal Pelajaran', 'murid.jadwal.index', 'fa-clock', null],
            ],
        };
        $roleLabels = ['admin' => 'Administrator', 'guru' => 'Guru', 'murid' => 'Murid'];
        $initial = mb_strtoupper(mb_substr(auth()->user()->name, 0, 1));
    @endphp
    <aside class="app-sidebar">
        <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark"><img src="{{ asset('logo.png') }}" alt="Logo SMK Mawaddah"></span><span>SMK MAWADDAH<br><small class="fw-normal opacity-75">PALENGAAN</small></span></a>
        <hr class="sidebar-divider">
        <nav>
            @foreach($navigation as $item)
                @php
                    [$label, $routeName, $icon, $resource] = array_pad($item, 4, null);
                    $href = match (true) {
                        $resource === 'riwayat' => route($routeName) . '#riwayat-absensi',
                        isset($resource) => route($routeName, $resource),
                        default => route($routeName),
                    };
                @endphp
                <a class="side-link {{ request()->routeIs($routeName) && (!isset($resource) || (request()->route()?->hasParameter('resource') && request()->route('resource') === $resource)) ? 'active' : '' }}" href="{{ $href }}"><i class="fa-solid {{ $icon }}"></i><span>{{ $label }}</span></a>
            @endforeach
        </nav>
        <div class="side-bottom"><a class="side-link {{ request()->routeIs('profile') ? 'active' : '' }}" href="{{ route('profile') }}"><i class="fa-regular fa-circle-user"></i><span>Profil Saya</span></a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="side-link border-0 bg-transparent w-100 text-start"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Keluar</span></button></form>
        </div>
    </aside>
    <div class="app-main">
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-label="Buka menu"><i class="fa-solid fa-bars"></i></button>
                <div class="topbar-title d-none d-lg-block">@yield('topbar', 'Panel Informasi Absensi')</div>
            </div>
            <div class="topbar-user"><div class="text-end hide-mobile"><div class="fw-bold" style="font-size:12px">{{ auth()->user()->name }}</div><div class="text-secondary" style="font-size:10px">{{ $roleLabels[$role] ?? $role }}</div></div><div class="avatar">{{ $initial }}</div></div>
        </header>
        <main class="page-wrap">
            @if($errors->any())<div class="alert alert-danger border-0 py-2">{{ $errors->first() }}</div>@endif
            @yield('content')
        </main>
    </div>
    <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel" style="width:280px;background:#0f3d2c!important">
        <div class="offcanvas-header"><a class="brand p-0" id="mobileMenuLabel" href="{{ route('dashboard') }}"><span class="brand-mark"><img src="{{ asset('logo.png') }}" alt="Logo SMK Mawaddah"></span>SMK MAWADDAH PALENGAAN</a><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button></div>
        <div class="offcanvas-body p-3">
            @foreach($navigation as $item)
                @php
                    [$label, $routeName, $icon, $resource] = array_pad($item, 4, null);
                    $href = match (true) {
                        $resource === 'riwayat' => route($routeName) . '#riwayat-absensi',
                        isset($resource) => route($routeName, $resource),
                        default => route($routeName),
                    };
                @endphp
                <a class="side-link {{ request()->routeIs($routeName) && (!isset($resource) || (request()->route()?->hasParameter('resource') && request()->route('resource') === $resource)) ? 'active' : '' }}" href="{{ $href }}"><i class="fa-solid {{ $icon }}"></i><span>{{ $label }}</span></a>
            @endforeach
            <a class="side-link mt-3" href="{{ route('profile') }}"><i class="fa-regular fa-circle-user"></i><span>Profil Saya</span></a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="side-link border-0 bg-transparent w-100 text-start"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Keluar</span></button></form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    @if(session('success'))<script>Swal.fire({icon:'success',title:'Berhasil',text:@json(session('success')),confirmButtonText:'OK',confirmButtonColor:'#1a8f4c'});</script>@endif
    <script>
    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form.matches('form[data-confirm]') || form.dataset.confirmed === '1') return;
        event.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Konfirmasi Hapus',
            text: form.getAttribute('data-confirm') || 'Hapus data ini?',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d64545',
            cancelButtonColor: '#8a98a4',
            reverseButtons: true,
        }).then(function (result) {
            if (result.isConfirmed) {
                form.dataset.confirmed = '1';
                form.submit();
            }
        });
    });
    </script>
    @stack('scripts')
</body>
</html>