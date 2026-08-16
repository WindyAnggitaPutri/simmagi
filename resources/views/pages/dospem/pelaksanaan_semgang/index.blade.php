@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-yellow-500">Jadwal Seminar Magang</h1>
            <p class="text-gray-500 text-sm mt-1">Daftar Jadwal Seminar Mahasiswa Bimbingan Anda</p>
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

<div class="bg-white shadow-md border border-gray-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-yellow-500 text-white">
                <tr>
                    <th class="p-4 text-center w-12">No</th>
                    <th class="p-4">Mahasiswa</th>
                    <th class="p-4">Perusahaan</th>
                    <th class="p-4">Tanggal</th>
                    <th class="p-4">Waktu</th>
                    <th class="p-4">Ruang</th>
                    <th class="p-4 text-center">Status</th>
                    <th class="p-4 text-center">Penilaian</th> {{-- KOLOM BARU 1 --}}
                    <th class="p-4 text-center">Surat Nilai</th>  {{-- KOLOM BARU 2 --}}
                </tr>
            </thead>
            <tbody class="text-gray-700">
                @forelse($jadwal as $key => $item)
                <tr class="border-b border-gray-100 hover:bg-yellow-50 transition">
                    <td class="p-4 text-center text-gray-500 font-medium">{{ $key + 1 }}</td>
                    <td class="p-4 font-semibold text-gray-900">{{ $item->mahasiswa->nama ?? '-' }}</td>
                    <td class="p-4">{{ $item->perusahaan->nama_perusahaan ?? '-' }}</td>
                    <td class="p-4">{{ \Carbon\Carbon::parse($item->tgl_semgang)->format('d M Y') }}</td>
                    <td class="p-4">{{ $item->jam_mulai }} - {{ $item->jam_selesai }}</td>
                    <td class="p-4">{{ $item->ruang ?? '-' }}</td>
                    
                    <td class="p-4 text-center">
                        @if($item->status_semgang == 'dijadwalkan')
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold">Dijadwalkan</span>
                        @else
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">Selesai</span>
                        @endif
                    </td>

                    {{-- KOLOM 1: TOMBOL ISI/UBAH NILAI --}}
                    <td class="p-4 text-center">
                        @if(is_null($item->total_nilai))
                            <a href="{{ route('dospem.jadwal-semgang.form-nilai', $item->id_jadwal_semgang) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1.5 px-3 rounded-lg text-xs transition">
                                Isi Nilai
                            </a>
                        @else
                            <a href="{{ route('dospem.jadwal-semgang.form-nilai', $item->id_jadwal_semgang) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1.5 px-3 rounded-lg text-xs transition">
                                Ubah Nilai
                            </a>
                        @endif
                    </td>

                    {{-- KOLOM 2: TOMBOL DETAIL SURAT --}}
                    <td class="p-4 text-center">
                        <a href="{{ route('dospem.jadwal-semgang.detail-nilai', $item->id_jadwal_semgang) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1.5 px-3 rounded-lg text-xs transition">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="p-10 text-center text-gray-400">Belum ada jadwal seminar untuk mahasiswa bimbingan Anda.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection