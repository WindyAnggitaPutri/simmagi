@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6">
    <h1 class="text-center text-2xl font-bold mb-2 text-yellow-400">Monitoring Penempatan Magang</h1>
    <p class="text-center text-gray-500 mb-8">Daftar mahasiswa yang diterima di perusahaan (Diverifikasi Kaprodi)</p>

    @if ($pengajuans->isEmpty())
        <div class="text-center py-10 bg-gray-50 border border-gray-200 rounded-lg text-gray-500">
            Belum ada mahasiswa yang diterima di perusahaan saat ini.
        </div>
    @else
        <div class="bg-white shadow-md border border-yellow-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-yellow-400 text-gray-100">
                        <tr>
                            <th class="p-4 text-center w-12">No</th>
                            <th class="p-4">Nama Mahasiswa</th>
                            <th class="p-4">NPM</th>
                            <th class="p-4">Perusahaan</th>
                            <th class="p-4 text-center">Status Verifikasi</th>
                            <th class="p-4 text-center w-40">Surat Balasan</th>
                            <th class="p-4 text-center w-40">Surat Pengantar</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @php $no = 1; @endphp
                        @foreach ($pengajuans as $p)
                            {{-- ✅ Cari surat balasan dari anggota kelompok manapun --}}
                            @php
                                $suratBalasanKelompok = $p->mahasiswa
                                    ->whereNotNull('surat_balasan')
                                    ->first()?->surat_balasan;
                            @endphp

                            @foreach ($p->mahasiswa as $pm)
                                @if($pm->mahasiswa) 
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                    <td class="p-4 text-center text-gray-500 font-medium">{{ $no++ }}</td>
                                    <td class="p-4 font-semibold text-gray-900">{{ $pm->mahasiswa->nama ?? '-' }}</td>
                                    <td class="p-4 text-gray-600">{{ $pm->mahasiswa->npm ?? '-' }}</td>
                                    <td class="p-4">{{ $p->perusahaan->nama_perusahaan ?? '-' }}</td>
                                    <td class="p-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">
                                            ✅ DITERIMA
                                        </span>
                                    </td>

                                    {{-- ✅ KOLOM SURAT BALASAN --}}
                                    <td class="p-4 text-center">
                                        @php
                                            $fileSuratBalasan = $pm->surat_balasan ?? $suratBalasanKelompok;
                                        @endphp
                                        @if ($fileSuratBalasan)
                                            <a href="{{ asset('storage/surat_balasan/' . $fileSuratBalasan) }}" target="_blank" class="text-blue-600 hover:underline text-xs font-semibold">
                                                📄 Lihat Surat
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs">Tidak ada</span>
                                        @endif
                                    </td>

                                    {{-- ✅ KOLOM SURAT PENGANTAR --}}
                                    <td class="p-4 text-center">
                                        @if ($p->suratPengantar && $p->suratPengantar->file_surat)
                                            <a href="{{ asset('storage/surat_pengantar_upload/' . $p->suratPengantar->file_surat) }}" target="_blank" class="text-blue-600 hover:underline text-xs font-semibold">
                                                📄 Lihat Surat
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs">Tidak ada</span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection