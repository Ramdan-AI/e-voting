@extends('layouts.admin')

@section('title', 'Review LPJ')

@section('content')
    <div style="margin-bottom:20px;">
        <a href="{{ route('admin.periode.index') }}" style="font-size:14px; color:white; text-decoration:none;">&larr; Kembali ke Periode</a>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:8px;">
            <h1 style="margin:0;">Review LPJ — {{ $periode->judul }}</h1>
            <a href="{{ route('admin.lpj.exportSemua', $periode) }}" class="btn btn-primary">Export Semua LPJ (PDF)</a>
        </div>
    </div>

    <div class="card">
        <table>
            <thead><tr><th>Divisi</th><th>PJ</th><th>Status</th><th>Diajukan</th><th></th></tr></thead>
            <tbody>
                @foreach ($semuaDivisi as $kode => $nama)
                    @php $lpj = $lpjs->get($kode); @endphp
                    <tr>
                        <td>{{ $nama }}</td>
                        <td>{{ $lpj->pj_nama ?? '—' }}</td>
                        <td>
                            @if ($lpj)
                                <span class="badge {{ $lpj->status === 'disahkan' ? 'running' : ($lpj->status === 'direvisi' ? 'stopped' : 'freeze') }}">
                                    {{ strtoupper($lpj->status) }}
                                </span>
                            @else
                                <span class="badge" style="background:#eee; color:#999;">BELUM MULAI</span>
                            @endif
                        </td>
                        <td>{{ $lpj?->diajukan_pada?->format('d M Y H:i') ?? '—' }}</td>
                        <td>
                            @if ($lpj)
                                <a href="{{ route('admin.lpj.show', [$periode, $lpj]) }}" class="btn btn-outline">Lihat</a>
                                <a href="{{ route('admin.lpj.export', [$periode, $lpj]) }}" class="btn btn-outline">PDF</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection