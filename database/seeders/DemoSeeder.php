<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Kandidat;
use App\Models\Pemilih;
use App\Models\Periode;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Seeder khusus TESTING LOKAL. Membuat 7 akun admin (satu per role
     * kepanitiaan), 1 periode siap-vote (status running, jadwal voting
     * sudah diset), 2 kandidat, dan 3 pemilih dummy yang sudah terdaftar
     * di periode tersebut.
     *
     * Jalankan dengan: php artisan db:seed --class=DemoSeeder
     * atau otomatis lewat: php artisan migrate:fresh --seed
     * (kalau sudah didaftarkan di DatabaseSeeder, lihat instruksi di bawah).
     */
    public function run(): void
    {
        $admins = [
            ['nama' => 'Ketua Testing', 'email' => 'ketua@test.com', 'role' => 'ketua_pelaksana'],
            ['nama' => 'Sekretaris Testing', 'email' => 'sekretaris@test.com', 'role' => 'sekretaris'],
            ['nama' => 'Bendahara Testing', 'email' => 'bendahara@test.com', 'role' => 'bendahara'],
            ['nama' => 'Div. Regulasi Testing', 'email' => 'regulasi@test.com', 'role' => 'divisi_regulasi_verifikasi'],
            ['nama' => 'Div. Acara Testing', 'email' => 'acara@test.com', 'role' => 'divisi_acara_pengawasan'],
            ['nama' => 'Div. Teknis Testing', 'email' => 'teknis@test.com', 'role' => 'divisi_teknis_pemilihan'],
            ['nama' => 'Div. Humas Testing', 'email' => 'humas@test.com', 'role' => 'divisi_humas_media'],
        ];

        foreach ($admins as $data) {
            Admin::create([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'password' => 'password123',
                'role' => $data['role'],
            ]);
        }

        $periode = Periode::create([
            'judul' => 'KPUM 2026 (Demo)',
            'status' => 'running',
            'tampilkan_hasil' => false,
            'start_date' => now()->subDays(3),
            'end_date' => now()->addDays(7),
            'waktu_mulai_voting' => now()->subHour(),
            'waktu_selesai_voting' => now()->addHours(6),
        ]);

        // 3 pemilih dummy: identifier 11111111 s.d. 33333333, password sama semua.
        foreach (range(1, 3) as $i) {
            $identifier = str_repeat((string) $i, 8);

            $pemilih = Pemilih::create([
                'identifier' => $identifier,
                'nama' => "Pemilih Testing {$i}",
                'password' => 'password123',
            ]);

            $periode->pemilihs()->attach($pemilih->id, [
                'status_akses' => 'belum_voting',
                'percobaan_gagal' => 0,
            ]);
        }

        $this->command->info('Demo data berhasil dibuat:');
        $this->command->info('- 7 Admin (password sama semua: password123):');
        foreach ($admins as $data) {
            $this->command->info("    {$data['email']} — {$data['role']}");
        }
        $this->command->info('- Pemilih: 11111111, 22222222, 33333333 / password123 (semua sama)');
        $this->command->info("- Periode aktif: {$periode->judul} (id: {$periode->id}, status: running)");
    }
}
