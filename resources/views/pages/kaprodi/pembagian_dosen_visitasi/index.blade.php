@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6">
    {{-- ==================== HEADER ==================== --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-yellow-400">Pembagian Dosen Visitasi</h1>
        <p class="text-gray-500 text-sm mt-1">Daftar dosen visitasi beserta mahasiswa yang divisitasi</p>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @if (session('errors') && count(session('errors')->all()) > 0)
        <div class="mb-6 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg">
            @foreach (session('errors')->all() as $error)
                <p class="text-sm">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- ==================== SEARCH & FILTER & TOMBOL ==================== --}}
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    <div class="flex items-center gap-2 w-full md:w-auto relative">
        <div class="relative flex-1 md:flex-initial">
            <input type="text" 
                   id="searchInput"
                   value="{{ $search ?? '' }}" 
                   placeholder="Cari dosen, mahasiswa, perusahaan..." 
                   class="w-full md:w-64 pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                @if($search ?? false)
                    <button type="button" 
                            id="clearSearch"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition cursor-pointer">
                        <i class="fas fa-times-circle text-sm"></i>
                    </button>
                @endif
            </div>
        </div>
        
        <div class="flex items-center gap-2 flex-wrap">
<a href="{{ route('kaprodi.pembagian-dosen-visitasi.export', array_filter(request()->only(['filter_tahun', 'filter_kelas', 'search', 'filter_tahun_visitasi']))) }}" 
   class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-4 py-2.5 rounded-lg shadow-sm whitespace-nowrap transition">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
    Export Excel
</a>
        <div class="flex items-center gap-1.5">
                <span class="text-sm text-gray-500 whitespace-nowrap">Angkatan:</span>
                <select id="filterTahunSelect" 
                        class="border border-blue-400 rounded-lg px-2 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 cursor-pointer transition">
                    <option value="{{ route('kaprodi.pembagian-dosen-visitasi.index', array_merge(request()->except(['page', 'filter_tahun']))) }}" {{ !$filterTahun ? 'selected' : '' }}>Semua</option>
                    @foreach($daftarTahun as $tahun)
                        <option value="{{ route('kaprodi.pembagian-dosen-visitasi.index', array_merge(request()->except('page'), ['filter_tahun' => $tahun])) }}" {{ $filterTahun == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                    @endforeach
                </select>
            </div>
                {{--  FILTER TAHUN VISITASI (BARU) --}}
          <div class="flex items-center gap-1.5">
            <span class="text-sm text-gray-500 whitespace-nowrap">Th. Visitasi:</span>
            <select onchange="window.location.href=this.value"
                    class="border border-orange-400 rounded-lg px-2 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 cursor-pointer transition">
                <option value="{{ route('kaprodi.pembagian-dosen-visitasi.index', array_merge(request()->except(['page', 'filter_tahun_visitasi']))) }}" {{ !$filterTahunVisitasi ? 'selected' : '' }}>Semua</option>
                @foreach($daftarTahunVisitasi as $tahun)
                    <option value="{{ route('kaprodi.pembagian-dosen-visitasi.index', array_merge(request()->except('page'), ['filter_tahun_visitasi' => $tahun])) }}" {{ $filterTahunVisitasi == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                @endforeach
            </select>
        </div>

          {{--  FILTER KELAS (BARU) --}}
<div class="flex items-center gap-1.5">
    <span class="text-sm text-gray-500 whitespace-nowrap">Kelas:</span>
    <select id="filterKelasSelect" 
            class="border border-purple-400 rounded-lg px-2 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-400 cursor-pointer transition">
        <option value="{{ route('kaprodi.pembagian-dosen-visitasi.index', array_merge(request()->except(['page', 'filter_kelas']))) }}" {{ !$filterKelas ? 'selected' : '' }}>Semua</option>
        @foreach($daftarKelas as $kelas)
            <option value="{{ route('kaprodi.pembagian-dosen-visitasi.index', array_merge(request()->except('page'), ['filter_kelas' => $kelas])) }}" {{ $filterKelas == $kelas ? 'selected' : '' }}>{{ $kelas }}</option>
        @endforeach
    </select>
</div>

<div class="w-px h-6 bg-gray-300 hidden md:block"></div>
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

            <div class="w-px h-6 bg-gray-300 hidden md:block"></div>

            <a href="{{ route('kaprodi.pembagian-dosen-visitasi.create') }}" 
               class="bg-yellow-500 text-white font-bold text-sm px-5 py-2.5 hover:bg-yellow-600 transition-all duration-200 rounded-lg shadow-sm whitespace-nowrap">
                + Tambah Visitasi
            </a>
        </div>
    </div>

    {{-- ==================== TABEL DATA ==================== --}}
    @if($results->isEmpty())
        <div class="text-center py-10 bg-gray-50 border border-gray-200 rounded-lg">
            @if($search ?? false)
                <div class="flex flex-col items-center gap-2 text-gray-400">
                    <i class="fas fa-search text-4xl text-gray-300"></i>
                    <span>Tidak ditemukan data yang cocok dengan "<strong class="text-gray-600">{{ $search }}</strong>"</span>
                    <button type="button" onclick="clearAndReload()" class="text-yellow-600 hover:underline text-sm font-semibold cursor-pointer">Lihat semua data</button>
                </div>
            @elseif($filterTahun ?? false)
                <div class="flex flex-col items-center gap-2 text-gray-400">
                    <i class="fas fa-filter text-4xl text-gray-300"></i>
                    <span>Tidak ada data dosen visitasi untuk tahun angkatan <strong class="text-gray-600">{{ $filterTahun }}</strong></span>
                    <button type="button" onclick="resetFilterTahun()" class="text-yellow-600 hover:underline text-sm font-semibold cursor-pointer">Lihat semua tahun</button>
                </div>
            @else
                <div class="flex flex-col items-center gap-2 text-gray-400">
                    <i class="fas fa-clipboard-list text-4xl text-gray-300"></i>
                    <span class="text-gray-500">Belum ada data dosen visitasi saat ini. Klik "Tambah Visitasi" untuk memulai.</span>
                </div>
            @endif
        </div>
    @else
       @php
    $grouped = $results->getCollection()
        ->sortBy(function($item) {
            return ($item->dosen->nama ?? 'zzz') . '_' . ($item->perusahaan->nama_perusahaan ?? 'zzz') . '_' . ($item->tahun ?? 0);
        })
        ->groupBy(function($item) {
            //  GROUP BY: dosen + perusahaan + tahun visitasi
            return $item->id_dosen . '_' . ($item->id_perusahaan ?? 'unknown') . '_' . ($item->tahun ?? 'unknown');
        });

    $no = $results->firstItem() ?? 1;
@endphp

<div class="bg-white shadow-md border border-yellow-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-yellow-500 text-white">
                <tr>
                    <th class="p-4 text-center w-12">No</th>
                    <th class="p-4">Dosen Visitasi</th>
                    <th class="p-4">Nama Mahasiswa</th>
                    <th class="p-4 text-center w-32">Tahun Visitasi</th>
                    <th class="p-4 text-center w-28">Tahun Angkatan</th>
                    <th class="p-4 text-center w-20">Kelas</th>
                    <th class="p-4">NPM</th>
                    <th class="p-4">Perusahaan</th>
                    <th class="p-4 text-center w-32">Visitasi</th>
                    <th class="p-4 text-center w-40">Interview Personal Magang</th>
                    <th class="p-4 text-center w-32">Aksi</th>
                </tr>
            </thead>
                <tbody class="text-gray-700">
                @foreach ($grouped as $groupKey => $items)
                    @php $rowspan = $items->count(); @endphp
                    @php $firstItem = $items->first(); @endphp

                    @php
                        //  CEK STATUS VISITASI (berdasarkan tgl_visitasi & foto di tabel dosen_visitasi)
                        $groupHasVisitasi = false;
                        foreach ($items as $groupItem) {
                            if ($groupItem->tgl_visitasi && $groupItem->foto) {
                                $groupHasVisitasi = true;
                                break;
                            }
                        }

                        //  CEK STATUS INTERVIEW (berdasarkan tabel Interview A & B)
                        $groupHasInterviewA = false;
                        $groupHasInterviewB = false;
                        foreach ($items as $groupItem) {
                            if (isset($hasInterviewA) && $hasInterviewA->has($groupItem->id_dosen_visitasi)) $groupHasInterviewA = true;
                            if (isset($hasInterviewB) && $hasInterviewB->has($groupItem->id_dosen_visitasi)) $groupHasInterviewB = true;
                        }
                        $groupHasInterview = $groupHasInterviewA && $groupHasInterviewB;
                    @endphp

                    @foreach ($items as $index => $item)
                        <tr class="border-b border-gray-100 hover:bg-yellow-50 transition">
                            
                            {{-- NO --}}
                            @if ($index == 0)
                                <td rowspan="{{ $rowspan }}" class="p-4 text-center text-gray-500 font-medium border-b border-gray-100 bg-yellow-50/30">
                                    <div class="flex items-center justify-center h-full min-h-[40px]">
                                        {{ $no++ }}
                                    </div>
                                </td>
                            @endif

                            {{-- DOSEN VISITASI --}}
                            @if ($index == 0)
                                <td rowspan="{{ $rowspan }}" class="p-4 font-semibold text-gray-900 border-b border-gray-100 bg-yellow-50/30">
                                    <div class="flex flex-col justify-center h-full min-h-[40px]">
                                        {{ $firstItem->dosen->nama ?? '-' }}
                                        <span class="text-gray-400 font-normal text-xs mt-0.5">{{ $firstItem->dosen->nidn ?? '-' }}</span>
                                    </div>
                                </td>
                            @endif

                            {{-- NAMA MAHASISWA --}}
                            <td class="p-4 font-semibold text-gray-900">
                                {{ $item->mahasiswa->nama ?? '-' }}
                            </td>

                            {{-- TAHUN VISITASI --}}
                            @if ($index == 0)
                                <td rowspan="{{ $rowspan }}" class="p-4 text-center border-b border-gray-100 bg-indigo-50/30">
                                    <div class="flex items-center justify-center h-full min-h-[40px]">
                                        <span class="inline-block bg-indigo-50 text-indigo-700 text-xs font-bold px-2 py-1 border border-indigo-200 rounded">
                                            {{ $firstItem->tahun ?? '-' }}
                                        </span>
                                    </div>
                                </td>
                            @endif

                            {{-- TAHUN ANGKATAN --}}
                            <td class="p-4 text-center">
                                <span class="inline-block bg-blue-50 text-blue-700 text-xs font-bold px-2 py-1 border border-blue-200 rounded">
                                    {{ $item->mahasiswa->angkatan ?? '-' }}
                                </span>
                            </td>

                            {{-- KELAS --}}
                            <td class="p-4 text-center">
                                <span class="inline-block bg-purple-50 text-purple-700 text-xs font-bold px-2.5 py-1 border border-purple-200 rounded">
                                    {{ $item->mahasiswa->kelas ?? '-' }}
                                </span>
                            </td>

                            {{-- NPM --}}
                            <td class="p-4 text-gray-600">
                                {{ $item->mahasiswa->npm ?? '-' }}
                            </td>

                            {{-- PERUSAHAAN --}}
                            @if ($index == 0)
                                <td rowspan="{{ $rowspan }}" class="p-4 border-b border-gray-100 bg-green-50/30">
                                    <div class="flex items-center h-full min-h-[40px]">
                                        {{ $firstItem->perusahaan->nama_perusahaan ?? '-' }}
                                    </div>
                                </td>
                            @endif

                            {{--  VISITASI: Cek tgl_visitasi & foto --}}
                            @if ($index == 0)
                                <td rowspan="{{ $rowspan }}" class="p-4 text-center border-b border-gray-100">
                                    <div class="flex items-center justify-center h-full min-h-[40px]">
                                        @if($groupHasVisitasi)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">
                                                 Selesai
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold">
                                                 Belum
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            @endif

                            {{--  INTERVIEW: Cek tabel Interview A & B, dan berikan link Isi Form jika belum --}}
                            @if ($index == 0)
                                <td rowspan="{{ $rowspan }}" class="p-4 text-center border-b border-gray-100">
                                    <div class="flex items-center justify-center h-full min-h-[40px]">
                                        @if($groupHasInterview)
                                            <a href="{{ route('kaprodi.interview-detail', $firstItem->id_perusahaan) }}"
                                               class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold hover:bg-blue-200 transition">
                                                 Lihat
                                            </a>
                                        @else
                                            <a href="{{ route('kaprodi.interview-detail', $firstItem->id_perusahaan) }}"
                                               class="inline-flex items-center gap-1 px-2.5 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-bold hover:bg-orange-200 transition">
                                                 Isi Form
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            @endif

                            {{-- AKSI --}}
                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('kaprodi.pembagian-dosen-visitasi.detail', $item->id_dosen_visitasi) }}"
                                       class="text-green-600 hover:underline text-xs font-semibold">Detail</a>
                                    <a href="{{ route('kaprodi.pembagian-dosen-visitasi.edit', $item->id_dosen_visitasi) }}"
                                       class="text-blue-600 hover:underline text-xs font-semibold">Edit</a>
                                    <button type="button"
                                            onclick="openDeleteModal('{{ route('kaprodi.pembagian-dosen-visitasi.destroy', $item->id_dosen_visitasi) }}')"
                                            class="text-red-600 hover:underline text-xs font-semibold cursor-pointer">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ==================== MODAL HAPUS ==================== --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-auto bg-black/30 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 transform transition-all">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Hapus Penugasan</h3>
            <p class="text-sm text-gray-500 mb-6">Yakin akan menghapus penugasan dosen visitasi ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex gap-3 justify-center">
                <button onclick="closeDeleteModal()" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-300">Kembali</button>
                <form id="deleteForm" method="POST" class="inline-block">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition duration-300 shadow-md">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let searchTimer = null;
    const searchInput = document.getElementById('searchInput');

    searchInput.addEventListener('input', function() {
        const value = this.value.trim();
        const clearBtn = document.getElementById('clearSearch');
        if (clearBtn) clearBtn.style.display = value.length > 0 ? 'block' : 'none';
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            const url = new URL(window.location.href);
            if (value.length > 0) { url.searchParams.set('search', value); } else { url.searchParams.delete('search'); }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }, 500);
    });

    const clearSearchBtn = document.getElementById('clearSearch');
    if (clearSearchBtn) clearSearchBtn.addEventListener('click', function() { clearAndReload(); });

    function clearAndReload() {
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    document.getElementById('filterTahunSelect').addEventListener('change', function() { window.location.href = this.value; });

    function resetFilterTahun() {
        const url = new URL(window.location.href);
        url.searchParams.delete('filter_tahun');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }
        // ========== FILTER KELAS (BARU) ==========
    document.getElementById('filterKelasSelect').addEventListener('change', function() {
        window.location.href = this.value;
    });

    function resetFilterKelas() {
        const url = new URL(window.location.href);
        url.searchParams.delete('filter_kelas');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    document.getElementById('perPageSelect').addEventListener('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', this.value);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    });

    function openDeleteModal(actionUrl) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        form.action = actionUrl;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.getElementById('deleteModal').addEventListener('click', function(e) { if (e.target === this) closeDeleteModal(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeDeleteModal(); });
</script>
@endsection