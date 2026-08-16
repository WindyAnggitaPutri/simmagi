@extends('layouts.app')

@section('title', 'Dosen Pembimbing - BAAK')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Data Dosen Pembimbing</h1>
        <p class="text-gray-500 text-sm mt-1">Daftar seluruh dosen pembimbing magang</p>
    </div>

    {{-- Search & Filter --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('baak.dosen-pembimbing.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" name="search" value="{{ $search ?? '' }}" 
                    placeholder="Nama dosen, NPM, perusahaan..." 
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>
            <div class="w-32">
                <label class="block text-sm font-medium text-gray-700 mb-1">Per Halaman</label>
                <select name="per_page" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                    <option value="5" {{ ($perPage ?? 10) == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ ($perPage ?? 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>
            <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-orange-600 transition">
                Cari
            </button>
            <a href="{{ route('baak.dosen-pembimbing.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-300 transition">
                Reset
            </a>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">No</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Dosen Pembimbing</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Mahasiswa</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Perusahaan</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">SK</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($dospemList as $index => $dp)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">{{ $dospemList->firstItem() + $index }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $dp->dosen->nama ?? '-' }}</p>
                            <p class="text-xs text-gray-400">NIDN: {{ $dp->dosen->nidn ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $dp->mahasiswa->nama ?? '-' }}</p>
                            <p class="text-xs text-gray-400">NPM: {{ $dp->mahasiswa->npm ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $dp->pengajuanMagang->perusahaan->nama_perusahaan ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($dp->file_sk)
                                <a href="{{ route('baak.dosen-pembimbing.lihat-sk', $dp->id_dosen_pembimbing) }}" 
                                   class="text-orange-600 hover:text-orange-700 text-xs font-medium">
                                    📄 Lihat SK
                                </a>
                            @else
                                <span class="text-gray-400 text-xs">Belum ada</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('baak.dosen-pembimbing.detail', $dp->id_dosen_pembimbing) }}" 
                               class="bg-blue-50 text-blue-600 px-3 py-1.5 rounded text-xs font-medium hover:bg-blue-100 transition">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                            <p class="text-4xl mb-2">📋</p>
                            <p>Belum ada data dosen pembimbing</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($dospemList->hasPages())
        <div class="px-4 py-3 border-t flex justify-between items-center">
            <p class="text-sm text-gray-500">
                Menampilkan {{ $dospemList->firstItem() }} - {{ $dospemList->lastItem() }} dari {{ $dospemList->total() }} data
            </p>
            {{ $dospemList->links() }}
        </div>
        @endif
    </div>

</div>
@endsection