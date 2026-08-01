<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Voting KPUM {{ $periode->judul ?? '' }}</title>
    <style>
        :root {
            --garnet: #4a0e14;
            --crimson: #7c1d24;
            --ruby: #a63a3a;
            --ember: #c96b6b;
            --blush: #e7e1e1;
            --error: #b3261e; --success: #1e7e34; --text: #2a1618;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--blush); color: var(--text);
        }

        /* ---------- Navbar ---------- */
        .navbar {
            background: #fff; border-bottom: 1px solid #ddc9cc;
            padding: 12px 24px; display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 20;
        }
        .navbar .brand { display: flex; align-items: center; gap: 10px; }
        .navbar .brand img { height: 50px; width: auto; border-radius: 4px; }
        .navbar .brand .logo-fallback {
            height: 50px; width: 50px; border-radius: 8px; background: var(--garnet); color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700;
        }

        .navbar .brand span { font-weight: 700; color: var(--garnet); font-size: 15px; }
        .btn-login-nav {
            background: var(--garnet); color: #fff; border: none; padding: 9px 18px;
            border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;
        }
        .btn-login-nav:hover { background: var(--crimson); }

        /* ---------- Hero (logo watermark) ---------- */
        .hero {
            position: relative; overflow: hidden;
            background: radial-gradient(ellipse at top, var(--crimson) 0%, var(--garnet) 65%, #2e080c 100%);
            padding: 56px 20px 64px; text-align: center; color: #fff;
        }
        .hero-logos {
            position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
            gap: 48px; opacity: 0.16; pointer-events: none;
        }
        .hero-logos img { height: 150px; width: 150px; object-fit: contain; filter: brightness(0) invert(1); }
        .hero-content { position: relative; z-index: 1; }
        .hero h1 { margin: 0 0 8px; font-size: 26px; }
        .hero p.subtitle { margin: 0; color: #f0d9dc; font-size: 14px; }
        .hero .turnout-num { font-size: 44px; font-weight: 800; margin-top: 18px; }
        .hero .turnout-label { font-size: 12px; color: #f0d9dc; letter-spacing: 0.04em; text-transform: uppercase; }
        .hero .turnout-bar-track { max-width: 420px; margin: 14px auto 0; background: rgba(255,255,255,0.2); border-radius: 20px; height: 8px; overflow: hidden; }
        .hero .turnout-bar-fill { background: #fff; height: 100%; border-radius: 20px; }
        .hero .turnout-sub { display: flex; justify-content: center; gap: 24px; font-size: 12px; color: #f0d9dc; margin-top: 8px; }

        /* ---------- Kandidat cards (section terpisah di bawah Hero) ---------- */
        .wrap { max-width: 900px; margin: -30px auto 40px; padding: 0 20px; }
        .section-title { font-size: 15px; color: white; font-weight: 700; margin: 32px 0 16px; }
        .kandidat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; align-items: start; }
        .kandidat-card {
            background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(74,14,20,0.10);
            overflow: hidden; border: 1px solid #ecdfe1;
        }
        .kandidat-card .foto-wrap {
            width: 100%; aspect-ratio: 4/3; background: var(--blush); overflow: hidden;
        }
        .kandidat-card .foto-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .kandidat-card .foto-wrap .fallback {
            width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
            color: var(--ember); font-size: 13px;
        }
        .kandidat-card .body { padding: 18px 20px; }
        .kandidat-card .nomor { display: inline-block; background: var(--ruby); color: #fff; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; margin-bottom: 8px; }
        .kandidat-card h3 { margin: 0 0 12px; font-size: 16px; color: var(--text); }
        .kandidat-card .persen-num { font-size: 28px; font-weight: 800; color: var(--garnet); }
        .kandidat-card .bar-track { background: var(--blush); border-radius: 20px; height: 8px; overflow: hidden; margin-top: 8px; }
        .kandidat-card .bar-fill { background: var(--ruby); height: 100%; border-radius: 20px; }
        .kandidat-card .hasil-tersembunyi { font-size: 13px; color: #a08a8d; font-style: italic; }
        .vismi-toggle {
            margin-top: 14px; width: 100%; background: var(--blush); border: none; border-radius: 8px;
            padding: 10px 12px; font-size: 13px; font-weight: 600; color: var(--garnet); cursor: pointer;
            display: flex; justify-content: space-between; align-items: center;
        }
        .vismi-toggle:hover { background: #ddc9cc; }
        .vismi-content {
            display: none; margin-top: 10px; font-size: 12.5px; color: #5c4346; line-height: 1.6;
        }
        .vismi-content.open { display: block; }
        .vismi-content strong { color: var(--garnet); }
        .empty-state { text-align: center; color: #a08a8d; font-size: 14px; padding: 40px 0; }

        /* ---------- Modal Login ---------- */
        .modal-overlay {
            display: none; position: fixed; inset: 0; background: rgba(20,5,8,0.6);
            align-items: center; justify-content: center; z-index: 100; padding: 20px;
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: #fff; border-radius: 14px; padding: 28px 26px; width: 100%; max-width: 380px;
            position: relative;
        }
        .modal-close {
            position: absolute; top: 14px; right: 16px; background: none; border: none;
            font-size: 20px; color: #999; cursor: pointer; line-height: 1;
        }
        .modal-box h2 { font-size: 17px; color: var(--garnet); margin: 0 0 20px; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #444; }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 11px 12px; border: 1px solid #d5c1c4; border-radius: 8px;
            font-size: 15px; margin-bottom: 16px;
        }
        button.submit {
            width: 100%; padding: 12px; background: var(--garnet); color: #fff; border: none;
            border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;
        }
        button.submit:hover { background: var(--crimson); }
        .errors {
            background: #fdecea; border: 1px solid #f5c2be; color: var(--error);
            padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 18px;
        }
        .errors ul { margin: 0; padding-left: 18px; }
        .help-link {
            display: block; text-align: center; font-size: 12px; color: #999;
            margin-top: 16px; text-decoration: none;
        }
        .help-link:hover { color: var(--crimson); }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="brand">
            @if (file_exists(public_path('images/logo/Logo_KPUM_STMIK.jpg')))
                <img src="{{ asset('images/logo/Logo_KPUM_STMIK.jpg') }}" alt="Logo KPUM">
            @else
                <div class="logo-fallback">KPUM</div>
            @endif
            <span>E-Voting KPUM</span>
        </div>
        <button class="btn-login-nav" onclick="document.getElementById('loginModal').classList.add('open')">
            Login Pemilih
        </button>
    </div>

    <div class="hero">
        <div class="hero-logos">
            {{-- Add your logo images here, e.g.: --}}
        </div>
        <div class="hero-content">
            <h1>{{ $periode->judul ?? 'Belum ada pemilihan yang sedang berjalan' }}</h1>
            <p class="subtitle">Komisi Pemilihan Umum Mahasiswa — STMIK Mardira Indonesia</p>

            @if ($periode && $ringkasan)
                <div class="turnout-num">{{ $ringkasan['turnout'] }}%</div>
                <div class="turnout-label">Tingkat Partisipasi</div>
                <div class="turnout-bar-track">
                    <div class="turnout-bar-fill" style="width: {{ $ringkasan['turnout'] }}%;"></div>
                </div>
                <div class="turnout-sub">
                    <span>{{ $ringkasan['total_suara'] }} suara masuk</span>
                    <span>{{ $ringkasan['total_pemilih'] }} pemilih terdaftar</span>
                </div>
            @endif
        </div>

        <div class="wrap">
            @if ($periode && $ringkasan && $ringkasan['kandidats']->isNotEmpty())
                <div class="section-title">Pasangan Calon</div>
                <div class="kandidat-grid">
                    @foreach ($ringkasan['kandidats'] as $kandidat)
                        <div class="kandidat-card">
                            <div class="foto-wrap">
                                @if ($kandidat->foto && $kandidat->foto !== 'dummy.jpg' && \Illuminate\Support\Facades\Storage::disk('public')->exists($kandidat->foto))
                                    <img src="{{ Storage::url($kandidat->foto) }}" alt="{{ $kandidat->nama }}">
                                @else
                                    <div class="fallback">Tidak ada foto</div>
                                @endif
                            </div>
                            <div class="body">
                                <span class="nomor">Paslon #{{ $kandidat->nomor_urut }}</span>
                                <h3>{{ $kandidat->nama }}</h3>

                                @if ($ringkasan['tampilkan_hasil'])
                                    <div class="persen-num">{{ $kandidat->persentase }}%</div>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: {{ $kandidat->persentase }}%;"></div>
                                    </div>
                                @else
                                    <div class="hasil-tersembunyi">Hasil suara belum ditampilkan panitia.</div>
                                @endif

                                <button class="vismi-toggle" onclick="toggleVismi(this)">
                                    <span>Lihat Visi &amp; Misi</span>
                                    <span class="chev">&#9662;</span>
                                </button>
                                <div class="vismi-content">
                                    <p><strong>Visi:</strong><br>{{ $kandidat->visi }}</p>
                                    <p style="margin-bottom:0;"><strong>Misi:</strong><br>{{ $kandidat->misi }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif ($periode)
                <div class="empty-state">Belum ada kandidat yang terdaftar untuk periode ini.</div>
            @else
                <div class="empty-state">Belum ada informasi pemilihan untuk ditampilkan saat ini.</div>
            @endif
        </div>
    </div>

    <div class="modal-overlay" id="loginModal">
        <div class="modal-box">
            <button class="modal-close" onclick="document.getElementById('loginModal').classList.remove('open')">&times;</button>
            <h2>Login Pemilih</h2>

            @if ($errors->any())
                <div class="errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf
                <label for="identifier">NIM / Identifier</label>
                <input type="text" id="identifier" name="identifier" value="{{ old('identifier') }}" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <button type="submit" class="submit">Masuk</button>
            </form>

            <a href="{{ route('pengaduan.create') }}" class="help-link">Ada kendala login? Laporkan lewat form bantuan &rarr;</a>
        </div>
    </div>

    <script>
        function toggleVismi(btn) {
            const content = btn.nextElementSibling;
            content.classList.toggle('open');
            const chev = btn.querySelector('.chev');
            chev.innerHTML = content.classList.contains('open') ? '&#9652;' : '&#9662;';
        }

        @if ($errors->any())
            document.getElementById('loginModal').classList.add('open');
        @endif
    </script>
</body>
</html>