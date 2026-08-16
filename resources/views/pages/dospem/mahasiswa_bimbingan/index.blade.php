@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-yellow-400">Mahasiswa Bimbingan</h1>
            <p class="text-gray-500 text-sm mt-1">Daftar seluruh mahasiswa bimbingan Anda dari semua angkatan</p>
        </div>

        {{-- Info Ringkas --}}
        @php
            $totalMhs = 0;
            foreach ($groupedByAngkatan as $items) {
                $totalMhs += $items->count();
            }
        @endphp
        <div class="bg-yellow-50 border-2 border-yellow-400 rounded-lg px-6 py-3 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $totalMhs }}</p>
            <p class="text-xs text-gray-600">Total Mahasiswa Bimbingan</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if($groupedByAngkatan->isEmpty())
        <div class="bg-white shadow-md border border-gray-200 rounded-lg p-16 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-gray-400 text-lg">Belum ada mahasiswa bimbingan.</p>
        </div>
    @else
        @foreach ($groupedByAngkatan as $angkatan => $items)
        <div class="mb-10">
            {{-- Header Angkatan --}}
            <div class="flex items-center gap-3 mb-4 border-b-2 border-yellow-400 pb-2">
                <div class="bg-yellow-500 text-white text-sm font-bold px-4 py-1.5 rounded-lg">
                    Angkatan {{ $angkatan }}
                </div>
                <span class="text-sm text-gray-500">{{ $items->count() }} mahasiswa</span>
            </div>

            {{-- Tabel --}}
            <div class="bg-white shadow-md border border-gray-200 rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-yellow-500 text-white">
                            <tr>
                                <th class="p-4 text-center w-12">No</th>
                                <th class="p-4">Nama Mahasiswa</th>
                                <th class="p-4">NPM</th>
                                <th class="p-4">Perusahaan</th>
                                <th class="p-4 text-center">Periode Magang</th>
                                <th class="p-4 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            @foreach ($items as $index => $item)
                            <tr class="border-b border-gray-100 hover:bg-yellow-50 transition">
                                <td class="p-4 text-center text-gray-500 font-medium">{{ $loop->iteration }}</td>
                                <td class="p-4 font-semibold text-gray-900">{{ $item->mahasiswa->nama ?? '-' }}</td>
                                <td class="p-4 text-gray-600">{{ $item->mahasiswa->npm ?? '-' }}</td>
                                <td class="p-4 text-gray-600">{{ $item->pengajuanMagang->perusahaan->nama_perusahaan ?? '-' }}</td>

                                {{-- Periode Magang --}}
                                <td class="p-4 text-center">
                                    @if($item->pengajuanMagang->tgl_mulai && $item->pengajuanMagang->tgl_selesai)
                                        <span class="text-xs bg-blue-50 text-blue-700 px-3 py-1 rounded-full font-medium">
                                            {{ \Carbon\Carbon::parse($item->pengajuanMagang->tgl_mulai)->locale('id')->translatedFormat('d M Y') }}
                                            &mdash;
                                            {{ \Carbon\Carbon::parse($item->pengajuanMagang->tgl_selesai)->locale('id')->translatedFormat('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="p-4 text-center">
                                    <a href="{{ route('dospem.mahasiswa-detail', $item->id_dosen_pembimbing) }}" class="text-green-600 hover:underline text-xs font-semibold">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
    @endif
</div>
@endsection