@extends('layouts.app')

@section('content')

{{-- TOMBOL AKSI --}}
<div class="no-print max-w-4xl mx-auto py-6 px-6">
    <div class="flex justify-between items-center">
        <a href="{{ route('mahasiswa.pengajuan_magang.index') }}" class="text-yellow-600 font-semibold text-sm hover:underline">
             Kembali ke Riwayat
        </a>
        <div class="flex gap-3 items-center">
            
            {{-- TAMBAHKAN BADGE STATUS INI --}}
            <span class="border text-xs font-bold px-3 py-2 rounded {{ $statusAlur['color'] }}">
                {{ $statusAlur['text'] }}
            </span>

            @if ($pengajuan->surat_pengajuan)
                <a href="{{ asset('storage/surat_pengajuan/' . $pengajuan->surat_pengajuan) }}" target="_blank" 
                   class="border border-gray-300 text-sm px-4 py-2 hover:bg-gray-100 transition">
                     File Upload Asli
                </a>
            @endif
            <button onclick="downloadPDF()" class="bg-blue-600 text-white text-sm px-4 py-2 hover:bg-blue-700 transition">
                 Download PDF
            </button>
        </div>
    </div>

    {{-- CATATAN PENGAJU (DI LUAR SURAT, TIDAK IKUT PDF) --}}
    @if ($pengajuan->catatan)
    <div class="mt-4 border border-yellow-400 bg-yellow-50 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="fas fa-sticky-note text-yellow-600 text-sm"></i>
            </div>
            <div>
                <h4 class="font-bold text-yellow-800 text-sm mb-1">Catatan Pengaju</h4>
                <p class="text-yellow-700 text-sm">{{ $pengajuan->catatan }}</p>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- AREA SURAT RESMI --}}
<div id="area-cetak" class="surat-container max-w-4xl mx-auto px-16 py-12 mb-10" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.6; color: #000; text-align: justify;">

{{-- KOP SURAT --}}
<div style="text-align: center; margin-bottom: 15px; position: relative; padding: 0 20px;">
    
    {{-- POSISI LOGO (Absolute: Kiri & Kanan Menempel) --}}
    {{-- Logonya akan otomatis mencari posisi tengah dari seluruh area ini --}}
    <img src="{{ asset('images/logo-PNC.png') }}" 
         alt="Logo Kiri" 
         style="position: absolute; top: 35%; left: 0; transform: translateY(-50%); width: 180px; height: 160px; object-fit: contain;">
    
    <div style="position: absolute; top: 50%; right: 0; transform: translateY(-50%); width: 90px; height: 90px; display: flex; align-items: center; justify-content: center;">
        {{-- Ganti tag div di bawah ini dengan tag img jika punya logo kanan --}}
        {{-- <img src="..." style="width: 90px; height: 90px; object-fit: contain;"> --}}
    </div>

    {{-- TEKS TENGAH --}}
    <div style="text-align: center; padding: 0 100px;"> {{-- Padding kiri kanan agar teks ketabrak logo --}}
        <p style="font-size: 12pt; font-weight: bold; margin: 0; text-transform: uppercase; letter-spacing: 1px;">
            Kementerian Pendidikan Tinggi, Sains, dan Teknologi
        </p>
       
        <h2 style="font-size: 16pt; font-weight: bold; margin: 5px 0 0 0; text-transform: uppercase; letter-spacing: 1.5px;">
            Jurusan Komputer dan Bisnis
        </h2>
    </div>

    {{-- BAGIAN 2: ALAMAT & GARIS PANJANG --}}
    <div style="border-bottom: 3px solid #000; padding-bottom: 10px; margin-top: 15px; text-align: center;">
        <p style="font-size: 10pt; margin: 2px 0; font-style: italic;">
            Jalan Dr. Soetomo No. 1, Sidakaya - Cilacap 53212 Jawa Tengah
        </p>
        <p style="font-size: 10pt; margin: 2px 0;">
            Telepon: (0282) 533329, Fax: (0282) 537992
        </p>
        
        {{-- LINK WEBSITE & EMAIL BIRU --}}
        <p style="font-size: 10pt; margin: 2px 0; font-weight: bold;">
            <a href="https://www.pnc.ac.id" target="_blank" style="color: blue;">www.pnc.ac.id</a>, Email: 
            <a href="mailto:sekretariat@pnc.ac.id" style="color: blue;">sekretariat@pnc.ac.id</a>
        </p>
    </div>

</div>

    {{-- KODE SURAT & PERIHAL --}}
    <div style="text-align: right; margin-bottom: 2px; font-size:9pt; ">
        FM.PM-PMI-C.01-R.0
    </div>
    <p style="margin-bottom: 5px;">
        <strong>Perihal</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Permohonan Ijin Magang Industri
    </p>

    {{-- TUJUAN SURAT --}}
    <p style="margin-bottom: 10px;">
        Yth. Kordinator Magang Industri<br>
        Politeknik Negeri Cilacap<br>
        di – Cilacap
    </p>

    {{-- ISI PEMBUKA --}}
    <p style="margin-bottom: 0px;">
        Dengan ini mohon dibuatkan surat ijin magang Industri atas nama mahasiswa dibawah ini :
    </p>

    {{-- TABEL MAHASISWA --}}
    <table style="border-collapse: collapse; width: 100%; margin-bottom: 30px; font-size: 11pt;">
        <thead>
            <tr style="background-color: #f9f9f9;">
                <th style="border: 1px solid #000; padding: 8px; width: 5%;">No</th>
                <th style="border: 1px solid #000; padding: 8px;">Nama Mahasiswa</th>
                <th style="border: 1px solid #000; padding: 8px; width: 15%;">NIM</th>
                <th style="border: 1px solid #000; padding: 8px; width: 25%;">Program Studi</th>
                <th style="border: 1px solid #000; padding: 8px; width: 10%;">IPK</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pengajuan->mahasiswa as $index => $pm)
            <tr>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #000; padding: 8px;">{{ $pm->mahasiswa->nama ?? '-' }}</td>
                <td style="border: 1px solid #000; padding: 8px;">{{ $pm->mahasiswa->npm ?? '-' }}</td>
                <td style="border: 1px solid #000; padding: 8px;">{{ $pm->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">{{ $pm->ipk ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- {{-- DETAIL INFO PERUSAHAAN --}}
    <div style="margin-bottom: 30px;">
        <p>1. Nama Perusahaan Tujuan &nbsp;&nbsp;&nbsp;&nbsp;: <strong>{{ $pengajuan->perusahaan->nama_perusahaan ?? '-' }}</strong></p>
        <p>2. Ditujukan kepada &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->ditujukan_kepada ?: '-' }}</p>
        <p>3. Alamat lengkap &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->perusahaan->alamat ?? '-' }}</p>
        <p>4. No. Telepon/e-mail &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->email_perusahaan ?: '-' }}</p>
        <p>5. Contact Person di Perusahaan &nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->contact_person ?? '-' }}</p>
        <p>6. No. HP CP di Perusahaan &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->no_hp_cp ?? '-' }}</p>
        <p>7. Waktu Pelaksanaan Magang &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ 
            \Carbon\Carbon::parse($pengajuan->tgl_mulai)->locale('id')->translatedFormat('F Y') . ' - ' . 
            \Carbon\Carbon::parse($pengajuan->tgl_selesai)->locale('id')->translatedFormat('F Y') 
        }}</p>
        <p>8. Deskripsi Perusahaan &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->deskripsi_perusahaan ?? '-' }}</p>
        <p>9. Nama Perwakilan Kelompok &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->mahasiswa->first()->mahasiswa->nama ?? '-' }}</p>
        <p>10. No. HP Perwakilan Kelompok &nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->mahasiswa->first()->mahasiswa->no_hp ?? '-' }}</p>
    </div> -->
    {{-- DETAIL INFO PERUSAHAAN --}}
    <table style="border-collapse: collapse; width: 100%; margin-bottom: 30px; font-size: 11pt;">
        <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">1.</td>

            <td style="width:25%; padding:3px 0; vertical-align:top;">Nama Perusahaan Tujuan</td>

            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>

            <td style="padding:3px 0; vertical-align:top;">
                {{ $pengajuan->perusahaan->nama_perusahaan ?? '-' }}
            </td>
        </tr>
        <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">2.</td>

            <td style="width:25%; padding:3px 0; vertical-align:top;">Ditunjukan Kepada</td>

            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>

            <td style="padding:3px 0; vertical-align:top;">
                {{ $pengajuan->ditujukan_kepada ?: '-' }}
            </td>
        </tr>

          <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">3.</td>

            <td style="width:25%; padding:3px 0; vertical-align:top;">Alamat Lengkap</td>

            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>

            <td style="padding:3px 0; vertical-align:top;">
                {{ $pengajuan->perusahaan->alamat ?? '-' }}
            </td>
        </tr>

          <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">4.</td>

            <td style="width:25%; padding:3px 0; vertical-align:top;">No. Telepon/e-mail</td>

            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>

            <td style="padding:3px 0; vertical-align:top;">
               {{ $pengajuan->email_perusahaan ?: '-' }}
            </td>
        </tr>

          <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">5.</td>

            <td style="width:25%; padding:3px 0; vertical-align:top;">Contact Person di Perusahaan</td>

            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>

            <td style="padding:3px 0; vertical-align:top;">
               {{ $pengajuan->contact_person ?? '-' }}
            </td>
        </tr>

          <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">6.</td>

            <td style="width:25%; padding:3px 0; vertical-align:top;">No. HP CP di Perusahaan</td>

            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>

            <td style="padding:3px 0; vertical-align:top;">
               {{ $pengajuan->no_hp_cp ?? '-' }}
            </td>
        </tr>
          <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">7.</td>

            <td style="width:25%; padding:3px 0; vertical-align:top;">Waktu Pelaksanaan Magang</td>

            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>

            <td style="padding:3px 0; vertical-align:top;">
               {{ 
                \Carbon\Carbon::parse($pengajuan->tgl_mulai)->locale('id')->translatedFormat('F Y') . ' - ' . 
                \Carbon\Carbon::parse($pengajuan->tgl_selesai)->locale('id')->translatedFormat('F Y') 
            }}
            </td>
        </tr>
        <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">8.</td>

            <td style="width:25%; padding:3px 0; vertical-align:top;">Deskripsi Perusahaan</td>

            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>

            <td style="padding:3px 0; vertical-align:top;">
               {{ $pengajuan->deskripsi_perusahaan ?? '-' }}
            </td>
        </tr>
        <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">9.</td>

            <td style="width:25%; padding:3px 0; vertical-align:top;">Nama Perwakilan Kelompok</td>

            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>

            <td style="padding:3px 0; vertical-align:top;">
               {{ $pengajuan->mahasiswa->first()->mahasiswa->nama ?? '-' }}
            </td>
        </tr>

        <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">10.</td>

            <td style="width:25%; padding:3px 0; vertical-align:top;">No. HP Perwakilan Kelompok</td>

            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>

            <td style="padding:3px 0; vertical-align:top;">
               {{ $pengajuan->mahasiswa->first()->mahasiswa->no_hp ?? '-' }}
            </td>
        </tr>
        
       
    </table>


    {{-- PENUTUP --}}
    <p style="margin-bottom: 10px;">
        Demikian permohonan ini, atas perhatian dan kerjasamanya, kami ucapkan terima kasih.
    </p>

    {{-- GABUNGAN: TANGGAL & TTD (PASTI SEJAJAR 100%) --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 0 20px; margin-top: 20px; margin-bottom: 40px;">
        
        {{-- KOLOM KIRI: TTD Koordinator --}}
        <div style="text-align: center; width: 35%;">
            <p style="margin: 0;">Menyetujui,<br>Kordinator Magang Industri</p>
            <div style="height: 20px;"></div>
            
            {{-- LOGIKA TTD KAPRODI (CONTEKAN DARI KAPRODI) --}}
            @if($approvalKaprodi && $approvalKaprodi->status == 'disetujui' && $dosen && $dosen->ttd_digital)
                {{-- TTD DIGITAL KAPRODI MUNCUL SETELAH APPROVE --}}
                <div style="display: flex; justify-content: center; margin-bottom: 5px;">
                    <img src="{{ asset('storage/ttd_digital/' . $dosen->ttd_digital) }}" 
                         alt="TTD Kaprodi" class="ttd-bersih"
                         style="width: 200px; height: auto; max-height: 100px; object-fit: contain;">
                </div>
                <p style="margin-top: 5px; font-size: 10pt;">
                    {{ $dosen->nama }}<br>
                    NIDN. {{ $dosen->nidn }}
                </p>
            @else
                {{-- BELUM APPROVE: GARIS KOSONG --}}
                <div style="height: 80px;"></div>
                <div style="border-bottom: 1px solid #000; width: 200px; margin: 0 auto;"></div>
                <p style="margin-top: 5px; font-size: 10pt;">NIDN. ................................</p>
            @endif
            {{-- SELESAI LOGIKA TTD --}}
        </div>

        {{-- KOLOM KANAN: Tanggal + TTD Perwakilan --}}
        <div style="width: 35%;">
            <div style="text-align: center;">
                Cilacap, {{ \Carbon\Carbon::parse($pengajuan->tgl_pengajuan)->locale('id')->translatedFormat('d F Y') }}
            </div>
            <div style="text-align: center;">
                <p style="margin: 0;">Perwakilan Kelompok</p>
                <div style="height: 20px;"></div>
                @if($pengajuan->mahasiswa->first()->mahasiswa->ttd_digital)
                    <div style="display: flex; justify-content: center; margin-bottom: 5px;">
                        <img src="{{ asset('storage/ttd_digital/' . $pengajuan->mahasiswa->first()->mahasiswa->ttd_digital) }}" 
                             alt="TTD Digital" class="ttd-bersih"
                             style="width: 200px; height: auto; max-height: 100px; object-fit: contain;">
                    </div>
                @else
                    <div style="height: 80px;"></div>
                    <div style="border-bottom: 1px solid #000; width: 200px; margin: 0 auto;"></div>
                @endif
                <p style="margin-top: 5px; font-size: 10pt;">
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

            <div class="surat-container max-w-4xl mx-auto px-16 py-12 mb-10" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; color: #000;">
                
                <h3 style="text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 5px;">
                    LAMPIRAN SURAT IZIN ORANG TUA
                </h3>
                <p style="text-align: center; font-size: 11pt; margin-bottom: 20px;">
                    {{ $pm->mahasiswa->nama ?? '-' }} ({{ $pm->mahasiswa->npm ?? '-' }})
                </p>

                <div style="border-top: 1px solid #000; margin-bottom: 20px;"></div>

                @if (strtolower($extension) == 'pdf')
                    <embed src="{{ $filePath }}#toolbar=0&navpanes=0&scrollbar=0" 
                           type="application/pdf" 
                           style="width: 100%; height: 900px; border: none;">
                @else
                    <img src="{{ $filePath }}" alt="Surat Izin Orang Tua" style="width: 100%; height: auto; display: block;">
                @endif

            </div>
        @endif
    @endforeach
</div>

@endsection

{{-- CSS --}}
<style>
    .surat-container {
        border: none !important; /* <-- Garis dihilangkan */
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
    .pdf-viewer {
    width: 100%;
    height: 100%;
    overflow: hidden;
}
</style>
{{-- SCRIPT UNTUK DOWNLOAD PDF LANGSUNG --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    // Fungsi ajaib untuk memutihkan background TTD memakai Canvas
    function bersihkanGambarTTD(imgElement) {
        return new Promise((resolve) => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            // Gunakan ukuran asli gambar agar kualitasnya bagus
            canvas.width = imgElement.naturalWidth;
            canvas.height = imgElement.naturalHeight;
            
            const img = new Image();
            img.crossOrigin = 'Anonymous'; // Biar bisa baca gambar dari storage
            img.src = imgElement.src;
            
            img.onload = function() {
                // 1. Isi dulu canvas dengan warna PUTIH MUTLAK
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                
                // 2. Gambar TTD di atas background putih tadi
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                
                // 3. Ambil data pixel gambarnya
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const data = imageData.data;
                
                // 4. Looping setiap pixel untuk dibersihkan
                for (let i = 0; i < data.length; i += 4) {
                    const r = data[i];
                    const g = data[i + 1];
                    const b = data[i + 2];
                    
                    // Jika pixel agak terang (kemungkinan background kotor), JADIKAN PUTIH
                    if (r > 150 && g > 150 && b > 150) {
                        data[i] = 255;     // Red
                        data[i + 1] = 255; // Green
                        data[i + 2] = 255; // Blue
                    } else {
                        // Jika pixel gelap (tinta TTD), JADIKAN HITAM PEKAT
                        data[i] = 0;
                        data[i + 1] = 0;
                        data[i + 2] = 0;
                    }
                }
                
                // 5. Kembalikan pixel yang sudah bersih ke canvas
                ctx.putImageData(imageData, 0, 0);
                
                // 6. Ubah canvas jadi gambar Base64
                resolve(canvas.toDataURL('image/png'));
            };
            
            img.onerror = function() {
                resolve(imgElement.src); // Kalau gagal, pakai gambar asli aja
            };
        });
    }

async function downloadPDF() {
    const element = document.getElementById('area-cetak');
    const ttdImages = element.querySelectorAll('.ttd-bersih');
    
    // Simpan state asli
    const originalPadding = element.style.padding;
    const originalOverflow = element.style.overflow;
    const originalSrcs = [];
    const originalFilters = [];
    
    // Siapkan element untuk PDF
    element.style.padding = '0';
    element.style.overflow = 'visible';
    
    // Proses gambar TTD jadi bersih via Canvas
    for (let i = 0; i < ttdImages.length; i++) {
        const img = ttdImages[i];
        originalSrcs[i] = img.src;
        originalFilters[i] = img.style.filter;
        
        const cleanSrc = await bersihkanGambarTTD(img);
        img.src = cleanSrc;
        img.style.filter = 'none'; // ✅ HAPUS FILTER CSS (canvas sudah bersih, jangan dobel proses)
    }
    
    // ⏳ TUNGGU DOM UPDATE — INI KUNCI UTAMANYA!
    await new Promise(resolve => setTimeout(resolve, 500));
    
    // 📏 HITUNG ULANG TINGGI SETELAH DOM UPDATE
    const fullHeight = element.scrollHeight;
    
    const opt = {
        margin:       [5, 10, 5, 10], 
        filename:     'Surat_Pengajuan_Magang.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { 
            scale: 2, 
            useCORS: true,
            scrollY: 0,
            windowHeight: fullHeight,  // ✅ PAKAI TINGGI TERBARU
            height: fullHeight
        },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak:    { mode: ['css', 'legacy'] }
    };
    
    // Generate PDF
    html2pdf().set(opt).from(element).save().then(() => {
        // ✅ RESTORE SEMUA KE KEADAAN SEMULA
        element.style.padding = originalPadding;
        element.style.overflow = originalOverflow;
        
        for (let i = 0; i < ttdImages.length; i++) {
            ttdImages[i].src = originalSrcs[i];
            ttdImages[i].style.filter = originalFilters[i];
        }
    });
}
</script>