@extends('layouts.app')

@section('content')

{{-- TOMBOL ATAS --}}
<div class="no-print max-w-4xl mx-auto py-6 px-6">
    <div class="flex justify-between items-center flex-wrap gap-3">
        <a href="{{ route('kaprodi.pengajuan_magang.index') }}" class="text-yellow-600 font-semibold text-sm hover:underline">
            ← Kembali ke Daftar Pengajuan
        </a>
        <div class="flex gap-3 items-center flex-wrap">
            @if ($suratBalasan)
                @php $filePath = asset('storage/surat_balasan/' . $suratBalasan); @endphp
                <!-- <a href="{{ $filePath }}" target="_blank" class="border border-gray-300 text-sm px-4 py-2 hover:bg-gray-100 transition">🔗 Buka di Tab Baru</a> -->
                <a href="{{ $filePath }}" download="{{ $suratBalasan }}" class="bg-blue-600 text-white text-sm px-4 py-2 hover:bg-blue-700 transition"> Download</a>
                <!-- <button onclick="window.print()" class="bg-amber-600 text-white text-sm px-4 py-2 hover:bg-amber-700 transition">🖨️ Cetak</button> -->
                
                @if($statusSuratBalasan == 'menunggu_verifikasi')
                    <button onclick="openModal()" class="bg-green-600 text-white text-sm px-4 py-2 hover:bg-green-700 transition font-semibold"> Verifikasi Mahasiswa</button>
                @elseif($statusSuratBalasan == 'sudah_diverifikasi')
                    <button onclick="openModal()" class="bg-yellow-500 text-white text-sm px-4 py-2 hover:bg-yellow-600 transition font-semibold"> Edit Verifikasi</button>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- NOTIFIKASI --}}
@if (session('success'))
    <div class="no-print max-w-4xl mx-auto px-6 mb-4">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    </div>
@endif

{{-- AREA KONTEN --}}
<div class="max-w-4xl mx-auto px-6 mb-10">
    @if ($suratBalasan)
        @php $extension = pathinfo($suratBalasan, PATHINFO_EXTENSION); @endphp

       {{-- BADGE STATUS --}}
        <div class="mb-4 flex items-center gap-2 no-print">
            @if($statusSuratBalasan == 'menunggu_verifikasi')
                <span class="bg-yellow-100 text-yellow-700 text-xs font-bold px-3 py-1.5 rounded border border-yellow-300"> Menunggu Verifikasi Anda</span>
            @elseif($statusSuratBalasan == 'sudah_diverifikasi')
                <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1.5 rounded border border-green-300"> Sudah Diverifikasi (Klik Edit untuk ubah)</span>
            @endif
        </div>

        {{-- TAMPILAN SURAT --}}
        <div class="surat-container px-16 py-12" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.6; color: #000; text-align: justify; border: 1px solid #000; background: #fff;">
            <h3 style="text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 15px;">SURAT BALASAN</h3>
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 11pt;">
                <div><div style="font-weight: bold;">Nama Perusahaan</div><div>{{ $pengajuan->perusahaan->nama_perusahaan ?? '-' }}</div></div>
                <div style="text-align: right;">
                    <div style="font-weight: bold;">Nama Mahasiswa</div>
                    <div>
                        @foreach ($pengajuan->mahasiswa as $pm) {{ $pm->mahasiswa->nama ?? '-' }} ({{ $pm->mahasiswa->npm ?? '-' }}) @if(!$loop->last)<br>@endif @endforeach
                    </div>
                </div>
            </div>
            <div style="border-top: 1px solid #000; margin-bottom: 20px;"></div>
            @if (strtolower($extension) == 'pdf')
                <embed src="{{ $filePath }}#toolbar=0&navpanes=0&scrollbar=0" type="application/pdf" style="width: 100%; height: 900px; border: none;">
            @else
                <img src="{{ $filePath }}" alt="Surat Balasan" style="width: 100%; height: auto; display: block;">
            @endif
        </div>
    @else
        <div class="border p-6 bg-white text-center py-16">
            <span class="bg-red-100 text-red-700 text-xs font-bold px-3 py-1.5 rounded border border-red-300"> Belum Ada Surat Balasan</span>
            <p class="text-gray-500 text-sm mt-2">Mahasiswa belum mengupload surat balasan dari perusahaan.</p>
        </div>
    @endif
</div>

{{-- ==================== MODAL CHECKBOX MAHASISWA ==================== --}}
<div id="modalKonfirmasi" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full">
        
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-800">
                {{ $statusSuratBalasan == 'sudah_diverifikasi' ? ' Edit Verifikasi Mahasiswa' : ' Verifikasi Penerimaan Mahasiswa' }}
            </h3>
            <p class="text-sm text-gray-500 mt-1">Centang mahasiswa yang <b>diterima</b> oleh perusahaan berdasarkan surat balasan. Yang tidak dicentang berarti ditolak perusahaan.</p>
        </div>

        <form action="{{ route('kaprodi.pengajuan_magang.proses_verifikasi_sb', $pengajuan->id_pengajuan) }}" method="POST">
            @csrf

            <div class="p-6 space-y-5">
                {{-- DAFTAR CHECKBOX MAHASISWA --}}
                <div>
                    @foreach ($pengajuan->mahasiswa as $pm)
                        <label class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer mb-2">
                            <input type="checkbox" name="mahasiswa_diterima[]" value="{{ $pm->id_mahasiswa }}" 
                                   class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500"
                                   {{ $pm->status_surat_balasan == 'diterima' ? 'checked' : '' }}>
                            <div>
                                <div class="font-semibold text-gray-800">{{ $pm->mahasiswa->nama ?? '-' }}</div>
                                <div class="text-xs text-gray-500">NPM: {{ $pm->mahasiswa->npm ?? '-' }}</div>
                                @if($pm->status_surat_balasan == 'diterima')
                                    <span class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded mt-1 inline-block">Diterima</span>
                                @elseif($pm->status_surat_balasan == 'ditolak')
                                    <span class="text-[10px] bg-red-100 text-red-700 px-2 py-0.5 rounded mt-1 inline-block">Ditolak</span>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>

                {{-- CATATAN --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                    <textarea name="catatan_verifikasi" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm" placeholder="Contoh: Andin tidak dicentang karena di surat tidak tertera nama nya..."></textarea>
                </div>
            </div>

            <div class="p-6 border-t border-gray-200 flex gap-3">
                <button type="button" onclick="closeModal()" class="flex-1 border border-gray-300 text-gray-700 font-semibold py-2.5 px-4 rounded-lg hover:bg-gray-50">Batal</button>
                <button type="submit" class="flex-1 {{ $statusSuratBalasan == 'sudah_diverifikasi' ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-600 hover:bg-green-700' }} text-white font-semibold py-2.5 px-4 rounded-lg">
                    {{ $statusSuratBalasan == 'sudah_diverifikasi' ? 'Update Verifikasi' : 'Simpan Verifikasi' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

<script>
    function openModal() { document.getElementById('modalKonfirmasi').classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
    function closeModal() { document.getElementById('modalKonfirmasi').classList.add('hidden'); document.body.style.overflow = ''; }
    document.getElementById('modalKonfirmasi').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
</script>

<style>
    .surat-container { border: 1px solid #000 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    @media print {
        nav, footer, .no-print, .sidebar, .navbar, header { display: none !important; }
        main { padding: 0 !important; margin: 0 !important; }
        .surat-container { border: 1px solid #000 !important; box-shadow: none !important; padding: 20mm 25mm !important; max-width: 100% !important; margin: 0 !important; }
        @page { size: A4; margin: 0; }
    }
</style>