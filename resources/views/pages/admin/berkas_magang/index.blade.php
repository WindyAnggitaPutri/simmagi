@extends('layouts.admin')

@section('title', 'SIMMAGI - Kelola Berkas Magang')
@section('page-title', 'Data Berkas Magang')

@section('content')
<h2 class="text-center text-4xl font-bold text-gray-800 mb-6">.:: Kelola Berkas Magang ::.</h2>

<div class="bg-white shadow-md p-4 rounded-lg">
    <div class="flex justify-between mb-4">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.berkas-magang.create') }}" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 flex items-center transition">
                <!-- <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> --> 
                Upload Berkas Baru
            </a>
            <div class="flex items-center">
                <label for="rowsPerPage" class="mr-2 text-sm text-gray-600">Tampilkan:</label>
                <select id="rowsPerPage" class="border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="5" selected>5</option>
                    <option value="10">10</option>
                    <option value="15">15</option>
                </select>
            </div>
        </div>
        <form id="searchForm" action="{{ route('admin.berkas-magang.index') }}" method="GET" class="w-1/3">
            <div class="relative">
                <!-- <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg> -->
                <input type="text" name="search" id="searchInput" placeholder="Cari nama berkas, jenis..." value="{{ request('search') }}" class="w-full border border-gray-300 rounded-lg p-2 pl-10 focus:ring-orange-500 focus:border-orange-500">
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300" id="dataTable">
            <thead class="bg-[#fffbeb]">
                <tr>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-16">No</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Nama Berkas</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Jenis Berkas</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Deskripsi</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-40">File</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-40">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @if($berkasMagang->isEmpty())
                    <tr id="noDataRow">
                        <td colspan="6" class="border border-gray-300 p-10 text-center text-gray-400">Belum ada berkas yang diupload.</td>
                    </tr>
                @else
                    @foreach ($berkasMagang as $item)
                        <tr class="data-row hover:bg-[#fffbeb]/30 transition">
                            <td class="border border-gray-300 p-3 text-center text-gray-500 font-medium nomor-urut">{{ $loop->iteration }}</td>
                            <td class="border border-gray-300 p-3 font-semibold text-gray-900">{{ $item->nama_berkas }}</td>
                            <td class="border border-gray-300 p-3 text-center">
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-1 rounded">
                                    {{ $item->jenis_berkas }}
                                </span>
                            </td>
                            <td class="border border-gray-300 p-3 text-sm text-gray-600 max-w-xs truncate">{{ $item->deskripsi ?? '-' }}</td>
                            <td class="border border-gray-300 p-3 text-center">
                                <a href="{{ asset('storage/' . $item->file) }}" target="_blank" class="text-blue-600 hover:underline text-xs font-semibold">
                                    Lihat File
                                </a>
                            </td>
                            <td class="border border-gray-300 p-3 text-center">
                                <a href="{{ route('admin.berkas-magang.edit', $item->id_berkas_magang) }}" class="text-orange-500 hover:underline mr-3 font-medium text-sm">Edit</a>
                                <form action="{{ route('admin.berkas-magang.destroy', $item->id_berkas_magang) }}" method="POST" class="inline-block delete-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline font-medium text-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        <div id="noSearchResults" class="text-center text-sm text-gray-500 mt-4 hidden">Data tidak ditemukan</div>
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
        Swal.fire({ icon: 'success', title: 'Sukses!', text: successMessage, timer: 2000, showConfirmButton: true, confirmButtonText: 'OK', confirmButtonColor: '#f97316' });
    }

    document.querySelectorAll('.delete-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const namaBerkas = form.closest('tr').querySelector('td:nth-child(2)').textContent.trim();
            Swal.fire({
                title: 'Peringatan!',
                html: '<p class="text-red-600 font-semibold">Jika anda menghapus berkas <strong>"' + namaBerkas + '"</strong> maka data pada:</p><ul class="text-left mt-2 text-gray-700 list-disc list-inside"><li>File Berkas di Penyimpanan</li><li>Data Berkas Magang</li></ul><p class="mt-3 text-red-600 font-bold">akan ikut terhapus!</p>',
                icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#f97316', confirmButtonText: 'Iya, Lanjut', cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menghapus...', text: 'Data berkas sedang dihapus.', icon: 'info', showConfirmButton: false, allowOutsideClick: false, didOpen: () => { form.submit(); } });
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

        filteredRows = Array.from(rows).filter(row => {
            return row.cells[1].textContent.toLowerCase().includes(searchTerm) || row.cells[2].textContent.toLowerCase().includes(searchTerm) || row.cells[3].textContent.toLowerCase().includes(searchTerm);
        });

        rows.forEach(row => row.style.display = 'none');
        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        currentPage = Math.min(currentPage, Math.max(1, totalPages));

        filteredRows.slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage).forEach((row, index) => {
            row.style.display = '';
            const nomorCell = row.querySelector('.nomor-urut');
            if (nomorCell) {
                nomorCell.textContent = (currentPage - 1) * rowsPerPage + index + 1;
            }
        });

        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages || totalRows === 0;
        pageInfo.textContent = totalRows > 0 ? 'Page ' + currentPage + ' of ' + totalPages : '';

        if (noDataRow) noDataRow.parentElement.style.display = rows.length === 0 ? '' : 'none';
        noSearchResults.classList.toggle('hidden', totalRows > 0 || rows.length === 0);

        const table = document.getElementById('dataTable');
        table.style.display = (totalRows === 0 && rows.length > 0) ? 'none' : '';
    }

    document.getElementById('searchForm').addEventListener('submit', function(e) { e.preventDefault(); });
    document.getElementById('searchInput').addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); e.stopPropagation(); } });
    document.getElementById('searchInput').addEventListener('input', function() { currentPage = 1; updateTable(); });
    document.getElementById('rowsPerPage').addEventListener('change', function() { rowsPerPage = parseInt(this.value); currentPage = 1; updateTable(); });
    document.getElementById('prevPage').addEventListener('click', function() { if (currentPage > 1) { currentPage--; updateTable(); } });
    document.getElementById('nextPage').addEventListener('click', function() { var tp = Math.ceil(filteredRows.length / rowsPerPage); if (currentPage < tp) { currentPage++; updateTable(); } });
    updateTable();
</script>
@endpush
@endsection