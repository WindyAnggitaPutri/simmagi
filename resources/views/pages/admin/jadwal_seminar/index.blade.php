@extends('layouts.admin')

@section('title', 'Jadwal Seminar Magang')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Jadwal Seminar Magang</h1>
            <p class="text-sm text-gray-500 mt-1">Data jadwal seminar magang seluruh mahasiswa</p>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Jadwal</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalJadwal }}</p>
                </div>
                <!-- <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div> -->
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Dijadwalkan</p>
                    <p class="text-3xl font-bold text-[#b45309] mt-1">{{ $dijadwalkan }}</p>
                </div>
                <!-- <div class="w-12 h-12 bg-[#fffbeb] rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#b45309]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div> -->
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Selesai Seminar</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ $selesai }}</p>
                </div>
                <!-- <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div> -->
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Sudah Dinilai</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $sudahIsiNilai }}</p>
                </div>
                <!-- <div class="w-12 h-12 bg-indigo-50 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div> -->
            </div>
        </div>
    </div>

    {{-- Tabel Data --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="flex items-center shrink-0">
                    <label for="rowsPerPage" class="text-xs text-gray-600 mr-1">Tampilkan:</label>
                    <select id="rowsPerPage" class="border border-gray-300 rounded-lg p-1.5 text-xs focus:ring-orange-500 focus:border-orange-500">
                        <option value="5" selected>5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="semua">Semua</option>
                    </select>
                </div>
                <select id="filterStatus" class="border border-gray-300 rounded-lg p-1.5 text-xs focus:ring-orange-500 focus:border-orange-500 min-w-0">
                    <option value="semua">Semua Status</option>
                    <option value="dijadwalkan">Dijadwalkan</option>
                    <option value="selesai">Selesai</option>
                    <option value="dinilai">Sudah Dinilai</option>
                </select>
                <div class="ml-auto relative shrink-0">
                    <!-- <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg> -->
                    <input type="text" id="searchInput" placeholder="Cari nama, NPM, perusahaan..." class="w-56 border border-gray-300 rounded-lg p-1.5 pl-8 text-xs focus:ring-orange-500 focus:border-orange-500">
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full" id="dataTable">
                <thead>
                    <tr class="bg-[#fffbeb]">
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-16">No</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Mahasiswa</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Prodi</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Perusahaan</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Dosen Pembimbing</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Tanggal</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Waktu</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Ruang</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Status</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbody-jadwal">
                    @forelse($jadwal as $index => $item)
                        <tr class="border border-gray-300 font-semibold text-gray-900 transition jadwal-row"
                            data-status="{{ $item->status_semgang }}"
                            data-dinilai="{{ $item->rata_rata ? 'ya' : 'tidak' }}"
                            data-search="{{ strtolower(($item->mahasiswa->nama ?? '') . ' ' . ($item->mahasiswa->npm ?? '') . ' ' . ($item->perusahaan->nama_perusahaan ?? '') . ' ' . ($item->dosenPembimbing->dosen->nama ?? '')) }}">
                            <td class="border border-gray-300 p-3 font-semibold text-gray-900 text-center nomor-urut">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 p-3 px-5 py-3.5">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $item->mahasiswa->nama ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $item->mahasiswa->npm ?? '-' }}</p>
                                </div>
                            </td>
                            <td class="border border-gray-300 p-3 px-5 py-3.5">
                                <p class="text-sm text-gray-700">{{ $item->mahasiswa->prodi->nama_prodi ?? '-' }}</p>
                            </td>
                            <td class="border border-gray-300 p-3 px-5 py-3.5">
                                <p class="text-sm text-gray-700">{{ $item->perusahaan->nama_perusahaan ?? '-' }}</p>
                            </td>
                            <td class="border border-gray-300 p-3 px-5 py-3.5">
                                <p class="text-sm text-gray-700">{{ $item->dosenPembimbing->dosen->nama ?? '-' }}</p>
                            </td>
                            <td class="border border-gray-300 p-3 px-5 py-3.5">
                                @php
                                    $tgl = $item->tgl_semgang
                                        ? \Carbon\Carbon::parse($item->tgl_semgang)->locale('id')->translatedFormat('d M Y')
                                        : '-';
                                @endphp
                                <p class="text-sm text-gray-700">{{ $tgl }}</p>
                            </td>
                            <td class="border border-gray-300 p-3 px-5 py-3.5">
                                @php
                                    $jamMulai = $item->jam_mulai ? substr($item->jam_mulai, 0, 5) : '-';
                                    $jamSelesai = $item->jam_selesai ? substr($item->jam_selesai, 0, 5) : '';
                                @endphp
                                <p class="text-sm text-gray-700">
                                    {{ $jamMulai }}
                                    @if($jamSelesai)
                                        <span class="text-gray-400">-</span> {{ $jamSelesai }}
                                    @endif
                                </p>
                            </td>
                            <td class="border border-gray-300 p-3 px-5 py-3.5">
                                <p class="text-sm text-gray-700">{{ $item->ruang ?? '-' }}</p>
                            </td>
                            <td class="border border-gray-300 p-3 px-5 py-3.5 text-center">
                                @if($item->status_semgang === 'selesai')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <!-- <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> -->
                                        Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#fffbeb] text-[#b45309]">
                                        <!-- <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> -->
                                        Dijadwalkan
                                    </span>
                                @endif
                            </td>
                                                     <td class="border border-gray-300 p-3 px-5 py-3.5 text-center">
                                @if($item->status_semgang === 'selesai' && $item->rata_rata !== null)
                                    <a href="{{ route('admin.jadwal-seminar.detail', $item->id_jadwal_semgang) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#b45309] hover:bg-[#92400e] text-white text-xs font-medium rounded-lg transition">
                                        <!-- <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg> -->
                                        Lihat
                                    </a>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="noDataRow">
                            <td colspan="10" class="px-5 py-12 text-center">
                                <!-- <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> -->
                                <p class="text-gray-500 font-medium">Belum ada jadwal seminar magang</p>
                                <p class="text-gray-400 text-sm mt-1">Data akan muncul setelah Kaprodi membuat jadwal seminar</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div id="noSearchResults" class="text-center text-sm text-gray-500 py-8 hidden">
                <!-- <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg> -->
                <p class="text-gray-500 font-medium">Data tidak ditemukan</p>
                <p class="text-gray-400 text-sm mt-1">Coba ubah kata kunci pencarian</p>
            </div>
        </div>

        <div class="flex justify-between items-center p-5 border-t border-gray-100">
            <button id="prevPage" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>Previous</button>
            <span id="pageInfo" class="text-sm text-gray-600"></span>
            <button id="nextPage" class="bg-[#b45309] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-[#92400e] transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>Next</button>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var elRowsPerPage = document.getElementById('rowsPerPage');
    var elSearchInput = document.getElementById('searchInput');
    var elFilterStatus = document.getElementById('filterStatus');
    var elPrevPage = document.getElementById('prevPage');
    var elNextPage = document.getElementById('nextPage');
    var elPageInfo = document.getElementById('pageInfo');
    var elNoDataRow = document.getElementById('noDataRow');
    var elNoSearchResults = document.getElementById('noSearchResults');
    var elDataTable = document.getElementById('dataTable');

    var rowsPerPage = 5;
    var currentPage = 1;
    var filteredRows = [];

    function getRowsPerPage() {
        var val = elRowsPerPage.value;
        if (val === 'semua') return 9999;
        return parseInt(val) || 5;
    }

    function updateTable() {
        var rows = document.querySelectorAll('.jadwal-row');
        var searchTerm = elSearchInput.value.toLowerCase();
        var activeStatusFilter = elFilterStatus.value;
        rowsPerPage = getRowsPerPage();

        filteredRows = [];
        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var searchData = (row.getAttribute('data-search') || '').toLowerCase();
            var searchMatch = !searchTerm || searchData.indexOf(searchTerm) !== -1;
            var rowStatus = (row.getAttribute('data-status') || '').trim().toLowerCase();
            var rowDinilai = (row.getAttribute('data-dinilai') || '').trim().toLowerCase();
            var statusMatch = true;
            if (activeStatusFilter === 'dijadwalkan') {
                statusMatch = (rowStatus === 'dijadwalkan');
            } else if (activeStatusFilter === 'selesai') {
                statusMatch = (rowStatus === 'selesai');
            } else if (activeStatusFilter === 'dinilai') {
                statusMatch = (rowDinilai === 'ya');
            }
            if (searchMatch && statusMatch) {
                filteredRows.push(row);
            }
        }

        for (var j = 0; j < rows.length; j++) {
            rows[j].style.display = 'none';
        }

        var totalRows = filteredRows.length;
        var totalPages = Math.ceil(totalRows / rowsPerPage);
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        var start = (currentPage - 1) * rowsPerPage;
        var end = Math.min(start + rowsPerPage, totalRows);

        for (var k = start; k < end; k++) {
            filteredRows[k].style.display = '';
            var noCell = filteredRows[k].querySelector('.nomor-urut');
            if (noCell) noCell.textContent = k + 1;
        }

        elPrevPage.disabled = (currentPage === 1);
        elNextPage.disabled = (currentPage === totalPages || totalRows === 0);
        elPageInfo.textContent = totalRows > 0 ? 'Page ' + currentPage + ' of ' + totalPages : '';

        if (elNoDataRow) {
            elNoDataRow.style.display = (rows.length === 0) ? '' : 'none';
        }

        if (totalRows === 0 && rows.length > 0) {
            elNoSearchResults.classList.remove('hidden');
            elDataTable.style.display = 'none';
        } else {
            elNoSearchResults.classList.add('hidden');
            elDataTable.style.display = '';
        }
    }

    elSearchInput.addEventListener('input', function() { currentPage = 1; updateTable(); });
    elRowsPerPage.addEventListener('change', function() { currentPage = 1; updateTable(); });
    elFilterStatus.addEventListener('change', function() { currentPage = 1; updateTable(); });
    elPrevPage.addEventListener('click', function() { if (currentPage > 1) { currentPage--; updateTable(); } });
    elNextPage.addEventListener('click', function() { var tp = Math.ceil(filteredRows.length / rowsPerPage); if (currentPage < tp) { currentPage++; updateTable(); } });

    updateTable();
});
</script>
@endpush