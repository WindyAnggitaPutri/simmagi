@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-3">
        <div>
            <h1 class="text-2xl font-bold text-yellow-400">Penilaian Dosen Pembimbing</h1>
            <p class="text-gray-500 text-sm mt-1">Rekap penilaian seminar magang oleh dosen pembimbing di prodi Anda</p>
        </div>
        <div class="text-sm text-gray-500 bg-gray-100 border border-gray-200 rounded-lg px-4 py-2 text-center">
            Total: <span class="font-bold text-gray-800">{{ $jadwal->count() }}</span> data
            @if($perPage !== 'all' && $jadwal instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <span class="text-gray-400 mx-1">·</span>
                Halaman <span class="font-bold text-gray-800">{{ $jadwal->currentPage() }}</span> dari {{ $jadwal->lastPage() }}
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="mb-5 bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-5 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- TOOLBAR: Search + Filter --}}
    <div class="bg-white shadow-md border border-gray-200 rounded-t-lg px-5 py-4">
        <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">
            {{-- Search Bar — plain input, no form, no button --}}
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input 
                    type="text" 
                    value="{{ $search }}" 
                    placeholder="Cari nama, NPM, dosen, perusahaan..." 
                    id="searchInput"
                    class="w-full pl-10 pr-10 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition outline-none"
                >
                @if(!empty($search))
                <button type="button" onclick="clearSearch()" 
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition"
                    title="Hapus pencarian">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                @endif
            </div>

            {{-- Filter Per Page — DROPDOWN --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <label for="perPageSelect" class="text-xs text-gray-500 font-medium whitespace-nowrap">Data/halaman:</label>
                <div class="relative">
                    <select 
                        id="perPageSelect" 
                        onchange="changePerPage(this.value)"
                        class="appearance-none w-28 pl-3 pr-8 py-2.5 text-sm font-semibold border border-gray-300 rounded-lg bg-white text-gray-700 cursor-pointer focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition outline-none"
                    >
                        <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                        <option value="all" {{ $perPage == 'all' ? 'selected' : '' }}>Semua</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info pencarian aktif --}}
        @if(!empty($search))
        <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            Hasil pencarian untuk: <strong class="text-gray-700">"{{ $search }}"</strong>
            <span class="text-gray-400">—</span>
            <span>ditemukan <strong class="text-gray-700">{{ $jadwal->count() }}</strong> data</span>
        </div>
        @endif
    </div>

    {{-- TABEL --}}
    <div class="bg-white shadow-md border border-t-0 border-gray-200 rounded-b-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-yellow-500 text-white">
                    <tr>
                        <th class="p-4 text-center w-12">No</th>
                        <th class="p-4">Mahasiswa</th>
                        <th class="p-4">Dosen Pembimbing</th>
                        <th class="p-4">Perusahaan</th>
                        <th class="p-4 text-center">Tanggal Seminar</th>
                        <th class="p-4 text-center">Nilai</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse($jadwal as $key => $item)
                    @php
                        $nomor = $perPage === 'all' 
                            ? $key + 1 
                            : ($jadwal->currentPage() - 1) * $jadwal->perPage() + $key + 1;
                    @endphp
                    <tr class="border-b border-gray-100 hover:bg-yellow-50/60 transition">
                        <td class="p-4 text-center text-gray-500 font-medium">{{ $nomor }}</td>
                        <td class="p-4">
                            <div class="font-semibold text-gray-900">{{ $item->mahasiswa->nama ?? '-' }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $item->mahasiswa->npm ?? '-' }}</div>
                        </td>
                        <td class="p-4">{{ $item->dosenPembimbing->dosen->nama ?? '-' }}</td>
                        <td class="p-4">{{ $item->perusahaan->nama_perusahaan ?? '-' }}</td>
                        <td class="p-4 text-center whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($item->tgl_semgang)->format('d M Y') }}
                        </td>
                        <td class="p-4 text-center">
                            @if($item->rata_rata != null)
                                <span class="inline-flex flex-col items-center">
                                    <span class="text-lg font-bold text-gray-900 leading-none">{{ number_format($item->rata_rata, 1) }}</span>
                                    <span class="text-[10px] text-gray-400 mt-0.5">rata-rata</span>
                                </span>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            @if($item->nilai_persiapan != null)
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    Sudah Dinilai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                                    Belum Dinilai
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            <a href="{{ route('kaprodi.jadwal-semgang.detail-nilai', $item->id_jadwal_semgang) }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 hover:border-green-300 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Lihat Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <div>
                                    <p class="text-gray-500 font-medium">Belum ada data penilaian</p>
                                    <p class="text-gray-400 text-xs mt-1">Data akan muncul setelah dosen pembimbing mengisi penilaian seminar magang</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($perPage !== 'all' && $jadwal instanceof \Illuminate\Pagination\LengthAwarePaginator && $jadwal->hasPages())
        <div class="px-5 py-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs text-gray-500">
                Menampilkan 
                <span class="font-semibold text-gray-700">{{ $jadwal->firstItem() }}</span> 
                - 
                <span class="font-semibold text-gray-700">{{ $jadwal->lastItem() }}</span> 
                dari 
                <span class="font-semibold text-gray-700">{{ $jadwal->total() }}</span> 
                data
            </div>
            <div class="flex items-center gap-1">
                @if($jadwal->onFirstPage())
                <span class="px-3 py-2 text-xs text-gray-300 bg-gray-50 border border-gray-200 rounded-lg cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </span>
                @else
                <a href="{{ $jadwal->previousPageUrl() }}" 
                   class="px-3 py-2 text-xs text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @endif

                @if($jadwal->currentPage() - 2 > 1)
                <a href="{{ $jadwal->url(1) }}" class="w-9 h-9 flex items-center justify-center text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition">1</a>
                <span class="w-9 h-9 flex items-center justify-center text-xs text-gray-400">...</span>
                @endif

                @foreach($jadwal->getUrlRange(max(1, $jadwal->currentPage() - 2), min($jadwal->lastPage(), $jadwal->currentPage() + 2)) as $page => $url)
                    @if($page == $jadwal->currentPage())
                    <span class="w-9 h-9 flex items-center justify-center text-xs font-bold bg-yellow-500 text-white rounded-lg shadow-sm">
                        {{ $page }}
                    </span>
                    @else
                    <a href="{{ $url }}" 
                       class="w-9 h-9 flex items-center justify-center text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition">
                        {{ $page }}
                    </a>
                    @endif
                @endforeach

                @if($jadwal->currentPage() + 2 < $jadwal->lastPage())
                <span class="w-9 h-9 flex items-center justify-center text-xs text-gray-400">...</span>
                <a href="{{ $jadwal->url($jadwal->lastPage()) }}" class="w-9 h-9 flex items-center justify-center text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition">{{ $jadwal->lastPage() }}</a>
                @endif

                @if($jadwal->hasMorePages())
                <a href="{{ $jadwal->nextPageUrl() }}" 
                   class="px-3 py-2 text-xs text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @else
                <span class="px-3 py-2 text-xs text-gray-300 bg-gray-50 border border-gray-200 rounded-lg cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

<script>
let searchTimeout;

document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const val = this.value.trim();
    searchTimeout = setTimeout(() => {
        const url = new URL(window.location.href);
        if (val) {
            url.searchParams.set('search', val);
        } else {
            url.searchParams.delete('search');
        }
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }, 400);
});

function clearSearch() {
    const url = new URL(window.location.href);
    url.searchParams.delete('search');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function changePerPage(val) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', val);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}
</script>