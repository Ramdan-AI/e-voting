@extends('layouts.admin')

@section('title', 'Kelola Kandidat')

@section('content')
    <div style="margin-bottom:20px;">
        <a href="{{ route('admin.periode.index') }}" style="font-size:13px; color:var(--accent); text-decoration:none;">&larr; Kembali ke Periode</a>
        <h1 style="margin:8px 0 0;">Kandidat — {{ $periode->judul }}</h1>
    </div>

    @if ($periode->isRunning())
        <div class="alert error">
            Periode sedang <strong>running</strong>. Data kandidat tidak bisa ditambah/diubah/dihapus
            selama status ini aktif.
        </div>
    @endif

    <div class="card">
        <table>
            <thead><tr><th>No. Urut</th><th>Nama</th><th>Total Suara</th></tr></thead>
            <tbody>
                @forelse ($kandidats as $kandidat)
                    <tr>
                        <td>#{{ $kandidat->nomor_urut }}</td>
                        <td>{{ $kandidat->nama }}</td>
                        <td>{{ $kandidat->totalSuara() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="color:#999;">Belum ada kandidat untuk periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @unless ($periode->isRunning())
        <div class="card" style="max-width:520px; item-align:center; margin:0 auto;">
            <strong style="font-size:20px; text-align:center;">Tambah Kandidat</strong>
            <form method="POST" action="{{ route('admin.kandidat.store', $periode) }}" enctype="multipart/form-data" style="margin-top:14px;">
                @csrf

                <label>Nomor Urut</label>
                <input type="text" name="nomor_urut" required>

                <label>Nama Pasangan Calon</label>
                <input type="text" name="nama" required>

                <label>Visi</label>
                <textarea name="visi" rows="3" required></textarea>

                <label>Misi</label>
                <textarea name="misi" rows="3" required></textarea>

                <label>Foto</label>
                <input type="file" name="foto" accept="image/*" required style="margin-bottom:14px;">

                <button type="submit" class="btn btn-primary" style="width:100%;">Tambah Kandidat</button>
            </form>
        </div>
    @endunless
@endsection