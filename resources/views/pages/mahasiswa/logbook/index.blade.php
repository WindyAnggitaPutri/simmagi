@push('styles')
<style>
.logbook-table-container {
    overflow: hidden;
}

.logbook-scroll-wrapper {
    overflow-x: visible;
}

.logbook-per-page-select {
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}

@media (max-width: 768px) {
    .logbook-table-container {
        overflow: visible !important;
        border-radius: 0 !important;
        margin-left: -1.5rem;
        margin-right: -1.5rem;
    }

    .logbook-scroll-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .logbook-scroll-wrapper table {
        min-width: 600px;
    }
}
</style>
@endpush

@extends('layouts.app') 

@section('content')
<div class="p-6">
    
    {{-- NOTIFIKASI ERROR JIKA NEKAT AKSES URL CREATE --}}
    @if (session('error'))
        <div class="max-w-4xl mx-auto mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                <p class="font-semibold text-sm">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- INFO DOSEN PEMBIMBING -->
    <div class="bg-white rounded-xl shadow-md p-4 mb-6 border-l-4 border-yellow-500 flex items-center">
        <div class="bg-yellow-100 p-3 rounded-full mr-4">
            <i class="fas fa-chalkboard-teacher text-yellow-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Dosen Pembimbing Magang</p>
            <h3 class="text-lg font-bold text-gray-800">
                @if($dosenPembimbing && $dosenPembimbing->dosen)
                    {{ $dosenPembimbing->dosen->nama }}
                @else
                  <span class="text-red-500 font-normal text-sm">Belum ada dosen pembimbing</span>
                @endif
            </h3>
        </div>
    </div>

    <!-- TAMPILKAN PER HALAMAN -->
    <div class="flex justify-end items-center mb-3">

    
        <div class="flex items-center gap-2">
            <label for="logbookPerPage" class="text-sm text-gray-500">Tampilkan</label>
            <select id="logbookPerPage" class="logbook-per-page-select bg-white border border-gray-300 rounded-lg text-sm text-gray-700 py-1.5 pl-3 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 cursor-pointer">
                <option value="all" selected>Semua</option>
                <option value="5">5</option>
                <option value="10">10</option>
            </select>
            <span class="text-sm text-gray-500">data</span>
        </div>
    </div>

    <!-- TABEL MINGGUAN -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden logbook-table-container">
        <div class="logbook-scroll-wrapper">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-yellow-500">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Minggu Ke</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Periode Tanggal</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Jml Kegiatan</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="logbookTableBody">
                    @forelse ($groupedByMinggu as $minggu => $items)
                        @php 
                            $total = $items->count(); 
                            $disetujui = $items->where('status', 'disetujui')->count();
                            $pending = $items->where('status', 'pending')->count();
                            $ditolak = $items->where('status', 'ditolak')->count();

                            $mingguDisetujui = \App\Models\ApprovalLogbook::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
                                ->where('minggu_ke', $minggu)
                                ->where('status', 'disetujui')
                                ->exists();
                        @endphp
                        <tr class="hover:bg-gray-50 transition logbook-data-row">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">Minggu ke-{{ $minggu }}</div>
                                @if($mingguDisetujui)
                                    <span class="text-[10px] font-bold text-blue-600 hidden"><i class="fas fa-lock-open mr-1"></i>UNLOCKED</span>
                                @else
                                    <span class="text-[10px] font-bold text-red-500 hidden"><i class="fas fa-lock mr-1"></i>LOCKED</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-700">{{ $items->first()->tanggal->format('d M Y') }} - {{ $items->last()->tanggal->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-blue-700 py-1 px-3 rounded-full text-xs font-bold">{{ $total }} Kegiatan</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center space-x-1 text-xs font-semibold">
                                    @if($pending > 0)<span class="text-yellow-700 py-1 px-2 rounded">{{ $pending }} Pending</span>@endif
                                    @if($disetujui > 0)<span class="text-green-700 py-1 px-2 rounded">{{ $disetujui }} Disetujui</span>@endif
                                    @if($ditolak > 0)<span class="text-red-700 py-1 px-2 rounded">{{ $ditolak }} Ditolak</span>@endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="{{ route('mahasiswa.logbook.minggu', $minggu) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition duration-300 text-xs">
                                    <i class="fas fa-eye mr-1"></i> Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr class="logbook-empty-row">
                            <td colspan="5" class="px-6 py-10 text-center">
                                <i class="fas fa-book-open text-4xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500 font-semibold">Belum ada logbook</p>
                                <p class="text-gray-400 text-sm">Mulai catat kegiatan magangmu sekarang!</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var select = document.getElementById('logbookPerPage');
    var tbody = document.getElementById('logbookTableBody');
    if (!select || !tbody) return;

    var rows = Array.from(tbody.querySelectorAll('tr.logbook-data-row'));
    var emptyRow = tbody.querySelector('tr.logbook-empty-row');

    function updateRows() {
        var val = select.value;

        if (rows.length === 0) {
            if (emptyRow) emptyRow.style.display = '';
            return;
        }

        if (val === 'all') {
            rows.forEach(function(r) { r.style.display = ''; });
        } else {
            var limit = parseInt(val, 10);
            rows.forEach(function(r, i) {
                r.style.display = i < limit ? '' : 'none';
            });
        }
    }

    select.addEventListener('change', updateRows);
    updateRows();
});
</script>
@endpush