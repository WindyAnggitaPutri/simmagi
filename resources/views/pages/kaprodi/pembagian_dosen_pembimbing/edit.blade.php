@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6">
    {{-- ==================== HEADER ==================== --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-yellow-400">Edit Dosen Pembimbing</h1>
        <p class="text-gray-500 text-sm mt-1">Ubah dosen pembimbing dan daftar mahasiswa bimbingan</p>
    </div>

    <form action="{{ route('kaprodi.dosen-pembimbing.update', $dosenPembimbing->id_dosen_pembimbing) }}" method="POST" id="form-edit-dospem">
        @csrf @method('PUT')

        {{-- ==================== DOSEN PEMBIMBING ==================== --}}
        <div class="mb-8">
            <h2 class="text-lg font-bold text-gray-800 mb-4 border-b-2 border-yellow-400 pb-2">
                 Data Dosen Pembimbing
            </h2>
            <div class="bg-white shadow-md border border-gray-200 rounded-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center gap-4">
                    <div class="flex-1">
                        <p class="text-xs text-gray-400 mb-1">Dosen Saat Ini</p>
                        <p class="font-semibold text-gray-900">
                            {{ $dosenPembimbing->dosen->nama ?? '-' }}
                            <span class="text-gray-400 font-normal text-sm">({{ $dosenPembimbing->dosen->nidn ?? '-' }})</span>
                        </p>
                    </div>
                    <div class="flex-1">
                        <label class="text-xs text-gray-400 mb-1 block">Ganti Ke Dosen</label>
                        <select name="id_dosen" id="select-dosen" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-400 outline-none transition text-sm" required>
                            <option value="" disabled>-- Pilih Dosen Baru --</option>
                            @foreach ($dosen as $d)
                                <option value="{{ $d->id_dosen }}" {{ $d->id_dosen == $dosenPembimbing->id_dosen ? 'selected' : '' }}>
                                    {{ $d->nama }} ({{ $d->nidn }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== TABEL MAHASISWA DENGAN CHECKBOX ==================== --}}
        <div class="mb-8">
            <div class="mb-4 border-b-2 border-yellow-400 pb-2">
                <h2 class="text-lg font-bold text-gray-800"> Daftar Mahasiswa Bimbingan</h2>
                <p class="text-xs text-gray-400 mt-0.5">Centang mahasiswa yang ingin ditugaskan ke dosen ini. Mahasiswa yang <span class="text-green-600 font-semibold">dicentang</span> akan dipertahankan/ditambahkan. <span class="text-red-500 font-semibold">Tidak dicentang = dihapus dari bimbingan.</span></p>
            </div>
            <div class="bg-white shadow-md border border-gray-200 rounded-lg overflow-hidden">
                {{-- Toolbar ==================== --}}
                <div class="p-4 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        {{-- Kiri: Search ==================== --}}
                        <div class="relative w-full sm:w-auto">
                            <input type="text" 
                                   id="searchMhs" 
                                   placeholder="Cari NPM / nama..." 
                                   class="w-full sm:w-64 pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        </div>
                        {{-- Kanan: Pilih Semua + Counter ==================== --}}
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" id="checkAll" class="w-4 h-4 cursor-pointer accent-yellow-600 rounded">
                                <span class="text-sm font-semibold text-gray-700">Pilih Semua</span>
                            </label>
                            <div id="selectedCount" class="text-sm font-bold text-yellow-600 bg-yellow-50 border border-yellow-200 px-3 py-1.5 rounded-lg min-w-[80px] text-center">
                                0 dipilih
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabel ==================== --}}
                @if($mahasiswaTersedia->isEmpty())
                    <div class="py-16 text-center text-gray-400">
                        <i class="fas fa-user-graduate text-5xl text-gray-200 mb-3 block"></i>
                        <p class="font-medium">Tidak ada mahasiswa yang tersedia</p>
                        <p class="text-xs mt-1">Semua mahasiswa sudah memiliki dosen pembimbing atau belum ada pengajuan yang diterima.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left" id="tableMhs">
                            <thead class="bg-yellow-500 text-white">
                                <tr>
                                    <th class="p-4 text-center w-12">No</th>
                                    <th class="p-4 w-32">NPM</th>
                                    <th class="p-4">Nama Mahasiswa</th>
                                    <th class="p-4 text-center w-28">Tahun Angkatan</th>
                                    <th class="p-4">Nama Perusahaan</th>
                                    <th class="p-4 text-center w-20">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700" id="tbodyMhs">
                                @foreach($mahasiswaTersedia as $index => $pm)
                                    @php
                                        $isChecked = $kelompokDosen->contains(function($kd) use ($pm) {
                                            return $kd->current_id_pm == $pm->id_pengajuan_mahasiswa;
                                        });
                                    @endphp
                                    <tr class="border-b border-gray-100 hover:bg-yellow-50/50 transition mhs-row {{ $isChecked ? 'bg-yellow-50/70' : '' }}" data-search="{{ strtolower(($pm->mahasiswa->npm ?? '') . ' ' . ($pm->mahasiswa->nama ?? '')) }}">
                                        <td class="p-4 text-center text-gray-500 font-medium row-number">{{ $index + 1 }}</td>
                                        <td class="p-4 font-mono text-xs text-gray-600">{{ $pm->mahasiswa->npm ?? '-' }}</td>
                                        <td class="p-4 font-semibold text-gray-900">
                                            {{ $pm->mahasiswa->nama ?? '-' }}
                                            @if($isChecked)
                                                <span class="ml-2 inline-block bg-green-100 text-green-700 text-[10px] font-bold px-1.5 py-0.5 rounded">Saat Ini</span>
                                            @endif
                                        </td>
                                        <td class="p-4 text-center">
                                            <span class="inline-block bg-blue-50 text-blue-700 text-xs font-bold px-2.5 py-1 border border-blue-200 rounded">
                                                {{ $pm->mahasiswa->angkatan ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-gray-600">{{ $pm->pengajuan->perusahaan->nama_perusahaan ?? '-' }}</td>
                                        <td class="p-4 text-center">
                                            <input type="checkbox" 
                                                   name="mahasiswa[]" 
                                                   value="{{ $pm->id_pengajuan_mahasiswa }}" 
                                                   {{ $isChecked ? 'checked' : '' }}
                                                   class="mhs-check w-4 h-4 cursor-pointer accent-yellow-600 rounded">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer info ==================== --}}
                    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50 flex justify-between items-center text-xs text-gray-500">
                        <span>Total: <strong class="text-gray-700">{{ $mahasiswaTersedia->count() }}</strong> mahasiswa tersedia</span>
                        <span id="filteredInfo"></span>
                    </div>
                @endif
            </div>
        </div>

        {{-- ==================== TOMBOL AKSI ==================== --}}
        <div class="mt-6 flex flex-wrap gap-3">
            <button type="submit" id="btnSubmit" class="bg-yellow-500 text-white px-8 py-2.5 font-bold hover:bg-yellow-600 transition text-sm rounded-lg shadow-sm">
                <i class="fas fa-save mr-1.5"></i>Update Penugasan
            </button>
            <button type="button" id="btn-reset" class="border border-orange-400 text-orange-600 px-6 py-2.5 font-semibold hover:bg-orange-50 transition text-sm rounded-lg">
                Reset
            </button>
            <a href="{{ route('kaprodi.dosen-pembimbing.index') }}" class="border border-gray-300 text-gray-700 px-6 py-2.5 font-semibold hover:bg-gray-50 transition text-sm rounded-lg">
                Kembali
            </a>
        </div>
    </form>
</div>

<script>
    const checkAll = document.getElementById('checkAll');
    const checks = document.querySelectorAll('.mhs-check');
    const countEl = document.getElementById('selectedCount');
    const searchInput = document.getElementById('searchMhs');
    const rows = document.querySelectorAll('.mhs-row');
    const filteredInfo = document.getElementById('filteredInfo');

    // ========== SIMPAN STATE AWAL (untuk reset) ==========
    const initialDosenValue = document.getElementById('select-dosen').value;
    const initialChecked = [];
    checks.forEach(cb => {
        if (cb.checked) initialChecked.push(cb.value);
    });

    // ========== UPDATE COUNTER (tanpa disable button) ==========
    function updateCount() {
        const checked = document.querySelectorAll('.mhs-check:checked');
        const total = checked.length;
        countEl.textContent = total + ' dipilih';
        countEl.classList.toggle('bg-yellow-100', total > 0);
        countEl.classList.toggle('border-yellow-400', total > 0);
        countEl.classList.toggle('text-yellow-700', total > 0);

        // Update row highlight
        rows.forEach(row => {
            const cb = row.querySelector('.mhs-check');
            if (cb) {
                row.classList.toggle('bg-yellow-50/70', cb.checked);
            }
        });
    }

    // ========== CHECK ALL ==========
    checkAll.addEventListener('change', function() {
        const isChecked = this.checked;
        document.querySelectorAll('.mhs-row:not(.hidden-row) .mhs-check').forEach(cb => {
            cb.checked = isChecked;
        });
        updateCount();
    });

    // ========== INDIVIDUAL CHECK ==========
    checks.forEach(cb => {
        cb.addEventListener('change', function() {
            const visibleChecks = document.querySelectorAll('.mhs-row:not(.hidden-row) .mhs-check');
            const visibleChecked = document.querySelectorAll('.mhs-row:not(.hidden-row) .mhs-check:checked');
            if (visibleChecked.length === 0) {
                checkAll.checked = false;
                checkAll.indeterminate = false;
            } else if (visibleChecked.length === visibleChecks.length) {
                checkAll.checked = true;
                checkAll.indeterminate = false;
            } else {
                checkAll.checked = false;
                checkAll.indeterminate = true;
            }
            updateCount();
        });
    });

    // ========== SEARCH / FILTER ==========
    let searchTimer = null;
    searchInput.addEventListener('input', function() {
        const keyword = this.value.trim().toLowerCase();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            let visibleCount = 0;
            let totalCount = rows.length;
            let num = 0;
            rows.forEach(row => {
                const text = row.getAttribute('data-search');
                if (!keyword || text.includes(keyword)) {
                    row.classList.remove('hidden-row');
                    row.style.display = '';
                    num++;
                    row.querySelector('.row-number').textContent = num;
                    visibleCount++;
                } else {
                    row.classList.add('hidden-row');
                    row.style.display = 'none';
                    const cb = row.querySelector('.mhs-check');
                    if (cb) cb.checked = false;
                }
            });
            filteredInfo.textContent = keyword ? 'Menampilkan ' + visibleCount + ' dari ' + totalCount : '';
            const visibleChecks = document.querySelectorAll('.mhs-row:not(.hidden-row) .mhs-check');
            const visibleChecked = document.querySelectorAll('.mhs-row:not(.hidden-row) .mhs-check:checked');
            checkAll.checked = visibleChecked.length === visibleChecks.length && visibleChecks.length > 0;
            checkAll.indeterminate = visibleChecked.length > 0 && visibleChecked.length < visibleChecks.length;
            updateCount();
        }, 200);
    });

    // ========== RESET ==========
    document.getElementById('btn-reset').addEventListener('click', function() {
        document.getElementById('select-dosen').value = initialDosenValue;

        checkAll.checked = false;
        checkAll.indeterminate = false;
        checks.forEach(cb => {
            cb.checked = initialChecked.includes(cb.value);
        });

        rows.forEach(row => {
            const cb = row.querySelector('.mhs-check');
            if (cb) {
                row.classList.toggle('bg-yellow-50/70', initialChecked.includes(cb.value));
            }
        });

        searchInput.value = '';
        let num = 0;
        rows.forEach(row => {
            row.classList.remove('hidden-row');
            row.style.display = '';
            num++;
            row.querySelector('.row-number').textContent = num;
        });
        filteredInfo.textContent = '';
        updateCount();
    });

    updateCount();
</script>
@endsection