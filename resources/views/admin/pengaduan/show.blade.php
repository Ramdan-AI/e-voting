@extends('layouts.admin')

@section('title', 'Detail Pengaduan')

@section('content')
    <div style="margin-bottom:20px;">
        <a href="{{ route('admin.pengaduan.index') }}" style="font-size:14px; color:white; text-decoration:none;">&larr; Kembali ke Daftar Pengaduan</a>
        <h1 style="margin:8px 0 0;">Pengaduan — {{ $pengaduan->nama }}</h1>
        <span class="badge {{ $pengaduan->status === 'disetujui' ? 'running' : ($pengaduan->status === 'ditolak' ? 'stopped' : 'freeze') }}">
            {{ strtoupper($pengaduan->status) }}
        </span>
    </div>

    <div class="card" style="display:flex; gap:24px; flex-wrap:wrap;">
        <div style="flex:1; min-width:200px;">
            <p><strong>Identifier:</strong> {{ $pengaduan->identifier }}</p>
            <p><strong>Nama:</strong> {{ $pengaduan->nama }}</p>
            <p><strong>Email:</strong> {{ $pengaduan->email }}</p>
            <p>
                <strong>No. HP/WA:</strong> {{ $pengaduan->no_hp }}
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', preg_replace('/^0/', '62', $pengaduan->no_hp)) }}" target="_blank" class="btn btn-outline" style="margin-left:8px; padding:3px 10px; font-size:11px;">Chat WA</a>
            </p>
            <p style="margin-bottom:0;"><strong>Periode saat pengaduan dikirim:</strong> {{ $pengaduan->periode->judul ?? '—' }}</p>
        </div>
        <div>
            <p style="font-size:12px; color:#888; margin-bottom:6px;">Foto Selfie (Bukti Identitas)</p>
            <img src="{{ Storage::url($pengaduan->foto_selfie) }}" alt="Selfie {{ $pengaduan->nama }}" style="width:160px; height:160px; object-fit:cover; border-radius:10px; border:1px solid var(--border);">
        </div>
    </div>

    <div class="card">
        <strong style="font-size:14px;">Status Voting Saat Ini</strong>
        @if (! $pengaduan->periode_id)
            <p style="margin-top:10px; color:#a56a00;">Tidak ada periode tercatat saat pengaduan ini dikirim.</p>
        @elseif (! $pivot)
            <p style="margin-top:10px; color:var(--error);">
                Identifier <strong>{{ $pengaduan->identifier }}</strong> TIDAK ditemukan sebagai pemilih terdaftar pada periode {{ $pengaduan->periode->judul }}.
                Kemungkinan salah ketik NIM, atau memang tidak terdaftar di DPT.
            </p>
        @elseif ($pivot->status_akses === 'sudah_voting')
            <p style="margin-top:10px; color:var(--error);">
                Pemilih ini <strong>SUDAH</strong> memberikan suara pada periode ini. Tidak bisa dibuka kunci / login ulang.
            </p>
        @elseif ($pivot->status_akses === 'terkunci')
            <p style="margin-top:10px; color:#a56a00;">
                Akun ini <strong>terkunci</strong> ({{ $pivot->percobaan_gagal }}x percobaan gagal) dan <strong>belum</strong> memberikan suara. Aman untuk dibuka kembali.
            </p>
        @else
            <p style="margin-top:10px; color:var(--success);">
                Akun ini tidak dalam status terkunci, dan belum memberikan suara.
            </p>
        @endif
    </div>

    @if ($pengaduan->status === 'pending')
        <div class="card">
            <strong style="font-size:14px;">Tindakan</strong>

            <form method="POST" action="{{ route('admin.pengaduan.setujui', $pengaduan) }}" style="margin-top:14px;"
                onsubmit="return confirm('Setujui pengaduan ini dan buka kunci akunnya?');">
                @csrf
                @method('PATCH')
                <label>Catatan (opsional)</label>
                <textarea name="catatan_admin" rows="2"></textarea>
                <button type="submit" class="btn btn-primary" style="width:100%;">Setujui &amp; Buka Kunci</button>
            </form>

            <form method="POST" action="{{ route('admin.pengaduan.tolak', $pengaduan) }}" style="margin-top:14px;">
                @csrf
                @method('PATCH')
                <label>Alasan Penolakan (wajib)</label>
                <textarea name="catatan_admin" rows="2" required></textarea>
                <button type="submit" class="btn btn-outline" style="width:100%;">Tolak Pengaduan</button>
            </form>
        </div>
    @else
        <div class="card">
            <strong style="font-size:14px;">Sudah Diproses</strong>
            <p style="margin-top:10px; font-size:13px;">
                Oleh {{ $pengaduan->diprosesOleh->nama ?? '-' }} pada {{ $pengaduan->diproses_pada?->format('d M Y H:i') }}.
            </p>
            @if ($pengaduan->catatan_admin)
                <p style="margin-top:6px; font-size:13px;"><strong>Catatan:</strong> {{ $pengaduan->catatan_admin }}</p>
            @endif
        </div>
    @endif
@endsection