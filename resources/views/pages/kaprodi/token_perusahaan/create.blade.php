@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('kaprodi.token-perusahaan.index') }}" class="inline-flex items-center gap-2 text-yellow-500 hover:text-yellow-700 font-medium text-sm mb-4 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Kembali
    </a>
    <h1 class="text-2xl font-bold text-gray-800">Generate Token Penilaian</h1>
    <p class="text-sm text-gray-500 mt-1">Sistem akan otomatis membuat 1 Link Angket & N Link Penilaian Mahasiswa sesuai jumlah magang</p>
</div>

<div class="max-w-3xl space-y-6">

    {{-- GENERATE TOKEN --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-base font-bold text-gray-800 mb-4">Generate Token</h3>
        <form method="POST" action="{{ route('kaprodi.token-perusahaan.store') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Perusahaan Tujuan Magang <span class="text-red-500">*</span></label>
                <select name="id_perusahaan" id="selectPerusahaan" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                    <option value="">Pilih Perusahaan</option>
                    @forelse($perusahaans as $p)
                    <option value="{{ $p->id_perusahaan }}" data-tokens="{{ $p->token_count ?? 0 }}">
                        {{ $p->nama_perusahaan }}
                        @if($p->token_count > 0)
                        <span class="text-xs text-green-600 ml-1">({{ $p->token_count }} token aktif)</span>
                        @endif
                    </option>
                    @empty
                    <option value="" disabled>Tidak ada perusahaan tersedia</option>
                    @endforelse
                </select>
                @if($perusahaans->isEmpty())
                <p class="text-xs text-red-500 mt-1">
                     Tidak ada perusahaan yang memiliki mahasiswa magang dari prodi Anda. 
                    Pastikan data pengajuan magang sudah benar.
                </p>
                @endif
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Batas Waktu Pengisian (Expired At) <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="expired_at" id="expiredAt" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
            </div>

            {{-- Info Preview --}}
            <div id="previewInfo" class="mb-4 hidden bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-800 font-medium"> Preview yang akan dibuat:</p>
                <div id="previewContent" class="text-sm text-blue-700 mt-1"></div>
            </div>

            <div class="flex gap-3">
                <button type="submit" id="btnGenerate" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Generate Semua Token
                </button>
                
                {{-- Opsi Force Regenerate --}}
                <button type="button" id="btnForceRegenerate" class="hidden bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-lg font-medium transition text-sm">
                     Force Regenerate Semua
                </button>
            </div>
        </form>
        
        {{-- Form terpisah untuk force regenerate --}}
        <form id="formForceRegenerate" method="POST" action="{{ route('kaprodi.token-perusahaan.force-regenerate') }}" class="hidden">
            @csrf
            <input type="hidden" name="id_perusahaan" id="forceIdPerusahaan">
            <input type="hidden" name="expired_at" id="forceExpiredAt">
        </form>
    </div>

</div>

{{-- Modal Konfirmasi Force Regenerate --}}
<div id="modalForceRegenerate" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl p-6 max-w-md mx-4 shadow-xl">
        <h3 class="text-lg font-bold text-gray-800 mb-2"> Konfirmasi Force Regenerate</h3>
        <p class="text-sm text-gray-600 mb-4">
            Semua token yang <strong>belum digunakan</strong> akan dihapus dan diganti dengan yang baru. 
            Token yang sudah diisi tidak akan terpengaruh.
        </p>
        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                Batal
            </button>
            <button type="button" onclick="confirmForceRegenerate()" class="px-4 py-2 text-sm text-white bg-orange-500 hover:bg-orange-600 rounded-lg">
                Ya, Regenerate
            </button>
        </div>
    </div>
</div>

<script>
// Set minimum datetime to now
const now = new Date();
now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
document.getElementById('expiredAt').min = now.toISOString().slice(0, 16);

// Preview saat pilih perusahaan
document.getElementById('selectPerusahaan').addEventListener('change', function() {
    const preview = document.getElementById('previewInfo');
    const content = document.getElementById('previewContent');
    const btnForce = document.getElementById('btnForceRegenerate');
    const tokenCount = this.options[this.selectedIndex].dataset.tokens || 0;
    
    if (this.value) {
        preview.classList.remove('hidden');
        content.innerHTML = `
            <ul class="list-disc ml-4 mt-1 space-y-1">
                <li>1 Token Angket Perusahaan</li>
                <li>N Token Penilaian Mahasiswa</li>
            </ul>
            <p class="mt-2 text-xs text-orange-600">* Token yang sudah ada & aktif akan dilewati</p>
        `;
        
        if (parseInt(tokenCount) > 0) {
            btnForce.classList.remove('hidden');
        } else {
            btnForce.classList.add('hidden');
        }
    } else {
        preview.classList.add('hidden');
        btnForce.classList.add('hidden');
    }
});

// Force Regenerate flow
document.getElementById('btnForceRegenerate').addEventListener('click', function() {
    document.getElementById('modalForceRegenerate').classList.remove('hidden');
    document.getElementById('modalForceRegenerate').classList.add('flex');
});

function closeModal() {
    document.getElementById('modalForceRegenerate').classList.add('hidden');
    document.getElementById('modalForceRegenerate').classList.remove('flex');
}

function confirmForceRegenerate() {
    const idPerusahaan = document.getElementById('selectPerusahaan').value;
    const expiredAt = document.getElementById('expiredAt').value;
    
    document.getElementById('forceIdPerusahaan').value = idPerusahaan;
    document.getElementById('forceExpiredAt').value = expiredAt;
    document.getElementById('formForceRegenerate').submit();
}
</script>
@endsection