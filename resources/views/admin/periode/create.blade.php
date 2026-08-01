@extends('layouts.admin')

@section('title', 'Periode Baru')

@section('content')
    <h1 style="text-align: center;">Buat Periode Baru</h1>

    <div class="card" style="max-width:480px; item-align:center; margin:0 auto;">
        <p style="font-size:12px; color:#888; margin-top:0;">
            start_date/end_date di sini adalah rentang keseluruhan acara KPUM
            (rapat pertama panitia s.d. acara selesai) — bukan jam pencoblosan.
            Jadwal pencoblosan diatur belakangan dari halaman Kelola Periode.
        </p>

        <form method="POST" action="{{ route('admin.periode.store') }}">
            @csrf

            <label for="judul">Judul Periode</label>
            <input type="text" id="judul" name="judul" value="{{ old('judul') }}" placeholder="KPUM 2026" required>

            <label for="start_date">Mulai Acara (rapat pertama)</label>
            <input type="datetime-local" id="start_date" name="start_date" value="{{ old('start_date') }}" required>

            <label for="end_date">Selesai Acara</label>
            <input type="datetime-local" id="end_date" name="end_date" value="{{ old('end_date') }}" required>

            <button type="submit" class="btn btn-primary" style="width:100%;">Simpan</button>
        </form>
    </div>
@endsection