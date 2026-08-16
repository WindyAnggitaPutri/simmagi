@extends('layouts.admin')

@section('title', 'Dashboard Super Admin - SIMMAGI')
@section('page-title', 'Dashboard Overview')

@section('content')

{{-- Banner Sambutan --}}
<div class="bg-gradient-to-r from-[#fffbeb] via-[#fffbeb]/50 to-white border border-[#ece75f]/30 rounded-lg p-6 mb-8 flex justify-between items-center shadow-sm">
    <div>
        <h3 class="text-lg font-bold text-gray-800">Selamat Datang, Super Admin!</h3>
        <p class="text-sm text-gray-600 mt-1">Berikut adalah ringkasan data sistem magang hari ini, <span class="font-medium text-[#b45309]">{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}</span></p>
    </div>
   
</div>

{{-- Kartu Statistik Utama --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

    {{-- Card 1: Total Mahasiswa --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 hover:shadow-md transition">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Total Mahasiswa</p>
                <h3 class="text-3xl font-bold text-gray-800">{{ $totalMahasiswa }}</h3>
                <p class="text-xs text-gray-500 mt-2">Terdaftar dalam sistem</p>
            </div>
            <!-- <div class="p-3 bg-amber-50 rounded-lg">
                <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div> -->
        </div>
    </div>

    {{-- Card 2: Perusahaan Mitra --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 hover:shadow-md transition">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Perusahaan Mitra</p>
                <h3 class="text-3xl font-bold text-gray-800">{{ $totalPerusahaan }}</h3>
                <p class="text-xs text-gray-500 mt-2">Mitra magang industri</p>
            </div>
            <!-- <div class="p-3 bg-blue-50 rounded-lg">
                <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div> -->
        </div>
    </div>

    {{-- Card 3: Jadwal Seminar --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 hover:shadow-md transition">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Jadwal Seminar</p>
                <h3 class="text-3xl font-bold text-gray-800">{{ $seminarMendatang }}</h3> {{-- Diubah dari $totalSeminar --}}
                <p class="text-xs text-gray-500 mt-2">
                    @if($seminarMendatang > 0)
                        <span class="text-xs text-gray-500 mt-2">Jadwal seminar magang mendatang</span>
                    @else
                        Tidak ada jadwal seminar magang mendatang
                    @endif
                </p>
            </div>
            <!-- <div class="p-3 bg-green-50 rounded-lg">
                <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div> -->
        </div>
    </div>

    {{-- Card 4: Total Dosen --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 hover:shadow-md transition">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Total Dosen</p>
                <h3 class="text-3xl font-bold text-gray-800">{{ $totalDosen }}</h3>
                <p class="text-xs text-gray-500 mt-2">Dosen terdaftar aktif</p>
            </div>
            <!-- <div class="p-3 bg-indigo-50 rounded-lg">
                <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div> -->
        </div>
    </div>
</div>

{{-- Tabel Seminar Magang Mendatang (Full Width) --}}
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
        <div class="flex items-center gap-2">
            <span class="w-2 h-6 bg-[#ece75f] rounded-full"></span>
            <h3 class="font-bold text-gray-800">Jadwal Seminar Magang Mendatang</h3>
        </div>
        <a href="{{ route('admin.jadwal-seminar.index') }}" class="text-sm text-[#b45309] font-semibold hover:underline">Lihat Semua</a>
    </div>
    <div class="overflow-x-auto">
        @if($seminarTerdekat->count() > 0)
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">Mahasiswa</th>
                    <th class="px-6 py-3">Prodi</th>
                    <th class="px-6 py-3">Perusahaan</th>
                    <th class="px-6 py-3">Dosen Pembimbing</th>
                    <th class="px-6 py-3">Tanggal</th>
                    <th class="px-6 py-3">Waktu</th>
                    <th class="px-6 py-3">Ruang</th>
                </tr>
            </thead>
            <tbody>
                @foreach($seminarTerdekat as $item)
                <tr class="border-b border-gray-50 hover:bg-[#fffbeb]/30 transition">
                    <td class="px-6 py-4">
                        <div>
                            <p class="font-medium text-gray-900">{{ $item->mahasiswa->nama ?? '-' }}</p>
                            <p class="text-xs text-gray-400">{{ $item->mahasiswa->npm ?? '-' }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-700">{{ $item->mahasiswa->prodi->nama_prodi ?? '-' }}</p>
                    </td>
                    <td class="px-6 py-4">{{ $item->perusahaan->nama_perusahaan ?? '-' }}</td>
                    <td class="px-6 py-4">{{ $item->dosenPembimbing->dosen->nama ?? '-' }}</td>
                    <td class="px-6 py-4">
                        @if($item->tgl_semgang)
                            {{ \Carbon\Carbon::parse($item->tgl_semgang)->locale('id')->translatedFormat('d M Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        {{ $item->jam_mulai ? substr($item->jam_mulai, 0, 5) : '-' }}
                        @if($item->jam_selesai)
                            <span class="text-gray-400">-</span> {{ substr($item->jam_selesai, 0, 5) }}
                        @endif
                    </td>
                    <td class="px-6 py-4">{{ $item->ruang ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="px-6 py-12 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <p class="text-gray-400 font-medium text-sm">Tidak ada jadwal seminar magang mendatang</p>
        </div>
        @endif
    </div>
</div>

@endsection