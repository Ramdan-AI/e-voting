<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CampusAuthService
{
    /**
     * Satu-satunya titik yang tahu detail teknis API kampus (URL, format
     * parameter, format response). Kalau nanti IT ubah detailnya, cukup
     * ubah isi method ini -- AuthController dan alur login lainnya tidak
     * perlu disentuh sama sekali.
     *
     * Return array:
     *   ['valid' => true, 'nim' => ..., 'email' => ..., 'tanggal_lahir' => ...]
     *   ['valid' => false, 'message' => '...']
     *
     * @throws \RuntimeException kalau API tidak bisa dihubungi sama sekali
     * (server kampus down, timeout, dsb) -- ini beda dari "kredensial salah".
     */
    public function verify(string $email, string $password, string $tanggalLahir): array
    {
        $response = Http::timeout(10)->get(config('services.stimik.url') . 'db-users', [
            'email' => $email,
            'password' => $password,
            'tanggal_lahir' => $tanggalLahir,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Tidak bisa menghubungi server kampus. Coba lagi beberapa saat.');
        }

        $body = $response->json();

        if (($body['status'] ?? null) !== 'success') {
            return [
                'valid' => false,
                'message' => $body['message'] ?? 'Email, password, atau tanggal lahir tidak cocok.',
            ];
        }

        return [
            'valid' => true,
            'nim' => $body['data']['nim'] ?? null,
            'nama' => $body['data']['nama'] ?? null,
            'email' => $body['data']['email'] ?? $email,
            'tanggal_lahir' => $body['data']['tanggal_lahir'] ?? $tanggalLahir,
        ];
    }
}
