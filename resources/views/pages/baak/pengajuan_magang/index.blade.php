@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6">

    {{-- Judul --}}
    <h1 class="text-center text-2xl font-bold mb-2">
        PERMOHONAN PENGAJUAN MAGANG INDUSTRI <br>
        MAHASISWA JURUSAN KOMPUTER DAN BISNIS
    </h1>
    <p class="text-center text-sm text-gray-500 mb-8">Daftar pengajuan yang telah disetujui Kaprodi & Admin Kajur</p>

    {{-- ========================================== --}}
    {{-- TABEL 1: MENUNGGU UPLOAD SURAT            --}}
    {{-- Data: BAAK sudah ACC, surat pengantar belum ada --}}
    {{-- ========================================== --}}
    <div class="mt-8 mb-10">
        <h2 class="text-lg font-bold mb-4 border-l-4 border-yellow-500 pl-3 pb-2">
            Menunggu Upload Surat Pengantar
            <span class="ml-2 text-xs font-normal text-gray-500 bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">
                {{ $pengajuanMenunggu->count() }}
            </span>
        </h2>

        @if ($pengajuanMenunggu->isEmpty())
            <div class="text-center text-gray-400 py-8 border border-gray-200 bg-gray-50 rounded-lg">
                Tidak ada pengajuan yang menunggu upload surat.
            </div>
        @else
            <div class="overflow-x-auto border border-yellow-400 rounded-lg shadow-sm">
                <table class="min-w-[1100px] w-full text-sm text-left">
                    <thead class="bg-yellow-50 text-xs uppercase text-gray-700 border-b-2 border-yellow-400">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 whitespace-nowrap">Nama Perusahaan</th>
                            <th class="px-4 py-3 whitespace-nowrap">Nama Mahasiswa</th>
                            <th class="px-4 py-3 whitespace-nowrap">Tgl Diajukan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Permohonan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Balasan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Pengantar</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($pengajuanMenunggu as $index => $p)
                            @php
                                $warna = $p->status_alur['color'];
                                $textStatus = $p->status_alur['text'];
                            @endphp
                            <tr class="hover:bg-yellow-50/40 transition">
                                <td class="px-4 py-3 text-center font-medium text-gray-600">{{ $index + 1 }}</td>
                                
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-800">{{ $p->perusahaan->nama_perusahaan ?? '-' }}</span>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $p->perusahaan->alamat ?? '' }}</div>
                                </td>
                                
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($p->mahasiswa as $pm)
                                            @if ($pm->mahasiswa)
                                                <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 border border-gray-200 rounded">
                                                    {{ $pm->mahasiswa->nama }}
                                                </span>
                                            @endif
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
    @if(str_contains($textStatus, 'Menunggu BAAK'))
        <span class="text-gray-400 text-xs italic">Menunggu Persetujuan</span>
    @elseif($p->suratPermohonan)
        <a href="{{ route('baak.upload_surat_permohonan', $p->id_pengajuan) }}" 
           class="inline-flex items-center gap-1 bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-indigo-700 transition rounded">
             Lihat
        </a>
    @else
        <a href="{{ route('baak.upload_surat_permohonan', $p->id_pengajuan) }}" 
           class="inline-flex items-center gap-1 bg-green-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-green-700 transition rounded">
             Upload
        </a>
    @endif
