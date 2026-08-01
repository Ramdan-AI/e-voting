<?php

namespace App\Http\Controllers;

use App\Models\AuditLogAdmin;
use App\Models\Lpj;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LpjController extends Controller
{
    /**
     * [ADMIN - divisi] Form isi/edit LPJ milik divisi admin yang sedang
     * login, untuk satu periode. Kalau belum ada baris LPJ-nya, dibuatkan
     * kosong dulu (belum disimpan ke DB sampai admin submit form).
     */
    public function edit(Periode $periode)
    {
        $admin = Auth::guard('admin')->user();

        $lpj = Lpj::firstOrNew([
            'periode_id' => $periode->id,
            'divisi' => $admin->role,
        ]);

        // Default nama PJ diisi otomatis dari nama admin, tapi tetap bisa
        // diedit manual (kadang PJ berbeda dari pemegang akun sistem).
        if (! $lpj->exists) {
            $lpj->pj_nama = $admin->nama;
        }

        return view('admin.lpj.edit', compact('periode', 'lpj'));
    }

    /**
     * [ADMIN - divisi] Simpan LPJ, bisa sebagai draft atau langsung diajukan
     * (dibedakan lewat tombol submit yang beda name/value di form).
     */
    public function update(Request $request, Periode $periode)
    {
        $admin = Auth::guard('admin')->user();

        $lpj = Lpj::firstOrNew([
            'periode_id' => $periode->id,
            'divisi' => $admin->role,
        ]);

        if (! $lpj->isEditable()) {
            return back()->with('error', 'LPJ ini sudah disahkan dan tidak bisa diubah lagi.');
        }

        $validated = $request->validate([
            'pj_nama' => ['required', 'string', 'max:100'],
            'anggota_divisi' => ['nullable', 'string'],
            'ringkasan' => ['nullable', 'string'],
            'tugas_pokok_fungsi' => ['nullable', 'string'],
            'parameter_keberhasilan' => ['nullable', 'string'],
            'kritik' => ['nullable', 'string'],
            'saran' => ['nullable', 'string'],
            'faktor_pendukung' => ['nullable', 'string'],
            'faktor_penghambat' => ['nullable', 'string'],
            'evaluasi' => ['nullable', 'string'],
            'rincian_anggaran' => ['nullable', 'string'],
            'file_wajib' => [$lpj->file_wajib ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'file_opsional' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('file_wajib')) {
            $validated['file_wajib'] = $request->file('file_wajib')->store('lpj-files', 'public');
        } else {
            unset($validated['file_wajib']); // pertahankan file lama kalau tidak upload baru
        }

        if ($request->hasFile('file_opsional')) {
            $validated['file_opsional'] = $request->file('file_opsional')->store('lpj-files', 'public');
        } else {
            unset($validated['file_opsional']);
        }

        $lpj->fill([
            ...$validated,
            'periode_id' => $periode->id,
            'divisi' => $admin->role,
        ]);

        // Tombol submit form ada dua: "simpan_draft" dan "ajukan".
        // Kalau sebelumnya berstatus 'direvisi' lalu diajukan ulang, statusnya
        // kembali ke 'diajukan' -- bukan tetap 'direvisi'.
        if ($request->input('aksi') === 'ajukan') {
            $lpj->status = 'diajukan';
            $lpj->diajukan_pada = now();
        } else {
            $lpj->status = 'draft';
        }

        $lpj->save();

        AuditLogAdmin::catat(
            $lpj->status === 'diajukan' ? 'ajukan_lpj' : 'simpan_draft_lpj',
            "{$admin->nama} (" . $lpj->namaDivisi() . ") " . ($lpj->status === 'diajukan' ? 'mengajukan' : 'menyimpan draft') . " LPJ untuk periode #{$periode->id} ({$periode->judul}).",
            $periode->id
        );

        $pesan = $lpj->status === 'diajukan'
            ? 'LPJ berhasil diajukan ke Ketua Pelaksana.'
            : 'Draft LPJ berhasil disimpan.';

        return redirect()->route('admin.lpj.edit', $periode)->with('success', $pesan);
    }

    /**
     * [KETUA] Daftar status LPJ semua divisi untuk satu periode.
     */
    public function index(Periode $periode)
    {
        $lpjs = Lpj::where('periode_id', $periode->id)->get()->keyBy('divisi');

        // Supaya divisi yang BELUM sama sekali bikin LPJ tetap kelihatan
        // di daftar (bukan cuma yang sudah ada baris di DB).
        $semuaDivisi = Lpj::NAMA_DIVISI;

        return view('admin.lpj.index', compact('periode', 'lpjs', 'semuaDivisi'));
    }

    /**
     * [KETUA] Lihat detail satu LPJ divisi tertentu.
     */
    public function show(Periode $periode, Lpj $lpj)
    {
        return view('admin.lpj.show', compact('periode', 'lpj'));
    }

    /**
     * [KETUA] Export satu LPJ jadi PDF. Dipakai untuk arsip/lampiran laporan
     * akhir ke pihak kampus -- format PDF, bukan cuma dilihat di dashboard.
     */
    public function export(Periode $periode, Lpj $lpj)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.lpj.pdf', compact('periode', 'lpj'));

        $namaFile = 'LPJ-' . str_replace(' ', '-', $lpj->namaDivisi()) . '-' . $periode->id . '.pdf';

        return $pdf->download($namaFile);
    }

    /**
     * [KETUA] Export SEMUA LPJ (yang sudah pernah diajukan, bukan draft)
     * jadi SATU PDF gabungan -- biar langsung siap dijadikan dokumentasi
     * fisik untuk diajukan ke atasan, tidak perlu download satu-satu.
     */
    public function exportSemua(Periode $periode)
    {
        $lpjs = Lpj::where('periode_id', $periode->id)
            ->where('status', '!=', 'draft')
            ->orderByRaw("FIELD(divisi, 'sekretaris', 'bendahara', 'divisi_regulasi_verifikasi', 'divisi_acara_pengawasan', 'divisi_teknis_pemilihan', 'divisi_humas_media')")
            ->get();

        if ($lpjs->isEmpty()) {
            return back()->with('error', 'Belum ada LPJ yang diajukan untuk periode ini.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.lpj.pdf-semua', compact('periode', 'lpjs'));

        AuditLogAdmin::catat(
            'export_semua_lpj',
            "Meng-export gabungan {$lpjs->count()} LPJ untuk periode #{$periode->id} ({$periode->judul}).",
            $periode->id
        );

        return $pdf->download("LPJ-Gabungan-Periode-{$periode->id}.pdf");
    }

    /**
     * [KETUA] Sahkan LPJ -- aksi final, tidak bisa diedit lagi setelah ini.
     */
    public function sahkan(Periode $periode, Lpj $lpj)
    {
        if ($lpj->status !== 'diajukan') {
            return back()->with('error', 'LPJ ini belum diajukan, tidak bisa disahkan.');
        }

        $lpj->update([
            'status' => 'disahkan',
            'disahkan_oleh' => Auth::guard('admin')->id(),
            'disahkan_pada' => now(),
        ]);

        AuditLogAdmin::catat(
            'sahkan_lpj',
            "Mengesahkan LPJ {$lpj->namaDivisi()} untuk periode #{$periode->id} ({$periode->judul}).",
            $periode->id
        );

        return back()->with('success', "LPJ {$lpj->namaDivisi()} berhasil disahkan.");
    }

    /**
     * [KETUA] Minta revisi -- LPJ dikembalikan ke PJ divisi dengan catatan.
     */
    public function mintaRevisi(Request $request, Periode $periode, Lpj $lpj)
    {
        $validated = $request->validate([
            'catatan_ketua' => ['required', 'string'],
        ]);

        $lpj->update([
            'status' => 'direvisi',
            'catatan_ketua' => $validated['catatan_ketua'],
        ]);

        AuditLogAdmin::catat(
            'minta_revisi_lpj',
            "Meminta revisi LPJ {$lpj->namaDivisi()} untuk periode #{$periode->id} ({$periode->judul}).",
            $periode->id
        );

        return back()->with('success', "Permintaan revisi untuk LPJ {$lpj->namaDivisi()} berhasil dikirim.");
    }
}
