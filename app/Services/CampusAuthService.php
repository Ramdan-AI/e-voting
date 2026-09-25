<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CampusAuthService
{
    public function verify(string $email, string $password, string $tanggalLahir): array
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.stimik.key'),
            'Accept'    => 'application/json',
        ])
            ->timeout(10)
            ->post(config('services.stimik.url') . '/user/check', [
                'email'           => $email,
                'password'        => $password,
                'tanggal_lahir'   => $tanggalLahir,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Tidak bisa menghubungi server kampus. Coba lagi beberapa saat.');
        }

        $body = $response->json();

        if (($body['status'] ?? false) !== true) {
            return [
                'valid' => false,
                'message' => 'Email, password, atau tanggal lahir tidak sesuai.',
            ];
        }

        return [
            'valid' => true,
            'id' => $body['data']['id'] ?? null,
            'nama' => $body['data']['name'] ?? null,
            'email' => $body['data']['email'] ?? null,
            'tanggal_lahir' => $body['data']['tanggal_lahir'] ?? null,
            'tipe' => $body['data']['type_user'] ?? null,
        ];
    }
}
