@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-6">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-yellow-400">Form Pembagian Dosen Visitasi</h1>
        <p class="text-gray-500 text-sm mt-1">Isi form di bawah untuk membuat penugasan baru</p>
    </div>

    <form action="{{ route('kaprodi.pembagian-dosen-visitasi.store') }}" method="POST" id="form-visitasi">
        @csrf

        {{-- Pilih Dosen --}}
        <div class="bg-white shadow-md border border-gray-200 rounded-lg p-6 mb-6">
            <label class="block font-bold text-gray-800 mb-2">Pilih Dosen Visitasi</label>
            <select name="id_dosen" id="select-dosen" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-yellow-500 focus:ring-yellow-500 outline-none transition" required>
                <option value="" disabled selected>-- Pilih Dosen --</option>
                @foreach ($dosen as $d)
                    <option value="{{ $d->id_dosen }}">{{ $d->nama }} ({{ $d->nidn }})</option>
                @endforeach
            </select>
        </div>

        {{-- Tahun --}}
        <div class="bg-white shadow-md border border-gray-200 rounded-lg p-6 mb-6">
            <label class="block font-bold text-gray-800 mb-2">Tahun Visitasi</label>
            <select name="tahun" id="select-tahun" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-yellow-500 focus:ring-yellow-500 outline-none transition" required>
                @php $currentYear = date('Y'); @endphp
                @for ($i = 0; $i < 5; $i++)
                    <option value="{{ $currentYear + $i }}" {{ $i == 0 ? 'selected' : '' }}>
                        {{ $currentYear + $i }}
                    </option>
                @endfor
            </select>
        </div>

        {{-- Pilih Perusahaan --}}
        <div class="bg-white shadow-md border border-gray-200 rounded-lg p-6 mb-6">
            <label class="block font-bold text-gray-800 mb-2"> Pilih Perusahaan Visitasi</label>
            <select id="perusahaan-select" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-yellow-500 focus:ring-yellow-500 outline-none transition" required>
                <option value="" disabled selected>-- Pilih Perusahaan --</option>
                @foreach ($perusahaanList as $p)
                    <option value="{{ $p->id_perusahaan }}">{{ $p->nama_perusahaan }}</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-2">*Pilih perusahaan terlebih dahulu, daftar mahasiswa akan otomatis muncul.</p>
        </div>

        {{-- Daftar Mahasiswa --}}
        <div class="bg-white shadow-md border border-gray-200 rounded-lg p-6">
            <div class="flex justify-between items-center mb-3">
                <label class="block font-bold text-gray-800"> Daftar Mahasiswa yang Divisitasi</label>
                <span id="mhs-count" class="text-xs font-bold text-yellow-600 bg-yellow-100 px-3 py-1 rounded-full hidden">0 mahasiswa</span>
            </div>

            <p class="text-xs text-gray-500 mb-4">*Semua mahasiswa otomatis masuk daftar. Klik <span class="text-red-500 font-bold">x</span> untuk menghapus yang tidak perlu divisitasi.</p>

            <div id="mahasiswa-list" class="space-y-2"></div>
            <p id="empty-msg" class="text-center py-4 text-gray-400 text-sm">Pilih perusahaan terlebih dahulu untuk melihat daftar mahasiswa.</p>

            <div id="add-mahasiswa-area" class="mt-4 hidden">
                <div class="flex items-center gap-2">
                    <select id="add-mahasiswa-select" class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:border-yellow-500 focus:ring-yellow-500 outline-none transition text-sm">
                        <option value="" disabled selected>-- Pilih Mahasiswa untuk Ditambahkan Kembali --</option>
                    </select>
                    <button type="button" onclick="addBackMahasiswa()" 
                            class="border-2 border-yellow-500 text-yellow-600 font-bold text-xs px-4 py-2 rounded-lg hover:bg-yellow-500 hover:text-white transition-all duration-200">
                        + Tambah
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="bg-yellow-500 text-white px-6 py-2.5 font-bold hover:bg-yellow-600 transition text-sm rounded-lg shadow-sm">
                Simpan Penugasan
            </button>
            <button type="button" id="btn-reset" class="border border-orange-400 text-orange-600 font-semibold py-2.5 px-6 rounded-lg hover:bg-orange-50 transition text-sm">
                Reset
            </button>
            <a href="{{ route('kaprodi.pembagian-dosen-visitasi.index') }}" class="border border-gray-300 text-gray-700 font-semibold py-2.5 px-6 rounded-lg hover:bg-gray-50 transition text-sm">
                Kembali
            </a>
        </div>
    </form>
</div>

<script>
    const allMahasiswa = @json($mahasiswaJson);
    let currentPerusahaanId = null;
    let includedIds = [];
    let removedItems = [];

    const perusahaanSelect = document.getElementById('perusahaan-select');
    const mahasiswaList = document.getElementById('mahasiswa-list');
    const emptyMsg = document.getElementById('empty-msg');
    const addMahasiswaArea = document.getElementById('add-mahasiswa-area');
    const addMahasiswaSelect = document.getElementById('add-mahasiswa-select');
    const mhsCount = document.getElementById('mhs-count');

    function getFilteredMahasiswa() {
        return allMahasiswa.filter(m => String(m.id_perusahaan) === String(currentPerusahaanId));
    }

    function renderList() {
        mahasiswaList.innerHTML = '';

        if (includedIds.length === 0) {
            emptyMsg.textContent = 'Tidak ada mahasiswa yang dipilih untuk visitasi.';
            emptyMsg.classList.remove('hidden');
            mhsCount.classList.add('hidden');
        } else {
            emptyMsg.classList.add('hidden');
            mhsCount.textContent = includedIds.length + ' mahasiswa';
            mhsCount.classList.remove('hidden');

            includedIds.forEach((id, index) => {
                const mhs = allMahasiswa.find(m => m.id === id);
                if (!mhs) return;

                const div = document.createElement('div');
                div.className = 'flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 hover:bg-yellow-50 transition';
                div.innerHTML = `
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-bold text-yellow-600 w-7 text-right">${index + 1}.</span>
                        <div>
                            <span class="text-sm font-semibold text-gray-900">${mhs.nama}</span>
                        </div>
                    </div>
                    <input type="hidden" name="mahasiswa[]" value="${mhs.id}">
                    <button type="button" onclick="removeMahasiswa(${mhs.id})" 
                            class="text-red-400 hover:text-red-600 hover:bg-red-100 rounded-full w-7 h-7 flex items-center justify-center transition cursor-pointer" title="Hapus mahasiswa ini">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                `;
                mahasiswaList.appendChild(div);
            });
        }

        updateAddDropdown();
    }

    function removeMahasiswa(id) {
        includedIds = includedIds.filter(i => i !== id);
        const mhs = allMahasiswa.find(m => m.id === id);
        if (mhs) removedItems.push(mhs);
        renderList();
    }

    function addBackMahasiswa() {
        const id = parseInt(addMahasiswaSelect.value);
        if (!id) return;

        includedIds.push(id);
        removedItems = removedItems.filter(m => m.id !== id);
        renderList();
    }

    function updateAddDropdown() {
        addMahasiswaSelect.innerHTML = '<option value="" disabled selected>-- Pilih Mahasiswa untuk Ditambahkan Kembali --</option>';

        if (removedItems.length === 0) {
            addMahasiswaArea.classList.add('hidden');
        } else {
            addMahasiswaArea.classList.remove('hidden');
            removedItems.forEach(mhs => {
                const option = document.createElement('option');
                option.value = mhs.id;
                option.text = mhs.nama;
                addMahasiswaSelect.appendChild(option);
            });
        }
    }

    perusahaanSelect.addEventListener('change', function () {
        currentPerusahaanId = this.value;
        includedIds = [];
        removedItems = [];

        const filtered = getFilteredMahasiswa();

        if (filtered.length === 0) {
            emptyMsg.textContent = 'Tidak ada mahasiswa magang di perusahaan ini.';
            emptyMsg.classList.remove('hidden');
            addMahasiswaArea.classList.add('hidden');
            mahasiswaList.innerHTML = '';
            mhsCount.classList.add('hidden');
        } else {
            filtered.forEach(m => includedIds.push(m.id));
            renderList();
        }
    });

    // ========== RESET ==========
    document.getElementById('btn-reset').addEventListener('click', function() {
        document.getElementById('form-visitasi').reset();
        document.getElementById('select-dosen').selectedIndex = 0;
        document.getElementById('select-tahun').selectedIndex = 0;
        perusahaanSelect.selectedIndex = 0;
        mahasiswaList.innerHTML = '';
        emptyMsg.textContent = 'Pilih perusahaan terlebih dahulu untuk melihat daftar mahasiswa.';
        emptyMsg.classList.remove('hidden');
        addMahasiswaArea.classList.add('hidden');
        mhsCount.classList.add('hidden');
    });
</script>
@endsection