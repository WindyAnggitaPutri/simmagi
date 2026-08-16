@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6">

    {{-- Judul --}}
    <h1 class="text-center text-2xl font-bold mb-2">
        PERMOHONAN PENGAJUAN MAGANG INDUSTRI <br>
        MAHASISWA JURUSAN KOMPUTER DAN BISNIS
    </h1>

    {{-- ========================================== --}}
    {{-- CARD PENETAPAN TANGGAL GLOBAL PRODI       --}}
    {{-- ========================================== --}}
    <div class="mt-8 bg-white p-5 rounded-lg shadow-md border border-yellow-300">
        <h3 class="font-bold text-gray-800 mb-1 border-b border-yellow-200 pb-2"> Penetapan Waktu Magang Prodi</h3>
       <p class="text-sm text-gray-500 mb-4">
    Tentukan tanggal magang untuk seluruh mahasiswa prodi Anda. 
    Perubahan akan berlaku untuk semua pengajuan di prodi Anda.
</p>
        <form action="{{ route('kaprodi.pengajuan_magang.update_tanggal_prodi') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="tgl_mulai" value="{{ old('tgl_mulai', $tglProdi->tgl_mulai ?? '') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Selesai <span class="text-red-500">*</span></label>
                    <input type="date" name="tgl_selesai" value="{{ old('tgl_selesai', $tglProdi->tgl_selesai ?? '') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500" required>
                </div>
                <div class="flex justify-start md:justify-end">
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2.5 rounded text-sm font-bold shadow transition">
                         Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ========================================== --}}
    {{-- TABEL 1: PENGAJUAN MASUK (DALAM PROSES)  --}}
    {{-- ========================================== --}}
    <div class="mt-8 mb-10">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold border-l-4 border-yellow-500 pl-3 pb-2">Pengajuan Masuk</h2>
            <div class="flex items-center gap-3">
                {{--  FILTER TAHUN ANGKATAN --}}
                <div class="flex items-center gap-1.5">
                    <span class="text-sm text-gray-600">Tahun:</span>
                    <select onchange="window.location.href=this.value" class="border border-blue-400 rounded-md px-2 py-1.5 text-sm bg-white focus:ring-blue-500 focus:border-blue-500">
                        <option value="{{ route('kaprodi.pengajuan_magang.index', array_merge(request()->except(['masuk_page', 'riwayat_page', 'filter_tahun']))) }}" {{ !$filterTahun ? 'selected' : '' }}>Semua</option>
                        @foreach($daftarTahun as $tahun)
                            <option value="{{ route('kaprodi.pengajuan_magang.index', array_merge(request()->except(['masuk_page', 'riwayat_page']), ['filter_tahun' => $tahun])) }}" {{ $filterTahun == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-px h-6 bg-gray-300"></div>
                {{-- DROPDOWN DATA PER HALAMAN --}}
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">Tampilkan:</span>
                    <select onchange="window.location.href=this.value" class="border border-yellow-400 rounded-md px-2 py-1.5 text-sm bg-white focus:ring-yellow-500 focus:border-yellow-500">
                        <option value="{{ route('kaprodi.pengajuan_magang.index', array_merge(request()->except('masuk_page'), ['masuk_per_page' => 5])) }}" {{ request('masuk_per_page', 5) == 5 ? 'selected' : '' }}>5</option>
                        <option value="{{ route('kaprodi.pengajuan_magang.index', array_merge(request()->except('masuk_page'), ['masuk_per_page' => 10])) }}" {{ request('masuk_per_page', 5) == 10 ? 'selected' : '' }}>10</option>
                    </select>
                    <span class="text-sm text-gray-600">data</span>
                </div>
            </div>
        </div>

        @if ($pengajuanMasuk->isEmpty())
            <div class="text-center text-gray-400 py-8 border border-gray-200 bg-gray-50 rounded-lg">
                @if($filterTahun)
                    Tidak ada pengajuan dari tahun angkatan <strong>{{ $filterTahun }}</strong> yang sedang menunggu proses.
                @else
                    Tidak ada pengajuan yang sedang menunggu proses.
                @endif
            </div>
        @else
            <div class="overflow-x-auto border border-yellow-400 rounded-lg shadow-sm">
                <table class="min-w-[1200px] w-full text-sm text-left">
                    <thead class="bg-yellow-50 text-xs uppercase text-gray-700 border-b-2 border-yellow-400">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 whitespace-nowrap">Nama Perusahaan</th>
                            <th class="px-4 py-3 whitespace-nowrap">Nama Mahasiswa</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap w-20">Tahun Angkatan</th>
                            <th class="px-4 py-3 whitespace-nowrap">Tgl Diajukan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Status Pengajuan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Permohonan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Balasan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Pengantar</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($pengajuanMasuk as $p)
                            @php
                                $warna = $p->status_alur['color'];
                                $textStatus = $p->status_alur['text'];
                                $adaFileSuratBalasan = $p->mahasiswa->whereNotNull('surat_balasan')->count() > 0;
                                $nomor = $pengajuanMasuk->firstItem() + $loop->index;
                                
                                $angkatanList = [];
                                foreach ($p->mahasiswa as $pm) {
                                    if ($pm->mahasiswa && $pm->mahasiswa->angkatan && !in_array($pm->mahasiswa->angkatan, $angkatanList)) {
                                        $angkatanList[] = $pm->mahasiswa->angkatan;
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-yellow-50/40 transition">
                                <td class="px-4 py-3 text-center font-medium text-gray-600">{{ $nomor }}</td>
                                
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-800">{{ $p->perusahaan->nama_perusahaan ?? '-' }}</span>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $p->perusahaan->alamat ?? '' }}</div>
                                </td>
                                
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($p->mahasiswa as $pm)
                                            @if ($pm->mahasiswa)
                                                <span class="inline-block bg-yellow-50 text-gray-700 text-xs px-2 py-1 border border-yellow-200 rounded">
                                                    {{ $pm->mahasiswa->nama }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <div class="flex flex-wrap gap-1 justify-center">
                                        @foreach ($angkatanList as $angkatan)
                                            <span class="inline-block bg-blue-50 text-blue-700 text-xs font-bold px-2 py-1 border border-blue-200 rounded">
                                                {{ $angkatan }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                    {{ \Carbon\Carbon::parse($p->tgl_pengajuan)->locale('id')->translatedFormat('d F Y') }}
                                </td>
                                
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block border text-xs font-bold px-3 py-1 whitespace-nowrap {{ $warna }}">
                                        {{ strtoupper($textStatus) }}
                                    </span>
                                </td>
                                
                                <td class="px-4 py-3 text-center">
                                    @if($p->suratPermohonan)
                                        <a href="{{ route('kaprodi.pengajuan_magang.surat_permohonan', $p->id_pengajuan) }}" 
                                           class="bg-yellow-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-yellow-700 transition rounded">
                                             Lihat
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Menunggu BAAK</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @if($adaFileSuratBalasan)
                                        <a href="{{ route('kaprodi.pengajuan_magang.verifikasi_surat_balasan', $p->id_pengajuan) }}" 
                                           class="bg-amber-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-amber-700 transition rounded">
                                              Detail
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Belum Upload</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @if($p->suratPengantar)
                                        <a href="{{ route('kaprodi.lihat_surat_pengantar', $p->id_pengajuan) }}" 
                                           class="bg-yellow-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-yellow-700 transition rounded">
                                             Lihat
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Menunggu BAAK</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('kaprodi.pengajuan_magang.detail', $p->id_pengajuan) }}" 
                                       class="text-yellow-600 hover:text-yellow-800 font-semibold text-sm hover:underline">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $pengajuanMasuk->appends(request()->input())->links() }}
            </div>
        @endif
    </div>

    {{-- ========================================== --}}
    {{-- TABEL 2: RIWAYAT PENGAJUAN KAPRODI        --}}
    {{-- ========================================== --}}
    <div class="mt-10">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold border-l-4 border-yellow-500 pl-3 pb-2">Riwayat Pengajuan</h2>
            <div class="flex items-center gap-3">
                {{--  FILTER TAHUN ANGKATAN --}}
                <div class="flex items-center gap-1.5">
                    <span class="text-sm text-gray-600">Tahun:</span>
                    <select onchange="window.location.href=this.value" class="border border-blue-400 rounded-md px-2 py-1.5 text-sm bg-white focus:ring-blue-500 focus:border-blue-500">
                        <option value="{{ route('kaprodi.pengajuan_magang.index', array_merge(request()->except(['masuk_page', 'riwayat_page', 'filter_tahun']))) }}" {{ !$filterTahun ? 'selected' : '' }}>Semua</option>
                        @foreach($daftarTahun as $tahun)
                            <option value="{{ route('kaprodi.pengajuan_magang.index', array_merge(request()->except(['masuk_page', 'riwayat_page']), ['filter_tahun' => $tahun])) }}" {{ $filterTahun == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-px h-6 bg-gray-300"></div>
                {{-- DROPDOWN DATA PER HALAMAN --}}
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">Tampilkan:</span>
                    <select onchange="window.location.href=this.value" class="border border-yellow-400 rounded-md px-2 py-1.5 text-sm bg-white focus:ring-yellow-500 focus:border-yellow-500">
                        <option value="{{ route('kaprodi.pengajuan_magang.index', array_merge(request()->except('riwayat_page'), ['riwayat_per_page' => 5])) }}" {{ request('riwayat_per_page', 5) == 5 ? 'selected' : '' }}>5</option>
                        <option value="{{ route('kaprodi.pengajuan_magang.index', array_merge(request()->except('riwayat_page'), ['riwayat_per_page' => 10])) }}" {{ request('riwayat_per_page', 5) == 10 ? 'selected' : '' }}>10</option>
                    </select>
                    <span class="text-sm text-gray-600">data</span>
                </div>
            </div>
        </div>

        @if ($riwayatPengajuan->isEmpty())
            <div class="text-center text-gray-400 py-8 border border-gray-200 bg-gray-50 rounded-lg">
                @if($filterTahun)
                    Belum ada riwayat pengajuan dari tahun angkatan <strong>{{ $filterTahun }}</strong>.
                @else
                    Belum ada riwayat pengajuan.
                @endif
            </div>
        @else
            <div class="overflow-x-auto border border-yellow-400 rounded-lg shadow-sm">
                <table class="min-w-[1200px] w-full text-sm text-left">
                    <thead class="bg-yellow-50 text-xs uppercase text-gray-700 border-b-2 border-yellow-400">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 whitespace-nowrap">Nama Perusahaan</th>
                            <th class="px-4 py-3 whitespace-nowrap">Nama Mahasiswa</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap w-20">Tahun Angkatan</th>
                            <th class="px-4 py-3 whitespace-nowrap">Tgl Diajukan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Status Pengajuan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Permohonan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Balasan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Pengantar</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($riwayatPengajuan as $p)
                            @php
                                $warna = $p->status_alur['color'];
                                $textStatus = $p->status_alur['text'];
                                $adaFileSuratBalasan = $p->mahasiswa->whereNotNull('surat_balasan')->count() > 0;
                                $totalMhs = $p->mahasiswa->count();
                                $mhsDitolak = $p->mahasiswa->where('status', 'ditolak')->count();
                                $semuaDitolak = ($totalMhs > 0 && $totalMhs === $mhsDitolak);
                                $nomor = $riwayatPengajuan->firstItem() + $loop->index;
                                
                                $angkatanList = [];
                                foreach ($p->mahasiswa as $pm) {
                                    if ($pm->mahasiswa && $pm->mahasiswa->angkatan && !in_array($pm->mahasiswa->angkatan, $angkatanList)) {
                                        $angkatanList[] = $pm->mahasiswa->angkatan;
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-yellow-50/40 transition">
                                <td class="px-4 py-3 text-center font-medium text-gray-600">{{ $nomor }}</td>
                                
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-700">{{ $p->perusahaan->nama_perusahaan ?? '-' }}</span>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $p->perusahaan->alamat ?? '' }}</div>
                                </td>
                                
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($p->mahasiswa as $pm)
                                            @if ($pm->mahasiswa)
                                                <span class="inline-block bg-yellow-50 text-gray-700 text-xs px-2 py-1 border border-yellow-200 rounded">
                                                    {{ $pm->mahasiswa->nama }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <div class="flex flex-wrap gap-1 justify-center">
                                        @foreach ($angkatanList as $angkatan)
                                            <span class="inline-block bg-blue-50 text-blue-700 text-xs font-bold px-2 py-1 border border-blue-200 rounded">
                                                {{ $angkatan }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                    {{ \Carbon\Carbon::parse($p->tgl_pengajuan)->locale('id')->translatedFormat('d F Y') }}
                                </td>
                                
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block border text-xs font-bold px-3 py-1 whitespace-nowrap {{ $warna }}">
                                        {{ strtoupper($textStatus) }}
                                    </span>
                                </td>
                                
                                <td class="px-4 py-3 text-center">
                                    @if($p->suratPermohonan)
                                        <a href="{{ route('kaprodi.pengajuan_magang.surat_permohonan', $p->id_pengajuan) }}" 
                                           class="bg-yellow-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-yellow-700 transition rounded">
                                              Lihat
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Menunggu BAAK</span>
                                    @endif
                                </td>
        
                                <td class="px-4 py-3 text-center">
                                    @if($adaFileSuratBalasan)
                                        <a href="{{ route('kaprodi.pengajuan_magang.verifikasi_surat_balasan', $p->id_pengajuan) }}" 
                                           class="bg-amber-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-amber-700 transition rounded">
                                              Lihat
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Belum Upload</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @if($p->suratPengantar)
                                        <a href="{{ route('kaprodi.lihat_surat_pengantar', $p->id_pengajuan) }}" 
                                           class="bg-yellow-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-yellow-700 transition rounded">
                                              Lihat
                                        </a>
                                    @elseif(str_contains($textStatus, 'Dibatalkan'))
                                        <span class="text-red-400 text-xs font-medium">Dibatalkan</span>
                                    @elseif($semuaDitolak)
                                        <span class="text-red-400 text-xs font-medium">Ditolak</span>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Menunggu BAAK</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('kaprodi.pengajuan_magang.detail', $p->id_pengajuan) }}" 
                                       class="text-yellow-600 hover:text-yellow-800 font-semibold text-sm hover:underline">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $riwayatPengajuan->appends(request()->input())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection