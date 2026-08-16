<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengajuan - SIMMAGI</title>
    {{-- Vite WAJIB ADA biar CSS Tailwindnya ke-load --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-950 min-h-screen">

<div class="max-w-4xl mx-auto py-10 px-6">
    
    {{-- Judul --}}
    <h1 class="text-center text-2xl font-bold mb-2">
        PENGAJUAN PERMOHONAN MAGANG INDUSTRI <br>
        MAHASISWA JURUSAN KOMPUTER DAN BISNIS
    </h1>
    

    {{-- Step Indicator --}}
    <div class="flex justify-center items-center gap-4 mb-10">
        <span id="step-indicator-1" class="font-bold text-yellow-600 underline decoration-yellow-500 pb-1">FORM 1</span>
        <span class="text-gray-400">—</span>
        <span id="step-indicator-2" class="font-bold text-gray-400 pb-1">FORM 2</span>
    </div>
 <a href="{{ route('mahasiswa.pengajuan_magang.index') }}" class="text-yellow-600 font-semibold text-sm hover:underline mb-4 inline-block">
        ← Kembali ke Daftar Pengajuan
    </a>
{{-- ==================== PERINGATAN TTD BELUM UPLOAD ==================== --}}
@if(!$sudahUploadTTD)
<div class="border-2 border-red-400 bg-red-50 rounded-lg p-5 mb-6">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
        </div>
        <div class="flex-1">
            <h3 class="font-bold text-red-700 text-lg mb-1">TTD Digital Belum Diupload!</h3>
            <p class="text-red-600 text-sm mb-3">
                Anda tidak dapat mengirim permohonan magang sebelum mengupload Tanda Tangan Digital.
            </p>
            <a href="{{ route('profil.edit') }}" class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white font-bold text-sm px-5 py-2.5 rounded-lg shadow transition">
                <i class="fas fa-pen"></i>
                Upload TTD Digital di Edit Profil
            </a>
        </div>
    </div>
</div>
@endif
{{-- ==================== END PERINGATAN ==================== --}}
    {{-- FORM --}}
    <form id="pengajuanForm" action="{{ route('mahasiswa.pengajuan_magang.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- ==================== FORM 1 ==================== --}}
        <div id="form-1" class="space-y-6 border border-gray-300 p-8">
            
           <div>
    <label class="block font-medium mb-1">Nama Perusahaan</label>
    <select id="pilih_perusahaan" onchange="togglePerusahaanBaru()" name="id_perusahaan" class="w-full border border-gray-300 px-4 py-2 focus:border-black outline-none" required>
        <option value="" disabled selected>-- Pilih Perusahaan --</option>
        @foreach ($perusahaan as $p)
            <option value="{{ $p->id_perusahaan }}">{{ $p->nama_perusahaan }} - {{ $p->alamat }}</option>
        @endforeach
        <option value="new">+ Tambah Perusahaan Lainnya</option>
    </select>
</div>

            {{-- FORM MANUAL (Awalnya Hidden) --}}
            <div id="form-perusahaan-baru" class="hidden space-y-4 mt-2 p-4 bg-gray-50 border border-gray-200">
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Perusahaan Baru</label>
                    <input type="text" name="nama_perusahaan_baru" id="input_nama_perusahaan" class="w-full border border-gray-300 px-4 py-2 focus:border-black outline-none" placeholder="Masukkan nama perusahaan">
                    @error('nama_perusahaan_baru')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Alamat Perusahaan Baru</label>
                    <textarea name="alamat_perusahaan_baru" id="input_alamat_perusahaan" rows="2" class="w-full border border-gray-300 px-4 py-2 focus:border-black outline-none" placeholder="Masukkan alamat lengkap"></textarea>
                </div>
            </div>

           {{-- SESUDAH --}}
<div>
    <label class="block font-medium mb-1">Tanggal Pengajuan</label>
    <input type="date" name="tgl_pengajuan" value="{{ date('Y-m-d') }}" disabled class="w-full border border-gray-200 bg-gray-100 text-gray-500 px-4 py-2 outline-none cursor-not-allowed">
    <p class="text-xs text-gray-400 mt-1">* Otomatis tanggal hari ini</p>
</div>

            <div>
                <label class="block font-medium mb-1">Catatan</label>
                <textarea name="catatan" rows="4" class="w-full border border-gray-300 px-4 py-2 focus:border-black outline-none"></textarea>
            </div>

            

            {{-- ==================== TAMBAHAN FORM BARU ==================== --}}
            <div class="border-t border-gray-300 my-6 pt-6">
                <h3 class="font-bold text-base mb-4 text-gray-700">Detail Tempat & Waktu Magang</h3>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-1">Ditujukan Kepada (Jabatan)</label>
                        <input type="text" name="ditujukan_kepada" class="w-full border border-gray-300 px-4 py-2 focus:border-black outline-none" placeholder="Contoh: Senior Manager, HRD, dll" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email Perusahaan</label>
                        <input type="email" name="email_perusahaan" class="w-full border border-gray-300 px-4 py-2 focus:border-black outline-none" placeholder="Contoh: hr@perusahaan.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Contact Person (CP)</label>
                        <input type="text" name="contact_person" class="w-full border border-gray-300 px-4 py-2 focus:border-black outline-none" placeholder="Nama CP di perusahaan" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">No. HP CP</label>
                        <input type="number" name="no_hp_cp" class="w-full border border-gray-300 px-4 py-2 focus:border-black outline-none" placeholder="08xxxxxxxxxx" required>
                    </div>
                   
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1">Deskripsi Perusahaan</label>
                    <textarea name="deskripsi_perusahaan" rows="3" class="w-full border border-gray-300 px-4 py-2 focus:border-black outline-none" placeholder="Deskripsikan singkat perusahaan tersebut..."></textarea>
                </div>
            </div>
            {{-- ==================== END TAMBAHAN FORM BARU ==================== --}}

            <div class="flex justify-end pt-4">
                <button type="button" onclick="nextStep()" class="border border-gray-300 px-8 py-3 font-bold hover:bg-gray-100 transition">
                    Berikutnya →
                </button>
            </div>

        </div>

        {{-- ==================== FORM 2 ==================== --}}
        <div id="form-2" class="space-y-6 border border-gray-300 p-8 hidden">
            
            <div id="mahasiswa-container" class="space-y-6">
                
        {{-- Card Mahasiswa 1 (YANG LOGIN - GAK BISA DIUBAH) --}}
        <div class="p-4 border border-gray-300 bg-gray-50">
            <h3 class="font-bold mb-4 text-gray-700">Mahasiswa 1 (Anda)</h3>
            
            {{-- TAMBAHKAN HIDDEN INI --}}
            <input type="hidden" name="mahasiswa[1][id]" value="{{ $userLogin->id_mahasiswa }}">
            
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nama</label>
                    <input type="text" value="{{ $userLogin->nama ?? 'Data mahasiswa belum lengkap' }}" disabled class="w-full border border-gray-200 bg-gray-100 text-gray-500 px-4 py-2 outline-none cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">IPK</label>
                    <input type="number" step="0.01" min="0" max="4.00" name="mahasiswa[1][ipk]" value="{{ old('mahasiswa.1.ipk', $userLogin->ipk) }}" class="w-full border border-gray-300 px-4 py-2 focus:border-black outline-none" placeholder="Contoh: 3.85" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Surat Izin Orang Tua</label>
                    <input type="file" name="mahasiswa[1][surat_ortu]" accept=".pdf,.jpg,.png" class="w-full border border-gray-300 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300">
                </div>
            </div>
        </div>

            </div>

            <button type="button" onclick="addMahasiswa()" class="text-yellow-600 font-semibold hover:underline text-sm">
                + Tambah Mahasiswa (Anggota Kelompok)
            </button>

            {{-- ==================== CHECKBOX PERSETUJUAN ==================== --}}
            <div class="border-t border-gray-300 pt-6">
<label class="flex items-start gap-3 cursor-pointer">
    <input type="checkbox" id="agree_ttd" onchange="toggleSubmitBtn()" class="mt-1 w-4 h-4 accent-yellow-500">
    <span class="text-sm text-gray-700">
        <strong>Setuju</strong> untuk mengajukan permohonan magang ini <br>
        <span class="text-gray-400">(Terlampir Tanda Tangan Digital di atas)</span>
    </span>
</label>
            </div>
            {{-- ==================== END CHECKBOX ==================== --}}

            <div class="flex justify-between pt-4 border-t border-gray-300">
                <button type="button" onclick="prevStep()" class="border border-gray-300 px-8 py-3 font-bold hover:bg-gray-100 transition">
                    ← Kembali
                </button>
                @if(!$sudahUploadTTD)
                    {{-- ✅ TOMBOL DISABLE KARENA BELUM UPLOAD TTD --}}
                    <button type="button" disabled class="border-2 border-gray-300 text-gray-400 px-8 py-3 font-bold cursor-not-allowed bg-gray-100" title="Upload TTD Digital terlebih dahulu">
                        <i class="fas fa-lock mr-2"></i>Kirim (TTD Belum Diupload)
                    </button>
                @else
                    <button type="submit" id="btn-submit" disabled class="border-2 border-yellow-500 text-yellow-600 px-8 py-3 font-bold hover:bg-yellow-500 hover:text-white transition opacity-50 cursor-not-allowed">
                        Kirim
                    </button>
                @endif
            </div>
        </div>

    </form>
</div>

{{-- SCRIPT MULTI-STEP --}}
{{-- SCRIPT MULTI-STEP --}}
<script>
    let mahasiswaCount = 1;

    // ✅ DATA MAHASISWA LAIN DARI PHP (disimpan global)
    const allMahasiswaOptions = @json($mahasiswaLain ?? []);

    // ✅ ID mahasiswa yang login (wajib di-exclude)
    const idLogin = {{ $userLogin->id_mahasiswa }};

    function nextStep() {
        document.getElementById('form-1').classList.add('hidden');
        document.getElementById('form-2').classList.remove('hidden');
        
        document.getElementById('step-indicator-1').classList.remove('text-yellow-600', 'underline', 'decoration-yellow-500');
        document.getElementById('step-indicator-1').classList.add('text-gray-400');
        
        document.getElementById('step-indicator-2').classList.remove('text-gray-400');
        document.getElementById('step-indicator-2').classList.add('text-yellow-600', 'underline', 'decoration-yellow-500');
    }

    function prevStep() {
        document.getElementById('form-2').classList.add('hidden');
        document.getElementById('form-1').classList.remove('hidden');
        
        document.getElementById('step-indicator-2').classList.remove('text-yellow-600', 'underline', 'decoration-yellow-500');
        document.getElementById('step-indicator-2').classList.add('text-gray-400');
        
        document.getElementById('step-indicator-1').classList.remove('text-gray-400');
        document.getElementById('step-indicator-1').classList.add('text-yellow-600', 'underline', 'decoration-yellow-500');
    }

    // ✅ FUNGSI BARU: Ambil ID yang sudah dipilih di semua card (kecuali card tertentu)
    function getSelectedIds(exceptCardId) {
        const selected = [idLogin]; // selalu exclude yang login
        const container = document.getElementById('mahasiswa-container');
        const selects = container.querySelectorAll('select[name^="mahasiswa["][name$="][id]"]');
        
        selects.forEach(sel => {
            const card = sel.closest('[id^="mhs-card-"]');
            if (card) {
                const cardId = parseInt(card.id.replace('mhs-card-', ''));
                // Hanya ambil dari card lain (bukan card yang sedang di-update)
                if (cardId !== exceptCardId && sel.value) {
                    selected.push(parseInt(sel.value));
                }
            }
        });
        
        return selected;
    }

    // ✅ FUNGSI BARU: Update dropdown sebuah card
    function updateDropdownOptions(selectEl, cardId) {
        const selectedIds = getSelectedIds(cardId);
        const currentVal = selectEl.value;
        
        let html = '<option value="" disabled selected>-- Pilih Mahasiswa --</option>';
        
        allMahasiswaOptions.forEach(m => {
            // Skip kalau ID-nya udah dipilih di card lain
            if (!selectedIds.includes(m.id_mahasiswa)) {
                const selected = m.id_mahasiswa == currentVal ? 'selected' : '';
                html += `<option value="${m.id_mahasiswa}" ${selected}>${m.nama}</option>`;
            }
        });
        
        selectEl.innerHTML = html;
    }

    // ✅ FUNGSI BARU: Update semua dropdown (bisa dipanggil dari onchange)
    function refreshAllDropdowns(changedCardId) {
        const container = document.getElementById('mahasiswa-container');
        const selects = container.querySelectorAll('select[name^="mahasiswa["][name$="][id]"]');
        
        selects.forEach(sel => {
            const card = sel.closest('[id^="mhs-card-"]');
            if (card) {
                const cardId = parseInt(card.id.replace('mhs-card-', ''));
                updateDropdownOptions(sel, cardId);
            }
        });
    }

    function addMahasiswa() {
        mahasiswaCount++;
        const container = document.getElementById('mahasiswa-container');

        // Bangun options yang sudah di-filter
        const selectedIds = getSelectedIds(mahasiswaCount);
        let optionsHtml = '<option value="" disabled selected>-- Pilih Mahasiswa --</option>';
        
        allMahasiswaOptions.forEach(m => {
            if (!selectedIds.includes(m.id_mahasiswa)) {
                optionsHtml += `<option value="${m.id_mahasiswa}">${m.nama}</option>`;
            }
        });

        const newCard = `
            <div id="mhs-card-${mahasiswaCount}" class="p-4 border border-gray-300 relative">
                
                <button type="button" onclick="removeMahasiswa(${mahasiswaCount})" class="absolute top-2 right-2 text-red-500 hover:text-red-700 font-bold text-sm px-2 py-1 border border-red-300 rounded hover:bg-red-50 transition">
                    ✕ Hapus
                </button>

                <h3 class="font-bold mb-4 text-gray-700">Mahasiswa ${mahasiswaCount}</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama</label>
                        <select name="mahasiswa[${mahasiswaCount}][id]" onchange="refreshAllDropdowns(${mahasiswaCount})" class="w-full border border-gray-300 px-4 py-2 focus:border-black outline-none" required>
                            ${optionsHtml}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">IPK</label>
                        <input type="number" step="0.01" min="0" max="4.00" name="mahasiswa[${mahasiswaCount}][ipk]" class="w-full border border-gray-300 px-4 py-2 focus:border-black outline-none" placeholder="Contoh: 3.85" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Surat Izin Orang Tua</label>
                        <input type="file" name="mahasiswa[${mahasiswaCount}][surat_ortu]" accept=".pdf,.jpg,.png" class="w-full border border-gray-300 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300">
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', newCard);
    }

    function removeMahasiswa(id) {
        const card = document.getElementById('mhs-card-' + id);
        if (card) {
            card.remove();
            // ✅ Setelah hapus, refresh dropdown card yang tersisa
            refreshAllDropdowns(-1);
        }
    }

    function togglePerusahaanBaru() {
        const select = document.getElementById('pilih_perusahaan');
        const formBaru = document.getElementById('form-perusahaan-baru');
        const inputNama = document.getElementById('input_nama_perusahaan');
        const inputAlamat = document.getElementById('input_alamat_perusahaan');

        if (select.value === 'new') {
            formBaru.classList.remove('hidden');
            inputNama.setAttribute('required', 'true');
            inputAlamat.setAttribute('required', 'true');
        } else {
            formBaru.classList.add('hidden');
            inputNama.removeAttribute('required');
            inputAlamat.removeAttribute('required');
            inputNama.value = '';
            inputAlamat.value = '';
        }
    }

function toggleSubmitBtn() {
    const checkbox = document.getElementById('agree_ttd');
    const btnSubmit = document.getElementById('btn-submit');

    // Kalau TTD belum upload, tombolnya bukan id="btn-submit" → skip saja
    if (!btnSubmit) return;

    if (checkbox.checked) {
        btnSubmit.disabled = false;
        btnSubmit.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        btnSubmit.disabled = true;
        btnSubmit.classList.add('opacity-50', 'cursor-not-allowed');
    }
}
</script>
</body>
</html>