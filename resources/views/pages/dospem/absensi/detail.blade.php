@extends('layouts.app') {{-- Extends layout utama aplikasi --}}

@section('content') {{-- Mulai section content yang akan dimasukkan ke layout --}}
<div class="max-w-7xl mx-auto px-4 md:px-6 py-6"> {{-- Container utama dengan max width dan padding responsif --}}

    <!-- Breadcrumb & Header -->
    <div class="mb-6"> {{-- Margin bawah --}}

        {{-- Navigation breadcrumb untuk menunjukkan posisi halaman --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-3">
            <a href="{{ route('dospem.absensi.index') }}" class="hover:text-yellow-600 transition">Absensi</a> {{-- Link ke daftar absensi --}}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Icon panah kanan --}}
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <span class="text-gray-800 font-medium">Detail {{ $mahasiswa->nama }}</span> {{-- Nama mahasiswa yang sedang dilihat --}}
        </nav>

        {{-- Header dengan judul dan tombol kembali --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Absensi - {{ $mahasiswa->nama }}</h1> {{-- Judul halaman --}}
                <p class="text-sm text-gray-500 mt-1">NPM: {{ $mahasiswa->npm }} &middot; Perusahaan: {{ $perusahaan }}</p> {{-- Info NPM dan perusahaan --}}
            </div>
            <a href="{{ route('dospem.absensi.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-yellow-600 transition font-medium"> {{-- Tombol kembali --}}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Icon panah kiri --}}
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Statistik Cards -->
    {{-- Grid 7 kolom untuk menampilkan statistik --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3 mb-6">
        
        {{-- Card Total Absensi --}}
        <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm text-center">
            <p class="text-xs text-gray-500 mb-1">Total</p>
            <p class="text-lg font-bold text-gray-800">{{ $totalAbsensi }}</p> {{-- Nilai total absensi --}}
        </div>
        
        {{-- Card Total Hadir (warna hijau) --}}
        <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm text-center">
            <p class="text-xs text-gray-500 mb-1">Hadir</p>
            <p class="text-lg font-bold text-green-600">{{ $totalHadir }}</p> {{-- Nilai hadir --}}
        </div>
        
        {{-- Card Total Sakit (warna kuning) --}}
        <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm text-center">
            <p class="text-xs text-gray-500 mb-1">Sakit</p>
            <p class="text-lg font-bold text-yellow-600">{{ $totalSakit }}</p> {{-- Nilai sakit --}}
        </div>
        
        {{-- Card Total Izin (warna biru) --}}
        <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm text-center">
            <p class="text-xs text-gray-500 mb-1">Izin</p>
            <p class="text-lg font-bold text-blue-600">{{ $totalIzin }}</p> {{-- Nilai izin --}}
        </div>
        
        {{-- Card Pending (background oranye muda) --}}
        <div class="bg-white border border-orange-200 rounded-lg p-3 shadow-sm text-center bg-orange-50">
            <p class="text-xs text-orange-600 mb-1">Pending</p>
            <p class="text-lg font-bold text-orange-600">{{ $totalPending }}</p> {{-- Nilai pending --}}
        </div>
        
        {{-- Card Diterima (background hijau muda) --}}
        <div class="bg-white border border-green-200 rounded-lg p-3 shadow-sm text-center bg-green-50">
            <p class="text-xs text-green-600 mb-1">Diterima</p>
            <p class="text-lg font-bold text-green-600">{{ $totalDiterima }}</p> {{-- Nilai diterima --}}
        </div>
        
        {{-- Card Ditolak (background merah muda) --}}
        <div class="bg-white border border-red-200 rounded-lg p-3 shadow-sm text-center bg-red-50">
            <p class="text-xs text-red-600 mb-1">Ditolak</p>
            <p class="text-lg font-bold text-red-600">{{ $totalDitolak }}</p> {{-- Nilai ditolak --}}
        </div>
    </div>

    <!-- Tabel Absensi -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden"> {{-- Container tabel --}}
        
        {{-- Header tabel --}}
        <div class="p-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-700">Riwayat Absensi</h2>
        </div>

        <div class="overflow-x-auto"> {{-- Wrapper untuk horizontal scroll pada layar kecil --}}
            <table class="w-full text-sm"> {{-- Tabel dengan lebar penuh --}}
                
                {{-- Header tabel --}}
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">No</th> {{-- Kolom nomor --}}
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Tanggal</th> {{-- Kolom tanggal --}}
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Jam</th> {{-- Kolom jam absen --}}
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th> {{-- Kolom status kehadiran --}}
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Jarak</th> {{-- Kolom jarak --}}
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Validasi</th> {{-- Kolom status validasi --}}
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Aksi</th> {{-- Kolom tombol aksi --}}
                    </tr>
                </thead>
                
                {{-- Body tabel --}}
                <tbody class="divide-y divide-gray-100">
                    @forelse($daftarAbsensi as $index => $absen) {{-- Loop melalui data absensi --}}
                        <tr class="hover:bg-gray-50 transition"> {{-- Baris dengan efek hover --}}
                            
                            {{-- Kolom Nomor: dihitung berdasarkan halaman --}}
                            <td class="px-4 py-3 text-gray-500">
                                {{ ($daftarAbsensi->currentPage() - 1) * $daftarAbsensi->perPage() + $index + 1 }}
                            </td>
                            
                            {{-- Kolom Tanggal: format Indonesia --}}
                            <td class="px-4 py-3 text-gray-700">
                                {{ \Carbon\Carbon::parse($absen->tanggal)->format('d F Y') }}
                            </td>
                            
                            {{-- Kolom Jam: font monospace --}}
                            <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $absen->jam_absen }}</td>
                            
                            {{-- Kolom Status Kehadiran: badge berwarna --}}
                            <td class="px-4 py-3 text-center">
                                @php
                                    // Konfigurasi warna badge berdasarkan status
                                    $statusConfig = [
                                        'hadir' => ['bg-green-100 text-green-700', 'Hadir'],    // Hijau untuk hadir
                                        'sakit' => ['bg-yellow-100 text-yellow-700', 'Sakit'],  // Kuning untuk sakit
                                        'izin'  => ['bg-blue-100 text-blue-700', 'Izin'],      // Biru untuk izin
                                        'alpa'  => ['bg-red-100 text-red-700', 'Alpa'],        // Merah untuk alpa
                                    ];
                                    // Ambil konfigurasi sesuai status, default abu-abu jika tidak dikenali
                                    $sc = $statusConfig[$absen->status] ?? ['bg-gray-100 text-gray-700', $absen->status];
                                @endphp
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $sc[0] }}">
                                    {{ $sc[1] }}
                                </span>
                            </td>
                            
                            {{-- Kolom Jarak: warna merah jika > 100 meter --}}
                            <td class="px-4 py-3 text-xs">
                                @if($absen->jarak_meter !== null) {{-- Jika jarak tercatat --}}
                                    @if($absen->jarak_meter > 100) {{-- Jika jarak melebihi 100 meter --}}
                                        <div class="flex items-center gap-1 text-red-600 font-semibold">
                                            
                                            {{ number_format($absen->jarak_meter, 0) }} m {{-- Tampilkan jarak tanpa desimal --}}
                                        </div>
                                    @else
                                        <span class="text-green-600 font-medium">{{ number_format($absen->jarak_meter, 0) }} m</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span> {{-- Tanda strip jika tidak ada data --}}
                                @endif
                            </td>
                            
                            {{-- Kolom Status Validasi: badge berwarna --}}
                            <td class="px-4 py-3 text-center">
                                @php
                                    // Konfigurasi warna badge untuk status validasi
                                    $validasiConfig = [
                                        'pending'  => ['bg-orange-100 text-orange-700', 'Pending'],  // Oranye untuk pending
                                        'diterima' => ['bg-green-100 text-green-700', 'Diterima'], // Hijau untuk diterima
                                        'ditolak'  => ['bg-red-100 text-red-700', 'Ditolak'],     // Merah untuk ditolak
                                    ];
                                    // Ambil konfigurasi sesuai status
                                    $vc = $validasiConfig[$absen->status_validasi] ?? ['bg-gray-100 text-gray-700', $absen->status_validasi];
                                @endphp
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $vc[0] }}">
                                    {{ $vc[1] }}
                                </span>
                            </td>
                            
                            {{-- Kolom Aksi: tombol validasi --}}
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('dospem.absensi.validasi', ['id_absensi' => $absen->id_absensi]) }}"
                                   class="inline-flex items-center gap-1 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium px-3 py-1.5 rounded-md transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Icon checklist --}}
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                    Validasi
                                </a>
                            </td>
                        </tr>
                    @empty {{-- Jika tidak ada data absensi --}}
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center"> {{-- Span 7 kolom --}}
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Icon kalender kosong --}}
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="text-gray-400 font-medium">Belum ada data absensi</p> {{-- Pesan kosong --}}
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($daftarAbsensi->hasPages()) {{-- Hanya tampilkan jika ada lebih dari 1 halaman --}}
        <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
            <p class="text-xs text-gray-500">
                Menampilkan {{ $daftarAbsensi->firstItem() ?? 0 }} - {{ $daftarAbsensi->lastItem() ?? 0 }} dari {{ $daftarAbsensi->total() }} data {{-- Info jumlah data --}}
            </p>
            {{ $daftarAbsensi->links() }} {{-- Render pagination links --}}
        </div>
        @endif
    </div>

    <!-- Warning jarak di luar tabel -->
    @php
        // Cek apakah ada absensi dengan jarak lebih dari 100 meter
        $adaJarakJauh = $daftarAbsensi->contains(function ($a) {
            return $a->jarak_meter !== null && $a->jarak_meter > 100;
        });
    @endphp
    @if($adaJarakJauh) {{-- Tampilkan warning jika ada jarak yang melebihi batas --}}
    <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3">
       
        <div class="text-sm">
            <p class="font-semibold text-red-700">Perhatian</p>
            <p class="text-red-600 mt-0.5">Absensi tidak berlaku jika jarak dari perusahaan lebih dari 100 meter. Absensi dengan jarak melebihi batas ditandai dengan <span class="font-semibold text-red-700">warna merah</span>.</p>
        </div>
    </div>
    @endif

</div>
@endsection