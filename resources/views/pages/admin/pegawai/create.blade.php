<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMMAGI - Tambah Pegawai</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-10">

    <div class="w-full max-w-4xl">

        <div class="bg-white shadow-lg p-10 rounded-lg border border-gray-200">
            <h2 class="text-center text-3xl font-bold text-gray-800 mb-8">.:: Tambah Pegawai ::.</h2>
            
            <form action="{{ route('admin.pegawai.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Nama --}}
                    <div class="md:col-span-2">
                        <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" id="nama" value="{{ old('nama') }}" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('nama') border-red-500 @enderror"
                            placeholder="Masukkan nama lengkap" oninput="this.value = this.value.replace(/\b\w/g, function(l){ return l.toUpperCase() })"> 
                        @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- NIP --}}
                    <div>
                        <label for="nip" class="block text-sm font-medium text-gray-700 mb-2">NIP <span class="text-red-500">*</span></label>
                        <input type="text" name="nip" id="nip" value="{{ old('nip') }}" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('nip') border-red-500 @enderror"
                            placeholder="Contoh: 198501012010011001"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        @error('nip')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Tanggal Lahir --}}
                    <div>
                        <label for="tgl_lahir" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                        <input type="date" name="tgl_lahir" id="tgl_lahir" value="{{ old('tgl_lahir') }}" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('tgl_lahir') border-red-500 @enderror">
                        @error('tgl_lahir')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div>
                        <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <select name="jenis_kelamin" id="jenis_kelamin" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('jenis_kelamin') border-red-500 @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- No HP --}}
                    <div>
                        <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-2">No. HP <span class="text-red-500">*</span></label>
                        <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp') }}" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('no_hp') border-red-500 @enderror"
                            placeholder="Contoh: 08123456789"
                            inputmode="numeric"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        @error('no_hp')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('email') border-red-500 @enderror"
                            placeholder="Contoh: pegawai@pnc.ac.id">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Upload TTD Digital --}}
                    <div class="md:col-span-2">
                        <label for="ttd_digital" class="block text-sm font-medium text-gray-700 mb-2">
                            TTD Digital
                            <span class="text-gray-400 font-normal">(Format: PNG/JPG — Background otomatis dibersihkan)</span>
                        </label>
                        <input type="file" name="ttd_digital" id="ttd_digital" accept="image/*"
                            class="w-full border border-gray-300 rounded-lg p-2 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300">

                        <div id="fileInfoContainer" class="mt-2 hidden">
                            <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                                <a href="javascript:void(0)" id="fileLink"
                                   class="file-link text-sm text-orange-600 font-medium cursor-pointer truncate"
                                   onclick="openPreviewPopup()">
                                    <span id="fileNameDisplay"></span>
                                </a>
                                <button type="button" onclick="removeFile()"
                                        class="ml-auto text-gray-400 hover:text-red-500 transition-colors flex-shrink-0 text-sm font-bold"
                                        title="Hapus file">
                                    x
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-1 ml-1">Klik nama file untuk preview</p>
                        </div>

                        @error('ttd_digital')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Info Akun --}}
                <div class="mt-8 p-4 bg-[#fffbeb] border border-[#ece75f]/40 rounded-lg">
                    <p class="text-sm text-[#b45309]">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Info Akun Login:</strong> Akun user akan dibuat otomatis dengan username = <strong>NIP</strong> dan password = <strong>Tanggal Lahir</strong> (format: YYYYMMDD)
                    </p>
                </div>

                {{-- Tombol --}}
                <div class="flex justify-between mt-8">
                    <a href="{{ route('admin.pegawai.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    <button type="submit" class="bg-orange-500 text-white px-8 py-2.5 rounded-lg hover:bg-orange-600 transition">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">SIMMAGI - Sistem Informasi Monitoring Magang Industri</p>
    </div>


    <!-- ============================================================ -->
    <!-- POPUP PREVIEW                                                -->
    <!-- ============================================================ -->
    <div id="previewPopup" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="popup-backdrop absolute inset-0" onclick="closePreviewPopup()"></div>

        <div id="popupContent" class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[92vh] overflow-hidden z-10 flex flex-col">

            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex-shrink-0">
                <h3 class="text-base font-semibold text-gray-800">Preview TTD Digital</h3>
                <button onclick="closePreviewPopup()"
                        class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200 transition-colors text-gray-500 hover:text-gray-800 text-lg font-bold"
                        title="Tutup">
                    x
                </button>
            </div>

            <!-- Body -->
            <div class="p-5 overflow-auto flex-1 relative" id="previewBody">

                <!-- Overlay proses -->
                <div id="processingOverlay" class="absolute inset-0 bg-white/90 flex items-center justify-center z-20">
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


    <!-- ============================================================ -->
    <!-- SCRIPT 1: File selection                                     -->
    <!-- ============================================================ -->
    <script>
    var originalFileDataUrl = null;
    var processedFileDataUrl = null;
    var selectedFileInfo = null;
    var originalFileName = "";

    var fileInput = document.getElementById('ttd_digital');
    var fileInfoContainer = document.getElementById('fileInfoContainer');
    var fileNameDisplay = document.getElementById('fileNameDisplay');

    fileInput.addEventListener('change', function(e) {
        var file = e.target.files[0];

        if (file) {
            originalFileName = file.name;
            selectedFileInfo = {
                name: file.name,
                size: file.size,
                type: file.type
            };
            processedFileDataUrl = null;

            var reader = new FileReader();
            reader.onload = function(ev) {
                originalFileDataUrl = ev.target.result;
                fileNameDisplay.textContent = file.name;
                fileInfoContainer.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            clearFileInfo();
        }
    });

    function removeFile() {
        fileInput.value = '';
        clearFileInfo();
    }

    function clearFileInfo() {
        originalFileDataUrl = null;
        processedFileDataUrl = null;
        originalFileName = "";
        selectedFileInfo = null;
        fileInfoContainer.classList.add('hidden');
        fileNameDisplay.textContent = '';
    }
    </script>


    <!-- ============================================================ -->
    <!-- SCRIPT 2: Popup buka/tutup                                   -->
    <!-- ============================================================ -->
    <script>
    var previewPopup = document.getElementById('previewPopup');
    var popupContent = document.getElementById('popupContent');
    var previewImage = document.getElementById('previewImage');
    var processingOverlay = document.getElementById('processingOverlay');

    function openPreviewPopup() {
        if (!originalFileDataUrl) return;

        processingOverlay.classList.remove('hidden');
        previewImage.style.opacity = '0';

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

        processAndApply();
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

    function processAndApply() {
        fetch(TTD_SERVICE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                image: originalFileDataUrl
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                previewImage.src = data.image;
                previewImage.style.opacity = '1';
                processingOverlay.classList.add('hidden');

                if (data.info) {
                    console.log('[TTD Service]', data.info);
                }

                applyToForm(data.image);
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
            document.getElementById('previewBody').appendChild(warningDiv);
            setTimeout(function() { warningDiv.remove(); }, 6000);
        });
    }

    function applyToForm(dataUrl) {
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
            fileNameDisplay.textContent = newName;
        } catch (err) {
            console.error('Gagal apply ke form:', err);
        }
    }
    </script>

</body>
</html>