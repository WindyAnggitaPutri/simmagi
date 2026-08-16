@extends('layouts.app') {{-- Extends layout utama aplikasi --}}

@section('title', 'Detail Absensi') {{-- Judul tab browser --}}

@section('content') {{-- Mulai section content --}}
<div class="max-w-4xl mx-auto px-4 py-8"> {{-- Container dengan lebar maksimal 4xl, padding horizontal dan vertikal --}}

    <!-- Header -->
    <div class="flex items-center gap-3 mb-8"> {{-- Flexbox dengan jarak antar elemen, margin bawah --}}
        
        {{-- Tombol kembali ke halaman riwayat absensi --}}
        <a href="{{ route('mahasiswa.absensi.index') }}" 
           class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/> {{-- Icon panah kiri --}}
            </svg>
        </a>
        
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Detail Absensi</h1> {{-- Judul halaman --}}
            <p class="text-gray-500 mt-1">Informasi lengkap absensi harian Anda</p> {{-- Deskripsi --}}
        </div>
    </div>

    <!-- Info Utama -->
    {{-- Grid 5 kolom responsif untuk menampilkan ringkasan info absensi --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
        
        {{-- Card: Tanggal Absensi --}}
        <div class="bg-white rounded-lg shadow p-4 border border-gray-100">
            <div class="text-xs font-medium text-gray-500">Tanggal</div> {{-- Label --}}
            <div class="mt-1 text-sm font-semibold text-gray-800">{{ $absensi->tanggal_formatted }}</div> {{-- Nilai tanggal dari accessor --}}
        </div>
        
        {{-- Card: Jam Absen --}}
        <div class="bg-white rounded-lg shadow p-4 border border-gray-100">
            <div class="text-xs font-medium text-gray-500">Jam Absen</div>
            <div class="mt-1 text-sm font-semibold text-gray-800">{{ $absensi->jam_formatted }}</div> {{-- Nilai jam dari accessor --}}
        </div>
        
        {{-- Card: Status Kehadiran (badge HTML dari accessor) --}}
        <div class="bg-white rounded-lg shadow p-4 border border-gray-100">
            <div class="text-xs font-medium text-gray-500">Status</div>
            <div class="mt-1">{!! $absensi->status_badge !!}</div> {{-- Badge berwarna, menggunakan {!! !!} karena berisi HTML --}}
        </div>
        
        {{-- Card: Jarak dari Perusahaan --}}
        <div class="bg-white rounded-lg shadow p-4 border border-gray-100">
            <div class="text-xs font-medium text-gray-500">Jarak</div>
            <div class="mt-1">{!! $absensi->jarak_tampil !!}</div> {{-- Jarak dengan indikator warna --}}
        </div>
        
        {{-- Card: Status Validasi oleh Dosen --}}
        <div class="bg-white rounded-lg shadow p-4 border border-gray-100">
            <div class="text-xs font-medium text-gray-500">Validasi</div>
            <div class="mt-1">{!! $absensi->validasi_badge !!}</div> {{-- Badge validasi (pending/diterima/ditolak) --}}
        </div>
    </div>

    <!-- Catatan (hanya tampil jika ada isinya) -->
    @if($absensi->catatan) {{-- Cek apakah field catatan tidak kosong --}}
    <div class="bg-white rounded-lg shadow mb-6 border border-gray-100"> {{-- Card catatan --}}
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Catatan</h2> {{-- Judul card --}}
        </div>
        <div class="p-6">
            {{-- Tampilkan catatan dengan whitespace-pre-wrap agar baris baru dihormati --}}
            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $absensi->catatan }}</p>
        </div>
    </div>
    @endif

    <!-- Foto Absensi -->
    <div class="bg-white rounded-lg shadow mb-6 border border-gray-100"> {{-- Card foto --}}
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Foto Absensi</h2>
        </div>
        <div class="p-6">
            @if($absensi->foto) {{-- Jika ada file foto --}}
                <div class="flex justify-center"> {{-- Foto di tengah --}}
                    <img src="{{ Storage::url($absensi->foto) }}" alt="Foto Absensi" class="max-w-md w-full rounded-lg border border-gray-200 shadow-sm"> {{-- Gambar dari storage --}}
                </div>
            @else {{-- Jika tidak ada foto --}}
                <div class="flex items-center justify-center h-48 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-gray-400 text-sm">Tidak ada foto</p> {{-- Pesan kosong --}}
                </div>
            @endif
        </div>
    </div>

    <!-- Lokasi GPS & Peta -->
    <div class="bg-white rounded-lg shadow mb-6 border border-gray-100"> {{-- Card lokasi --}}
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Lokasi GPS</h2>
        </div>
        <div class="p-6">
            @if($absensi->latitude && $absensi->longitude) {{-- Jika ada data koordinat --}}
                
                {{-- Box info koordinat dan alamat --}}
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 mb-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"> {{-- Icon lokasi/pin --}}
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1 min-w-0"> {{-- min-w-0 agar teks panjang bisa wrap --}}
                            <p class="text-sm text-gray-700 font-mono"> {{-- Koordinat dalam font monospace --}}
                                Lat: {{ number_format($absensi->latitude, 6) }} | Lng: {{ number_format($absensi->longitude, 6) }} {{-- Format 6 desimal --}}
                            </p>
                            @if($absensi->alamat) {{-- Tampilkan alamat jika ada --}}
                                <p class="text-sm text-gray-500 mt-1 break-words">{{ $absensi->alamat }}</p> {{-- break-words agar alamat panjang tidak overflow --}}
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Embed Google Maps dengan koordinat --}}
                <div class="rounded-lg overflow-hidden border-2 border-blue-200 shadow-sm">
                    <iframe 
                        src="https://www.google.com/maps?q={{ $absensi->latitude }},{{ $absensi->longitude }}&output=embed&z=17"  {{-- URL embed dengan zoom 17 --}}
                        width="100%" 
                        height="350" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy"  {{-- Lazy loading untuk performa --}}
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>

                {{-- Link untuk membuka lokasi di Google Maps tab baru --}}
                <a href="https://www.google.com/maps?q={{ $absensi->latitude }},{{ $absensi->longitude }}" 
                   target="_blank" 
                   rel="noopener noreferrer"  {{-- Keamanan untuk target _blank --}}
                   class="mt-3 inline-flex items-center gap-1.5 text-sm text-blue-500 hover:text-blue-700 hover:underline font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Icon external link --}}
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Buka di Google Maps
                </a>
            @else {{-- Jika tidak ada data koordinat --}}
                <div class="flex items-center justify-center h-32 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-gray-400 text-sm">Tidak ada data lokasi</p> {{-- Pesan kosong --}}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection 