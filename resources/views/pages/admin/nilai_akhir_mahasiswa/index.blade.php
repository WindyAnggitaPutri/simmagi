@extends('layouts.admin')

@section('title', 'SIMMAGI - Nilai Akhir Mahasiswa')
@section('page-title', 'Nilai Akhir Mahasiswa')

@section('content')
<h2 class="text-center text-4xl font-bold text-gray-800 mb-2">.:: Nilai Akhir Mahasiswa ::.</h2>
<p class="text-center text-sm text-gray-500 mb-6">
    Rekapitulasi: <span class="font-semibold text-orange-600">Perusahaan {{ $bobotPerusahaan }}%</span> + <span class="font-semibold text-blue-600">Dosen Pembimbing {{ $bobotPembimbing }}%</span>
</p>

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
                <input type="text" id="searchInput" placeholder="Cari nama, NPM, perusahaan..." class="w-full border border-gray-300 rounded-lg p-2 pl-10 focus:ring-orange-500 focus:border-orange-500">
            </div>
        </div>
    </div>

    {{-- Tabel Data --}}
    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300" id="dataTable">
            <thead class="bg-[#fffbeb]">
                <tr>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-12">No</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">NIM</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Nama Mahasiswa</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Perusahaan</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">
                        Nilai Perusahaan<br><span class="text-orange-600 normal-case">({{ $bobotPerusahaan }}%)</span>
                    </th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">
                        Nilai Dospem<br><span class="text-blue-600 normal-case">({{ $bobotPembimbing }}%)</span>
                    </th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Nilai Akhir</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Grade</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Status</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-24">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @if($jadwalSelesai->isEmpty())
                    <tr id="noDataRow">
                        <td colspan="10" class="border border-gray-300 p-10 text-center text-gray-400">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            Belum ada data nilai akhir.
                        </td>
                    </tr>
                @else
                    @foreach ($jadwalSelesai as $data)
                        <tr class="data-row hover:bg-[#fffbeb]/30 transition">
                            <td class="border border-gray-300 p-3 text-center text-gray-500 font-medium row-no">{{ $loop->iteration }}</td>
                            <td class="border border-gray-300 p-3 text-sm text-gray-700">{{ $data->mahasiswa->npm ?? '-' }}</td>
                            <td class="border border-gray-300 p-3 text-sm font-semibold text-gray-900">{{ $data->mahasiswa->nama ?? '-' }}</td>
                            <td class="border border-gray-300 p-3 text-sm text-gray-700">{{ $data->perusahaan->nama_perusahaan ?? '-' }}</td>

                            {{-- Nilai Perusahaan --}}
                            <td class="border border-gray-300 p-3 text-sm text-center">
                                @if($data->nilai_perusahaan !== null)
                                    <span class="font-semibold text-orange-600">{{ number_format($data->nilai_perusahaan, 2) }}</span>
                                @else
                                    <span class="text-red-400 text-xs italic">Belum ada</span>
                                @endif
                            </td>

                            {{-- Nilai Dospem --}}
                            <td class="border border-gray-300 p-3 text-sm text-center">
                                @if($data->nilai_pembimbing !== null)
                                    <span class="font-semibold text-blue-600">{{ number_format($data->nilai_pembimbing, 2) }}</span>
                                @else
                                    <span class="text-red-400 text-xs italic">Belum ada</span>
                                @endif
                            </td>

                            {{-- Nilai Akhir --}}
                            <td class="border border-gray-300 p-3 text-sm text-center">
                                @if($data->nilai_akhir_calc !== null)
                                    <span class="font-bold text-gray-900">{{ number_format($data->nilai_akhir_calc, 2) }}</span>
                                @else
                                    <span class="text-gray-400 text-xs italic">-</span>
                                @endif
                            </td>

                            {{-- Grade --}}
                            <td class="border border-gray-300 p-3 text-center">
                                @if($data->grade_calc)
                                    @php
                                        $gradeColors = [
                                            'A'  => 'bg-green-100 text-green-700',
                                            'AB' => 'bg-green-50 text-green-600',
                                            'B'  => 'bg-blue-100 text-blue-700',
                                            'BC' => 'bg-blue-50 text-blue-600',
                                            'C'  => 'bg-yellow-100 text-yellow-700',
                                            'D'  => 'bg-orange-100 text-orange-700',
                                            'E'  => 'bg-red-100 text-red-700',
                                        ];
                                        $gc = $gradeColors[$data->grade_calc] ?? 'bg-gray-100 text-gray-700';
                                    @endphp
                                    <span class="px-2.5 py-1 rounded text-xs font-bold {{ $gc }}">
                                        {{ $data->grade_calc }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="border border-gray-300 p-3 text-center">
                                @if(! $data->lengkap)
                                    <span class="bg-red-100 text-red-600 text-xs font-medium px-2 py-1 rounded"> Belum Lengkap</span>
                                @elseif($data->sudah_disimpan)
                                    <span class="bg-green-100 text-green-600 text-xs font-medium px-2 py-1 rounded">Diproses</span>
                                @else
                                    <span class="bg-yellow-100 text-yellow-600 text-xs font-medium px-2 py-1 rounded"> Belum Diproses</span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="border border-gray-300 p-3 text-center">
                                <a href="{{ route('admin.nilai-akhir.detail', $data->id_jadwal_semgang) }}" 
                                   class="text-blue-600 hover:underline text-xs font-semibold">
                                    Detail
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