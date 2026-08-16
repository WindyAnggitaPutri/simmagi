@extends('layouts.app') {{-- Extends layout utama aplikasi --}}

@section('title', 'Absensi - Tambah') {{-- Judul tab browser --}}

@section('content') {{-- Mulai section content --}}
<div class="max-w-4xl mx-auto px-4 py-8"> {{-- Container dengan lebar maksimal 4xl --}}

    <!-- Header -->
    <div class="flex items-center gap-3 mb-8"> {{-- Flexbox: icon kembali + judul --}}
        
        {{-- Tombol kembali ke riwayat absensi --}}
        <a href="{{ route('mahasiswa.absensi.index') }}" 
           class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/> {{-- Icon panah kiri --}}
            </svg>
        </a>
        
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Absensi Magang</h1> {{-- Judul halaman --}}
            <p class="text-gray-500 mt-1">Lakukan absensi harian selama masa magang</p> {{-- Deskripsi --}}
        </div>
    </div>

    <!-- Info Cards -->
    {{-- Grid 2 kolom untuk menampilkan info tanggal hari ini dan nama perusahaan --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        
        {{-- Card: Tanggal Hari Ini --}}
        <div class="bg-white rounded-lg shadow p-5 border border-gray-100">
            <div class="text-sm font-medium text-gray-500">Tanggal Hari Ini</div>
            <div class="mt-1 text-lg font-semibold text-gray-800">{{ now()->locale('id_ID')->translatedFormat('d F Y') }}</div> {{-- Tanggal dalam format Indonesia --}}
        </div>
        
        {{-- Card: Nama Perusahaan --}}
        <div class="bg-white rounded-lg shadow p-5 border border-gray-100">
            <div class="text-sm font-medium text-gray-500">Perusahaan</div>
            <div class="mt-1 text-sm font-semibold text-gray-800 truncate">{{ $namaPerusahaan }}</div> {{-- Nama perusahaan, truncate jika panjang --}}
        </div>
    </div>

    <!-- Form Absensi -->
    <div class="bg-white rounded-lg shadow mb-8 border border-gray-100"> {{-- Container form --}}
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Form Absensi Hari Ini</h2> {{-- Judul form --}}
        </div>
        <div class="p-6">

            <!-- 1. PILIH STATUS -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Kehadiran <span class="text-red-500">*</span></label> {{-- Label wajib --}}
                <select id="select-status" class="w-full md:w-1/2 border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    <option value="">-- Pilih Status --</option> {{-- Placeholder --}}
                    <option value="hadir">Hadir</option>
                    <option value="sakit">Sakit</option>
                    <option value="izin">Izin</option>
                </select>
                <p id="status-hint" class="text-xs text-gray-400 mt-1.5 hidden"></p> {{-- Hint text, awalnya tersembunyi --}}
            </div>

            <!-- 1.5 CATATAN (opsional, selalu tampil setelah pilih status) -->
            <div id="section-catatan" class="hidden mb-6"> {{-- Awalnya tersembunyi, muncul setelah pilih status --}}
                <label for="input-catatan" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea id="input-catatan" rows="3" maxlength="1000" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition resize-y" placeholder="Opsional. Tambahkan keterangan jika diperlukan..."></textarea>
                <p class="text-xs text-gray-400 mt-1 text-right"><span id="catatan-count">0</span>/1000</p> {{-- Penghitung karakter --}}
            </div>

            <!-- 2. SEKSI FOTO (muncul untuk semua status) -->
            <div id="section-foto" class="hidden mb-6"> {{-- Awalnya tersembunyi --}}
                <label id="label-foto" class="block text-sm font-medium text-gray-700 mb-2">Foto Absensi <span class="text-red-500">*</span></label> {{-- Label dinamis, berubah sesuai status --}}

                <div class="flex flex-col items-center"> {{-- Konten foto di tengah --}}
                    
                    {{-- Box preview foto --}}
                    <div id="foto-preview-box" class="relative w-80 h-60 bg-gray-100 rounded-lg overflow-hidden border-2 border-dashed border-gray-300">
                        <img id="foto-preview-img" src="" alt="Preview" class="w-full h-full object-contain bg-white" style="display:none;"> {{-- Gambar preview, awalnya tersembunyi --}}
                        <div id="foto-placeholder" class="flex flex-col items-center justify-center h-full text-gray-400"> {{-- Placeholder saat belum ada foto --}}
                            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Icon kamera besar --}}
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span id="foto-placeholder-text" class="text-sm">Belum ada foto</span> {{-- Teks placeholder, berubah sesuai status --}}
                        </div>
                    </div>

                    {{-- Tombol aksi foto --}}
                    <div class="mt-4 flex gap-3">
                        <button type="button" id="btn-buka-kamera" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm font-medium">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Ambil Foto
                            </span>
                        </button>

                        <button type="button" id="btn-hapus-foto" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm font-medium" style="display:none;"> {{-- Awalnya tersembunyi, muncul setelah foto diambil --}}
                            Hapus Foto
                        </button>
                    </div>

                    <p id="foto-hint" class="text-xs text-gray-400 mt-2 text-center hidden"></p> {{-- Petunjuk foto, berubah sesuai status --}}
                </div>
            </div>

            <!-- 3. SEKSI LOKASI GPS (muncul untuk semua status) -->
            <div id="section-lokasi" class="hidden mb-6"> {{-- Awalnya tersembunyi --}}
                <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi GPS <span class="text-red-500">*</span></label> {{-- Label wajib --}}
                
                {{-- Teks status lokasi --}}
                <div class="flex items-center gap-2">
                    <span id="lokasi-status" class="text-sm text-gray-500">Akan diambil otomatis setelah foto</span> {{-- Teks berubah sesuai kondisi --}}
                </div>

                {{-- Box info koordinat (muncul setelah lokasi berhasil diambil) --}}
                <div id="lokasi-info" class="mt-3 hidden">
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"> {{-- Icon pin hijau --}}
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-600 font-mono">Lat: <span id="lat-display"></span> | Lng: <span id="lng-display"></span></p> {{-- Koordinat ditampilkan di sini --}}
                                <p class="text-xs text-gray-500 mt-1 break-words" id="alamat-display"></p> {{-- Alamat dari reverse geocoding --}}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Loading spinner (muncul saat mengambil lokasi) --}}
                <div id="lokasi-loading" class="mt-3 hidden">
                    <div class="flex items-center gap-2 text-sm text-blue-600">
                        <div class="w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div> {{-- Spinner animasi --}}
                        <span>Mengambil lokasi GPS...</span>
                    </div>
                </div>

                {{-- Box error lokasi (muncul jika gagal mengambil lokasi) --}}
                <div id="lokasi-error-box" class="mt-3 hidden">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"> {{-- Icon warning --}}
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-xs text-red-600" id="lokasi-error-msg">Gagal mengambil lokasi.</p> {{-- Pesan error dinamis --}}
                                <button type="button" id="btn-coba-lokasi" class="mt-1 text-xs text-red-500 hover:text-red-700 hover:underline font-medium">Coba Lagi</button> {{-- Tombol retry --}}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Embed Google Maps (muncul setelah lokasi berhasil) --}}
                <div id="gmaps-container" class="mt-3 hidden">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Icon peta --}}
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Lokasi di Google Maps</span>
                    </div>
                    <div class="rounded-lg overflow-hidden border-2 border-blue-200 shadow-sm">
                        <iframe id="gmaps-iframe" src="" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe> {{-- Frame peta, src diisi via JS --}}
                    </div>
                    <a id="gmaps-link" href="#" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center gap-1 text-xs text-blue-500 hover:text-blue-700 hover:underline font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Buka di Google Maps
                    </a>
                </div>
            </div>

            {{-- Hidden inputs untuk menyimpan data yang akan dikirim ke server --}}
            <input type="hidden" id="input-lat" value="">     {{-- Latitude --}}
            <input type="hidden" id="input-lng" value="">     {{-- Longitude --}}
            <input type="hidden" id="input-alamat" value=""> {{-- Alamat dari reverse geocoding --}}

            {{-- Tombol submit, disabled sampai form lengkap --}}
            <button type="button" id="btn-submit" class="w-full py-3 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition font-semibold text-lg disabled:bg-gray-300 disabled:cursor-not-allowed" disabled>Simpan Absensi</button>
        </div>
    </div>
</div>

<!-- MODAL KAMERA -->
{{-- Modal popup fullscreen untuk mengakses kamera dan mengambil foto --}}
<div id="modal-kamera" class="fixed inset-0 bg-black bg-opacity-75 z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg w-full max-w-lg shadow-2xl overflow-hidden" onclick="event.stopPropagation()"> {{-- Klik di dalam modal tidak menutup modal --}}
        
        {{-- Header modal --}}
        <div class="px-5 py-3 bg-gray-800 text-white flex justify-between items-center">
            <h3 id="kamera-modal-title" class="font-semibold text-sm">Ambil Foto</h3> {{-- Judul dinamis sesuai status --}}
            <button type="button" id="btn-tutup-kamera" class="text-gray-300 hover:text-white"> {{-- Tombol tutup --}}
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        {{-- Area kamera --}}
        <div class="relative bg-black" style="height:360px;">
            <video id="kamera-video" class="w-full h-full object-cover" autoplay playsinline muted></video> {{-- Video streaming dari kamera --}}
            <canvas id="kamera-canvas" class="w-full h-full object-cover absolute inset-0" style="display:none;"></canvas> {{-- Canvas untuk capture foto, di atas video --}}
            <div id="kamera-guide" class="absolute inset-0 flex items-center justify-center pointer-events-none"> {{-- Guide lingkaran, tidak bisa diklik --}}
                <div class="w-56 h-56 border-2 border-white rounded-full opacity-50"></div>
            </div>
            <div id="kamera-error" class="absolute inset-0 flex flex-col items-center justify-center text-white hidden"> {{-- Tampilan error kamera --}}
                <svg class="w-10 h-10 mb-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                <p id="kamera-error-msg" class="text-sm text-center px-4">Gagal mengakses kamera</p>
            </div>
        </div>
        
        {{-- Tombol aksi di bawah modal --}}
        <div class="px-5 py-4 flex justify-center gap-4">
            <button type="button" id="btn-ambil-foto" class="px-6 py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition text-sm font-semibold">Ambil Foto</button> {{-- Capture foto --}}
            <button type="button" id="btn-ulang-foto" class="px-6 py-2.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition text-sm font-semibold" style="display:none;">Ambil Ulang</button> {{-- Muncul setelah capture --}}
            <button type="button" id="btn-pakai-foto" class="px-6 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm font-semibold" style="display:none;">Gunakan Foto</button> {{-- Konfirmasi pakai foto --}}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() { {{-- Jalankan setelah DOM siap --}}

    // ============================================
    // VARIABEL GLOBAL
    // ============================================
    var stream = null;             // Menyimpan referensi stream kamera (untuk di-stop nanti)
    var capturedPhoto = null;      // Menyimpan data URL foto yang sudah di-capture
    var locationCaptured = false;  // Flag: apakah lokasi GPS sudah berhasil diambil
    var selectedStatus = '';       // Status kehadiran yang dipilih (hadir/sakit/izin)

    // ============================================
    // REFERENSI ELEMEN DOM
    // ============================================
    var selectStatus = document.getElementById('select-status');         // Dropdown pilih status
    var statusHint = document.getElementById('status-hint');             // Teks hint status
    var sectionCatatan = document.getElementById('section-catatan');     // Section catatan
    var inputCatatan = document.getElementById('input-catatan');         // Textarea catatan
    var catatanCount = document.getElementById('catatan-count');         // Penghitung karakter catatan
    var sectionFoto = document.getElementById('section-foto');           // Section foto
    var labelFoto = document.getElementById('label-foto');               // Label foto (dinamis)
    var fotoPreviewBox = document.getElementById('foto-preview-box');   // Box preview foto
    var fotoPreviewImg = document.getElementById('foto-preview-img');   // Elemen gambar preview
    var fotoPlaceholder = document.getElementById('foto-placeholder');   // Placeholder foto
    var fotoPlaceholderText = document.getElementById('foto-placeholder-text'); // Teks placeholder (dinamis)
    var fotoHint = document.getElementById('foto-hint');                 // Petunjuk foto (dinamis)
    var btnBukaKamera = document.getElementById('btn-buka-kamera');     // Tombol buka kamera
    var btnHapusFoto = document.getElementById('btn-hapus-foto');       // Tombol hapus foto
    var sectionLokasi = document.getElementById('section-lokasi');       // Section lokasi GPS
    var lokasiStatus = document.getElementById('lokasi-status');         // Teks status lokasi
    var lokasiLoading = document.getElementById('lokasi-loading');       // Spinner loading lokasi
    var lokasiErrorBox = document.getElementById('lokasi-error-box');   // Box error lokasi
    var lokasiErrorMsg = document.getElementById('lokasi-error-msg');   // Pesan error lokasi
    var btnCobaLokasi = document.getElementById('btn-coba-lokasi');     // Tombol coba lagi lokasi
    var lokasiInfo = document.getElementById('lokasi-info');             // Box info koordinat
    var gmapsContainer = document.getElementById('gmaps-container');     // Container peta Google Maps
    var gmapsIframe = document.getElementById('gmaps-iframe');           // Iframe Google Maps
    var gmapsLink = document.getElementById('gmaps-link');               // Link buka di Google Maps
    var modalKamera = document.getElementById('modal-kamera');           // Modal kamera
    var kameraModalTitle = document.getElementById('kamera-modal-title'); // Judul modal kamera
    var btnTutupKamera = document.getElementById('btn-tutup-kamera');   // Tombol tutup modal
    var kameraVideo = document.getElementById('kamera-video');           // Elemen video kamera
    var kameraCanvas = document.getElementById('kamera-canvas');         // Elemen canvas untuk capture
    var kameraGuide = document.getElementById('kamera-guide');           // Guide lingkaran
    var kameraError = document.getElementById('kamera-error');           // Area error kamera
    var kameraErrorMsg = document.getElementById('kamera-error-msg');   // Pesan error kamera
    var btnAmbilFoto = document.getElementById('btn-ambil-foto');       // Tombol capture foto
    var btnUlangFoto = document.getElementById('btn-ulang-foto');       // Tombol ambil ulang
    var btnPakaiFoto = document.getElementById('btn-pakai-foto');       // Tombol gunakan foto
    var btnSubmit = document.getElementById('btn-submit');               // Tombol submit form

    // Validasi keberadaan CSRF token
    var csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF meta tag tidak ditemukan!');
        alert('Terjadi kesalahan: CSRF token tidak ditemukan. Silakan refresh halaman.');
        return; // Hentikan eksekusi jika tidak ada CSRF
    }

    // ============================================
    // EVENT: Hitung karakter catatan secara real-time
    // ============================================
    inputCatatan.addEventListener('input', function() {
        catatanCount.textContent = this.value.length; // Update tampilan jumlah karakter
    });

    // ============================================
    // KONFIGURASI PER STATUS
    // Menentukan label, hint, dan judul modal untuk masing-masing status
    // ============================================
    var statusConfig = {
        hadir: {
            showFoto: true,               // Tampilkan section foto
            showLokasi: true,             // Tampilkan section lokasi
            fotoLabel: 'Foto Selfie Absensi',     // Label foto untuk hadir
            fotoPlaceholder: 'Belum ada foto selfie', // Teks placeholder
            fotoHint: 'Ambil foto selfie dengan latar belakang tempat magang', // Petunjuk
            kameraTitle: 'Ambil Foto Selfie'        // Judul modal kamera
        },
        sakit: {
            showFoto: true,
            showLokasi: true,
            fotoLabel: 'Foto Bukti Sakit',
            fotoPlaceholder: 'Belum ada bukti sakit',
            fotoHint: 'Ambil foto bukti sakit (surat keterangan dokter, rekam medis, dll)',
            kameraTitle: 'Ambil Foto Bukti Sakit'
        },
        izin: {
            showFoto: true,
            showLokasi: true,
            fotoLabel: 'Foto Bukti Izin',
            fotoPlaceholder: 'Belum ada bukti izin',
            fotoHint: 'Ambil foto bukti izin (surat izin, undangan, dll)',
            kameraTitle: 'Ambil Foto Bukti Izin'
        }
    };

    // ============================================
    // EVENT: Perubahan dropdown status
    // Menampilkan/menyembunyikan section sesuai status yang dipilih
    // ============================================
    selectStatus.addEventListener('change', function() {
        selectedStatus = this.value; // Simpan status yang dipilih

        // Sembunyikan semua section terlebih dahulu
        sectionCatatan.classList.add('hidden');
        sectionFoto.classList.add('hidden');
        sectionLokasi.classList.add('hidden');
        statusHint.classList.add('hidden');
        fotoHint.classList.add('hidden');

        // Jika tidak ada status atau status tidak valid, reset dan cek form
        if (!selectedStatus || !statusConfig[selectedStatus]) {
            resetSemuaState();
            cekFormLengkap();
            return;
        }

        var cfg = statusConfig[selectedStatus]; // Ambil konfigurasi sesuai status
        resetSemuaState(); // Reset semua state sebelumnya

        // Tampilkan section catatan (selalu tampil untuk semua status)
        sectionCatatan.classList.remove('hidden');

        // Tampilkan section foto jika konfigurasi mengizinkan
        if (cfg.showFoto) {
            sectionFoto.classList.remove('hidden');
            labelFoto.innerHTML = cfg.fotoLabel + ' <span class="text-red-500">*</span>'; // Update label
            fotoPlaceholderText.textContent = cfg.fotoPlaceholder; // Update placeholder
            fotoHint.textContent = cfg.fotoHint; // Update hint
            fotoHint.classList.remove('hidden'); // Tampilkan hint
        }

        // Tampilkan section lokasi jika konfigurasi mengizinkan
        if (cfg.showLokasi) {
            sectionLokasi.classList.remove('hidden');
        }

        cekFormLengkap(); // Cek apakah form sudah lengkap
    });

    // ============================================
    // FUNGSI: Reset semua state ke kondisi awal
    // Dipanggil saat ganti status atau hapus foto
    // ============================================
    function resetSemuaState() {
        capturedPhoto = null;      // Kosongkan foto
        locationCaptured = false;  // Reset flag lokasi
        inputCatatan.value = '';   // Kosongkan catatan
        catatanCount.textContent = '0'; // Reset penghitung

        // Reset tampilan foto
        fotoPreviewImg.src = '';
        fotoPreviewImg.style.display = 'none';
        fotoPlaceholder.style.display = 'flex';
        btnHapusFoto.style.display = 'none';
        fotoPreviewBox.classList.add('border-dashed', 'border-gray-300'); // Border dashed default
        fotoPreviewBox.classList.remove('border-solid', 'border-green-400');

        // Reset hidden inputs lokasi
        document.getElementById('input-lat').value = '';
        document.getElementById('input-lng').value = '';
        document.getElementById('input-alamat').value = '';

        // Sembunyikan semua sub-elemen lokasi
        lokasiInfo.classList.add('hidden');
        gmapsContainer.classList.add('hidden');
        lokasiErrorBox.classList.add('hidden');
        lokasiLoading.classList.add('hidden');

        // Reset teks status lokasi ke default
        lokasiStatus.textContent = 'Akan diambil otomatis setelah foto';
        lokasiStatus.classList.remove('text-green-600', 'text-red-600');
        lokasiStatus.classList.add('text-gray-500');
        lokasiStatus.classList.remove('hidden');
    }

    // ============================================
    // EVENT: Tombol buka kamera
    // Membuka modal dan memulai stream kamera
    // ============================================
    btnBukaKamera.addEventListener('click', function() {
        var cfg = statusConfig[selectedStatus];
        kameraModalTitle.textContent = cfg ? cfg.kameraTitle : 'Ambil Foto'; // Set judul modal sesuai status
        modalKamera.classList.remove('hidden'); // Tampilkan modal
        document.body.style.overflow = 'hidden'; // Cegah scroll di background
        mulaiKamera(); // Mulai akses kamera
    });

    // ============================================
    // EVENT: Tutup modal kamera
    // ============================================
    btnTutupKamera.addEventListener('click', tutupModalKamera); // Klik tombol X
    modalKamera.addEventListener('click', function(e) {
        if (e.target === modalKamera) tutupModalKamera(); // Klik di luar konten modal
    });

    // ============================================
    // FUNGSI: Tutup modal kamera
    // Stop stream dan reset tampilan
    // ============================================
    function tutupModalKamera() {
        modalKamera.classList.add('hidden'); // Sembunyikan modal
        document.body.style.overflow = ''; // Kembalikan scroll
        stopKamera(); // Hentikan stream kamera
        resetTampilanKamera(); // Reset tampilan modal ke awal
    }

    // ============================================
    // FUNGSI: Reset tampilan modal kamera ke keadaan awal
    // ============================================
    function resetTampilanKamera() {
        kameraVideo.style.display = 'block';   // Tampilkan video
        kameraCanvas.style.display = 'none';   // Sembunyikan canvas
        kameraGuide.classList.remove('hidden'); // Tampilkan guide lingkaran
        kameraError.classList.add('hidden');   // Sembunyikan error
        btnAmbilFoto.style.display = 'inline-block'; // Tampilkan tombol ambil
        btnUlangFoto.style.display = 'none';        // Sembunyikan tombol ulang
        btnPakaiFoto.style.display = 'none';        // Sembunyikan tombol pakai
    }

    // ============================================
    // FUNGSI: Mulai akses kamera
    // Menggunakan getUserMedia API
    // ============================================
    function mulaiKamera() {
        resetTampilanKamera(); // Reset tampilan dulu

        // Cek dukungan browser
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            tampilkanErrorKamera('Browser tidak mendukung kamera.');
            return;
        }

        // Minta akses kamera depan (facingMode: 'user')
        navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } } // Resolusi ideal 640x480
        }).then(function(s) { // Jika berhasil
            stream = s; // Simpan stream
            kameraVideo.srcObject = stream; // Set ke elemen video
            kameraVideo.play(); // Putar video
        }).catch(function(err) { // Jika gagal
            // Tentukan pesan error berdasarkan jenis error
            var pesan = 'Gagal mengakses kamera.';
            if (err.name === 'NotAllowedError') pesan = 'Izin kamera ditolak. Silakan izinkan akses kamera di browser Anda, lalu coba lagi.';
            else if (err.name === 'NotFoundError') pesan = 'Tidak ditemukan kamera di perangkat ini.';
            else if (err.name === 'NotReadableError') pesan = 'Kamera sedang digunakan aplikasi lain. Tutup aplikasi lain lalu coba lagi.';
            tampilkanErrorKamera(pesan);
        });
    }

    // ============================================
    // FUNGSI: Tampilkan error di area modal kamera
    // ============================================
    function tampilkanErrorKamera(pesan) {
        kameraVideo.style.display = 'none';       // Sembunyikan video
        kameraGuide.classList.add('hidden');       // Sembunyikan guide
        kameraError.classList.remove('hidden');    // Tampilkan area error
        kameraErrorMsg.textContent = pesan;        // Set pesan error
        btnAmbilFoto.style.display = 'none';      // Sembunyikan tombol ambil
    }

    // ============================================
    // FUNGSI: Hentikan stream kamera
    // Penting untuk melepas akses kamera (lampu indikator mati)
    // ============================================
    function stopKamera() {
        if (stream) {
            stream.getTracks().forEach(function(t) { t.stop(); }); // Stop semua track
            stream = null;
        }
        kameraVideo.srcObject = null; // Hapus referensi stream dari video
    }

    // ============================================
    // EVENT: Tombol ambil foto (capture)
    // Menggambar frame video ke canvas dan konversi ke data URL
    // ============================================
    btnAmbilFoto.addEventListener('click', function() {
        if (!stream) return; // Jangan proses jika tidak ada stream

        var ctx = kameraCanvas.getContext('2d'); // Ambil context 2D canvas
        kameraCanvas.width = kameraVideo.videoWidth;   // Set lebar canvas sesuai video
        kameraCanvas.height = kameraVideo.videoHeight;  // Set tinggi canvas sesuai video
        ctx.drawImage(kameraVideo, 0, 0); // Gambar frame video ke canvas

        capturedPhoto = kameraCanvas.toDataURL('image/jpeg', 0.85); // Konversi ke JPEG kualitas 85%

        // Update tampilan modal: tampilkan canvas, sembunyikan video dan guide
        kameraVideo.style.display = 'none';
        kameraCanvas.style.display = 'block';
        kameraGuide.classList.add('hidden');
        btnAmbilFoto.style.display = 'none';        // Sembunyikan tombol ambil
        btnUlangFoto.style.display = 'inline-block'; // Tampilkan tombol ulang
        btnPakaiFoto.style.display = 'inline-block'; // Tampilkan tombol pakai

        stopKamera(); // Hentikan stream kamera
    });

    // ============================================
    // EVENT: Tombol ambil ulang foto
    // Reset canvas dan mulai kamera lagi
    // ============================================
    btnUlangFoto.addEventListener('click', function() {
        capturedPhoto = null; // Hapus foto yang sudah di-capture
        kameraCanvas.style.display = 'none'; // Sembunyikan canvas
        mulaiKamera(); // Mulai kamera lagi
    });

    // ============================================
    // EVENT: Tombol gunakan foto
    // Terapkan foto ke preview form, tutup modal, dan ambil lokasi
    // ============================================
    btnPakaiFoto.addEventListener('click', function() {
        if (!capturedPhoto) return; // Jangan proses jika tidak ada foto

        tampilkanPreviewKamera(capturedPhoto); // Tampilkan di preview form
        tutupModalKamera(); // Tutup modal kamera
        ambilLokasiOtomatis(); // Otomatis ambil lokasi GPS setelah foto
    });

    // ============================================
    // FUNGSI: Tampilkan foto yang di-capture ke preview di form
    // ============================================
    function tampilkanPreviewKamera(dataUrl) {
        fotoPreviewImg.src = dataUrl;              // Set sumber gambar
        fotoPreviewImg.style.display = 'block';    // Tampilkan gambar
        fotoPlaceholder.style.display = 'none';    // Sembunyikan placeholder
        btnHapusFoto.style.display = 'inline-block'; // Tampilkan tombol hapus

        // Ubah border dari dashed abu-abu menjadi solid hijau
        fotoPreviewBox.classList.remove('border-dashed', 'border-gray-300');
        fotoPreviewBox.classList.add('border-solid', 'border-green-400');
    }

    // ============================================
    // EVENT: Tombol hapus foto
    // Reset semua yang terkait foto dan lokasi
    // ============================================
    btnHapusFoto.addEventListener('click', function() {
        capturedPhoto = null;       // Hapus foto
        locationCaptured = false;   // Reset flag lokasi

        // Reset tampilan preview foto
        fotoPreviewImg.src = '';
        fotoPreviewImg.style.display = 'none';
        fotoPlaceholder.style.display = 'flex';
        btnHapusFoto.style.display = 'none';
        fotoPreviewBox.classList.add('border-dashed', 'border-gray-300');
        fotoPreviewBox.classList.remove('border-solid', 'border-green-400');

        // Reset data lokasi
        document.getElementById('input-lat').value = '';
        document.getElementById('input-lng').value = '';
        document.getElementById('input-alamat').value = '';
        lokasiInfo.classList.add('hidden');
        gmapsContainer.classList.add('hidden');
        lokasiErrorBox.classList.add('hidden');
        lokasiLoading.classList.add('hidden');

        // Reset teks status lokasi
        lokasiStatus.textContent = 'Akan diambil otomatis setelah foto';
        lokasiStatus.classList.remove('text-green-600', 'text-red-600');
        lokasiStatus.classList.add('text-gray-500');
        lokasiStatus.classList.remove('hidden');

        cekFormLengkap(); // Cek kembali kelengkapan form
    });

    // ============================================
    // FUNGSI: Ambil lokasi GPS secara otomatis
    // Menggunakan Geolocation API + Reverse Geocoding Nominatim
    // ============================================
    function ambilLokasiOtomatis() {
        // Cek dukungan browser
        if (!navigator.geolocation) {
            tampilkanErrorLokasi('Browser tidak mendukung fitur lokasi.');
            return;
        }

        // Tampilkan loading, sembunyikan yang lain
        lokasiStatus.classList.add('hidden');
        lokasiErrorBox.classList.add('hidden');
        lokasiInfo.classList.add('hidden');
        gmapsContainer.classList.add('hidden');
        lokasiLoading.classList.remove('hidden'); // Tampilkan spinner

        // Panggil Geolocation API
        navigator.geolocation.getCurrentPosition(
            // CALLBACK SUKSES: lokasi berhasil diambil
            function(pos) {
                var lat = pos.coords.latitude;   // Latitude
                var lng = pos.coords.longitude;  // Longitude

                // Simpan ke hidden inputs
                document.getElementById('input-lat').value = lat;
                document.getElementById('input-lng').value = lng;

                // Tampilkan koordinat di info box
                document.getElementById('lat-display').textContent = lat.toFixed(6);
                document.getElementById('lng-display').textContent = lng.toFixed(6);

                // Update UI: sembunyikan loading, tampilkan info
                lokasiLoading.classList.add('hidden');
                lokasiInfo.classList.remove('hidden');
                lokasiStatus.classList.remove('hidden');
                lokasiStatus.textContent = 'Lokasi berhasil diambil';
                lokasiStatus.classList.remove('text-gray-500', 'text-red-600');
                lokasiStatus.classList.add('text-green-600');

                // Set embed Google Maps
                var mapsEmbedUrl = 'https://www.google.com/maps?q=' + lat + ',' + lng + '&output=embed&z=17';
                gmapsIframe.src = mapsEmbedUrl;
                gmapsLink.href = 'https://www.google.com/maps?q=' + lat + ',' + lng;
                gmapsContainer.classList.remove('hidden');

                // Reverse Geocoding: ambil nama alamat dari koordinat menggunakan Nominatim (OpenStreetMap)
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            var d = JSON.parse(xhr.responseText);
                            if (d.display_name) { // Jika ada nama alamat
                                document.getElementById('input-alamat').value = d.display_name; // Simpan ke hidden input
                                document.getElementById('alamat-display').textContent = d.display_name; // Tampilkan di UI
                            }
                        } catch(e) {} // Abaikan error parse
                    }
                };
                xhr.send(); // Kirim request

                locationCaptured = true; // Set flag lokasi berhasil
                cekFormLengkap(); // Cek kelengkapan form
            },
            // CALLBACK ERROR: lokasi gagal diambil
            function(err) {
                // Tentukan pesan error berdasarkan kode error
                var msg = 'Gagal mengambil lokasi.';
                if (err.code === 1) msg = 'Izin lokasi ditolak. Izinkan akses lokasi di browser, lalu klik Coba Lagi.';
                else if (err.code === 2) msg = 'Lokasi tidak tersedia di perangkat ini.';
                else if (err.code === 3) msg = 'Timeout mengambil lokasi. Coba lagi.';
                tampilkanErrorLokasi(msg);
            },
            // OPTIONS: akurasi tinggi, timeout 15 detik, tidak gunakan cache
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    }

    // ============================================
    // FUNGSI: Tampilkan error lokasi
    // ============================================
    function tampilkanErrorLokasi(pesan) {
        lokasiLoading.classList.add('hidden');    // Sembunyikan loading
        lokasiInfo.classList.add('hidden');       // Sembunyikan info
        gmapsContainer.classList.add('hidden');   // Sembunyikan peta
        lokasiStatus.classList.remove('hidden');  // Tampilkan status
        lokasiStatus.textContent = 'Gagal mengambil lokasi';
        lokasiStatus.classList.remove('text-gray-500', 'text-green-600');
        lokasiStatus.classList.add('text-red-600');
        lokasiErrorMsg.textContent = pesan;       // Set pesan error
        lokasiErrorBox.classList.remove('hidden'); // Tampilkan box error
        locationCaptured = false;                 // Set flag gagal
        cekFormLengkap();                         // Cek form
    }

    // ============================================
    // EVENT: Tombol coba lagi ambil lokasi
    // ============================================
    btnCobaLokasi.addEventListener('click', function() {
        ambilLokasiOtomatis(); // Panggil ulang fungsi ambil lokasi
    });

    // ============================================
    // FUNGSI: Cek apakah form sudah lengkap
    // Tombol submit hanya enabled jika status, foto, dan lokasi sudah ada
    // ============================================
    function cekFormLengkap() {
        if (!selectedStatus) { // Jika belum pilih status
            btnSubmit.disabled = true;
            return;
        }
        // Enabled hanya jika foto sudah diambil DAN lokasi sudah berhasil
        btnSubmit.disabled = !(capturedPhoto && locationCaptured);
    }

    // ============================================
    // EVENT: Tombol submit absensi
    // Mengirim data via AJAX (XMLHttpRequest) dengan FormData
    // ============================================
    btnSubmit.addEventListener('click', function() {
        // Validasi akhir sebelum kirim
        if (!selectedStatus) {
            alert('Harap pilih status kehadiran!');
            return;
        }
        if (!capturedPhoto || !locationCaptured) {
            alert('Harap ambil foto dan pastikan lokasi berhasil diambil!');
            return;
        }

        var btn = this; // Referensi ke tombol submit
        btn.disabled = true; // Disable saat proses
        btn.textContent = 'Menyimpan...'; // Ubah teks tombol

        try {
            // Bangun FormData untuk pengiriman multipart (karena ada file foto)
            var fd = new FormData();
            fd.append('status', selectedStatus);                                    // Status kehadiran
            fd.append('alamat', document.getElementById('input-alamat').value);       // Alamat dari reverse geocoding
            fd.append('catatan', inputCatatan.value.trim());                       // Catatan (bisa kosong)

            // Konversi data URL foto ke Blob dan append ke FormData
            var blob = dataURItoBlob(capturedPhoto);
            fd.append('foto', blob, 'absensi_' + selectedStatus + '_' + Date.now() + '.jpg'); // Nama file unik

            fd.append('latitude', document.getElementById('input-lat').value);       // Latitude
            fd.append('longitude', document.getElementById('input-lng').value);     // Longitude

            // Kirim via XMLHttpRequest (bukan fetch, untuk kompatibilitas)
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route("mahasiswa.absensi.store") }}', true); // URL ke controller store
            xhr.timeout = 30000; // Timeout 30 detik
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken.content); // Set CSRF header
            xhr.setRequestHeader('Accept', 'application/json'); // Minta response JSON

            // CALLBACK: Request selesai
            xhr.onload = function() {
                if (xhr.status === 200) { // Sukses (HTTP 200)
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.success) {
                            window.location.href = res.redirect; // Redirect ke halaman riwayat
                            return;
                        } else {
                            // Tampilkan error dari server
                            var errMsg = res.message || 'Gagal menyimpan.';
                            if (res.errors && res.errors.length > 0) {
                                errMsg += '\n' + res.errors.join('\n');
                            }
                            alert(errMsg);
                        }
                    } catch(e) {
                        console.error('JSON parse error:', e);
                        alert('Gagal memproses respons server.');
                    }
                } else if (xhr.status === 422) { // Validasi gagal (HTTP 422)
                    try {
                        var res = JSON.parse(xhr.responseText);
                        var errMsg = res.message || 'Validasi gagal.';
                        if (res.errors && res.errors.length > 0) {
                            errMsg += '\n' + res.errors.join('\n');
                        }
                        console.error('Validation errors:', res.errors);
                        alert(errMsg);
                    } catch(e) {
                        alert('Validasi gagal.');
                    }
                } else if (xhr.status === 419) { // CSRF token expired (HTTP 419)
                    alert('Sesi telah berakhir. Silakan refresh halaman dan coba lagi.');
                } else { // Error lainnya
                    alert('Terjadi kesalahan (HTTP ' + xhr.status + '). Coba lagi.');
                }
                // Kembalikan tombol ke keadaan semula
                btn.disabled = false;
                btn.textContent = 'Simpan Absensi';
            };

            // CALLBACK: Gagal terhubung ke server
            xhr.onerror = function() {
                alert('Gagal terhubung ke server.');
                btn.disabled = false;
                btn.textContent = 'Simpan Absensi';
            };

            // CALLBACK: Request timeout
            xhr.ontimeout = function() {
                alert('Request timeout. Coba lagi.');
                btn.disabled = false;
                btn.textContent = 'Simpan Absensi';
            };

            xhr.send(fd); // Kirim FormData
        } catch(e) { // Tangkap error tak terduga
            console.error('Submit error:', e);
            alert('Terjadi error: ' + e.message);
            btn.disabled = false;
            btn.textContent = 'Simpan Absensi';
        }
    });

    // ============================================
    // FUNGSI UTILITAS: Konversi Data URI ke Blob
    // Diperlukan karena FormData butuh Blob/File, bukan base64 string
    // ============================================
    function dataURItoBlob(uri) {
        var bs = atob(uri.split(',')[1]); // Decode base64 ke binary string
        var ms = uri.split(',')[0].split(':')[1].split(';')[0]; // Ekstrak MIME type
        var ab = new ArrayBuffer(bs.length); // Buat ArrayBuffer
        var ia = new Uint8Array(ab); // Buat typed array view
        for (var i = 0; i < bs.length; i++) ia[i] = bs.charCodeAt(i); // Isi byte per byte
        return new Blob([ab], { type: ms }); // Buat Blob dari ArrayBuffer
    }
});
</script>
@endsection 