<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 13px; color: #000; }
        .cover { text-align: center; padding-top: 40px; }
        .cover .judul-utama { font-size: 14px; font-weight: bold; margin-bottom: 30px; }
        .cover .judul-kpum { font-size: 14px; font-weight: bold; margin-bottom: 4px; }
        .cover .tahun { font-size: 13px; font-weight: bold; margin-bottom: 4px; }
        .cover .divisi { font-size: 13px; font-weight: bold; margin-bottom: 30px; }
        .info-block { width: 380px; margin: 0 auto 30px; text-align: left; }
        .info-block table { width: 100%; }
        .info-block td { padding: 2px 0; vertical-align: top; font-size: 13px; }
        .info-block td.label { width: 110px; }
        .info-block td.titik { width: 12px; }
        .info-block ol { margin: 4px 0 0 0; padding-left: 20px; }
        .logo-block { margin: 20px 0 10px; }
        .logo-block img { width: 130px; height: auto; }
        .kampus-nama { font-size: 13px; font-weight: bold; margin-top: 8px; }
        .kampus-tahun { font-size: 13px; margin-top: 4px; }
        .footer-cover { margin-top: 60px; text-align: center; }
        .footer-cover p { font-size: 13px; font-weight: bold; margin: 2px 0; }

        .isi-page { page-break-before: always; padding-top: 20px; }
        .isi-page h2 { font-size: 13px; font-weight: bold; margin-top: 16px; margin-bottom: 4px; text-decoration: underline; }
        .isi-page p { line-height: 1.6; margin: 0 0 10px; white-space: pre-line; text-align: justify; }
        .status-note { font-size: 11px; color: #000; margin-bottom: 16px; }

        .cover-utama { text-align: center; padding-top: 120px; }
        .cover-utama h1 { font-size: 18px; margin-bottom: 8px; }
        .cover-utama h2 { font-size: 14px; font-weight: normal; margin-bottom: 60px; }
        .cover-utama table { margin: 0 auto; text-align: left; font-size: 13px; }
        .cover-utama table td { padding: 4px 10px; }
        .divisi-block { page-break-before: always; }
    </style>
</head>
<body>
    <div class="cover-utama">
        <h1>LAPORAN PERTANGGUNG JAWABAN</h1>
        <h2>KPUM (KOMISI PEMILIHAN UMUM MAHASISWA) — {{ $periode->judul }}</h2>
        <table>
            <tr><td>Jumlah Divisi Melaporkan</td><td>: {{ $lpjs->count() }}</td></tr>
            <tr><td>Tanggal Dokumen Dibuat</td><td>: {{ now()->format('d F Y') }}</td></tr>
        </table>
    </div>

    @foreach ($lpjs as $lpj)
        <div class="divisi-block">
            <div class="cover">
                <div class="judul-utama">LAPORAN PERTANGGUNG JAWABAN</div>
                <div class="judul-kpum">KPUM (KOMISI PEMILIHAN UMUM MAHASISWA)</div>
                <div class="tahun">TAHUN AKADEMIK {{ $periode->start_date->format('Y') }}</div>
                <div class="divisi">({{ strtoupper($lpj->namaDivisi()) }})</div>

                <div class="info-block">
                    <table>
                        <tr>
                            <td class="label">PJ Divisi</td>
                            <td class="titik">:</td>
                            <td>{{ $lpj->pj_nama }}</td>
                        </tr>
                        <tr>
                            <td class="label" style="vertical-align:top;">Anggota Divisi</td>
                            <td class="titik" style="vertical-align:top;">:</td>
                            <td>
                                @php
                                    $anggotaList = $lpj->anggota_divisi ? preg_split('/\r\n|\r|\n/', trim($lpj->anggota_divisi)) : [];
                                @endphp
                                @if (count($anggotaList) > 0)
                                    <ol>
                                        @foreach ($anggotaList as $anggota)
                                            @if (trim($anggota) !== '')
                                                <li>{{ trim($anggota) }}</li>
                                            @endif
                                        @endforeach
                                    </ol>
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="logo-block">
                    @if (file_exists(public_path('images/logo/STMIK.jpeg')))
                        <img src="{{ public_path('images/logo/STMIK.jpeg') }}" alt="Logo STMIK">
                    @endif
                </div>
                <div class="kampus-nama">STMIK MARDIRA INDONESIA</div>
                <div class="kampus-tahun">{{ $periode->start_date->format('Y') }}</div>

                <div class="footer-cover">
                    <p>Laporan Pertanggung Jawaban</p>
                    <p>KPUM (KOMISI PEMILIHAN UMUM MAHASISWA) {{ $periode->start_date->format('Y') }}</p>
                    <p>({{ strtoupper($lpj->namaDivisi()) }})</p>
                </div>
            </div>

            <div class="isi-page">
                <div class="status-note">Status Laporan: {{ strtoupper($lpj->status) }}</div>

                <h2>Ringkasan</h2>
                <p>{{ $lpj->ringkasan ?: '-' }}</p>

                <h2>Tugas Pokok dan Fungsi</h2>
                <p>{{ $lpj->tugas_pokok_fungsi ?: '-' }}</p>

                <h2>Parameter Keberhasilan</h2>
                <p>{{ $lpj->parameter_keberhasilan ?: '-' }}</p>

                <h2>Kritik</h2>
                <p>{{ $lpj->kritik ?: '-' }}</p>

                <h2>Saran</h2>
                <p>{{ $lpj->saran ?: '-' }}</p>

                <h2>Faktor Pendukung</h2>
                <p>{{ $lpj->faktor_pendukung ?: '-' }}</p>

                <h2>Faktor Penghambat</h2>
                <p>{{ $lpj->faktor_penghambat ?: '-' }}</p>

                <h2>Evaluasi</h2>
                <p>{{ $lpj->evaluasi ?: '-' }}</p>

                <h2>Laporan Anggaran Biaya</h2>
                <p>{{ $lpj->rincian_anggaran ?: 'Tidak ada pengeluaran dilaporkan.' }}</p>

                @if ($lpj->status === 'disahkan')
                    <p style="margin-top:20px;">Disahkan oleh: {{ $lpj->disahkanOleh->nama ?? '-' }} pada {{ $lpj->disahkan_pada?->format('d F Y H:i') }}</p>
                @endif
            </div>
        </div>
    @endforeach
</body>
</html>