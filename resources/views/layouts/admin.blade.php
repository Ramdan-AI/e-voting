<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — E-Voting KPUM</title>
    <style>
        :root {
            --navy: #6b1d2c;
            --accent: #8c2f3f;
            --bg: #f4f6f9;
            --error: #b3261e;
            --success: #1e7e34;
            --text: #222;
            --border: #e2e6ec;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
        }
        .topbar {
            background: var(--navy);
            color: #fff;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .topbar .brand { display: flex; align-items: center; gap: 10px;}
        .topbar .brand img { height: 50px; width: auto; }
        .topbar nav a {
            color: #cfd9e8;
            text-decoration: none;
            font-size: 13px;
            margin-right: 18px;
        }
        .topbar nav a:hover { color: #fff; }
        .topbar form button {
            background: transparent;
            border: 1px solid #4a6690;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
        }
        .topbar form button:hover { background: #2e5c8a; }
        main {
            max-width: 960px;
            margin: 0 auto;
            padding: 32px 24px;
        }
        h1 { color: var(--navy); font-size: 22px; margin: 0 0 20px; }
        .card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 24px;
            margin-bottom: 20px;
        }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid var(--border); }
        th { color: #666; font-weight: 600; font-size: 12px; text-transform: uppercase; }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge.running { background: #e6f4ea; color: var(--success); }
        .badge.freeze { background: #fff4e0; color: #a56a00; }
        .badge.stopped { background: #fdecea; color: var(--error); }
        .btn {
            display: inline-block;
            padding: 7px 14px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        .btn-primary { background: var(--navy); color: #fff; }
        .btn-primary:hover { background: var(--accent); }
        .btn-outline { background: #fff; color: var(--navy); border: 1px solid var(--navy); }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #444; }
        input[type="text"], input[type="email"], input[type="password"],
        input[type="datetime-local"], input[type="date"], textarea, select {
            width: 100%;
            padding: 9px 11px;
            border: 1px solid #d5dae1;
            border-radius: 7px;
            font-size: 14px;
            margin-bottom: 14px;
        }
        .alert {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
        }
        .alert.success { background: #e6f4ea; color: var(--success); border: 1px solid #b7e0c3; }
        .alert.error { background: #fdecea; color: var(--error); border: 1px solid #f5c2be; }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px; }
        .stat-box { background: #f7f9fc; border-radius: 10px; padding: 16px; text-align: center; }
        .stat-box .num { font-size: 24px; font-weight: 700; color: var(--navy); }
        .stat-box .label { font-size: 12px; color: #777; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="brand">
            @if (file_exists(public_path('images/logo/Logo_KPUM_STMIK.jpg')))
                <img src="{{ asset('images/logo/Logo_KPUM_STMIK.jpg') }}" alt="Logo KPUM">
            @else
                <div class="logo-fallback">KPUM</div>
            @endif
            <span>E-Voting KPUM - Admin</span>
        </div>
        @auth('admin')
            @php $admin = Auth::guard('admin')->user(); $periodeTerbaru = \App\Models\Periode::latest('id')->first(); @endphp
            <nav style="display:flex; align-items:center; flex-wrap:wrap; gap:2px;">
                <span style="font-size:12px; color:#a9bcd6; margin-right:16px;">
                    {{ $admin->nama }} — {{ $admin->namaRole() }}
                </span>

                <a href="{{ route('admin.dashboard') }}">Dashboard</a>

                @if ($admin->bisaKelolaPeriode())
                    <a href="{{ route('admin.periode.index') }}">Periode</a>
                @endif

                @if ($periodeTerbaru && $admin->bisaKelolaKandidat())
                    <a href="{{ route('admin.kandidat.index', $periodeTerbaru) }}">Kandidat</a>
                @endif

                @if ($periodeTerbaru && $admin->bisaKelolaPemilih())
                    {{-- <a href="{{ route('admin.pemilih.terkunci', $periodeTerbaru) }}">Akun Terkunci</a> --}}
                    <a href="{{ route('admin.audit.presensi', $periodeTerbaru) }}">Log Presensi</a>
                    <a href="{{ route('admin.pengaduan.index') }}">Pengaduan</a>
                @endif

                @if ($periodeTerbaru && $admin->bisaIsiLpj())
                    <a href="{{ route('admin.lpj.edit', $periodeTerbaru) }}">LPJ Saya</a>
                @endif

                @if ($periodeTerbaru && $admin->bisaReviewLpj())
                    <a href="{{ route('admin.lpj.index', $periodeTerbaru) }}">Review LPJ</a>
                @endif

                @if ($admin->bisaLihatAuditLog())
                    <a href="{{ route('admin.audit.index') }}">Log Aktivitas</a>
                @endif

                <form method="POST" action="{{ route('admin.logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit">Keluar</button>
                </form>
            </nav>
        @endauth
    </div>

    <main>
        @if (session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert error">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>