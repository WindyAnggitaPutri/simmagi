@extends('layouts.app')

@section('title', 'SIMMAGI - Berkas Magang')
@section('page-title', 'Kelola Berkas Magang')

@section('content')
<h2 class="text-center text-4xl font-bold text-gray-800 mb-6">.:: Kelola Berkas Magang ::.</h2>

<div class="bg-white shadow-md p-4 rounded-lg">
    <div class="flex justify-between mb-4">
        <a href="{{ route('admin_kajur.berkas-magang.create') }}" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 flex items-center transition">
            <i class="fas fa-plus mr-2"></i>Tambah Berkas
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300">
            <thead class="bg-[#fffbeb]">
                <tr>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-16">No</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Nama Berkas</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Jenis Berkas</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">File</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Deskripsi</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-20">Tanggal</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-36">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($berkasMagang as $index => $b)
                <tr class="hover:bg-[#fffbeb]/30 transition">
                    <td class="border border-gray-300 p-3 text-center text-sm text-gray-900">{{ $index + 1 }}</td>
                    <td class="border border-gray-300 p-3 text-sm text-gray-900 font-medium">{{ $b->nama_berkas }}</td>
                    <td class="border border-gray-300 p-3 text-center">
                        <span class="inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-full">{{ $b->jenis_berkas }}</span>
                    </td>
                    <td class="border border-gray-300 p-3 text-center text-sm">
                        @if($b->file)
                            <a href="{{ Storage::url($b->file) }}" target="_blank" class="text-orange-600 hover:underline font-medium">
                                <i class="fas fa-file-download mr-1"></i>Lihat
                            </a>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="border border-gray-300 p-3 text-sm text-gray-600">{{ $b->deskripsi ?? '-' }}</td>
                    <td class="border border-gray-300 p-3 text-center text-xs text-gray-600">{{ $b->created_at ? \Carbon\Carbon::parse($b->created_at)->format('d M Y') : '-' }}</td>
                    <td class="border border-gray-300 p-3 text-center text-sm">
                        <a href="{{ route('admin_kajur.berkas-magang.edit', $b->id_berkas_magang) }}" class="text-orange-500 hover:underline mr-2 font-medium">Edit</a>
                        {{-- ✅ UBAH DARI FORM INLINE JADI TOMBOL MODAL --}}
                        <button type="button" 
                                onclick="openDeleteModal('{{ route('admin_kajur.berkas-magang.destroy', $b->id_berkas_magang) }}', '{{ $b->nama_berkas }}')" 
                                class="text-red-600 hover:underline font-medium cursor-pointer">Hapus</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="border border-gray-300 p-6 text-center text-gray-400">Belum ada berkas magang.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ==================== MODAL HAPUS ==================== --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-auto bg-black/30 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 transform transition-all">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                <i class="fas fa-trash-alt text-red-500 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Hapus Berkas</h3>
            <p class="text-sm text-gray-500 mb-1">Yakin ingin menghapus berkas:</p>
            <p id="deleteNamaBerkas" class="text-sm font-bold text-gray-800 mb-4">"Nama Berkas"</p>
            <p class="text-sm text-gray-500 mb-6">Tindakan ini tidak dapat dibatalkan.</p>
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
    // ========== Hapus ==========
    function openDeleteModal(actionUrl, namaBerkas) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        const namaEl = document.getElementById('deleteNamaBerkas');
        
        form.action = actionUrl;
        namaEl.textContent = '"' + namaBerkas + '"';
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });

    // ========== Keyboard: ESC ==========
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });
</script>
@endsection