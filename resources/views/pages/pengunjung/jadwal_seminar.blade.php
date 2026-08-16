@extends('layouts.app')

@section('content')
<div class="my-4 md:my-8"></div>

<div class="text-center mb-4 md:mb-8">
    <h2 class="text-base md:text-xl font-semibold underline decoration-blue-500">
        Jadwal Seminar Magang Mahasiswa <br>
        Jurusan Komputer dan Bisnis
    </h2>
</div>

{{-- ✅ FILTER PRODI DROPDOWN --}}
<div class="flex justify-center mb-4 md:mb-6">
    <form action="{{ route('pengunjung.jadwal-seminar') }}" method="GET" class="flex items-center gap-2 md:gap-3">
        <label for="id_prodi" class="font-semibold text-gray-700 text-xs md:text-sm">Pilih Prodi:</label>
        <select name="id_prodi" id="id_prodi" 
                onchange="this.form.submit()" 
                class="border border-gray-400 rounded-lg px-2 py-1.5 md:px-4 md:py-2 text-xs md:text-sm bg-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent cursor-pointer">
            <option value="" {{ request('id_prodi') == '' ? 'selected' : '' }}>Semua Prodi</option>
            @foreach($prodis as $prodi)
                <option value="{{ $prodi->id_prodi }}" {{ request('id_prodi') == $prodi->id_prodi ? 'selected' : '' }}>
                    {{ $prodi->nama_prodi }}
                </option>
            @endforeach
        </select>
    </form>
</div>

{{-- ✅ TABEL JADWAL DENGAN PAGINATE --}}
<div class="max-w-6xl mx-auto px-2 md:px-0">
    <div class="border md:border-2 border-yellow-400 rounded-lg overflow-x-auto">
        {{-- Perbaikan typo text-balck-500 jadi text-gray-700 --}}
        <table class="w-full text-[11px] md:text-sm text-left text-gray-700">
            
            {{-- ✅ HEADER WARNANA KUNING DENGAN TEKS HITAM --}}
            <thead class="text-[10px] md:text-xs text-gray-900 uppercase bg-yellow-400 border-b border-yellow-500">
                <tr>
                    <th class="px-2 md:px-6 py-2 md:py-3">No</th>
                    <th class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">Nama Mahasiswa</th>
                    <th class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">Prodi</th>
                    <th class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">Perusahaan</th>
                    <th class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">Tanggal</th>
                    <th class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">Waktu</th>
                    <th class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">Ruang</th>
                </tr>
            </thead>
            
            <tbody>
                @forelse($jadwal as $key => $item)
                <tr class="bg-white border-b hover:bg-yellow-50">
                    <td class="px-2 md:px-6 py-2 md:py-4">{{ $jadwal->firstItem() + $key }}</td>
                    <td class="px-2 md:px-6 py-2 md:py-4 font-medium text-gray-900 whitespace-nowrap">
                        {{ $item->mahasiswa->nama ?? '-' }}
                    </td>
                    <td class="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap">
                        {{ $item->mahasiswa->prodi->nama_prodi ?? '-' }}
                    </td>
                    <td class="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap">
                        {{ $item->perusahaan->nama_perusahaan ?? '-' }}
                    </td>
                    <td class="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($item->tgl_semgang)->translatedFormat('d M Y') }}
                    </td>
                    <td class="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap">
                        {{ $item->jam_mulai }} - {{ $item->jam_selesai }}
                    </td>
                    <td class="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap">
                        {{ $item->ruang ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-2 md:px-6 py-4 text-center text-gray-500">
                        Belum ada jadwal seminar untuk prodi ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ✅ TOMBOL PAGINASI BIAR BISA LIHAT LEBIH BANYAK --}}
<div class="mt-4 md:mt-6 flex justify-center text-xs md:text-sm">
    {{ $jadwal->appends(request()->query())->links() }}
</div>

<div class="flex justify-start mt-4 px-4 md:px-10">
    <a href="{{ route('beranda_pengunjung') }}" class="flex items-center gap-1 md:gap-2 text-gray-700 hover:text-black text-xs md:text-sm">
        <span class="text-base md:text-xl">←</span>
        Kembali ke Beranda
    </a>
</div>

@endsection