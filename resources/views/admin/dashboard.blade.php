@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1>Dashboard</h1>

    @if ($ringkasan)
        <div class="card">
            <p style="font-size:13px; color:#666; margin-top:0;">
                Periode aktif: <strong>{{ $ringkasan['periode']->judul }}</strong>
                <span class="badge {{ $ringkasan['periode']->status }}">{{ $ringkasan['periode']->status }}</span>
            </p>
            <div class="stat-grid">
                <div class="stat-box">
                    <div class="num">{{ $ringkasan['total_suara'] }}</div>
                    <div class="label">Suara Masuk</div>
                </div>
                <div class="stat-box">
                    <div class="num">{{ $ringkasan['total_pemilih'] }}</div>
                    <div class="label">Pemilih Terdaftar</div>
                </div>
                <div class="stat-box">
                    <div class="num">{{ $ringkasan['total_kandidat'] }}</div>
                    <div class="label">Kandidat</div>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <p style="margin:0; color:#666;">Tidak ada periode yang sedang berjalan (running) saat ini.</p>
        </div>
    @endif

    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <strong style="font-size:14px;">Semua Periode</strong>
            <a href="{{ route('admin.periode.index') }}" class="btn btn-outline">Kelola Periode</a>
        </div>
        <table>
            <thead><tr><th>ID</th><th>Judul</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($semuaPeriode as $p)
                    <tr>
                        <td>#{{ $p->id }}</td>
                        <td>{{ $p->judul }}</td>
                        <td><span class="badge {{ $p->status }}">{{ $p->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="color:#999;">Belum ada periode.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection