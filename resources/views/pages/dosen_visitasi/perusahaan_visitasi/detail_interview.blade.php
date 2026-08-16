@extends('layouts.app')

@section('content')

{{-- TOMBOL AKSI --}}
<div class="no-print max-w-4xl mx-auto py-6 px-6">
    <div class="flex justify-between items-center">
<a href="{{ $backUrl ?? route('dosen-visitasi.personal-interview') }}" class="text-yellow-600 font-semibold text-sm hover:underline">
    ← Kembali ke Daftar
</a>
        <div class="flex gap-3 items-center">
            <button onclick="downloadPDF()" class="bg-blue-600 text-white text-sm px-4 py-2 hover:bg-blue-700 transition">
                 Download PDF
            </button>
        </div>
    </div>
</div>

{{-- ==================== AREA SURAT RESMI ==================== --}}
<div id="area-cetak" class="surat-container max-w-4xl mx-auto mb-10" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.6; color: #000; text-align: justify;">

    {{-- ============================================ --}}
    {{-- ===== FORM A - VISITOR 1 ===== --}}
    {{-- ============================================ --}}
    <div class="surat-border" style="position: relative; margin-bottom: 40px; border: 1px double #000; padding: 30px 40px;">
        
        {{-- Label A di pojok kanan atas --}}
        <div style="position: absolute; top: 15px; right: 15px; border: 2px solid #000; width: 65px; height: 65px; display: flex; align-items: center; justify-content: center;">
            <span style="font-size: 30pt; font-weight: bold;">A</span>
        </div>

        {{-- Judul --}}
        <div style="text-align: center; margin-bottom: 0;">
            <h2 style="font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin: 0;">
                Personal Interview
            </h2>
            <h2 style="font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin: 0;">
                Visitasi Magang Industri
            </h2>
        </div>

        {{-- ===== 2 GARIS SURAT RESMI (FORM A) ===== --}}
        <div style="border-top: 3px solid #000; margin-top: 10px;"></div>
        <div style="border-top: 1px solid #000; margin-bottom: 20px;"></div>

        {{-- Diisi oleh Visitor --}}
        <p style="font-size: 11pt; font-style: italic; margin-bottom: 15px;">
            Diisi oleh Visitor Magang Industri:
        </p>

        {{-- Identitas Form A - Titik dua sejajar pakai tabel --}}
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 12pt;">
            <tr>
                <td style="width: 230px; vertical-align: top; padding: 2px 0;">Nama Perusahaan</td>
                <td style="width: 15px; vertical-align: top; padding: 2px 0;">:</td>
                <td style="vertical-align: top; padding: 2px 0;"><strong>{{ $perusahaan->nama_perusahaan ?? '-' }}</strong></td>
            </tr>
            <tr>
                <td style="vertical-align: top; padding: 2px 0;">Nama Penerima / Pewawancara</td>
                <td style="vertical-align: top; padding: 2px 0;">:</td>
                <td style="vertical-align: top; padding: 2px 0;">{{ $interviewA->penerima ?? '-' }}</td>
            </tr>
            <tr>
                <td style="vertical-align: top; padding: 2px 0;">Jabatan</td>
                <td style="vertical-align: top; padding: 2px 0;">:</td>
                <td style="vertical-align: top; padding: 2px 0;">{{ $interviewA->jabatan_penerima ?? '-' }}</td>
            </tr>
        </table>

        <div style="border-top: 1px solid #000; margin-bottom: 15px;"></div>

        {{-- Pertanyaan Form A --}}
        <div style="margin-bottom: 25px;">
            <p style="margin-bottom: 5px;">1. Sikap dan Perilaku Peserta Magang?</p>
            <p style="margin-left: 30px; margin-bottom: 12px;">{{ $interviewA->sikap_perilaku ?? '-' }} ..........................................................................................................................................................................</p>

            <p style="margin-bottom: 5px;">2. Kedisiplinan dan Motivasi Peserta Magang?</p>
            <p style="margin-left: 30px; margin-bottom: 12px;">{{ $interviewA->kedisiplinan_motivasi ?? '-' }} ..........................................................................................................................................................................</p>

            <p style="margin-bottom: 5px;">3. Kekurangan yang dimiliki Peserta Magang?</p>
            <p style="margin-left: 30px; margin-bottom: 12px;">{{ $interviewA->kekurangan_peserta ?? '-' }} ..........................................................................................................................................................................</p>

            <p style="margin-bottom: 5px;">4. Peran Peserta Magang di Perusahaan?</p>
            <p style="margin-left: 30px; margin-bottom: 12px;">{{ $interviewA->peran_peserta ?? '-' }} ..........................................................................................................................................................................</p>

            <p style="margin-bottom: 5px;">5. Adaptasi dan Komunikasi Peserta Magang?</p>
            <p style="margin-left: 30px; margin-bottom: 12px;">{{ $interviewA->adaptasi_komunikasi ?? '-' }} ..........................................................................................................................................................................</p>

            <p style="margin-bottom: 5px;">6. Kompetensi dan Kesesuaian Bidang Magang?</p>
            <p style="margin-left: 30px; margin-bottom: 12px;">{{ $interviewA->kompetensi_kesesuaian ?? '-' }} ..........................................................................................................................................................................</p>

            <p style="margin-bottom: 5px;">7. Penempatan dan Tugas Peserta Magang?</p>
            <p style="margin-left: 30px; margin-bottom: 12px;">{{ $interviewA->penempatan_dan_tugas ?? '-' }} ..........................................................................................................................................................................</p>

            <p style="margin-bottom: 5px;">8. Kepuasan Perusahaan Terhadap Peserta Magang?</p>
            <p style="margin-left: 30px; margin-bottom: 12px;">{{ $interviewA->kepuasan_perusahaan ?? '-' }} ..........................................................................................................................................................................</p>

            <p style="margin-bottom: 5px;">9. Saran dan Masukan dari Perusahaan?</p>
            <p style="margin-left: 30px; margin-bottom: 12px;">{{ $interviewA->saran_masukan ?? '-' }} ..........................................................................................................................................................................</p>
        </div>

        {{-- Ucapan Terima Kasih --}}
        <p style="font-style: italic; font-size: 11pt;">
            Terima kasih atas kesediaan Bapak/Ibu untuk memberikan masukan demi pengembangan kualitas pelaksanaan magang industri di masa yang akan datang.
        </p>

    </div>

    {{-- ===== PEMISAH HALAMAN ===== --}}
    <div class="page-break"></div>

    {{-- ============================================ --}}
    {{-- ===== FORM B - VISITOR 2 ===== --}}
    {{-- ============================================ --}}
    <div class="surat-border" style="position: relative; border: 1px double #000; padding: 30px 40px;">
        
        {{-- Label B di pojok kanan atas --}}
        <div style="position: absolute; top: 15px; right: 15px; border: 2px solid #000; width: 65px; height: 65px; display: flex; align-items: center; justify-content: center;">
            <span style="font-size: 30pt; font-weight: bold;">B</span>
        </div>

        {{-- Judul --}}
        <div style="text-align: center; margin-bottom: 0;">
            <h2 style="font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin: 0;">
                Personal Interview
            </h2>
            <h2 style="font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin: 0;">
                Visitasi Magang Industri
            </h2>
        </div>

        {{-- ===== 2 GARIS SURAT RESMI (FORM B) ===== --}}
        <div style="border-top: 3px solid #000; margin-top: 10px;"></div>
        <div style="border-top: 1px solid #000; margin-bottom: 20px;"></div>

        {{-- Diisi oleh Visitor --}}
        <p style="font-size: 11pt; font-style: italic; margin-bottom: 15px;">
            Diisi oleh Visitor Magang Industri:
        </p>

        {{-- Identitas Form B - Titik dua sejajar pakai tabel --}}
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 12pt;">
            <tr>
                <td style="width: 230px; vertical-align: top; padding: 2px 0;">Nama Perusahaan</td>
                <td style="width: 15px; vertical-align: top; padding: 2px 0;">:</td>
                <td style="vertical-align: top; padding: 2px 0;"><strong>{{ $perusahaan->nama_perusahaan ?? '-' }}</strong></td>
            </tr>
            <tr>
                <td style="vertical-align: top; padding: 2px 0;">Alamat</td>
                <td style="vertical-align: top; padding: 2px 0;">:</td>
                <td style="vertical-align: top; padding: 2px 0;">{{ $perusahaan->alamat ?? '-' }}</td>
            </tr>
        </table>

        <div style="border-top: 1px solid #000; margin-bottom: 15px;"></div>

        {{-- Pertanyaan Form B --}}
        <div style="margin-bottom: 25px;">
            <p style="margin-bottom: 5px;">1. Skala Perusahaan? <span style="font-style: italic;">(kecil, sedang, besar dan regional...)</span></p>
            <p style="margin-left: 30px; margin-bottom: 12px;">{{ $interviewB->skala_perusahaan ?? '-' }} ..........................................................................................................................................................................</p>

            <p style="margin-bottom: 5px;">2. Bidang usaha/produksi dari Perusahaan?</p>
            <p style="margin-left: 30px; margin-bottom: 12px;">{{ $interviewB->bidang_usaha ?? '-' }} ..........................................................................................................................................................................</p>

            <p style="margin-bottom: 5px;">3. Penerapan K3 di Perusahaan?</p>
            <p style="margin-left: 30px; margin-bottom: 12px;">{{ $interviewB->penerapan_k3 ?? '-' }} ..........................................................................................................................................................................</p>

            <p style="margin-bottom: 5px;">4. Fasilitas yang diberikan Perusahaan untuk Peserta Magang?</p>
            <p style="margin-left: 30px; margin-bottom: 12px;">{{ $interviewB->fasilitas_magang ?? '-' }} ..........................................................................................................................................................................</p>

            <p style="margin-bottom: 5px;">5. Tanggapan Perusahaan terhadap Peserta Magang?</p>
            <p style="margin-left: 30px; margin-bottom: 12px;">{{ $interviewB->tanggapan_perusahaan ?? '-' }} ..........................................................................................................................................................................</p>

            <p style="margin-bottom: 5px;">6. Kelebihan dari Perusahaan?</p>
            <p style="margin-left: 30px; margin-bottom: 12px;">{{ $interviewB->kelebihan_perusahaan ?? '-' }} ..........................................................................................................................................................................</p>

            <p style="margin-bottom: 5px;">7. Kekurangan dari Perusahaan?</p>
            <p style="margin-left: 30px; margin-bottom: 12px;">{{ $interviewB->kekurangan_perusahaan ?? '-' }} ..........................................................................................................................................................................</p>

            <p style="margin-bottom: 5px;">8. Rekomendasi?</p>
            <p style="margin-left: 30px; margin-bottom: 12px;">{{ $interviewB->rekomendasi ?? '-' }} ..........................................................................................................................................................................</p>
        </div>

        {{-- Catatan Tambahan --}}
        @if($interviewB->catatan)
         <div class="catatan-section" style="margin-bottom: 30px;">
            <p style="margin-bottom: 5px;"><strong>Catatan Tambahan:</strong></p>
            <div style="border: 1px solid #000; padding: 10px; min-height: 50px;">
                <p style="margin: 0;">{{ $interviewB->catatan }} ..........................................................................................................................................................................</p>
            </div>
        </div>
        @endif

        {{-- TTD Form B (HANYA DI B) --}}
        <div style="display: flex; justify-content: flex-end; margin-top: 30px; margin-bottom: 10px;">
            <div style="text-align: center; width: 250px;">
                <p style="margin: 0; font-size: 11pt;">Dosen Visitasi / Pengisi</p>
                <div style="height: 15px;"></div>
                @if($ttdUrl)
                    <div style="display: flex; justify-content: center; margin-bottom: 5px;">
                        <img src="{{ $ttdUrl }}" alt="TTD Digital" class="ttd-bersih"
                             style="width: 180px; height: auto; max-height: 80px; object-fit: contain;">
                    </div>
                @else
                    <div style="height: 70px;"></div>
                @endif
                <div style="border-bottom: 1px solid #000; width: 200px; margin: 0 auto;"></div>
                <p style="margin: 5px 0 0 0; font-size: 10pt;">
                    <strong>{{ $dosenPengisi->nama ?? '............................' }}</strong>
                </p>
                <p style="margin: 0; font-size: 10pt;">NIP. {{ $dosenPengisi->nip ?? $dosenPengisi->nidn ?? '............................' }}</p>
            </div>
        </div>

    </div>

    

