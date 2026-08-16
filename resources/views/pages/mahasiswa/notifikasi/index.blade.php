@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <a href="javascript:history.back()" class="text-gray-400 hover:text-gray-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Notifikasi</h1>
        </div>
        <form action="{{ route('mahasiswa.notifikasi.readAll') }}" method="POST" id="form-read-all">
            @csrf
            <button type="submit" class="text-sm text-yellow-600 hover:text-yellow-700 font-medium transition">
                ✅ Tandai Semua Dibaca
            </button>
        </form>
    </div>

    {{-- Daftar Notifikasi --}}
    @forelse($notifikasis as $notif)
        <a href="{{ route('mahasiswa.notifikasi.baca', $notif->id_notifikasi) }}" 
           class="block mb-2 p-4 rounded-lg border transition hover:shadow-md {{ $notif->read ? 'bg-white border-gray-200' : 'bg-yellow-50 border-yellow-200 shadow-sm' }}">
            <div class="flex items-start gap-4">
                <span class="text-3xl flex-shrink-0">{{ $notif->icon }}</span>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-gray-800 {{ $notif->read ? '' : 'text-gray-900' }}">{{ $notif->judul }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ $notif->pesan }}</p>
                    <p class="text-xs text-gray-400 mt-2">{{ $notif->waktuLalu }}</p>
                </div>
                @if(!$notif->read)
                    <span class="w-3 h-3 bg-yellow-500 rounded-full mt-2 flex-shrink-0 shadow-sm"></span>
                @endif
            </div>
        </a>
    @empty
        <div class="text-center py-20">
            <span class="text-7xl">🔔</span>
            <p class="text-gray-400 text-lg mt-4">Belum ada notifikasi</p>
            <p class="text-gray-300 text-sm mt-1">Notifikasi akan muncul saat ada aktivitas terkait akun Anda</p>
        </div>
    @endforelse

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $notifikasis->links() }}
    </div>

</div>
@endsection