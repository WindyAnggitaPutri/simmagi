@extends('layouts.app')

@section('content')
<!-- beranda -->
<div class="grid md:grid-cols-2 gap-6 items-center">
    {{-- Kiri --}}
    <div>
        <h1 class="text-6xl font-bold mb-4">
            SISTEM INFORMASI <br>
            MONITORING MAGANG <br>
            INDUSTRI
        </h1>

        <p class="text-gray-950 mb-5">
            Sistem Informasi Monitoring Magang Industri adalah platform digital
            berbasis web yang dirancang untuk mengelola, memantau, dan mengevaluasi
            kinerja serta aktivitas peserta magang secara real-time.
        </p>

        {{-- ✅ STATISTIK DINAMIS --}}
        <div class="flex gap-6">
            <div>
                <p class="text-2xl font-bold">{{ $jumlahMahasiswa }}</p>
                <p class="text-sm text-gray-500">Mahasiswa Aktif</p>
            </div>
            <div>
                <p class="text-2xl font-bold">{{ $jumlahProdi }}</p>
                <p class="text-sm text-gray-500">Jumlah Prodi</p>
            </div>
            <div>
                <p class="text-2xl font-bold">{{ $jumlahPerusahaan }}</p>
                <p class="text-sm text-gray-500">Perusahaan</p>
            </div>
        </div>
    </div>

    {{-- Kanan --}}
    <img src="{{ asset('images/profil.png') }}" class="w-300 h-140 object-cover">
</div>

<!-- jadwal -->
<div class="my-16"></div>
<div class="text-center mb-8">
    <h2 class="text-xl font-semibold underline decoration-blue-500">
        Jadwal Seminar Magang Mahasiswa <br>
        Jurusan Komputer dan Bisnis
    </h2>
</div>

