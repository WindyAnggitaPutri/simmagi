@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6">
    {{-- ==================== HEADER ==================== --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-yellow-400">Pembagian Dosen Pembimbing</h1>
        <p class="text-gray-500 text-sm mt-1">Magang Industri Mahasiswa Jurusan Komputer dan Bisnis</p>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
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
        {{-- KIRI: Search --}}
        <div class="flex items-center gap-2 w-full md:w-auto relative">
            <div class="relative flex-1 md:flex-initial">
                <input type="text" 
                       id="searchInput"
                       value="{{ $search ?? '' }}" 
                       placeholder="Cari dosen, mahasiswa, perusahaan..." 
                       class="w-full md:w-80 pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition">
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
        
        {{-- KANAN: Filter Tahun, Filter Kelas, Per Page, Tombol Tambah --}}
        <div class="flex items-center gap-2 flex-wrap">
            {{-- ✅ EXPORT EXCEL (dengan filter) --}}
            <a href="{{ route('kaprodi.dosen-pembimbing.export', array_filter(request()->only(['filter_tahun', 'filter_kelas', 'search']))) }}" 
               class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-4 py-2.5 rounded-lg shadow-sm whitespace-nowrap transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export Excel
            </a>

            {{-- ✅ FILTER TAHUN ANGKATAN --}}
            <div class="flex items-center gap-1.5">
                <span class="text-sm text-gray-500 whitespace-nowrap">Tahun:</span>
                <select id="filterTahunSelect" 
                        class="border border-blue-400 rounded-lg px-2 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 cursor-pointer transition">
                    <option value="{{ route('kaprodi.dosen-pembimbing.index', array_merge(request()->except(['page', 'filter_tahun']))) }}" {{ !$filterTahun ? 'selected' : '' }}>Semua</option>
                    @foreach($daftarTahun as $tahun)
                        <option value="{{ route('kaprodi.dosen-pembimbing.index', array_merge(request()->except('page'), ['filter_tahun' => $tahun])) }}" {{ $filterTahun == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                    @endforeach
                </select>
            </div>

            {{-- ✅ FILTER KELAS (BARU - SEBELAH FILTER TAHUN) --}}
            <div class="flex items-center gap-1.5">
                <span class="text-sm text-gray-500 whitespace-nowrap">Kelas:</span>
                <select id="filterKelasSelect" 
                        class="border border-purple-400 rounded-lg px-2 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-400 cursor-pointer transition">
                    <option value="{{ route('kaprodi.dosen-pembimbing.index', array_merge(request()->except(['page', 'filter_kelas']))) }}" {{ !$filterKelas ? 'selected' : '' }}>Semua</option>
                    @foreach($daftarKelas as $kelas)
                        <option value="{{ route('kaprodi.dosen-pembimbing.index', array_merge(request()->except('page'), ['filter_kelas' => $kelas])) }}" {{ $filterKelas == $kelas ? 'selected' : '' }}>Kelas {{ $kelas }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-px h-6 bg-gray-300 hidden md:block"></div>

            {{-- PER PAGE --}}
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-500 whitespace-nowrap">Tampilkan:</label>
                <select id="perPageSelect" 
                        class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 cursor-pointer transition">
                    @foreach([5, 10, 25, 50, 100] as $option)
                        <option value="{{ $option }}" {{ ($perPage ?? 5) == $option ? 'selected' : '' }}>
                            {{ $option }} data
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-px h-6 bg-gray-300 hidden md:block"></div>

            {{-- ✅ TOMBOL TAMBAH DIPINDAH KE SINI --}}
            <a href="{{ route('kaprodi.dosen-pembimbing.create') }}" 
               class="bg-yellow-500 text-white font-bold text-sm px-5 py-2.5 hover:bg-yellow-600 transition-all duration-200 rounded-lg shadow-sm whitespace-nowrap">
                + Tambah Dospem
            </a>
        </div>
    </div>

    {{-- ==================== INFO FILTER AKTIF ==================== --}}
    @if($filterTahun || $filterKelas || ($search ?? false))
        <div class="mb-4 flex items-center gap-2 flex-wrap">
            <span class="text-sm text-gray-500">Filter aktif:</span>
            @if($filterTahun)
                <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-full border border-blue-200">
                    Tahun {{ $filterTahun }}
                    <button type="button" onclick="removeFilter('filter_tahun')" class="ml-1 hover:text-blue-900 cursor-pointer">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                </span>
            @endif
            @if($filterKelas)
                <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 text-xs font-semibold px-2.5 py-1 rounded-full border border-purple-200">
                    Kelas {{ $filterKelas }}
                    <button type="button" onclick="removeFilter('filter_kelas')" class="ml-1 hover:text-purple-900 cursor-pointer">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                </span>
            @endif
            @if($search ?? false)
                <span class="inline-flex items-center gap-1 bg-yellow-50 text-yellow-700 text-xs font-semibold px-2.5 py-1 rounded-full border border-yellow-200">
                    Cari: "{{ $search }}"
                    <button type="button" onclick="removeFilter('search')" class="ml-1 hover:text-yellow-900 cursor-pointer">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                </span>
            @endif
            <button type="button" onclick="clearAllFilters()" class="text-xs text-red-500 hover:text-red-700 hover:underline cursor-pointer ml-2">
                Hapus semua filter
            </button>
        </div>
    @endif

    {{-- ==================== TABEL DATA ==================== --}}
    <div>
        <div class="bg-white shadow-md border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-yellow-500 text-white">
                        <tr>
                            <th class="p-4 text-center w-12">No</th>
                            <th class="p-4">Nama Dosen</th>
                            <th class="p-4 text-center w-28">Tahun Angkatan</th>
                            <th class="p-4 text-center w-20">Kelas</th>
                            <th class="p-4">Nama Mahasiswa</th>
                            <th class="p-4">Perusahaan</th>
                            <th class="p-4 text-center w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                  
@php
    $groupedDospem = $semuaDospem->getCollection()
        ->groupBy(function($item) {
            // ✅ Group by Dosen + Angkatan SAJA (kelas dihapus dari key)
            return $item->id_dosen . '_' . ($item->mahasiswa->angkatan ?? 'unknown');
        });
    $no = $semuaDospem->firstItem() ?? 1;
@endphp
                        @if($groupedDospem->isEmpty())
                            <tr>
                                <td colspan="7" class="p-10 text-center text-gray-400">
                                    @if($search ?? false)
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="fas fa-search text-4xl text-gray-300"></i>
                                            <span>Tidak ditemukan data yang cocok dengan "<strong class="text-gray-600">{{ $search }}</strong>"</span>
                                            <button type="button" onclick="clearAndReload()" class="text-yellow-600 hover:underline text-sm font-semibold mt-1 cursor-pointer">Lihat semua data</button>
                                        </div>
                                    @elseif($filterTahun ?? false)
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="fas fa-filter text-4xl text-gray-300"></i>
                                            <span>Tidak ada data dosen pembimbing untuk tahun angkatan <strong class="text-gray-600">{{ $filterTahun }}</strong></span>
                                            <button type="button" onclick="resetFilterTahun()" class="text-yellow-600 hover:underline text-sm font-semibold mt-1 cursor-pointer">Lihat semua tahun</button>
                                        </div>
                                    @elseif($filterKelas ?? false)
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="fas fa-users text-4xl text-gray-300"></i>
                                            <span>Tidak ada data dosen pembimbing untuk kelas <strong class="text-gray-600">{{ $filterKelas }}</strong></span>
                                            <button type="button" onclick="resetFilterKelas()" class="text-yellow-600 hover:underline text-sm font-semibold mt-1 cursor-pointer">Lihat semua kelas</button>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="fas fa-clipboard-list text-4xl text-gray-300"></i>
                                            <span>Belum ada data dosen pembimbing. Klik "Tambah Dospem" untuk memulai.</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @else
                            @foreach ($groupedDospem as $groupKey => $kelompokDosen)
                                @php $rowspan = $kelompokDosen->count(); @endphp
                                @php $firstItem = $kelompokDosen->first(); @endphp
                                
                                @foreach ($kelompokDosen as $item)
                                    <tr class="border-b border-gray-100 hover:bg-yellow-50 transition">
                                        @if ($loop->first)
                                            <td rowspan="{{ $rowspan }}" class="p-4 text-center text-gray-500 font-medium align-top border-b border-gray-100 bg-yellow-50/30">
                                                {{ $no++ }}
                                            </td>
                                            <td rowspan="{{ $rowspan }}" class="p-4 font-semibold text-gray-900 align-top border-b border-gray-100 bg-yellow-50/30">
                                                {{ $firstItem->dosen->nama ?? '-' }}
                                                <span class="text-gray-400 font-normal text-xs block mt-0.5">{{ $firstItem->dosen->nidn ?? '-' }}</span>
                                            </td>
                                            <td rowspan="{{ $rowspan }}" class="p-4 text-center align-top border-b border-gray-100 bg-yellow-50/30">
                                                <span class="inline-block bg-blue-50 text-blue-700 text-xs font-bold px-2 py-1 border border-blue-200 rounded">
                                                    {{ $firstItem->mahasiswa->angkatan ?? '-' }}
                                                </span>
                                            </td>
                                           <td rowspan="{{ $rowspan }}" class="p-4 text-center align-top border-b border-gray-100 bg-yellow-50/30">
    @php
        // ✅ Kumpulkan semua kelas unik dalam kelompok ini
        $daftarKelas = $kelompokDosen->pluck('mahasiswa.kelas')
            ->filter(function($k) { return $k && trim($k) !== ''; })
            ->unique()
            ->sort()
            ->values();
    @endphp
    @if($daftarKelas->isEmpty())
        <span class="text-gray-400">-</span>
    @else
        @foreach($daftarKelas as $k)
            <span class="inline-block bg-purple-50 text-purple-700 text-xs font-bold px-2.5 py-1 border border-purple-200 rounded">
                {{ $k }}
            </span>
            @if(!$loop->last)<br>@endif
        @endforeach
    @endif
</td>
                                        @endif

                                        <td class="p-4">
                                            {{ $item->mahasiswa->nama ?? '-' }}
                                            <span class="text-gray-400 text-xs block">{{ $item->mahasiswa->npm ?? '-' }}</span>
                                        </td>

                                        <td class="p-4">
                                            {{ $item->pengajuanMagang->perusahaan->nama_perusahaan ?? '-' }}
                                        </td>

                                        <td class="p-4 text-center">
                                            <div class="flex justify-center gap-2">
                                                <a href="{{ route('kaprodi.dosen-pembimbing.detail', $item->id_dosen_pembimbing) }}" 
                                                   class="text-green-600 hover:underline text-xs font-semibold">Detail</a>
                                                <a href="{{ route('kaprodi.dosen-pembimbing.edit', $item->id_dosen_pembimbing) }}" 
                                                   class="text-blue-600 hover:underline text-xs font-semibold">Edit</a>
                                                <button type="button" 
                                                        onclick="openDeleteModal('{{ route('kaprodi.dosen-pembimbing.destroy', $item->id_dosen_pembimbing) }}')" 
                                                        class="text-red-600 hover:underline text-xs font-semibold cursor-pointer">Hapus</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- ==================== PAGINATION ==================== --}}
            @if($semuaDospem->hasPages())
                <div class="px-4 py-4 border-t border-gray-200 bg-gray-50/50 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p class="text-sm text-gray-500">
                        Menampilkan 
                        <span class="font-semibold text-gray-700">{{ $semuaDospem->firstItem() }}</span> - 
                        <span class="font-semibold text-gray-700">{{ $semuaDospem->lastItem() }}</span> 
                        dari 
                        <span class="font-semibold text-gray-700">{{ $semuaDospem->total() }}</span> data
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
                        @if($semuaDospem->onFirstPage())
                            <span class="px-3 py-1.5 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </span>
                        @else
                            <a href="{{ $semuaDospem->previousPageUrl() }}" 
                               class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </a>
                        @endif

                        @php
                            $start = max(1, $semuaDospem->currentPage() - 2);
                            $end   = min($semuaDospem->lastPage(), $semuaDospem->currentPage() + 2);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $semuaDospem->url(1) }}" 
                               class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">1</a>
                            @if($start > 2)
                                <span class="px-2 text-gray-400">...</span>
                            @endif
                        @endif

                        @for($page = $start; $page <= $end; $page++)
                            @if($page == $semuaDospem->currentPage())
                                <span class="px-3 py-1.5 text-sm font-bold text-white bg-yellow-500 rounded-lg shadow-sm">{{ $page }}</span>
                            @else
                                <a href="{{ $semuaDospem->url($page) }}" 
                                   class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">{{ $page }}</a>
                            @endif
                        @endfor

                        @if($end < $semuaDospem->lastPage())
                            @if($end < $semuaDospem->lastPage() - 1)
                                <span class="px-2 text-gray-400">...</span>
                            @endif
                            <a href="{{ $semuaDospem->url($semuaDospem->lastPage()) }}" 
                               class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">{{ $semuaDospem->lastPage() }}</a>
                        @endif

                        @if($semuaDospem->hasMorePages())
                            <a href="{{ $semuaDospem->nextPageUrl() }}" 
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
        </div>
    </div>
</div>

{{-- ==================== MODAL PREVIEW SURAT FINAL ==================== --}}
<div id="modalPreviewSurat" class="fixed inset-0 z-50 hidden overflow-auto bg-gray-800 flex items-start justify-center py-8 px-4">
    <div class="bg-white w-[210mm] min-h-[297mm] shadow-lg relative" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; color: #000; padding: 25mm 30mm;">
        
        <div class="text-center mb-6">
            <p style="font-size: 14pt; font-weight: bold; letter-spacing: 1px;">DAFTAR MAHASISWA BIMBINGAN MAGANG INDUSTRI</p>
            <p style="font-size: 14pt; font-weight: bold;">TA {{ date('Y') }}/2026</p>
        </div>

        <div class="space-y-4">
            @if($previewDospem->isEmpty())
                <p class="text-center text-gray-400 py-20">Belum ada data dosen pembimbing yang ditugaskan.</p>
            @else
                @foreach($previewDospem as $idDosen => $kelompok)
                    <div>
                        <p style="font-size: 12pt; font-weight: bold; margin-bottom: 4px;">
                            {{ $loop->iteration }}. {{ $kelompok->first()->dosen->nama ?? '-' }}{{ $kelompok->first()->dosen->nidn ? ', ' . $kelompok->first()->dosen->nidn : '' }}
                        </p>
                        <table style="width: 100%; border-collapse: collapse; font-size: 11pt;">
                            <thead>
                                <tr style="border: 1px solid #000;">
                                    <th style="border: 1px solid #000; padding: 4px 6px; width: 30px; text-align: center; font-weight: bold;">NO</th>
                                    <th style="border: 1px solid #000; padding: 4px 6px; width: 100px; text-align: center; font-weight: bold;">NPM</th>
                                    <th style="border: 1px solid #000; padding: 4px 6px; width: 50px; text-align: center; font-weight: bold;">KELAS</th>
                                    <th style="border: 1px solid #000; padding: 4px 6px; text-align: left; font-weight: bold;">NAMA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kelompok as $idx => $mhs)
                                <tr style="border: 1px solid #000;">
                                    <td style="border: 1px solid #000; padding: 3px 6px; text-align: center;">{{ $idx + 1 }}</td>
                                    <td style="border: 1px solid #000; padding: 3px 6px; text-align: center;">{{ $mhs->mahasiswa->npm ?? '-' }}</td>
                                    <td style="border: 1px solid #000; padding: 3px 6px; text-align: center;">{{ $mhs->mahasiswa->kelas ?? '-' }}</td>
                                    <td style="border: 1px solid #000; padding: 3px 6px; padding-left: 10px;">{{ $mhs->mahasiswa->nama ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @endif
        </div>

        <div style="margin-top: 40px;">
            <div style="text-align: right; width: 250px; margin-left: auto;">
                <p style="font-size: 12pt; font-weight: bold;">Cilacap, {{ date('d F Y') }}</p>
                <p style="font-size: 12pt; font-weight: bold; margin-top: 2px;">Jurusan Komputer dan Bisnis</p>
                <p style="font-size: 12pt; margin-top: 2px;">Ketua,</p>
                <div style="margin-top: 70px; margin-bottom: 2px;">
                    <p style="font-size: 12pt; font-weight: bold; text-decoration: underline;">{{ $dosenLogin->nama ?? '-' }}</p>
                    @if($dosenLogin->nip)
                        <p style="font-size: 10pt;">NIP. {{ $dosenLogin->nip }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="absolute -top-10 right-0">
            <button type="button" onclick="closePreviewSurat()" 
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm font-bold shadow">
                ✕ Tutup
            </button>
        </div>
    </div>
</div>

{{-- ==================== MODAL HAPUS ==================== --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-auto bg-black/30 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 transform transition-all">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                <i class="fas fa-trash-alt text-red-500 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Hapus Penugasan</h3>
            <p class="text-sm text-gray-500 mb-6">Yakin akan menghapus penugasan dosen pembimbing ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex gap-3 justify-center">
                <button onclick="closeDeleteModal()" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-300">Batal</button>
                <form id="deleteForm" method="POST" class="inline-block">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition duration-300 shadow-md">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // ========== SEARCH OTOMATIS (debounce 500ms) ==========
    let searchTimer = null;
    const searchInput = document.getElementById('searchInput');

    searchInput.addEventListener('input', function() {
        const value = this.value.trim();
        const clearBtn = document.getElementById('clearSearch');

        if (clearBtn) {
            clearBtn.style.display = value.length > 0 ? 'block' : 'none';
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

    // ========== TOMBOL X HAPUS SEARCH ==========
    const clearSearchBtn = document.getElementById('clearSearch');
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            clearAndReload();
        });
    }

    function clearAndReload() {
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // ========== FILTER TAHUN ANGKATAN ==========
    document.getElementById('filterTahunSelect').addEventListener('change', function() {
        window.location.href = this.value;
    });

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

    // ========== HAPUS FILTER TERTENTU ==========
    function removeFilter(filterName) {
        const url = new URL(window.location.href);
        url.searchParams.delete(filterName);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // ========== HAPUS SEMUA FILTER ==========
    function clearAllFilters() {
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        url.searchParams.delete('filter_tahun');
        url.searchParams.delete('filter_kelas');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // ========== PER PAGE SELECTOR ==========
    document.getElementById('perPageSelect').addEventListener('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', this.value);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    });

    // ========== Preview Surat ==========
    function openPreviewSurat() {
        const modal = document.getElementById('modalPreviewSurat');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closePreviewSurat() {
        const modal = document.getElementById('modalPreviewSurat');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.getElementById('modalPreviewSurat').addEventListener('click', function(e) {
        if (e.target === this) closePreviewSurat();
    });

    // ========== Hapus ==========
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

    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });

    // ========== Keyboard: ESC ==========
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePreviewSurat();
            closeDeleteModal();
        }
    });
</script>
@endsection