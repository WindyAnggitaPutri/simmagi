@extends('layouts.app')

@section('content')
<div class="p-6">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Nilai Akhir Mahasiswa</h1>
        <p class="text-gray-500 text-sm mt-1">
            Rekapitulasi nilai akhir magang berdasarkan perhitungan:
            <span class="font-semibold text-orange-600">Perusahaan {{ $bobotPerusahaan }}%</span> +
            <span class="font-semibold text-blue-600">Dosen Pembimbing {{ $bobotPembimbing }}%</span>
        </p>
    </div>

    {{-- Statistik Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-gray-500">
            <p class="text-sm text-gray-500">Total Seminar Selesai</p>
            <p class="text-2xl font-bold text-gray-700">{{ $totalMhs }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Sudah Diproses</p>
            <p class="text-2xl font-bold text-green-600">{{ $sudahProses }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <p class="text-sm text-gray-500">Belum Diproses</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $belumProses }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <p class="text-sm text-gray-500">Nilai Belum Lengkap</p>
            <p class="text-2xl font-bold text-red-600">{{ $belumLengkap }}</p>
        </div>
    </div>

    {{-- Tombol Proses Semua --}}
    <div class="mb-4 flex justify-end">
        <form action="{{ route('kaprodi.nilai-akhir.proses-semua') }}" method="POST"
              onsubmit="return confirm('Proses semua nilai akhir yang belum diproses/diulang?');">
            @csrf
            <button type="submit"
                    class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-lg font-semibold text-sm transition">
                  Proses Semua Nilai Akhir
            </button>
        </form>
    </div>

    {{-- TOOLBAR: Search + Filter --}}
    <div class="bg-white shadow-md border border-gray-200 rounded-t-lg px-5 py-4 mb-0">
        <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">
            {{-- Search Bar --}}
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input 
                    type="text" 
                    value="{{ $search }}" 
                    placeholder="Cari nama, NIM, perusahaan..." 
                    id="searchInput"
                    class="w-full pl-10 pr-10 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition outline-none"
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

            {{-- Filter Per Page --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <label for="perPageSelect" class="text-xs text-gray-500 font-medium whitespace-nowrap">Data/halaman:</label>
                <div class="relative">
                    <select 
                        id="perPageSelect" 
                        onchange="changePerPage(this.value)"
                        class="appearance-none w-28 pl-3 pr-8 py-2.5 text-sm font-semibold border border-gray-300 rounded-lg bg-white text-gray-700 cursor-pointer focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition outline-none"
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

        @if(!empty($search))
        <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            Hasil pencarian untuk: <strong class="text-gray-700">"{{ $search }}"</strong>
            <span class="text-gray-400">—</span>
            <span>ditemukan <strong class="text-gray-700">{{ $dataMahasiswa->count() }}</strong> data</span>
        </div>
        @endif
    </div>

    {{-- Tabel --}}
    <div class="bg-white shadow-md border border-t-0 border-gray-200 rounded-b-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 text-left text-gray-700">
                        <th class="px-4 py-3 border-b w-10">No</th>
                        <th class="px-4 py-3 border-b">NIM</th>
                        <th class="px-4 py-3 border-b">Nama Mahasiswa</th>
                        <th class="px-4 py-3 border-b">Perusahaan</th>
                        <th class="px-4 py-3 border-b text-center">
                            Nilai Perusahaan<br><span class="text-xs text-orange-500">({{ $bobotPerusahaan }}%)</span>
                        </th>
                        <th class="px-4 py-3 border-b text-center">
                            Nilai Dospem<br><span class="text-xs text-blue-500">({{ $bobotPembimbing }}%)</span>
                        </th>
                        <th class="px-4 py-3 border-b text-center">Nilai Akhir</th>
                        <th class="px-4 py-3 border-b text-center">Grade</th>
                        <th class="px-4 py-3 border-b text-center">Status</th>
                        <th class="px-4 py-3 border-b text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataMahasiswa as $i => $data)
                    @php
                        $nomor = $perPage === 'all'
                            ? $i + 1
                            : ($jadwalPaginated->currentPage() - 1) * $jadwalPaginated->perPage() + $i + 1;
                    @endphp
                    <tr class="hover:bg-gray-50 border-b">
                        <td class="px-4 py-3 text-center text-gray-500">{{ $nomor }}</td>
                        <td class="px-4 py-3">{{ $data['jadwal']->mahasiswa->npm ?? '-' }}</td>
                        <td class="px-4 py-3 font-medium">{{ $data['jadwal']->mahasiswa->nama ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $data['jadwal']->perusahaan->nama_perusahaan ?? '-' }}</td>

                        <td class="px-4 py-3 text-center">
                            @if($data['nilai_perusahaan'] !== null)
                                <span class="font-semibold text-orange-600">{{ number_format($data['nilai_perusahaan'], 2) }}</span>
                            @else
                                <span class="text-red-400 text-xs italic">Belum tersedia</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($data['nilai_pembimbing'] !== null)
                                <span class="font-semibold text-blue-600">{{ number_format($data['nilai_pembimbing'], 2) }}</span>
                            @else
                                <span class="text-red-400 text-xs italic">Belum tersedia</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($data['nilai_akhir_calc'] !== null)
                                <span class="font-bold text-lg text-gray-800">{{ number_format($data['nilai_akhir_calc'], 2) }}</span>
                            @else
                                <span class="text-red-400 text-xs italic">-</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($data['grade_calc'])
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
                                    $gc = $gradeColors[$data['grade_calc']] ?? 'bg-gray-100 text-gray-700';
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $gc }}">
                                    {{ $data['grade_calc'] }}
                                </span>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if(! $data['lengkap'])
                                <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-600 font-semibold">
                                      Nilai Belum Lengkap
                                </span>
                            @elseif($data['sudah_disimpan'])
                                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-600 font-semibold">
                                      Sudah Diproses
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-600 font-semibold">
                                      Belum Diproses
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('kaprodi.nilai-akhir.detail', $data['jadwal']->id_jadwal_semgang) }}"
                                   class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium transition"
                                   title="Lihat Detail Perhitungan">
                                      Detail
                                </a>

                                @if($data['lengkap'])
                                    <form action="{{ route('kaprodi.nilai-akhir.proses', $data['jadwal']->id_jadwal_semgang) }}" method="POST"
                                          onsubmit="return confirm('{{ $data['sudah_disimpan'] ? "Proses ulang nilai akhir mahasiswa ini?" : "Proses nilai akhir mahasiswa ini?" }}');">
                                        @csrf
                                        <button type="submit"
                                                class="{{ $data['sudah_disimpan'] ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600' }} text-white px-3 py-1 rounded text-xs font-medium transition"
                                                title="{{ $data['sudah_disimpan'] ? 'Proses Ulang' : 'Proses' }}">
                                            {{ $data['sudah_disimpan'] ? '   Ulang' : ' Proses' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <div>
                                    <p class="text-gray-500 font-medium">Belum ada data</p>
                                    <p class="text-gray-400 text-xs mt-1">Data akan muncul setelah mahasiswa menyelesaikan seminar magang</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($perPage !== 'all' && $jadwalPaginated instanceof \Illuminate\Pagination\LengthAwarePaginator && $jadwalPaginated->hasPages())
        <div class="px-5 py-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs text-gray-500">
                Menampilkan 
                <span class="font-semibold text-gray-700">{{ $jadwalPaginated->firstItem() }}</span> 
                - 
                <span class="font-semibold text-gray-700">{{ $jadwalPaginated->lastItem() }}</span> 
                dari 
                <span class="font-semibold text-gray-700">{{ $jadwalPaginated->total() }}</span> 
                data
            </div>
            <div class="flex items-center gap-1">
                @if($jadwalPaginated->onFirstPage())
                <span class="px-3 py-2 text-xs text-gray-300 bg-gray-50 border border-gray-200 rounded-lg cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </span>
                @else
                <a href="{{ $jadwalPaginated->previousPageUrl() }}" 
                   class="px-3 py-2 text-xs text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @endif

                @if($jadwalPaginated->currentPage() - 2 > 1)
                <a href="{{ $jadwalPaginated->url(1) }}" class="w-9 h-9 flex items-center justify-center text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition">1</a>
                <span class="w-9 h-9 flex items-center justify-center text-xs text-gray-400">...</span>
                @endif

                @foreach($jadwalPaginated->getUrlRange(max(1, $jadwalPaginated->currentPage() - 2), min($jadwalPaginated->lastPage(), $jadwalPaginated->currentPage() + 2)) as $page => $url)
                    @if($page == $jadwalPaginated->currentPage())
                    <span class="w-9 h-9 flex items-center justify-center text-xs font-bold bg-orange-500 text-white rounded-lg shadow-sm">
                        {{ $page }}
                    </span>
                    @else
                    <a href="{{ $url }}" 
                       class="w-9 h-9 flex items-center justify-center text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition">
                        {{ $page }}
                    </a>
                    @endif
                @endforeach

                @if($jadwalPaginated->currentPage() + 2 < $jadwalPaginated->lastPage())
                <span class="w-9 h-9 flex items-center justify-center text-xs text-gray-400">...</span>
                <a href="{{ $jadwalPaginated->url($jadwalPaginated->lastPage()) }}" class="w-9 h-9 flex items-center justify-center text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition">{{ $jadwalPaginated->lastPage() }}</a>
                @endif

                @if($jadwalPaginated->hasMorePages())
                <a href="{{ $jadwalPaginated->nextPageUrl() }}" 
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