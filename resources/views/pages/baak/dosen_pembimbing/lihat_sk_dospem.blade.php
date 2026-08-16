@extends('layouts.app')

@section('content')

{{-- TOMBOL ATAS --}}
<div class="no-print max-w-4xl mx-auto py-6 px-6">
    <div class="flex justify-between items-center">
        <a href="{{ $backUrl }}" class="text-yellow-600 font-semibold text-sm hover:underline">
            ← Kembali
        </a>
        <div class="flex gap-3">
            <button onclick="downloadPDF()" class="bg-yellow-500 text-white text-sm px-4 py-2 rounded-lg hover:bg-yellow-600 transition inline-flex items-center gap-2 font-semibold">
                📥 Download PDF
            </button>
        </div>
    </div>
</div>

{{-- AREA KONTEN --}}
<div class="max-w-4xl mx-auto px-6 mb-10">

    @if ($fileSk)
        @php $extension = pathinfo($fileSk, PATHINFO_EXTENSION); @endphp
        @php $filePath = asset('storage/sk_dospem/' . $fileSk); @endphp

        {{-- INFO PRODI --}}
        <div class="mb-4 flex items-center gap-2 no-print">
            <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-bold px-3 py-1.5 rounded border border-green-300">
                ✅ SK Dosen Pembimbing — {{ $prodi->nama_prodi }}
            </span>
        </div>

        {{-- TAMPILAN SK --}}
        <div id="area-cetak" class="surat-container px-16 py-12" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.6; color: #000; text-align: justify; border: 1px solid #000; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            
            <h3 style="text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 15px;">
                SK DOSEN PEMBIMBING MAGANG INDUSTRI
            </h3>

            <div style="display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 11pt;">
                <div>
                    <div style="font-weight: bold;">Program Studi</div>
                    <div>{{ $prodi->nama_prodi }}</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-weight: bold;">Jurusan</div>
                    <div>{{ $prodi->jurusan->nama_jurusan ?? '-' }}</div>
                </div>
            </div>

            <div style="border-top: 1px solid #000; margin-bottom: 20px;"></div>

            @if (strtolower($extension) == 'pdf')
                <embed src="{{ $filePath }}#toolbar=0&navpanes=0&scrollbar=0" 
                       type="application/pdf" 
                       style="width: 100%; height: 900px; border: none;">
            @else
                <img src="{{ $filePath }}" alt="SK Dosen Pembimbing" style="width: 100%; height: auto; display: block;">
            @endif
        </div>

    @else
        <div class="border p-6 bg-white text-center py-16">
            <span class="bg-red-100 text-red-700 text-xs font-bold px-3 py-1.5 rounded border border-red-300">❌ Belum Ada SK</span>
            <p class="text-gray-500 text-sm mt-2">SK Dosen Pembimbing belum diupload oleh BAAK.</p>
        </div>
    @endif

</div>

@endsection

<style>
    .surat-container {
        border: 1px solid #000 !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
        @page {
            size: A4;
            margin: 0;
        }
    }
</style>

{{-- ✅ SCRIPT HTML2PDF — SAMA PERSIS DENGAN LOGBOOK REKAP --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadPDF() {
        // Langsung download file SK asli dari storage
        const fileUrl = '{{ asset("storage/sk_dospem/" . $fileSk) }}';
        const fileName = 'SK_Dosen_Pembimbing_{{ \Str::slug($prodi->nama_prodi) }}.pdf';
        
        const link = document.createElement('a');
        link.href = fileUrl;
        link.download = fileName;
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>