<div class="flex justify-center">
    <div class="w-full max-w-6xl border-2 border-gray-400 rounded-lg overflow-hidden">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Nama Mahasiswa</th>
                    <th class="px-6 py-3">Prodi</th>
                    <th class="px-6 py-3">Perusahaan</th>
                    <th class="px-6 py-3">Tanggal</th>
                    <th class="px-6 py-3">Waktu</th>
                    <th class="px-6 py-3">Ruang</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwal as $key => $item)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4">{{ $key + 1 }}</td>
                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                        {{ $item->mahasiswa->nama ?? '-' }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $item->mahasiswa->prodi->nama_prodi ?? '-' }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $item->perusahaan->nama_perusahaan ?? '-' }}
                    </td>
                    <td class="px-6 py-4">
                        {{ \Carbon\Carbon::parse($item->tgl_semgang)->translatedFormat('d F Y') }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $item->jam_mulai }} - {{ $item->jam_selesai }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $item->ruang ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        Belum ada jadwal seminar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="flex justify-end mt-4">
    <a href="{{ route('pengunjung.jadwal-seminar') }}" class="flex items-center gap-2 text-gray-700 hover:text-black">
        Selanjutnya
        <span class="text-xl">→</span>
    </a>
</div>

<!-- ✅ REFERENSI PERUSAHAAN DINAMIS -->
<div class="my-16"></div>

<div class="text-center mb-10">
    <h2 class="text-xl font-semibold">
        REFERENSI PERUSAHAAN MAGANG INDUSTRI <br>
        MAHASISWA JURUSAN KOMPUTER DAN BISNIS
    </h2>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-6 px-10">
    @forelse($perusahaans as $perusahaan)
    <div class="border border-gray-300 p-8 text-center hover:shadow-md transition">
        <div class="w-full h-30 bg-gray-200 mb-7 flex items-center justify-center">
            {{-- Placeholder logo perusahaan --}}
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
        </div>
        <p class="font-medium">{{ $perusahaan->nama_perusahaan }}</p>
        <p class="text-sm text-gray-500">Jumlah mahasiswa : {{ $perusahaan->jumlah_mahasiswa }}</p>
        <p class="text-sm text-gray-500">Alamat : {{ Str::limit($perusahaan->alamat ?? '-', 25) }}</p>
    </div>
    @empty
    <div class="col-span-4 text-center text-gray-500 py-8">
        Belum ada data perusahaan.
    </div>
    @endforelse
</div>

<div class="flex justify-end mt-6 px-10">
    <a href="{{ route('pengunjung.referensi-perusahaan') }}" class="flex items-center gap-2 text-gray-700 hover:text-black">
        Selanjutnya
        <span class="text-xl">→</span>
    </a>
</div>

<!-- Alur -->
<div class="my-16"></div>

<div class="text-center mb-10">
    <h2 class="text-xl font-semibold">
        Alur Pengajuan Permohonan Magang
    </h2>
</div>

<div class="w-full max-w-5xl mx-auto flex flex-col items-center gap-0 px-4">
    
    <!-- Baris 1: Langkah 1, 2, 3 -->
    <div class="flex flex-col md:flex-row items-center gap-4 md:gap-6 w-full justify-center">
        
        <!-- Langkah 1 -->
        <div class="bg-[#fffbeb] p-6 rounded-2xl shadow-md border border-amber-100 w-full md:w-72">
            <div class="flex items-center gap-3 mb-3">
                <div class="bg-white w-10 h-10 rounded-full flex items-center justify-center shadow-sm text-amber-600 font-bold text-lg">1</div>
                <h3 class="font-bold text-gray-800">Langkah 1</h3>
            </div>
            <p class="text-sm text-gray-700 leading-relaxed">Membuat surat permohonan magang dan surat izin orang tua</p>
        </div>
        
        <!-- Panah Kanan (Desktop) / Bawah (Mobile) -->
        <div class="text-amber-400 hidden md:flex items-center justify-center w-8">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
        </div>
        <div class="md:hidden text-amber-400 flex justify-center">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
        </div>

        <!-- Langkah 2 -->
        <div class="bg-orange-50 p-6 rounded-2xl shadow-md border border-orange-100 w-full md:w-72">
            <div class="flex items-center gap-3 mb-3">
                <div class="bg-white w-10 h-10 rounded-full flex items-center justify-center shadow-sm text-orange-600 font-bold text-lg">2</div>
                <h3 class="font-bold text-gray-800">Langkah 2</h3>
            </div>
            <p class="text-sm text-gray-700 leading-relaxed">Menunggu persetujuan kaprodi</p>
        </div>

        <!-- Panah Kanan (Desktop) / Bawah (Mobile) -->
        <div class="text-amber-400 hidden md:flex items-center justify-center w-8">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
        </div>
        <div class="md:hidden text-amber-400 flex justify-center">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
        </div>

        <!-- Langkah 3 -->
        <div class="bg-amber-50 p-6 rounded-2xl shadow-md border border-amber-100 w-full md:w-72">
            <div class="flex items-center gap-3 mb-3">
                <div class="bg-white w-10 h-10 rounded-full flex items-center justify-center shadow-sm text-amber-700 font-bold text-lg">3</div>
                <h3 class="font-bold text-gray-800">Langkah 3</h3>
            </div>
            <p class="text-sm text-gray-700 leading-relaxed">Menunggu persetujuan ketua jurusan</p>
        </div>
    </div>

    <!-- Panah Bawah (Desktop - rata kanan sejajar Langkah 3) -->
    <div class="hidden md:flex items-center gap-4 md:gap-6 w-full justify-center my-2">
        <div class="w-72"></div> <!-- Spacer agar panah sejajar di bawah langkah 3 -->
        <div class="w-8"></div>
        <div class="w-72"></div>
        <div class="w-8"></div>
        <div class="w-72 flex justify-center text-amber-400 py-2">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
        </div>
    </div>
    <!-- Panah Bawah (Mobile) -->
    <div class="md:hidden text-amber-400 flex justify-center py-2">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
    </div>

    <!-- Baris 2: Langkah 5, 4 -->
    <div class="flex flex-col md:flex-row items-center gap-4 md:gap-6 w-full justify-center">
        
        <!-- Hidden Spacers untuk Desktop agar posisinya sejajar ke kanan -->
        <div class="hidden md:block w-72"></div>
        <div class="hidden md:block w-8"></div>

        <!-- Langkah 5 -->
        <div class="bg-lime-50 p-6 rounded-2xl shadow-md border border-lime-100 w-full md:w-72">
            <div class="flex items-center gap-3 mb-3">
                <div class="bg-white w-10 h-10 rounded-full flex items-center justify-center shadow-sm text-lime-700 font-bold text-lg">5</div>
                <h3 class="font-bold text-gray-800">Langkah 5</h3>
            </div>
            <p class="text-sm text-gray-700 leading-relaxed">Upload surat balasan dari perusahaan ke sistem</p>
        </div>

        <!-- Panah Kiri (Desktop) / Bawah (Mobile) -->
        <div class="text-amber-400 hidden md:flex items-center justify-center w-8">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path></svg>
        </div>
        <div class="md:hidden text-amber-400 flex justify-center">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
        </div>

        <!-- Langkah 4 -->
        <div class="bg-yellow-50 p-6 rounded-2xl shadow-md border border-yellow-100 w-full md:w-72">
            <div class="flex items-center gap-3 mb-3">
                <div class="bg-white w-10 h-10 rounded-full flex items-center justify-center shadow-sm text-yellow-600 font-bold text-lg">4</div>
                <h3 class="font-bold text-gray-800">Langkah 4</h3>
            </div>
            <p class="text-sm text-gray-700 leading-relaxed">Menunggu surat permohonan magang resmi dari bagian akademik dan kemahasiswaan</p>
        </div>

    </div>

</div>
@endsection