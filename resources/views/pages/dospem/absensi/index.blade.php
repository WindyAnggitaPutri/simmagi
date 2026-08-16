@extends('layouts.app') {{-- Extends layout utama aplikasi --}}

@section('content') {{-- Mulai section content --}}
<div class="max-w-7xl mx-auto px-4 md:px-6 py-6"> {{-- Container utama responsif --}}

    <!-- Header -->
    <div class="mb-6"> {{-- Margin bawah --}}
        <h1 class="text-2xl font-bold text-gray-800">Absensi Mahasiswa Bimbingan</h1> {{-- Judul halaman --}}
        <p class="text-sm text-gray-500 mt-1">Daftar mahasiswa bimbingan beserta rekap absensi harian mereka.</p> {{-- Deskripsi --}}
    </div>

    <!-- Statistik Cards -->
    {{-- Grid 2 kolom untuk statistik ringkas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        
        {{-- Card Total Mahasiswa Bimbingan --}}
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center"> {{-- Icon background --}}
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Icon users --}}
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Mahasiswa</p> {{-- Label --}}
                    <p class="text-xl font-bold text-gray-800">{{ $totalMahasiswa }}</p> {{-- Nilai total mahasiswa --}}
                </div>
            </div>
        </div>
        
        {{-- Card Total Pending Validasi --}}
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center"> {{-- Icon background --}}
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Icon clock --}}
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Menunggu Validasi</p> {{-- Label --}}
                    <p class="text-xl font-bold text-orange-600">{{ $totalPending }}</p> {{-- Nilai pending --}}
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden"> {{-- Container tabel --}}
        
        {{-- Header tabel --}}
        <div class="p-4 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h2 class="font-semibold text-gray-700">Daftar Mahasiswa</h2>
            </div>
        </div>

        <div class="overflow-x-auto"> {{-- Wrapper untuk horizontal scroll --}}
            <table class="w-full text-sm"> {{-- Tabel lebar penuh --}}
                
                {{-- Header tabel --}}
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">No</th> {{-- Nomor urut --}}
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Nama Mahasiswa</th> {{-- Nama --}}
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">NPM</th> {{-- NPM --}}
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Perusahaan</th> {{-- Tempat magang --}}
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Total</th> {{-- Total absensi --}}
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Hadir</th> {{-- Jumlah hadir --}}
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Sakit</th> {{-- Jumlah sakit --}}
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Izin</th> {{-- Jumlah izin --}}
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Pending</th> {{-- Jumlah pending --}}
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Aksi</th> {{-- Tombol aksi --}}
                    </tr>
                </thead>
                
                {{-- Body tabel --}}
                <tbody class="divide-y divide-gray-100">
                    @forelse($dataMahasiswa as $index => $mhs) {{-- Loop data mahasiswa --}}
                        <tr class="hover:bg-gray-50 transition"> {{-- Baris dengan hover effect --}}
                            <td class="px-4 py-3 text-gray-500">{{ $index + 1 }}</td> {{-- Nomor urut --}}
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $mhs->nama }}</td> {{-- Nama mahasiswa --}}
                            <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $mhs->npm }}</td> {{-- NPM dengan font monospace --}}
                            <td class="px-4 py-3 text-gray-600">{{ $mhs->perusahaan }}</td> {{-- Nama perusahaan --}}
                            
                            {{-- Badge Total (abu-abu) --}}
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-700 font-semibold text-xs">
                                    {{ $mhs->total_absensi }}
                                </span>
                            </td>
                            
                            {{-- Badge Hadir (hijau) --}}
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-700 font-semibold text-xs">
                                    {{ $mhs->total_hadir }}
                                </span>
                            </td>
                            
                            {{-- Badge Sakit (kuning) --}}
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-100 text-yellow-700 font-semibold text-xs">
                                    {{ $mhs->total_sakit }}
                                </span>
                            </td>
                            
                            {{-- Badge Izin (biru) --}}
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs">
                                    {{ $mhs->total_izin }}
                                </span>
                            </td>
                            
                            {{-- Badge Pending (oranye, hanya tampil jika > 0) --}}
                            <td class="px-4 py-3 text-center">
                                @if($mhs->pending > 0)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-700 font-semibold text-xs">
                                        {{ $mhs->pending }}
                                    </span>
                                @else
                                    <span class="text-gray-300">-</span> {{-- Tanda strip jika 0 --}}
                                @endif
                            </td>
                            
                            {{-- Tombol Detail --}}
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('dospem.absensi.detail', ['id_mahasiswa' => $mhs->id_mahasiswa]) }}"
                                   class="inline-flex items-center gap-1 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium px-3 py-1.5 rounded-md transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Icon eye --}}
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty {{-- Jika tidak ada data mahasiswa --}}
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center"> {{-- Span semua kolom --}}
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Icon clipboard kosong --}}
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p class="text-gray-400 font-medium">Belum ada mahasiswa bimbingan</p> {{-- Pesan kosong --}}
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection 