@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Referensi Perusahaan</h1>
    <p class="text-gray-500 text-sm mt-1">
        Kelola perusahaan yang ditampilkan di halaman publik sebagai referensi magang mahasiswa
    </p>
</div>

{{-- STATISTIK --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide">Total Perusahaan</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalPerusahaan }}</p>
            </div>
            <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-blue-400 uppercase tracking-wide">Memenuhi Syarat</p>
                <p class="text-2xl font-bold text-blue-800 mt-1">{{ $totalMemenuhiSyarat }}</p>
                <p class="text-xs text-blue-400 mt-1">Pernah terima mahasiswa magang</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-green-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-green-400 uppercase tracking-wide">Ditampilkan di Publik</p>
                <p class="text-2xl font-bold text-green-800 mt-1">{{ $totalDitampilkan }}</p>
                <p class="text-xs text-green-400 mt-1">Terlihat oleh pengunjung</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- INFO --}}
<div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="text-sm text-yellow-800">
            <p class="font-semibold">Keterangan:</p>
            <p class="mt-1">Daftar di bawah ini hanya menampilkan perusahaan yang <strong>pernah menjadi tempat magang</strong> mahasiswa dan <strong>mahasiswanya diterima</strong>. Gunakan tombol untuk mengatur apakah perusahaan ditampilkan di halaman publik.</p>
        </div>
    </div>
</div>

{{-- FLASH --}}
@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-800 px-5 py-3 rounded-xl mb-6 flex items-center gap-2">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
    </svg>
    <span class="text-sm font-medium">{{ session('success') }}</span>
</div>
@endif

