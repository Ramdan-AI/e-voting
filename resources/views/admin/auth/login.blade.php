<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — E-Voting KPUM</title>
    <style>
        :root { --navy: #1f3864; --accent: #2e5c8a; --bg: #f4f6f9; --error: #b3261e; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                
            background-image:
                linear-gradient(
                    rgba(20,20,20,.55),
                    rgba(20,20,20,.55)
                ),
                url('{{ asset("images/bg-utama.png") }}');
                
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
                
            display: flex;
            justify-content: center;
            align-items: center;
                
            min-height: 100vh;
            padding: 24px;
        }
        .card {
            background: #fff; border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 40px 36px; width: 100%; max-width: 400px;
        }
        h1 { font-size: 20px; color: var(--navy); margin: 0 0 4px; }
        p.subtitle { font-size: 14px; color: #666; margin: 0 0 28px; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #444; }
        input { width: 100%; padding: 11px 12px; border: 1px solid #d5dae1; border-radius: 8px; font-size: 15px; margin-bottom: 18px; }
        button { width: 100%; padding: 12px; background: var(--navy); color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; }
        button:hover { background: var(--accent); }
        .errors { background: #fdecea; border: 1px solid #f5c2be; color: var(--error); padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 18px; }
        .errors ul { margin: 0; padding-left: 18px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Login Panitia</h1>
        <p class="subtitle">Dashboard Admin — Sistem E-Voting KPUM</p>

        @if ($errors->any())
            <div class="errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" autofocus required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Masuk</button>
        </form>
    </div>
</body>
</html>