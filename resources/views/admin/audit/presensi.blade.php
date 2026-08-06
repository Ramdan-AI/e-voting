@extends('layouts.admin')

@section('title', 'Log Presensi')

@section('content')

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px;">

        <form method="GET" action="{{ route('admin.audit.presensi', $periode) }}" style="display:flex; gap:10px;">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari Nama atau Identifier..."
                style="min-width:250px; margin:0;"
            >
        
            <button class="btn btn-primary" type="submit">
                Cari
            </button>
        
            @if(request('search'))
                <a href="{{ route('admin.audit.presensi', $periode) }}"
                   class="btn btn-outline">
                    Reset
                </a>
            @endif
            
        </form>

    </div>

    <div style="margin-bottom:20px;">
        <a href="{{ route('admin.periode.index') }}" style="font-size:13px; color:var(--accent); text-decoration:none;">&larr; Kembali ke Periode</a>
        <h1 style="margin:8px 0 0;">Log Presensi — {{ $periode->judul }}</h1>
        <p style="font-size:12px; color:#888; margin:4px 0 0;">
            Hanya mencatat waktu submit suara, TIDAK mencatat kandidat yang dipilih (anonimitas tetap terjaga).
        </p>
    </div>

    <div class="card">
        <p style="font-size:13px; color:#666; margin-top:0;">
            Total presensi: <strong>{{ $presensi->count() }}</strong> — bandingkan dengan jumlah suara masuk
            di dashboard untuk memastikan angkanya cocok.
        </p>
        <table>
            <thead><tr><th>Waktu Submit</th><th>Identifier</th><th>Nama</th><th>Tipe</th></tr></thead>
            <tbody>
                @forelse ($presensi as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d M Y H:i:s') }}</td>
                        <td>{{ $log->pemilih->identifier }}</td>
                        <td>{{ $log->pemilih->nama }}</td>
                        <td>{{ $log->tipe_pemilih ? ucfirst($log->tipe_pemilih) : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="color:#999;">Belum ada suara masuk untuk periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection