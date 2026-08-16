@extends('layouts.app') 

@section('content')
<div class="p-3 md:p-6 max-w-3xl mx-auto">
    <div class="mb-4 md:mb-6">
        <a href="{{ route('mahasiswa.logbook.minggu', $logbook->minggu_ke) }}" class="text-yellow-600 hover:text-yellow-700 font-semibold text-xs md:text-sm mb-1 md:mb-2 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Minggu ke-{{ $logbook->minggu_ke }}</a>
        <h1 class="text-base md:text-2xl font-bold text-gray-800">Detail Logbook</h1>
        <p class="text-gray-500 text-xs md:text-sm">Rincian kegiatan magang Anda</p>
    </div>

    <div class="bg-white rounded-xl shadow-md border-t-4 border-yellow-500 overflow-hidden">
        <div class="p-4 md:p-6">
            <!-- Tanggal, Waktu & Minggu -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-6 mb-4 md:mb-6">
                <div>
                    <p class="text-xs md:text-sm text-gray-500 font-semibold mb-1">Tanggal Kegiatan</p>
                    <div class="bg-gray-50 p-2 md:p-3 rounded-lg border border-gray-200">
                        <p class="text-gray-800 font-bold text-xs md:text-sm">{{ $logbook->tanggal->format('l') }}</p>
                        <p class="text-[10px] md:text-sm text-gray-600">{{ $logbook->tanggal->format('d F Y') }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs md:text-sm text-gray-500 font-semibold mb-1">Waktu Kegiatan</p>
                    <div class="bg-gray-50 p-2 md:p-3 rounded-lg border border-gray-200">
                        <p class="text-gray-800 font-bold text-xs md:text-lg">
                            {{ $logbook->jam_mulai ? \Carbon\Carbon::parse($logbook->jam_mulai)->format('H:i') : '-' }} 
                            &mdash; 
                            {{ $logbook->jam_selesai ? \Carbon\Carbon::parse($logbook->jam_selesai)->format('H:i') : '-' }}
                        </p>
                    </div>
                </div>
                <div>
                    <p class="text-xs md:text-sm text-gray-500 font-semibold mb-1">Minggu Ke</p>
                    <div class="bg-gray-50 p-2 md:p-3 rounded-lg border border-gray-200">
                        <p class="text-gray-800 font-bold text-xs md:text-lg">Minggu ke-{{ $logbook->minggu_ke }}</p>
                    </div>
                </div>
            </div>

            <!-- Isi Kegiatan -->
            <div class="mb-4 md:mb-6">
                <p class="text-xs md:text-sm text-gray-500 font-semibold mb-1">Deskripsi Kegiatan</p>
                <div class="bg-yellow-50 p-3 md:p-4 rounded-lg border border-yellow-200 text-gray-800 leading-relaxed whitespace-pre-wrap text-xs md:text-sm">
                    {{ $logbook->kegiatan }}
                </div>
            </div>

            <!-- Foto Kegiatan -->
            @if($logbook->foto)
            <div class="mb-4 md:mb-6">
                <p class="text-xs md:text-sm text-gray-500 font-semibold mb-1">Dokumentasi Kegiatan</p>
                <div class="bg-gray-50 p-2 md:p-3 rounded-lg border border-gray-200">
                    <img src="{{ asset('storage/' . $logbook->foto) }}" alt="Foto Kegiatan" class="w-full max-w-xs md:max-w-md rounded-lg shadow-md border">
                </div>
            </div>
            @endif

            <!-- Status & Dosen -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-6 mb-4 md:mb-6">
                <div>
                    <p class="text-xs md:text-sm text-gray-500 font-semibold mb-1">Status Persetujuan</p>
                    <div class="p-2 md:p-3 rounded-lg border border-gray-200 bg-gray-50">
                        @if($logbook->status == 'disetujui')
                            <span class="px-2 py-0.5 md:px-3 md:py-1 inline-flex text-[10px] md:text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i> Disetujui</span>
                        @elseif($logbook->status == 'ditolak')
                            <span class="px-2 py-0.5 md:px-3 md:py-1 inline-flex text-[10px] md:text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800"><i class="fas fa-times-circle mr-1"></i> Ditolak</span>
                        @else
                            <span class="px-2 py-0.5 md:px-3 md:py-1 inline-flex text-[10px] md:text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"><i class="fas fa-clock mr-1"></i> Pending</span>
                        @endif
                    </div>
                </div>
                <div>
                    <p class="text-xs md:text-sm text-gray-500 font-semibold mb-1">Dosen Pembimbing</p>
                    <div class="p-2 md:p-3 rounded-lg border border-gray-200 bg-gray-50">
                        <p class="text-gray-800 font-bold text-xs md:text-sm">
                            {{ $logbook->dosenPembimbing && $logbook->dosenPembimbing->dosen ? $logbook->dosenPembimbing->dosen->nama : '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Catatan Dosen (Jika ada) -->
            @if($logbook->catatan)
            <div class="mb-4 md:mb-6">
                <p class="text-xs md:text-sm text-gray-500 font-semibold mb-1">Catatan dari Dosen</p>
                <div class="bg-red-50 border-l-4 border-red-400 p-3 md:p-4 rounded-r-lg">
                    <p class="text-xs md:text-sm text-red-700 whitespace-pre-wrap">{{ $logbook->catatan }}</p>
                </div>
            </div>
            @endif

            <!-- ✅ TANDA TANGAN: KIRI DOSEN PEMBIMBING | KANAN MAHASISWA -->
            <div class="mt-6 md:mt-8 border-t-2 border-gray-200 pt-4 md:pt-6">
                <h3 class="text-center text-[10px] md:text-sm font-bold text-gray-600 uppercase tracking-wider mb-4 md:mb-6">Lembar Persetujuan</h3>
                
                <div class="grid grid-cols-2 gap-4 md:gap-8">
                    
                    @php
                        $dosen = $logbook->dosenPembimbing ? $logbook->dosenPembimbing->dosen : null;
                    @endphp

                    <!-- KIRI: Tanda Tangan Dosen Pembimbing -->
                    <div class="text-center flex flex-col items-center">
                        <p class="text-[10px] md:text-sm text-gray-500 font-semibold mb-2 md:mb-3">Dosen Pembimbing</p>
                        
                        <div class="h-24 md:h-32 w-full flex items-center justify-center mb-1 md:mb-2">
                            @if($logbook->status == 'disetujui' && $dosen && $dosen->ttd_digital)
                                <div class="ttd-container">
                                    <img src="{{ asset('storage/ttd_digital/' . $dosen->ttd_digital) }}" 
                                         alt="TTD Dosen" 
                                         class="ttd-bersih w-20 md:w-32 max-h-24 md:max-h-32 object-contain mx-auto"
                                         data-ttd="true">
                                </div>
                            @else
                                <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg h-20 w-20 md:h-32 md:w-32 flex items-center justify-center">
                                    <div>
                                        @if($logbook->status == 'pending')
                                            <i class="fas fa-clock text-yellow-400 text-lg md:text-2xl mb-0.5 md:mb-1"></i>
                                            <p class="text-[8px] md:text-xs text-gray-400 italic">Menunggu persetujuan</p>
                                        @elseif($logbook->status == 'ditolak')
                                            <i class="fas fa-times-circle text-red-400 text-lg md:text-2xl mb-0.5 md:mb-1"></i>
                                            <p class="text-[8px] md:text-xs text-red-400 italic">Logbook ditolak</p>
                                        @else
                                            <i class="fas fa-signature text-gray-300 text-lg md:text-2xl mb-0.5 md:mb-1"></i>
                                            <p class="text-[8px] md:text-xs text-gray-400 italic">Belum ditandatangani</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <p class="text-[10px] md:text-sm text-gray-700 font-bold mt-0.5 md:mt-1 truncate w-full">{{ $dosen ? $dosen->nama : '-' }}</p>
                        <p class="text-[8px] md:text-xs text-gray-400">NIDN: {{ $dosen->nidn ?? '-' }}</p>
                    </div>

                    <!-- KANAN: Tanda Tangan Mahasiswa -->
                    <div class="text-center flex flex-col items-center">
                        <p class="text-[10px] md:text-sm text-gray-500 font-semibold mb-2 md:mb-3">Mahasiswa</p>
                        
                        <div class="h-24 md:h-32 w-full flex items-center justify-center mb-1 md:mb-2">
                            @if($mahasiswa->ttd_digital)
                                <div class="ttd-container">
                                    <img src="{{ asset('storage/ttd_digital/' . $mahasiswa->ttd_digital) }}" 
                                         alt="TTD Mahasiswa" 
                                         class="ttd-bersih w-20 md:w-32 max-h-24 md:max-h-32 object-contain mx-auto"
                                         data-ttd="true">
                                </div>
                            @else
                                <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg h-20 w-20 md:h-32 md:w-32 flex items-center justify-center">
                                    <div>
                                        <i class="fas fa-exclamation-circle text-red-300 text-lg md:text-2xl mb-0.5 md:mb-1"></i>
                                        <p class="text-[8px] md:text-xs text-red-400 italic">TTD belum diupload</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <p class="text-[10px] md:text-sm text-gray-700 font-bold mt-0.5 md:mt-1 truncate w-full">{{ $mahasiswa->nama }}</p>
                        <p class="text-[8px] md:text-xs text-gray-400">NPM: {{ $mahasiswa->npm }}</p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

{{-- ✅ CSS LANGSUNG DI SINI, BUKAN @push --}}
<style>
    .ttd-bersih {
        filter: grayscale(100%) contrast(500%) brightness(150%);
        mix-blend-mode: multiply;
    }
    .ttd-container {
        background: transparent;
        padding: 0;
        border: none;
        border-radius: 0;
        display: inline-block;
        box-shadow: none;
    }
    @media print {
        .ttd-bersih {
            filter: grayscale(100%) contrast(500%) brightness(150%) !important;
            mix-blend-mode: multiply !important;
        }
    }
</style>

{{-- ✅ JS LANGSUNG DI SINI, BUKAN @push --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ttdImages = document.querySelectorAll('img[data-ttd="true"]');
    
    ttdImages.forEach(function(img) {
        if (img.complete) {
            bersihkanTTD(img);
        } else {
            img.onload = function() {
                bersihkanTTD(img);
            };
        }
    });
});

function bersihkanTTD(imgElement) {
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
        imgElement.src = canvas.toDataURL('image/png');
        imgElement.style.filter = 'none';
        imgElement.style.mixBlendMode = 'multiply';
    };
    
    img.onerror = function() {
        console.log('Canvas gagal, pakai CSS filter sebagai fallback');
    };
}
</script>
@endsection