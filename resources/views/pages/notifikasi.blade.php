@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-10 px-4 sm:px-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"> Notifikasi</h1>
            <p class="text-sm text-gray-500 mt-1">Semua pemberitahuan terkait akademik magang Anda</p>
        </div>
        @php $unreadCount = $notifikasis->getCollection()->where('read', 0)->count(); @endphp
        @if($unreadCount > 0)
            <button id="markAllBtn" class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold rounded-lg transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Tandai Semua Dibaca ({{ $unreadCount }})
            </button>
        @endif
    </div>

    {{-- SEARCH & PER PAGE --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <div class="relative flex-1">
            <input type="text" 
                   id="searchInput"
                   value="{{ $search ?? '' }}" 
                   placeholder="Cari notifikasi..." 
                   class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <button type="button" id="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition cursor-pointer {{ ($search ?? '') ? '' : 'hidden' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <select id="perPageSelect" class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 cursor-pointer transition">
            @foreach([5, 10, 15, 25] as $option)
                <option value="{{ $option }}" {{ ($perPage ?? 15) == $option ? 'selected' : '' }}>{{ $option }} data</option>
            @endforeach
        </select>
    </div>

    {{-- DAFTAR NOTIFIKASI --}}
    @if ($notifikasis->isEmpty())
        <div class="text-center py-16 bg-white border border-gray-200 rounded-xl">
            @if($search ?? false)
                <div class="flex flex-col items-center gap-3 text-gray-400">
                    <svg class="w-14 h-14" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span class="text-gray-500">Tidak ditemukan notifikasi untuk "<strong>{{ $search }}</strong>"</span>
                    <button onclick="clearSearch()" class="text-yellow-600 hover:underline text-sm font-semibold cursor-pointer">Lihat semua notifikasi</button>
                </div>
            @else
                <span class="text-5xl mb-3 block"></span>
                <span class="text-gray-500">Belum ada notifikasi.</span>
            @endif
        </div>
    @else
        @php
            $grouped = $notifikasis->getCollection()->groupBy(function ($n) {
                $created = \Carbon\Carbon::parse($n->created_at);
                if ($created->isToday()) return 'Hari Ini';
                if ($created->isYesterday()) return 'Kemarin';
                return 'Lebih Lama';
            });
        @endphp

        @foreach ($grouped as $label => $items)
            {{-- Label Grup --}}
            <div class="flex items-center gap-3 mt-6 mb-3 first:mt-0">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ $label }}</span>
                <span class="flex-1 border-t border-gray-200"></span>
                <span class="text-xs text-gray-400">{{ $items->count() }}</span>
            </div>

            {{-- Card Notifikasi --}}
            <div class="space-y-2">
                @foreach ($items as $notif)
                    <a href="{{ route('notifikasi.baca', $notif->id_notifikasi) }}" 
                       class="block rounded-lg border transition {{ $notif->read == 0 
                            ? 'border-l-4 border-l-yellow-500 bg-yellow-50/60 hover:bg-yellow-50 border-gray-200' 
                            : 'border-l-4 border-l-transparent bg-white hover:bg-gray-50 border-gray-200' }}">
                        <div class="flex items-start gap-3 px-4 py-3.5">
                            
                            {{-- Icon --}}
                            <div class="text-xl mt-0.5 flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full {{ $notif->read == 0 ? 'bg-yellow-100' : 'bg-gray-100' }}">
                                {{ $notif->icon ?? '' }}
                            </div>

                            {{-- Konten --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-semibold text-gray-800 leading-snug {{ $notif->read == 0 ? '' : 'font-medium text-gray-600' }}">
                                        {{ $notif->judul }}
                                    </p>
                                    @if($notif->read == 0)
                                        <span class="w-2.5 h-2.5 bg-yellow-500 rounded-full flex-shrink-0 mt-1.5"></span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 mt-0.5 leading-relaxed line-clamp-2">
                                    {{ $notif->pesan }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1.5">
                                    {{ \Carbon\Carbon::parse($notif->created_at)->locale('id')->translatedFormat('d F Y, H:i') }}
                                </p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endforeach

        {{-- PAGINATION --}}
        @if($notifikasis->hasPages())
            <div class="mt-8 flex flex-col sm:flex-row justify-between items-center gap-3">
                <p class="text-sm text-gray-500">
                    Menampilkan <span class="font-semibold text-gray-700">{{ $notifikasis->firstItem() }}</span> - <span class="font-semibold text-gray-700">{{ $notifikasis->lastItem() }}</span> dari <span class="font-semibold text-gray-700">{{ $notifikasis->total() }}</span>
                </p>
                <div class="flex items-center gap-1">
                    @if($notifikasis->onFirstPage())
                        <span class="px-3 py-1.5 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">‹</span>
                    @else
                        <a href="{{ $notifikasis->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">‹</a>
                    @endif
                    @php
                        $start = max(1, $notifikasis->currentPage() - 2);
                        $end   = min($notifikasis->lastPage(), $notifikasis->currentPage() + 2);
                    @endphp
                    @if($start > 1)
                        <a href="{{ $notifikasis->url(1) }}" class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">1</a>
                        @if($start > 2)<span class="px-2 text-gray-400 text-sm">...</span>@endif
                    @endif
                    @for($page = $start; $page <= $end; $page++)
                        @if($page == $notifikasis->currentPage())
                            <span class="px-3 py-1.5 text-sm font-bold text-white bg-yellow-500 rounded-lg">{{ $page }}</span>
                        @else
                            <a href="{{ $notifikasis->url($page) }}" class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">{{ $page }}</a>
                        @endif
                    @endfor
                    @if($end < $notifikasis->lastPage())
                        @if($end < $notifikasis->lastPage() - 1)<span class="px-2 text-gray-400 text-sm">...</span>@endif
                        <a href="{{ $notifikasis->url($notifikasis->lastPage()) }}" class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">{{ $notifikasis->lastPage() }}</a>
                    @endif
                    @if($notifikasis->hasMorePages())
                        <a href="{{ $notifikasis->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition">›</a>
                    @else
                        <span class="px-3 py-1.5 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">›</span>
                    @endif
                </div>
            </div>
        @endif
    @endif
</div>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<script>
    // ========== SEARCH ==========
    let searchTimer = null;
    document.getElementById('searchInput').addEventListener('input', function() {
        const value = this.value.trim();
        document.getElementById('clearSearch').classList.toggle('hidden', value.length === 0);

        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            const url = new URL(window.location.href);
            if (value.length > 0) {
                url.searchParams.set('search', value);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }, 500);
    });

    document.getElementById('clearSearch').addEventListener('click', clearSearch);
    function clearSearch() {
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // ========== PER PAGE ==========
    document.getElementById('perPageSelect').addEventListener('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', this.value);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    });

    // ========== MARK ALL AS READ ==========
    document.getElementById('markAllBtn')?.addEventListener('click', function() {
        const btn = this;
        const csrfToken = '{{ csrf_token() }}';

        fetch('{{ route("notifikasi.readAll") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    });
</script>
@endsection