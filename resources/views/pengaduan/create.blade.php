<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengaduan Akun — E-Voting KPUM</title>
    <style>
        :root { --garnet: #4a0e14; --crimson: #7c1d24; --blush: #e7e1e1; --error: #b3261e; --success: #1e7e34; }
        * { box-sizing: border-box; }
        body {
            margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(rgba(20,4,6,0.6), rgba(20,4,6,0.6)), url('{{ asset('images/bg-utama.png') }}') center/cover no-repeat fixed;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 24px;
        }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 24px rgba(74,14,20,0.10); padding: 32px; width: 100%; max-width: 440px; }
        h1 { font-size: 19px; color: var(--garnet); margin: 0 0 6px; }
        p.subtitle { font-size: 13px; color: #666; margin: 0 0 24px; line-height: 1.5; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #444; }
        input[type="text"], input[type="email"], input[type="file"] {
            width: 100%; padding: 10px 12px; border: 1px solid #d5c1c4; border-radius: 8px;
            font-size: 14px; margin-bottom: 16px;
        }
        .hint { font-size: 11.5px; color: #999; margin: -10px 0 16px; }
        button { width: 100%; padding: 12px; background: var(--garnet); color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; }
        button:hover { background: var(--crimson); }
        .alert { padding: 12px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 18px; }
        .alert.success { background: #e6f4ea; color: var(--success); border: 1px solid #b7e0c3; }
        .alert.error { background: #fdecea; color: var(--error); border: 1px solid #f5c2be; }
        .alert.error ul { margin: 0; padding-left: 18px; }
        a.back { display: block; text-align: center; font-size: 12px; color: #999; margin-top: 18px; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Form Pengaduan Akun</h1>
        <p class="subtitle">
            Gunakan form ini kalau akun Anda terkunci, NIM/identifier tidak ditemukan, atau kendala login lainnya.
            Sertakan foto ktm sebagai bukti identitas -- panitia akan mengecek status Anda sebelum membuka kunci akun.
        </p>

        @if (session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('pengaduan.store') }}" enctype="multipart/form-data">
            @csrf

            <label>NIM / NID / NIP</label>
            <input type="text" name="identifier" value="{{ old('identifier') }}" required>

            <label>Nama Lengkap</label>
            <input type="text" name="nama" value="{{ old('nama') }}" required>

            <label>Email Aktif</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
            <div class="hint">Panitia akan menghubungi Anda lewat email ini setelah pengaduan diproses.</div>

            <label>No. HP / WhatsApp Aktif</label>
            <input type="text" name="no_hp" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx"
                inputmode="numeric" pattern="[0-9]*" maxlength="20"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>

            <label>Foto KTM (bukti identitas)</label>
            <input type="file" name="foto_ktm" accept="image/*" required>
            <div class="hint">Pastikan wajah terlihat jelas. Maks 2MB.</div>

            <label>Keterangan (laporkan kendala yang terjadi, lupa password/email, atau akun terkunci)</label>
            <input type="text" name="keterangan" value="{{ old('keterangan') }}" required>
            <div class="hint">Jelaskan kendala Anda secara singkat dan jelas.</div>
            <button type="submit">Kirim Pengaduan</button>
        </form>

        <a href="{{ route('landing') }}" class="back">&larr; Kembali ke halaman utama</a>
    </div>
</body>
</html>