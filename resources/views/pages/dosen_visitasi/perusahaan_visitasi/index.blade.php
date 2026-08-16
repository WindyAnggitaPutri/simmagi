@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-yellow-400">Perusahaan Visitasi</h1>
            <p class="text-gray-500 text-sm mt-1">Daftar perusahaan yang harus Anda visitasi dari semua penugasan</p>
        </div>

        @php
            $totalPerusahaan = $groupedByPerusahaan->count();
            $totalMahasiswa = 0;
            foreach ($groupedByPerusahaan as $items) {
                $totalMahasiswa += $items->count();
            }
        @endphp
        <div class="flex gap-4">
            <div class="bg-yellow-50 border-2 border-yellow-400 rounded-lg px-6 py-3 text-center">
                <p class="text-2xl font-bold text-yellow-600">{{ $totalPerusahaan }}</p>
                <p class="text-xs text-gray-600">Total Perusahaan</p>
            </div>
            <div class="bg-blue-50 border-2 border-blue-400 rounded-lg px-6 py-3 text-center">
                <p class="text-2xl font-bold text-blue-600">{{ $totalMahasiswa }}</p>
                <p class="text-xs text-gray-600">Total Mahasiswa</p>
            </div>
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

    @if($groupedByPerusahaan->isEmpty())
        <div class="bg-white shadow-md border border-gray-200 rounded-lg p-16 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="text-gray-400 text-lg">Belum ada perusahaan visitasi.</p>
        </div>
    @else
        <div class="bg-white shadow-md border border-gray-200 rounded-lg overflow-hidden mb-10">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead class="bg-yellow-500 text-white">
                        <tr>
                            <th class="p-4 text-center w-12">No</th>
                            <th class="p-4">Nama Perusahaan</th>
                            <th class="p-4">Nama Mahasiswa</th>
                            <th class="p-4">NPM</th>
                            <th class="p-4 text-center">SK Visitasi</th>
                            <th class="p-4 text-center">Periode Magang</th>
                            <th class="p-4 text-center w-40">Personal Interview</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @php
                            $companyNo = 1; 
                            $groupIndex = 0; 
                        @endphp

                        @foreach ($groupedByPerusahaan as $idPerusahaan => $items)
                            @php
                                $perusahaan = $items->first()->perusahaan;
                                $namaPerusahaan = $perusahaan ? $perusahaan->nama_perusahaan : 'Perusahaan Tidak Diketahui';
                                $rowspanCount = $items->count();
                                $bgColor = $groupIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50'; 
                                $hoverBg = 'hover:bg-yellow-50';
                            @endphp

                            @foreach ($items as $item)
                            <tr class="border-b border-gray-100 {{ $bgColor }} {{ $hoverBg }} transition">
                                
                                @if($loop->first)
                                    <td class="p-4 text-center text-gray-500 font-medium align-top" rowspan="{{ $rowspanCount }}">
                                        {{ $companyNo }}
                                    </td>
                                    <td class="p-4 font-bold text-gray-900 align-top" rowspan="{{ $rowspanCount }}">
                                        {{ $namaPerusahaan }}
                                    </td>
                                @endif

                                <td class="p-4 font-semibold text-gray-900">{{ $item->mahasiswa->nama ?? '-' }}</td>
                                <td class="p-4 text-gray-600">{{ $item->mahasiswa->npm ?? '-' }}</td>

                                <td class="p-4 text-center">
                                    @if($item->file_sk)
                                        <a href="{{ route('dosen-visitasi.lihat-sk', $dosenLogin->id_dosen) }}" class="text-indigo-600 hover:underline text-xs font-semibold">
                                            Lihat SK
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>

                                <td class="p-4 text-center">
                                    @php
                                        $pengajuan = $item->dosenPembimbing->pengajuanMagang ?? null;
                                    @endphp
                                    @if($pengajuan && $pengajuan->tgl_mulai && $pengajuan->tgl_selesai)
                                        <span class="text-xs bg-blue-50 text-blue-700 px-3 py-1 rounded-full font-medium whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($pengajuan->tgl_mulai)->locale('id')->translatedFormat('d M Y') }}
                                            &mdash;
                                            {{ \Carbon\Carbon::parse($pengajuan->tgl_selesai)->locale('id')->translatedFormat('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>

                                @if($loop->first)
                                    <td class="p-4 text-center align-top" rowspan="{{ $rowspanCount }}">
                                        @if($interviewComplete[$idPerusahaan] ?? false)
                                            <a href="{{ route('dosen-visitasi.interview-detail', $idPerusahaan) }}" 
                                               class="text-xs bg-blue-100 text-blue-700 px-3 py-1.5 rounded-full font-bold inline-block hover:bg-blue-200 transition">
                                                Lihat Form
                                            </a>
                                        @else
                                            <a href="{{ route('dosen-visitasi.interview-form-a', $idPerusahaan) }}" 
                                               class="text-xs bg-yellow-100 text-yellow-700 px-3 py-1.5 rounded-full font-bold inline-block hover:bg-yellow-200 transition">
                                                Isi Form
                                            </a>
                                        @endif
                                    </td>
                                @endif

                            </tr>
                            @endforeach

                            @php $companyNo++; $groupIndex++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection