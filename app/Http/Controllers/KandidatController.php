<?php

namespace App\Http\Controllers;

use App\Models\AuditLogAdmin;
use App\Models\Kandidat;
use App\Models\Periode;
use Illuminate\Http\Request;

class KandidatController extends Controller
{
    /**
     * [PUBLIK] Daftar kandidat untuk periode yang sedang aktif.
     * Dipakai landing page untuk menampilkan pilihan calon saat login sukses.
     */
    public function publikAktif()
    {
        $periode = Periode::aktif();

        if (! $periode) {
            return response()->json(['message' => 'Tidak ada periode yang sedang berjalan.'], 404);
        }

        $kandidats = $periode->kandidats()
            ->orderBy('nomor_urut')
            ->get(['id', 'nama', 'nomor_urut', 'visi', 'misi', 'foto']);

        return response()->json($kandidats);
    }

    /**
     * [ADMIN] Daftar kandidat untuk satu periode tertentu (dashboard admin).
     */
    public function index(Periode $periode)
    {
        $kandidats = $periode->kandidats()->orderBy('nomor_urut')->get();

        return view('admin.kandidat.index', compact('periode', 'kandidats'));
    }

    /**
     * [ADMIN] Simpan kandidat baru untuk suatu periode.
     * Foto disimpan via storage disk 'public', path-nya yang disimpan ke DB.
     */
    public function store(Request $request, Periode $periode)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'nomor_urut' => [
                'required',
                'string',
                'max:10',
                // nomor urut harus unik di dalam periode yang sama, boleh sama di periode berbeda
                function ($attribute, $value, $fail) use ($periode) {
                    if ($periode->kandidats()->where('nomor_urut', $value)->exists()) {
                        $fail('Nomor urut ini sudah dipakai kandidat lain pada periode ini.');
                    }
                },
            ],
            'visi' => ['required', 'string'],
            'misi' => ['required', 'string'],
            'foto' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('foto')->store('kandidat', 'public');

        $kandidat = $periode->kandidats()->create([
            'nama' => $validated['nama'],
            'nomor_urut' => $validated['nomor_urut'],
            'visi' => $validated['visi'],
            'misi' => $validated['misi'],
            'foto' => $path,
        ]);

        AuditLogAdmin::catat(
            'tambah_kandidat',
            "Menambahkan kandidat {$kandidat->nama} (#{$kandidat->nomor_urut}) pada periode #{$periode->id} ({$periode->judul}).",
            $periode->id
        );

        return redirect()
            ->route('admin.kandidat.index', $periode)
            ->with('success', "Kandidat {$kandidat->nama} berhasil ditambahkan.");
    }

    /**
     * [ADMIN] Update data kandidat.
     * Hanya boleh dilakukan selama periode belum 'running', supaya data
     * calon tidak berubah di tengah proses pemilihan berlangsung.
     */
    public function update(Request $request, Periode $periode, Kandidat $kandidat)
    {
        if ($periode->isRunning()) {
            return back()->with('error', 'Data kandidat tidak bisa diubah saat periode sedang berjalan (running).');
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'nomor_urut' => ['required', 'string', 'max:10'],
            'visi' => ['required', 'string'],
            'misi' => ['required', 'string'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('kandidat', 'public');
        }

        $kandidat->update($validated);

        AuditLogAdmin::catat(
            'ubah_kandidat',
            "Mengubah data kandidat {$kandidat->nama} (#{$kandidat->nomor_urut}) pada periode #{$periode->id} ({$periode->judul}).",
            $periode->id
        );

        return back()->with('success', "Data kandidat {$kandidat->nama} berhasil diperbarui.");
    }

    /**
     * [ADMIN] Hapus kandidat. Sama seperti update, dibatasi saat periode running.
     */
    public function destroy(Periode $periode, Kandidat $kandidat)
    {
        if ($periode->isRunning()) {
            return back()->with('error', 'Kandidat tidak bisa dihapus saat periode sedang berjalan (running).');
        }

        $namaKandidat = $kandidat->nama;
        $kandidat->delete();

        AuditLogAdmin::catat(
            'hapus_kandidat',
            "Menghapus kandidat {$namaKandidat} dari periode #{$periode->id} ({$periode->judul}).",
            $periode->id
        );

        return back()->with('success', 'Kandidat berhasil dihapus.');
    }
}
