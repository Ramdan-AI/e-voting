<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pemilih — E-Voting KPUM</title>
    <style>
        :root {
            --navy: #1f3864;
            --accent: #2e5c8a;
            --bg: #f4f6f9;
            --error: #b3261e;
            --text: #222;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 40px 36px;
            width: 100%;
            max-width: 400px;
        }
        h1 {
            font-size: 20px;
            color: var(--navy);
            margin: 0 0 4px;
        }
        p.subtitle {
            font-size: 14px;
            color: #666;
            margin: 0 0 28px;
        }
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #444;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d5dae1;
            border-radius: 8px;
            font-size: 15px;
            margin-bottom: 18px;
        }
        input:focus {
            outline: 2px solid var(--accent);
            outline-offset: 1px;
            border-color: var(--accent);
        }
        button {
            width: 100%;
            padding: 12px;
            background: var(--navy);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover { background: var(--accent); }
        .errors {
            background: #fdecea;
            border: 1px solid #f5c2be;
            color: var(--error);
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
        }
        .errors ul { margin: 0; padding-left: 18px; }
        .note {
            margin-top: 20px;
            font-size: 12px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Login Pemilih</h1>
        <p class="subtitle">Sistem E-Voting KPUM</p>

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

            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}"required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required>

            <button type="submit">Masuk</button>
        </form>
        <p class="note">
            Login menggunakan akun Sistem Akademik STMIK Mardira Indonesia.
        </p>
    </div>
</body>
</html>