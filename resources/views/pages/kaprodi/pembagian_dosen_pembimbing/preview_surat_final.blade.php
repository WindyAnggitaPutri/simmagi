@extends('layouts.app')

@section('content')

{{-- ==================== TOMBOL AKSI ==================== --}}
<div class="no-print relative z-50 max-w-4xl mx-auto py-6 px-6">
    <div class="flex justify-between items-center">
        <a href="{{ $backUrl ?? route('kaprodi.dosen-pembimbing.index') }}" class="text-yellow-600 font-semibold text-sm hover:underline">
            ← {{ $backText ?? 'Kembali ke Pembagian Dosen Pembimbing' }}
        </a>
        <div class="flex gap-3 items-center">
            <button onclick="downloadPDF()" class="bg-blue-600 text-white text-sm px-4 py-2 hover:bg-blue-700 transition rounded-lg cursor-pointer font-semibold shadow-sm">
                📥 Download PDF
            </button>
        </div>
    </div>
</div>

{{-- ==================== AREA CETAK ==================== --}}
<div id="area-cetak" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.6; color: #000; text-align: justify;">

    {{-- ==================== LEMBAR 1: NOTA DINAS ==================== --}}
    <div class="surat-container" style="max-width: 210mm; margin: 0 auto 20px auto; padding: 25mm 30mm; background: #fff;">

        {{-- KOP SURAT --}}
        <div style="text-align: center; margin-bottom: 15px; position: relative; padding: 0 20px;">
            <img src="{{ asset('images/logo-PNC.png') }}" 
                 alt="Logo Kiri" 
                 style="position: absolute; top: 35%; left: 0; transform: translateY(-50%); width: 85px; height: 75px; object-fit: contain;">
            <div style="position: absolute; top: 50%; right: 0; transform: translateY(-50%); width: 50px; height: 50px;"></div>

            <div style="text-align: center; padding: 0 60px;">
                <p style="font-size: 11pt; font-weight: bold; margin: 0; text-transform: uppercase; letter-spacing: 1px;">
                    Kementerian Pendidikan Tinggi, Sains, dan Teknologi
                </p>
                <h2 style="font-size: 14pt; font-weight: bold; margin: 3px 0 0 0; text-transform: uppercase; letter-spacing: 1.5px;">
                    Politeknik Negeri Cilacap
                </h2>
                <p style="font-size: 9pt; margin: 2px 0; font-weight: bold;">
                    Jurusan Komputer dan Bisnis
                </p>
            </div>

            <div style="border-bottom: 3px solid #000; padding-bottom: 8px; margin-top: 12px; text-align: center;">
                <p style="font-size: 9pt; margin: 1px 0; font-style: italic;">
                    Jalan Dr. Soetomo No. 1, Sidakaya - Cilacap 53212 Jawa Tengah
                </p>
                <p style="font-size: 9pt; margin: 1px 0;">
                    Telepon: (0282) 533329, Fax: (0282) 537992
                </p>
                <p style="font-size: 9pt; margin: 1px 0; font-weight: bold;">
                    <a href="https://www.pnc.ac.id" target="_blank" style="color: blue;">www.pnc.ac.id</a>, Email: 
                    <a href="mailto:sekretariat@pnc.ac.id" style="color: blue;">sekretariat@pnc.ac.id</a>
                </p>
            </div>
        </div>

        {{-- JUDUL NOTA DINAS --}}
        <div style="text-align: center; margin-bottom: 20px; margin-top: 5px;">
            <h3 style="font-size: 14pt; font-weight: bold; text-decoration: underline; margin: 0; letter-spacing: 1px;">
                NOTA DINAS
            </h3>
        </div>

        {{-- NOMOR & PERIHAL --}}
        <table style="width: 100%; font-size: 11pt; margin-bottom: 15px;">
            <tr>
                <td style="width: 25%; vertical-align: top;">Nomor</td>
                <td style="width: 3%; vertical-align: top;">:</td>
                <td style="vertical-align: top;">{{ $nomorSurat ?? '-' }}</td>
            </tr>
            <tr>
                <td style="vertical-align: top;">Lampiran</td>
                <td style="vertical-align: top;">:</td>
                <td style="vertical-align: top;">1 (satu) berkas</td>
            </tr>
            <tr>
                <td style="vertical-align: top;">Perihal</td>
                <td style="vertical-align: top;">:</td>
                <td style="vertical-align: top;"><strong>Pengajuan SK Pembimbing Magang Industri</strong></td>
            </tr>
        </table>

        {{-- TUJUAN & PEMOHON --}}
        <table style="width: 100%; font-size: 11pt; margin-bottom: 15px;">
            <tr>
                <td style="width: 6%; vertical-align: top;">Kepada</td>
                <td style="width: 3%; vertical-align: top;">Yth.</td>
                <td style="vertical-align: top;">
                    Direktur Politeknik Negeri Cilacap<br>
                    di – Cilacap
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;">Dari</td>
                <td style="vertical-align: top;"></td>
                <td style="vertical-align: top;">
                    Ketua Jurusan Komputer dan Bisnis<br>
                    Politeknik Negeri Cilacap
                </td>
            </tr>
        </table>

        {{-- ISI NOTA DINAS --}}
        <div style="font-size: 11pt; margin-bottom: 10px;">
            <p style="margin: 0 0 10px 0;">
                Dengan hormat, sehubungan dengan pelaksanaan kegiatan Magang Industri mahasiswa Jurusan Komputer dan Bisnis Politeknik Negeri Cilacap, bersama ini kami mengajukan permohonan agar Bapak/Ibu berkenan menerbitkan Surat Keputusan (SK) Pembimbing Magang Industri.
            </p>
            <p style="margin: 0 0 10px 0;">
                Adapun data mahasiswa dan dosen pembimbing yang diusulkan sebagaimana terlampir pada lembar berikut, dengan rincian:
            </p>
            <ul style="margin: 0 0 10px 0; padding-left: 25px;">
                <li>Jumlah Dosen Pembimbing &nbsp;&nbsp;: <strong>{{ $totalDosen ?? 0 }} orang</strong></li>
                <li>Jumlah Mahasiswa Bimbingan &nbsp;: <strong>{{ $totalMhs ?? 0 }} orang</strong></li>
            </ul>
            <p style="margin: 0 0 10px 0;">
                Demikian nota dinas ini dibuat untuk dapat dipergunakan sebagaimana mestinya. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.
            </p>
        </div>

        {{-- TTD --}}
        <div style="text-align: right; margin-top: 30px; width: 50%; margin-left: auto;">
            <p style="margin: 0; font-size: 11pt;">Cilacap, {{ $tanggalSurat ?? \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</p>
            <p style="margin: 0; font-size: 11pt;">Ketua Jurusan Komputer dan Bisnis</p>
            <p style="margin: 0; font-size: 11pt; margin-bottom: 5px;">Politeknik Negeri Cilacap</p>
            
            <div style="height: 80px;"></div>
            <div style="border-bottom: 1px solid #000; width: 220px; margin-left: auto;"></div>
            <p style="margin-top: 5px; font-size: 10pt;">
                <strong>................................</strong><br>
                NIDN. ................................
            </p>
        </div>

    </div>

    {{-- ==================== LEMBAR 2: DAFTAR MAHASISWA ==================== --}}
    <div class="surat-container lembar-2" style="max-width: 210mm; margin: 0 auto 20px auto; padding: 25mm 30mm; background: #fff;">

        {{-- KOP SURAT --}}
        <div style="text-align: center; margin-bottom: 15px; position: relative; padding: 0 20px;">
            <img src="{{ asset('images/logo-PNC.png') }}" 
                 alt="Logo Kiri" 
                 style="position: absolute; top: 35%; left: 0; transform: translateY(-50%); width: 85px; height: 75px; object-fit: contain;">
            <div style="position: absolute; top: 50%; right: 0; transform: translateY(-50%); width: 50px; height: 50px;"></div>

            <div style="text-align: center; padding: 0 60px;">
                <p style="font-size: 11pt; font-weight: bold; margin: 0; text-transform: uppercase; letter-spacing: 1px;">
                    Kementerian Pendidikan Tinggi, Sains, dan Teknologi
                </p>
                <h2 style="font-size: 14pt; font-weight: bold; margin: 3px 0 0 0; text-transform: uppercase; letter-spacing: 1.5px;">
                    Politeknik Negeri Cilacap
                </h2>
                <p style="font-size: 9pt; margin: 2px 0; font-weight: bold;">
                    Jurusan Komputer dan Bisnis
                </p>
            </div>

            <div style="border-bottom: 3px solid #000; padding-bottom: 8px; margin-top: 12px; text-align: center;">
                <p style="font-size: 9pt; margin: 1px 0; font-style: italic;">
                    Jalan Dr. Soetomo No. 1, Sidakaya - Cilacap 53212 Jawa Tengah
                </p>
                <p style="font-size: 9pt; margin: 1px 0;">
                    Telepon: (0282) 533329, Fax: (0282) 537992
                </p>
                <p style="font-size: 9pt; margin: 1px 0; font-weight: bold;">
                    <a href="https://www.pnc.ac.id" target="_blank" style="color: blue;">www.pnc.ac.id</a>, Email: 
                    <a href="mailto:sekretariat@pnc.ac.id" style="color: blue;">sekretariat@pnc.ac.id</a>
                </p>
            </div>
        </div>

        {{-- JUDUL --}}
        <div style="text-align: center; margin-bottom: 20px; margin-top: 5px;">
            <h3 style="font-size: 13pt; font-weight: bold; text-decoration: underline; margin: 0; letter-spacing: 0.5px;">
                DAFTAR MAHASISWA BIMBINGAN MAGANG INDUSTRI
            </h3>
            <p style="font-size: 12pt; font-weight: bold; margin: 3px 0 0 0;">
                TA {{ \Carbon\Carbon::now()->year . '/' . (\Carbon\Carbon::now()->year + 1) }}
            </p>
        </div>

        {{-- DAFTAR PER DOSEN --}}
        @if($previewDospem->isEmpty())
            <div style="text-align: center; padding: 40px; font-style: italic; color: #666;">
                — Belum ada data dosen pembimbing dan mahasiswa bimbingan —
            </div>
        @else
            @foreach ($previewDospem as $indexDosen => $items)
                <div style="margin-bottom: 20px; page-break-inside: avoid;">
                    @php
                        $dosenPertama = $items->first();
                        $namaDosen = $dosenPertama->dosen->nama ?? '-';
                        $nidnDosen = $dosenPertama->dosen->nidn ?? '';
                    @endphp
                    <p style="font-weight: bold; margin: 0 0 6px 0; font-size: 11pt;">
                        {{ $loop->iteration }}. {{ $namaDosen }}{{ $nidnDosen ? ', ' . $nidnDosen : '' }}
                    </p>

                    <table style="border-collapse: collapse; width: 100%; font-size: 11pt;">
                        <thead>
                            <tr style="background-color: #f0f0f0;">
                                <th style="border: 1px solid #000; padding: 7px 10px; width: 8%; text-align: center;">NO</th>
                                <th style="border: 1px solid #000; padding: 7px 10px; width: 22%;">NPM</th>
                                <th style="border: 1px solid #000; padding: 7px 10px;">NAMA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $index => $item)
                            <tr>
                                <td style="border: 1px solid #000; padding: 6px 10px; text-align: center;">{{ $index + 1 }}</td>
                                <td style="border: 1px solid #000; padding: 6px 10px;">{{ $item->mahasiswa->npm ?? '-' }}</td>
                                <td style="border: 1px solid #000; padding: 6px 10px;">{{ strtoupper($item->mahasiswa->nama ?? '-') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @endif

        {{-- TOTAL --}}
        <div style="margin-bottom: 10px; font-size: 11pt;">
            <p style="margin: 0;">
                <strong>Jumlah Total Mahasiswa : {{ $totalMhs ?? 0 }} orang</strong>
            </p>
        </div>

        {{-- TTD --}}
        <div style="text-align: right; margin-top: 30px; width: 50%; margin-left: auto;">
            <p style="margin: 0; font-size: 11pt;">Cilacap, {{ $tanggalSurat ?? \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</p>
            <p style="margin: 0; font-size: 11pt;">Jurusan Komputer dan Bisnis</p>
            <p style="margin: 0; font-size: 11pt; margin-bottom: 5px;">Ketua,</p>
            
            <div style="height: 80px;"></div>
            <div style="border-bottom: 1px solid #000; width: 220px; margin-left: auto;"></div>
            <p style="margin-top: 5px; font-size: 10pt;">
                <strong>................................</strong><br>
                NIDN. ................................
            </p>
        </div>

    </div>

</div>

@endsection

{{-- ==================== CSS ==================== --}}
<style>
    .surat-container {
        border: none !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .lembar-2 {
        border-top: 3px dashed #ccc !important;
        padding-top: 30mm !important;
    }

    @media print {
        nav, footer, .no-print, .sidebar, .navbar, header { 
            display: none !important; 
        }
        main { 
            padding: 0 !important; 
            margin: 0 !important;
        }
        .surat-container { 
            border: 1px solid #000 !important;
            box-shadow: none !important;
            padding: 20mm 25mm !important;
            max-width: 100% !important;
            margin: 0 !important;
        }
        .lembar-2 {
            border-top: none !important;
            padding-top: 20mm !important;
            page-break-before: always;
        }
        @page {
            size: A4;
            margin: 0;
        }
    }
</style>

{{-- ==================== SCRIPTS ==================== --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    async function downloadPDF() {
        const element = document.getElementById('area-cetak');
        const fullHeight = element.scrollHeight;
        
        const opt = {
            margin:       [5, 10, 5, 10], 
            filename:     'Nota_Dinas_Dan_Daftar_Bimbingan_Magang.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { 
                scale: 2, useCORS: true, scrollY: 0,
                windowHeight: fullHeight, height: fullHeight
            },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak:    { mode: ['css', 'legacy'] }
        };
        
        html2pdf().set(opt).from(element).save();
    }
</script>