</td>

                                {{-- KOLOM SURAT BALASAN --}}
                                <td class="px-4 py-3 text-center">
                                    @if($p->sudah_verifikasi_kaprodi)
                                        <a href="{{ route('baak.lihat_surat_balasan', $p->id_pengajuan) }}" 
                                           class="inline-flex items-center gap-1 bg-purple-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-purple-700 transition rounded">
                                             Lihat
                                        </a>
                                    @elseif($p->ada_file_surat_balasan)
                                        <span class="text-yellow-600 text-xs font-semibold italic">⏳ Menunggu Verifikasi Kaprodi</span>
                                    @elseif(!$p->suratPermohonan)
                                        <span class="text-gray-400 text-xs italic">Upload Permohonan Dulu</span>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Menunggu Upload Mahasiswa</span>
                                    @endif
                                </td>

                                {{-- KOLOM SURAT PENGANTAR --}}
                                <td class="px-4 py-3 text-center">
                                    @if($p->ada_diterima_kaprodi)
                                        <a href="{{ route('baak.upload_surat_pengantar', $p->id_pengajuan) }}" 
                                           class="inline-flex items-center gap-1 bg-green-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-green-700 transition rounded">
                                             Upload
                                        </a>
                                    @elseif($p->sudah_verifikasi_kaprodi)
                                        <span class="text-red-400 text-xs font-medium">Balasan Ditolak Kaprodi</span>
                                    @elseif($p->ada_file_surat_balasan)
                                        <span class="text-yellow-600 text-xs font-semibold italic"> Menunggu Verifikasi Kaprodi</span>
                                    @elseif(!$p->suratPermohonan)
                                        <span class="text-gray-400 text-xs italic">Upload Permohonan Dulu</span>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Menunggu Upload Balasan</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('baak.pengajuan_magang.detail', $p->id_pengajuan) }}" 
                                       class="text-blue-600 hover:text-blue-800 font-semibold text-sm hover:underline">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ========================================== --}}
    {{-- TABEL 2: RIWAYAT PENGAJUAN                --}}
    {{-- Data: surat pengantar SUDAH ADA atau DITOLAK --}}
    {{-- ========================================== --}}
    <div class="mt-10">
        <h2 class="text-lg font-bold mb-4 border-l-4 border-gray-500 pl-3 pb-2">
            Riwayat Pengajuan
            <span class="ml-2 text-xs font-normal text-gray-500 bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">
                {{ $riwayatPengajuan->count() }}
            </span>
        </h2>

        @if ($riwayatPengajuan->isEmpty())
            <div class="text-center text-gray-400 py-8 border border-gray-200 bg-gray-50 rounded-lg">
                Belum ada riwayat pengajuan.
            </div>
        @else
            <div class="overflow-x-auto border border-gray-300 rounded-lg shadow-sm">
                <table class="min-w-[1100px] w-full text-sm text-left">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 border-b-2 border-gray-300">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 whitespace-nowrap">Nama Perusahaan</th>
                            <th class="px-4 py-3 whitespace-nowrap">Nama Mahasiswa</th>
                            <th class="px-4 py-3 whitespace-nowrap">Tgl Diajukan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Permohonan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Balasan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Pengantar</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($riwayatPengajuan as $index => $p)
                        @php
                            $warna = $p->status_alur['color'];
                            $textStatus = $p->status_alur['text'];
                            $isDitolak = str_contains($textStatus, 'Ditolak');
                        @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-center font-medium text-gray-500">{{ $index + 1 }}</td>
                                
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-700">{{ $p->perusahaan->nama_perusahaan ?? '-' }}</span>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $p->perusahaan->alamat ?? '' }}</div>
                                </td>
                                
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($p->mahasiswa as $pm)
                                            @if ($pm->mahasiswa)
                                                <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 border border-gray-200 rounded">
                                                    {{ $pm->mahasiswa->nama }}
                                                </span>
                                            @endif
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
                                
                                {{-- KOLOM SURAT PERMOHONAN --}}
                                <td class="px-4 py-3 text-center">
                                    @if($p->suratPermohonan)
                                        <a href="{{ route('baak.upload_surat_permohonan', $p->id_pengajuan) }}" 
                                           class="inline-flex items-center gap-1 bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-indigo-700 transition rounded">
                                             Lihat
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">—</span>
                                    @endif
                                </td>

                                {{-- KOLOM SURAT BALASAN --}}
                                <td class="px-4 py-3 text-center">
                                    @if($p->ada_file_surat_balasan)
                                        <a href="{{ route('baak.lihat_surat_balasan', $p->id_pengajuan) }}" 
                                           class="inline-flex items-center gap-1 bg-purple-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-purple-700 transition rounded">
                                             Lihat
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">—</span>
                                    @endif
                                </td>

                                {{-- KOLOM SURAT PENGANTAR --}}
                                <td class="px-4 py-3 text-center">
                                    @if($p->suratPengantar)
                                        <a href="{{ route('baak.upload_surat_pengantar', $p->id_pengajuan) }}" 
                                           class="inline-flex items-center gap-1 bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-indigo-700 transition rounded">
                                             Lihat
                                        </a>
                                    @elseif($isDitolak)
                                        <span class="text-red-400 text-xs font-medium">Ditolak</span>
                                    @else
                                        <span class="text-gray-400 text-xs italic">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('baak.pengajuan_magang.detail', $p->id_pengajuan) }}" 
                                       class="text-blue-600 hover:text-blue-800 font-semibold text-sm hover:underline">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection