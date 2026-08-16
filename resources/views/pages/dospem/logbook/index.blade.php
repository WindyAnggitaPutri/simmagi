@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-yellow-400">Logbook Mahasiswa Bimbingan</h1>
            <p class="text-gray-500 text-sm mt-1">Daftar logbook magang seluruh mahasiswa bimbingan Anda</p>
        </div>

        {{-- Statistik Ringkas --}}
        <div class="flex gap-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg px-5 py-3 text-center">
                <p class="text-xl font-bold text-blue-600">{{ $totalMhs }}</p>
                <p class="text-xs text-gray-600">Total Mhs</p>
            </div>
            <div class="bg-orange-50 border border-orange-200 rounded-lg px-5 py-3 text-center">
                <p class="text-xl font-bold text-orange-600">{{ $totalPending }}</p>
                <p class="text-xs text-gray-600">Pending</p>
            </div>
        </div>
    </div>

    {{-- ==================== SEARCH & FILTER ==================== --}}
    <div class="bg-white shadow-md border border-gray-200 rounded-lg p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
            {{-- Search Bar --}}
            <form action="{{ route('dospem.logbook.index') }}" method="GET" class="flex-1 w-full md:w-auto">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" 
                           name="search" 
                           value="{{ $search ?? '' }}" 
                           placeholder="Cari nama mahasiswa, NPM, atau perusahaan..." 
                           class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 text-sm outline-none transition">
                </div>
            </form>

            {{-- Filter Per Page --}}
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600 font-medium whitespace-nowrap">Tampilkan:</label>
                <select id="perPageSelect" 
                        class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition cursor-pointer">
                    <option value="5" {{ ($perPage ?? 5) == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ ($perPage ?? 5) == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ ($perPage ?? 5) == 15 ? 'selected' : '' }}>15</option>
                    <option value="all" {{ ($perPage ?? 5) == 'all' ? 'selected' : '' }}>Semua</option>
                </select>
            </div>
        </div>

        
    </div>

    {{-- ==================== TABEL ==================== --}}
    <div class="bg-white shadow-md border border-gray-200 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-yellow-500 text-white">
                    <tr>
                        <th class="p-4 text-center w-12">No</th>
                        <th class="p-4">Nama Mahasiswa</th>
                        <th class="p-4">NPM</th>
                        <th class="p-4">Perusahaan</th>
                        <th class="p-4 text-center">Total Logbook</th>
                        <th class="p-4 text-center">Pending</th>
                        <th class="p-4 text-center">Disetujui</th>
                        <!-- <th class="p-4 text-center">Terakhir Diisi</th> -->
                        <th class="p-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @if($mahasiswaBimbingan->isEmpty())
                        <tr>
                            <td colspan="9" class="p-10 text-center text-gray-400">
                                @if($search)
                                    Tidak ditemukan mahasiswa dengan kata kunci "{{ $search }}"
                                @else
                                    Belum ada mahasiswa bimbingan.
                                @endif
                            </td>
                        </tr>
                    @else
                        @foreach ($mahasiswaBimbingan as $index => $item)
                        <tr class="border-b border-gray-100 hover:bg-yellow-50 transition">
                            <td class="p-4 text-center text-gray-500 font-medium">{{ $mahasiswaBimbingan->firstItem() + $loop->index }}</td>
                            <td class="p-4 font-semibold text-gray-900">{{ $item->mahasiswa->nama ?? '-' }}</td>
                            <td class="p-4 text-gray-600">{{ $item->mahasiswa->npm ?? '-' }}</td>
                            <td class="p-4 text-gray-600">{{ $item->pengajuanMagang->perusahaan->nama_perusahaan ?? '-' }}</td>
                            
                            <td class="p-4 text-center">
                                <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-bold">
                                    {{ $item->total_logbook }}
                                </span>
                            </td>

                            <td class="p-4 text-center">
                                @if($item->logbook_pending > 0)
                                    <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-bold">
                                        {{ $item->logbook_pending }}
                                    </span>
                                @else
                                    <span class="text-gray-300">0</span>
                                @endif
                            </td>

                            <td class="p-4 text-center">
                                @if($item->logbook_disetujui > 0)
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">
                                        {{ $item->logbook_disetujui }}
                                    </span>
                                @else
                                    <span class="text-gray-300">0</span>
                                @endif
                            </td>