</div>

@endsection

{{-- CSS --}}
<style>
    .surat-container {
        border: none !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .ttd-bersih {
        filter: grayscale(100%) contrast(500%) brightness(150%);
    }

    body.generating-pdf .surat-border {
        border: none !important;
    }

    body.generating-pdf .catatan-section {
        display: none !important;
    }

    /* Pemisah Halaman untuk PDF */
    .page-break {
        height: 0;
        display: block;
        page-break-before: always;
        margin: 0;
        border: 0;
    }

    @media print {
        nav, footer, .no-print, 
        .sidebar, .navbar, header { 
            display: none !important; 
        }

        .catatan-section {
            display: none !important;
        }
        main { 
            padding: 0 !important; 
            margin: 0 !important;
        }
        
        .surat-container { 
            box-shadow: none !important;
            padding: 0 !important;
            max-width: 100% !important;
            margin: 0 !important;
        }

        .ttd-bersih {
            filter: grayscale(100%) contrast(500%) brightness(150%) !important;
        }
        
        @page {
            size: A4;
            margin: 15mm;
        }
    }
</style>

{{-- SCRIPT DOWNLOAD PDF --}}
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
                    const r = data[i];
                    const g = data[i + 1];
                    const b = data[i + 2];
                    
                    if (r > 150 && g > 150 && b > 150) {
                        data[i] = 255;
                        data[i + 1] = 255;
                        data[i + 2] = 255;
                    } else {
                        data[i] = 0;
                        data[i + 1] = 0;
                        data[i + 2] = 0;
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
        
        const originalSrcs = [];
        const originalFilters = [];
        
        // 1. Tambahkan class ke body untuk menghilangkan border bingkai (dibaca CSS)
        document.body.classList.add('generating-pdf');
        
        // 2. Bersihkan gambar TTD
        for (let i = 0; i < ttdImages.length; i++) {
            const img = ttdImages[i];
            originalSrcs[i] = img.src;
            originalFilters[i] = img.style.filter;
            
            const cleanSrc = await bersihkanGambarTTD(img);
            img.src = cleanSrc;
            img.style.filter = 'none';
        }
        
        await new Promise(resolve => setTimeout(resolve, 300));
        
        // 3. Generate PDF (Form A, B, dan C digabung jadi 1 file)
        const opt = {
            margin:       [10, 10, 10, 10], // Margin A4
            filename:     'Interview_Visitasi_{{ $perusahaan->nama_perusahaan ?? "dokumen" }}.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { 
                scale: 2, 
                useCORS: true,
                scrollY: 0
                // JANGAN TAMBAHKAN height / windowHeight DI SINI, NANTI Form B & C GAK MASUK PDF!
            },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak:    { mode: ['css', 'legacy'] }
        };
        
        // 4. Proses download
        html2pdf().set(opt).from(element).save().then(() => {
            // 5. Kembalikan semuanya seperti semula setelah PDF selesai terdownload
            document.body.classList.remove('generating-pdf');
            
            for (let i = 0; i < ttdImages.length; i++) {
                ttdImages[i].src = originalSrcs[i];
                ttdImages[i].style.filter = originalFilters[i];
            }
        });
    }
</script>