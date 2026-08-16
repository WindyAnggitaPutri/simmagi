@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6">
    <div class="mb-8">
        <a href="{{ route('dospem.logbook.index') }}" class="text-yellow-600 font-semibold text-sm hover:underline">← Kembali ke Logbook Mahasiswa</a>
        <h1 class="text-2xl font-bold text-yellow-400 mt-2">Detail Logbook Magang</h1>
    </div>

    {{-- INFO MAHASISWA --}}
    <div class="bg-white shadow-md border border-gray-200 rounded-lg p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <p class="text-sm text-gray-500">Nama Mahasiswa</p>
                <p class="text-lg font-bold text-gray-900">{{ $dosenPembimbing->mahasiswa->nama ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">NPM</p>
                <p class="text-lg font-semibold text-gray-900">{{ $dosenPembimbing->mahasiswa->npm ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Perusahaan</p>
                <p class="text-lg font-semibold text-gray-900">{{ $dosenPembimbing->pengajuanMagang->perusahaan->nama_perusahaan ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Periode Magang</p>
                <p class="text-md font-semibold text-blue-700">
                    @if($dosenPembimbing->pengajuanMagang->tgl_mulai)
                        {{ \Carbon\Carbon::parse($dosenPembimbing->pengajuanMagang->tgl_mulai)->locale('id')->translatedFormat('d M Y') }}
                        &mdash;
                        {{ \Carbon\Carbon::parse($dosenPembimbing->pengajuanMagang->tgl_selesai)->locale('id')->translatedFormat('d M Y') }}
                    @else
                        -
                    @endif
                </p>
            </div>
        </div>

        {{-- Statistik Logbook --}}
        <div class="grid grid-cols-3 gap-4 mt-6 pt-4 border-t border-gray-100">
            <div class="bg-green-50 rounded-lg p-3 text-center">
                <p class="text-xl font-bold text-green-600">{{ $logbooks->where('status', 'disetujui')->count() }}</p>
                <p class="text-xs text-gray-600">Disetujui</p>
            </div>
            <div class="bg-orange-50 rounded-lg p-3 text-center">
                <p class="text-xl font-bold text-orange-600">{{ $logbooks->where('status', 'pending')->count() }}</p>
                <p class="text-xs text-gray-600">Pending</p>
            </div>
            <div class="bg-red-50 rounded-lg p-3 text-center">
                <p class="text-xl font-bold text-red-600">{{ $logbooks->where('status', 'ditolak')->count() }}</p>
                <p class="text-xs text-gray-600">Ditolak</p>
            </div>
        </div>
    </div>

    {{-- DAFTAR LOGBOOK PER MINGGU --}}
    @if($logbooks->isEmpty())
        <div class="bg-white shadow-md border border-gray-200 rounded-lg p-16 text-center">
            <p class="text-gray-400 text-lg">Mahasiswa ini belum mengisi logbook.</p>
        </div>
    @else
        @foreach ($groupedByMinggu as $minggu => $entries)
            @php
                $disetujuiHarian = $entries->where('status', 'disetujui')->count();
                $totalHarian = $entries->count();
                $belumDisetujui = $totalHarian - $disetujuiHarian;
                $kurangHari = 7 - $totalHarian;
                
                // Cek apakah minggu ini sudah di-approve mingguan
                $isMingguDisetujui = \App\Models\ApprovalLogbook::where('id_dosen_pembimbing', $dosenPembimbing->id_dosen_pembimbing)
                    ->where('minggu_ke', $minggu)
                    ->where('status', 'disetujui')
                    ->exists();

                //  LOGIKA BARU: Baru muncul kalau tepat 7 hari DAN semua disetujui
                $bolehApproveMinggu = ($totalHarian == 7 && $disetujuiHarian == 7);
            @endphp
            
            <div class="bg-white shadow-md border border-gray-200 rounded-lg mb-8 overflow-hidden">
                {{-- HEADER MINGGU + TOMBOL APPROVE MINGGUAN --}}
                <div class="flex justify-between items-center bg-gray-50 p-4 border-b">
                    <div class="flex items-center gap-3">
                        <div class="bg-yellow-500 text-white text-sm font-bold px-4 py-1.5 rounded-lg">
                            Minggu Ke-{{ $minggu }}
                        </div>
                        <span class="text-sm text-gray-500">{{ $entries->count() }}/7 entri</span>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($isMingguDisetujui)
                            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">
                                <i class="fas fa-check-double mr-1"></i> Minggu Diapprove
                            </span>
                            
                            {{-- TOMBOL REKAP PDF --}}
                            <a href="{{ route('dospem.logbook.rekap', [$dosenPembimbing->id_dosen_pembimbing, $minggu]) }}" 
                               class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition shadow-sm inline-flex items-center gap-1">
                                <i class="fas fa-file-pdf"></i> Rekap PDF
                            </a>
                        @elseif($bolehApproveMinggu)
                            {{--TOMBOL APPROVE: HANYA MUNCUL JIKA SUDAH 7 HARI & SEMUA DISETUJUI --}}
                            <form action="{{ route('dospem.logbook.approve-minggu', [$dosenPembimbing->id_dosen_pembimbing, $minggu]) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-1.5 rounded-lg transition shadow-sm inline-flex items-center gap-1">
                                    <i class="fas fa-calendar-check"></i> Approve Minggu Ini
                                </button>
                            </form>
                        @else
                            {{-- TOMBOL DISABLE: MENUNJUKKAN INFORMASI KURANG APA --}}
                            <span class="bg-gray-100 text-gray-500 text-xs font-bold px-3 py-1.5 rounded-lg cursor-not-allowed inline-flex items-center gap-1" title="Lengkapi 7 hari dan setujui semua harian terlebih dahulu">
                                <i class="fas fa-lock text-gray-400"></i> 
                                <span>
                                    @if($kurangHari > 0)
                                        Kurang {{ $kurangHari }} hari
                                    @else
                                        Ada {{ $belumDisetujui }} harian belum disetujui
                                    @endif
                                </span>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- TABEL HARIAN --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-yellow-500 text-white">
                            <tr>
                                <th class="p-4 text-center w-12">No</th>
                                <th class="p-4 text-center w-28">Tanggal</th>
                                <th class="p-4 w-64">Kegiatan</th>
                                <th class="p-4 text-center w-28">Status Harian</th>
                                <th class="p-4 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            @foreach ($entries as $idx => $log)
                            <tr class="border-b border-gray-100 hover:bg-yellow-50 transition">
                                <td class="p-4 text-center text-gray-500 font-medium">{{ $loop->iteration }}</td>
                                <td class="p-4 text-center text-gray-600 text-xs">
                                    {{ \Carbon\Carbon::parse($log->tanggal)->locale('id')->translatedFormat('d M Y') }}
                                </td>
                                <td class="p-4">
                                    <p class="text-gray-900 leading-relaxed truncate max-w-xs">
                                        {{ \Illuminate\Support\Str::limit($log->kegiatan ?? '-', 60) }}
                                    </p>
                                </td>
                                <td class="p-4 text-center">
                                    @if($log->status === 'disetujui')
                                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold"> Disetujui</span>
                                    @elseif($log->status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-bold"> Pending</span>
                                    @elseif($log->status === 'ditolak')
                                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold"> Ditolak</span>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    <a href="{{ route('dospem.logbook.detail_logbook', $log->id_logbook) }}" 
                                       class="text-blue-600 hover:underline text-xs font-bold">
                                    Detail
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif

    <div class="mt-6">
        <a href="{{ route('dospem.logbook.index') }}" class="border border-gray-300 text-gray-700 px-6 py-2.5 font-semibold hover:bg-gray-50 transition text-sm rounded-lg">
            Kembali
        </a>
    </div>
</div>
@endsection