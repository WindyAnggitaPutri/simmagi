@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-yellow-400">Jadwal Seminar Magang</h1>
            <p class="text-gray-500 text-sm mt-1">Daftar Jadwal Seminar Magang Mahasiswa</p>
        </div>
        
        <a href="{{ route('kaprodi.jadwal-semgang.create') }}" 
           class="border-2 border-yellow-500 text-yellow-600 font-bold text-sm px-5 py-2.5 hover:bg-yellow-500 hover:text-white transition-all duration-200 rounded-lg">
            + Tambah Jadwal
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{--  SEARCH BAR & FILTER --}}
    <div class="bg-white shadow-md border border-gray-200 rounded-lg p-4 mb-4">
        <form method="GET" action="{{ route('kaprodi.jadwal-semgang.index') }}" id="filterForm" class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
            {{-- Search Input --}}
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       id="searchInput"
                       value="{{ $search ?? '' }}" 
                       placeholder="Cari nama mahasiswa, NIM, dosen, perusahaan..." 
                       class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition">
                @if(!empty($search))
                    <button type="button" 
                            id="clearSearch"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-500 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
            </div>
            
            {{-- Filter Per Page --}}
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600 font-medium whitespace-nowrap">Tampilkan:</label>
                <select name="per_page" 
                        id="perPageSelect"
                        class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition cursor-pointer">
                    <option value="5" {{ ($perPage ?? 10) == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ ($perPage ?? 10) == 15 ? 'selected' : '' }}>15</option>
                    <option value="all" {{ ($perPage ?? 10) == 'all' ? 'selected' : '' }}>Semua</option>
                </select>
            </div>
        </form>
    </div>

    {{--  INFO JUMLAH DATA --}}
    <div class="flex justify-between items-center mb-3">
        <p class="text-sm text-gray-500">
            @if($perPage === 'all')
                Menampilkan <span class="font-semibold text-gray-700">{{ $jadwal->count() }}</span> data
            @else
                Menampilkan <span class="font-semibold text-gray-700">{{ $jadwal->firstItem() ?? 0 }} - {{ $jadwal->lastItem() ?? 0 }}</span> dari <span class="font-semibold text-gray-700">{{ $jadwal->total() }}</span> data
            @endif
            @if(!empty($search))
                untuk pencarian "<span class="font-semibold text-yellow-600">{{ $search }}</span>"
            @endif
        </p>
    </div>

    <div class="bg-white shadow-md border border-gray-200 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-yellow-500 text-white">
                    <tr>
                        <th class="p-4 text-center w-12">No</th>
                        <th class="p-4">Mahasiswa</th>
                        <th class="p-4">Dosen Pembimbing</th>
                        <th class="p-4">Perusahaan</th>
                        <th class="p-4">Tanggal</th>
                        <th class="p-4">Waktu</th>
                        <th class="p-4">Ruang</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center w-32">Penilaian Seminar</th>
                        <th class="p-4 text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse($jadwal as $key => $item)
                    <tr class="border-b border-gray-100 hover:bg-yellow-50 transition">
                        <td class="p-4 text-center text-gray-500 font-medium">
                            @if($perPage === 'all')
                                {{ $key + 1 }}
                            @else
                                {{ ($jadwal->currentPage() - 1) * $jadwal->perPage() + $key + 1 }}
                            @endif
                        </td>
                        <td class="p-4 font-semibold text-gray-900">{{ $item->mahasiswa->nama ?? '-' }}</td>
                        <td class="p-4">{{ $item->dosenPembimbing->dosen->nama ?? '-' }}</td>
                        <td class="p-4">{{ $item->perusahaan->nama_perusahaan ?? '-' }}</td>
                        <td class="p-4">{{ \Carbon\Carbon::parse($item->tgl_semgang)->format('d M Y') }}</td>
                        <td class="p-4">{{ $item->jam_mulai }} - {{ $item->jam_selesai }}</td>
                        <td class="p-4">{{ $item->ruang ?? '-' }}</td>
                        
                        <td class="p-4 text-center">
                            @if($item->status_semgang == 'dijadwalkan')
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">
                                    Dijadwalkan
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">
                                    Selesai
                                </span>
                            @endif
                        </td>

                        {{-- KOLOM PENILAIAN SEMINAR --}}
                        <td class="p-4 text-center">
                            @if($item->status_semgang == 'selesai' && $item->rata_rata !== null)
                                <a href="{{ route('kaprodi.jadwal-semgang.detail-nilai', $item->id_jadwal_semgang) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold hover:bg-emerald-100 hover:border-emerald-400 transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Lihat
                                </a>
                            @else
                                <span class="text-gray-400 text-sm font-medium">-</span>
                            @endif
                        </td>

                        <td class="p-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('kaprodi.jadwal-semgang.edit', $item->id_jadwal_semgang) }}" class="text-blue-600 hover:underline text-xs font-semibold">Edit</a>
                                <button type="button" onclick="openDeleteModal('{{ route('kaprodi.jadwal-semgang.destroy', $item->id_jadwal_semgang) }}')" class="text-red-600 hover:underline text-xs font-semibold cursor-pointer">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="p-10 text-center text-gray-400">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            @if(!empty($search))
                                Tidak ada data yang cocok untuk pencarian "<span class="font-semibold">{{ $search }}</span>"
                            @else
                                Belum ada jadwal seminar magang.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{--  PAGINATION --}}
        @if($perPage !== 'all' && $jadwal->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 flex flex-col sm:flex-row justify-between items-center gap-3">
            <p class="text-xs text-gray-500">
                Halaman {{ $jadwal->currentPage() }} dari {{ $jadwal->lastPage() }}
            </p>
            <div class="flex items-center gap-1">
                {{-- Previous Button --}}
                @if($jadwal->onFirstPage())
                    <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </span>
                @else
                    <a href="{{ $jadwal->previousPageUrl() }}" 
                       class="px-3 py-2 text-sm text-yellow-600 bg-white border border-yellow-300 rounded-lg hover:bg-yellow-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                @endif

                {{-- Page Numbers --}}
                @foreach($jadwal->getUrlRange(max(1, $jadwal->currentPage() - 2), min($jadwal->lastPage(), $jadwal->currentPage() + 2)) as $page => $url)
                    @if($page == $jadwal->currentPage())
                        <span class="px-3 py-2 text-sm font-bold text-white bg-yellow-500 rounded-lg">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" 
                           class="px-3 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-300 hover:text-yellow-600 transition">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                {{-- Show dots if there are more pages --}}
                @if($jadwal->currentPage() + 2 < $jadwal->lastPage())
                    <span class="px-2 py-2 text-gray-400">...</span>
                @endif
                @if($jadwal->currentPage() - 2 > 1)
                    <span class="px-2 py-2 text-gray-400">...</span>
                @endif

                {{-- Next Button --}}
                @if($jadwal->hasMorePages())
                    <a href="{{ $jadwal->nextPageUrl() }}" 
                       class="px-3 py-2 text-sm text-yellow-600 bg-white border border-yellow-300 rounded-lg hover:bg-yellow-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- MODAL POP-UP HAPUS (TRANSPARAN BLUR) -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-auto bg-black/30 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 transform transition-all">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                <i class="fas fa-trash-alt text-red-500 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Hapus Jadwal</h3>
            <p class="text-sm text-gray-500 mb-6">Yakin akan menghapus jadwal seminar ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex gap-3 justify-center">
                <button onclick="closeDeleteModal()" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-300">Batal</button>
                <form id="deleteForm" method="POST" class="inline-block">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition duration-300 shadow-md">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let searchTimeout;

    // Debounce: otomatis submit form 500ms setelah berhenti mengetik
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const val = this.value;

        // Sembunyikan tanda X saat input kosong
        const clearBtn = document.getElementById('clearSearch');
        if (clearBtn) {
            clearBtn.style.display = val.length > 0 ? 'flex' : 'none';
        }

        searchTimeout = setTimeout(() => {
            submitFilter('search', val);
        }, 500);
    });

    // Tombol X untuk clear search
    document.getElementById('clearSearch')?.addEventListener('click', function() {
        document.getElementById('searchInput').value = '';
        this.style.display = 'none';
        submitFilter('search', '');
    });

    // Auto submit saat ganti per_page
    document.getElementById('perPageSelect').addEventListener('change', function() {
        submitFilter('per_page', this.value);
    });

    // Fungsi submit filter tanpa reload full parameter, hanya update yang berubah
    function submitFilter(key, value) {
        const form = document.getElementById('filterForm');
        const params = new URLSearchParams(window.location.search);

        if (value === '' || value === null) {
            params.delete(key);
        } else {
            params.set(key, value);
        }

        // Reset ke halaman 1 saat filter berubah
        params.delete('page');

        window.location.href = '{{ route("kaprodi.jadwal-semgang.index") }}?' + params.toString();
    }

    function openDeleteModal(actionUrl) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        form.action = actionUrl;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });
</script>
@endsection