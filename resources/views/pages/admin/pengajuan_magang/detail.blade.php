@extends('layouts.admin')

@section('title', 'SIMMAGI - Detail Pengajuan Magang')
@section('page-title', 'Detail Pengajuan Magang')

@section('content')

{{-- TOMBOL AKSI ATAS --}}
<div class="no-print mb-6">
    <div class="flex flex-wrap justify-between items-center gap-3">
        <a href="{{ route('admin.pengajuan-magang.index') }}" class="text-orange-500 hover:underline flex items-center gap-1 text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke Data Pengajuan
        </a>
        <div class="flex flex-wrap gap-2 items-center">
            <span class="border text-xs font-bold px-2.5 py-1.5 rounded {{ $statusAlur['color'] }}">
                {{ $statusAlur['text'] }}
            </span>
<!-- 
            @if ($pengajuan->surat_pengajuan)
                <a href="{{ asset('storage/surat_pengajuan/' . $pengajuan->surat_pengajuan) }}" target="_blank" 
                   class="border border-gray-300 text-xs px-3 py-1.5 hover:bg-gray-100 transition rounded-lg">
                    📎 File Asli
                </a>
            @endif -->

            @php
                $fileBalasan = $pengajuan->mahasiswa->firstWhere('surat_balasan', '!=', null);
            @endphp
            @if ($fileBalasan)
                <a href="{{ asset('storage/surat_balasan/' . $fileBalasan->surat_balasan) }}" target="_blank"
                   class="border border-blue-300 text-blue-600 text-xs px-3 py-1.5 hover:bg-blue-50 transition rounded-lg">
                     Surat Balasan
                </a>
            @endif

            @if ($pengajuan->suratPengantar)
                <a href="{{ asset('storage/surat_pengantar/' . $fileBalasan->surat_balasan) }}" target="_blank"
                   class="border border-green-300 text-green-600 text-xs px-3 py-1.5 hover:bg-green-50 transition rounded-lg">
                     Surat Pengantar
                </a>
            @endif

            <button onclick="downloadPDF()" class="bg-blue-600 text-white text-xs px-3 py-1.5 hover:bg-blue-700 transition rounded-lg">
                 Download PDF
            </button>
        </div>
    </div>

    @if ($pengajuan->catatan)
    <div class="mt-3 border border-yellow-400 bg-yellow-50 rounded-lg p-3">
        <div class="flex items-start gap-2">
            <div class="flex-shrink-0 w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="fas fa-sticky-note text-yellow-600 text-xs"></i>
            </div>
            <div>
                <h4 class="font-bold text-yellow-800 text-xs mb-0.5">Catatan Pengaju</h4>
                <p class="text-yellow-700 text-xs">{{ $pengajuan->catatan }}</p>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- ========================================== --}}
{{-- AREA SURAT RESMI - COMPACT --}}
{{-- ========================================== --}}
<div id="area-cetak" class="surat-container bg-white shadow-md mx-auto mb-8" style="font-family: 'Times New Roman', Times, serif; font-size: 10pt; line-height: 1.4; color: #000; text-align: justify; padding: 25mm 20mm; max-width: 210mm;">

{{-- KOP SURAT --}}
<div style="text-align: center; margin-bottom: 8px; position: relative; padding: 0 70px;">
    
    <img src="{{ asset('images/logo-PNC.png') }}" 
         alt="Logo" 
         style="position: absolute; top: 30%; left: 0; transform: translateY(-50%); width: 65px; height: auto; object-fit: contain;">

    <div style="text-align: center;">
        <p style="font-size: 9pt; font-weight: bold; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">
            Kementerian Pendidikan Tinggi, Sains, dan Teknologi
        </p>
        <h2 style="font-size: 12pt; font-weight: bold; margin: 2px 0 0 0; text-transform: uppercase; letter-spacing: 1px;">
            Jurusan Komputer dan Bisnis
        </h2>
    </div>

    <div style="border-bottom: 2px solid #000; padding-bottom: 4px; margin-top: 6px; text-align: center;">
        <p style="font-size: 8pt; margin: 1px 0; font-style: italic;">
            Jalan Dr. Soetomo No. 1, Sidakaya - Cilacap 53212 Jawa Tengah
        </p>
        <p style="font-size: 8pt; margin: 1px 0;">
            Telepon: (0282) 533329, Fax: (0282) 537992
        </p>
        <p style="font-size: 8pt; margin: 1px 0; font-weight: bold;">
            <a href="https://www.pnc.ac.id" target="_blank" style="color: blue;">www.pnc.ac.id</a>, Email: 
            <a href="mailto:sekretariat@pnc.ac.id" style="color: blue;">sekretariat@pnc.ac.id</a>
        </p>
    </div>
