@extends('layouts.admin')

@section('title', 'LPJ Divisi Saya')

@section('content')
    <h1>LPJ — {{ $lpj->namaDivisi() ?? \App\Models\Lpj::NAMA_DIVISI[Auth::guard('admin')->user()->role] }}</h1>
    <p style="font-size:13px; color:#888; margin-top:-14px;">{{ $periode->judul }}</p>

    @if ($lpj->exists)
        <div class="card" style="margin-bottom:16px;">
            <span class="badge {{ $lpj->status === 'disahkan' ? 'running' : ($lpj->status === 'direvisi' ? 'stopped' : 'freeze') }}">
                {{ strtoupper($lpj->status) }}
            </span>
            @if ($lpj->status === 'direvisi' && $lpj->catatan_ketua)
                <p style="margin:10px 0 0; font-size:13px; color:#a56a00;">
                    <strong>Catatan revisi dari Ketua:</strong> {{ $lpj->catatan_ketua }}
                </p>
            @endif
            @if ($lpj->status === 'disahkan')
                <p style="margin:10px 0 0; font-size:13px; color:#1e7e34;">
                    Disahkan oleh {{ $lpj->disahkanOleh->nama ?? '-' }} pada {{ $lpj->disahkan_pada?->format('d M Y H:i') }}.
                </p>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('admin.lpj.update', $periode) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card">
            <strong style="font-size:14px;">Cover</strong>
            <div style="margin-top:14px;">
                <label>PJ Divisi</label>
                <input type="text" name="pj_nama" value="{{ old('pj_nama', $lpj->pj_nama) }}" {{ ! $lpj->isEditable() ? 'disabled' : '' }} required>

                <label>Anggota Divisi (satu nama per baris)</label>
                <textarea name="anggota_divisi" rows="3" {{ ! $lpj->isEditable() ? 'disabled' : '' }}>{{ old('anggota_divisi', $lpj->anggota_divisi) }}</textarea>
            </div>
        </div>

        <div class="card">
            <strong style="font-size:14px;">Isi Laporan</strong>
            <div style="margin-top:14px;">
                <label>Ringkasan</label>
                <textarea name="ringkasan" rows="3" {{ ! $lpj->isEditable() ? 'disabled' : '' }}>{{ old('ringkasan', $lpj->ringkasan) }}</textarea>

                <label>Tugas Pokok dan Fungsi</label>
                <textarea name="tugas_pokok_fungsi" rows="3" {{ ! $lpj->isEditable() ? 'disabled' : '' }}>{{ old('tugas_pokok_fungsi', $lpj->tugas_pokok_fungsi) }}</textarea>

                <label>Parameter Keberhasilan</label>
                <textarea name="parameter_keberhasilan" rows="3" {{ ! $lpj->isEditable() ? 'disabled' : '' }}>{{ old('parameter_keberhasilan', $lpj->parameter_keberhasilan) }}</textarea>

                <label>Kritik</label>
                <textarea name="kritik" rows="2" {{ ! $lpj->isEditable() ? 'disabled' : '' }}>{{ old('kritik', $lpj->kritik) }}</textarea>

                <label>Saran</label>
                <textarea name="saran" rows="2" {{ ! $lpj->isEditable() ? 'disabled' : '' }}>{{ old('saran', $lpj->saran) }}</textarea>

                <label>Faktor Pendukung</label>
                <textarea name="faktor_pendukung" rows="2" {{ ! $lpj->isEditable() ? 'disabled' : '' }}>{{ old('faktor_pendukung', $lpj->faktor_pendukung) }}</textarea>

                <label>Faktor Penghambat</label>
                <textarea name="faktor_penghambat" rows="2" {{ ! $lpj->isEditable() ? 'disabled' : '' }}>{{ old('faktor_penghambat', $lpj->faktor_penghambat) }}</textarea>

                <label>Evaluasi</label>
                <textarea name="evaluasi" rows="3" {{ ! $lpj->isEditable() ? 'disabled' : '' }}>{{ old('evaluasi', $lpj->evaluasi) }}</textarea>
            </div>
        </div>

        <div class="card">
            <strong style="font-size:14px;">Laporan Anggaran Biaya <span style="font-weight:400; color:#888; font-size:12px;">(opsional, isi kalau ada pengeluaran)</span></strong>
            <div style="margin-top:14px;">
                <label>Rincian Anggaran</label>
                <textarea name="rincian_anggaran" rows="3" {{ ! $lpj->isEditable() ? 'disabled' : '' }}>{{ old('rincian_anggaran', $lpj->rincian_anggaran) }}</textarea>
            </div>
        </div>

        <div class="card">
            <strong style="font-size:14px;">Berkas LPJ</strong>
            <div style="margin-top:14px;">
                <label>File Wajib <span style="color:var(--error); font-weight:600;">*</span> <span style="font-weight:400; color:#888; font-size:12px;">(dokumen inti LPJ — PDF/gambar, maks 5MB)</span></label>
                @if ($lpj->file_wajib)
                    <p style="font-size:12px; margin:0 0 8px;">
                        File saat ini: <a href="{{ Storage::url($lpj->file_wajib) }}" target="_blank">lihat file</a> — upload file baru di bawah untuk mengganti.
                    </p>
                @endif
                <input type="file" name="file_wajib" accept=".pdf,docx,excel,.jpg,.jpeg,.png" {{ ! $lpj->isEditable() ? 'disabled' : '' }} {{ $lpj->file_wajib ? '' : 'required' }} style="margin-bottom:14px;">

                <label>File Opsional <span style="font-weight:400; color:#888; font-size:12px;">(lampiran tambahan, mis. nota/bukti pengeluaran — kalau ada)</span></label>
                @if ($lpj->file_opsional)
                    <p style="font-size:12px; margin:0 0 8px;">
                        File saat ini: <a href="{{ Storage::url($lpj->file_opsional) }}" target="_blank">lihat file</a> — upload file baru di bawah untuk mengganti.
                    </p>
                @endif
                <input type="file" name="file_opsional" accept=".pdf,docx,excel,.jpg,.jpeg,.png" {{ ! $lpj->isEditable() ? 'disabled' : '' }} style="margin-bottom:14px;">
            </div>
        </div>

        @if ($lpj->isEditable())
            <div style="display:flex; gap:10px;">
                <button type="submit" name="aksi" value="draft" class="btn btn-outline" style="flex:1;">Simpan sebagai Draft</button>
                <button type="submit" name="aksi" value="ajukan" class="btn btn-primary" style="flex:1;"
                    onclick="return confirm('Ajukan LPJ ini ke Ketua Pelaksana? Pastikan semua bagian sudah terisi.');">
                    Ajukan ke Ketua
                </button>
            </div>
        @else
            <p style="text-align:center; color:#888; font-size:13px;">LPJ ini sudah disahkan dan tidak bisa diubah lagi.</p>
        @endif
    </form>
@endsection