@extends('layouts.app')

@section('content')

{{-- TOMBOL ATAS --}}
<div class="no-print max-w-4xl mx-auto py-6 px-6">
    <div class="flex justify-between items-center flex-wrap gap-3">
        <a href="{{ $backUrl ?? route('kajur.pengajuan_magang.index') }}" class="text-yellow-600 font-semibold text-sm hover:underline">
            ← Kembali ke Daftar Pengajuan
        </a>
        <div class="flex gap-3 items-center flex-wrap">
            @if ($suratBalasan)
                @php $filePath = asset('storage/surat_balasan/' . $suratBalasan); @endphp
                <!-- <a href="{{ $filePath }}" target="_blank" class="border border-gray-300 text-sm px-4 py-2 hover:bg-gray-100 transition">🔗 Buka di Tab Baru</a> -->
                <a href="{{ $filePath }}" download="{{ $suratBalasan }}" class="bg-blue-600 text-white text-sm px-4 py-2 hover:bg-blue-700 transition"> Download</a>
                <!-- <button onclick="window.print()" class="bg-amber-600 text-white text-sm px-4 py-2 hover:bg-amber-700 transition">🖨️ Cetak</button> -->
            @endif
        </div>
    </div>
</div>

{{-- AREA KONTEN --}}
<div class="max-w-4xl mx-auto px-6 mb-10">
    @if ($suratBalasan)
        @php $extension = pathinfo($suratBalasan, PATHINFO_EXTENSION); @endphp

        {{-- Filter hanya mahasiswa yang status_surat_balasan-nya 'diterima' --}}
        @php
            $mhsDiterima = $pengajuan->mahasiswa->filter(function ($pm) {
                return $pm->status_surat_balasan === 'diterima';
            });
        @endphp

        {{-- TAMPILAN SURAT --}}
        <div class="surat-container px-16 py-12" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.6; color: #000; text-align: justify; border: 1px solid #000; background: #fff;">
            <h3 style="text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 15px;">SURAT BALASAN</h3>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 11pt;">
                <div>
                    <div style="font-weight: bold;">Nama Perusahaan</div>
                    <div>{{ $pengajuan->perusahaan->nama_perusahaan ?? '-' }}</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-weight: bold;">Daftar Mahasiswa yang Diterima</div>
                    <div>
                        @foreach ($mhsDiterima as $pm) 
                            {{ $pm->mahasiswa->nama ?? '-' }} ({{ $pm->mahasiswa->npm ?? '-' }}) 
                            @if(!$loop->last)<br>@endif 
                        @endforeach
                        
                        @if($mhsDiterima->isEmpty())
                            <span style="color: #888; font-style: italic;">Tidak Ada Daftar Mahasiswa Yang Diterima</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div style="border-top: 1px solid #000; margin-bottom: 20px;"></div>
            
            @if (strtolower($extension) == 'pdf')
                <embed src="{{ $filePath }}#toolbar=0&navpanes=0&scrollbar=0" type="application/pdf" style="width: 100%; height: 900px; border: none;">
            @else
                <img src="{{ $filePath }}" alt="Surat Balasan" style="width: 100%; height: auto; display: block;">
            @endif
        </div>
    @else
        <div class="border p-6 bg-white text-center py-16">
            <span class="bg-red-100 text-red-700 text-xs font-bold px-3 py-1.5 rounded border border-red-300">❌ Belum Ada Surat Balasan</span>
            <p class="text-gray-500 text-sm mt-2">Mahasiswa belum mengupload surat balasan dari perusahaan.</p>
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