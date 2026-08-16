@extends('layouts.admin')

@section('title', 'SIMMAGI - Kelola Mahasiswa')
@section('page-title', 'Data Mahasiswa')

@section('content')
<h2 class="text-center text-4xl font-bold text-gray-800 mb-6">.:: Kelola Mahasiswa ::.</h2>

<div class="bg-white shadow-md p-4 rounded-lg">
    {{-- SATU BARIS: TOMBOL KIRI, FILTER KANAN --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        
        {{-- KIRI: TOMBOL AKSI --}}
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.mahasiswa.create') }}"
               class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 flex items-center transition">
                <i class="fas fa-plus mr-2"></i>Tambah
            </a>

            <a href="{{ route('admin.mahasiswa.import.form') }}"
               class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 flex items-center transition">
                <i class="fas fa-file-import mr-2"></i>Import Excel
            </a>

            <a href="{{ route('admin.mahasiswa.template') }}"
               class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 flex items-center transition">
                <i class="fas fa-download mr-2"></i>Template
            </a>
        </div>

        {{-- KANAN: SEARCH + FILTER --}}
        <div class="flex flex-wrap items-center gap-2">
            
            {{-- SEARCH BAR --}}
            <form id="searchForm">
                <div class="relative">
                    <input type="text"
                           name="search"
                           id="searchInput"
                           placeholder="Cari..."
                           value="{{ request('search') }}"
                           class="w-40 border border-gray-300 rounded-lg p-2 pr-8 text-sm focus:ring-orange-500 focus:border-orange-500">
                    <i class="fas fa-search absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                </div>
            </form>

            {{-- FILTER ANGKATAN --}}
            <select id="filterTahun"
                    class="border border-gray-300 rounded-lg p-2 text-sm">
                <option value="">Tahun</option>
                @foreach ($listAngkatan as $tahun)
                    <option value="{{ $tahun }}">{{ $tahun }}</option>
                @endforeach
            </select>

            {{-- FILTER PRODI --}}
            <select id="filterProdi"
                    class="border border-gray-300 rounded-lg p-2 text-sm max-w-[160px]">
                <option value="">Prodi</option>
                @foreach ($listProdi as $p)
                    <option value="{{ $p->id_prodi }}">{{ $p->nama_prodi }}</option>
                @endforeach
            </select>

            {{-- FILTER KELAS --}}
            <select id="filterKelas"
                    class="border border-gray-300 rounded-lg p-2 text-sm w-20">
                <option value="">Kelas</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>

            {{-- TAMPILKAN PER HALAMAN --}}
            <select id="rowsPerPage"
                    class="border border-gray-300 rounded-lg p-2 text-sm">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="15">15</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>

            {{-- RESET FILTER --}}
            <button id="resetFilter"
                    class="px-2.5 py-2 text-red-500 hover:bg-red-50 rounded-lg transition"
                    title="Reset Filter">
                <i class="fas fa-times-circle"></i>
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300 min-w-max" id="dataTable">
            <thead class="bg-[#fffbeb]">
                <tr>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">No</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">NPM</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Nama</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">L/P</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Prodi</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Angkatan</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Semester</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Kelas</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">No HP</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Email</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse ($mahasiswa as $index => $m)
                <tr class="data-row hover:bg-[#fffbeb]/30 transition" 
                    data-angkatan="{{ $m->angkatan }}" 
                    data-prodi="{{ $m->id_prodi }}"
                    data-kelas="{{ $m->kelas }}">
                    <td class="border border-gray-300 p-3 text-center text-sm text-gray-900">{{ $index + 1 }}</td>
                    <td class="border border-gray-300 p-3 text-sm text-gray-900 font-mono whitespace-nowrap">{{ $m->npm }}</td>
                    <td class="border border-gray-300 p-3 text-sm text-gray-900 font-medium whitespace-nowrap">{{ $m->nama }}</td>
                    <td class="border border-gray-300 p-3 text-sm text-gray-900 text-center">
                        <span class="px-2 py-0.5 rounded text-xs font-bold {{ $m->jenis_kelamin == 'L' ? ' text-black' : ' text-black' }}">
                            {{ $m->jenis_kelamin == 'L' ? 'L' : 'P' }}
                        </span>
                    </td>
                    <td class="border border-gray-300 p-3 text-sm text-gray-900 whitespace-nowrap">{{ $m->prodi->nama_prodi ?? '-' }}</td>
                    <td class="border border-gray-300 p-3 text-sm text-gray-900 text-center font-semibold">{{ $m->angkatan }}</td>
                    <td class="border border-gray-300 p-3 text-sm text-gray-900 text-center">{{ $m->semester }}</td>
                    <td class="border border-gray-300 p-3 text-sm text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-bold  text-black">{{ $m->kelas_lengkap }}</span>
                    </td>
                    <td class="border border-gray-300 p-3 text-sm text-gray-900 whitespace-nowrap">{{ $m->no_hp }}</td>
                    <td class="border border-gray-300 p-3 text-sm text-gray-900 whitespace-nowrap">{{ $m->email }}</td>
                    <td class="border border-gray-300 p-3 text-center text-sm whitespace-nowrap">
                        <a href="{{ route('admin.mahasiswa.edit', $m->id_mahasiswa) }}" class="text-orange-500 hover:underline mr-2 font-medium">Edit</a>
                        <form action="{{ route('admin.mahasiswa.destroy', $m->id_mahasiswa) }}" method="POST" class="inline-block delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline font-medium">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr id="noDataRow">
                    <td colspan="11" class="border border-gray-300 p-3 text-center text-sm text-gray-900">Tidak ada data ditemukan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div id="noSearchResults" class="text-center text-sm text-gray-900 mt-4 hidden">Data tidak ditemukan</div>
    </div>

    <div class="flex justify-between mt-4">
        <button id="prevPage" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg disabled:opacity-50" disabled>Previous</button>
        <span id="pageInfo" class="text-sm text-gray-900 self-center"></span>
        <button id="nextPage" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 disabled:opacity-50" disabled>Next</button>
    </div>
</div>

<input type="hidden" id="success-message" value="{{ session('success') ?? '' }}">

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const successMessage = document.getElementById('success-message').value;
    if (successMessage) {
        Swal.fire({ icon: 'success', title: 'Sukses!', text: successMessage, timer: 2000, showConfirmButton: true, confirmButtonText: 'OK' });
    }

    document.querySelectorAll('.delete-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Peringatan!',
                html: '<p class="text-red-600 font-semibold">Jika anda menghapus Mahasiswa ini maka data pada:</p><ul class="text-left mt-2 text-gray-700 list-disc list-inside"><li>Akun Login User</li><li>Data Magang</li><li>Jadwal Seminar</li></ul><p class="mt-3 text-red-600 font-bold">akan ikut terhapus!</p>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#f97316',
                confirmButtonText: 'Iya, Lanjut',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menghapus...', text: 'Data mahasiswa sedang dihapus.', icon: 'info', showConfirmButton: false, allowOutsideClick: false, didOpen: () => { form.submit(); } });
                }
            });
        });
    });

    let rowsPerPage = parseInt(document.getElementById('rowsPerPage').value);
    let currentPage = 1;
    let filteredRows = [];

    function updateTable() {
        const rows = document.querySelectorAll('#tableBody .data-row');
        const noDataRow = document.getElementById('noDataRow');
        const noSearchResults = document.getElementById('noSearchResults');
        const prevButton = document.getElementById('prevPage');
        const nextButton = document.getElementById('nextPage');
        const pageInfo = document.getElementById('pageInfo');
        
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const filterTahun = document.getElementById('filterTahun').value;
        const filterProdi = document.getElementById('filterProdi').value;
        const filterKelas = document.getElementById('filterKelas').value;

        filteredRows = Array.from(rows).filter(row => {
            const matchSearch = row.cells[1].textContent.toLowerCase().includes(searchTerm) || 
                                row.cells[2].textContent.toLowerCase().includes(searchTerm) || 
                                row.cells[4].textContent.toLowerCase().includes(searchTerm) ||
                                row.cells[9].textContent.toLowerCase().includes(searchTerm);

            const rowTahun = row.getAttribute('data-angkatan');
            const matchTahun = filterTahun === "" || rowTahun === filterTahun;

            const rowProdi = row.getAttribute('data-prodi');
            const matchProdi = filterProdi === "" || rowProdi === filterProdi;

            const rowKelas = row.getAttribute('data-kelas');
            const matchKelas = filterKelas === "" || rowKelas === filterKelas;

            return matchSearch && matchTahun && matchProdi && matchKelas;
        });

        rows.forEach(row => row.style.display = 'none');
        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        currentPage = Math.min(currentPage, Math.max(1, totalPages));
        filteredRows.slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage).forEach(row => row.style.display = '');
        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages || totalRows === 0;
        pageInfo.textContent = totalRows > 0 ? 'Page ' + currentPage + ' of ' + totalPages + ' (Total: ' + totalRows + ')' : '';
        if (noDataRow) noDataRow.parentElement.style.display = rows.length === 0 ? '' : 'none';
        noSearchResults.classList.toggle('hidden', totalRows > 0 || rows.length === 0);
    }

    document.getElementById('searchForm').addEventListener('submit', function(e) { e.preventDefault(); });
    document.getElementById('searchInput').addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); e.stopPropagation(); } });
    document.getElementById('searchInput').addEventListener('input', function() { currentPage = 1; updateTable(); });
    document.getElementById('rowsPerPage').addEventListener('change', function() { rowsPerPage = parseInt(this.value); currentPage = 1; updateTable(); });
    document.getElementById('filterTahun').addEventListener('change', function() { currentPage = 1; updateTable(); });
    document.getElementById('filterProdi').addEventListener('change', function() { currentPage = 1; updateTable(); });
    document.getElementById('filterKelas').addEventListener('change', function() { currentPage = 1; updateTable(); });
    
    document.getElementById('resetFilter').addEventListener('click', function() {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterTahun').value = '';
        document.getElementById('filterProdi').value = '';
        document.getElementById('filterKelas').value = '';
        document.getElementById('rowsPerPage').value = '10';
        rowsPerPage = 10;
        currentPage = 1;
        updateTable();
    });

    document.getElementById('prevPage').addEventListener('click', function() { if (currentPage > 1) { currentPage--; updateTable(); } });
    document.getElementById('nextPage').addEventListener('click', function() { var tp = Math.ceil(filteredRows.length / rowsPerPage); if (currentPage < tp) { currentPage++; updateTable(); } });
    
    updateTable();
