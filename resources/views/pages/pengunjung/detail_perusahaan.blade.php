@extends('layouts.app')

@section('content')
{{-- HEADER --}}
<div class="mb-4 md:mb-8">
    <a href="{{ route('pengunjung.referensi-perusahaan') }}" class="inline-flex items-center gap-1 md:gap-2 text-gray-500 hover:text-yellow-700 font-medium text-xs md:text-sm mb-2 md:mb-4 transition">
       
        Kembali ke Daftar Perusahaan
    </a>
    <h1 class="text-lg md:text-2xl font-bold text-gray-800">{{ $perusahaan->nama_perusahaan }}</h1>
</div>

{{-- PROFIL PERUSAHAAN --}}
<div class="bg-white rounded-xl shadow-sm border border-yellow-200 overflow-hidden mb-4 md:mb-8">
    <div class="bg-yellow-50 border-b border-yellow-200 px-2 md:px-6 py-2 md:py-4">
        <h2 class="text-sm md:text-lg font-semibold text-yellow-800 flex items-center gap-1 md:gap-2">
           
            Profil Perusahaan
        </h2>
    </div>
    <div class="p-2 md:p-6">
        {{-- Diubah jadi grid-cols-3 biar di HP posisinya sama kayak desktop --}}
        <div class="grid grid-cols-3 gap-2 md:gap-6">
            {{-- Nama Perusahaan --}}
            <div class="flex items-start gap-1 md:gap-3">
                <!-- <div class="w-6 h-6 md:w-10 md:h-10 bg-yellow-100 rounded md:rounded-lg flex items-center justify-center flex-shrink-0">
                   
                </div> -->
                <div>
                    <p class="text-[8px] md:text-xs text-gray-400 uppercase tracking-wide">Nama Perusahaan</p>
                    <p class="font-semibold text-gray-900 text-[10px] md:text-base leading-tight">{{ $perusahaan->nama_perusahaan }}</p>
                </div>
            </div>

            {{-- Alamat --}}
            <div class="flex items-start gap-1 md:gap-3">
                <!-- <div class="w-6 h-6 md:w-10 md:h-10 bg-yellow-100 rounded md:rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3 md:w-5 md:h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div> -->
                <div>
                    <p class="text-[8px] md:text-xs text-gray-400 uppercase tracking-wide">Alamat</p>
                    <p class="font-semibold text-gray-900 text-[10px] md:text-base leading-tight">{{ $perusahaan->alamat ?? '-' }}</p>
                </div>
            </div>

            {{-- Total Mahasiswa --}}
            <div class="flex items-start gap-1 md:gap-3">
                <!-- <div class="w-6 h-6 md:w-10 md:h-10 bg-yellow-100 rounded md:rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3 md:w-5 md:h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div> -->
                <div>
                    <p class="text-[8px] md:text-xs text-gray-400 uppercase tracking-wide">Total Mhs Magang</p>
                    <p class="font-semibold text-gray-900 text-[10px] md:text-base leading-tight">{{ $totalMahasiswa }} mhs</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- DAFTAR MAHASISWA --}}
