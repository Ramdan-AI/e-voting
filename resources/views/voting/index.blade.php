<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pilih Kandidat — E-Voting KPUM</title>
    <style>
        :root {
            --navy: #6b1d2c;
            --accent: #8c2f3f;
            --bg: #f4f6f9;
            --error: #b3261e;
            --success: #1e7e34;
            --text: #222;
        }
        * { box-sizing: border-box; }
       body {
            margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(rgba(20,4,6,0.6), rgba(20,4,6,0.6)), url('{{ asset('images/bg-utama.png') }}') center/cover no-repeat fixed;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 24px;
        }
        .wrap { max-width: 720px; margin: 0 auto; }
        h1 { color: white; font-size: 22px; margin-bottom: 4px; }
        p.subtitle { color: #666; font-size: 14px; margin-bottom: 28px; }
        .kandidat-list {
            display: grid;
            gap: 14px;
        }
        .kandidat-card {
            background: #fff;
            border: 2px solid #e2e6ec;
            border-radius: 12px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            cursor: pointer;
            transition: border-color 0.15s;
        }
        .kandidat-card:hover { border-color: var(--accent); }
        .kandidat-card.selected {
            border-color: var(--navy);
            background: #eef3f9;
        }
        .kandidat-card input[type="radio"] { width: 18px; height: 18px; }
        .kandidat-card .foto-kecil {
            width: 56px; height: 56px; border-radius: 10px; overflow: hidden; flex-shrink: 0;
            background: #f0e6e8;
        }
        .kandidat-card .foto-kecil img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .kandidat-card .foto-kecil .fallback {
            width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
            font-size: 11px; color: #a08a8d; text-align: center;
        }
        .nomor-urut {
            font-weight: 700;
            color: var(--navy);
            font-size: 14px;
            min-width: 34px;
        }
        .kandidat-info h3 { margin: 0; font-size: 16px; }
        button#submit-btn {
            margin-top: 24px;
            width: 100%;
            padding: 13px;
            background: var(--navy);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }
        button#submit-btn:disabled { background: #a9b4c2; cursor: not-allowed; }
        button#submit-btn:not(:disabled):hover { background: var(--accent); }
        #message {
            margin-top: 16px;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 14px;
            display: none;
        }
        #message.error { background: #fdecea; color: var(--error); border: 1px solid #f5c2be; display: block; }
        #message.success { background: #e6f4ea; color: var(--success); border: 1px solid #b7e0c3; display: block; }
        .empty {
            background: #fff;
            border-radius: 12px;
            padding: 32px;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h1>Pilih Kandidat</h1>
                <p class="subtitle">{{ $periode->judul ?? 'Periode Pemilihan' }} — pilih satu pasangan calon, lalu kirim suara Anda.</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="background:#fff; color:#b3261e; border:1px solid #f1b8b3; border-radius:8px; padding:8px 14px; font-size:13px; cursor:pointer; white-space:nowrap;">
                    Keluar
                </button>
            </form>
        </div>

        @if ($kandidats->isEmpty())
            <div class="empty">Belum ada kandidat yang terdaftar untuk periode ini.</div>
        @else
            <form id="voting-form">
                @csrf
                <div class="kandidat-list">
                    @foreach ($kandidats as $kandidat)
                        <label class="kandidat-card" data-id="{{ $kandidat->id }}">
                            <input type="radio" name="kandidat_id" value="{{ $kandidat->id }}" required>
                            <div class="foto-kecil">
                                @if ($kandidat->foto && $kandidat->foto !== 'dummy.jpg' && \Illuminate\Support\Facades\Storage::disk('public')->exists($kandidat->foto))
                                    <img src="{{ Storage::url($kandidat->foto) }}" alt="{{ $kandidat->nama }}">
                                @else
                                    <div class="fallback">Tidak ada foto</div>
                                @endif
                            </div>
                            <span class="nomor-urut">#{{ $kandidat->nomor_urut }}</span>
                            <span class="kandidat-info">
                                <h3>{{ $kandidat->nama }}</h3>
                            </span>
                        </label>
                    @endforeach
                </div>

                <button type="submit" id="submit-btn">Kirim Suara</button>
                <div id="message"></div>
            </form>
        @endif
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const form = document.getElementById('voting-form');
        const messageBox = document.getElementById('message');
        const submitBtn = document.getElementById('submit-btn');

        document.querySelectorAll('.kandidat-card').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.kandidat-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
            });
        });

        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const selected = form.querySelector('input[name="kandidat_id"]:checked');
                if (! selected) {
                    messageBox.className = 'error';
                    messageBox.textContent = 'Silakan pilih salah satu kandidat terlebih dahulu.';
                    return;
                }

                // Pop-up konfirmasi sebelum submit, sesuai dokumen fitur.
                const yakin = confirm('Apakah Anda yakin dengan pilihan Anda? Suara tidak dapat diubah setelah dikirim.');
                if (! yakin) return;

                submitBtn.disabled = true;
                submitBtn.textContent = 'Mengirim...';
                messageBox.className = '';
                messageBox.textContent = '';

                try {
                    const response = await fetch("{{ route('voting.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ kandidat_id: selected.value }),
                    });

                    const data = await response.json();

                    if (response.ok) {
                        messageBox.className = 'success';
                        messageBox.textContent = data.message + ' Anda akan diarahkan kembali ke halaman utama.';
                        form.querySelectorAll('input, button').forEach(el => el.disabled = true);
                        setTimeout(() => { window.location.href = "{{ route('login') }}"; }, 2500);
                    } else {
                        messageBox.className = 'error';
                        messageBox.textContent = data.message || 'Terjadi kesalahan saat mengirim suara.';
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Kirim Suara';
                    }
                } catch (err) {
                    messageBox.className = 'error';
                    messageBox.textContent = 'Gagal terhubung ke server. Coba lagi.';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Kirim Suara';
                }
            });
        }
    </script>
</body>
</html>