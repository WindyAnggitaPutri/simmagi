<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Penilaian - SIMMAGI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .radio-card input[type="radio"]:checked + div {
            background-color: #eab308;
            color: white;
            border-color: #ca8a04;
        }

        /* =========== TTD Preview Popup Styles =========== */
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
<body class="bg-gray-50 min-h-screen py-10 px-4">
    <div class="max-w-4xl mx-auto">
        
        {{-- Header --}}
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Form Penilaian Magang</h1>
            <p class="text-gray-500 mt-1">Perusahaan: <span class="font-semibold text-gray-700">{{ $tokenData->perusahaan->nama_perusahaan }}</span></p>
            
            @if($tokenData->type == 'penilaian' && $tokenData->mahasiswa)
            <p class="text-gray-500">Mahasiswa: <span class="font-semibold text-gray-700">{{ $tokenData->mahasiswa->nama }} ({{ $tokenData->mahasiswa->npm }})</span></p>
            @endif
        </div>

        {{-- STEPPER INDICATOR --}}
        <div class="flex items-center justify-center mb-8">
            <div class="flex items-center">
                <div id="circle1" class="w-8 h-8 bg-yellow-500 text-white rounded-full flex justify-center items-center text-sm font-bold">1</div>
                <span id="text1" class="ml-2 text-sm font-medium text-gray-700">Data Pengisi</span>
            </div>
            <div id="line1" class="w-10 h-0.5 bg-gray-300 mx-3"></div>
            <div class="flex items-center">
                <div id="circle2" class="w-8 h-8 bg-gray-300 text-white rounded-full flex justify-center items-center text-sm font-bold">2</div>
                <span id="text2" class="ml-2 text-sm font-medium text-gray-400">
                    @if($tokenData->type == 'angket') Angket Perusahaan @else Penilaian Mahasiswa @endif
                </span>
            </div>
        </div>

        <form id="penilaianForm" method="POST" action="{{ route('perusahaan.form.submit', $tokenData->token) }}" enctype="multipart/form-data">
            @csrf
            @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                <p class="font-bold mb-1">Terdapat kesalahan pengisian:</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- STEP 1: DATA PENGISI --}}
            <div id="step1" class="step-section">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Step 1: Data Pengisi</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pengisi <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_pengisi" value="{{ old('nama_pengisi') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-yellow-500 focus:border-yellow-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan <span class="text-red-500">*</span></label>
                            <input type="text" name="jabatan_pengisi" value="{{ old('jabatan_pengisi') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-yellow-500 focus:border-yellow-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email_pengisi" value="{{ old('email_pengisi') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-yellow-500 focus:border-yellow-500" required>
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="pernyataan_keaslian" value="1" class="w-4 h-4 text-yellow-500 border-gray-300 rounded focus:ring-yellow-500" required>
                                <span class="text-sm text-gray-700">Saya menyatakan isian ini asli <span class="text-red-500">*</span></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="nextStep(1)" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg font-medium transition">
                        Selanjutnya →
                    </button>
                </div>
            </div>

            {{-- STEP 2: DYNAMIC BERDASARKAN TYPE --}}
            <div id="step2" class="step-section" style="display: none;">
                
                @if($tokenData->type == 'angket')
                {{-- ANGKET PERUSAHAAN --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Step 2: Angket Perusahaan</h2>
                    <p class="text-sm text-gray-500 mb-6">Pilih salah satu: <span class="font-medium text-gray-700">Sangat Baik (4) | Baik (3) | Cukup (2) | Kurang (1)</span></p>
                    
                    <div class="space-y-6">
                        @php
                            $angketFields = [
                                'integritas_motivasi' => 'Bagaimana integritas dan motivasi dari mahasiswa Politeknik Negeri Cilacap dalam melaksanakan Magang?',
                                'etika_sopan_santun' => 'Bagaimana pengamatan Bapak/Ibu terhadap mahasiswa Politeknik Negeri Cilacap dalam melaksanakan magang dilihat dari segi etika/sopan santun?',
                                'kompetensi_keahlian' => 'Bagaimana pendapat Bapak/Ibu terhadap kompetensi dan keahlian mahasiswa Politeknik Negeri Cilacap pada saat magang?',
                                'kedisiplinan' => 'Bagaimana pendapat Bapak/Ibu kedisiplinan mahasiswa Politeknik Negeri Cilacap?',
                                'respon_kecepatan_kerja' => 'Bagaimana respon dan kecepatan pengerjaan dan mahasiswa pada saat mendapat tugas dari perusahaan tempat Magang?',
                                'adaptasi_komunikasi' => 'Bagaimana cara beradaptasi dan komunikasi dari mahasiswa Politeknik Negeri Cilacap?',
                                'kerjasama_tim' => 'Bagaimana kemampuan kerjasama dalam tim dari mahasiswa Politeknik Negeri Cilacap?',
                                'pengembangan_diri' => 'Bagaimana pengembangan diri dan kemauan belajar dari mahasiswa Politeknik Negeri Cilacap?',
                                'kemampuan_bahasa_inggris' => 'Bagaimana kemampuan bahasa asing (bahasa inggris) dari mahasiswa Politeknik Negeri Cilacap?',
                                'kemampuan_teknologi_informasi' => 'Bagaimana kemampuan dalam penggunaan teknologi informasi dari mahasiswa Politeknik Negeri Cilacap?',
                            ];
                            $angketOptions = [4 => 'Sangat Baik', 3 => 'Baik', 2 => 'Cukup', 1 => 'Kurang'];
                        @endphp

                        @foreach ($angketFields as $field => $label)
                        <div class="border-b border-gray-100 pb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-3">{{ $label }} <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-4 gap-2 radio-card">
                                @foreach ($angketOptions as $val => $textLabel)
                                <label class="text-center">
                                    <input type="radio" name="{{ $field }}" value="{{ $val }}" class="hidden" required>
                                    <div class="border border-gray-300 rounded-lg py-2 px-1 text-xs md:text-sm cursor-pointer hover:bg-yellow-50 transition leading-tight">
                                        {{ $textLabel }}
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                            <textarea name="catatan_angket" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                        </div>
                    </div>
                </div>

                @else
                {{-- PENILAIAN MAHASISWA --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Step 2: Penilaian Mahasiswa</h2>
                    <p class="text-sm text-gray-500 mb-6">Masukkan nilai <span class="font-medium text-gray-700">1 sampai 100</span> untuk setiap aspek penilaian.</p>
                    
                    <div class="space-y-4">
                        @php
                            $penilaianGroups = [
                                ['number' => '1', 'title' => 'Kedisiplinan', 'items' => ['kedisiplinan_mhs' => 'Kedisiplinan']],
                                ['number' => '2', 'title' => 'Kemampuan Beradaptasi dengan Lingkungan', 'items' => ['adaptasi_mhs' => 'Kemampuan Beradaptasi dengan Lingkungan']],
                                ['number' => '3', 'title' => 'Tanggung Jawab terhadap Tugas', 'items' => ['tanggung_jawab' => 'Tanggung Jawab terhadap Tugas']],
                                ['number' => '4', 'title' => 'Kemandirian', 'items' => ['kemandirian' => 'Kemandirian']],
                                ['number' => '5', 'title' => 'Inisiatif dan Kreativitas', 'items' => ['inisiatif_kreativitas' => 'Inisiatif dan Kreativitas']],
                                ['number' => '6', 'title' => 'Keterampilan dalam:', 'items' => ['kesesuaian_instruksi' => 'Kesesuaian dalam Instruksi', 'kualitas_pekerjaan' => 'Kualitas Hasil Pekerjaan', 'ketepatan_waktu' => 'Ketepatan Waktu', 'problem_solving' => 'Kemampuan Memecahkan Masalah']],
                                ['number' => '7', 'title' => 'Komunikasi:', 'items' => ['teamwork_mhs' => 'Kerjasama dengan Teamwork/Kelompok', 'hubungan_atasan' => 'Hubungan dengan Atasan', 'hubungan_rekan' => 'Hubungan dengan Rekan Kerja', 'hubungan_relasi' => 'Hubungan dengan Relasi']],
                                ['number' => '8', 'title' => 'Sikap Potensial:', 'items' => ['sikap_kerja' => 'Sikap Menghadapi Pekerjaan', 'disiplin_kerja' => 'Disiplin Kerja', 'loyalitas' => 'Loyalitas', 'motivasi' => 'Semangat dan Motivasi Kerja', 'penampilan' => 'Penampilan']]
                            ];
                            $abjad = ['a', 'b', 'c', 'd', 'e'];
                        @endphp

                        @foreach ($penilaianGroups as $group)
                            <div class="border border-gray-200 rounded-lg bg-gray-50/70 overflow-hidden">
                                <div class="bg-white px-5 py-3 border-b border-gray-200 font-semibold text-gray-800 flex justify-between items-center">
                                    <span>{{ $group['number'] }}. {{ $group['title'] }}</span>
                                    @if(count($group['items']) == 1)
                                        @php $field = array_keys($group['items'])[0]; @endphp
                                        <input type="number" name="{{ $field }}" min="1" max="100" required class="w-24 border border-gray-300 rounded-lg px-3 py-2 text-sm text-center focus:ring-yellow-500 focus:border-yellow-500 bg-white shadow-sm penilaian-input" placeholder="1-100">
                                    @endif
                                </div>
                                @if(count($group['items']) > 1)
                                <div class="p-5 space-y-3">
                                    @foreach ($group['items'] as $field => $label)
                                    <div class="flex items-center justify-between gap-4">
                                        <label class="text-sm text-gray-700 flex-1">{{ $abjad[$loop->index] }}. {{ $label }} <span class="text-red-500">*</span></label>
                                        <input type="number" name="{{ $field }}" min="1" max="100" required class="w-24 border border-gray-300 rounded-lg px-3 py-2 text-sm text-center focus:ring-yellow-500 focus:border-yellow-500 bg-white shadow-sm penilaian-input" placeholder="1-100">
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        @endforeach

                        {{-- TOTAL NILAI & RATA-RATA --}}
                        <div class="border border-gray-200 rounded-lg bg-white p-4 mt-6">
                            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                                <div class="flex gap-8">
                                    <div>
                                        <span class="text-sm font-semibold text-gray-700">TOTAL NILAI</span>
                                        <div id="totalNilai" class="text-xl font-bold text-gray-900">0</div>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold text-gray-700">RATA-RATA</span>
                                        <div id="rataRata" class="text-xl font-bold text-gray-900">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Penilaian (Opsional)</label>
                            <textarea name="catatan_penilaian" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                        </div>

                        {{-- ✅ UPLOAD TANDA TANGAN PEMBIMBING LAPANGAN (SUDAH DIPERBAIKI) --}}
                        <div class="border border-gray-200 rounded-lg bg-white p-5 mt-4">
                            <h3 class="text-sm font-semibold text-gray-800 mb-1">Tanda Tangan Pembimbing Lapangan <span class="text-red-500">*</span></h3>
                            <p class="text-xs text-gray-500 mb-3">Upload tanda tangan digital Anda. Background akan dibersihkan secara otomatis.</p>
                            
                            <input type="file" name="ttd_digital" id="ttdInput" accept="image/*" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
                                required>

                            {{-- Info file + tombol preview --}}
                            <div id="fileInfoContainer" class="mt-2 hidden">
                                <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                                    <i class="fas fa-image text-gray-400"></i>
                                    <a href="javascript:void(0)" id="fileLink"
                                       class="file-link text-sm text-orange-600 font-medium cursor-pointer truncate"
                                       onclick="openPreviewPopup()">
                                        <span id="fileNameDisplay"></span>
                                    </a>
                                    <button type="button" onclick="removeTtd()"
                                            class="ml-auto text-gray-400 hover:text-red-500 transition-colors flex-shrink-0 text-sm font-bold"
                                            title="Hapus file">
                                        x
                                    </button>
                                </div>
                                <p class="text-xs text-gray-400 mt-1 ml-1">Klik nama file untuk preview & bersihkan background</p>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1">Format: JPG/PNG, maks 2MB</p>
                        </div>
                    </div>
                </div>
                @endif

                <div class="flex justify-between">
                    <button type="button" onclick="prevStep(2)" class="border border-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-50 transition">
                        ← Sebelumnya
                    </button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-8 py-2 rounded-lg font-semibold transition shadow-md">
                        Kirim Formulir
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- ============================================================ -->
    <!-- POPUP PREVIEW TTD — bersih otomatis via OpenCV Backend       -->
    <!-- ============================================================ -->
    <div id="previewPopup" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="popup-backdrop absolute inset-0" onclick="closePreviewPopup()"></div>
        <div id="popupContent" class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[92vh] overflow-hidden z-10 flex flex-col">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex-shrink-0">
                <h3 class="text-base font-semibold text-gray-800">
                    <i class="fas fa-signature mr-2 text-orange-500"></i>Preview TTD Digital
                </h3>
                <button onclick="closePreviewPopup()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200 transition-colors text-gray-500 hover:text-gray-800 text-lg font-bold" title="Tutup">x</button>
            </div>
            <div class="p-5 overflow-auto flex-1 relative" id="previewBody">
                <div id="processingOverlay" class="absolute inset-0 bg-white/90 flex items-center justify-center z-20">
                    <div class="text-center">
                        <i class="fas fa-circle-notch fa-spin text-3xl text-orange-500"></i>
                        <p class="text-sm text-gray-500 mt-2 font-medium">Menghapus background & memperbaiki tinta...</p>
                    </div>
                </div>
                <div id="previewArea" class="flex items-center justify-center rounded-lg p-4 checkerboard-bg" style="min-height: 200px;">
                    <img id="previewImage" src="" alt="Preview TTD" class="max-w-full max-h-[70vh] object-contain rounded shadow-md" style="opacity:0">
                </div>
            </div>
        </div>
    </div>
    <canvas id="processCanvas" class="hidden"></canvas>

    {{-- JAVASCRIPT (SUDAH DIGABUNG DAN DIPERBAIKI KONFLIK ID-NYA) --}}
    <script>
        // ================================================================
        // 1. LOGIC STEPPER & HITUNG NILAI
        // ================================================================
        let currentStep = 1;
        const totalSteps = 2;

        function showStep(step) {
            document.querySelectorAll('.step-section').forEach(el => el.style.display = 'none');
            document.getElementById('step' + step).style.display = 'block';

            for(let i = 1; i <= totalSteps; i++) {
                let circle = document.getElementById('circle' + i);
                let text = document.getElementById('text' + i);
                let line = document.getElementById('line' + i);

                if (i < step) {
                    circle.className = 'w-8 h-8 bg-green-500 text-white rounded-full flex justify-center items-center text-sm font-bold';
                    if(text) text.className = 'ml-2 text-sm font-medium text-green-600';
                    if(line) line.className = 'w-10 h-0.5 bg-green-500 mx-3';
                } else if (i === step) {
                    circle.className = 'w-8 h-8 bg-yellow-500 text-white rounded-full flex justify-center items-center text-sm font-bold';
                    if(text) text.className = 'ml-2 text-sm font-medium text-gray-700';
                } else {
                    circle.className = 'w-8 h-8 bg-gray-300 text-white rounded-full flex justify-center items-center text-sm font-bold';
                    if(text) text.className = 'ml-2 text-sm font-medium text-gray-400';
                    if(line) line.className = 'w-10 h-0.5 bg-gray-300 mx-3';
                }
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function nextStep(current) {
            let currentSection = document.getElementById('step' + current);
            let inputs = currentSection.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (!input.checkValidity()) {
                    input.reportValidity();
                    isValid = false;
                }
            });

            if (isValid) {
                currentStep = current + 1;
                showStep(currentStep);
            }
        }

        function prevStep(current) {
            currentStep = current - 1;
            showStep(currentStep);
        }

        showStep(currentStep);

        // Auto-Sum Total & Rata-rata
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.penilaian-input');
            const totalDisplay = document.getElementById('totalNilai');
            const rataRataDisplay = document.getElementById('rataRata');
            const jumlahItem = inputs.length;

            if (jumlahItem > 0) {
                inputs.forEach(input => {
                    input.addEventListener('input', calculateTotal);
                });

                function calculateTotal() {
                    let total = 0;
                    let filledCount = 0;
                    inputs.forEach(input => {
                        let val = parseInt(input.value);
                        if (!isNaN(val) && val >= 1 && val <= 100) {
                            total += val;
                            filledCount++;
                        }
                    });
                    totalDisplay.textContent = total;
                    if(filledCount > 0) {
                        let avg = total / jumlahItem;
                        rataRataDisplay.textContent = avg.toFixed(2);
                    } else {
                        rataRataDisplay.textContent = '0';
                    }
                }
            }
        });

        // ================================================================
        // 2. LOGIC PREVIEW & HAPUS BACKGROUND TTD (OPEN CV)
        // ================================================================
        var originalFileDataUrl = null;
        var processedFileDataUrl = null;
        var originalFileName = "";

        // PENTING: Pakai ID yang sama persis dengan yang di HTML (ttdInput)
        var fileInput = document.getElementById('ttdInput');
        var fileInfoContainer = document.getElementById('fileInfoContainer');
        var fileNameDisplay = document.getElementById('fileNameDisplay');

        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    // Cek ukuran file 2MB
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file terlalu besar! Maksimal 2MB.');
                        this.value = '';
                        return;
                    }

                    originalFileName = file.name;
                    processedFileDataUrl = null; // Reset hasil proses lama

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
        }

        function removeTtd() {
            if (fileInput) fileInput.value = '';
            clearFileInfo();
        }

        function clearFileInfo() {
            originalFileDataUrl = null;
            processedFileDataUrl = null;
            originalFileName = "";
            if (fileInfoContainer) fileInfoContainer.classList.add('hidden');
            if (fileNameDisplay) fileNameDisplay.textContent = '';
        }

        var previewPopup = document.getElementById('previewPopup');
        var popupContent = document.getElementById('popupContent');
        var previewImage = document.getElementById('previewImage');
        var processingOverlay = document.getElementById('processingOverlay');

        function openPreviewPopup() {
            if (!originalFileDataUrl) return;

            processingOverlay.classList.remove('hidden');
            previewImage.style.opacity = '0';

            // Hapus warning lama kalau ada
            var oldWarning = document.querySelector('#previewBody > .bg-yellow-50');
            if (oldWarning) oldWarning.remove();

            previewPopup.classList.remove('hidden');
            previewPopup.classList.add('flex');
            popupContent.classList.remove('popup-exit');
            popupContent.classList.add('popup-enter');
            document.body.style.overflow = 'hidden';

            // Kalau sudah pernah diproses, tampilkan langsung
            if (processedFileDataUrl) {
                previewImage.src = processedFileDataUrl;
                previewImage.style.opacity = '1';
                processingOverlay.classList.add('hidden');
                return;
            }

            // Kalau belum, proses dulu
            var img = new Image();
            img.onload = function() {
                processAndApply(img);
            };
            img.src = originalFileDataUrl;
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

        var TTD_SERVICE_URL = 'http://localhost:5001/process';

        function processAndApply(imgObj) {
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

                    applyToForm(data.image);
                } else {
                    throw new Error(data.message || 'Gagal memproses');
                }
            })
            .catch(function(err) {
                console.error('[TTD Service Error]', err);
                processingOverlay.classList.add('hidden');

                // Fallback: tampilkan gambar asli + warning
                previewImage.src = originalFileDataUrl;
                previewImage.style.opacity = '1';

                var warningDiv = document.createElement('div');
                warningDiv.className = 'mt-3 bg-yellow-50 border border-yellow-300 text-yellow-800 text-xs px-4 py-3 rounded-lg';
                warningDiv.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i> <strong>TTD Service tidak tersedia</strong> (' + err.message + '). Gambar akan dikirim apa adanya. Pastikan <code>ttd_service.py</code> berjalan di port 5001.';
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

                processedFileDataUrl = dataUrl;
                fileNameDisplay.textContent = newName;
            } catch (err) {
                console.error('Gagal apply ke form:', err);
            }
        }
    </script>
</body>
</html>