<div class="bg-white rounded-xl shadow-sm border border-yellow-200 overflow-hidden mb-4 md:mb-8">
    <div class="bg-yellow-50 border-b border-yellow-200 px-2 md:px-6 py-2 md:py-4">
        {{-- Diubah jadi flex-row biar posisinya sejajar kayak desktop --}}
        <div class="flex flex-row items-center gap-2 md:gap-4">
            <h2 class="text-sm md:text-lg font-semibold text-yellow-800 flex items-center gap-1 md:gap-2 whitespace-nowrap">
                <!-- <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg> -->
                Daftar Mahasiswa
            </h2>

            {{-- FILTER FORM --}}
            <form id="filterForm" method="GET" action="{{ route('pengunjung.detail-perusahaan', $perusahaan->id_perusahaan) }}"
                  class="flex flex-row items-center gap-2 md:gap-3 w-full">
                {{-- Search input --}}
                <div class="relative flex-1 max-w-[140px] md:max-w-xs">
                    <div class="absolute inset-y-0 left-0 pl-2 md:pl-3 flex items-center pointer-events-none">
                        <svg class="w-3 h-3 md:w-4 md:h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" id="searchMhs"
                        value="{{ request('search') }}"
                        placeholder="Cari nama/NPM..."
                        autocomplete="off"
                        class="w-full border border-yellow-300 rounded-lg pl-7 pr-7 py-1 md:pl-10 md:pr-10 md:py-2 text-[10px] md:text-sm bg-white focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    @if(request('search'))
                    <button type="button" id="clearSearchMhs"
                        class="absolute inset-y-0 right-0 pr-2 md:pr-3 flex items-center text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    @endif
                </div>

                {{-- Prodi select --}}
                <select name="id_prodi" id="filterProdi"
                    class="border border-yellow-300 rounded-lg px-1.5 py-1 md:px-3 md:py-2 text-[10px] md:text-sm bg-white focus:outline-none focus:ring-2 focus:ring-yellow-500 whitespace-nowrap">
                    <option value="">Semua Prodi</option>
                    @foreach($prodis as $prodi)
                    <option value="{{ $prodi->id_prodi }}" {{ request('id_prodi') == $prodi->id_prodi ? 'selected' : '' }}>
                        {{ $prodi->nama_prodi }}
                    </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        @if($daftarMahasiswa->count() > 0)
        <table class="w-full text-[10px] md:text-sm text-left whitespace-nowrap md:whitespace-normal">
            <thead class="text-[8px] md:text-xs uppercase bg-yellow-50 border-b border-yellow-200">
                <tr class="text-yellow-800">
                    <th class="px-2 md:px-6 py-2 md:py-4 font-semibold">No</th>
                    <th class="px-2 md:px-6 py-2 md:py-4 font-semibold">Nama Mahasiswa</th>
                    <th class="px-2 md:px-6 py-2 md:py-4 font-semibold">NPM</th>
                    <th class="px-2 md:px-6 py-2 md:py-4 font-semibold">Program Studi</th>
                    <th class="px-2 md:px-6 py-2 md:py-4 font-semibold">Periode Magang</th>
                </tr>
            </thead>
            <tbody>
                @foreach($daftarMahasiswa as $key => $mhs)
                <tr class="bg-white border-b border-gray-100 hover:bg-yellow-50/30 transition">
                    <td class="px-2 md:px-6 py-2 md:py-4 text-gray-500">{{ $key + 1 }}</td>
                    <td class="px-2 md:px-6 py-2 md:py-4">
                        <div class="flex items-center gap-1 md:gap-3">
                            <div class="w-5 h-5 md:w-8 md:h-8 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-yellow-700 font-bold text-[8px] md:text-xs">{{ Str::upper(Str::substr($mhs->nama, 0, 1)) }}</span>
                            </div>
                            <span class="font-medium text-gray-900">{{ $mhs->nama }}</span>
                        </div>
                    </td>
                    <td class="px-2 md:px-6 py-2 md:py-4 text-gray-600">{{ $mhs->npm ?? '-' }}</td>
                    <td class="px-2 md:px-6 py-2 md:py-4">
                        <span class="inline-flex items-center bg-blue-50 text-blue-700 text-[8px] md:text-xs px-1.5 py-0.5 md:px-2.5 md:py-1 rounded-full">
                            {{ $mhs->prodi }}
                        </span>
                    </td>
                    <td class="px-2 md:px-6 py-2 md:py-4 text-gray-600">
                        {{ \Carbon\Carbon::parse($mhs->tgl_mulai)->format('d M Y') }} —
                        {{ \Carbon\Carbon::parse($mhs->tgl_selesai)->format('d M Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="text-center py-8 md:py-16">
            <svg class="w-10 h-10 md:w-14 md:h-14 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            @if(request('search') || request('id_prodi'))
            <p class="text-gray-400 text-xs md:text-base">Tidak ditemukan mahasiswa dengan filter tersebut.</p>
            <a href="{{ route('pengunjung.detail-perusahaan', $perusahaan->id_perusahaan) }}"
               class="mt-2 inline-block text-yellow-600 hover:underline text-[10px] md:text-sm">Reset filter</a>
            @else
            <p class="text-gray-400 text-xs md:text-base">Belum ada data mahasiswa magang di perusahaan ini.</p>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- JAVASCRIPT AUTO-SEARCH --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchMhs');
    const prodiSelect = document.getElementById('filterProdi');
    const clearSearchBtn = document.getElementById('clearSearchMhs');
    let debounceTimer;

    // ✅ Auto-submit saat mengetik (debounce 500ms)
    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            form.submit();
        }, 500);
    });

    // ✅ Auto-submit saat ganti prodi
    prodiSelect.addEventListener('change', function () {
        form.submit();
    });

    // ✅ Tombol clear search → kosongkan & submit
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function () {
            searchInput.value = '';
            form.submit();
        });
    }

    // ✅ Pertahankan posisi kursor
    if (searchInput) {
        searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        searchInput.focus();
    }
});
</script>
@endsection