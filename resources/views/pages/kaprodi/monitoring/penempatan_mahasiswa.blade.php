@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6">
    <h1 class="text-center text-2xl font-bold mb-2 text-yellow-400">Monitoring Penempatan Magang</h1>
    <p class="text-center text-gray-500 mb-8">Daftar mahasiswa yang diterima di perusahaan (Surat Balasan Diverifikasi Kaprodi)</p>

    {{--   SEARCH BAR & FILTER --}}
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        {{-- Search Bar --}}
        <form method="GET" action="{{ route('kaprodi.monitoring.penempatan') }}" class="flex-1">
            <div class="relative">
                <input type="text" 
                       name="search" 
                       value="{{ $search ?? '' }}" 
                       placeholder="Cari nama, NPM, atau perusahaan..." 
                       class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                @if($search)
                    <a href="{{ route('kaprodi.monitoring.penempatan') }}" 
                       class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </a>
                @endif
            </div>
        </form>
        
        {{--   FILTER PER PAGE (Semua, 5, 10, 15) --}}
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600 whitespace-nowrap font-medium">Tampilkan:</label>
            <select id="perPageSelect" 
                    class="px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition bg-white min-w-[100px]">
                <option value="all" {{ ($perPage ?? 'all') === 'all' ? 'selected' : '' }}>Semua</option>
                <option value="5" {{ ($perPage ?? '') === '5' ? 'selected' : '' }}>5</option>
                <option value="10" {{ ($perPage ?? '') === '10' ? 'selected' : '' }}>10</option>
                <option value="15" {{ ($perPage ?? '') === '15' ? 'selected' : '' }}>15</option>
            </select>
        </div>
    </div>

    @if($dataMahasiswa->isEmpty())
        <div class="text-center py-16 bg-gray-50 border border-gray-200 rounded-lg">
            <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-gray-500 font-medium">Belum ada mahasiswa yang diterima di perusahaan saat ini.</p>
            <p class="text-gray-400 text-sm mt-1">Data akan muncul setelah surat balasan diverifikasi oleh Kaprodi.</p>
        </div>
    @else
        <div class="bg-white shadow-md border border-yellow-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-yellow-400">
                        <tr>
                            <th class="p-4 text-center w-16 text-white font-semibold">No</th>
                            <th class="p-4 text-white font-semibold">Nama Mahasiswa</th>
                            <th class="p-4 text-white font-semibold">NPM</th>
                            <th class="p-4 text-white font-semibold">Perusahaan</th>
                            <th class="p-4 text-center text-white font-semibold">Status Verifikasi</th>
                            <th class="p-4 text-center w-40 text-white font-semibold">Surat Balasan</th>
                            <th class="p-4 text-center w-40 text-white font-semibold">Surat Pengantar</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @php 
                            $no = ($isPaginated ?? false) ? $dataMahasiswa->firstItem() : 1; 
                        @endphp
                        @foreach($dataMahasiswa as $pm)
                            <tr class="border-b border-gray-100 hover:bg-yellow-50/50 transition">
                                <td class="p-4 text-center text-gray-500 font-medium">{{ $no++ }}</td>
                                <td class="p-4 font-semibold text-gray-900">{{ $pm->mahasiswa->nama ?? '-' }}</td>
                                <td class="p-4 text-gray-600 font-mono">{{ $pm->mahasiswa->npm ?? '-' }}</td>
                                <td class="p-4">{{ $pm->pengajuan->perusahaan->nama_perusahaan ?? '-' }}</td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-100 text-green-700 rounded-full text-xs font-bold border border-green-200">
                                        <!-- <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg> -->
                                        DITERIMA
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    @if($pm->surat_balasan)
                                        <a href="{{ asset('storage/surat_balasan/' . $pm->surat_balasan) }}" 
                                           target="_blank" 
                                           class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 hover:underline text-xs font-semibold transition">
                                            <!-- <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg> -->
                                            Lihat Surat
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    @if($pm->pengajuan->suratPengantar && $pm->pengajuan->suratPengantar->file_surat)
                                        <a href="{{ asset('storage/surat_pengantar_upload/' . $pm->pengajuan->suratPengantar->file_surat) }}" 
                                           target="_blank" 
                                           class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 hover:underline text-xs font-semibold transition">
                                            <!-- <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg> -->
                                            Lihat Surat
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs">Tidak ada</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{--   PAGINATION --}}
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                @if($isPaginated ?? false)
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="text-sm text-gray-600">
                            Menampilkan <span class="font-semibold">{{ $dataMahasiswa->firstItem() }}</span> - <span class="font-semibold">{{ $dataMahasiswa->lastItem() }}</span> 
                            dari <span class="font-semibold">{{ $dataMahasiswa->total() }}</span> data
                        </p>
                        <div class="flex items-center gap-1">
                            {{ $dataMahasiswa->appends(['search' => $search, 'per_page' => $perPage])->links() }}
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-600">
                        Menampilkan semua <span class="font-semibold">{{ $dataMahasiswa->count() }}</span> data
                    </p>
                @endif
            </div>
        </div>
    @endif
</div>

{{--   JavaScript untuk Filter Per Page --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const perPageSelect = document.getElementById('perPageSelect');
    
    perPageSelect.addEventListener('change', function() {
        const url = new URL(window.location.href);
        
        if (this.value === 'all') {
            url.searchParams.delete('per_page');
        } else {
            url.searchParams.set('per_page', this.value);
        }
        
        // Reset ke halaman 1 saat ganti filter
        url.searchParams.delete('page');
        
        window.location.href = url.toString();
    });
});
</script>
@endsection