<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMMAGI - Edit Mahasiswa</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>

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
            <h2 class="text-center text-3xl font-bold text-gray-800 mb-8">.:: Edit Mahasiswa ::.</h2>
            <form action="{{ route('admin.mahasiswa.update', $mahasiswa->id_mahasiswa) }}" method="POST" enctype="multipart/form-data" id="formMahasiswa">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Nama --}}
                    <div class="md:col-span-2">
                        <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" id="nama" value="{{ old('nama', $mahasiswa->nama) }}" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('nama') border-red-500 @enderror"
                            placeholder="Masukkan nama lengkap" oninput="this.value = this.value.replace(/\b\w/g, function(l){ return l.toUpperCase() })">
                        @error('nama')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- NPM --}}
                    <div>
                        <label for="npm" class="block text-sm font-medium text-gray-700 mb-2">NPM <span class="text-red-500">*</span></label>
                        <input type="text" name="npm" id="npm" value="{{ old('npm', $mahasiswa->npm) }}" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('npm') border-red-500 @enderror"
                            placeholder="Contoh: 240101001">
                        <p class="text-xs text-gray-400 mt-1">* 2 digit pertama akan dibaca sebagai tahun masuk, dan NPM akan menyesuaikan otomatis berdasarkan semester.</p>
                        @error('npm')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Lahir --}}
                    <div>
                        <label for="tgl_lahir" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                        <input type="date" name="tgl_lahir" id="tgl_lahir" value="{{ old('tgl_lahir', $mahasiswa->tgl_lahir ? $mahasiswa->tgl_lahir->format('Y-m-d') : '') }}" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('tgl_lahir') border-red-500 @enderror">
                        @error('tgl_lahir')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div>
                        <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <select name="jenis_kelamin" id="jenis_kelamin" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('jenis_kelamin') border-red-500 @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="L" {{ old('jenis_kelamin', $mahasiswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin', $mahasiswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Prodi --}}
                    <div>
                        <label for="id_prodi" class="block text-sm font-medium text-gray-700 mb-2">Program Studi <span class="text-red-500">*</span></label>
                        <select name="id_prodi" id="id_prodi" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('id_prodi') border-red-500 @enderror">
                            <option value="">-- Pilih Prodi --</option>
                            @foreach ($prodi as $p)
                                <option value="{{ $p->id_prodi }}" {{ old('id_prodi', $mahasiswa->id_prodi) == $p->id_prodi ? 'selected' : '' }}>{{ $p->nama_prodi }}</option>
                            @endforeach
                        </select>
                        @error('id_prodi')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Semester --}}
                    <div>
                        <label for="semester" class="block text-sm font-medium text-gray-700 mb-2">Semester <span class="text-red-500">*</span></label>
                        <select name="semester" id="semester" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('semester') border-red-500 @enderror"
                            style="background-color: #fffbeb; border-color: #fbbf24;">
                            <option value="">-- Pilih --</option>
                            @for ($i = 1; $i <= 14; $i++)
                                <option value="{{ $i }}" {{ old('semester', $mahasiswa->semester) == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                        @error('semester')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kelas --}}
                    <div>
                        <label for="kelas" class="block text-sm font-medium text-gray-700 mb-2">Kelas (Huruf) <span class="text-red-500">*</span></label>
                        <select name="kelas" id="kelas" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('kelas') border-red-500 @enderror">
                            <option value="">-- Pilih Kelas --</option>
                            <option value="A" {{ old('kelas', $mahasiswa->kelas) == 'A' ? 'selected' : '' }}>A</option>
                            <option value="B" {{ old('kelas', $mahasiswa->kelas) == 'B' ? 'selected' : '' }}>B</option>
                            <option value="C" {{ old('kelas', $mahasiswa->kelas) == 'C' ? 'selected' : '' }}>C</option>
                            <option value="D" {{ old('kelas', $mahasiswa->kelas) == 'D' ? 'selected' : '' }}>D</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">* Kelas lengkap otomatis: contoh "Semester 5 + Kelas C" = <strong>3C</strong></p>
                        @error('kelas')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Angkatan --}}
                    <div>
                        <label for="angkatan" class="block text-sm font-medium text-gray-700 mb-2">Angkatan <span class="text-red-500">*</span></label>
                        <input type="number" name="angkatan" id="angkatan" value="{{ old('angkatan', $mahasiswa->angkatan) }}" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('angkatan') border-red-500 @enderror"
                            placeholder="Contoh: 2023" min="2020" max="2100">
                        @error('angkatan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Preview Kelas --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kelas Lengkap <span class="text-blue-500 font-bold text-xs">[Preview]</span></label>
                        <input type="text" id="previewKelas" 
                            readonly
                            class="w-full border border-gray-300 rounded-lg p-3 bg-blue-50 text-blue-700 font-bold cursor-not-allowed"
                            placeholder="Contoh: 3C">
                    </div>

                    {{-- No HP --}}
                    <div>
                        <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-2">No. HP <span class="text-red-500">*</span></label>
                        <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp', $mahasiswa->no_hp) }}" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('no_hp') border-red-500 @enderror"
                            placeholder="Contoh: 08123456789"
                            inputmode="numeric"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        @error('no_hp')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email', $mahasiswa->email) }}" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('email') border-red-500 @enderror"
                            placeholder="Contoh: mahasiswa@gmail.com">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    {{-- Upload TTD Digital --}}
                    <div class="md:col-span-2">
                        <label for="ttd_digital" class="block text-sm font-medium text-gray-700 mb-2">
                            TTD Digital
                            <span class="text-gray-400 font-normal">(Format: PNG/JPG — Background otomatis dibersihkan)</span>
                        </label>

                        {{-- FILE LAMA --}}
                        @if($mahasiswa->ttd_digital)
                            <div class="mb-2">
                                <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 bg-white">
                                    <a href="javascript:void(0)"
                                       class="file-link text-sm text-orange-600 font-medium cursor-pointer truncate"
                                       onclick="openOldFilePreview()">
                                        {{ $mahasiswa->ttd_digital }}
                                    </a>
                                </div>
                                <p class="text-xs text-gray-400 mt-1 ml-1">Klik nama file untuk melihat TTD saat ini</p>
                            </div>
                        @endif

                        {{-- FIELD INPUT FILE BARU --}}
                        <input type="file" name="ttd_digital" id="ttd_digital" accept="image/*" 
                            class="w-full border border-gray-300 rounded-lg p-2 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300">

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
                </div>

                {{-- Info Akun --}}
                <div class="mt-8 p-4 bg-[#fffbeb] border border-[#ece75f]/40 rounded-lg">
                    <p class="text-sm text-[#b45309]">
                        <strong>Perhatian:</strong> Perubahan NPM akan mengubah username akun login mahasiswa. Password tetap menggunakan tanggal lahir.
                    </p>
                </div>

                {{-- Tombol --}}
                <div class="flex justify-between mt-8">
                    <a href="{{ route('admin.mahasiswa.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition">
                        Kembali
                    </a>
                    <button type="submit" class="bg-orange-500 text-white px-8 py-2.5 rounded-lg hover:bg-orange-600 transition">
                        Update
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
                <h3 id="popupTitle" class="text-base font-semibold text-gray-800"></h3>
                <button onclick="closePreviewPopup()"
                        class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200 transition-colors text-gray-500 hover:text-gray-800 text-lg font-bold"
                        title="Tutup">
                    x
                </button>
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


    <!-- ============================================================ -->
    <!-- SCRIPT 1: NPM, Angkatan, Semester, Preview Kelas             -->
    <!-- ============================================================ -->
    <script>
    var semesterDipilihManual = true;

    document.getElementById('npm').addEventListener('input', function() {
        semesterDipilihManual = false;
        generateNpm();
    });
    document.getElementById('semester').addEventListener('change', function() {
        semesterDipilihManual = true;
        generateNpm();
        updatePreviewKelas();
    });
    document.getElementById('kelas').addEventListener('change', updatePreviewKelas);
    document.getElementById('id_prodi').addEventListener('change', updatePreviewKelas);

    function updatePreviewKelas() {
        var semesterSelect = document.getElementById('semester');
        var kelasSelect = document.getElementById('kelas');
        var prodiSelect = document.getElementById('id_prodi');
        var previewInput = document.getElementById('previewKelas');
        
        var semester = parseInt(semesterSelect.value) || 0;
        var kelas = kelasSelect.value;
        
        if (semester > 0 && kelas) {
            var tahunKelas = Math.ceil(semester / 2);
            
            var maxTahunKelas = 4;
            var selectedOption = prodiSelect.options[prodiSelect.selectedIndex];
            if (selectedOption && selectedOption.text) {
                var namaProdi = selectedOption.text.toUpperCase();
                if (namaProdi.includes('TI')) {
                    maxTahunKelas = 3;
                }
            }
            
            if (tahunKelas > maxTahunKelas) {
                previewInput.value = kelas;
            } else {
                previewInput.value = tahunKelas + kelas;
            }
            previewInput.style.borderColor = '#3b82f6';
            previewInput.style.backgroundColor = '#eff6ff';
        } else {
            previewInput.value = '';
            previewInput.style.borderColor = '#d1d5db';
            previewInput.style.backgroundColor = '#eff6ff';
        }
    }

    function generateNpm() {
        var npmInput = document.getElementById('npm');
        var semesterSelect = document.getElementById('semester');
        var angkatanInput = document.getElementById('angkatan');
        
        var npm = npmInput.value.trim();
        var semester = parseInt(semesterSelect.value) || 0;
        
        if (npm.length >= 2) {
            var tahunMasuk = parseInt(npm.substring(0, 2));
            
            if (!isNaN(tahunMasuk) && tahunMasuk >= 20 && tahunMasuk <= 99) {
                tahunMasuk = 2000 + tahunMasuk;
                
                angkatanInput.value = tahunMasuk;

                var tahunSekarang = new Date().getFullYear();
                var selisihTahun = tahunSekarang - tahunMasuk;
                var semesterOtomatis = selisihTahun * 2;

                if (!semesterDipilihManual && semesterOtomatis > 0 && semesterOtomatis <= 14) {
                    semesterSelect.value = semesterOtomatis;
                    semester = semesterOtomatis;
                    updatePreviewKelas();
                }

                if (semester > 0) {
                    var tahunDepanNpm = String(tahunMasuk % 100).padStart(2, '0');
                    var sisaNpm = npm.substring(2);
                    npmInput.value = tahunDepanNpm + sisaNpm;
                    
                    npmInput.style.borderColor = '#22c55e';
                    npmInput.style.backgroundColor = '#f0fdf4';
                    return;
                }
            }
        }
        
        npmInput.style.borderColor = '#d1d5db'; 
        npmInput.style.backgroundColor = '#ffffff';
    }

    document.addEventListener('DOMContentLoaded', function() {
        generateNpm();
        updatePreviewKelas();
    });
    </script>


    <!-- ============================================================ -->
    <!-- SCRIPT 2: File baru selection & state                        -->
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
            selectedFileInfo = {
                name: file.name,
                size: file.size,
                type: file.type
            };
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
    <!-- SCRIPT 3: Popup buka/tutup                                   -->
    <!-- ============================================================ -->
    <script>
    var previewPopup = document.getElementById('previewPopup');
    var popupContent = document.getElementById('popupContent');
    var popupTitle = document.getElementById('popupTitle');
    var previewImage = document.getElementById('previewImage');
    var processingOverlay = document.getElementById('processingOverlay');
    var previewBody = document.getElementById('previewBody');

    var oldFileUrl = '{{ $mahasiswa->ttd_digital ? asset("storage/ttd_digital/" . $mahasiswa->ttd_digital) : "" }}';

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
    <!-- SCRIPT 4: TTD Processing via OpenCV Backend                  -->
    <!-- ============================================================ -->
    <script>
    var TTD_SERVICE_URL = 'http://localhost:5001/process';

    function processNewFile() {
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

</body>
</html>