@extends('layouts.admin')

@section('title', 'Detail Logbook Mahasiswa')
@section('page-title', 'Detail Logbook Mahasiswa')

@section('content')
<div class="p-6 space-y-6">
    {{-- Tombol Kembali --}}
    <div class="mb-4">
        <a href="{{ route('admin.logbook.index') }}" class="text-orange-500 hover:underline flex items-center gap-1 text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke Monitoring Logbook
        </a>
    </div>

    {{-- Info Mahasiswa --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <h4 class="text-sm font-medium text-gray-500 mb-3">Informasi Mahasiswa</h4>
            <div class="space-y-2">
                <p class="text-sm"><span class="text-gray-500">Nama:</span> <span class="font-medium text-gray-800">{{ $dosenPembimbing->mahasiswa->nama ?? '-' }}</span></p>
                <p class="text-sm"><span class="text-gray-500">NPM:</span> <span class="font-medium text-gray-800">{{ $dosenPembimbing->mahasiswa->npm ?? '-' }}</span></p>
                <p class="text-sm"><span class="text-gray-500">Prodi:</span> <span class="font-medium text-gray-800">{{ $dosenPembimbing->mahasiswa->prodi->nama_prodi ?? '-' }}</span></p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <h4 class="text-sm font-medium text-gray-500 mb-3">Informasi Magang</h4>
            <div class="space-y-2">
                <p class="text-sm"><span class="text-gray-500">Perusahaan:</span> <span class="font-medium text-gray-800">{{ $dosenPembimbing->pengajuanMagang->perusahaan->nama_perusahaan ?? '-' }}</span></p>
                <p class="text-sm"><span class="text-gray-500">Dosen Pembimbing:</span> <span class="font-medium text-gray-800">{{ $dosenPembimbing->dosen->nama ?? '-' }}</span></p>
                <p class="text-sm"><span class="text-gray-500">Total Logbook:</span> <span class="font-medium text-gray-800">{{ $logbooks->count() }} entri</span></p>
            </div>
        </div>
    </div>

    {{-- Logbook per Minggu --}}
    <div class="space-y-4">
        @forelse($groupedByMinggu as $minggu => $logs)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="bg-[#fffbeb] px-5 py-3 flex items-center justify-between border-b border-gray-100">
                <h5 class="text-sm font-semibold text-[#b45309]">Minggu ke-{{ $minggu }}</h5>
                <span class="text-xs text-gray-500 bg-white px-2.5 py-1 rounded-full border border-gray-200">{{ $logs->count() }} hari</span>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($logs as $log)
                <div class="px-5 py-3 flex items-start gap-3 hover:bg-gray-50 transition">
                    <div class="flex-shrink-0 mt-0.5">
                        @if($log->status === 'disetujui')
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100">
                                <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </span>
                        @elseif($log->status === 'ditolak')
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100">
                                <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </span>
                        @else
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-100">
                                <svg class="w-3.5 h-3.5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-medium text-gray-800">{{ \Carbon\Carbon::parse($log->tanggal)->format('d M Y') }}</span>
                            <span class="text-xs text-gray-400">{{ $log->jam_mulai }} - {{ $log->jam_selesai }}</span>
                        </div>
                        <p class="text-sm text-gray-600">{{ $log->kegiatan }}</p>
                        @if($log->status === 'ditolak' && $log->catatan)
                            <p class="text-xs text-red-500 mt-1">Catatan: {{ $log->catatan }}</p>
                        @endif
                    </div>
                    <span class="flex-shrink-0">
                        @if($log->status === 'disetujui')
                            <span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">Disetujui</span>
                        @elseif($log->status === 'ditolak')
                            <span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800">Ditolak</span>
                        @else
                            <span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        @endif
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <p class="text-gray-500 font-medium">Belum ada logbook</p>
        </div>
        @endforelse
    </div>
</div>
@endsection