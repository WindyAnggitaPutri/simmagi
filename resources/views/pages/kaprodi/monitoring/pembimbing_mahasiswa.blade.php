@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6">
    <h1 class="text-center text-2xl font-bold mb-2 text-yellow-400">Monitoring Dosen Pembimbing</h1>
    <p class="text-center text-gray-500 mb-8">Daftar dosen pembimbing beserta mahasiswa bimbingannya</p>

    {{-- ==================== SEARCH & FILTER & PER PAGE ==================== --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="relative flex-1 md:flex-initial">
            <input type="text" 
                   id="searchInput"
                   value="{{ $search ?? '' }}" 
                   placeholder="Cari dosen, mahasiswa, perusahaan..." 
                   class="w-full md:w-80 pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <button type="button" 
                    id="clearSearch"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition cursor-pointer {{ ($search ?? '') ? '' : 'hidden' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="flex items-center gap-2 flex-wrap">
            {{-- ✅ FILTER TAHUN ANGKATAN --}}
            @if(isset($daftarTahun))
            <div class="flex items-center gap-1.5">
                <span class="text-sm text-gray-500 whitespace-nowrap">Tahun:</span>
                <select id="filterTahunSelect" 
                        class="border border-blue-400 rounded-lg px-2 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 cursor-pointer transition">
                    <option value="{{ route('kaprodi.monitoring.pembimbing', array_merge(request()->except(['page', 'filter_tahun']))) }}" {{ !$filterTahun ? 'selected' : '' }}>Semua</option>
                    @foreach($daftarTahun as $tahun)
                        <option value="{{ route('kaprodi.monitoring.pembimbing', array_merge(request()->except('page'), ['filter_tahun' => $tahun])) }}" {{ $filterTahun == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- ✅ FILTER KELAS --}}
            @if(isset($daftarKelas))
            <div class="flex items-center gap-1.5">
                <span class="text-sm text-gray-500 whitespace-nowrap">Kelas:</span>
                <select id="filterKelasSelect" 
                        class="border border-purple-400 rounded-lg px-2 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-400 cursor-pointer transition">
                    <option value="{{ route('kaprodi.monitoring.pembimbing', array_merge(request()->except(['page', 'filter_kelas']))) }}" {{ !$filterKelas ? 'selected' : '' }}>Semua</option>
                    @foreach($daftarKelas as $kelas)
                        <option value="{{ route('kaprodi.monitoring.pembimbing', array_merge(request()->except('page'), ['filter_kelas' => $kelas])) }}" {{ $filterKelas == $kelas ? 'selected' : '' }}>{{ $kelas }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="w-px h-6 bg-gray-300 hidden md:block"></div>

            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-500 whitespace-nowrap">Tampilkan:</label>
                <select id="perPageSelect" 
                        class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 cursor-pointer transition">
                    @foreach([5, 10, 15, 25, 50] as $option)
                        <option value="{{ $option }}" {{ ($perPage ?? 5) == $option ? 'selected' : '' }}>
                            {{ $option }} data
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if ($dospemList->isEmpty())
        <div class="text-center py-10 bg-gray-50 border border-gray-200 rounded-lg">
            @if($search ?? false)
                <div class="flex flex-col items-center gap-2 text-gray-400">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Tidak ditemukan data yang cocok dengan "<strong class="text-gray-600">{{ $search }}</strong>"</span>
                    <button type="button" onclick="clearAndReload()" class="text-yellow-600 hover:underline text-sm font-semibold cursor-pointer">Lihat semua data</button>
                </div>
            @elseif(($filterTahun ?? false) || ($filterKelas ?? false))
                <div class="flex flex-col items-center gap-2 text-gray-400">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Tidak ada data untuk filter yang dipilih</span>
                    <button type="button" onclick="resetAllFilters()" class="text-yellow-600 hover:underline text-sm font-semibold cursor-pointer">Lihat semua data</button>
                </div>
            @else
                <span class="text-gray-500">Belum ada data dosen pembimbing saat ini.</span>
            @endif
        </div>
    @else
        <div class="bg-white shadow-md border border-yellow-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-yellow-500 text-white">
                        <tr>
                            <th class="p-4 text-center w-12">No</th>
                            <th class="p-4">Nama Dosen Pembimbing</th>
                            <th class="p-4">Nama Mahasiswa</th>
                            <th class="p-4">NPM</th>
                            <th class="p-4 text-center w-20">Kelas</th>
                            <th class="p-4">Perusahaan Magang</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @php
                            $groupedDospem = $dospemList->getCollection()->groupBy('id_dosen');
                            $no = $dospemList->firstItem() ?? 1;
                        @endphp

                        @foreach ($groupedDospem as $idDosen => $items)
                            @php $first = true; @endphp
                            @foreach ($items as $dospem)
                                <tr class="border-b border-gray-100 hover:bg-yellow-50 transition">
                                    @if($first)
                                    <td class="p-4 text-center text-gray-500 font-medium align-top border-b border-gray-100 bg-yellow-50/30" rowspan="{{ $items->count() }}">{{ $no++ }}</td>
                                    <td class="p-4 font-semibold text-gray-900 align-top border-b border-gray-100 bg-yellow-50/30" rowspan="{{ $items->count() }}">
                                        {{ $dospem->dosen->nama ?? '-' }}
                                        <span class="text-gray-400 font-normal text-xs block mt-0.5">{{ $dospem->dosen->nidn ?? '-' }}</span>
                                    </td>
                                    @php $first = false; @endphp
                                    @endif
                                    
                                    <td class="p-4 font-semibold text-gray-900">
                                        {{ $dospem->mahasiswa->nama ?? '-' }}
                                    </td>
                                    <td class="p-4 text-gray-600">
                                        {{ $dospem->mahasiswa->npm ?? '-' }}
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="inline-block bg-purple-50 text-purple-700 text-xs font-bold px-2.5 py-1 border border-purple-200 rounded">
                                            {{ $dospem->mahasiswa->kelas ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        {{ $dospem->pengajuanMagang->perusahaan->nama_perusahaan ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ==================== PAGINATION ==================== --}}
            @if($dospemList->hasPages())
                <div class="px-4 py-4 border-t border-gray-200 bg-gray-50/50 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p class="text-sm text-gray-500">
                        Menampilkan 
                        <span class="font-semibold text-gray-700">{{ $dospemList->firstItem() }}</span> - 
                        <span class="font-semibold text-gray-700">{{ $dospemList->lastItem() }}</span> 
                        dari 
                        <span class="font-semibold text-gray-700">{{ $dospemList->total() }}</span> data
                        @if($search ?? false)
                            <span class="text-yellow-600"> untuk "{{ $search }}"</span>
                        @endif
                        @if($filterTahun ?? false)
                            <span class="text-blue-600"> tahun {{ $filterTahun }}</span>
                        @endif
                        @if($filterKelas ?? false)
                            <span class="text-purple-600"> kelas {{ $filterKelas }}</span>
                        @endif
                    </p>
                    
                    <div class="flex items-center gap-1">
                        @if($dospemList->onFirstPage())
                            <span class="px-3 py-1.5 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </span>
                        @else
                            <a href="{{ $dospemList->previousPageUrl() }}" 
                               class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </a>
                        @endif

                        @php
                            $start = max(1, $dospemList->currentPage() - 2);
                            $end   = min($dospemList->lastPage(), $dospemList->currentPage() + 2);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $dospemList->url(1) }}" 
                               class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">1</a>
                            @if($start > 2)
                                <span class="px-2 text-gray-400">...</span>
                            @endif
                        @endif

                        @for($page = $start; $page <= $end; $page++)
                            @if($page == $dospemList->currentPage())
                                <span class="px-3 py-1.5 text-sm font-bold text-white bg-yellow-500 rounded-lg shadow-sm">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $dospemList->url($page) }}" 
                                   class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">
                                    {{ $page }}
                                </a>
                            @endif
                        @endfor

                        @if($end < $dospemList->lastPage())
                            @if($end < $dospemList->lastPage() - 1)
                                <span class="px-2 text-gray-400">...</span>
                            @endif
                            <a href="{{ $dospemList->url($dospemList->lastPage()) }}" 
                               class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">
                                {{ $dospemList->lastPage() }}
                            </a>
                        @endif

                        @if($dospemList->hasMorePages())
                            <a href="{{ $dospemList->nextPageUrl() }}" 
                               class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @else
                            <span class="px-3 py-1.5 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Info total (hanya tampil kalau 1 halaman) --}}
            @if(!$dospemList->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 bg-gray-50/50">
                    <p class="text-sm text-gray-500">
                        Total: <span class="font-semibold text-gray-700">{{ $groupedDospem->count() }}</span> dosen pembimbing, 
                        <span class="font-semibold text-gray-700">{{ $dospemList->count() }}</span> mahasiswa bimbingan
                    </p>
                </div>
            @endif
        </div>
    @endif
</div>

<script>
    // ========== SEARCH OTOMATIS (debounce 500ms) ==========
    let searchTimer = null;
    const searchInput = document.getElementById('searchInput');

    searchInput.addEventListener('input', function() {
        const value = this.value.trim();
        const clearBtn = document.getElementById('clearSearch');

        if (clearBtn) {
            clearBtn.classList.toggle('hidden', value.length === 0);
        }

        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            const url = new URL(window.location.href);
            if (value.length > 0) {
                url.searchParams.set('search', value);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }, 500);
    });

    // ========== CLEAR SEARCH ==========
    document.getElementById('clearSearch').addEventListener('click', clearAndReload);

    function clearAndReload() {
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // ========== RESET ALL FILTERS ==========
    function resetAllFilters() {
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        url.searchParams.delete('filter_tahun');
        url.searchParams.delete('filter_kelas');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // ========== FILTER TAHUN ==========
    const filterTahunSelect = document.getElementById('filterTahunSelect');
    if (filterTahunSelect) {
        filterTahunSelect.addEventListener('change', function() {
            window.location.href = this.value;
        });
    }

    // ========== FILTER KELAS ==========
    const filterKelasSelect = document.getElementById('filterKelasSelect');
    if (filterKelasSelect) {
        filterKelasSelect.addEventListener('change', function() {
            window.location.href = this.value;
        });
    }

    // ========== PER PAGE ==========
    document.getElementById('perPageSelect').addEventListener('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', this.value);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    });
</script>
@endsection