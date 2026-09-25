@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1>Dashboard</h1>

    @if ($ringkasan)
        <div class="card">
            <p style="font-size:13px; color:#666; margin-top:0;">
                Periode aktif: <strong>{{ $ringkasan['periode']->judul }}</strong>
                <span class="badge {{ $ringkasan['periode']->status }}">{{ $ringkasan['periode']->status }}</span>
            </p>
            <div class="stat-grid">
                <div class="stat-box">
                    <div class="num" id="live-total-suara">{{ $ringkasan['total_suara'] }}</div>
                    <div class="label">Suara Masuk</div>
                </div>
                <div class="stat-box">
                    <div class="num">{{ $ringkasan['total_pemilih'] }}</div>
                    <div class="label">Pemilih Terdaftar</div>
                </div>
                <div class="stat-box">
                    <div class="num">{{ $ringkasan['total_kandidat'] }}</div>
                    <div class="label">Kandidat</div>
                </div>
            </div>
        </div>

            <div class="card" id="live-hasil-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <strong style="font-size:14px;">Hasil Real-Time</strong>
                    <span style="font-size:12px; color:#888;">Auto-refresh tiap 5 detik</span>
                </div>
                <table>
                    <thead><tr><th>No. Urut</th><th>Nama</th><th>Suara</th><th>%</th></tr></thead>
                    <tbody id="live-hasil-body">
                        <tr><td colspan="4" style="color:#999;">Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
            
            <script>
                (function () {
                    const periodeId = {{ $ringkasan['periode']->id }};
                    const url = "{{ url('/admin/periode') }}/" + periodeId + "/hasil-live";
                    const totalEl = document.getElementById('live-total-suara');
                    const bodyEl = document.getElementById('live-hasil-body');
                
                    async function refreshHasil() {
                        try {
                            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                            if (!res.ok) return;
                            const data = await res.json();
                        
                            totalEl.textContent = data.total_suara;
                        
                            if (!data.hasil.length) {
                                bodyEl.innerHTML = '<tr><td colspan="4" style="color:#999;">Belum ada kandidat.</td></tr>';
                                return;
                            }
                        
                            bodyEl.innerHTML = data.hasil.map(k => `
                                <tr>
                                    <td>${k.nomor_urut}</td>
                                    <td>${k.nama}</td>
                                    <td>${k.suaras_count}</td>
                                    <td>${k.persentase}%</td>
                                </tr>
                            `).join('');
                        } catch (e) {}
                    }
                
                    refreshHasil();
                    setInterval(refreshHasil, 5000);
                })();
            </script>
            @else
                <div class="card">
                    <p style="margin:0; color:#666;">Tidak ada periode yang sedang berjalan (running) saat ini.</p>
                </div>
            @endif

    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <strong style="font-size:14px;">Semua Periode</strong>
            <a href="{{ route('admin.periode.index') }}" class="btn btn-outline">Kelola Periode</a>
        </div>
        <table>
            <thead><tr><th>ID</th><th>Judul</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($semuaPeriode as $p)
                    <tr>
                        <td>#{{ $p->id }}</td>
                        <td>{{ $p->judul }}</td>
                        <td><span class="badge {{ $p->status }}">{{ $p->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="color:#999;">Belum ada periode.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection