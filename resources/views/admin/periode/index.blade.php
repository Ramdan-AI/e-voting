@extends('layouts.admin')

@section('title', 'Kelola Periode')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 style="margin:0;">Kelola Periode</h1>
        <a href="{{ route('admin.periode.create') }}" class="btn btn-primary">+ Periode Baru</a>
    </div>

    @forelse ($periodes as $periode)
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px;">
                <div>
                    <strong style="font-size:15px;">{{ $periode->judul }}</strong>
                    <span class="badge {{ $periode->status }}">{{ $periode->status }}</span>
                    <span class="badge" style="background:{{ $periode->tampilkan_hasil ? '#e6f4ea' : '#eee' }}; color:{{ $periode->tampilkan_hasil ? '#1e7e34' : '#888' }};">
                        hasil {{ $periode->tampilkan_hasil ? 'tampil' : 'tersembunyi' }}
                    </span>
                    <p style="font-size:12px; color:#888; margin:6px 0 0;">
                        Rentang acara: {{ $periode->start_date->format('d M Y H:i') }} — {{ $periode->end_date->format('d M Y H:i') }}
                    </p>
                    <p style="font-size:12px; color:#888; margin:2px 0 0;">
                        Jendela voting:
                        @if ($periode->waktu_mulai_voting)
                            {{ $periode->waktu_mulai_voting->format('d M Y H:i') }} — {{ $periode->waktu_selesai_voting->format('d M Y H:i') }}
                        @else
                            <em>belum diatur</em>
                        @endif
                    </p>
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-bottom:16px;">
                <form method="POST" action="{{ route('admin.periode.toggleHasil', $periode) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline">
                        {{ $periode->tampilkan_hasil ? 'Sembunyikan Hasil di Publik' : 'Tampilkan Hasil di Publik' }}
                    </button>
                </form>
                <a href="{{ route('admin.pemilih.terkunci', $periode) }}" class="btn btn-outline">Lihat Akun Terkunci</a>
                <a href="{{ route('admin.audit.presensi', $periode) }}" class="btn btn-outline">Log Presensi</a>
            </div>

            <div style="display:flex; gap:24px; flex-wrap:wrap; border-top:1px solid var(--border); padding-top:16px;">
                <form method="POST" action="{{ route('admin.periode.setJadwalVoting', $periode) }}" style="flex:1; min-width:260px;">
                    @csrf
                    @method('PATCH')
                    <label style="font-size:12px;">Jadwal Pencoblosan</label>
                    <div style="display:flex; gap:8px;">
                        <input type="datetime-local" name="waktu_mulai_voting" style="margin-bottom:8px;" required>
                        <input type="datetime-local" name="waktu_selesai_voting" style="margin-bottom:8px;" required>
                    </div>
                    <button type="submit" class="btn btn-outline" style="width:100%;">Set Jadwal</button>
                </form>

                <form method="POST" action="{{ route('admin.periode.updateStatus', $periode) }}" style="min-width:200px;">
                    @csrf
                    @method('PATCH')
                    <label style="font-size:12px;">Ubah Status</label>
                    <select name="status" style="margin-bottom:8px;">
                        <option value="running" @selected($periode->status === 'running')>running</option>
                        <option value="freeze" @selected($periode->status === 'freeze')>freeze</option>
                        <option value="stopped" @selected($periode->status === 'stopped')>stopped</option>
                    </select>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Simpan Status</button>
                </form>
            </div>
        </div>
    @empty
        <div class="card">Belum ada periode. <a href="{{ route('admin.periode.create') }}">Buat periode baru</a>.</div>
    @endforelse

    {{ $periodes->links() }}
@endsection