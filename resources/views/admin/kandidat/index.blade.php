@extends('layouts.admin')

@section('title', 'Kelola Kandidat')

@section('content')
    <div style="margin-bottom:20px;">
        <a href="{{ route('admin.periode.index') }}" style="font-size:14px; color:white; text-decoration:none;">&larr; Kembali ke Periode</a>
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
            <thead><tr><th>No. Urut</th><th>Nama</th><th>Total Suara</th><th></th></tr></thead>
            <tbody>
                @forelse ($kandidats as $kandidat)
                    <tr>
                        <td>#{{ $kandidat->nomor_urut }}</td>
                        <td>{{ $kandidat->nama }}</td>
                        <td>{{ $kandidat->totalSuara() }}</td>
                        <td>
                            @unless ($periode->isRunning())
                                <button type="button" class="btn btn-outline" onclick="toggleEditRow('edit-{{ $kandidat->id }}')">Edit</button>
                                <form method="POST" action="{{ route('admin.kandidat.destroy', [$periode, $kandidat]) }}" style="display:inline;"
                                    onsubmit="return confirm('Yakin hapus kandidat {{ $kandidat->nama }}? Tidak bisa dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline" style="color:var(--error); border-color:#f5c2be;">Hapus</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                    @unless ($periode->isRunning())
                        <tr id="edit-{{ $kandidat->id }}" class="edit-row" style="display:none;">
                            <td colspan="4" style="background:#faf6f7;">
                                <form method="POST" action="{{ route('admin.kandidat.update', [$periode, $kandidat]) }}" enctype="multipart/form-data" style="max-width:480px; padding:12px 0;">
                                    @csrf
                                    @method('PATCH')

                                    <label>Nomor Urut</label>
                                    <input type="text" name="nomor_urut" value="{{ $kandidat->nomor_urut }}" required>

                                    <label>Nama Pasangan Calon</label>
                                    <input type="text" name="nama" value="{{ $kandidat->nama }}" required>

                                    <label>Visi</label>
                                    <textarea name="visi" rows="3" required>{{ $kandidat->visi }}</textarea>

                                    <label>Misi</label>
                                    <textarea name="misi" rows="3" required>{{ $kandidat->misi }}</textarea>

                                    <label>Ganti Foto (opsional, kosongkan kalau tidak ingin diganti)</label>
                                    <input type="file" name="foto" accept="image/*" style="margin-bottom:14px;">

                                    <button type="submit" class="btn btn-primary" style="width:100%;">Simpan Perubahan</button>
                                </form>
                            </td>
                        </tr>
                    @endunless
                @empty
                    <tr><td colspan="4" style="color:#999;">Belum ada kandidat untuk periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        function toggleEditRow(id) {
            const row = document.getElementById(id);
            row.style.display = row.style.display === 'table-row' ? 'none' : 'table-row';
        }
    </script>

    @unless ($periode->isRunning())
        <div class="card" style="max-width:520px;">
            <strong style="font-size:14px;">Tambah Kandidat</strong>
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