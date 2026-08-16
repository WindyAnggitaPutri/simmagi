@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-6">
    <div class="mb-8">
        <a href="{{ route('dospem.mahasiswa-bimbingan') }}" class="text-yellow-600 font-semibold text-sm hover:underline">← Kembali ke Daftar Mahasiswa Bimbingan</a>
        <h1 class="text-2xl font-bold text-yellow-400 mt-2">Detail Mahasiswa Bimbingan</h1>
    </div>

    {{-- ==================== INFO MAHASISWA ==================== --}}
    <div class="bg-white shadow-md border border-gray-200 rounded-lg p-8 mb-6">
        <h2 class="text-lg font-bold text-gray-800 border-b pb-2 mb-6"> Informasi Mahasiswa</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-5 gap-x-10">
            <div>
                <p class="text-sm text-gray-500">Nama Lengkap</p>
                <p class="text-lg font-bold text-gray-900">{{ $dosenPembimbing->mahasiswa->nama ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">NPM</p>
                <p class="text-lg font-semibold text-gray-900">{{ $dosenPembimbing->mahasiswa->npm ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Angkatan</p>
                <p class="text-lg font-semibold text-gray-900">{{ $dosenPembimbing->mahasiswa->angkatan ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Semester</p>
                <p class="text-lg font-semibold text-gray-900">{{ $dosenPembimbing->mahasiswa->semester ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Email</p>
                <p class="text-md font-semibold text-gray-900">{{ $dosenPembimbing->mahasiswa->email ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">No HP</p>
                <p class="text-md font-semibold text-gray-900">{{ $dosenPembimbing->mahasiswa->no_hp ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- ==================== INFO MAGANG ==================== --}}
    <div class="bg-white shadow-md border border-gray-200 rounded-lg p-8 mb-6">
        <h2 class="text-lg font-bold text-gray-800 border-b pb-2 mb-6">Informasi Magang</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-5 gap-x-10">
            <div>
                <p class="text-sm text-gray-500">Perusahaan</p>
                <p class="text-lg font-bold text-gray-900">{{ $dosenPembimbing->pengajuanMagang->perusahaan->nama_perusahaan ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Alamat Perusahaan</p>
                <p class="text-md font-semibold text-gray-900">{{ $dosenPembimbing->pengajuanMagang->perusahaan->alamat ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Periode Magang</p>
                <p class="text-md font-semibold text-blue-700">
                    @if($dosenPembimbing->pengajuanMagang->tgl_mulai)
                        {{ \Carbon\Carbon::parse($dosenPembimbing->pengajuanMagang->tgl_mulai)->locale('id')->translatedFormat('d F Y') }}
                        &mdash;
                        {{ \Carbon\Carbon::parse($dosenPembimbing->pengajuanMagang->tgl_selesai)->locale('id')->translatedFormat('d F Y') }}
                    @else
                        -
                    @endif
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Durasi Magang</p>
                @php
                    $durasi = '-';
                    if ($dosenPembimbing->pengajuanMagang->tgl_mulai && $dosenPembimbing->pengajuanMagang->tgl_selesai) {
                        $mulai = \Carbon\Carbon::parse($dosenPembimbing->pengajuanMagang->tgl_mulai);
                        $selesai = \Carbon\Carbon::parse($dosenPembimbing->pengajuanMagang->tgl_selesai);
                        $bulan = $mulai->diffInMonths($selesai);
                        $durasi = $bulan . ' Bulan';
                    }
                @endphp
                <p class="text-md font-semibold text-gray-900">{{ $durasi }}</p>
            </div>
        </div>
    </div>

    {{-- ==================== INFO DOSEN PEMBIMBING ==================== --}}
    <div class="bg-white shadow-md border border-gray-200 rounded-lg p-8 mb-6">
        <h2 class="text-lg font-bold text-gray-800 border-b pb-2 mb-6"> Dosen Pembimbing</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-5 gap-x-10">
            <div>
                <p class="text-sm text-gray-500">Nama Dosen</p>
                <p class="text-lg font-bold text-gray-900">{{ $dosenPembimbing->dosen->nama ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">NIDN</p>
                <p class="text-md font-semibold text-gray-900">{{ $dosenPembimbing->dosen->nidn ?? '-' }}</p>
            </div>
        </div>
    </div>

</div>
@endsection