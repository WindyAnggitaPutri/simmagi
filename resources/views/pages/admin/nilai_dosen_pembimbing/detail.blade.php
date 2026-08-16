@extends('layouts.admin')

@section('title', 'Detail Nilai Dosen Pembimbing')

@section('page-title', 'Detail Penilaian Dospem')


@push('styles')
<style>
    .surat-container { border: 2px solid #000 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.1); background: #fff; width: 210mm; min-height: 297mm; box-sizing: border-box; margin: 0 auto; }
    .ttd-bersih { filter: grayscale(100%) contrast(500%) brightness(150%); }
    
    /* Hilangkan garis tepi saat di-print (Ctrl+P) maupun download PDF */
    @media print {
        nav, footer, .no-print, aside, header { display: none !important; } 
        main { padding: 0 !important; margin: 0 !important; } 
        .surat-container { 
            box-shadow: none !important; 
            width: 100% !important; 
            margin: 0 !important; 
            border: none !important; 
        } 
        .ttd-bersih { filter: grayscale(100%) contrast(500%) brightness(150%) !important; } 
        @page { size: A4; margin: 10mm 15mm; } 
    }
</style>
@endpush

@section('content')
{{-- Wrapper utama agar bisa di-scroll jika layar kecil --}}
<div class="w-full overflow-x-auto py-10">
    
    {{-- Wadah seukuran A4 yang diposisikan di tengah --}}
    <div class="mx-auto" style="width: 210mm; min-width: 210mm; padding: 0 5px;">

        {{-- TOMBOL AKSI --}}
        <div class="no-print flex justify-between items-center mb-4">
            <a href="{{ route('admin.penilaian-dospem.index') }}" class="inline-flex items-center gap-2 text-sm text-[#b45309] hover:text-[#92400e] font-semibold hover:underline transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Kembali ke Daftar Nilai
            </a>
            
            <button onclick="downloadPDF()" class="bg-[#b45309] hover:bg-[#92400e] text-white text-sm px-4 py-2 rounded-lg transition inline-flex items-center gap-2 font-medium">
                <!-- <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> -->
                Download PDF
            </button>
        </div>

        <div id="area-cetak" class="surat-container" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; text-align: justify; padding: 10mm 15mm;">
            {{-- KOP SURAT DENGAN LOGO --}}
            <div style="text-align: center; margin-bottom: 20px; position: relative; padding: 0 10px;">
                <img src="{{ asset('images/logo-PNC.png') }}" alt="Logo PNC" style="position: absolute; top: 35%; left: 0; transform: translateY(-50%); width: 140px; height: 120px; object-fit: contain;">
                <div style="position: absolute; top: 50%; right: 0; transform: translateY(-50%); width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;"></div>
                <div style="text-align: center; padding: 0 80px;">
                    <p style="font-size: 11pt; font-weight: bold; margin: 0; text-transform: uppercase; letter-spacing: 1px;">Kementerian Pendidikan Tinggi, Sains, dan Teknologi</p>
                    <h2 style="font-size: 14pt; font-weight: bold; margin: 3px 0 0 0; text-transform: uppercase; letter-spacing: 1.5px;">Jurusan Komputer dan Bisnis</h2>
                </div>
                <div style="border-bottom: 3px solid #000; padding-bottom: 8px; margin-top: 10px; text-align: center;">
                    <p style="font-size: 9pt; margin: 1px 0; font-style: italic;">Jalan Dr. Soetomo No. 1, Sidakaya - Cilacap 53212 Jawa Tengah</p>
                    <p style="font-size: 9pt; margin: 1px 0;">Telepon: (0282) 533329, Fax: (0282) 537992</p>
                    <p style="font-size: 9pt; margin: 1px 0; font-weight: bold;">
                        <a href="https://www.pnc.ac.id" target="_blank" style="color: blue;">www.pnc.ac.id</a>, Email: 
                        <a href="mailto:sekretariat@pnc.ac.id" style="color: blue;">sekretariat@pnc.ac.id</a>
                    </p>
                </div>
            </div>

            {{-- JUDUL SURAT --}}
            <h3 style="text-align: center; font-weight: bold; font-size: 13pt; text-transform: uppercase; margin-bottom: 1px;">Penilaian Seminar Magang Industri</h3>
            <p style="text-align: center; font-size: 11pt; margin-bottom: 15px;">Hasil Evaluasi Seminar Magang Industri Untuk Mahasiswa:</p>

            {{-- DATA MAHASISWA --}}
            <div style="margin-bottom: 15px;">
                <table style="width: 100%; font-size: 11pt;">
                    <tr><td style="width: 22%; vertical-align: top; padding: 1px 0;">Nama</td><td style="width: 3%; vertical-align: top;">:</td><td style="vertical-align: top;">{{ $jadwal->mahasiswa->nama ?? '-' }}</td></tr>
                    <tr><td style="vertical-align: top; padding: 1px 0;">Judul Laporan</td><td style="vertical-align: top;">:</td><td style="vertical-align: top;">{{ $jadwal->judul_laporan ?? '-' }}</td></tr>
                    <tr><td style="vertical-align: top; padding: 1px 0;">Hari / Tanggal</td><td style="vertical-align: top;">:</td><td style="vertical-align: top;">{{ \Carbon\Carbon::parse($jadwal->tgl_semgang)->locale('id')->translatedFormat('d F Y') }}</td></tr>
                    <tr><td style="vertical-align: top; padding: 1px 0;">Waktu</td><td style="vertical-align: top;">:</td><td style="vertical-align: top;">{{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }} WIB</td></tr>
                    <tr><td style="vertical-align: top; padding: 1px 0;">Ruang</td><td style="vertical-align: top;">:</td><td style="vertical-align: top;">{{ $jadwal->ruang ?? '-' }}</td></tr>
                </table>
            </div>

            {{-- TABEL NILAI --}}
            <table style="border-collapse: collapse; width: 100%; margin-bottom: 15px; font-size: 11pt;">
                <thead><tr style="background-color: #f9f9f9;"><th style="border: 2px solid #000; padding: 6px; width: 5%;">No</th><th style="border: 2px solid #000; padding: 6px;">Sasaran Penilaian</th><th style="border: 2px solid #000; padding: 6px; width: 15%; text-align: center;">Range Nilai</th><th style="border: 2px solid #000; padding: 6px; width: 12%; text-align: center;">Nilai</th></tr></thead>
                <tbody>
                    <tr><td style="border: 2px solid #000; padding: 6px; text-align: center;">1</td><td style="border: 2px solid #000; padding: 6px;">Persiapan seminar magang industri</td><td style="border: 2px solid #000; padding: 6px; text-align: center;">0 - 100</td><td style="border: 2px solid #000; padding: 6px; text-align: center; font-weight: bold;">{{ $jadwal->nilai_persiapan ?? '-' }}</td></tr>
                    <tr><td style="border: 2px solid #000; padding: 6px; text-align: center;">2</td><td style="border: 2px solid #000; padding: 6px;">Sikap dan penampilan</td><td style="border: 2px solid #000; padding: 6px; text-align: center;">0 - 100</td><td style="border: 2px solid #000; padding: 6px; text-align: center; font-weight: bold;">{{ $jadwal->nilai_sikap ?? '-' }}</td></tr>
                    <tr><td style="border: 2px solid #000; padding: 6px; text-align: center;">3</td><td style="border: 2px solid #000; padding: 6px;">Penguasaan materi dan objektifitas dalam menanggapi pertanyaan</td><td style="border: 2px solid #000; padding: 6px; text-align: center;">0 - 100</td><td style="border: 2px solid #000; padding: 6px; text-align: center; font-weight: bold;">{{ $jadwal->nilai_penguasaan ?? '-' }}</td></tr>
                    <tr><td style="border: 2px solid #000; padding: 6px; text-align: center;">4</td><td style="border: 2px solid #000; padding: 6px;">Kemampuan menjelaskan dan mempertahankan ide</td><td style="border: 2px solid #000; padding: 6px; text-align: center;">0 - 100</td><td style="border: 2px solid #000; padding: 6px; text-align: center; font-weight: bold;">{{ $jadwal->nilai_mempertahankan_ide ?? '-' }}</td></tr>
                    <tr><td style="border: 2px solid #000; padding: 6px; text-align: center;">5</td><td style="border: 2px solid #000; padding: 6px;">Hasil yang dicapai dari pelaksanaan magang industri</td><td style="border: 2px solid #000; padding: 6px; text-align: center;">0 - 100</td><td style="border: 2px solid #000; padding: 6px; text-align: center; font-weight: bold;">{{ $jadwal->nilai_hasil_magang ?? '-' }}</td></tr>
                    <tr><td style="border: 2px solid #000; padding: 6px; text-align: center;">6</td><td style="border: 2px solid #000; padding: 6px;">Penyajian penulisan laporan magang industri</td><td style="border: 2px solid #000; padding: 6px; text-align: center;">0 - 100</td><td style="border: 2px solid #000; padding: 6px; text-align: center; font-weight: bold;">{{ $jadwal->nilai_penulisan ?? '-' }}</td></tr>
                    <tr style="background-color: #f9f9f9; font-weight: bold;"><td colspan="3" style="border: 2px solid #000; padding: 6px; text-align: right;">Total</td><td style="border: 2px solid #000; padding: 6px; text-align: center;">{{ $jadwal->total_nilai ?? '-' }}</td></tr>
                    <tr style="background-color: #f9f9f9; font-weight: bold;"><td colspan="3" style="border: 2px solid #000; padding: 6px; text-align: right;">Nilai Rata-Rata</td><td style="border: 2px solid #000; padding: 6px; text-align: center;">{{ $jadwal->rata_rata ?? '-' }}</td></tr>
                </tbody>
            </table>

            {{-- TTD DOSEN --}}
            <div style="display: flex; justify-content: flex-end; margin-top: 15px; margin-bottom: 25px;">
                <div style="text-align: center; width: 35%;">
                    @if($jadwal->status_semgang == 'selesai' && $jadwal->updated_at)<p style="margin: 0;">Cilacap, {{ \Carbon\Carbon::parse($jadwal->updated_at)->locale('id')->translatedFormat('d F Y') }}</p>@else<p style="margin: 0;">Cilacap, {{ \Carbon\Carbon::parse($jadwal->tgl_semgang)->locale('id')->translatedFormat('d F Y') }}</p>@endif
                    <p style="margin: 0; font-weight: medium;">Dosen Pembimbing / Penguji</p><div style="height: 8px;"></div>
                    @if($jadwal->status_semgang == 'selesai')
                        @if($jadwal->dosenPembimbing->dosen->ttd_digital)<div style="display: flex; justify-content: center; margin-bottom: 3px;"><img src="{{ asset('storage/ttd_digital/' . $jadwal->dosenPembimbing->dosen->ttd_digital) }}" alt="TTD Dospem" class="ttd-bersih" style="width: 180px; height: auto; max-height: 80px; object-fit: contain;"></div>
                        @else<p style="font-weight: bold; font-size: 16pt; font-style: italic; color: #1e3a5f; margin: 3px 0; font-family: 'Brush Script MT', cursive, serif;">{{ $jadwal->dosenPembimbing->dosen->nama ?? '..............................' }}</p>@endif
                        <p style="margin-top: 3px; font-size: 10pt;"><strong>{{ $jadwal->dosenPembimbing->dosen->nama ?? '-' }}</strong><br>NIDN. {{ $jadwal->dosenPembimbing->dosen->nidn ?? '-' }}</p>
                    @else<div style="height: 60px;"></div><div style="border-bottom: 2px solid #000; width: 180px; margin: 0 auto;"></div><p style="margin-top: 3px; font-size: 10pt;">NIDN. ................................</p>@endif
                </div>
            </div>

            {{-- CATATAN KETERANGAN NILAI --}}
            <div style="padding-top: 3px; margin-top: 3px;">
                <p style="font-weight: bold; margin-bottom: 6px;">Catatan :</p>
                <table style="border-collapse: collapse; width: 50%; font-size: 10pt;">
                    <thead><tr style="background-color: #f9f9f9;"><th style="border: 2px solid #000; padding: 4px 8px; text-align: center; width: 40%;">Range Nilai</th><th style="border: 2px solid #000; padding: 4px 8px; text-align: center; width: 60%;">Keterangan</th></tr></thead>
                    <tbody>
                        <tr><td style="border: 2px solid #000; padding: 3px 8px; text-align: center;">80,0 - 100</td><td style="border: 2px solid #000; padding: 3px 8px;">Sangat Istimewa</td></tr>
                        <tr><td style="border: 2px solid #000; padding: 3px 8px; text-align: center;">75,0 - 79,9</td><td style="border: 2px solid #000; padding: 3px 8px;">Istimewa</td></tr>
                        <tr><td style="border: 2px solid #000; padding: 3px 8px; text-align: center;">65 - 74,9</td><td style="border: 2px solid #000; padding: 3px 8px;">Baik</td></tr>
                        <tr><td style="border: 2px solid #000; padding: 3px 8px; text-align: center;">60,0 - 64,9</td><td style="border: 2px solid #000; padding: 3px 8px;">Cukup Baik</td></tr>
                        <tr><td style="border: 2px solid #000; padding: 3px 8px; text-align: center;">50 - 59,9</td><td style="border: 2px solid #000; padding: 3px 8px;">Cukup</td></tr>
                        <tr><td style="border: 2px solid #000; padding: 3px 8px; text-align: center;">40 - 49,9</td><td style="border: 2px solid #000; padding: 3px 8px;">Kurang</td></tr>
                        <tr><td style="border: 2px solid #000; padding: 3px 8px; text-align: center;">0 - 39,9</td><td style="border: 2px solid #000; padding: 3px 8px;">Gagal</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function bersihkanGambarTTD(imgElement){return new Promise((resolve)=>{const canvas=document.createElement('canvas');const ctx=canvas.getContext('2d');canvas.width=imgElement.naturalWidth;canvas.height=imgElement.naturalHeight;const img=new Image();img.crossOrigin='Anonymous';img.src=imgElement.src;img.onload=function(){ctx.fillStyle='#FFFFFF';ctx.fillRect(0,0,canvas.width,canvas.height);ctx.drawImage(img,0,0,canvas.width,canvas.height);const imageData=ctx.getImageData(0,0,canvas.width,canvas.height);const data=imageData.data;for(let i=0;i<data.length;i+=4){const r=data[i];const g=data[i+1];const b=data[i+2];if(r>150&&g>150&&b>150){data[i]=255;data[i+1]=255;data[i+2]=255;data[i+3]=255}else{data[i]=0;data[i+1]=0;data[i+2]=0;data[i+3]=255}}ctx.putImageData(imageData,0,0);resolve(canvas.toDataURL('image/png'))};img.onerror=function(){resolve(imgElement.src)}})}

    async function downloadPDF(){
        const element=document.getElementById('area-cetak');
        
     
        element.style.setProperty('border', 'none', 'important');

        const ttdImages=element.querySelectorAll('.ttd-bersih');
        const originalSrcs=[];
        const originalFilters=[];
        for(let i=0;i<ttdImages.length;i++){
            const img=ttdImages[i];
            originalSrcs[i]=img.src;
            originalFilters[i]=img.style.filter;
            const cleanSrc=await bersihkanGambarTTD(img);
            img.src=cleanSrc;
            img.style.filter='none'
        }
        
        await new Promise(resolve=>setTimeout(resolve,500));
        const fullHeight=element.scrollHeight;
        const opt={margin:[10,15,10,15],filename:'Penilaian_Seminar_Magang.pdf',image:{type:'jpeg',quality:0.98},html2canvas:{scale:2,useCORS:true,scrollY:0,windowHeight:fullHeight,height:fullHeight,width:element.scrollWidth},jsPDF:{unit:'mm',format:'a4',orientation:'portrait'},pagebreak:{mode:['css','legacy']}};
        
        html2pdf().set(opt).from(element).save().then(()=>{
            for(let i=0;i<ttdImages.length;i++){
                ttdImages[i].src=originalSrcs[i];
                ttdImages[i].style.filter=originalFilters[i]
            }
         
            element.style.removeProperty('border');
        })
    }
</script>
@endpush