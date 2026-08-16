@extends('layouts.admin')

@section('title', 'SIMMAGI - Nilai Dosen Pembimbing')
@section('page-title', 'Nilai Dosen Pembimbing')

@section('content')
<h2 class="text-center text-4xl font-bold text-gray-800 mb-6">.:: Nilai Dosen Pembimbing ::.</h2>

<div class="bg-white shadow-md p-4 rounded-lg">
    {{-- Toolbar: Tampilkan + Search --}}
    <div class="flex flex-wrap justify-between mb-4 gap-3">
        <div class="flex items-center space-x-4">
            <div class="flex items-center">
                <label for="rowsPerPage" class="mr-2 text-sm text-gray-600">Tampilkan:</label>
                <select id="rowsPerPage" class="border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                </select>
            </div>
        </div>
        <div class="flex items-center space-x-3 flex-1 max-w-xl">
            <div class="relative w-full">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" id="searchInput" placeholder="Cari nama, NPM, dosen, perusahaan..." class="w-full border border-gray-300 rounded-lg p-2 pl-10 focus:ring-orange-500 focus:border-orange-500">
            </div>
        </div>
    </div>

    {{-- Tabel Data --}}
    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300" id="dataTable">
            <thead class="bg-[#fffbeb]">
                <tr>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-16">No</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase min-w-[180px]">Nama Mahasiswa</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase min-w-[180px]">Nama Dosen</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase min-w-[150px]">Perusahaan</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-36">Tgl Seminar</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-28">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @if($jadwal->isEmpty())
                    <tr id="noDataRow">
                        <td colspan="6" class="border border-gray-300 p-10 text-center text-gray-400">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            Belum ada data penilaian.
                        </td>
                    </tr>
                @else
                    @foreach ($jadwal as $item)
                        <tr class="data-row hover:bg-[#fffbeb]/30 transition">
                            <td class="border border-gray-300 p-3 text-center text-gray-500 font-medium row-no">{{ $loop->iteration }}</td>
                            <td class="border border-gray-300 p-3">
                                <span class="font-semibold text-gray-900 text-sm">{{ $item->mahasiswa->nama ?? '-' }}</span>
                                <p class="text-xs text-gray-500">{{ $item->mahasiswa->npm ?? '-' }}</p>
                            </td>
                            <td class="border border-gray-300 p-3 text-sm text-gray-700">{{ $item->dosenPembimbing->dosen->nama ?? '-' }}</td>
                            <td class="border border-gray-300 p-3 text-sm text-gray-700">{{ $item->perusahaan->nama_perusahaan ?? '-' }}</td>
                            <td class="border border-gray-300 p-3 text-center text-sm text-gray-700">{{ \Carbon\Carbon::parse($item->tgl_semgang)->format('d M Y') }}</td>
                            <td class="border border-gray-300 p-3 text-center">
                                <a href="{{ route('admin.penilaian-dospem.detail', $item->id_jadwal_semgang) }}" 
                                   class="text-blue-600 hover:underline text-xs font-semibold">
                                     Lihat
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        <div id="noSearchResults" class="text-center text-sm text-gray-500 mt-4 hidden">Data tidak ditemukan</div>
    </div>

    {{-- Pagination --}}
    <div class="flex justify-between mt-4">
        <button id="prevPage" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg disabled:opacity-50" disabled>Previous</button>
        <span id="pageInfo" class="text-sm text-gray-900 self-center"></span>
        <button id="nextPage" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 disabled:opacity-50" disabled>Next</button>
    </div>
</div>

@push('scripts')
<script>
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

        filteredRows = Array.from(rows).filter(row => {
            return row.textContent.toLowerCase().includes(searchTerm);
        });

        rows.forEach(row => row.style.display = 'none');

        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        currentPage = Math.min(currentPage, Math.max(1, totalPages));

        const start = (currentPage - 1) * rowsPerPage;
        filteredRows.slice(start, start + rowsPerPage).forEach((row, index) => {
            row.style.display = '';
            const cell = row.querySelector('.row-no');
            if (cell) cell.textContent = start + index + 1;
        });

        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages || totalRows === 0;
        pageInfo.textContent = totalRows > 0 ? 'Page ' + currentPage + ' of ' + totalPages : '';

        if (noDataRow) noDataRow.style.display = rows.length === 0 ? '' : 'none';
        noSearchResults.classList.toggle('hidden', totalRows > 0 || rows.length === 0);

        const table = document.getElementById('dataTable');
        table.style.display = (totalRows === 0 && rows.length > 0) ? 'none' : '';
    }

    document.getElementById('searchInput').addEventListener('input', function () {
        currentPage = 1;
        updateTable();
    });
    document.getElementById('rowsPerPage').addEventListener('change', function () {
        rowsPerPage = parseInt(this.value);
        currentPage = 1;
        updateTable();
    });
    document.getElementById('prevPage').addEventListener('click', function () {
        if (currentPage > 1) { currentPage--; updateTable(); }
    });
    document.getElementById('nextPage').addEventListener('click', function () {
        var tp = Math.ceil(filteredRows.length / rowsPerPage);
        if (currentPage < tp) { currentPage++; updateTable(); }
    });

    updateTable();
</script>
@endpush
@endsection