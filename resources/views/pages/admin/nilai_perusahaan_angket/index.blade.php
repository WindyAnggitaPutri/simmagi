@extends('layouts.admin')

@section('title', 'SIMMAGI - Nilai Perusahaan & Angket')
@section('page-title', 'Nilai Perusahaan & Angket')

@section('content')
<h2 class="text-center text-4xl font-bold text-gray-800 mb-6">.:: Nilai Perusahaan & Angket ::.</h2>

<div class="bg-white shadow-md p-4 rounded-lg">
    {{-- Toolbar --}}
    <div class="flex flex-wrap justify-between mb-4 gap-3">
        <div class="flex items-center space-x-4">
            <div class="flex items-center">
                <label for="rowsPerPage" class="mr-2 text-sm text-gray-600">Tampilkan:</label>
                <select id="rowsPerPage" class="border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="5" selected>5</option>
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                </select>
            </div>
        </div>
        <div class="flex items-center space-x-3 flex-1 max-w-3xl">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" id="searchInput" placeholder="Cari nama perusahaan, jenis..." class="w-full border border-gray-300 rounded-lg p-2 pl-10 focus:ring-orange-500 focus:border-orange-500">
            </div>
            <select id="filterProdi" class="border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 min-w-[160px]">
                <option value="">Semua Prodi</option>
                @foreach($prodis as $prodi)
                <option value="{{ $prodi->id_prodi }}">{{ $prodi->nama_prodi }}</option>
                @endforeach
            </select>
            <select id="filterType" class="border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 min-w-[150px]">
                <option value="">Semua Jenis</option>
                <option value="angket">Angket</option>
                <option value="penilaian">Penilaian Mahasiswa</option>
            </select>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300" id="dataTable">
            <thead class="bg-[#fffbeb]">
                <tr>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-16">No</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase min-w-[200px]">Nama Perusahaan</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase min-w-[280px]">Jenis Token</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-40">Nilai</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @if($tokens->isEmpty())
                    <tr id="noDataRow">
                        <td colspan="4" class="border border-gray-300 p-10 text-center text-gray-400">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Belum ada data token.
                        </td>
                    </tr>
                @else
                    @foreach($tokens->groupBy('id_perusahaan') as $groupItems)
                        @foreach($groupItems as $item)
                            <tr class="data-row hover:bg-[#fffbeb]/30 transition"
                                data-company-id="{{ $item->id_perusahaan }}"
                                data-company-name="{{ $item->perusahaan->nama_perusahaan ?? '-' }}"
                                data-prodi="{{ $item->id_prodi ?? '' }}"
                                data-type="{{ $item->type }}">
                                
                                {{-- TAMBAHAN align-top DI SINI --}}
                                <td class="border border-gray-300 p-3 text-center text-gray-500 font-medium align-top nomor-urut"></td>
                                <td class="border border-gray-300 p-3 font-semibold text-gray-900 align-top company-cell">{{ $item->perusahaan->nama_perusahaan ?? '-' }}</td>
                                
                                <td class="border border-gray-300 p-3">
                                    <div class="flex flex-col gap-1">
                                        @if($item->type == 'angket')
                                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-1 rounded w-fit">
                                                 Angket {{ $item->prodi->nama_prodi ?? '' }}
                                            </span>
                                        @else
                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-1 rounded w-fit">
                                                 Penilaian: {{ $item->mahasiswa->nama ?? '-' }}
                                            </span>
                                        @endif
                                        @if($item->is_used)
                                            <span class="bg-emerald-50 text-emerald-600 text-[10px] px-1.5 py-0.5 rounded-full w-fit inline-flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Diisi
                                            </span>
                                        @elseif(now()->isAfter($item->expired_at))
                                            <span class="bg-red-50 text-red-500 text-[10px] px-1.5 py-0.5 rounded-full w-fit">Kadaluarsa</span>
                                        @else
                                            <span class="bg-yellow-50 text-yellow-600 text-[10px] px-1.5 py-0.5 rounded-full w-fit">Menunggu</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="border border-gray-300 p-3 text-center">
                                    @if($item->is_used)
    @if($item->type == 'angket')
        <a href="{{ route('admin.penilaian-perusahaan.detail-angket', $item->id_token) }}" class="text-blue-600 hover:underline text-xs font-semibold">
             Lihat Angket
        </a>
    @else
        <a href="{{ route('admin.penilaian-perusahaan.detail-penilaian', $item->id_token) }}" class="text-blue-600 hover:underline text-xs font-semibold">
             Lihat Nilai
        </a>
    @endif
@else
                                        <span class="text-gray-400 text-xs italic">
                                            @if(now()->isAfter($item->expired_at))
                                                Kadaluarsa
                                            @else
                                                Belum diisi
                                            @endif
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
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

    function applyRowspan() {
        document.querySelectorAll('#tableBody .nomor-urut').forEach(cell => {
            cell.removeAttribute('rowspan');
            cell.style.display = '';
            cell.textContent = '';
        });
        document.querySelectorAll('#tableBody .company-cell').forEach(cell => {
            cell.removeAttribute('rowspan');
            cell.style.display = '';
        });

        const visible = Array.from(document.querySelectorAll('#tableBody .data-row')).filter(r => r.style.display !== 'none');

        let groupNum = 0;
        let i = 0;
        while (i < visible.length) {
            const cid = visible[i].dataset.companyId;
            const numCell = visible[i].querySelector('.nomor-urut');
            const compCell = visible[i].querySelector('.company-cell');

            groupNum++;
            numCell.textContent = groupNum;

            let count = 1;
            while (i + count < visible.length && visible[i + count].dataset.companyId === cid) {
                count++;
            }

            if (count > 1) {
                numCell.setAttribute('rowspan', count);
                compCell.setAttribute('rowspan', count);
                for (let j = 1; j < count; j++) {
                    visible[i + j].querySelector('.nomor-urut').style.display = 'none';
                    visible[i + j].querySelector('.company-cell').style.display = 'none';
                }
            }

            i += count;
        }
    }

    function updateTable() {
        const rows = document.querySelectorAll('#tableBody .data-row');
        const noDataRow = document.getElementById('noDataRow');
        const noSearchResults = document.getElementById('noSearchResults');
        const prevButton = document.getElementById('prevPage');
        const nextButton = document.getElementById('nextPage');
        const pageInfo = document.getElementById('pageInfo');
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const filterProdi = document.getElementById('filterProdi').value;
        const filterType = document.getElementById('filterType').value;

        filteredRows = Array.from(rows).filter(row => {
            const textPerusahaan = (row.dataset.companyName || '').toLowerCase();
            const textJenis = row.cells[2] ? row.cells[2].textContent.toLowerCase() : '';
            const matchSearch = textPerusahaan.includes(searchTerm) || textJenis.includes(searchTerm);
            const matchProdi = !filterProdi || row.dataset.prodi === filterProdi;
            const matchType = !filterType || row.dataset.type === filterType;
            return matchSearch && matchProdi && matchType;
        });

        rows.forEach(row => row.style.display = 'none');

        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        currentPage = Math.min(currentPage, Math.max(1, totalPages || 1));

        const start = (currentPage - 1) * rowsPerPage;
        filteredRows.slice(start, start + rowsPerPage).forEach(row => row.style.display = '');

        applyRowspan();

        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages || totalRows === 0;
        pageInfo.textContent = totalRows > 0 ? 'Page ' + currentPage + ' of ' + totalPages : '';

        if (noDataRow) noDataRow.style.display = rows.length === 0 ? '' : 'none';
        noSearchResults.classList.toggle('hidden', totalRows > 0 || rows.length === 0);
        document.getElementById('dataTable').style.display = (totalRows === 0 && rows.length > 0) ? 'none' : '';
    }

    document.getElementById('searchInput').addEventListener('input', () => { currentPage = 1; updateTable(); });
    document.getElementById('filterProdi').addEventListener('change', () => { currentPage = 1; updateTable(); });
    document.getElementById('filterType').addEventListener('change', () => { currentPage = 1; updateTable(); });
    document.getElementById('rowsPerPage').addEventListener('change', function () {
        rowsPerPage = parseInt(this.value);
        currentPage = 1;
        updateTable();
    });
    document.getElementById('prevPage').addEventListener('click', () => {
        if (currentPage > 1) { currentPage--; updateTable(); }
    });
    document.getElementById('nextPage').addEventListener('click', () => {
        var tp = Math.ceil(filteredRows.length / rowsPerPage);
        if (currentPage < tp) { currentPage++; updateTable(); }
    });

    updateTable();
</script>
@endpush
@endsection