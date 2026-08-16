@extends('layouts.app') @section('content')
@php
    $kembaliUrl = $berandaUrl;
    $prev = url()->previous();
    if ($prev && str_contains($prev, 'pengajuan_magang')) {
        $kembaliUrl = $prev;
    }
@endphp
<div class="max-w-3xl mx-auto py-8 px-4">
    
    {{-- Tombol Kembali di Atas --}}
    <a href="{{ $kembaliUrl }}" class="inline-flex items-center gap-1 text-sm font-semibold text-orange-600 hover:text-orange-800 mb-4 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Kembali ke Beranda
    </a>

    <h1 class="text-2xl font-bold text-gray-800 mb-2">Edit Profil</h1>
    <p class="text-gray-500 mb-8">Perbarui informasi kontak dan Tanda Tangan Digital Anda</p>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('profil.update') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg p-6 space-y-6">
        @csrf
        @method('PUT')

        {{-- Info yang tidak bisa diedit (Read-only) & Akun --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-b pb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" value="{{ $profil->nama }}" disabled class="mt-1 block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm text-gray-500 cursor-not-allowed">
            </div>
            
            @if($role == 'mahasiswa')
                <div>
                    <label class="block text-sm font-medium text-gray-700">NPM</label>
                    <input type="text" value="{{ $profil->npm }}" disabled class="mt-1 block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm text-gray-500 cursor-not-allowed">
                </div>
            @elseif($role == 'dosen')
                <div>
                    <label class="block text-sm font-medium text-gray-700">NIDN</label>
                    <input type="text" value="{{ $profil->nidn }}" disabled class="mt-1 block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm text-gray-500 cursor-not-allowed">
                </div>
            @elseif($role == 'pegawai')
                <div>
                    <label class="block text-sm font-medium text-gray-700">NIP</label>
                    <input type="text" value="{{ $profil->nip }}" disabled class="mt-1 block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm text-gray-500 cursor-not-allowed">
                </div>
            @endif

            {{-- Username & Ganti Password --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" value="{{ auth()->user()->username }}" disabled class="mt-1 block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm text-gray-500 cursor-not-allowed">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <button type="button" onclick="openPasswordModal()" class="mt-1 w-full px-3 py-2 bg-orange-500 text-white rounded-md shadow-sm hover:bg-orange-600 transition font-semibold text-sm cursor-pointer">
                     Ubah Password
                </button>
            </div>
        </div>

        {{-- Field yang bisa diedit --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="no_hp" class="block text-sm font-medium text-gray-700">No. HP <span class="text-red-500">*</span></label>
                <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp', $profil->no_hp) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 @error('no_hp') border-red-500 @enderror">
                @error('no_hp')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email', $profil->email) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Upload TTD Digital --}}
        <div>
            <label for="ttd_digital" class="block text-sm font-medium text-gray-700 mb-2">
                Tanda Tangan Digital
                <span class="text-gray-400 font-normal">(Format: PNG/JPG — Background otomatis dibersihkan)</span>
            </label>

            {{-- FILE LAMA --}}
            @if($profil->ttd_digital)
                <div class="mb-2">
                    <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 bg-white">
                        <a href="javascript:void(0)"
                           class="file-link text-sm text-orange-600 font-medium cursor-pointer truncate"
                           onclick="openOldFilePreview()">
                            {{ $profil->ttd_digital }}
                        </a>
                    </div>
                    <p class="text-xs text-gray-400 mt-1 ml-1">Klik nama file untuk melihat TTD saat ini</p>
                </div>
            @endif

            {{-- FIELD INPUT FILE BARU --}}
            <input type="file" name="ttd_digital" id="ttd_digital" accept="image/*"
                   class="w-full border border-gray-300 rounded-md p-1 text-sm text-gray-500
                          file:mr-4 file:py-2 file:px-4 file:border-0
                          file:text-sm file:font-semibold
                          file:bg-gray-200 file:text-gray-700
                          hover:file:bg-gray-300
                          @error('ttd_digital') border-red-500 @enderror">

            {{-- FILE BARU --}}
            <div id="newFileInfoContainer" class="mt-2 hidden">
                <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 bg-white">
                    <a href="javascript:void(0)"
                       class="file-link text-sm text-orange-600 font-medium cursor-pointer truncate"
                       onclick="openNewFilePreview()">
                        <span id="newFileNameDisplay"></span>
                    </a>
                    <button type="button" onclick="removeNewFile()"
                            class="ml-auto text-gray-400 hover:text-red-500 transition-colors flex-shrink-0 text-sm font-bold"
                            title="Hapus file baru">
                        x
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-1 ml-1">Klik nama file untuk preview & proses background removal</p>
            </div>

            @error('ttd_digital')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex justify-end pt-6 border-t">
            <a href="{{ url()->previous() }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 font-semibold text-sm mr-2">Batal</a>
            <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded hover:bg-orange-600 font-semibold text-sm">Simpan Perubahan</button>
        </div>
    </form>
</div>

{{-- ==================== MODAL GANTI PASSWORD ==================== --}}
<div id="passwordModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden">
        <div class="bg-orange-500 text-white px-6 py-4 flex justify-between items-center">
            <h3 class="text-lg font-bold">Ubah Password</h3>
            <button type="button" onclick="closePasswordModal()" class="text-white hover:text-orange-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="{{ route('profil.updatePassword') }}" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password Baru <span class="text-red-500">*</span></label>
                <input type="password" name="password" id="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 @error('password_confirmation') border-red-500 @enderror">
                @error('password_confirmation')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex justify-end pt-4 border-t">
                <button type="button" onclick="closePasswordModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 mr-2 text-sm">Batal</button>
                <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 font-semibold text-sm">Ubah Password</button>
            </div>
        </form>
    </div>
</div>


<!-- ============================================================ -->
<!-- POPUP PREVIEW TTD                                            -->
<!-- ============================================================ -->
<div id="previewPopup" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="popup-backdrop absolute inset-0" onclick="closePreviewPopup()"></div>
    <div id="popupContent" class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[92vh] overflow-hidden z-10 flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex-shrink-0">
            <h3 id="popupTitle" class="text-base font-semibold text-gray-800"></h3>
            <button onclick="closePreviewPopup()"
                    class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200 transition-colors text-gray-500 hover:text-gray-800 text-lg font-bold"
                    title="Tutup">x</button>
        </div>
        <!-- Body -->
        <div class="p-5 overflow-auto flex-1 relative" id="previewBody">
            <!-- Overlay proses -->
            <div id="processingOverlay" class="absolute inset-0 bg-white/90 flex items-center justify-center z-20 hidden">
                <div class="text-center">
                    <div class="inline-block w-8 h-8 border-3 border-orange-200 border-t-orange-500 rounded-full animate-spin mb-2"></div>
                    <p class="text-sm text-gray-500 mt-1 font-medium">Menghapus background & memperbaiki tinta...</p>
                </div>
            </div>
            <!-- Gambar -->
            <div id="previewArea" class="flex items-center justify-center rounded-lg p-4 checkerboard-bg" style="min-height: 200px;">
                <img id="previewImage" src="" alt="Preview TTD"
                     class="max-w-full max-h-[70vh] object-contain rounded shadow-md" style="opacity:0">
            </div>
        </div>
    </div>
</div>


<style>
.popup-backdrop {
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}
.popup-enter {
    animation: popupIn 0.25s ease-out forwards;
}
@keyframes popupIn {
    from { opacity: 0; transform: scale(0.9) translateY(10px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
.popup-exit {
    animation: popupOut 0.2s ease-in forwards;
}
@keyframes popupOut {
    from { opacity: 1; transform: scale(1) translateY(0); }
    to   { opacity: 0; transform: scale(0.9) translateY(10px); }
}
.file-link {
    transition: all 0.2s ease;
}
.file-link:hover {
    color: #ea580c;
    text-decoration: underline;
}
.checkerboard-bg {
    background-image:
        linear-gradient(45deg, #d1d5db 25%, transparent 25%),
        linear-gradient(-45deg, #d1d5db 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #d1d5db 75%),
        linear-gradient(-45deg, transparent 75%, #d1d5db 75%);
    background-size: 16px 16px;
    background-position: 0 0, 0 8px, 8px -8px, -8px 0;
    background-color: #f3f4f6;
}
</style>


<!-- ============================================================ -->
<!-- SCRIPT 1: File baru selection & state                        -->
<!-- ============================================================ -->
<script>
var originalFileDataUrl = null;
var processedFileDataUrl = null;
var originalFileName = '';
var selectedFileInfo = null;

var fileInput = document.getElementById('ttd_digital');
var newFileInfoContainer = document.getElementById('newFileInfoContainer');
var newFileNameDisplay = document.getElementById('newFileNameDisplay');

fileInput.addEventListener('change', function(e) {
    var file = e.target.files[0];
    if (file) {
        originalFileName = file.name;
        selectedFileInfo = { name: file.name, size: file.size, type: file.type };
        processedFileDataUrl = null;

        var reader = new FileReader();
        reader.onload = function(ev) {
            originalFileDataUrl = ev.target.result;
            newFileNameDisplay.textContent = file.name;
            newFileInfoContainer.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        clearNewFileInfo();
    }
});

function removeNewFile() {
    fileInput.value = '';
    clearNewFileInfo();
}

function clearNewFileInfo() {
    originalFileDataUrl = null;
    processedFileDataUrl = null;
    originalFileName = '';
    selectedFileInfo = null;
    newFileInfoContainer.classList.add('hidden');
    newFileNameDisplay.textContent = '';
}
</script>


<!-- ============================================================ -->
<!-- SCRIPT 2: Popup buka/tutup                                   -->
<!-- ============================================================ -->
<script>
var previewPopup = document.getElementById('previewPopup');
var popupContent = document.getElementById('popupContent');
var popupTitle = document.getElementById('popupTitle');
var previewImage = document.getElementById('previewImage');
var processingOverlay = document.getElementById('processingOverlay');
var previewBody = document.getElementById('previewBody');

var oldFileUrl = '{{ $profil->ttd_digital ? asset("storage/ttd_digital/" . $profil->ttd_digital) : "" }}';

function openOldFilePreview() {
    if (!oldFileUrl) return;
    popupTitle.textContent = 'TTD Digital Saat Ini';
    processingOverlay.classList.add('hidden');
    previewImage.style.opacity = '0';

    previewPopup.classList.remove('hidden');
    previewPopup.classList.add('flex');
    popupContent.classList.remove('popup-exit');
    popupContent.classList.add('popup-enter');
    document.body.style.overflow = 'hidden';

    previewImage.src = oldFileUrl;
    previewImage.onload = function() {
        previewImage.style.opacity = '1';
    };
}

function openNewFilePreview() {
    if (!originalFileDataUrl) return;
    popupTitle.textContent = 'Preview TTD Baru (Proses Background Removal)';
    processingOverlay.classList.remove('hidden');
    previewImage.style.opacity = '0';

    var oldWarning = previewBody.querySelector('.bg-yellow-50');
    if (oldWarning) oldWarning.remove();

    previewPopup.classList.remove('hidden');
    previewPopup.classList.add('flex');
    popupContent.classList.remove('popup-exit');
    popupContent.classList.add('popup-enter');
    document.body.style.overflow = 'hidden';

    if (processedFileDataUrl) {
        previewImage.src = processedFileDataUrl;
        previewImage.style.opacity = '1';
        processingOverlay.classList.add('hidden');
        return;
    }

    processNewFile();
}

function closePreviewPopup() {
    popupContent.classList.remove('popup-enter');
    popupContent.classList.add('popup-exit');
    setTimeout(function() {
        previewPopup.classList.add('hidden');
        previewPopup.classList.remove('flex');
        previewImage.src = '';
        document.body.style.overflow = '';
    }, 200);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !previewPopup.classList.contains('hidden')) {
        closePreviewPopup();
    }
});
</script>


<!-- ============================================================ -->
<!-- SCRIPT 3: TTD Processing via OpenCV Backend                  -->
<!-- ============================================================ -->
<script>
var TTD_SERVICE_URL = 'http://localhost:5001/process';

function processNewFile() {
    fetch(TTD_SERVICE_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ image: originalFileDataUrl })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            previewImage.src = data.image;
            previewImage.style.opacity = '1';
            processingOverlay.classList.add('hidden');
            if (data.info) console.log('[TTD Service]', data.info);
            applyProcessedToForm(data.image);
        } else {
            throw new Error(data.message || 'Gagal memproses');
        }
    })
    .catch(function(err) {
        console.error('[TTD Service Error]', err);
        processingOverlay.classList.add('hidden');
        previewImage.src = originalFileDataUrl;
        previewImage.style.opacity = '1';

        var warningDiv = document.createElement('div');
        warningDiv.className = 'absolute bottom-4 left-4 right-4 bg-yellow-50 border border-yellow-300 text-yellow-800 text-xs px-4 py-3 rounded-lg z-30';
        warningDiv.innerHTML = '<strong>TTD Service tidak tersedia</strong> (' + err.message + '). Gambar akan dikirim tanpa proses. Pastikan <code>ttd_service.py</code> berjalan di port 5001.';
        previewBody.appendChild(warningDiv);
        setTimeout(function() { warningDiv.remove(); }, 6000);
    });
}

function applyProcessedToForm(dataUrl) {
    try {
        var byteStr = atob(dataUrl.split(',')[1]);
        var mime = dataUrl.split(',')[0].split(':')[1].split(';')[0];
        var ab = new ArrayBuffer(byteStr.length);
        var ia = new Uint8Array(ab);
        for (var i = 0; i < byteStr.length; i++) ia[i] = byteStr.charCodeAt(i);
        var blob = new Blob([ab], { type: mime });

        var origName = originalFileName.replace(/\.[^.]+$/, '');
        var newName = origName + '_bersih.png';
        var cleanFile = new File([blob], newName, { type: 'image/png' });

        var dt = new DataTransfer();
        dt.items.add(cleanFile);
        fileInput.files = dt.files;

        selectedFileInfo = { name: newName, size: blob.size, type: 'image/png' };
        processedFileDataUrl = dataUrl;
        newFileNameDisplay.textContent = newName;
    } catch (err) {
        console.error('Gagal apply ke form:', err);
    }
}
</script>


<!-- ============================================================ -->
<!-- SCRIPT 4: Modal Password                                     -->
<!-- ============================================================ -->
<script>
function openPasswordModal() {
    document.getElementById('passwordModal').classList.remove('hidden');
}

function closePasswordModal() {
    document.getElementById('passwordModal').classList.add('hidden');
}

document.getElementById('passwordModal').addEventListener('click', function(e) {
    if (e.target === this) closePasswordModal();
});
</script>

@endsection