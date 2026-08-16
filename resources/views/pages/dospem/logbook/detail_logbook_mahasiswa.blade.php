@extends('layouts.app')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('dospem.logbook.per_mahasiswa', $logbook->id_dosen_pembimbing) }}" 
           class="text-yellow-600 hover:text-yellow-700 font-semibold text-sm mb-2 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Logbook Mahasiswa
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Detail Logbook</h1>
        <p class="text-gray-500 text-sm">Rincian kegiatan magang mahasiswa bimbingan</p>
    </div>

    <div class="bg-white rounded-xl shadow-md border-t-4 border-yellow-500 overflow-hidden">
        <div class="p-6">

            {{-- Header: Minggu, Status & Tombol Konfirmasi --}}
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
                <div>
                    <span class="text-sm text-gray-500">Minggu ke-{{ $logbook->minggu_ke }}</span>
                </div>
                <div class="flex items-center gap-3">
                    @if($logbook->status === 'pending')
                        <button type="button" onclick="openModalKonfirmasi()" 
                            class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-5 rounded-lg transition shadow-md text-sm">
                            <i class="fas fa-pen-fancy mr-1"></i> Konfirmasi
                        </button>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            <i class="fas fa-clock mr-1"></i> Pending
                        </span>
                    @elseif($logbook->status === 'disetujui')
                        <span class="bg-green-600 text-white font-bold py-2 px-5 rounded-lg text-sm shadow-md">
                            <i class="fas fa-check-circle mr-1"></i> DISETUJUI
                        </span>
                    @elseif($logbook->status === 'ditolak')
                        <span class="bg-red-600 text-white font-bold py-2 px-5 rounded-lg text-sm shadow-md">
                            <i class="fas fa-times-circle mr-1"></i> DITOLAK
                        </span>
                    @endif
                </div>
            </div>

            {{-- Tanggal, Waktu & Minggu --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <p class="text-sm text-gray-500 font-semibold mb-1">Tanggal Kegiatan</p>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <p class="text-gray-800 font-bold">{{ \Carbon\Carbon::parse($logbook->tanggal)->locale('id')->translatedFormat('l') }}</p>
                        <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($logbook->tanggal)->locale('id')->translatedFormat('d F Y') }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-semibold mb-1">Waktu Kegiatan</p>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <p class="text-gray-800 font-bold text-lg">
                            {{ $logbook->jam_mulai ? \Carbon\Carbon::parse($logbook->jam_mulai)->format('H:i') : '-' }} 
                            &mdash; 
                            {{ $logbook->jam_selesai ? \Carbon\Carbon::parse($logbook->jam_selesai)->format('H:i') : '-' }}
                        </p>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-semibold mb-1">Minggu Ke</p>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <p class="text-gray-800 font-bold text-lg">Minggu ke-{{ $logbook->minggu_ke }}</p>
                    </div>
                </div>
            </div>

            {{-- ABSENSI MAHASISWA --}}
            @php
                $absensi = \App\Models\Absensi::where('id_mahasiswa', $logbook->id_mahasiswa)
                    ->whereDate('tanggal', $logbook->tanggal)
                    ->first();
            @endphp
            <div class="mb-6">
                <p class="text-sm text-gray-500 font-semibold mb-1">Absensi Mahasiswa</p>
                @if($absensi)
                    <div class="bg-gray-50 px-3 py-2 rounded-lg border border-gray-200 inline-flex items-center gap-4">
                        <span class="text-xs text-gray-500">Masuk: <span class="font-bold text-gray-800">{{ $absensi->jam_masuk ? \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') : '-' }}</span></span>
                        <span class="text-gray-300">|</span>
                        <span class="text-xs text-gray-500">Pulang: <span class="font-bold text-gray-800">{{ $absensi->jam_pulang ? \Carbon\Carbon::parse($absensi->jam_pulang)->format('H:i') : '-' }}</span></span>
                    </div>
                @else
                    <p class="text-xs text-gray-400">Tidak ada data absensi pada tanggal ini</p>
                @endif
            </div>

            {{-- Isi Kegiatan --}}
            <div class="mb-6">
                <p class="text-sm text-gray-500 font-semibold mb-5">Deskripsi Kegiatan</p>
                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 text-gray-800 leading-relaxed whitespace-pre-wrap">
                    {{ $logbook->kegiatan ?? '-' }}
                </div>
            </div>

            {{-- Foto Kegiatan --}}
            <div class="mb-6">
                <p class="text-sm text-gray-500 font-semibold mb-1">Dokumentasi Kegiatan</p>
                @if($logbook->foto)
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <img src="{{ asset('storage/' . $logbook->foto) }}" alt="Foto Kegiatan" class="w-full max-w-md rounded-lg shadow-md border">
                    </div>
                @else
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 text-center">
                        <p class="text-gray-400">Tidak ada dokumentasi</p>
                    </div>
                @endif
            </div>

            {{-- Catatan Dosen --}}
            @if($logbook->catatan)
            <div class="mb-6">
                <p class="text-sm text-gray-500 font-semibold mb-1">Catatan dari Dosen</p>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                    <p class="text-sm text-red-700 whitespace-pre-wrap">{{ $logbook->catatan }}</p>
                </div>
            </div>
            @endif

            {{-- LEMBAR PERSETUJUAN --}}
            <div class="mt-8 border-t-2 border-gray-200 pt-6">
                <h3 class="text-center text-sm font-bold text-gray-600 uppercase tracking-wider mb-6">Lembar Persetujuan</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    @php
                        $dosen = $logbook->dosenPembimbing ? $logbook->dosenPembimbing->dosen : null;
                        $mahasiswa = $logbook->dosenPembimbing ? $logbook->dosenPembimbing->mahasiswa : null;
                    @endphp

                    {{-- KIRI: Dosen Pembimbing --}}
                    <div class="text-center flex flex-col items-center">
                        <p class="text-sm text-gray-500 font-semibold mb-3">Dosen Pembimbing</p>
                        
                        <div class="h-32 w-full flex items-center justify-center mb-2">
                            @if($logbook->status == 'disetujui' && $dosen && $dosen->ttd_digital)
                                <div class="ttd-container">
                                    <img src="{{ asset('storage/ttd_digital/' . $dosen->ttd_digital) }}" 
                                         alt="TTD Dosen" class="ttd-bersih w-32 max-h-32 object-contain mx-auto" data-ttd="true">
                                </div>
                            @else
                                <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg h-32 w-32 flex items-center justify-center">
                                    <div>
                                        @if($logbook->status == 'pending')
                                            <i class="fas fa-clock text-yellow-400 text-2xl mb-1"></i>
                                            <p class="text-xs text-gray-400 italic">Menunggu persetujuan</p>
                                        @elseif($logbook->status == 'ditolak')
                                            <i class="fas fa-times-circle text-red-400 text-2xl mb-1"></i>
                                            <p class="text-xs text-red-400 italic">Logbook ditolak</p>
                                        @else
                                            <i class="fas fa-signature text-gray-300 text-2xl mb-1"></i>
                                            <p class="text-xs text-gray-400 italic">Belum ditandatangani</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <p class="text-sm text-gray-700 font-bold mt-1">{{ $dosen ? $dosen->nama : '-' }}</p>
                        <p class="text-xs text-gray-400">NIDN: {{ $dosen->nidn ?? '-' }}</p>
                    </div>

                    {{-- KANAN: Mahasiswa --}}
                    <div class="text-center flex flex-col items-center">
                        <p class="text-sm text-gray-500 font-semibold mb-3">Mahasiswa</p>
                        
                        <div class="h-32 w-full flex items-center justify-center mb-2">
                            @if($mahasiswa && $mahasiswa->ttd_digital)
                                <div class="ttd-container">
                                    <img src="{{ asset('storage/ttd_digital/' . $mahasiswa->ttd_digital) }}" 
                                         alt="TTD Mahasiswa" class="ttd-bersih w-32 max-h-32 object-contain mx-auto" data-ttd="true">
                                </div>
                            @else
                                <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg h-32 w-32 flex items-center justify-center">
                                    <div>
                                        <i class="fas fa-exclamation-circle text-red-300 text-2xl mb-1"></i>
                                        <p class="text-xs text-red-400 italic">TTD belum diupload</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <p class="text-sm text-gray-700 font-bold mt-1">{{ $mahasiswa ? $mahasiswa->nama : '-' }}</p>
                        <p class="text-xs text-gray-400">NPM: {{ $mahasiswa->npm ?? '-' }}</p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI --}}
@if($logbook->status === 'pending')
<div id="modalKonfirmasi" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeModalKonfirmasi()"></div>
    
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform transition-all">
            
            {{-- Header --}}
            <div class="bg-yellow-500 rounded-t-2xl px-6 py-4">
                <h3 class="text-white text-lg font-bold"><i class="fas fa-pen-fancy mr-2"></i>Konfirmasi Logbook</h3>
                <p class="text-yellow-100 text-sm">Minggu ke-{{ $logbook->minggu_ke }} — {{ \Carbon\Carbon::parse($logbook->tanggal)->locale('id')->translatedFormat('d F Y') }}</p>
            </div>

            {{-- Form --}}
            <form id="formKonfirmasi" method="POST" action="{{ route('dospem.logbook.konfirmasi', $logbook->id_logbook) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" id="statusInput" value="">

                <div class="p-6">
                    
                    {{-- Pilihan Aksi --}}
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-3">Pilih Aksi:</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center justify-center gap-2 p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-green-400 hover:bg-green-50 transition" id="labelSetujui">
                                <input type="radio" name="aksi" value="disetujui" class="h-4 w-4 text-green-600 focus:ring-green-500" onchange="onAksiChange()">
                                <span class="text-sm font-semibold text-gray-700">Disetujui</span>
                                <i class="fas fa-check-circle text-green-500 text-sm"></i>
                            </label>
                            <label class="flex items-center justify-center gap-2 p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-red-400 hover:bg-red-50 transition" id="labelTolak">
                                <input type="radio" name="aksi" value="ditolak" class="h-4 w-4 text-red-600 focus:ring-red-500" onchange="onAksiChange()">
                                <span class="text-sm font-semibold text-gray-700">Ditolak</span>
                                <i class="fas fa-times-circle text-red-500 text-sm"></i>
                            </label>
                        </div>
                    </div>

                    {{-- Catatan --}}
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Catatan / Alasan <span id="catatanWajib" class="text-red-500 hidden">(wajib jika ditolak)</span>
                        </label>
                        <textarea name="catatan" id="catatanDosen" rows="2" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"
                            placeholder="Tulis catatan atau alasan penolakan di sini..."
                            oninput="validateForm()"></textarea>
                    </div>

                    {{-- Checkbox Konfirmasi --}}
                    <div class="mb-4">
                        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-3">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" id="checkboxTtd" class="h-5 w-5 text-blue-600 focus:ring-blue-500 mt-0.5 rounded" onchange="validateForm()">
                                <div>
                                    <p class="text-sm font-bold text-gray-800">Konfirmasi Tanda Tangan Digital</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Dengan mencentang ini, saya mengonfirmasi keputusan saya sebagai Dosen Pembimbing.
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Info Mahasiswa --}}
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <p class="text-xs text-gray-500">Mahasiswa</p>
                        <p class="text-sm font-bold text-gray-800">{{ $mahasiswa ? $mahasiswa->nama : '-' }}</p>
                        <p class="text-xs text-gray-500">{{ $mahasiswa ? $mahasiswa->npm : '-' }}</p>
                    </div>

                </div>

                {{-- Footer Buttons --}}
                <div class="bg-gray-50 rounded-b-2xl px-6 py-4 flex gap-3">
                    <button type="button" onclick="closeModalKonfirmasi()" 
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg transition text-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </button>
                    <button type="button" id="btnKirim" onclick="submitKonfirmasi()" disabled
                        class="flex-1 bg-gray-300 text-gray-500 font-bold py-3 px-6 rounded-lg cursor-not-allowed text-sm transition">
                        <i class="fas fa-paper-plane mr-1"></i> Kirim
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endif

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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ttdImages = document.querySelectorAll('img[data-ttd="true"]');
    ttdImages.forEach(function(img) {
        if (img.complete) { bersihkanTTD(img); } 
        else { img.onload = function() { bersihkanTTD(img); }; }
    });
});

function openModalKonfirmasi() {
    const modal = document.getElementById('modalKonfirmasi');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    document.querySelectorAll('input[name="aksi"]').forEach(r => r.checked = false);
    document.getElementById('statusInput').value = '';
    document.getElementById('catatanDosen').value = '';
    document.getElementById('catatanWajib').classList.add('hidden');
    document.getElementById('checkboxTtd').checked = false;
    document.getElementById('labelSetujui').classList.remove('border-green-500', 'bg-green-50');
    document.getElementById('labelTolak').classList.remove('border-red-500', 'bg-red-50');
    disableBtn();
}

function closeModalKonfirmasi() {
    document.getElementById('modalKonfirmasi').classList.add('hidden');
    document.body.style.overflow = '';
}

function onAksiChange() {
    const selected = document.querySelector('input[name="aksi"]:checked');
    const labelSetujui = document.getElementById('labelSetujui');
    const labelTolak = document.getElementById('labelTolak');
    const catatanWajib = document.getElementById('catatanWajib');
    
    labelSetujui.classList.remove('border-green-500', 'bg-green-50');
    labelTolak.classList.remove('border-red-500', 'bg-red-50');
    
    if (selected) {
        if (selected.value === 'disetujui') {
            labelSetujui.classList.add('border-green-500', 'bg-green-50');
            catatanWajib.classList.add('hidden');
        } else {
            labelTolak.classList.add('border-red-500', 'bg-red-50');
            catatanWajib.classList.remove('hidden');
        }
    }
    validateForm();
}

