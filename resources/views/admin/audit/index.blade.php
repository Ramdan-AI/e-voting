@extends('layouts.admin')

@section('title', 'Log Aktivitas Admin')

@section('content')
    <h1>Log Aktivitas Admin</h1>

    <div class="card">
        <table>
            <thead><tr><th>Waktu</th><th>Admin</th><th>Aksi</th><th>Keterangan</th><th>Periode</th></tr></thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td style="white-space:nowrap;">{{ $log->created_at->format('d M Y H:i') }}</td>
                        <td>{{ $log->admin->nama ?? '—' }}</td>
                        <td><code style="font-size:12px;">{{ $log->aksi }}</code></td>
                        <td style="max-width:320px;">{{ $log->keterangan }}</td>
                        <td>{{ $log->periode->judul ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="color:#999;">Belum ada aktivitas tercatat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $logs->links() }}
@endsection