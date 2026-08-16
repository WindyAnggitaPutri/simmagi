@extends('layouts.app')

@section('content')
<div class="p-3 md:p-6">
    <div class="flex justify-between items-center mb-4 md:mb-6">
        <div class="min-w-0">
           <a href="{{ route('mahasiswa.logbook.index') }}"
   class="inline-flex items-center gap-1 md:gap-2 text-yellow-500 hover:text-yellow-600 font-semibold text-xs md:text-sm mb-1 md:mb-3 transition-colors">
    <svg xmlns="http://www.w3.org/2000/svg"
         class="w-4 h-4 md:w-5 md:h-5 flex-shrink-0"
         fill="none"
         viewBox="0 0 24 24"
         stroke="currentColor"
         stroke-width="2">
        <path stroke-linecap="round"
              stroke-linejoin="round"
              d="M15 19l-7-7 7-7" />
    </svg>
    <span class="truncate sm:whitespace-normal">Kembali ke Daftar Minggu</span>
</a>
            <h1 class="text-sm md:text-2xl font-bold text-gray-800 truncate">Minggu ke-{{ $minggu }}</h1>
            <p class="text-[10px] md:text-sm text-gray-500">Kegiatan harian Anda pada minggu ini</p>
        </div>
        <div class="flex gap-3 flex-shrink-0 ml-2">
            @if(isset($isMingguDisetujui) && $isMingguDisetujui)
                <a href="{{ route('mahasiswa.logbook.rekap', $minggu) }}" 
                   class="bg-yellow-500 text-white text-[10px] px-2.5 py-1.5 md:text-sm md:px-4 md:py-2 rounded-lg hover:bg-yellow-600 transition shadow-md inline-flex items-center gap-1 md:gap-2 whitespace-nowrap">
                    <i class="fas fa-file-alt"></i> Rekap Mingguan
                </a>
            @else
                <button type="button" disabled 
                   class="bg-gray-300 text-gray-500 text-[10px] px-2.5 py-1.5 md:text-sm md:px-4 md:py-2 rounded-lg cursor-not-allowed inline-flex items-center gap-1 md:gap-2 whitespace-normal md:whitespace-nowrap" 
                   title="Rekap hanya bisa diakses jika Dosen sudah Approve Mingguan">
                    <i class="fas fa-lock flex-shrink-0"></i> Rekap Mingguan<br class="md:hidden">(Belum Diapprove)
                </button>
            @endif
        </div>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 md:p-4 mb-3 md:mb-4 rounded text-xs md:text-sm" role="alert">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter Tampilkan Data --}}
    <div class="flex items-center justify-end mb-3 md:mb-4">
        <label class="text-xs md:text-sm text-gray-500 mr-2">Tampilkan:</label>
        <select id="filterPerPage" onchange="filterRows(this.value)" 
                class="text-xs md:text-sm border border-gray-300 rounded-lg px-2 py-1 md:px-3 md:py-1.5 focus:outline-none focus:ring-2 focus:ring-yellow-400 bg-white">
            <option value="all">Semua</option>
            <option value="5">5</option>
            <option value="10">10</option>
        </select>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] divide-y divide-gray-200">
                <thead class="bg-yellow-500">
                    <tr>
                        <th class="px-3 py-2 md:px-6 md:py-3 text-left text-[10px] md:text-xs font-bold text-white uppercase tracking-wider">Tanggal</th>
                        <th class="px-3 py-2 md:px-6 md:py-3 text-left text-[10px] md:text-xs font-bold text-white uppercase tracking-wider">Waktu</th>
                        <th class="px-3 py-2 md:px-6 md:py-3 text-left text-[10px] md:text-xs font-bold text-white uppercase tracking-wider">Kegiatan</th>
                        <th class="px-3 py-2 md:px-6 md:py-3 text-center text-[10px] md:text-xs font-bold text-white uppercase tracking-wider">Status</th>
                        <th class="px-3 py-2 md:px-6 md:py-3 text-center text-[10px] md:text-xs font-bold text-white uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody id="logbookTableBody" class="bg-white divide-y divide-gray-200">
                    @foreach ($logbooks as $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                            <div class="text-xs md:text-sm font-bold text-gray-900">{{ $item->tanggal->format('l') }}</div>
                            <div class="text-[10px] md:text-xs text-gray-500">{{ $item->tanggal->format('d M Y') }}</div>
                        </td>
                        <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                            <div class="text-xs md:text-sm font-bold text-gray-900">
                                {{ $item->jam_mulai ? \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') : '-' }} 
                                - 
                                {{ $item->jam_selesai ? \Carbon\Carbon::parse($item->jam_selesai)->format('H:i') : '-' }}
                            </div>
                        </td>
                        <td class="px-3 py-2 md:px-6 md:py-4">
                            <div class="text-xs md:text-sm text-gray-900 max-w-[120px] md:max-w-xs truncate">{{ $item->kegiatan }}</div>
                            @if($item->catatan)
                                <div class="text-[10px] md:text-xs text-red-500 mt-1"><i class="fas fa-comment-dots mr-1"></i> {{ Str::limit($item->catatan, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap text-center">
                            @if($item->status == 'disetujui')
                                <span class="px-2 inline-flex text-[10px] md:text-xs leading-5 font-semibold rounded-full text-green-800">Disetujui</span>
                            @elseif($item->status == 'ditolak')
                                <span class="px-2 inline-flex text-[10px] md:text-xs leading-5 font-semibold rounded-full text-red-800">Ditolak</span>
                            @else
                                <span class="px-2 inline-flex text-[10px] md:text-xs leading-5 font-semibold rounded-full text-yellow-800">Pending</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap text-center text-xs md:text-sm font-medium space-x-1 md:space-x-2">
                            <a href="{{ route('mahasiswa.logbook.detail', $item->id_logbook) }}" class="text-yellow-600 hover:text-yellow-800 font-bold">Detail</a>
                            <a href="{{ route('mahasiswa.logbook.edit', $item->id_logbook) }}" class="text-indigo-600 hover:text-indigo-800 font-bold">Edit</a>
                            <button type="button" onclick="openDeleteModal('{{ route('mahasiswa.logbook.destroy', $item->id_logbook) }}')" class="text-red-500 hover:text-red-700 font-bold cursor-pointer">Hapus</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($logbooks->isEmpty())
        <div class="p-6 md:p-10 text-center text-gray-400 text-xs md:text-base">
            Belum ada catatan di minggu ini.
        </div>
        @endif
    </div>
</div>

<!-- MODAL POP-UP HAPUS -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-auto bg-black/30 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 transform transition-all">
        <div class="p-4 md:p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 md:h-14 md:w-14 rounded-full bg-red-100 mb-3 md:mb-4">
                <i class="fas fa-trash-alt text-red-500 text-xl md:text-2xl"></i>
            </div>
            <h3 class="text-base md:text-xl font-bold text-gray-900 mb-1 md:mb-2">Hapus Logbook</h3>
            <p class="text-xs md:text-sm text-gray-500 mb-4 md:mb-6">Yakin akan menghapus logbook ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex gap-3 justify-center">
                <button onclick="closeDeleteModal()" class="px-4 py-2 md:px-5 md:py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-300 text-xs md:text-sm">Batal</button>
                <form id="deleteForm" method="POST" class="inline-block">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2 md:px-5 md:py-2.5 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition duration-300 shadow-md text-xs md:text-sm">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function filterRows(limit) {
        const rows = document.querySelectorAll('#logbookTableBody tr');
        rows.forEach((row, index) => {
            if (limit === 'all' || index < parseInt(limit)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
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