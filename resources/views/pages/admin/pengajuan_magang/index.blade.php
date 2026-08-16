@extends('layouts.admin')

@section('title', 'SIMMAGI - Data Pengajuan Magang')
@section('page-title', 'Data Pengajuan Magang')

@section('content')
<h2 class="text-center text-4xl font-bold text-gray-800 mb-6">.:: Data Pengajuan Magang ::.</h2>

<div class="bg-white shadow-md p-4 rounded-lg">
    <div class="flex justify-between mb-4">
        <div class="flex items-center space-x-4">
            {{-- HAPUS TOMBOL TAMBAH KARENA ADMIN GABISA TAMBAH --}}
            <div class="flex items-center">
                <label for="rowsPerPage" class="mr-2 text-sm text-gray-600">Tampilkan:</label>
                <select id="rowsPerPage" class="border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="15">15</option>
                </select>
            </div>
        </div>
        <form id="searchForm" action="{{ route('admin.pengajuan-magang.index') }}" method="GET" class="w-1/3">
            <input type="text" name="search" id="searchInput" placeholder="Cari nama mahasiswa, perusahaan..." value="{{ request('search') }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500">
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300" id="dataTable">
            <thead class="bg-[#fffbeb]">
                <tr>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-16">No</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Tgl Pengajuan</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Nama Mahasiswa</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Perusahaan</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Status Alur</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-32">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse ($pengajuans as $index => $p)
                <tr class="data-row hover:bg-[#fffbeb]/30 transition">
                    <td class="border border-gray-300 p-3 text-center text-sm text-gray-900">{{ $index + 1 }}</td>
                    <td class="border border-gray-300 p-3 text-center text-sm text-gray-900">{{ \Carbon\Carbon::parse($p->tgl_pengajuan)->format('d M Y') }}</td>
                    <td class="border border-gray-300 p-3 text-sm text-gray-900">
                        @foreach($p->mahasiswa as $m)
                            <span class="block">{{ $m->mahasiswa->nama ?? '-' }}</span>
                        @endforeach
                    </td>
                    <td class="border border-gray-300 p-3 text-sm text-gray-900">{{ $p->perusahaan->nama_perusahaan ?? '-' }}</td>
                    <td class="border border-gray-300 p-3 text-center">
                        <span class="px-2 py-1 rounded text-xs font-medium border {{ $p->status_alur['color'] ?? 'bg-gray-100 text-gray-600 border-gray-300' }}">
                            {{ $p->status_alur['text'] ?? 'Tidak Diketahui' }}
                        </span>
                    </td>
                    <td class="border border-gray-300 p-3 text-center text-sm">
                        <a href="{{ route('admin.pengajuan-magang.detail', $p->id_pengajuan) }}" class="text-orange-500 hover:underline font-medium">Detail</a>
                    </td>
                </tr>
                @empty
                <tr id="noDataRow">
                    <td colspan="6" class="border border-gray-300 p-3 text-center text-sm text-gray-900">Tidak ada data ditemukan</td>
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
        
        // Filter berdasarkan Nama Mahasiswa, Nama Perusahaan, dan Tanggal
        filteredRows = Array.from(rows).filter(row => {
            // Kolom 1 = Tgl, Kolom 2 = Nama Mhs, Kolom 3 = Perusahaan
            return row.cells[1].textContent.toLowerCase().includes(searchTerm) || 
                   row.cells[2].textContent.toLowerCase().includes(searchTerm) || 
                   row.cells[3].textContent.toLowerCase().includes(searchTerm);
        });
        
        rows.forEach(row => row.style.display = 'none'); // Sembunyikan semua dulu
        
        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        currentPage = Math.min(currentPage, Math.max(1, totalPages));
        
        // Tampilkan hanya baris yang ada di halaman saat ini
        filteredRows.slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage).forEach(row => row.style.display = '');
        
        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages || totalRows === 0;
        pageInfo.textContent = totalRows > 0 ? 'Page ' + currentPage + ' of ' + totalPages : '';
        
        if (noDataRow) noDataRow.parentElement.style.display = rows.length === 0 ? '' : 'none';
        noSearchResults.classList.toggle('hidden', totalRows > 0 || rows.length === 0);
    }

    document.getElementById('searchForm').addEventListener('submit', function(e) { e.preventDefault(); });
    document.getElementById('searchInput').addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); e.stopPropagation(); } });
    document.getElementById('searchInput').addEventListener('input', function() { currentPage = 1; updateTable(); });
    document.getElementById('rowsPerPage').addEventListener('change', function() { rowsPerPage = parseInt(this.value); currentPage = 1; updateTable(); });
    document.getElementById('prevPage').addEventListener('click', function() { if (currentPage > 1) { currentPage--; updateTable(); } });
    document.getElementById('nextPage').addEventListener('click', function() { var tp = Math.ceil(filteredRows.length / rowsPerPage); if (currentPage < tp) { currentPage++; updateTable(); } });
    
    updateTable(); // Panggil saat pertama kali load
</script> 
@endpush
@endsection