{{-- TOOLBAR: Search + Filter Status + Filter Per Page --}}
<div class="bg-white shadow-sm border border-gray-200 rounded-t-xl px-5 py-4 mb-0">
    <div class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
        {{-- Search Bar — otomatis tanpa tombol --}}
        <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input 
                type="text" 
                value="{{ $search }}" 
                placeholder="Cari nama perusahaan..." 
                id="searchInput"
                autocomplete="off"
                class="w-full border border-gray-300 rounded-lg pl-10 pr-10 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#ece75f] focus:border-[#ece75f] transition"
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

        {{-- Filter Status Tampil --}}
        <div class="relative">
            <select 
                id="filterTampil"
                onchange="changeFilterTampil(this.value)"
                class="appearance-none border border-gray-300 rounded-lg pl-3 pr-8 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#ece75f] min-w-[180px] cursor-pointer"
            >
                <option value="" {{ !request('filter_tampil') ? 'selected' : '' }}>Semua Status</option>
                <option value="1" {{ request('filter_tampil') === '1' ? 'selected' : '' }}>✅ Ditampilkan</option>
                <option value="0" {{ request('filter_tampil') === '0' ? 'selected' : '' }}>❌ Disembunyikan</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>

        {{-- Filter Per Page --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            <label for="perPageSelect" class="text-xs text-gray-500 font-medium whitespace-nowrap">Data/halaman:</label>
            <div class="relative">
                <select 
                    id="perPageSelect" 
                    onchange="changePerPage(this.value)"
                    class="appearance-none w-28 pl-3 pr-8 py-2.5 text-sm font-semibold border border-gray-300 rounded-lg bg-white text-gray-700 cursor-pointer focus:ring-2 focus:ring-[#ece75f] focus:border-[#ece75f] transition outline-none"
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
        <span>ditemukan <strong class="text-gray-700">{{ $perusahaans->count() }}</strong> data</span>
    </div>
    @endif
</div>

{{-- TABEL --}}
<div class="bg-white shadow-sm border border-t-0 border-gray-200 rounded-b-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs uppercase bg-gray-50 border-b border-gray-200">
                <tr class="text-gray-600">
                    <th class="px-6 py-4 font-semibold w-12">
                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-[#ece75f] focus:ring-[#ece75f]">
                    </th>
                    <th class="px-6 py-4 font-semibold">No</th>
                    <th class="px-6 py-4 font-semibold">Nama Perusahaan</th>
                    <th class="px-6 py-4 font-semibold">Alamat</th>
                    <th class="px-6 py-4 font-semibold text-center">Jumlah Mahasiswa</th>
                    <th class="px-6 py-4 font-semibold text-center">Status Tampil</th>
                    <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($perusahaans as $key => $perusahaan)
                @php
                    $nomor = $perPage === 'all'
                        ? $key + 1
                        : ($perusahaans->currentPage() - 1) * $perusahaans->perPage() + $key + 1;
                @endphp
                <tr class="bg-white border-b border-gray-100 hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <input type="checkbox" name="selected[]" value="{{ $perusahaan->id_perusahaan }}" class="perusahaan-checkbox rounded border-gray-300 text-[#ece75f] focus:ring-[#ece75f]">
                    </td>
                    <td class="px-6 py-4 text-gray-500">{{ $nomor }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-[#ece75f]/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-[#b8b44a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <span class="font-medium text-gray-900">{{ $perusahaan->nama_perusahaan }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ Str::limit($perusahaan->alamat ?? '-', 40) }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center justify-center bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
                            {{ $perusahaan->jumlah_mahasiswa }} mahasiswa
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($perusahaan->tampilkan_publik)
                            <span class="inline-flex items-center gap-1 text-green-700 bg-green-100 text-xs font-semibold px-3 py-1.5 rounded-full">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Ditampilkan
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-gray-500 bg-gray-100 text-xs font-semibold px-3 py-1.5 rounded-full">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                Disembunyikan
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <form method="POST" action="{{ route('kaprodi.referensi-perusahaan.toggle', $perusahaan->id_perusahaan) }}" style="display:inline">
                            @csrf
                            @if($perusahaan->tampilkan_publik)
                                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                                    Sembunyikan
                                </button>
                            @else
                                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-green-50 text-green-700 hover:bg-green-100 border border-green-200 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    Tampilkan
                                </button>
                            @endif
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <p class="text-gray-400 text-lg font-medium">Belum ada perusahaan yang memenuhi syarat.</p>
                        <p class="text-gray-400 text-sm mt-2">Perusahaan akan muncul jika ada mahasiswa yang diterima magang di perusahaan tersebut.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    @if($perPage !== 'all' && $perusahaans instanceof \Illuminate\Pagination\LengthAwarePaginator && $perusahaans->hasPages())
    <div class="px-5 py-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="text-xs text-gray-500">
            Menampilkan 
            <span class="font-semibold text-gray-700">{{ $perusahaans->firstItem() }}</span> 
            - 
            <span class="font-semibold text-gray-700">{{ $perusahaans->lastItem() }}</span> 
            dari 
            <span class="font-semibold text-gray-700">{{ $perusahaans->total() }}</span> 
            data
        </div>
        <div class="flex items-center gap-1">
            @if($perusahaans->onFirstPage())
            <span class="px-3 py-2 text-xs text-gray-300 bg-gray-50 border border-gray-200 rounded-lg cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </span>
            @else
            <a href="{{ $perusahaans->previousPageUrl() }}" 
               class="px-3 py-2 text-xs text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            @endif

            @if($perusahaans->currentPage() - 2 > 1)
            <a href="{{ $perusahaans->url(1) }}" class="w-9 h-9 flex items-center justify-center text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition">1</a>
            <span class="w-9 h-9 flex items-center justify-center text-xs text-gray-400">...</span>
            @endif

            @foreach($perusahaans->getUrlRange(max(1, $perusahaans->currentPage() - 2), min($perusahaans->lastPage(), $perusahaans->currentPage() + 2)) as $page => $url)
                @if($page == $perusahaans->currentPage())
                <span class="w-9 h-9 flex items-center justify-center text-xs font-bold bg-[#ece75f] text-gray-800 rounded-lg shadow-sm">
                    {{ $page }}
                </span>
                @else
                <a href="{{ $url }}" 
                   class="w-9 h-9 flex items-center justify-center text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition">
                    {{ $page }}
                </a>
                @endif
            @endforeach

            @if($perusahaans->currentPage() + 2 < $perusahaans->lastPage())
            <span class="w-9 h-9 flex items-center justify-center text-xs text-gray-400">...</span>
            <a href="{{ $perusahaans->url($perusahaans->lastPage()) }}" class="w-9 h-9 flex items-center justify-center text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition">{{ $perusahaans->lastPage() }}</a>
            @endif

            @if($perusahaans->hasMorePages())
            <a href="{{ $perusahaans->nextPageUrl() }}" 
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

{{-- AKSI MASSAL --}}
@unless($perusahaans->isEmpty())
<div class="mt-4 flex items-center justify-between">
    <form id="formMassal" method="POST" action="{{ route('kaprodi.referensi-perusahaan.set-massal') }}" class="flex items-center gap-2">
        @csrf
        <input type="hidden" name="action" id="massalAction" value="">
        <button type="button" onclick="submitMassal('tampilkan')" 
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-50 text-green-700 border border-green-200 rounded-lg text-sm font-medium hover:bg-green-100 transition disabled:opacity-50 disabled:cursor-not-allowed"
                id="btnTampilkanMassal" disabled>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            Tampilkan yang Dipilih
        </button>
        <button type="button" onclick="submitMassal('sembunyikan')" 
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-50 text-red-700 border border-red-200 rounded-lg text-sm font-medium hover:bg-red-100 transition disabled:opacity-50 disabled:cursor-not-allowed"
                id="btnSembunyikanMassal" disabled>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
            Sembunyikan yang Dipilih
        </button>
    </form>
    <span class="text-xs text-gray-400" id="selectedCount">0 dipilih</span>
</div>
@endunless

{{-- SCRIPTS --}}
<script>
// === SEARCH OTOMATIS ===
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
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}

// === FILTER STATUS TAMPIL ===
function changeFilterTampil(val) {
    const url = new URL(window.location.href);
    if (val) {
        url.searchParams.set('filter_tampil', val);
    } else {
        url.searchParams.delete('filter_tampil');
    }
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}

// === FILTER PER PAGE ===
function changePerPage(val) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', val);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}

// === CHECKBOX MASSAL ===
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.perusahaan-checkbox');
    const btnT = document.getElementById('btnTampilkanMassal');
    const btnS = document.getElementById('btnSembunyikanMassal');
    const count = document.getElementById('selectedCount');

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
            update();
        });
    }
    checkboxes.forEach(cb => cb.addEventListener('change', function () {
        if (selectAll) selectAll.checked = [...checkboxes].every(c => c.checked);
        update();
    }));

    function update() {
        const n = [...checkboxes].filter(c => c.checked).length;
        count.textContent = n + ' dipilih';
        btnT.disabled = n === 0;
        btnS.disabled = n === 0;
    }
});

function submitMassal(action) {
    const checked = document.querySelectorAll('.perusahaan-checkbox:checked');
    if (checked.length === 0) return;
    if (!confirm('Yakin ingin ' + (action === 'tampilkan' ? 'menampilkan' : 'menyembunyikan') + ' ' + checked.length + ' perusahaan?')) return;

    const form = document.getElementById('formMassal');
    form.querySelectorAll('input[name="id_perusahaan[]"]').forEach(el => el.remove());
    
    document.getElementById('massalAction').value = action;
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'id_perusahaan[]';
        input.value = cb.value;
        form.appendChild(input);
    });
    form.submit();
}
</script>
@endsection