@extends('layouts.app')

@section('content')

{{-- TOMBOL AKSI --}}
<div class="no-print max-w-4xl mx-auto py-3 px-3 md:py-6 md:px-6">
    <div class="flex justify-between items-center gap-2 md:gap-0">
        <div class="min-w-0">
            <h1 class="text-sm md:text-2xl font-bold text-gray-800 truncate">{{ $berkas->jenis_berkas }}</h1>
            <p class="text-[10px] md:text-sm text-gray-500 mt-1 truncate">{{ $berkas->nama_berkas }}</p>
        </div>
        
        @php
            $extension = strtolower(pathinfo($berkas->file, PATHINFO_EXTENSION));
            $filePath = asset('storage/' . $berkas->file); 
            
            // Nama file untuk download PDF (ekstensi asli)
            $namaFilePDF = \Illuminate\Support\Str::slug($berkas->jenis_berkas) . '.' . $extension;
            
            // Nama file untuk download Word (paksa ekstensi .doc)
            $namaFileWord = \Illuminate\Support\Str::slug($berkas->jenis_berkas) . '.doc';
        @endphp

        <div class="flex gap-1.5 md:gap-3 flex-shrink-0">
            
            {{-- TOMBOL DOWNLOAD PDF --}}
            <a href="{{ $filePath }}" download="{{ $namaFilePDF }}" 
               class="bg-blue-600 text-white text-[11px] md:text-sm px-3 py-1.5 md:px-5 md:py-2.5 rounded-lg hover:bg-blue-700 transition flex items-center gap-1 md:gap-2">
                 Download File
            </a>
            
            <!-- {{-- TOMBOL DOWNLOAD WORD: Memaksa ekstensi .doc saat download --}}
            <a href="{{ $filePath }}" download="{{ $namaFileWord }}" 
               class="bg-green-600 text-white text-[11px] md:text-sm px-3 py-1.5 md:px-5 md:py-2.5 rounded-lg hover:bg-green-700 transition flex items-center gap-1 md:gap-2">
                 Download Word
            </a> -->

        </div>
    </div>
    
    {{-- Deskripsi dari DB (jika ada) --}}
    @if($berkas->deskripsi)
        <div class="mt-3 md:mt-4 bg-yellow-50 border border-yellow-200 text-yellow-800 px-2 py-2 md:px-4 md:py-3 rounded-lg text-[10px] md:text-sm">
             {{ $berkas->deskripsi }}
        </div>
    @endif
</div>

{{-- AREA TAMPILAN 1 BERKAS --}}
<div id="berkas-content" class="surat-container max-w-4xl mx-auto px-3 py-4 md:px-6 md:py-8 mb-10 bg-white shadow-lg border border-gray-200">
    
    @if ($extension == 'pdf')
        <embed src="{{ $filePath }}#toolbar=0&navpanes=0&scrollbar=0" 
               type="application/pdf" 
               class="w-full h-[500px] md:h-[900px] border-none">
    @elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
        <img src="{{ $filePath }}" alt="{{ $berkas->nama_berkas }}" 
             class="w-full h-auto block mx-auto">
    @else
        <div class="text-center py-16 md:py-20 text-gray-500">
            <p class="text-3xl md:text-5xl mb-4">📄</p>
            <p class="text-base md:text-lg font-semibold">Format file tidak bisa ditampilkan langsung.</p>
            <a href="{{ $filePath }}" download class="text-blue-600 underline mt-2 inline-block text-xs md:text-base">
                Download File
            </a>
        </div>
    @endif

</div>

@endsection

{{-- CSS UNTUK CETAK (Hanya sebagai fallback) --}}
<style>
    .surat-container {
        border: 1px solid #e5e7eb !important;
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
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            max-width: 100% !important;
            margin: 0 !important;
        }
        
        @page {
            size: A4;
            margin: 15mm;
        }
    }
</style>