@extends('layouts.admin')

@section('title', 'Log Presensi')

@section('content')
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
            <thead><tr><th>Waktu Submit</th><th>Identifier</th><th>Nama</th></tr></thead>
            <tbody>
                @forelse ($presensi as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d M Y H:i:s') }}</td>
                        <td>{{ $log->pemilih->identifier }}</td>
                        <td>{{ $log->pemilih->nama }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="color:#999;">Belum ada suara masuk untuk periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection