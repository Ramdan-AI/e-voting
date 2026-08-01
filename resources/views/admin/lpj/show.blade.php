@extends('layouts.admin')

@section('title', 'Detail LPJ')

@section('content')
    <div style="margin-bottom:20px;">
        <a href="{{ route('admin.lpj.index', $periode) }}" style="font-size:13px; color:var(--accent); text-decoration:none;">&larr; Kembali ke Daftar LPJ</a>
        <h1 style="margin:8px 0 0;">LPJ — {{ $lpj->namaDivisi() }}</h1>
        <span class="badge {{ $lpj->status === 'disahkan' ? 'running' : ($lpj->status === 'direvisi' ? 'stopped' : 'freeze') }}">
            {{ strtoupper($lpj->status) }}
        </span>
    </div>

    <div class="card">
        <p><strong>PJ Divisi:</strong> {{ $lpj->pj_nama }}</p>
        <p><strong>Anggota Divisi:</strong><br>{!! nl2br(e($lpj->anggota_divisi)) !!}</p>
    </div>

    <div class="card">
        <p><strong>Ringkasan</strong><br>{{ $lpj->ringkasan ?: '—' }}</p>
        <p><strong>Tugas Pokok dan Fungsi</strong><br>{{ $lpj->tugas_pokok_fungsi ?: '—' }}</p>
        <p><strong>Parameter Keberhasilan</strong><br>{{ $lpj->parameter_keberhasilan ?: '—' }}</p>
        <p><strong>Kritik</strong><br>{{ $lpj->kritik ?: '—' }}</p>
        <p><strong>Saran</strong><br>{{ $lpj->saran ?: '—' }}</p>
        <p><strong>Faktor Pendukung</strong><br>{{ $lpj->faktor_pendukung ?: '—' }}</p>
        <p><strong>Faktor Penghambat</strong><br>{{ $lpj->faktor_penghambat ?: '—' }}</p>
        <p style="margin-bottom:0;"><strong>Evaluasi</strong><br>{{ $lpj->evaluasi ?: '—' }}</p>
    </div>

    <div class="card">
        <strong>Laporan Anggaran Biaya</strong>
        <p style="margin-top:10px;">{{ $lpj->rincian_anggaran ?: 'Tidak ada pengeluaran dilaporkan.' }}</p>
    </div>

    <div class="card">
        <strong>Berkas LPJ</strong>
        <div style="display:flex; gap:10px; margin-top:14px;">
            @if ($lpj->file_wajib)
                <a href="{{ Storage::url($lpj->file_wajib) }}" target="_blank" class="btn btn-primary">Lihat File Wajib</a>
            @else
                <span style="font-size:13px; color:#999;">File wajib belum diunggah.</span>
            @endif
            @if ($lpj->file_opsional)
                <a href="{{ Storage::url($lpj->file_opsional) }}" target="_blank" class="btn btn-outline">Lihat File Opsional</a>
            @endif
        </div>
    </div>

    @if ($lpj->status === 'diajukan')
        <div class="card">
            <strong style="font-size:14px;">Tindakan</strong>
            <div style="display:flex; gap:10px; margin-top:14px;">
                <form method="POST" action="{{ route('admin.lpj.sahkan', [$periode, $lpj]) }}" style="flex:1;"
                    onsubmit="return confirm('Sahkan LPJ ini? Setelah disahkan, LPJ tidak bisa diubah lagi.');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-primary" style="width:100%;">Sahkan LPJ</button>
                </form>
            </div>

            <form method="POST" action="{{ route('admin.lpj.mintaRevisi', [$periode, $lpj]) }}" style="margin-top:14px;">
                @csrf
                @method('PATCH')
                <label>Catatan Revisi (wajib diisi kalau minta revisi)</label>
                <textarea name="catatan_ketua" rows="3" required></textarea>
                <button type="submit" class="btn btn-outline" style="width:100%;">Minta Revisi</button>
            </form>
        </div>
    @elseif ($lpj->status === 'disahkan')
        <div class="alert success">
            Disahkan oleh {{ $lpj->disahkanOleh->nama ?? '-' }} pada {{ $lpj->disahkan_pada?->format('d M Y H:i') }}.
        </div>
    @elseif ($lpj->catatan_ketua)
        <div class="card">
            <strong style="font-size:14px;">Catatan Revisi Terakhir</strong>
            <p style="margin-top:10px;">{{ $lpj->catatan_ketua }}</p>
        </div>
    @endif
@endsection