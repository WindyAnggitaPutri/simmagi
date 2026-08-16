@extends('layouts.app')

@section('content')
{{-- TOMBOL AKSI --}}
<div class="no-print max-w-4xl mx-auto py-6 px-6">
    <div class="flex justify-between items-center">
        {{-- ✅ DIUBAH: Pakai $backUrl dari controller --}}
        <a href="{{ $backUrl }}" class="text-yellow-600 font-semibold text-sm hover:underline">
            ← Kembali
        </a>
        <div class="flex gap-3">
            <button onclick="downloadPDF()" class="bg-blue-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700 transition inline-flex items-center gap-2">
                <i class="fas fa-download"></i> Download PDF
            </button>
        </div>
    </div>
</div>

{{-- AREA SURAT / REKAPAN --}}
<div id="area-cetak" class="surat-container max-w-4xl mx-auto px-6 pt-3 pb-6 mb-10" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.6; color: #000; text-align: justify;">

    <div style="text-align: center; margin: 5px 0 3px 0;">
        <h3 style="font-size: 13pt; font-weight: bold; text-decoration: underline; margin: 0;">
            REKAPAN LOGBOOK MINGGU KE-{{ $minggu }}
        </h3>
        <p style="font-size: 10pt; margin: 2px 0 0 0;">
            Magang Industri — Program Studi {{ $mahasiswa->prodi->nama_prodi ?? '-' }}
        </p>
    </div>

    <table style="width: 100%; margin-bottom: 10px; font-size: 11pt;">
        <tr>
            <td style="width: 25%; padding: 1px 0;">Nama Mahasiswa</td>
            <td style="width: 3%;">:</td>
            <td style="font-weight: bold;">{{ $mahasiswa->nama }}</td>
        </tr>
        <tr>
            <td style="padding: 1px 0;">NPM</td>
            <td>:</td>
            <td>{{ $mahasiswa->npm }}</td>
        </tr>
        <tr>
            <td style="padding: 1px 0;">Perusahaan</td>
            <td>:</td>
            <td>{{ $perusahaan->nama_perusahaan ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding: 1px 0;">Dosen Pembimbing</td>
            <td>:</td>
            <td>{{ $dosenPembimbing && $dosenPembimbing->dosen ? $dosenPembimbing->dosen->nama : '-' }}</td>
        </tr>
        <tr>
            <td style="padding: 1px 0;">Periode Minggu</td>
            <td>:</td>
            <td>
                @if($tanggalMulai && $tanggalSelesai)
                    {{ \Carbon\Carbon::parse($tanggalMulai)->locale('id')->translatedFormat('d F Y') }} 
                    s.d. 
                    {{ \Carbon\Carbon::parse($tanggalSelesai)->locale('id')->translatedFormat('d F Y') }}
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    @if($logbooks->count() > 0)
    <table style="border-collapse: collapse; width: 100%; margin-bottom: 15px; font-size: 10pt;">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th style="border: 1px solid #000; padding: 4px; width: 5%; text-align: center;">No</th>
                <th style="border: 1px solid #000; padding: 4px; width: 15%; text-align: center;">Tanggal</th>
                <th style="border: 1px solid #000; padding: 4px; width: 12%; text-align: center;">Jam</th>
                <th style="border: 1px solid #000; padding: 4px; width: 43%;">Kegiatan</th>
                <th style="border: 1px solid #000; padding: 4px; width: 25%; text-align: center;">Dokumentasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logbooks as $index => $log)
            <tr>
                <td style="border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle;">{{ $loop->iteration }}</td>
                <td style="border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle;">
                    {{ \Carbon\Carbon::parse($log->tanggal)->locale('id')->translatedFormat('d M Y') }}<br>
                    <span style="font-size: 8pt;">({{ \Carbon\Carbon::parse($log->tanggal)->locale('id')->translatedFormat('l') }})</span>
                </td>
                <td style="border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle;">
                    {{ $log->jam_mulai ? \Carbon\Carbon::parse($log->jam_mulai)->format('H:i') : '-' }} 
                    - 
                    {{ $log->jam_selesai ? \Carbon\Carbon::parse($log->jam_selesai)->format('H:i') : '-' }}
                </td>
                <td style="border: 1px solid #000; padding: 4px; vertical-align: top; white-space: pre-wrap;">{{ $log->kegiatan }}</td>
                <td style="border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle;">
                    @if($log->foto)
                        <img src="{{ asset('storage/' . $log->foto) }}" alt="Foto" style="width: 120px; height: 80px; object-fit: cover; border: 1px solid #ccc; border-radius: 4px;">
                    @else
                        <span style="color: #999; font-size: 9pt;">-</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align: center; padding: 30px; border: 1px solid #ccc; margin-bottom: 15px;">
        <p style="color: #999;">Belum ada catatan logbook pada minggu ini.</p>
    </div>
    @endif

    <div style="display: flex; justify-content: flex-end; padding: 0 10px; margin-top: 15px;">
        <div style="text-align: center; width: 40%;">
            <p style="margin: 0; font-size: 10pt;">Pembimbing Magang Industri,</p>
            <div style="height: 10px;"></div>
            @php
                $dosen = $dosenPembimbing ? $dosenPembimbing->dosen : null;
            @endphp
            @if($dosen && $dosen->ttd_digital)
                <div style="display: flex; justify-content: center; margin-bottom: 5px;">
                    <img src="{{ asset('storage/ttd_digital/' . $dosen->ttd_digital) }}" alt="TTD Dosen" class="ttd-bersih" style="width: 160px; height: auto; max-height: 80px; object-fit: contain;">
                </div>
            @else
                <div style="height: 70px;"></div>
                <div style="border-bottom: 1px solid #000; width: 160px; margin: 0 auto;"></div>
            @endif
            <p style="margin-top: 5px; font-size: 10pt;">
                <strong>{{ $dosen ? $dosen->nama : '____________________' }}</strong><br>
                <span style="font-size: 9pt;">NIDN. {{ $dosen->nidn ?? '....................' }}</span>
            </p>
        </div>
    </div>
</div>

<style>
    .surat-container { background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .ttd-bersih { filter: grayscale(100%) contrast(500%) brightness(150%); mix-blend-mode: multiply; }
    
    @media print {
        nav, footer, .no-print, .sidebar, .navbar, header { display: none !important; }
        main { padding: 0 !important; margin: 0 !important; }
        .surat-container { box-shadow: none !important; padding: 15mm 20mm !important; max-width: 100% !important; margin: 0 !important; }
        .ttd-bersih { filter: grayscale(100%) contrast(500%) brightness(150%) !important; mix-blend-mode: multiply !important; }
        @page { size: A4; margin: 10mm; }
    }
</style>

{{-- ✅ SCRIPT HTML2PDF SAMA PERSIS DENGAN SURAT PENGAJUAN --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    // Fungsi ajaib untuk memutihkan background TTD memakai Canvas
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
                    const r = data[i];
                    const g = data[i + 1];
                    const b = data[i + 2];
                    
                    if (r > 150 && g > 150 && b > 150) {
                        data[i] = 255;
                        data[i + 1] = 255;
                        data[i + 2] = 255;
                        data[i + 3] = 255;
                    } else {
                        data[i] = 0;
                        data[i + 1] = 0;
                        data[i + 2] = 0;
                        data[i + 3] = 255;
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
        
        // Simpan state asli
        const originalSrcs = [];
        const originalFilters = [];
        
        // Proses gambar TTD jadi bersih via Canvas
        for (let i = 0; i < ttdImages.length; i++) {
            const img = ttdImages[i];
            originalSrcs[i] = img.src;
            originalFilters[i] = img.style.filter;
            
            const cleanSrc = await bersihkanGambarTTD(img);
            img.src = cleanSrc;
            img.style.filter = 'none';
        }
        
        // Tunggu DOM update
        await new Promise(resolve => setTimeout(resolve, 500));
        
        const fullHeight = element.scrollHeight;
        
        const opt = {
            margin:       [5, 10, 5, 10], 
            filename:     'Rekap-Logbook-Minggu-{{ $minggu }}.pdf',
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
        
        // Generate PDF
        html2pdf().set(opt).from(element).save().then(() => {
            // Restore keadaan semula
            for (let i = 0; i < ttdImages.length; i++) {
                ttdImages[i].src = originalSrcs[i];
                ttdImages[i].style.filter = originalFilters[i];
            }
        });
    }
</script>

@endsection