</div>

    {{-- KODE SURAT & PERIHAL --}}
    <div style="text-align: right; margin-bottom: 1px; font-size: 7.5pt;">
        FM.PM-PMI-C.01-R.0
    </div>
    <p style="margin-bottom: 3px;">
        <strong>Perihal</strong> &nbsp;&nbsp;: Permohonan Ijin Magang Industri
    </p>

    {{-- TUJUAN SURAT --}}
    <p style="margin-bottom: 5px;">
        Yth. Kordinator Magang Industri<br>
        Politeknik Negeri Cilacap<br>
        di – Cilacap
    </p>

    {{-- ISI PEMBUKA --}}
    <p style="margin-bottom: 5px;">
        Dengan ini mohon dibuatkan surat ijin magang Industri atas nama mahasiswa dibawah ini :
    </p>

    {{-- TABEL MAHASISWA --}}
    <table style="border-collapse: collapse; width: 100%; margin-bottom: 12px; font-size: 9pt;">
        <thead>
            <tr style="background-color: #f5f5f5;">
                <th style="border: 1px solid #000; padding: 4px 6px; width: 5%;">No</th>
                <th style="border: 1px solid #000; padding: 4px 6px;">Nama Mahasiswa</th>
                <th style="border: 1px solid #000; padding: 4px 6px; width: 14%;">NIM</th>
                <th style="border: 1px solid #000; padding: 4px 6px; width: 28%;">Program Studi</th>
                <th style="border: 1px solid #000; padding: 4px 6px; width: 8%;">IPK</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pengajuan->mahasiswa as $index => $pm)
            <tr>
                <td style="border: 1px solid #000; padding: 4px 6px; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #000; padding: 4px 6px;">{{ $pm->mahasiswa->nama ?? '-' }}</td>
                <td style="border: 1px solid #000; padding: 4px 6px;">{{ $pm->mahasiswa->npm ?? '-' }}</td>
                <td style="border: 1px solid #000; padding: 4px 6px;">{{ $pm->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                <td style="border: 1px solid #000; padding: 4px 6px; text-align: center;">{{ $pm->ipk ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- DETAIL INFO PERUSAHAAN --}}
    <div style="margin-bottom: 12px; font-size: 9.5pt;">
        <p style="margin: 2px 0;">1. Nama Perusahaan Tujuan &nbsp;&nbsp;&nbsp;&nbsp;: <strong>{{ $pengajuan->perusahaan->nama_perusahaan ?? '-' }}</strong></p>
        <p style="margin: 2px 0;">2. Ditujukan kepada &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->ditujukan_kepada ?: '-' }}</p>
        <p style="margin: 2px 0;">3. Alamat lengkap &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->perusahaan->alamat ?? '-' }}</p>
        <p style="margin: 2px 0;">4. No. Telepon/e-mail &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->email_perusahaan ?: '-' }}</p>
        <p style="margin: 2px 0;">5. Contact Person di Perusahaan &nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->contact_person ?? '-' }}</p>
        <p style="margin: 2px 0;">6. No. HP CP di Perusahaan &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->no_hp_cp ?? '-' }}</p>
        <p style="margin: 2px 0;">7. Waktu Pelaksanaan Magang &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ 
            \Carbon\Carbon::parse($pengajuan->tgl_mulai)->locale('id')->translatedFormat('F Y') . ' - ' . 
            \Carbon\Carbon::parse($pengajuan->tgl_selesai)->locale('id')->translatedFormat('F Y') 
        }}</p>
        <p style="margin: 2px 0;">8. Deskripsi Perusahaan &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->deskripsi_perusahaan ?? '-' }}</p>
        <p style="margin: 2px 0;">9. Nama Perwakilan Kelompok &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->mahasiswa->first()->mahasiswa->nama ?? '-' }}</p>
        <p style="margin: 2px 0;">10. No. HP Perwakilan Kelompok &nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->mahasiswa->first()->mahasiswa->no_hp ?? '-' }}</p>
    </div>

    {{-- PENUTUP --}}
    <p style="margin-bottom: 8px;">
        Demikian permohonan ini, atas perhatian dan kerjasamanya, kami ucapkan terima kasih.
    </p>

    {{-- TTD --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 0 10px; margin-top: 10px;">
        
        {{-- KOLOM KIRI: TTD Koordinator --}}
        <div style="text-align: center; width: 38%;">
            <p style="margin: 0; font-size: 9pt;">Menyetujui,<br>Kordinator Magang Industri</p>
            <div style="height: 10px;"></div>
            
            @if($approvalKaprodi && $approvalKaprodi->status == 'disetujui' && $dosen && $dosen->ttd_digital)
                <div style="display: flex; justify-content: center; margin-bottom: 2px;">
                    <img src="{{ asset('storage/ttd_digital/' . $dosen->ttd_digital) }}" 
                         alt="TTD Kaprodi" class="ttd-bersih"
                         style="width: 130px; height: auto; max-height: 60px; object-fit: contain;">
                </div>
                <p style="margin-top: 2px; font-size: 8.5pt;">
                    <strong>{{ $dosen->nama }}</strong><br>
                    NIDN. {{ $dosen->nidn }}
                </p>
            @else
                <div style="height: 55px;"></div>
                <div style="border-bottom: 1px solid #000; width: 130px; margin: 0 auto;"></div>
                <p style="margin-top: 2px; font-size: 8.5pt;">NIDN. ................................</p>
            @endif
        </div>

        {{-- KOLOM KANAN: Tanggal + TTD Perwakilan --}}
        <div style="width: 38%;">
            <div style="text-align: center; font-size: 9pt;">
                Cilacap, {{ \Carbon\Carbon::parse($pengajuan->tgl_pengajuan)->locale('id')->translatedFormat('d F Y') }}
            </div>
            <div style="text-align: center;">
                <p style="margin: 0; font-size: 9pt;">Perwakilan Kelompok</p>
                <div style="height: 10px;"></div>
                @if($pengajuan->mahasiswa->first()->mahasiswa->ttd_digital)
                    <div style="display: flex; justify-content: center; margin-bottom: 2px;">
                        <img src="{{ asset('storage/ttd_digital/' . $pengajuan->mahasiswa->first()->mahasiswa->ttd_digital) }}" 
                             alt="TTD Digital" class="ttd-bersih"
                             style="width: 130px; height: auto; max-height: 60px; object-fit: contain;">
                    </div>
                @else
                    <div style="height: 55px;"></div>
                    <div style="border-bottom: 1px solid #000; width: 130px; margin: 0 auto;"></div>
                @endif
                <p style="margin-top: 2px; font-size: 8.5pt;">
                    {{ $pengajuan->mahasiswa->first()->mahasiswa->nama ?? '-' }}<br>
                    NIM. {{ $pengajuan->mahasiswa->first()->mahasiswa->npm ?? '-' }}
                </p>
            </div>
        </div>
    </div>

</div>

{{-- ==================== LAMPIRAN SURAT IZIN ORANG TUA ==================== --}}
<div class="no-print">
    @foreach ($pengajuan->mahasiswa as $pm)
        @if ($pm->surat_ortu)
            @php
                $extension = pathinfo($pm->surat_ortu, PATHINFO_EXTENSION);
                $filePath = asset('storage/surat_ortu/' . $pm->surat_ortu);
            @endphp

            <div class="surat-container bg-white shadow-md mx-auto mb-8" style="font-family: 'Times New Roman', Times, serif; font-size: 10pt; color: #000; padding: 25mm 20mm; max-width: 210mm;">
                
                <h3 style="text-align: center; font-weight: bold; font-size: 11pt; margin-bottom: 3px;">
                    LAMPIRAN SURAT IZIN ORANG TUA
                </h3>
                <p style="text-align: center; font-size: 9pt; margin-bottom: 12px;">
                    {{ $pm->mahasiswa->nama ?? '-' }} ({{ $pm->mahasiswa->npm ?? '-' }})
                </p>

                <div style="border-top: 1px solid #000; margin-bottom: 12px;"></div>

                @if (strtolower($extension) == 'pdf')
                    <embed src="{{ $filePath }}#toolbar=0&navpanes=0&scrollbar=0" 
                           type="application/pdf" 
                           style="width: 100%; height: 700px; border: none;">
                @else
                    <img src="{{ $filePath }}" alt="Surat Izin Orang Tua" style="width: 100%; height: auto; display: block;">
                @endif

            </div>
        @endif
    @endforeach
</div>

@endsection

{{-- ==================== CSS ==================== --}}
<style>
    .surat-container {
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
    }
    .ttd-bersih {
        filter: grayscale(100%) contrast(500%) brightness(150%);
    }

    @media print {
        nav, footer, .no-print, 
        .sidebar, .navbar, header { 
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
        .ttd-bersih {
            filter: grayscale(100%) contrast(500%) brightness(150%) !important;
        }
        @page {
            size: A4;
            margin: 0;
        }
    }
</style>

{{-- ==================== SCRIPT PDF ==================== --}}
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function bersihkanGambarTTD(imgElement) {
        return new Promise((resolve) => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = imgElement.naturalWidth;
            canvas.height = imgElement.naturalHeight;
            
            const img = new Image();
            img.crossOrigin = 'Anonymous';
            img.src = imgElement.src;
            
            img.onload = function() {
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const data = imageData.data;
                
                for (let i = 0; i < data.length; i += 4) {
                    const r = data[i], g = data[i + 1], b = data[i + 2];
                    if (r > 150 && g > 150 && b > 150) {
                        data[i] = 255; data[i + 1] = 255; data[i + 2] = 255;
                    } else {
                        data[i] = 0; data[i + 1] = 0; data[i + 2] = 0;
                    }
                }
                
                ctx.putImageData(imageData, 0, 0);
                resolve(canvas.toDataURL('image/png'));
            };
            
            img.onerror = function() {
                resolve(imgElement.src);
            };
        });
    }

    async function downloadPDF() {
        const element = document.getElementById('area-cetak');
        const ttdImages = element.querySelectorAll('.ttd-bersih');
        
        const originalPadding = element.style.padding;
        const originalOverflow = element.style.overflow;
        const originalSrcs = [];
        const originalFilters = [];
        
        element.style.padding = '0';
        element.style.overflow = 'visible';
        
        for (let i = 0; i < ttdImages.length; i++) {
            const img = ttdImages[i];
            originalSrcs[i] = img.src;
            originalFilters[i] = img.style.filter;
            
            const cleanSrc = await bersihkanGambarTTD(img);
            img.src = cleanSrc;
            img.style.filter = 'none';
        }
        
        await new Promise(resolve => setTimeout(resolve, 500));
        
        const fullHeight = element.scrollHeight;
        
        const opt = {
            margin:       [5, 10, 5, 10], 
            filename:     'Surat_Pengajuan_Magang_' + {{ $pengajuan->id_pengajuan }} + '.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { 
                scale: 2, 
                useCORS: true,
                scrollY: 0,
                windowHeight: fullHeight,
                height: fullHeight
            },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak:    { mode: ['css', 'legacy'] }
        };
        
        html2pdf().set(opt).from(element).save().then(() => {
            element.style.padding = originalPadding;
            element.style.overflow = originalOverflow;
            
            for (let i = 0; i < ttdImages.length; i++) {
                ttdImages[i].src = originalSrcs[i];
                ttdImages[i].style.filter = originalFilters[i];
            }
        });
    }
</script>
@endpush