<p align="center">
<img src="docs/images/logo-kpum.png" width="180">
</p>

<h1 align="center">
E-Voting KPUM
</h1>

<p align="center">
Sistem E-Voting STMIK Mardira Indonesia
</p>

 # E-Voting KPUM STMIK Mardira Indonesia

Sistem E-Voting berbasis web yang dikembangkan menggunakan Laravel 12 sebagai media pemilihan Ketua BEM/KPUM STMIK Mardira Indonesia.

Project ini dibuat sebagai simulasi sistem pemungutan suara elektronik dengan memperhatikan keamanan login, pencatatan aktivitas admin, serta transparansi proses pemilihan.

---

## ✨ Fitur

### Public

- Landing Page
- Statistik partisipasi
- Daftar kandidat
- Visi dan Misi kandidat
- Login Pemilih
- OTP Verification melalui Email
- Voting
- Form Pengaduan Akun

---

### Admin

- Dashboard
- Manajemen Periode
- Manajemen Kandidat
- Manajemen Pemilih
- Log Presensi
- Audit Log Aktivitas
- Review LPJ
- Pengelolaan Pengaduan Akun
- Freeze & Stop Periode
- Role Based Access

---

## Security

Project ini menerapkan beberapa mekanisme keamanan:

- Password menggunakan Hash Laravel
- OTP Email Login
- Session Authentication
- CSRF Protection
- Role Middleware
- Audit Log
- Login Lock setelah beberapa kali gagal
- Validasi File Upload

---

## Tech Stack

- Laravel 12
- PHP 8.4
- MySQL
- Blade Template
- HTML
- CSS
- JavaScript

---

## Installation

Clone repository

```bash
git clone https://github.com/Ramdan-AI/e-voting.git
```

Masuk folder

```bash
cd E-Voting
```

Install dependency

```bash
composer install
```

Copy environment

```bash
cp .env.example .env
```

Generate key

```bash
php artisan key:generate
```

Migrasi database

```bash
php artisan migrate:fresh --seed
```

Jalankan server

```bash
php artisan serve
```

## Author

**Muhammad Ramdan**

Teknik Informatika

STMIK Mardira Indonesia

## License

This project is created for educational purposes.

Copyright © 2026 Muhammad Ramdan