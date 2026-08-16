@extends('layouts.admin')

@section('title', 'Monitoring & Logbook')
@section('page-title', 'Monitoring & Logbook')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div>
        <h2 class="text-center text-4xl font-bold text-gray-800 mb-6">.:: Monitoring & Logbook ::.</h2>
    </div>

    {{-- Statistik Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Mahasiswa Magang</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalMahasiswa ?? 0 }}</p>
                </div>
                <!-- <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div> -->
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Logbook Pending</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $totalPending ?? 0 }}</p>
                </div>
                <!-- <div class="w-12 h-12 bg-yellow-50 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div> -->
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Logbook Disetujui</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ $totalDisetujui ?? 0 }}</p>
                </div>
                <!-- <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div> -->
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Logbook Ditolak</p>
                    <p class="text-3xl font-bold text-red-600 mt-1">{{ $totalDitolak ?? 0 }}</p>
                </div>
                <!-- <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div> -->
            </div>
        </div>
    </div>

    {{-- Tabel Data --}}
    <div class="bg-white shadow-md p-4 rounded-lg">
        {{-- Filter Bar - Satu Baris --}}
        <div class="flex items-center gap-2 mb-4">
            <div class="flex items-center shrink-0">
                <label for="rowsPerPage" class="text-xs text-gray-600 mr-1">Tampilkan:</label>
                <select id="rowsPerPage" class="border border-gray-300 rounded p-1.5 text-xs focus:ring-orange-500 focus:border-orange-500">
                    <option value="5" selected>5</option>
                    <option value="10">10</option>
                    <option value="15">15</option>
                </select>
            </div>
            <select id="filterProdi" class="border border-gray-300 rounded p-1.5 text-xs focus:ring-orange-500 focus:border-orange-500 min-w-0">
                <option value="">Semua Prodi</option>
                @foreach($prodis as $prodi)
                    <option value="{{ $prodi->id_prodi }}">{{ $prodi->nama_prodi }}</option>
                @endforeach
            </select>
            <select id="filterDosen" class="border border-gray-300 rounded p-1.5 text-xs focus:ring-orange-500 focus:border-orange-500 min-w-0">
                <option value="">Semua Dosen</option>
                @foreach($dosens as $dosen)
                    <option value="{{ $dosen->id_dosen }}">{{ $dosen->nama }}</option>
                @endforeach
            </select>
            <select id="filterStatus" class="border border-gray-300 rounded p-1.5 text-xs focus:ring-orange-500 focus:border-orange-500 min-w-0">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="disetujui">Disetujui</option>
                <option value="ditolak">Ditolak</option>
            </select>
            <div class="ml-auto relative shrink-0">
                <!-- <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg> -->
                <input type="text" id="searchInput" placeholder="Cari..." class="w-44 border border-gray-300 rounded p-1.5 pl-8 text-xs focus:ring-orange-500 focus:border-orange-500">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300" id="dataTable">
                <thead class="bg-[#fffbeb]">
                    <tr>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-16">No</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase min-w-[180px]">Perusahaan</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase min-w-[160px]">Mahasiswa</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase min-w-[120px]">Prodi</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase min-w-[150px]">Dosen Pembimbing</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-20">Minggu Ke</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-20">Total Hari</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-20">Pending</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-20">Disetujui</th>
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-20">Ditolak</th>
                        <!-- <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-28">Persentase</th> -->
                        <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-16">Aksi</th>
                    </tr>
                </thead>

                @if($groupedLogbook->isEmpty())
                    <tbody>
                        <tr id="noDataRow">
                            <td colspan="12" class="border border-gray-300 p-10 text-center text-gray-400">Belum ada data logbook.</td>
                        </tr>
                    </tbody>
                @else
                    @php $noPerusahaan = 0; @endphp
                    @foreach ($groupedLogbook as $namaPerusahaan => $items)
                        @php
                            $noPerusahaan++;
                            $perusahaanRowspan = $items->count();
                            $showPerusahaan = true;

                            $searchText = strtolower($namaPerusahaan . ' ');
                            $prodiIds = [];
                            $dosenIds = [];
                            $statusList = [];
                            foreach ($items as $si) {
                                $searchText .= ($si->mahasiswa->nama ?? '') . ' ' . ($si->mahasiswa->npm ?? '') . ' ';
                                $searchText .= ($si->dosen->nama ?? '') . ' ';
                                $searchText .= ($si->mahasiswa->prodi->nama_prodi ?? '') . ' ';
                                if ($si->mahasiswa->prodi) $prodiIds[] = $si->mahasiswa->prodi->id_prodi;
                                if ($si->id_dosen) $dosenIds[] = $si->id_dosen;
                                if (($si->logbook_pending ?? 0) > 0 && !in_array('pending', $statusList)) $statusList[] = 'pending';
                                if (($si->logbook_disetujui ?? 0) > 0 && !in_array('disetujui', $statusList)) $statusList[] = 'disetujui';
                                if (($si->logbook_ditolak ?? 0) > 0 && !in_array('ditolak', $statusList)) $statusList[] = 'ditolak';
                            }
                            $searchText = trim($searchText);
                            $prodiData = implode(',', array_unique($prodiIds));
                            $dosenData = implode(',', array_unique($dosenIds));
                            $statusData = implode(',', $statusList);
                        @endphp

                        <tbody class="group-row" 
                               data-search="{{ $searchText }}" 
                               data-prodi="{{ $prodiData }}" 
                               data-dosen="{{ $dosenData }}" 
                               data-status="{{ $statusData }}">
                            @foreach ($items as $item)
                                <tr class="data-row hover:bg-[#fffbeb]/30 transition">
                                    @if($showPerusahaan)
                                        <td class="border border-gray-300 p-3 text-center text-sm text-gray-600 align-top nomor-urut" rowspan="{{ $perusahaanRowspan }}">
                                            {{ $noPerusahaan }}
                                        </td>
                                        <td class="border border-gray-300 p-3 text-sm align-top" rowspan="{{ $perusahaanRowspan }}">
                                            <span class="font-semibold text-gray-800 text-sm">{{ $namaPerusahaan }}</span>
                                        </td>
                                        @php $showPerusahaan = false; @endphp
                                    @endif

                                    <td class="border border-gray-300 p-3">
                                        <p class="text-sm font-medium text-gray-800">{{ $item->mahasiswa->nama ?? '-' }}</p>
                                        <p class="text-xs text-gray-500">{{ $item->mahasiswa->npm ?? '-' }}</p>
                                    </td>
                                    <td class="border border-gray-300 p-3 text-sm text-gray-600">{{ $item->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                                    <td class="border border-gray-300 p-3 text-sm text-gray-600">{{ $item->dosen->nama ?? '-' }}</td>
                                    <td class="border border-gray-300 p-3 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600 font-semibold text-xs">
                                            {{ $item->max_minggu ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="border border-gray-300 p-3 text-center text-sm font-medium text-gray-600">{{ $item->total_logbook ?? 0 }}</td>
                                    <td class="border border-gray-300 p-3 text-center">
                                        @if(($item->logbook_pending ?? 0) > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ $item->logbook_pending }}</span>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="border border-gray-300 p-3 text-center">
                                        @if(($item->logbook_disetujui ?? 0) > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $item->logbook_disetujui }}</span>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="border border-gray-300 p-3 text-center">
                                        @if(($item->logbook_ditolak ?? 0) > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ $item->logbook_ditolak }}</span>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <!-- <td class="border border-gray-300 p-3">
                                        <div class="flex flex-col items-center gap-1">
                                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                                <!-- <div class="bg-[#b45309] h-2 rounded-full" style="width: {{ $item->progress_percentage ?? 0 }}%"></div> -->
                                            <!-- </div> -->
                                            <!-- <span class="text-xs text-gray-500">{{ number_format($item->progress_percentage ?? 0, 1) }}%</span> -->
                                        <!-- </div>
                                    </td>  -->
                                    <td class="border border-gray-300 p-3 text-center">
                                        <a href="{{ route('admin.logbook.detail_logbook', $item->id_dosen_pembimbing) }}" 
                                           class="inline-flex items-center px-3 py-1.5 bg-[#b45309] text-white text-xs font-medium rounded-lg hover:bg-[#92400e] transition"
                                           title="Lihat Detail Logbook">
                                            <!-- <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg> -->
                                            Lihat
                                        </a>
                                    </td>
                                </tr>
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
        const prodiFilter = document.getElementById('filterProdi').value;
        const dosenFilter = document.getElementById('filterDosen').value;
        const statusFilter = document.getElementById('filterStatus').value;

        filteredGroups = Array.from(groups).filter(group => {
            const searchMatch = !searchTerm || group.getAttribute('data-search').includes(searchTerm);
            const prodiData = group.getAttribute('data-prodi');
            const prodiMatch = !prodiFilter || (prodiData && prodiData.split(',').includes(prodiFilter));
            const dosenData = group.getAttribute('data-dosen');
            const dosenMatch = !dosenFilter || (dosenData && dosenData.split(',').includes(dosenFilter));
            const statusData = group.getAttribute('data-status');
            const statusMatch = !statusFilter || (statusData && statusData.split(',').includes(statusFilter));
            return searchMatch && prodiMatch && dosenMatch && statusMatch;
        });

        groups.forEach(g => g.style.display = 'none');

        const totalGroups = filteredGroups.length;
        const totalPages = Math.ceil(totalGroups / rowsPerPage);
        currentPage = Math.min(currentPage, Math.max(1, totalPages));

        const start = (currentPage - 1) * rowsPerPage;
        const visibleGroups = filteredGroups.slice(start, start + rowsPerPage);

        visibleGroups.forEach((group, index) => {
            group.style.display = '';
            const noCell = group.querySelector('.nomor-urut');
            if (noCell) noCell.textContent = start + index + 1;
        });

        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages || totalGroups === 0;
        pageInfo.textContent = totalGroups > 0 ? 'Page ' + currentPage + ' of ' + totalPages : '';

        if (noDataRow) noDataRow.style.display = groups.length === 0 ? '' : 'none';
        noSearchResults.classList.toggle('hidden', totalGroups > 0 || groups.length === 0);

        const table = document.getElementById('dataTable');
        table.style.display = (totalGroups === 0 && groups.length > 0) ? 'none' : '';
    }

    document.getElementById('searchInput').addEventListener('input', function() { currentPage = 1; updateTable(); });
    document.getElementById('rowsPerPage').addEventListener('change', function() { rowsPerPage = parseInt(this.value); currentPage = 1; updateTable(); });
    document.getElementById('filterProdi').addEventListener('change', function() { currentPage = 1; updateTable(); });
    document.getElementById('filterDosen').addEventListener('change', function() { currentPage = 1; updateTable(); });
    document.getElementById('filterStatus').addEventListener('change', function() { currentPage = 1; updateTable(); });
    document.getElementById('prevPage').addEventListener('click', function() { if (currentPage > 1) { currentPage--; updateTable(); } });
    document.getElementById('nextPage').addEventListener('click', function() { var tp = Math.ceil(filteredGroups.length / rowsPerPage); if (currentPage < tp) { currentPage++; updateTable(); } });
    updateTable();
</script>
@endpush