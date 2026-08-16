@extends('layouts.admin')

@section('title', 'Personal Interview Magang')
@section('page-title', 'Data Personal Interview Magang')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div>
        <h2 class="text-center text-4xl font-bold text-gray-800 mb-6">.:: Personal Interview Magang ::.</h2>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Perusahaan</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $groupedByPerusahaan->count() }}</p>
                </div>
                <!-- <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div> -->
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Interview A Terisi</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ $interviewAList->count() }}</p>
                </div>
                <!-- <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div> -->
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Interview B Terisi</p>
                    <p class="text-3xl font-bold text-[#b45309] mt-1">{{ $interviewBList->count() }}</p>
                </div>
                <!-- <div class="w-12 h-12 bg-[#fffbeb] rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#b45309]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div> -->
            </div>
        </div>
    </div>

    {{-- Tabel Data --}}
    <div class="bg-white shadow-md p-4 rounded-lg">
        <div class="flex justify-between mb-4">
            <div class="flex items-center space-x-4">
                <div class="flex items-center">
                    <label for="rowsPerPage" class="mr-2 text-sm text-gray-600">Tampilkan:</label>
                    <select id="rowsPerPage" class="border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="5" selected>5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                    </select>
                </div>
            </div>
            <form id="searchForm" action="{{ route('admin.personal-interview.index') }}" method="GET" class="w-1/3">
                <div class="relative">
                    <!-- <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg> -->
                    <input type="text" name="search" id="searchInput" placeholder="Cari perusahaan, mahasiswa, dosen..." value="{{ request('search') }}" class="w-full border border-gray-300 rounded-lg p-2 pl-10 focus:ring-orange-500 focus:border-orange-500">
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300" id="dataTable">
                <thead class="bg-[#fffbeb]">
                    <tr>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-16">No</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase min-w-[180px]">Perusahaan</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase min-w-[160px]">Mahasiswa</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase min-w-[160px]">Dosen Pembimbing</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase min-w-[160px]">Dosen Visitasi</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-28">Interview A</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-28">Interview B</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-24">Aksi</th>
                    </tr>
                </thead>

                @if($groupedByPerusahaan->isEmpty())
                    <tbody>
                        <tr id="noDataRow">
                            <td colspan="8" class="border border-gray-300 p-10 text-center text-gray-400">Belum ada data personal interview.</td>
                        </tr>
                    </tbody>
                @else
                    @php $noPerusahaan = 0; @endphp
                    @foreach ($groupedByPerusahaan as $idPerusahaan => $items)
                        @php
                            $noPerusahaan++;
                            $groupedByDosen = $items->groupBy('id_dosen');
                            $perusahaanRowspan = $items->count();
                            $showPerusahaan = true;

                            // Bangun teks pencarian untuk seluruh grup perusahaan ini
                            $searchText = '';
                            foreach ($items as $si) {
                                $searchText .= ($si->perusahaan->nama_perusahaan ?? '') . ' ';
                                $searchText .= ($si->mahasiswa->nama ?? '') . ' ' . ($si->mahasiswa->npm ?? '') . ' ';
                                $searchText .= ($si->dosenPembimbing->dosen->nama ?? '') . ' ' . ($si->dosenPembimbing->dosen->nidn ?? '') . ' ';
                                $searchText .= ($si->dosen->nama ?? '') . ' ' . ($si->dosen->nidn ?? '') . ' ';
                            }
                            $searchText = trim(strtolower($searchText));
                        @endphp

                        {{-- Setiap perusahaan dibungkus <tbody> sendiri agar rowspan aman saat paginasi --}}
                        <tbody class="group-row" data-search-text="{{ $searchText }}">
                            @foreach($groupedByDosen as $idDosen => $dosenItems)
                                @php
                                    $dosenRowspan = $dosenItems->count();
                                    $showDosen = true;
                                    $firstItem = $dosenItems->first();
                                    $hasA = isset($interviewAList[$firstItem->id_dosen_visitasi]);
                                    $hasB = isset($interviewBList[$firstItem->id_dosen_visitasi]);
                                @endphp

                                @foreach($dosenItems as $item)
                                    <tr class="data-row hover:bg-[#fffbeb]/30 transition">

                                        {{-- No: 1 angka per perusahaan, rowspan seluruh grup --}}
                                        @if($showPerusahaan)
                                            <td class="border border-gray-300 p-3 text-center text-sm text-gray-600 align-top nomor-urut" rowspan="{{ $perusahaanRowspan }}">
                                                {{ $noPerusahaan }}
                                            </td>

                                            {{-- Perusahaan: rowspan seluruh grup --}}
                                            <td class="border border-gray-300 p-3 text-sm align-top" rowspan="{{ $perusahaanRowspan }}">
                                                <span class="font-semibold text-gray-800 text-sm">{{ $item->perusahaan->nama_perusahaan ?? '-' }}</span>
                                            </td>
                                            @php $showPerusahaan = false; @endphp
                                        @endif

                                        {{-- Mahasiswa: per baris --}}
                                        <td class="border border-gray-300 p-3">
                                            <p class="text-sm font-medium text-gray-800">{{ $item->mahasiswa->nama ?? '-' }}</p>
                                            <p class="text-xs text-gray-500">{{ $item->mahasiswa->npm ?? '-' }}</p>
                                        </td>

                                        {{-- Dosen Pembimbing: per baris --}}
                                        <td class="border border-gray-300 p-3">
                                            <p class="text-sm text-gray-700">{{ $item->dosenPembimbing->dosen->nama ?? '-' }}</p>
                                            <p class="text-xs text-gray-400">{{ $item->dosenPembimbing->dosen->nidn ?? '-' }}</p>
                                        </td>

                                        {{-- Dosen Visitasi: rowspan per dosen --}}
                                        @if($showDosen)
                                            <td class="border border-gray-300 p-3 align-top" rowspan="{{ $dosenRowspan }}">
                                                <p class="text-sm text-gray-700">{{ $item->dosen->nama ?? '-' }}</p>
                                                <p class="text-xs text-gray-400">{{ $item->dosen->nidn ?? '-' }}</p>
                                            </td>

                                            {{-- Interview A: rowspan per dosen --}}
                                            <td class="border border-gray-300 p-3 text-center align-top" rowspan="{{ $dosenRowspan }}">
                                                @if($hasA)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <!-- <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> -->
                                                        Terisi
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Belum</span>
                                                @endif
                                            </td>

                                            {{-- Interview B: rowspan per dosen --}}
                                            <td class="border border-gray-300 p-3 text-center align-top" rowspan="{{ $dosenRowspan }}">
                                                @if($hasB)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <!-- <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> -->
                                                        Terisi
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Belum</span>
                                                @endif
                                            </td>

                                            {{-- Aksi: rowspan per dosen --}}
                                            <td class="border border-gray-300 p-3 align-top" rowspan="{{ $dosenRowspan }}">
                                                <div class="flex items-center justify-center">
                                                    @if($hasA || $hasB)
                                                        <a href="{{ route('admin.personal-interview.detail', $idPerusahaan) }}" 
                                                           class="inline-flex items-center px-3 py-1.5 bg-[#b45309] text-white text-xs font-medium rounded-lg hover:bg-[#92400e] transition">Lihat</a>
                                                    @else
                                                        <span class="text-xs text-gray-400">-</span>
                                                    @endif
                                                </div>
                                            </td>
                                            
                                            @php $showDosen = false; @endphp
                                        @endif
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    @endforeach
                @endif
            </table>
            <div id="noSearchResults" class="text-center text-sm text-gray-500 mt-4 hidden">Data tidak ditemukan</div>
        </div>

        <div class="flex justify-between mt-4">
            <button id="prevPage" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg disabled:opacity-50" disabled>Previous</button>
            <span id="pageInfo" class="text-sm text-gray-900 self-center"></span>
            <button id="nextPage" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 disabled:opacity-50" disabled>Next</button>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    let rowsPerPage = parseInt(document.getElementById('rowsPerPage').value);
    let currentPage = 1;
    let filteredGroups = [];

    function updateTable() {
        const groups = document.querySelectorAll('#dataTable .group-row');
        const noDataRow = document.getElementById('noDataRow');
        const noSearchResults = document.getElementById('noSearchResults');
        const prevButton = document.getElementById('prevPage');
        const nextButton = document.getElementById('nextPage');
        const pageInfo = document.getElementById('pageInfo');
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();

        // Filter berdasarkan grup perusahaan (bukan per baris)
        filteredGroups = Array.from(groups).filter(group => {
            return group.getAttribute('data-search-text').includes(searchTerm);
        });

        // Sembunyikan semua grup
        groups.forEach(g => g.style.display = 'none');

        const totalGroups = filteredGroups.length;
        const totalPages = Math.ceil(totalGroups / rowsPerPage);
        currentPage = Math.min(currentPage, Math.max(1, totalPages));

        // Tampilkan grup sesuai halaman
        const start = (currentPage - 1) * rowsPerPage;
        const visibleGroups = filteredGroups.slice(start, start + rowsPerPage);

        visibleGroups.forEach((group, index) => {
            group.style.display = '';
            // Update nomor urut perusahaan
            const noCell = group.querySelector('.nomor-urut');
            if (noCell) {
                noCell.textContent = start + index + 1;
            }
        });

        // Update tombol pagination
        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages || totalGroups === 0;
        pageInfo.textContent = totalGroups > 0 ? 'Page ' + currentPage + ' of ' + totalPages : '';

        // Handle no data state
        if (noDataRow) noDataRow.style.display = groups.length === 0 ? '' : 'none';
        noSearchResults.classList.toggle('hidden', totalGroups > 0 || groups.length === 0);

        const table = document.getElementById('dataTable');
        table.style.display = (totalGroups === 0 && groups.length > 0) ? 'none' : '';
    }

    document.getElementById('searchForm').addEventListener('submit', function(e) { e.preventDefault(); });
    document.getElementById('searchInput').addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); e.stopPropagation(); } });
    document.getElementById('searchInput').addEventListener('input', function() { currentPage = 1; updateTable(); });
    document.getElementById('rowsPerPage').addEventListener('change', function() { rowsPerPage = parseInt(this.value); currentPage = 1; updateTable(); });
    document.getElementById('prevPage').addEventListener('click', function() { if (currentPage > 1) { currentPage--; updateTable(); } });
    document.getElementById('nextPage').addEventListener('click', function() { var tp = Math.ceil(filteredGroups.length / rowsPerPage); if (currentPage < tp) { currentPage++; updateTable(); } });
    updateTable();
</script>
@endpush