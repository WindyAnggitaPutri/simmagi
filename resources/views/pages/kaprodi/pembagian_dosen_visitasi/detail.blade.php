@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-6">
    <div class="mb-8">
        <a href="{{ route('kaprodi.pembagian-dosen-visitasi.index') }}" class="text-yellow-600 font-semibold text-sm hover:underline">← Kembali ke Daftar Dosen Visitasi</a>
        <h1 class="text-2xl font-bold text-yellow-400 mt-2">Detail Penugasan Dosen Visitasi</h1>
    </div>

    <div class="bg-white shadow-md border border-gray-200 rounded-lg p-8">

        {{-- Info Dosen Visitasi --}}
        <div class="mb-8">
            <h2 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Informasi Dosen Visitasi</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Nama Dosen Visitasi</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $visitasi->dosen->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">NIDN</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $visitasi->dosen->nidn ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Info Mahasiswa & Perusahaan --}}
        <div class="mb-8">
            <h2 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Informasi Mahasiswa & Magang</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                <div>
                    <p class="text-sm text-gray-500">Nama Mahasiswa</p>
                    <p class="text-md font-semibold text-gray-900">{{ $visitasi->mahasiswa->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">NPM</p>
                    <p class="text-md font-semibold text-gray-900">{{ $visitasi->mahasiswa->npm ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Perusahaan Magang</p>
                    <p class="text-md font-semibold text-gray-900">{{ $visitasi->perusahaan->nama_perusahaan ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Alamat Perusahaan</p>
                    <p class="text-md font-semibold text-gray-900">{{ $visitasi->perusahaan->alamat ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Dosen Pembimbing</p>
                    <p class="text-md font-semibold text-gray-900">{{ $visitasi->dosenPembimbing->dosen->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tahun</p>
                    <p class="text-md font-semibold text-gray-900">{{ $visitasi->tahun ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Status Visitasi --}}
       {{--  UPDATE: Informasi Visitasi (Hapus File SK, Hapus Judul Status Workflow) --}}
        <div class="mb-8">
            <h2 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Informasi Visitasi</h2>
            
            {{-- Cek apakah sudah visitasi berdasarkan tanggal --}}
            @if($visitasi->tgl_visitasi)
                <span class="inline-flex items-center gap-1 px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-bold">
                     Sudah Visitasi
                </span>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Visitasi</p>
                        <p class="text-md font-semibold text-gray-900">{{ \Carbon\Carbon::parse($visitasi->tgl_visitasi)->locale('id')->translatedFormat('d F Y') }}</p>
                    </div>

                    @if($visitasi->catatan)
                    <div>
                        <p class="text-sm text-gray-500">Catatan</p>
                        <p class="text-md font-semibold text-gray-900">{{ $visitasi->catatan }}</p>
                    </div>
                    @endif
                </div>

                @if($visitasi->foto)
                    <div class="mt-4">
                        <p class="text-sm text-gray-500 mb-2">Bukti Foto</p>
                        <img src="{{ asset('storage/foto_visitasi/' . $visitasi->foto) }}" alt="Foto Visitasi" class="max-w-xs rounded-lg border shadow-sm">
                    </div>
                @endif
            @else
                <span class="inline-flex items-center gap-1 px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm font-bold">
                    🕐 Belum Visitasi
                </span>
                <p class="mt-3 text-sm text-gray-500">Dosen belum melakukan visitasi ke mahasiswa ini.</p>
            @endif
        </div>

    </div>
@endsection