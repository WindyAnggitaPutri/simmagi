@extends('layouts.app')

@section('title', 'Token Penilaian Perusahaan')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-6xl">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Token Penilaian Perusahaan</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola token untuk penilaian dan angket perusahaan</p>
        </div>
        <a href="{{ route('kaprodi.token-perusahaan.create') }}" 
           class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white px-5 py-2.5 rounded-lg font-medium transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Token Baru
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span class="text-green-700 text-sm">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('warning'))
    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg flex items-center gap-3">
        <svg class="w-5 h-5 text-yellow-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span class="text-yellow-700 text-sm">{{ session('warning') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3">
        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span class="text-red-700 text-sm">{{ session('error') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
        <form method="GET" action="{{ route('kaprodi.token-perusahaan.index') }}" class="flex flex-col sm:flex-row gap-3 items-end">
            <div class="flex-1 w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Perusahaan</label>
                <input type="text" name="search" value="{{ $search }}" 
                       placeholder="Ketik nama perusahaan..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 text-sm">
            </div>
            <div class="w-full sm:w-40">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tampilkan</label>
                <select name="per_page" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 text-sm">
                    <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5 per halaman</option>
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 per halaman</option>
                    <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15 per halaman</option>
                    <option value="all" {{ $perPage == 'all' ? 'selected' : '' }}>Semua</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-lg transition-colors">
                    Cari
                </button>
                @if($search)
                <a href="{{ route('kaprodi.token-perusahaan.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Daftar Token Perusahaan</h2>
        </div>

        @if($tokens->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Perusahaan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Token</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Expired</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php $no = ($paginatedIds->currentPage() - 1) * $paginatedIds->perPage() + 1; @endphp
                    @foreach($tokens as $tokenGroup)
                        @php
                            $firstToken = $tokenGroup->first();
                            $perusahaan = $firstToken->perusahaan;
                            $groupCount = $tokenGroup->count();
                        @endphp
                        @foreach($tokenGroup as $i => $token)
                        <tr class="hover:bg-gray-50 transition-colors">
                            @if($i == 0)
                            <td class="px-4 py-3 text-sm text-gray-600" rowspan="{{ $groupCount }}">
                                {{ $no++ }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 align-middle" rowspan="{{ $groupCount }}">
                                {{ $perusahaan->nama_perusahaan ?? '-' }}
                            </td>
                            @endif
                            <td class="px-4 py-3">
                                <code class="bg-gray-200 text-gray-800 px-2 py-0.5 rounded text-xs font-mono">{{ $token->token }}</code>
                            </td>
                            <td class="px-4 py-3">
                                @if($token->type == 'angket')
                                    <span class="px-2 py-0.5 bg-yellow-50 text-yellow-700 text-xs font-medium rounded-full">Angket</span>
                                @else
                                    <span class="px-2 py-0.5 bg-purple-50 text-purple-700 text-xs font-medium rounded-full">Penilaian</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $token->mahasiswa->nama ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($token->is_used)
                                    <span class="px-2 py-0.5 bg-green-50 text-green-700 text-xs font-medium rounded-full">Sudah Digunakan</span>
                                @elseif($token->expired_at < now())
                                    <span class="px-2 py-0.5 bg-red-50 text-red-700 text-xs font-medium rounded-full">Expired</span>
                                @else
                                    <span class="px-2 py-0.5 bg-yellow-50 text-yellow-700 text-xs font-medium rounded-full">Aktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $token->expired_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @if(!$token->is_used && $token->expired_at >= now())
                                        <button type="button" onclick="copyLink('{{ $token->token }}')" 
                                                class="px-2 py-1 bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs font-medium rounded transition-colors">
                                            Salin Link
                                        </button>
                                    @endif
                                    @if($token->is_used)
                                        <a href="{{ route('kaprodi.token-perusahaan.detail-nilai', $token->id_token) }}" 
                                           class="px-2 py-1 bg-green-50 hover:bg-green-100 text-green-600 text-xs font-medium rounded transition-colors">
                                            Lihat Nilai
                                        </a>
                                    @endif
                                    <form action="{{ route('kaprodi.token-perusahaan.regenerate', $token->id_token) }}" method="POST" 
                                          onsubmit="return confirm('Regenerate token ini?')">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 bg-orange-50 hover:bg-orange-100 text-orange-600 text-xs font-medium rounded transition-colors">
                                            Regenerate
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex justify-center">
                {{ $paginatedIds->appends(request()->query())->links() }}
            </div>
        </div>

        @else
        <div class="py-16 px-6 text-center">
            <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-1">Belum Ada Token</h3>
            <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">
                @if($search)
                    Tidak ditemukan token untuk pencarian "{{ $search }}"
                @else
                    Belum ada token yang dibuat. Buat token pertama untuk perusahaan magang.
                @endif
            </p>
            <a href="{{ route('kaprodi.token-perusahaan.create') }}" 
               class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white px-5 py-2.5 rounded-lg font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Token Baru
            </a>
        </div>
        @endif
    </div>

</div>

<script>
function copyLink(token) {
    var link = window.location.origin + '/perusahaan/form/' + token;
    navigator.clipboard.writeText(link).then(function() {
        var t = document.createElement('div');
        t.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;background:#dcfce7;color:#166534;padding:12px 20px;border-radius:10px;font-size:14px;font-family:system-ui;box-shadow:0 4px 15px rgba(0,0,0,.08);border:1px solid #bbf7d0;opacity:0;transform:translateY(-10px);transition:all .3s ease;display:flex;align-items:center;gap:8px';
        t.innerHTML = '<svg width="18" height="18" fill="none" stroke="#16a34a" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Link berhasil disalin';
        document.body.appendChild(t);
        requestAnimationFrame(function() { t.style.opacity = '1'; t.style.transform = 'translateY(0)'; });
        setTimeout(function() { t.style.opacity = '0'; t.style.transform = 'translateY(-10px)'; setTimeout(function() { t.remove(); }, 300); }, 2500);
    });
}
</script>
@endsection