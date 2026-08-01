@extends('layouts.admin')

@section('title', 'Akun Terkunci')

@section('content')
    <div style="margin-bottom:20px;">
        <a href="{{ route('admin.periode.index') }}" style="font-size:13px; color:var(--accent); text-decoration:none;">&larr; Kembali ke Periode</a>
        <h1 style="margin:8px 0 0;">Akun Terkunci — {{ $periode->judul }}</h1>
    </div>

    <div class="card">
        <table>
            <thead><tr><th>Identifier</th><th>Nama</th><th>Percobaan Gagal</th><th></th></tr></thead>
            <tbody>
                @forelse ($terkunci as $pemilih)
                    <tr>
                        <td>{{ $pemilih->identifier }}</td>
                        <td>{{ $pemilih->nama }}</td>
                        <td>{{ $pemilih->pivot->percobaan_gagal }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.pemilih.unlock', [$periode, $pemilih]) }}" style="margin:0;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-primary">Buka Kunci</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="color:#999;">Tidak ada akun yang terkunci saat ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection