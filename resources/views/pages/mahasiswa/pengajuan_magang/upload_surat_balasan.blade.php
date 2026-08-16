@extends('layouts.app')

@section('content')

{{-- ==================== TOMBOL AKSI DI ATAS (KAYA KAPRODI) ==================== --}}
<div class="no-print max-w-4xl mx-auto py-6 px-6">
    <div class="flex justify-between items-center">
        <a href="{{ route('mahasiswa.pengajuan_magang.index') }}" class="text-yellow-600 font-semibold text-sm hover:underline">
            ← Kembali ke Daftar Pengajuan
        </a>
        <div class="flex gap-3 items-center">
            @if ($suratBalasan)
                @php
                    $filePath = asset('storage/surat_balasan/' . $suratBalasan);
                @endphp

              <a href="{{ $filePath }}" target="_blank" 
 
</a>
<a href="{{ $filePath }}" download="{{ $suratBalasan }}"
   class="bg-blue-600 text-white text-sm px-4 py-2 hover:bg-blue-700 transition">
     Download
</a>
<!-- <button onclick="window.print()" class="bg-blue-600 text-white text-sm px-4 py-2 hover:bg-blue-700 transition">
    🖨️ Cetak / Simpan PDF
</button> -->
            @endif
        </div>
    </div>
</div>

{{-- ==================== NOTIFIKASI ==================== --}}
@if (session('success'))
    <div class="no-print max-w-4xl mx-auto px-6 mb-4">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="no-print max-w-4xl mx-auto px-6 mb-4">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            @foreach ($errors->all() as $error)
                <p class="text-sm">{{ $error }}</p>
            @endforeach
        </div>
    </div>
@endif

{{-- ==================== AREA KONTEN ==================== --}}
<div class="max-w-4xl mx-auto px-6 mb-10">

    @if ($suratBalasan)

        @php
            $extension = pathinfo($suratBalasan, PATHINFO_EXTENSION);
        @endphp

        {{-- BADGE STATUS MAHASISWA --}}
        <div class="mb-4 flex items-center gap-2 no-print">
            @if($statusSuratBalasan == 'menunggu_verifikasi')
                <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-700 text-xs font-bold px-3 py-1.5 rounded border border-yellow-300">
                     Menunggu Verifikasi Kaprodi
                </span>
            @elseif($statusSuratBalasan == 'diterima')
                <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-bold px-3 py-1.5 rounded border border-green-300">
                     Surat Balasan Sudah Diverifikasi (Diterima)
                </span>
            @elseif($statusSuratBalasan == 'ditolak')
                <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-xs font-bold px-3 py-1.5 rounded border border-red-300">
                     Surat Balasan Ditolak
                </span>
            @else
                <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 text-xs font-bold px-3 py-1.5 rounded border border-gray-300">
                     Status Tidak Diketahui
                </span>
            @endif
        </div>

        {{-- TAMPILAN SURAT --}}
        <div class="surat-container px-16 py-12" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.6; color: #000; text-align: justify; border: 1px solid #000; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            
            {{-- JUDUL SURAT --}}
            <h3 style="text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 15px;">
                SURAT BALASAN
            </h3>

            {{-- BARIS NAMA PERUSAHAAN & MAHASISWA SEJAJAR --}}
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 11pt;">
                <div>
                    <div style="font-weight: bold;">Nama Perusahaan</div>
                    <div>{{ $pengajuan->perusahaan->nama_perusahaan ?? '-' }}</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-weight: bold;">Nama Mahasiswa</div>
                    <div>
                        {{ $userLogin->nama ?? '-' }}
                        @if($userLogin->npm ?? null)
                            ({{ $userLogin->npm }})
                        @endif
                    </div>
                </div>
            </div>

            {{-- GARIS PEMISAH --}}
            <div style="border-top: 1px solid #000; margin-bottom: 20px;"></div>

            @if (strtolower($extension) == 'pdf')
                <embed src="{{ $filePath }}#toolbar=0&navpanes=0&scrollbar=0" 
                       type="application/pdf" 
                       style="width: 100%; height: 900px; border: none;">
            @else
                <img src="{{ $filePath }}" alt="Surat Balasan" style="width: 100%; height: auto; display: block;">
            @endif
        </div>

    @else

        {{-- ==================== BELUM UPLOAD: TAMPILKAN FORM ==================== --}}
        <div class="border p-6 bg-white">
            <div class="mb-5 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-700 text-xs font-bold px-3 py-1.5 rounded border border-yellow-300">
                     Belum Upload Surat Balasan
                </span>
            </div>

            <form method="POST"
                  action="{{ route('mahasiswa.pengajuan_magang.upload_surat_balasan.store', $pengajuan->id_pengajuan) }}"
                  enctype="multipart/form-data">

                @csrf

                <div class="mb-4">
                    <label class="block font-medium mb-2">
                        Upload File Surat Balasan
                    </label>

                    <input type="file"
                        name="surat_balasan"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full border border-gray-300 text-sm text-gray-500
                                file:mr-4
                                file:py-2
                                file:px-4
                                file:border-0
                                file:text-sm
                                file:font-semibold
                                file:bg-gray-200
                                file:text-gray-700
                                hover:file:bg-gray-300"
                        required>

                    <p class="text-xs text-gray-400 mt-1">
                        Format file: PDF, JPG, JPEG, PNG (Max 2MB)
                    </p>

                    @error('surat_balasan')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex gap-3 items-center">
                    <button type="submit"
                            class="bg-yellow-600 text-white px-5 py-2 font-semibold hover:bg-yellow-700 transition">
                        Upload Surat Balasan
                    </button>
                  
                </div>

            </form>
        </div>

    @endif

</div>

@endsection

{{-- CSS TAMBAHAN UNTUK PRINT --}}
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