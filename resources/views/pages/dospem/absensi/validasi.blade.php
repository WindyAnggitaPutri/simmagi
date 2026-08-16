@extends('layouts.app') {{-- Extends layout utama aplikasi --}}

@section('content') {{-- Mulai section content --}}
<div class="max-w-4xl mx-auto px-4 md:px-6 py-6"> {{-- Container dengan max width lebih kecil untuk fokus --}}

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-4"> {{-- Navigasi breadcrumb --}}
        <a href="{{ route('dospem.absensi.index') }}" class="hover:text-yellow-600 transition">Absensi</a> {{-- Link ke daftar --}}
      
        <a href="{{ route('dospem.absensi.detail', ['id_mahasiswa' => $absensi->id_mahasiswa]) }}" class="hover:text-yellow-600 transition">
            {{ $absensi->mahasiswa->nama ?? 'Mahasiswa' }} {{-- Nama mahasiswa, fallback jika null --}}
        </a>
       
        <span class="text-gray-800 font-medium">Validasi</span> {{-- Halaman saat ini --}}
    </nav>

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Validasi Absensi</h1> {{-- Judul halaman --}}
        <p class="text-sm text-gray-500 mt-1">Periksa detail absensi dan berikan keputusan validasi.</p> {{-- Deskripsi --}}
    </div>

    <!-- Info Mahasiswa -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 mb-4"> {{-- Card informasi mahasiswa --}}
        <h2 class="font-semibold text-gray-700 mb-3">Informasi Mahasiswa</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm"> {{-- Grid 2 kolom --}}
            <div>
                <span class="text-gray-500">Nama</span>
                <p class="font-medium text-gray-800">{{ $absensi->mahasiswa->nama ?? '-' }}</p> {{-- Nama mahasiswa --}}
            </div>
            <div>
                <span class="text-gray-500">NPM</span>
                <p class="font-medium text-gray-800 font-mono">{{ $absensi->mahasiswa->npm ?? '-' }}</p> {{-- NPM --}}
            </div>
            <div>
                <span class="text-gray-500">Perusahaan</span>
                <p class="font-medium text-gray-800">{{ $perusahaan->nama_perusahaan ?? '-' }}</p> {{-- Nama perusahaan --}}
            </div>
            <div>
                <span class="text-gray-500">Alamat Perusahaan</span>
                <p class="font-medium text-gray-800">{{ $perusahaan->alamat ?? '-' }}</p> {{-- Alamat perusahaan --}}
            </div>
        </div>
    </div>

    <!-- Detail Absensi -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 mb-4"> {{-- Card detail absensi --}}
        <h2 class="font-semibold text-gray-700 mb-3">Detail Absensi</h2>
        
        {{-- Grid 4 kolom untuk info utama --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm mb-4">
            <div>
                <span class="text-gray-500">Tanggal</span>
                <p class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($absensi->tanggal)->format('d F Y') }}</p> {{-- Format tanggal Indonesia --}}
            </div>
            <div>
                <span class="text-gray-500">Jam Absen</span>
                <p class="font-medium text-gray-800 font-mono">{{ $absensi->jam_absen }}</p> {{-- Jam absen --}}
            </div>
            <div>
                <span class="text-gray-500">Status Kehadiran</span>
                <p class="mt-1">
                    @php
                        // Konfigurasi warna badge status kehadiran
                        $statusConfig = [
                            'hadir' => ['bg-green-100 text-green-700', 'Hadir'],
                            'sakit' => ['bg-yellow-100 text-yellow-700', 'Sakit'],
                            'izin'  => ['bg-blue-100 text-blue-700', 'Izin'],
                            'alpa'  => ['bg-red-100 text-red-700', 'Alpa'],
                        ];
                        $sc = $statusConfig[$absensi->status] ?? ['bg-gray-100 text-gray-700', $absensi->status];
                    @endphp
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $sc[0] }}">{{ $sc[1] }}</span>
                </p>
            </div>
            <div>
                <span class="text-gray-500">Status Validasi</span>
                <p class="mt-1">
                    @php
                        // Konfigurasi warna badge status validasi
                        $validasiConfig = [
                            'pending'  => ['bg-orange-100 text-orange-700', 'Pending'],
                            'diterima' => ['bg-green-100 text-green-700', 'Diterima'],
                            'ditolak'  => ['bg-red-100 text-red-700', 'Ditolak'],
                        ];
                        $vc = $validasiConfig[$absensi->status_validasi] ?? ['bg-gray-100 text-gray-700', $absensi->status_validasi];
                    @endphp
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $vc[0] }}">{{ $vc[1] }}</span>
                </p>
            </div>
        </div>

        <!-- Jarak -->
        @if($absensi->jarak_meter !== null) {{-- Hanya tampilkan jika ada data jarak --}}
            @php $jarakMelebihi = $absensi->jarak_meter > 100; @endphp {{-- Cek apakah melebihi 100 meter --}}
            
            {{-- Box informasi jarak --}}
            <div class="rounded-lg p-3 mb-4 {{ $jarakMelebihi ? 'bg-red-50 border border-red-300' : 'bg-gray-50' }}">
                <div class="flex items-center gap-2 text-sm">
                    @if($jarakMelebihi) {{-- Jika jarak melebihi batas --}}
                      
                        <span class="text-red-700 font-semibold">Jarak dari lokasi perusahaan: {{ number_format($absensi->jarak_meter, 0) }} meter</span>
                    @else {{-- Jika jarak dalam batas --}}
                       
                        <span class="text-gray-600">Jarak dari lokasi perusahaan: <strong>{{ number_format($absensi->jarak_meter, 0) }} meter</strong></span>
                    @endif
                </div>
            </div>

            @if($jarakMelebihi) {{-- Warning jika jarak melebihi batas --}}
            <div class="bg-red-50 border border-red-300 rounded-lg p-4 mb-4 flex items-start gap-3">
              
                <div>
                    <p class="font-bold text-red-700">Absensi Tidak Berlaku</p>
                    <p class="text-sm text-red-600 mt-1">Jarak melebihi 100 meter. Disarankan untuk <strong>menolak</strong> absensi ini.</p> {{-- Rekomendasi --}}
                </div>
            </div>
            @endif
        @endif

        <!-- Lokasi Mahasiswa -->
        <div class="mb-4">
            <span class="text-sm text-gray-500">Lokasi Absensi Mahasiswa</span>
            <p class="text-sm text-gray-700 mt-1">{{ $absensi->alamat ?: 'Tidak ada data alamat' }}</p> {{-- Alamat, fallback jika kosong --}}
            @if($absensi->latitude && $absensi->longitude) {{-- Tampilkan koordinat jika ada --}}
            <p class="text-xs text-gray-400 mt-1 font-mono">
                Lat: {{ $absensi->latitude }}, Lng: {{ $absensi->longitude }} {{-- Koordinat GPS --}}
            </p>
            @endif
        </div>

        @if($absensi->catatan) {{-- Tampilkan catatan jika ada --}}
        <div class="mb-4">
            <span class="text-sm text-gray-500">Catatan dari Mahasiswa</span>
            <p class="text-sm text-gray-700 mt-1 bg-gray-50 rounded-lg p-3">{{ $absensi->catatan }}</p> {{-- Catatan dalam box --}}
        </div>
        @endif
    </div>

    <!-- Foto Absensi -->
    @if($absensi->foto) {{-- Hanya tampilkan jika ada foto --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 mb-4">
        <h2 class="font-semibold text-gray-700 mb-3">Bukti Foto Absensi</h2>
        <div class="flex justify-center">
            <img src="{{ Storage::url($absensi->foto) }}" {{-- URL foto dari storage --}}
                 alt="Foto absensi {{ $absensi->mahasiswa->nama ?? '' }}" 
                 class="max-w-md w-full rounded-lg border border-gray-200 shadow-sm"
                 onclick="window.open(this.src, '_blank')" {{-- Klik untuk buka di tab baru --}}
                 style="cursor: zoom-in;"> {{-- Cursor zoom untuk indikasi bisa diklik --}}
        </div>
        <p class="text-xs text-gray-400 text-center mt-2">Klik foto untuk memperbesar</p> {{-- Petunjuk --}}
    </div>
    @endif

    <!-- ========================================== -->
    <!-- FORM VALIDASI - HANYA JIKA STATUS PENDING -->
    <!-- ========================================== -->
    @if($absensi->status_validasi === 'pending') {{-- Form hanya muncul jika belum divalidasi --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 mb-4">
        <h2 class="font-semibold text-gray-700 mb-4">Proses Validasi</h2>

        {{-- Form untuk memproses validasi --}}
        <form id="form-validasi" 
              action="{{ route('dospem.absensi.proses-validasi', ['id_absensi' => $absensi->id_absensi]) }}" 
              method="POST">
            @csrf {{-- Token CSRF untuk keamanan --}}
            @method('PUT') {{-- Override method menjadi PUT --}}

            {{-- Input catatan validasi dari dosen --}}
            <div class="mb-4">
                <label for="catatan_validasi" class="block text-sm font-medium text-gray-700 mb-1">
                    Catatan Validasi <span class="text-gray-400 font-normal">(opsional)</span>
                </label>
                <textarea 
                    id="catatan_validasi" 
                    name="catatan_validasi" 
                    rows="3" 
                    maxlength="1000"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 resize-none"
                    placeholder="Tambahkan catatan jika diperlukan...">{{ old('catatan_validasi') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Maksimal 1000 karakter</p>
            </div>

            {{-- Hidden input untuk menyimpan status yang dipilih --}}
            <input type="hidden" name="status_validasi" id="status_validasi_input" value="">

            {{-- Tombol aksi --}}
            <div class="flex flex-col sm:flex-row gap-3">
                
                {{-- Tombol Setujui --}}
                <button type="button" 
                        id="btn-setujui"
                        class="flex-1 inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-4 rounded-lg transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Icon check --}}
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Setujui
                </button>
                
                {{-- Tombol Tolak --}}
                <button type="button" 
                        id="btn-tolak"
                        class="flex-1 inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-4 rounded-lg transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Icon X --}}
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Tolak
                </button>
                
                {{-- Tombol Batal --}}
                <a href="{{ route('dospem.absensi.detail', ['id_mahasiswa' => $absensi->id_mahasiswa]) }}"
                   class="inline-flex items-center justify-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2.5 px-4 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Icon arrow left --}}
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Batal
                </a>
            </div>
        </form>
    </div>

    <!-- Modal Konfirmasi -->
    {{-- Modal popup untuk konfirmasi sebelum submit --}}
    <div id="modal-konfirmasi" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background: rgba(0,0,0,0.5);">
        <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 transform transition"> {{-- Content modal --}}
            
            {{-- Bagian icon dan teks --}}
            <div class="text-center mb-4">
                <div id="modal-icon" class="w-16 h-16 rounded-full mx-auto mb-3 flex items-center justify-center"></div> {{-- Container icon, diisi via JS --}}
                <h3 id="modal-title" class="text-lg font-bold text-gray-800"></h3> {{-- Judul modal --}}
                <p id="modal-desc" class="text-sm text-gray-500 mt-1"></p> {{-- Deskripsi modal --}}
            </div>
            
            {{-- Tombol modal --}}
            <div class="flex gap-3">
                <button type="button" 
                        id="modal-batal"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2.5 rounded-lg transition">
                    Batal
                </button>
                <button type="button" 
                        id="modal-konfirm-btn"
                        class="flex-1 font-medium py-2.5 rounded-lg transition text-white">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>

    <!-- SCRIPT DI DALAM SECTION! -->
    {{-- JavaScript untuk menghandle interaksi form dan modal --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() { {{-- Jalankan setelah DOM selesai dimuat --}}
        console.log('Script validasi dimuat'); {{-- Debug log --}}
        
        let pendingStatus = ''; // Variabel untuk menyimpan status sementara
        
        // Ambil referensi ke elemen-elemen DOM
        const formValidasi = document.getElementById('form-validasi');
        const statusInput = document.getElementById('status_validasi_input');
        const modal = document.getElementById('modal-konfirmasi');
        const modalIcon = document.getElementById('modal-icon');
        const modalTitle = document.getElementById('modal-title');
        const modalDesc = document.getElementById('modal-desc');
        const modalBtn = document.getElementById('modal-konfirm-btn');
        const modalBatal = document.getElementById('modal-batal');
        const btnSetujui = document.getElementById('btn-setujui');
        const btnTolak = document.getElementById('btn-tolak');

        // Debug: cek apakah elemen ditemukan
        console.log('formValidasi:', formValidasi);
        console.log('btnSetujui:', btnSetujui);
        console.log('btnTolak:', btnTolak);
        console.log('modalBtn:', modalBtn);

        // Validasi: jika elemen penting tidak ditemukan, hentikan script
        if (!btnSetujui || !btnTolak || !modalBtn) {
            console.error('Elemen tidak ditemukan!');
            return;
        }

        /**
         * Fungsi untuk membuka modal konfirmasi
         * @param {string} status - 'diterima' atau 'ditolak'
         */
        function openModal(status) {
            console.log('openModal dipanggil:', status);
            pendingStatus = status; // Simpan status yang dipilih
            
            if (status === 'diterima') { // Konfigurasi untuk setujui
                modalIcon.className = 'w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3';
                modalIcon.innerHTML = '<svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                modalTitle.textContent = 'Setujui Absensi?';
                modalDesc.textContent = 'Anda akan menyetujui absensi ini. Tindakan ini tidak dapat dibatalkan.';
                modalBtn.className = 'flex-1 font-medium py-2.5 rounded-lg transition text-white bg-green-600 hover:bg-green-700';
            } else { // Konfigurasi untuk tolak
                modalIcon.className = 'w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3';
                modalIcon.innerHTML = '<svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                modalTitle.textContent = 'Tolak Absensi?';
                modalDesc.textContent = 'Anda akan menolak absensi ini. Tindakan ini tidak dapat dibatalkan.';
                modalBtn.className = 'flex-1 font-medium py-2.5 rounded-lg transition text-white bg-red-600 hover:bg-red-700';
            }

            // Tampilkan modal dengan mengubah class
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        /**
         * Fungsi untuk menutup modal
         */
        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            pendingStatus = ''; // Reset status
        }

        // Event listener: Tombol Setujui diklik
        btnSetujui.addEventListener('click', function(e) {
            e.preventDefault(); // Mencegah default behavior
            console.log('Tombol Setujui diklik');
            openModal('diterima'); // Buka modal dengan status diterima
        });

        // Event listener: Tombol Tolak diklik
        btnTolak.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Tombol Tolak diklik');
            openModal('ditolak'); // Buka modal dengan status ditolak
        });

        // Event listener: Tombol konfirmasi di modal diklik
        modalBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Tombol Konfirmasi diklik, status:', pendingStatus);
            
            if (pendingStatus) { // Jika status sudah diset
                statusInput.value = pendingStatus; // Set nilai hidden input
                console.log('Menyimpan status:', statusInput.value);
                console.log('Mengirim form ke:', formValidasi.action);
                formValidasi.submit(); // Submit form
            }
        });

        // Event listener: Tombol batal di modal diklik
        modalBatal.addEventListener('click', function(e) {
            e.preventDefault();
            closeModal(); // Tutup modal
        });

        // Event listener: Klik di luar modal untuk menutup
        modal.addEventListener('click', function(e) {
            if (e.target === modal) { // Jika yang diklik adalah backdrop modal
                closeModal();
            }
        });

        console.log('Script validasi siap!'); // Debug log
    });
    </script>

    @else
    <!-- SUDAH DIVALIDASI -->
    {{-- Tampilan jika absensi sudah divalidasi (bukan pending lagi) --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 text-center">
        @if($absensi->status_validasi === 'diterima') {{-- Jika sudah disetujui --}}
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Icon check --}}
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <p class="font-semibold text-green-700 text-lg">Absensi Sudah Disetujui</p>
        @else {{-- Jika sudah ditolak --}}
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                
            </div>
            <p class="font-semibold text-red-700 text-lg">Absensi Sudah Ditolak</p>
        @endif

        {{-- Tombol kembali ke detail --}}
        <a href="{{ route('dospem.absensi.detail', ['id_mahasiswa' => $absensi->id_mahasiswa]) }}"
           class="inline-flex items-center gap-2 mt-4 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg transition text-sm">
          
            Kembali ke Detail
        </a>
    </div>
    @endif

</div>
@endsection 