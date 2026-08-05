<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP — E-Voting KPUM</title>
    <style>
        :root { --garnet: #4a0e14; --crimson: #7c1d24; --blush: #e7e1e1; --error: #b3261e; --success: #1e7e34; }
        * { box-sizing: border-box; }
        body {
            margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--blush); display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 24px;
        }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 24px rgba(74,14,20,0.10); padding: 32px; width: 100%; max-width: 380px; text-align: center; }
        h1 { font-size: 18px; color: var(--garnet); margin: 0 0 6px; }
        p.subtitle { font-size: 13px; color: #666; margin: 0 0 24px; line-height: 1.5; }
        input[name="kode"] {
            width: 100%; padding: 14px; border: 1px solid #d5c1c4; border-radius: 8px;
            font-size: 26px; text-align: center; letter-spacing: 10px; margin-bottom: 16px;
        }
        button.submit { width: 100%; padding: 12px; background: var(--garnet); color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; }
        button.submit:hover { background: var(--crimson); }
        .alert { padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 18px; text-align: left; }
        .alert.success { background: #e6f4ea; color: var(--success); border: 1px solid #b7e0c3; }
        .alert.error { background: #fdecea; color: var(--error); border: 1px solid #f5c2be; }
        .alert.error ul { margin: 0; padding-left: 18px; }
        form.resend { margin-top: 14px; }
        .resend button {
            background: none; border: none; color: var(--crimson); font-size: 13px;
            text-decoration: underline; cursor: pointer; padding: 0;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Verifikasi Kode OTP</h1>
        <p class="subtitle">
            Kode 6 digit sudah dikirim ke email terdaftar milik
            <strong>{{ $pemilih->nama }}</strong>. Kode berlaku 5 menit.
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

        <form method="POST" action="{{ route('otp.verify') }}">
            @csrf
            <input type="text" name="kode" inputmode="numeric" pattern="[0-9]*" maxlength="6"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')" autofocus required>
            <button type="submit" class="submit">Verifikasi</button>
        </form>

        <form method="POST" action="{{ route('otp.resend') }}" class="resend">
            @csrf
            <button type="submit">Tidak menerima kode? Kirim ulang</button>
        </form>
    </div>
</body>
</html>