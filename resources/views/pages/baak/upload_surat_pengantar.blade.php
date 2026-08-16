@extends('layouts.app')

@section('content')
@php
    $isViewOnly = $isViewOnly ?? false;
    $emailPerusahaan = $pengajuan->email_perusahaan;
    $emailTersedia = !empty($emailPerusahaan);
@endphp

{{-- ==================== TOMBOL AKSI DI ATAS ==================== --}}
<div class="no-print max-w-4xl mx-auto py-6 px-6">
    <div class="flex justify-between items-center">
        <a href="{{ $backUrl ?? route('baak.pengajuan_magang.index') }}" class="text-amber-700 font-semibold text-sm hover:underline">
            ← Kembali ke Daftar Pengajuan
        </a>
        <div class="flex gap-3 items-center">
            @if ($suratPengantar)
                @php
                    $filePath = asset('storage/surat_pengantar_upload/' . $suratPengantar->file_surat);
                    $extension = pathinfo($suratPengantar->file_surat, PATHINFO_EXTENSION);
                @endphp

               <a href="{{ $filePath }}" target="_blank"
  
</a>
<a href="{{ $filePath }}" download="{{ $suratPengantar->file_surat }}"
   class="bg-blue-600 text-white text-sm px-4 py-2 hover:bg-blue-700 transition rounded-lg">
     Download
</a>
<!-- <button onclick="window.print()" class="bg-amber-600 text-white text-sm px-4 py-2 hover:bg-amber-700 transition rounded-lg">
    🖨️ Cetak / Simpan PDF
</button> -->
            @endif
        </div>
    </div>
</div>

{{-- ==================== NOTIFIKASI ==================== --}}
@if (session('success'))
    <div class="no-print max-w-4xl mx-auto px-6 mb-4">
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    </div>
@endif

@if (session('error'))
    <div class="no-print max-w-4xl mx-auto px-6 mb-4">
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="no-print max-w-4xl mx-auto px-6 mb-4">
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            @foreach ($errors->all() as $error)
                <p class="text-sm">{{ $error }}</p>
            @endforeach
        </div>
    </div>
@endif

{{-- ==================== AREA KONTEN ==================== --}}
<div class="max-w-4xl mx-auto px-6 mb-10">

    @if ($suratPengantar)

        {{-- ========================================================== --}}
        {{--  KIRIM EMAIL (DI ATAS SURAT, HANYA UNTUK BAAK)          --}}
        {{-- ========================================================== --}}
        @if(!$isViewOnly)
        <div class="no-print mb-6 border border-amber-200 rounded-xl overflow-hidden shadow-sm bg-gradient-to-r from-amber-50 to-yellow-50">
            <div class="px-5 py-4 border-b border-amber-100">
                <h4 class="text-sm font-bold text-amber-900 flex items-center gap-2">
                     Kirim Surat via Email
                </h4>
            </div>
            <div class="px-5 py-4 bg-white/60 backdrop-blur-sm">
                @if($emailTersedia)
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-3">
                            <!-- <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-amber-600 text-lg"></span>
                            </div> -->
                            <div>
                                <p class="text-sm font-semibold text-amber-900">Email Perusahaan</p>
                                <p class="text-xs text-amber-700">{{ $emailPerusahaan }}</p>
                            </div>
                        </div>
                        <form method="POST"
                              action="{{ route('baak.kirim_email_surat_pengantar', $pengajuan->id_pengajuan) }}">
                            @csrf
                            <button type="submit"
                                class="bg-amber-600 text-white px-5 py-2.5 font-semibold hover:bg-amber-700 transition text-sm rounded-lg flex items-center gap-2 whitespace-nowrap shadow-sm">
                                <span></span> Kirim Surat Sekarang
                            </button>
                        </form>
                    </div>
                    <p class="text-xs text-amber-600/80 mt-3">
                        File surat yang sedang ditampilkan akan dikirim sebagai lampiran ke email perusahaan.
                    </p>
                @else
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-red-50 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-red-400 text-lg"></span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-red-800">Email Perusahaan Belum Diisi</p>
                            <p class="text-xs text-red-500">Surat tidak bisa dikirim via email.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif

        {{-- TAMPILAN PREVIEW SURAT --}}
        <div class="surat-container px-16 py-12" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.6; color: #000; text-align: justify; border: 1px solid #000; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">

            <h3 style="text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 15px;">
                SURAT PENGANTAR MAGANG
            </h3>

           <div style="display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 11pt;">
    {{-- KOLOM KIRI: Info Perusahaan --}}
    <div style="flex: 1;">
        <div style="font-weight: bold;">Nama Perusahaan</div>
        <div>{{ $pengajuan->perusahaan->nama_perusahaan ?? '-' }}</div>
        <div class="text-xs" style="color: #555; margin-top:2px;">{{ $pengajuan->perusahaan->alamat ?? '-' }}</div>
    </div>
    
    {{-- KOLOM KANAN: Tanggal Surat & Periode Magang --}}
    <div style="text-align: right; min-width: 180px;">
        <div style="margin-bottom: 8px;">
            <div style="font-weight: bold;">Tanggal Surat</div>
            <div>{{ \Carbon\Carbon::parse($pengajuan->tgl_pengajuan)->locale('id')->translatedFormat('d F Y') }}</div>
        </div>
        <div>
            <div style="font-weight: bold;">Periode Magang</div>
            @if($pengajuan->tgl_mulai && $pengajuan->tgl_selesai)
                <div>{{ \Carbon\Carbon::parse($pengajuan->tgl_mulai)->locale('id')->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($pengajuan->tgl_selesai)->locale('id')->translatedFormat('d M Y') }}</div>
            @else
                <div style="color: #888; font-style: italic;">Belum ditentukan</div>
            @endif
        </div>
    </div>
</div>

            <div style="border-top: 1px solid #000; margin-bottom: 20px;"></div>

            @if (strtolower($extension) == 'pdf')
                <embed src="{{ $filePath }}#toolbar=0&navpanes=0&scrollbar=0"
                       type="application/pdf"
                       style="width: 100%; height: 900px; border: none;">
            @else
                <img src="{{ $filePath }}" alt="Surat Pengantar" style="width: 100%; height: auto; display: block;">
            @endif
        </div>

        {{-- ========================================================== --}}
        {{--  UBAH FILE SURAT (DI BAWAH PREVIEW, HANYA UNTUK BAAK)   --}}
        {{-- ========================================================== --}}
        @if(!$isViewOnly)
        <div class="no-print mt-6 border border-yellow-200 rounded-xl overflow-hidden shadow-sm">
            <div class="bg-gradient-to-r from-yellow-50 to-amber-50 px-5 py-3 border-b border-yellow-100">
                <h4 class="text-sm font-bold text-yellow-900 flex items-center gap-2">
                     Ubah File Surat Pengantar
                </h4>
                <p class="text-xs text-amber-700 mt-1"> Upload file baru akan mengganti surat pengantar yang lama.</p>
            </div>
            <div class="px-5 py-4 bg-white">
                <form method="POST"
                      action="{{ route('baak.upload_surat_pengantar.store', $pengajuan->id_pengajuan) }}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <input type="file" name="file_surat" accept=".pdf"
                            class="w-full border border-amber-200 text-sm text-amber-800 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200 rounded-lg"
                            required>
                        <p class="text-xs text-amber-500 mt-1">Format file: PDF (Max 5MB)</p>
                        @error('file_surat')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="bg-yellow-800 text-white px-5 py-2 font-semibold hover:bg-yellow-900 transition text-sm rounded-lg shadow-sm">
                        Ubah File Surat
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="no-print mt-6 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-sm">
             Surat Pengantar sudah diupload oleh BAAK. Anda dapat melihat atau mengunduh surat menggunakan tombol di atas.
        </div>
        @endif

    @else

        {{-- ========================================================== --}}
        {{-- BELUM UPLOAD: TAMPILKAN FORM UPLOAD + CHECKBOX EMAIL       --}}
        {{-- ========================================================== --}}
        <div class="border border-amber-200 p-6 bg-white rounded-xl shadow-sm">
            <div class="mb-5 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1.5 rounded-md border border-yellow-200">
                     Belum Upload Surat Pengantar
                </span>
            </div>

            @if(!$isViewOnly)
            <form method="POST"
                  action="{{ route('baak.upload_surat_pengantar.store', $pengajuan->id_pengajuan) }}"
                  enctype="multipart/form-data">
                @csrf

                {{-- INFO EMAIL --}}
                <div class="mb-4 p-4 rounded-lg border {{ $emailTersedia ? 'bg-amber-50 border-amber-200' : 'bg-red-50 border-red-200' }}">
                    <div class="flex items-start gap-3">
                        @if($emailTersedia)
                            <span class="text-amber-500"></span>
                            <div>
                                <p class="text-sm font-semibold text-amber-900">Email Perusahaan Tersedia</p>
                                <p class="text-xs text-amber-700">{{ $emailPerusahaan }}</p>
                            </div>
                        @else
                            <span class="text-red-400"></span>
                            <div>
                                <p class="text-sm font-semibold text-red-800">Email Perusahaan Belum Diisi</p>
                                <p class="text-xs text-red-500">Surat tidak bisa dikirim via email otomatis.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block font-medium mb-2 text-amber-900">Upload File Surat Pengantar</label>
                    <input type="file" name="file_surat" accept=".pdf"
                        class="w-full border border-amber-200 text-sm text-amber-800 file:mr-4 file:py-2.5 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-amber-600 file:text-white hover:file:bg-amber-700 rounded-lg"
                        required>
                    <p class="text-xs text-amber-500 mt-1">Format file: PDF (Max 5MB)</p>
                    @error('file_surat')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- CHECKBOX EMAIL (HANYA SAAT UPLOAD PERTAMA) --}}
                @if($emailTersedia)
                <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="kirim_email" value="1" checked
                            class="w-4 h-4 text-amber-600 border-amber-300 rounded focus:ring-amber-500">
                        <div>
                            <span class="text-sm font-semibold text-yellow-900"> Kirim surat ke email perusahaan setelah upload</span>
                            <p class="text-xs text-amber-700 mt-0.5">
                                Surat akan dikirim ke <strong class="text-amber-900">{{ $emailPerusahaan }}</strong>
                            </p>
                        </div>
                    </label>
                </div>
                @endif

                <button type="submit" class="bg-amber-600 text-white px-6 py-2.5 font-semibold hover:bg-amber-700 transition rounded-lg shadow-sm">
                    Upload Surat Pengantar
                </button>
            </form>
            @else
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg text-sm">
                 Surat Pengantar belum diupload oleh BAAK. Silakan menunggu.
            </div>
            @endif
        </div>

    @endif

</div>

@endsection

<style>
    .surat-container { border: 1px solid #000 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    @media print {
        nav, footer, .no-print, .sidebar, .navbar, header { display: none !important; }
        main { padding: 0 !important; margin: 0 !important; }
        .surat-container { border: 1px solid #000 !important; box-shadow: none !important; padding: 20mm 25mm !important; max-width: 100% !important; margin: 0 !important; }
        @page { size: A4; margin: 0; }
    }
</style>