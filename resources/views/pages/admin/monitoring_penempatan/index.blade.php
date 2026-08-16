@extends('layouts.admin')

@section('title', 'SIMMAGI - Monitoring Penempatan Magang')
@section('page-title', 'Monitoring Penempatan')

@section('content')
<h2 class="text-center text-4xl font-bold text-gray-800 mb-2">.:: Monitoring Penempatan Magang ::.</h2>
<p class="text-center text-gray-500 mb-8">Daftar mahasiswa yang telah diterima perusahaan dan resmi ditugaskan</p>

<div class="bg-white shadow-md p-4 rounded-lg">
    <div class="flex justify-between mb-4">
        <div class="flex items-center space-x-4">
            <div class="flex items-center">
                <label for="rowsPerPage" class="mr-2 text-sm text-gray-600">Tampilkan:</label>
                <select id="rowsPerPage" class="border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="15">15</option>
                </select>
            </div>
        </div>
        <form id="searchForm" action="#" method="GET" class="w-1/3">
            <input type="text" name="search" id="searchInput" placeholder="Cari nama mahasiswa, perusahaan..." value="" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500">
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300" id="dataTable">
            <thead class="bg-[#fffbeb]">
                <tr>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-16">No</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Nama Mahasiswa</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">NPM</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Perusahaan</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-40">Status</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-40">Surat Balasan</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @php $no = 1; @endphp
                @forelse ($pengajuans as $p)
                    @foreach ($p->mahasiswa as $pm)
                        @if($pm->mahasiswa)
                        <tr class="data-row hover:bg-[#fffbeb]/30 transition">
                            <td class="border border-gray-300 p-3 text-center text-sm text-gray-900">{{ $no++ }}</td>
                            <td class="border border-gray-300 p-3 text-sm text-gray-900 font-medium">{{ $pm->mahasiswa->nama ?? '-' }}</td>
                            <td class="border border-gray-300 p-3 text-sm text-gray-900 font-mono">{{ $pm->mahasiswa->npm ?? '-' }}</td>
                            
                            {{-- KOLOM NAMA PERUSAHAAN --}}
                            <td class="border border-gray-300 p-3 text-sm text-gray-900">{{ $p->perusahaan->nama_perusahaan ?? '-' }}</td>
                            
                            {{-- KOLOM STATUS PERUSAHAAN --}}
                            <td class="border border-gray-300 p-3 text-center">
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold border border-green-300">
                                DITERIMA
                                </span>
                            </td>

                            {{-- TOMBOL LIHAT SURAT BALASAN --}}
                            <td class="border border-gray-300 p-3 text-center text-sm">
                                @if ($pm->surat_balasan)
                                    <a href="{{ asset('storage/surat_balasan/' . $pm->surat_balasan) }}" target="_blank" class="text-orange-500 hover:underline font-medium">
                                    Lihat Surat
                                    </a>
                                @else
                                    <span class="text-gray-400 text-xs">Tidak ada</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                    @endforeach
                @empty
                <tr id="noDataRow">
                    <td colspan="6" class="border border-gray-300 p-3 text-center text-sm text-gray-900">Belum ada data mahasiswa yang diterima di perusahaan saat ini.</td>
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
        
        // Filter berdasarkan Nama Mahasiswa (Kolom 1) & Perusahaan (Kolom 3)
        filteredRows = Array.from(rows).filter(row => {
            return row.cells[1].textContent.toLowerCase().includes(searchTerm) || 
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