function validateForm() {
    const selected = document.querySelector('input[name="aksi"]:checked');
    const checkboxChecked = document.getElementById('checkboxTtd').checked;
    const catatan = document.getElementById('catatanDosen').value.trim();
    
    let valid = true;
    if (!selected) valid = false;
    if (!checkboxChecked) valid = false;
    if (selected && selected.value === 'ditolak' && catatan === '') valid = false;
    
    valid ? enableBtn() : disableBtn();
}

function enableBtn() {
    const btn = document.getElementById('btnKirim');
    btn.disabled = false;
    btn.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
    btn.classList.add('bg-yellow-500', 'hover:bg-yellow-600', 'text-white', 'shadow-md');
}

function disableBtn() {
    const btn = document.getElementById('btnKirim');
    btn.disabled = true;
    btn.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
    btn.classList.remove('bg-yellow-500', 'hover:bg-yellow-600', 'text-white', 'shadow-md');
}

function submitKonfirmasi() {
    const selected = document.querySelector('input[name="aksi"]:checked');
    if (!selected) return;
    document.getElementById('statusInput').value = selected.value;
    document.getElementById('formKonfirmasi').submit();
}

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
            const r = data[i], g = data[i+1], b = data[i+2];
            if (r > 150 && g > 150 && b > 150) {
                data[i]=255; data[i+1]=255; data[i+2]=255; data[i+3]=255;
            } else {
                data[i]=0; data[i+1]=0; data[i+2]=0; data[i+3]=255;
            }
        }
        ctx.putImageData(imageData, 0, 0);
        imgElement.src = canvas.toDataURL('image/png');
        imgElement.style.filter = 'none';
        imgElement.style.mixBlendMode = 'multiply';
    };
    img.onerror = function() { console.log('Canvas gagal, pakai CSS filter'); };
}
</script>
@endsection