@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-10 px-6">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-yellow-600">Pelaksanaan Seminar Magang</h1>
        <p class="text-gray-500 text-sm mt-1">Informasi jadwal seminar magang Anda</p>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-yellow-50 border border-yellow-300 text-yellow-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($jadwal)
        {{-- KARTU JADWAL (GAYA TIKET) --}}
        <div class="bg-white shadow-lg border border-gray-100 rounded-xl overflow-hidden">
            {{-- Header Kartu --}}
            <div class="bg-yellow-500 p-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-80">Status Seminar</p>
                        <h2 class="text-xl font-bold uppercase">
                            @if($jadwal->status_semgang == 'dijadwalkan')
                                Dijadwalkan
                            @else
                                Selesai
                            @endif
                        </h2>
                    </div>
                    <svg class="w-10 h-10 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>

            {{-- Body Kartu --}}
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Tanggal</p>
                        <p class="text-md font-semibold text-gray-800 mt-1">{{ \Carbon\Carbon::parse($jadwal->tgl_semgang)->format('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Waktu</p>
                        <p class="text-md font-semibold text-gray-800 mt-1">{{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }} WIB</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Dosen Pembimbing</p>
                        <p class="text-md font-semibold text-gray-800 mt-1">{{ $jadwal->dosenPembimbing->dosen->nama ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Perusahaan</p>
                        <p class="text-md font-semibold text-gray-800 mt-1">{{ $jadwal->perusahaan->nama_perusahaan ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Ruang / Link</p>
                        <p class="text-md font-semibold text-gray-800 mt-1">{{ $jadwal->ruang ?? 'Belum diisi' }}</p>
                    </div>
                </div>

                <hr class="my-5 border-dashed">

                <div class="md:col-span-2">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Judul Laporan</p>
                    <p class="text-md font-semibold text-gray-800 mt-1">{{ $jadwal->judul_laporan ?? 'Belum diisi' }}</p>
                </div>
            </div>

            {{-- Footer Kartu --}}
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end">
                <a href="{{ route('mahasiswa.pelaksanaan-seminar.detail', $jadwal->id_jadwal_semgang) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-5 rounded-lg transition duration-200 text-sm">
                    Lihat Detail & Isi Form
                </a>
            </div>
        </div>
    @else
        {{-- EMPTY STATE --}}
        <div class="bg-white shadow-md border border-gray-200 rounded-lg p-10 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Belum Ada Jadwal</h3>
            <p class="mt-2 text-sm text-gray-500">Kaprodi belum membuatkan jadwal seminar magang untuk Anda. Silakan cek kembali nanti.</p>
        </div>
    @endif
</div>
@endsection