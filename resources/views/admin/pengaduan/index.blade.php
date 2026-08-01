@extends('layouts.admin')

@section('title', 'Pengaduan Akun')

@section('content')
    <h1>Pengaduan Akun Terkunci</h1>

    <div class="card">
        <table>
            <thead><tr><th>Identifier</th><th>Nama</th><th>Email</th><th>Periode</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($pengaduans as $p)
                    <tr>
                        <td>{{ $p->identifier }}</td>
                        <td>{{ $p->nama }}</td>
                        <td>{{ $p->email }}</td>
                        <td>{{ $p->no_hp }}</td>
                        <td>{{ $p->periode->judul ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $p->status === 'disetujui' ? 'running' : ($p->status === 'ditolak' ? 'stopped' : 'freeze') }}">
                                {{ strtoupper($p->status) }}
                            </span>
                        </td>
                        <td><a href="{{ route('admin.pengaduan.show', $p) }}" class="btn btn-outline">Lihat</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="color:#999;">Belum ada pengaduan masuk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $pengaduans->links() }}
@endsection