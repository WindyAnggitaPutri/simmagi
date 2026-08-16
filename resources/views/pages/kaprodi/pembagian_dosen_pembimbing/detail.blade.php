@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-6">
    <div class="mb-8">
        <a href="{{ route('kaprodi.dosen-pembimbing.index') }}" class="text-yellow-600 font-semibold text-sm hover:underline">← Kembali</a>
        <h1 class="text-2xl font-bold text-yellow-400 mt-2">Detail Penugasan Dosen Pembimbing</h1>
    </div>

    <div class="bg-white shadow-md border border-gray-200 rounded-lg p-8">
        
        {{-- Info Dosen Pembimbing --}}
        <div class="mb-8">
            <h2 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Informasi Dosen Pembimbing</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Nama Dosen</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $dosenPembimbing->dosen->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">NIDN</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $dosenPembimbing->dosen->nidn ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Info Mahasiswa & Magang --}}
        <div class="mb-8">
            <h2 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Informasi Mahasiswa & Magang</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                <div>
                    <p class="text-sm text-gray-500">Nama Mahasiswa</p>
                    <p class="text-md font-semibold text-gray-900">{{ $dosenPembimbing->mahasiswa->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">NPM</p>
                    <p class="text-md font-semibold text-gray-900">{{ $dosenPembimbing->mahasiswa->npm ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Perusahaan Magang</p>
                    <p class="text-md font-semibold text-gray-900">{{ $dosenPembimbing->pengajuanMagang->perusahaan->nama_perusahaan ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Alamat Perusahaan</p>
                    <p class="text-md font-semibold text-gray-900">{{ $dosenPembimbing->pengajuanMagang->perusahaan->alamat ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tanggal Mulai Magang</p>
                    <p class="text-md font-semibold text-gray-900">
                        {{ $dosenPembimbing->pengajuanMagang->tgl_mulai ? \Carbon\Carbon::parse($dosenPembimbing->pengajuanMagang->tgl_mulai)->locale('id')->translatedFormat('d F Y') : '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tanggal Selesai Magang</p>
                    <p class="text-md font-semibold text-gray-900">
                        {{ $dosenPembimbing->pengajuanMagang->tgl_selesai ? \Carbon\Carbon::parse($dosenPembimbing->pengajuanMagang->tgl_selesai)->locale('id')->translatedFormat('d F Y') : '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection