@extends('layouts.admin')

@section('title', 'Akun Terkunci & Pengaduan')

@section('content')
    <h1>Akun Terkunci &amp; Pengaduan</h1>

    <div class="card">
        <strong style="font-size:14px;">Buka Blokir Percobaan Login (Belum Pernah Berhasil Login)</strong>
        <p style="font-size:12px; color:#888; margin-top:4px;">
            Khusus untuk orang yang BELUM PERNAH sukses login sama sekali -- akun mereka belum
            tercatat di sistem, jadi tidak muncul di daftar di bawah. Blokir jenis ini otomatis
            hilang sendiri setelah 15 menit, tapi bisa dibuka manual di sini kalau mereka tidak mau menunggu.
        </p>
        <form method="POST" action="{{ route('admin.pengaduan.bukaBlokirEmail') }}" style="max-width:420px; margin-top:12px; display:flex; gap:10px;">
            @csrf
            <input type="email" name="email" placeholder="email@contoh.com" required style="margin-bottom:0;">
            <button type="submit" class="btn btn-primary" style="white-space:nowrap;">Buka Blokir</button>
        </form>
    </div>

    <div class="card">
        <strong style="font-size:14px;">Akun Terkunci Saat Ini</strong>
        <p style="font-size:12px; color:#888; margin-top:4px;">
            Diambil langsung dari data sistem -- paling akurat. Kalau kamu sudah yakin identitas pemilih
            (lewat WA, ketemu langsung, dsb), bisa langsung buka kunci dari sini tanpa lewat pengaduan.
        </p>
        <table style="margin-top:12px;">
            <thead><tr><th>Identifier</th><th>Nama</th><th>Percobaan Gagal</th><th></th></tr></thead>
            <tbody>
                @forelse ($terkunci as $pemilih)
                    <tr>
                        <td>{{ $pemilih->identifier }}</td>
                        <td>{{ $pemilih->nama }}</td>
                        <td>{{ $pemilih->pivot->percobaan_gagal }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.pengaduan.unlockLangsung', [$periodeTerbaru, $pemilih]) }}" style="margin:0;"
                                onsubmit="return confirm('Buka kunci akun {{ $pemilih->identifier }}?');">
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

    <div class="card">
        <strong style="font-size:14px;">Daftar Pengaduan Masuk</strong>
        <p style="font-size:12px; color:#888; margin-top:4px;">
            Pengaduan lewat form publik, lengkap dengan bukti foto selfie & kontak WA/email.
        </p>
        <table style="margin-top:12px;">
            <thead><tr><th>Identifier</th><th>Nama</th><th>Email</th><th>No. HP/WA</th><th>Periode</th><th>Status</th><th></th></tr></thead>
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
                    <tr><td colspan="7" style="color:#999;">Belum ada pengaduan masuk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $pengaduans->links() }}
@endsection