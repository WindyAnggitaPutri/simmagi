@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6">

    <h1 class="text-center text-2xl font-bold mb-2">
        PERMOHONAN PENGAJUAN MAGANG INDUSTRI <br>
        MAHASISWA JURUSAN KOMPUTER DAN BISNIS
    </h1>

    {{-- SEARCH --}}
    <div class="mt-6 mb-8 max-w-md mx-auto">
        <div class="relative">
            <input type="text" 
                   id="searchInput"
                   value="{{ $search ?? '' }}" 
                   placeholder="Cari perusahaan, mahasiswa, NPM..." 
                   class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <button type="button" id="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition cursor-pointer {{ ($search ?? '') ? '' : 'hidden' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {{-- TABEL 1: MENUNGGU DITERUSKAN --}}
    <div class="mb-10">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
            <h2 class="text-lg font-bold border-l-4 border-yellow-500 pl-3 pb-2">Menunggu Diteruskan</h2>
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-500 whitespace-nowrap">Tampilkan:</label>
                <select id="menungguPerPage" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 cursor-pointer transition">
                    @foreach([5, 10, 15] as $option)
                        <option value="{{ $option }}" {{ ($menungguPerPage ?? 5) == $option ? 'selected' : '' }}>{{ $option }} data</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if ($menungguList->isEmpty())
            <div class="text-center text-gray-400 py-8 border border-gray-200 bg-gray-50 rounded-lg">
                @if($search ?? false)
                    <div class="flex flex-col items-center gap-2">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Tidak ditemukan data yang cocok dengan "<strong class="text-gray-600">{{ $search }}</strong>"</span>
                    </div>
                @else
                    Tidak ada pengajuan yang menunggu untuk diteruskan.
                @endif
            </div>
        @else
            <div class="overflow-x-auto border border-yellow-400 rounded-lg shadow-sm">
                <table class="min-w-[1100px] w-full text-sm text-left">
                    <thead class="bg-yellow-50 text-xs uppercase text-gray-700 border-b-2 border-yellow-400">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 whitespace-nowrap">Nama Perusahaan</th>
                            <th class="px-4 py-3 whitespace-nowrap">Nama Mahasiswa</th>
                            <th class="px-4 py-3 whitespace-nowrap">Prodi</th>
                            <th class="px-4 py-3 whitespace-nowrap">Tgl Diajukan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Permohonan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Balasan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Pengantar</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($menungguList as $p)
                            @php
                                $warna = $p->status_alur['color'];
                                $textStatus = $p->status_alur['text'];
                                $sudahVerifikasiBalasan = $p->mahasiswa->whereIn('status_surat_balasan', ['diterima', 'ditolak'])->count() > 0;
                            @endphp
                            <tr class="hover:bg-yellow-50/40 transition">
                                <td class="px-4 py-3 text-center font-medium text-gray-600">{{ $menungguList->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-800">{{ $p->perusahaan->nama_perusahaan ?? '-' }}</span>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $p->perusahaan->alamat ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($p->mahasiswa as $pm)
                                            @if ($pm->mahasiswa)
                                                <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 border border-gray-200 rounded">{{ $pm->mahasiswa->nama }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @php $prodiList = $p->mahasiswa->pluck('mahasiswa.prodi.nama_prodi')->filter()->unique(); @endphp
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($prodiList as $namaProdi)
                                            <span class="inline-block bg-blue-50 text-blue-700 text-xs px-2 py-0.5 border border-blue-200 rounded">{{ $namaProdi }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ \Carbon\Carbon::parse($p->tgl_pengajuan)->locale('id')->translatedFormat('d F Y') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block border text-xs font-bold px-3 py-1 whitespace-nowrap {{ $warna }}">{{ strtoupper($textStatus) }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($p->suratPermohonan)
                                        <a href="{{ route('kajur.pengajuan_magang.surat_permohonan', $p->id_pengajuan) }}" class="bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-indigo-700 transition rounded"> Lihat</a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Menunggu BAAK</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($sudahVerifikasiBalasan)
                                        <a href="{{ route('kajur.lihat_surat_balasan', $p->id_pengajuan) }}" class="bg-purple-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-purple-700 transition rounded"> Lihat</a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Menunggu Verifikasi</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-gray-400 text-xs italic">Menunggu BAAK</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('kajur.pengajuan_magang.detail', $p->id_pengajuan) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-sm hover:underline">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($menungguList->hasPages())
                <div class="mt-4 flex flex-col sm:flex-row justify-between items-center gap-3">
                    <p class="text-sm text-gray-500">
                        Menampilkan <span class="font-semibold text-gray-700">{{ $menungguList->firstItem() }}</span> - <span class="font-semibold text-gray-700">{{ $menungguList->lastItem() }}</span> dari <span class="font-semibold text-gray-700">{{ $menungguList->total() }}</span> data
                    </p>
                    <div class="flex items-center gap-1">
                        @if($menungguList->onFirstPage())
                            <span class="px-3 py-1.5 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">‹</span>
                        @else
                            <a href="{{ $menungguList->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">‹</a>
                        @endif
                        @for($page = max(1, $menungguList->currentPage() - 2); $page <= min($menungguList->lastPage(), $menungguList->currentPage() + 2); $page++)
                            @if($page == $menungguList->currentPage())
                                <span class="px-3 py-1.5 text-sm font-bold text-white bg-yellow-500 rounded-lg">{{ $page }}</span>
                            @else
                                <a href="{{ $menungguList->url($page) }}" class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">{{ $page }}</a>
                            @endif
                        @endfor
                        @if($menungguList->hasMorePages())
                            <a href="{{ $menungguList->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">›</a>
                        @else
                            <span class="px-3 py-1.5 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">›</span>
                        @endif
                    </div>
                </div>
            @else
                <div class="mt-3 px-1">
                    <p class="text-sm text-gray-500">Total: <span class="font-semibold text-gray-700">{{ $menungguList->count() }}</span> pengajuan</p>
                </div>
            @endif
        @endif
    </div>

    {{-- TABEL 2: RIWAYAT PENGAJUAN --}}
    <div class="mt-10">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
            <h2 class="text-lg font-bold border-l-4 border-gray-500 pl-3 pb-2">Riwayat Pengajuan</h2>
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-500 whitespace-nowrap">Tampilkan:</label>
                <select id="riwayatPerPage" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 cursor-pointer transition">
                    @foreach([5, 10, 15] as $option)
                        <option value="{{ $option }}" {{ ($riwayatPerPage ?? 5) == $option ? 'selected' : '' }}>{{ $option }} data</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if ($riwayatList->isEmpty())
            <div class="text-center text-gray-400 py-8 border border-gray-200 bg-gray-50 rounded-lg">
                @if($search ?? false)
                    <div class="flex flex-col items-center gap-2">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Tidak ditemukan data yang cocok dengan "<strong class="text-gray-600">{{ $search }}</strong>"</span>
                    </div>
                @else
                    Belum ada riwayat pengajuan.
                @endif
            </div>
        @else
            <div class="overflow-x-auto border border-yellow-400 rounded-lg shadow-sm">
                <table class="min-w-[1100px] w-full text-sm text-left">
                    <thead class="bg-yellow-50 text-xs uppercase text-gray-700 border-b-2 border-yellow-400">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 whitespace-nowrap">Nama Perusahaan</th>
                            <th class="px-4 py-3 whitespace-nowrap">Nama Mahasiswa</th>
                            <th class="px-4 py-3 whitespace-nowrap">Prodi</th>
                            <th class="px-4 py-3 whitespace-nowrap">Tgl Diajukan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Permohonan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Balasan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Pengantar</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($riwayatList as $p)
                            @php
                                $warna = $p->status_alur['color'];
                                $textStatus = $p->status_alur['text'];
                                $sudahVerifikasiBalasan = $p->mahasiswa->whereIn('status_surat_balasan', ['diterima', 'ditolak'])->count() > 0;
                                $totalMhs = $p->mahasiswa->count();
                                $mhsDitolak = $p->mahasiswa->where('status', 'ditolak')->count();
                                $semuaDitolak = ($totalMhs > 0 && $totalMhs === $mhsDitolak);
                                $dikembalikan = str_contains($textStatus, 'Dikembalikan');
                            @endphp
                            <tr class="hover:bg-yellow-50/40 transition">
                                <td class="px-4 py-3 text-center font-medium text-gray-600">{{ $riwayatList->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-700">{{ $p->perusahaan->nama_perusahaan ?? '-' }}</span>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $p->perusahaan->alamat ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($p->mahasiswa as $pm)
                                            @if ($pm->mahasiswa)
                                                <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 border border-gray-200 rounded">{{ $pm->mahasiswa->nama }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @php $prodiListR = $p->mahasiswa->pluck('mahasiswa.prodi.nama_prodi')->filter()->unique(); @endphp
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($prodiListR as $namaProdi)
                                            <span class="inline-block bg-blue-50 text-blue-700 text-xs px-2 py-0.5 border border-blue-200 rounded">{{ $namaProdi }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ \Carbon\Carbon::parse($p->tgl_pengajuan)->locale('id')->translatedFormat('d F Y') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block border text-xs font-bold px-3 py-1 whitespace-nowrap {{ $warna }}">{{ strtoupper($textStatus) }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($p->suratPermohonan)
                                        <a href="{{ route('kajur.pengajuan_magang.surat_permohonan', $p->id_pengajuan) }}" class="bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-indigo-700 transition rounded">  Lihat</a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Menunggu BAAK</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($sudahVerifikasiBalasan)
                                        <a href="{{ route('kajur.lihat_surat_balasan', $p->id_pengajuan) }}" class="bg-purple-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-purple-700 transition rounded"> Lihat</a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Menunggu Verifikasi</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($p->suratPengantar)
                                        <a href="{{ route('kajur.lihat_surat_pengantar', $p->id_pengajuan) }}" class="bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-indigo-700 transition rounded"> Lihat</a>
                                    @elseif($dikembalikan)
                                        <span class="text-orange-400 text-xs font-medium">Dikembalikan</span>
                                    @elseif($semuaDitolak)
                                        <span class="text-red-400 text-xs font-medium">Ditolak</span>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Menunggu BAAK</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('kajur.pengajuan_magang.detail', $p->id_pengajuan) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-sm hover:underline">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($riwayatList->hasPages())
                <div class="mt-4 flex flex-col sm:flex-row justify-between items-center gap-3">
                    <p class="text-sm text-gray-500">
                        Menampilkan <span class="font-semibold text-gray-700">{{ $riwayatList->firstItem() }}</span> - <span class="font-semibold text-gray-700">{{ $riwayatList->lastItem() }}</span> dari <span class="font-semibold text-gray-700">{{ $riwayatList->total() }}</span> data
                    </p>
                    <div class="flex items-center gap-1">
                        @if($riwayatList->onFirstPage())
                            <span class="px-3 py-1.5 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">‹</span>
                        @else
                            <a href="{{ $riwayatList->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">‹</a>
                        @endif
                        @for($page = max(1, $riwayatList->currentPage() - 2); $page <= min($riwayatList->lastPage(), $riwayatList->currentPage() + 2); $page++)
                            @if($page == $riwayatList->currentPage())
                                <span class="px-3 py-1.5 text-sm font-bold text-white bg-yellow-500 rounded-lg">{{ $page }}</span>
                            @else
                                <a href="{{ $riwayatList->url($page) }}" class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">{{ $page }}</a>
                            @endif
                        @endfor
                        @if($riwayatList->hasMorePages())
                            <a href="{{ $riwayatList->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">›</a>
                        @else
                            <span class="px-3 py-1.5 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">›</span>
                        @endif
                    </div>
                </div>
            @else
                <div class="mt-3 px-1">
                    <p class="text-sm text-gray-500">Total: <span class="font-semibold text-gray-700">{{ $riwayatList->count() }}</span> pengajuan</p>
                </div>
            @endif
        @endif
    </div>
</div>

<script>
    let searchTimer = null;
    const searchInput = document.getElementById('searchInput');

    searchInput.addEventListener('input', function() {
        const value = this.value.trim();
        document.getElementById('clearSearch').classList.toggle('hidden', value.length === 0);

        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            const url = new URL(window.location.href);
            if (value.length > 0) {
                url.searchParams.set('search', value);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.delete('menunggu_page');
            url.searchParams.delete('riwayat_page');
            window.location.href = url.toString();
        }, 500);
    });

    document.getElementById('clearSearch').addEventListener('click', function() {
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        url.searchParams.delete('menunggu_page');
        url.searchParams.delete('riwayat_page');
        window.location.href = url.toString();
    });

    document.getElementById('menungguPerPage').addEventListener('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set('menunggu_per_page', this.value);
        url.searchParams.delete('menunggu_page');
        window.location.href = url.toString();
    });

    document.getElementById('riwayatPerPage').addEventListener('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set('riwayat_per_page', this.value);
        url.searchParams.delete('riwayat_page');
        window.location.href = url.toString();
    });
</script>
@endsection