<!-- 
                            <td class="p-4 text-center text-gray-500 text-xs">
                                @if($item->last_logbook_date)
                                    {{ \Carbon\Carbon::parse($item->last_logbook_date)->locale('id')->translatedFormat('d M Y') }}
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td> -->

                            <td class="p-4 text-center">
                                <a href="{{ route('dospem.logbook.per_mahasiswa', $item->id_dosen_pembimbing) }}" 
                                   class="text-green-600 hover:underline text-xs font-semibold">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== PAGINATION ==================== --}}
    @if($perPage != 'all' && $mahasiswaBimbingan->hasPages())
    <div class="mt-6 flex flex-col md:flex-row items-center justify-between gap-4">
        {{-- Info --}}
        <p class="text-sm text-gray-500">
            Menampilkan 
            <span class="font-semibold text-gray-700">{{ $mahasiswaBimbingan->firstItem() }}</span> 
            - 
            <span class="font-semibold text-gray-700">{{ $mahasiswaBimbingan->lastItem() }}</span> 
            dari 
            <span class="font-semibold text-gray-700">{{ $mahasiswaBimbingan->total() }}</span> data
        </p>

        {{-- Pagination Links --}}
        <div class="flex items-center gap-1">
            {{-- Previous --}}
            @if($mahasiswaBimbingan->onFirstPage())
                <span class="px-3 py-2 text-sm text-gray-300 bg-gray-50 border border-gray-200 rounded-lg cursor-not-allowed">
                    ← Sebelumnya
                </span>
            @else
                <a href="{{ $mahasiswaBimbingan->previousPageUrl() }}" 
                   class="px-3 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">
                    ← Sebelumnya
                </a>
            @endif

            {{-- Page Numbers --}}
            @foreach($mahasiswaBimbingan->getUrlRange(1, $mahasiswaBimbingan->lastPage()) as $page => $url)
                @if($page == $mahasiswaBimbingan->currentPage())
                    <span class="px-3.5 py-2 text-sm font-bold text-white bg-yellow-500 border border-yellow-500 rounded-lg">
                        {{ $page }}
                    </span>
                @elseif($page == '...')
                    <span class="px-2 py-2 text-sm text-gray-400">...</span>
                @else
                    <a href="{{ $url }}" 
                       class="px-3.5 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">
                        {{ $page }}
                    </a>
                @endif
            @endforeach

            {{-- Next --}}
            @if($mahasiswaBimbingan->hasMorePages())
                <a href="{{ $mahasiswaBimbingan->nextPageUrl() }}" 
                   class="px-3 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">
                    Selanjutnya →
                </a>
            @else
                <span class="px-3 py-2 text-sm text-gray-300 bg-gray-50 border border-gray-200 rounded-lg cursor-not-allowed">
                    Selanjutnya →
                </span>
            @endif
        </div>
    </div>
    @endif

    @if($perPage == 'all' && $mahasiswaBimbingan->count() > 0)
    <div class="mt-6">
        <p class="text-sm text-gray-500">
            Menampilkan semua 
            <span class="font-semibold text-gray-700">{{ $mahasiswaBimbingan->count() }}</span> data
        </p>
    </div>
    @endif
</div>

{{-- ==================== JAVASCRIPT ==================== --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const perPageSelect = document.getElementById('perPageSelect');
    
    perPageSelect.addEventListener('change', function() {
        const url = new URL(window.location.href);
        const value = this.value;
        
        if (value === 'all') {
            url.searchParams.set('per_page', 'all');
        } else {
            url.searchParams.set('per_page', value);
        }
        
        url.searchParams.delete('page');
        window.location.href = url.toString();
    });

    // Langsung filter saat mengetik
    let searchTimeout;
    const searchInput = document.querySelector('input[name="search"]');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            const value = this.value.trim();
            const url = new URL(window.location.href);
            
            if (value === '') {
                url.searchParams.delete('search');
            } else {
                url.searchParams.set('search', value);
            }
            
            url.searchParams.delete('page');
            
            searchTimeout = setTimeout(() => {
                window.location.href = url.toString();
            }, 400);
        });
    }
});
</script>
@endpush
@endsection