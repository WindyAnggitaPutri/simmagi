@extends('layouts.admin')

@section('title', 'Detail Nilai Akhir')

@section('page-title', 'Detail Nilai Akhir Mahasiswa')

@section('content')
<div class="p-6 space-y-6 max-w-5xl mx-auto">

    {{-- Tombol Kembali di Atas Judul --}}
    <div>
        <a href="{{ route('admin.nilai-akhir.index') }}" class="inline-flex items-center gap-2 text-sm text-[#b45309] hover:text-[#92400e] font-semibold hover:underline transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke Daftar Nilai
        </a>
    </div>

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Detail Nilai Akhir</h1>
        <p class="text-gray-500 text-sm mt-1">Breakdown lengkap perhitungan nilai akhir mahasiswa</p>
    </div>

    {{-- DATA MAHASISWA --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-bold text-gray-700 mb-4 flex items-center gap-2">Data Mahasiswa</h2>
        <div class="grid grid-cols-2 gap-y-3 gap-x-8">
            <div>
                <span class="text-sm text-gray-500">NPM</span>
                <p class="font-semibold">{{ $jadwal->mahasiswa->npm ?? '-' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Nama</span>
                <p class="font-semibold">{{ $jadwal->mahasiswa->nama ?? '-' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Program Studi</span>
                <p class="font-semibold">{{ $jadwal->mahasiswa->prodi->nama_prodi ?? '-' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Perusahaan</span>
                <p class="font-semibold">{{ $jadwal->perusahaan->nama_perusahaan ?? '-' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Dosen Pembimbing</span>
                <p class="font-semibold">{{ $jadwal->dosenPembimbing->dosen->nama ?? '-' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Tanggal Seminar</span>
                <p class="font-semibold">{{ \Carbon\Carbon::parse($jadwal->tgl_semgang)->format('d M Y') }}</p>
            </div>
        </div>
    </div>

    {{-- PENILAIAN PERUSAHAAN --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-bold text-gray-700 mb-1 flex items-center gap-2"> Penilaian Perusahaan</h2>
        <p class="text-sm text-orange-500 mb-4 font-medium">Bobot: {{ $bobotPerusahaan }}%</p>

        @if($detailPerusahaan)
            <table class="w-full text-sm mb-4">
                <thead>
                    <tr class="bg-orange-50 text-orange-700">
                        <th class="px-4 py-2 text-left border-b">No</th>
                        <th class="px-4 py-2 text-left border-b">Aspek Penilaian</th>
                        <th class="px-4 py-2 text-center border-b w-24">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailPerusahaan as $label => $nilai)
                    <tr class="border-b hover:bg-orange-50/50">
                        <td class="px-4 py-2 text-center text-gray-500">{{ $loop->iteration }}</td>
                        <td class="px-4 py-2">{{ $label }}</td>
                        <td class="px-4 py-2 text-center font-semibold">{{ $nilai ?? '-' }}</td>
                    </tr>
                    @endforeach
                    <tr class="bg-orange-50 font-bold">
                        <td colspan="2" class="px-4 py-2 text-right">Rata-rata</td>
                        <td class="px-4 py-2 text-center text-orange-600 text-lg">{{ number_format($nilaiPerusahaan, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @else
            <div class="bg-red-50 text-red-500 p-4 rounded-lg text-sm italic"> Belum ada penilaian dari perusahaan.</div>
        @endif
    </div>

    {{-- PENILAIAN DOSEN PEMBIMBING --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-bold text-gray-700 mb-1 flex items-center gap-2">Penilaian Dosen Pembimbing</h2>
        <p class="text-sm text-blue-500 mb-4 font-medium">Bobot: {{ $bobotPembimbing }}%</p>

        @if($detailPembimbing)
            <table class="w-full text-sm mb-4">
                <thead>
                    <tr class="bg-blue-50 text-blue-700">
                        <th class="px-4 py-2 text-left border-b">No</th>
                        <th class="px-4 py-2 text-left border-b">Aspek Penilaian</th>
                        <th class="px-4 py-2 text-center border-b w-24">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailPembimbing as $label => $nilai)
                    <tr class="border-b hover:bg-blue-50/50">
                        <td class="px-4 py-2 text-center text-gray-500">{{ $loop->iteration }}</td>
                        <td class="px-4 py-2">{{ $label }}</td>
                        <td class="px-4 py-2 text-center font-semibold">{{ $nilai ?? '-' }}</td>
                    </tr>
                    @endforeach
                    <tr class="bg-blue-50 font-bold">
                        <td colspan="2" class="px-4 py-2 text-right">Rata-rata</td>
                        <td class="px-4 py-2 text-center text-blue-600 text-lg">{{ number_format($nilaiPembimbing, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @else
            <div class="bg-red-50 text-red-500 p-4 rounded-lg text-sm italic"> Belum ada penilaian dari dosen pembimbing.</div>
        @endif
    </div>

    {{-- PERHITUNGAN NILAI AKHIR --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-bold text-gray-700 mb-4 flex items-center gap-2"> Perhitungan Nilai Akhir</h2>

        @if($nilaiAkhirCalc !== null)
            <div class="bg-gray-50 rounded-lg p-4 mb-4 text-sm font-mono text-gray-600">
                <p class="mb-1"><strong>Rumus:</strong></p>
                <p>Nilai Akhir = (Nilai Perusahaan × {{ $bobotPerusahaan }}%) + (Nilai Dospem × {{ $bobotPembimbing }}%)</p>
            </div>

            <table class="w-full text-sm mb-4">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="px-4 py-3 text-left border-b">Komponen</th>
                        <th class="px-4 py-3 text-center border-b">Nilai Rata-rata</th>
                        <th class="px-4 py-3 text-center border-b">Bobot</th>
                        <th class="px-4 py-3 text-center border-b">Hasil Perhitungan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b bg-orange-50/50">
                        <td class="px-4 py-3 font-medium"> Perusahaan</td>
                        <td class="px-4 py-3 text-center">{{ number_format($nilaiPerusahaan, 2) }}</td>
                        <td class="px-4 py-3 text-center text-orange-600 font-semibold">{{ $bobotPerusahaan }}%</td>
                        <td class="px-4 py-3 text-center font-semibold">
                            {{ number_format($nilaiPerusahaan, 2) }} × {{ $bobotPerusahaan / 100 }} = 
                            <span class="text-orange-600">{{ number_format($nilaiPerusahaan * $bobotPerusahaan / 100, 2) }}</span>
                        </td>
                    </tr>
                    <tr class="border-b bg-blue-50/50">
                        <td class="px-4 py-3 font-medium"> Dosen Pembimbing</td>
                        <td class="px-4 py-3 text-center">{{ number_format($nilaiPembimbing, 2) }}</td>
                        <td class="px-4 py-3 text-center text-blue-600 font-semibold">{{ $bobotPembimbing }}%</td>
                        <td class="px-4 py-3 text-center font-semibold">
                            {{ number_format($nilaiPembimbing, 2) }} × {{ $bobotPembimbing / 100 }} = 
                            <span class="text-blue-600">{{ number_format($nilaiPembimbing * $bobotPembimbing / 100, 2) }}</span>
                        </td>
                    </tr>
                    <tr class="bg-green-50">
                        <td class="px-4 py-3 font-bold text-lg" colspan="3">NILAI AKHIR</td>
                        <td class="px-4 py-3 text-center font-bold text-2xl text-green-700">
                            {{ number_format($nilaiAkhirCalc, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-6 p-5 rounded-lg border {{ $nilaiAkhirCalc >= 70 ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200' }} flex flex-col items-start gap-4">
                @php
                    $gradeColors = [
                        'A'  => 'bg-green-600 text-white', 'AB' => 'bg-green-500 text-white',
                        'B'  => 'bg-blue-600 text-white', 'BC' => 'bg-blue-500 text-white',
                        'C'  => 'bg-yellow-500 text-white', 'D'  => 'bg-orange-500 text-white',
                        'E'  => 'bg-red-600 text-white',
                    ];
                    $gc = $gradeColors[$gradeCalc] ?? 'bg-gray-500 text-white';
                @endphp

                <div class="flex items-center gap-3">
                    <span class="text-gray-700 font-semibold text-lg">Konversi Grade:</span>
                    <span class="px-5 py-1.5 rounded-full text-2xl font-black shadow-sm {{ $gc }}">{{ $gradeCalc }}</span>
                </div>

                <div class="w-64 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-3 py-1.5 text-left text-gray-600 border-b">Rentang Nilai</th>
                                <th class="px-3 py-1.5 text-center text-gray-600 border-b">Huruf Mutu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b {{ $gradeCalc == 'A' ? 'bg-green-100 font-bold text-green-800' : '' }}"><td class="px-3 py-1.5">80 - 100</td><td class="px-3 py-1.5 text-center">A</td></tr>
                            <tr class="border-b {{ $gradeCalc == 'AB' ? 'bg-green-50 font-bold text-green-700' : '' }}"><td class="px-3 py-1.5">75 - 79</td><td class="px-3 py-1.5 text-center">AB</td></tr>
                            <tr class="border-b {{ $gradeCalc == 'B' ? 'bg-blue-50 font-bold text-blue-700' : '' }}"><td class="px-3 py-1.5">70 - 74</td><td class="px-3 py-1.5 text-center">B</td></tr>
                            <tr class="border-b {{ $gradeCalc == 'BC' ? 'bg-blue-50 font-bold text-blue-700' : '' }}"><td class="px-3 py-1.5">65 - 69</td><td class="px-3 py-1.5 text-center">BC</td></tr>
                            <tr class="border-b {{ $gradeCalc == 'C' ? 'bg-yellow-50 font-bold text-yellow-700' : '' }}"><td class="px-3 py-1.5">60 - 64</td><td class="px-3 py-1.5 text-center">C</td></tr>
                            <tr class="border-b {{ $gradeCalc == 'D' ? 'bg-orange-50 font-bold text-orange-700' : '' }}"><td class="px-3 py-1.5">50 - 59</td><td class="px-3 py-1.5 text-center">D</td></tr>
                            <tr class="{{ $gradeCalc == 'E' ? 'bg-red-50 font-bold text-red-700' : '' }}"><td class="px-3 py-1.5">&lt; 50</td><td class="px-3 py-1.5 text-center">E</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-red-50 text-red-500 p-4 rounded-lg text-sm italic">
                 Nilai akhir belum bisa dihitung karena salah satu atau kedua komponen nilai belum tersedia.
            </div>
        @endif
    </div>

    {{-- STATUS PENYIMPANAN (READ ONLY) --}}
    @if($nilaiAkhirRecord)
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <p class="text-green-700 font-semibold"> Nilai akhir sudah disimpan ke database</p>
        <p class="text-sm text-green-600 mt-1">
            Disimpan pada: {{ \Carbon\Carbon::parse($nilaiAkhirRecord->created_at)->format('d M Y, H:i') }}
        </p>
    </div>
    @elseif($nilaiAkhirCalc !== null)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <p class="text-yellow-700 font-semibold"> Nilai akhir sudah dihitung tapi belum disimpan ke database (Menunggu diproses Kaprodi)</p>
    </div>
    @endif

</div>
@endsection  