</script>

<script>
    @if(session('import_errors') && count(session('import_errors')) > 0)
        const importErrors = @json(session('import_errors'));
        
        let errorHtml = '<div class="max-h-96 overflow-y-auto"><table class="w-full text-sm">';
        errorHtml += '<thead class="bg-red-100"><tr><th class="p-2 text-left">Baris</th><th class="p-2 text-left">NPM</th><th class="p-2 text-left">Nama</th><th class="p-2 text-left">Error</th></tr></thead><tbody>';
        
        importErrors.forEach(function(err) {
            errorHtml += '<tr class="border-b">';
            errorHtml += '<td class="p-2 font-mono">' + err.row + '</td>';
            errorHtml += '<td class="p-2 font-mono">' + err.npm + '</td>';
            errorHtml += '<td class="p-2">' + err.nama + '</td>';
            errorHtml += '<td class="p-2 text-red-600"><ul class="list-disc list-inside">';
            err.errors.forEach(function(e) {
                errorHtml += '<li>' + e + '</li>';
            });
            errorHtml += '</ul></td></tr>';
        });
        
        errorHtml += '</tbody></table></div>';
        
        Swal.fire({
            title: 'Detail Error Import',
            html: errorHtml,
            icon: 'warning',
            width: '700px',
            confirmButtonText: 'Tutup'
        });
    @endif
</script>
@endpush
@endsection