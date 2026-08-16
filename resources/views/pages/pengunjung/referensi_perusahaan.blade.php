@extends('layouts.app')

@section('content')
<div class="mb-4 md:mb-6">
    <h1 class="text-lg md:text-2xl font-bold text-gray-800">Referensi Perusahaan Magang Industri</h1>
    <p class="text-gray-500 text-xs md:text-sm mt-1">
        Daftar perusahaan mitra magang mahasiswa Jurusan Komputer dan Bisnis
    </p>
</div>

{{-- SEARCH BAR --}}
<div class="mb-4 md:mb-6">
    <form id="searchForm" method="GET" action="{{ route('pengunjung.referensi-perusahaan') }}" class="max-w-full md:max-w-lg">
        <div class="relative">
            {{-- Ikon search --}}
            <div class="absolute inset-y-0 left-0 pl-2 md:pl-3 flex items-center pointer-events-none">
               
            </div>
            <input type="text" name="search" id="searchInput"
                value="{{ request('search') }}"
                placeholder="Cari nama perusahaan..."
                autocomplete="off"
                class="w-full border border-gray-300 rounded-lg pl-8 pr-10 py-2 md:pl-10 md:py-2.5 text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
            {{-- Tombol clear (X) --}}
            @if(request('search'))
            <button type="button" id="clearBtn"
                class="absolute inset-y-0 right-0 pr-2 md:pr-3 flex items-center text-gray-400 hover:text-gray-600 transition">
            
            </button>
            @else
            <div class="absolute inset-y-0 right-0 pr-2 md:pr-3 flex items-center pointer-events-none">
                <span class="text-[9px] md:text-xs text-gray-300 bg-gray-100 px-1 py-0.5 md:px-1.5 md:py-0.5 rounded">Enter</span>
            </div>
            @endif
        </div>
    </form>
</div>

{{-- TABEL PERUSAHAAN --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
    <table class="w-full text-[11px] md:text-sm text-left whitespace-nowrap md:whitespace-normal">
        <thead class="text-[9px] md:text-xs uppercase bg-yellow-50 border-b border-yellow-200">
            <tr class="text-yellow-800">
                <th class="px-2 md:px-6 py-2 md:py-4 font-semibold">No</th>
                <th class="px-2 md:px-6 py-2 md:py-4 font-semibold">Nama Perusahaan</th>
                <th class="px-2 md:px-6 py-2 md:py-4 font-semibold">Alamat</th>
                <th class="px-2 md:px-6 py-2 md:py-4 font-semibold text-center">Jumlah Mahasiswa</th>
                <th class="px-2 md:px-6 py-2 md:py-4 font-semibold text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($perusahaans as $key => $perusahaan)
            <tr class="bg-white border-b border-gray-100 hover:bg-yellow-50/30 transition">
                <td class="px-2 md:px-6 py-2 md:py-4 text-gray-500">
                    {{ ($perusahaans->currentPage() - 1) * $perusahaans->perPage() + $key + 1 }}
                </td>
                <td class="px-2 md:px-6 py-2 md:py-4">
                    <div class="flex items-center gap-1 md:gap-3">
                       
                        <span class="font-medium text-gray-900">{{ $perusahaan->nama_perusahaan }}</span>
                    </div>
                </td>
                <td class="px-2 md:px-6 py-2 md:py-4 text-gray-600">
                    {{ Str::limit($perusahaan->alamat ?? '-', 20) }}
                </td>
                <td class="px-2 md:px-6 py-2 md:py-4 text-center">
                    <span class="inline-flex items-center justify-center bg-yellow-100 text-yellow-800 text-[9px] md:text-xs font-semibold px-1.5 py-0.5 md:px-3 md:py-1 rounded-full">
                        {{ $perusahaan->jumlah_mahasiswa }} mhs
                    </span>
                </td>
                <td class="px-2 md:px-6 py-2 md:py-4 text-center">
                    <a href="{{ route('pengunjung.detail-perusahaan', $perusahaan->id_perusahaan) }}"
                       class="inline-flex items-center gap-1 text-yellow-700 hover:text-yellow-900 font-medium text-[11px] md:text-sm hover:underline transition">
                      
                        Detail
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-2 md:px-6 py-8 md:py-16 text-center">
                    <svg class="w-10 h-10 md:w-16 md:h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <p class="text-gray-400 text-sm md:text-lg font-medium">Belum ada data perusahaan.</p>
                    @if(request('search'))
                    <p class="mt-2 text-gray-400 text-xs md:text-base">Coba kata kunci lain atau
                        <a href="{{ route('pengunjung.referensi-perusahaan') }}" class="text-yellow-600 underline">reset pencarian</a>
                    </p>
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- PAGINATION --}}
@if($perusahaans->hasMorePages())
<div class="mt-4 md:mt-6 text-xs md:text-sm">
    {{ $perusahaans->withQueryString()->links() }}
</div>
@endif

{{-- TOMBOL KEMBALI --}}
<div class="mt-6 md:mt-8">
    <a href="{{ route('beranda_pengunjung') }}" class="inline-flex items-center gap-1 md:gap-2 text-gray-500 hover:text-yellow-700 font-medium text-xs md:text-sm transition">
        <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Beranda
    </a>
</div>

{{-- JAVASCRIPT AUTO-SEARCH --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('searchForm');
    const input = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearBtn');
    let debounceTimer;

    //  Auto-submit saat mengetik (debounce 500ms)
    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            form.submit();
        }, 500);
    });

    //  Tombol clear → hapus search & submit ulang
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            input.value = '';
            form.submit();
        });
    }

    // Pertahankan posisi kursor setelah submit
    input.setSelectionRange(input.value.length, input.value.length);
    input.focus();
});
</script>
@endsection