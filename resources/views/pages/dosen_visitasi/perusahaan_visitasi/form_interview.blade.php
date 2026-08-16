@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-10 px-6">
    
    {{-- TOMBOL KEMBALI --}}
    <div class="mb-4">
        <a href="{{ route('dosen-visitasi.personal-interview') }}" class="text-yellow-600 hover:text-yellow-800 font-semibold flex items-center gap-1 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
        
        {{-- Header Form --}}
        <div class="bg-yellow-500 text-white p-6 text-center">
            <h2 class="text-2xl font-bold uppercase tracking-wider">Personal Interview Visitasi Magang</h2>
            <p class="mt-1 text-yellow-100 font-medium"> {{ $perusahaan->nama_perusahaan ?? '-' }}</p>
        </div>

        {{-- Step Indicators --}}
        <div class="flex border-b border-gray-200 bg-gray-50">
            <button type="button" id="tab-step-1" onclick="switchStep(1)" 
                    class="flex-1 py-4 text-center font-bold text-sm uppercase tracking-wider transition border-b-4 border-transparent text-gray-400 hover:text-gray-600">
                Form 1 (Penerima & Peserta)
            </button>
            <button type="button" id="tab-step-2" onclick="switchStep(2)" 
                    class="flex-1 py-4 text-center font-bold text-sm uppercase tracking-wider transition border-b-4 border-yellow-500 text-yellow-700">
                Form 2 (Perusahaan & K3)
            </button>
        </div>

        {{-- Notifikasi --}}
        @if(session('success'))
            <div class="mx-6 mt-6 bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mx-6 mt-6 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ============================================ --}}
        {{-- FORM A (Step 1) --}}
        {{-- ============================================ --}}
        <form id="form-step-1" action="{{ route('dosen-visitasi.interview-store-a', $perusahaan->id_perusahaan) }}" method="POST" style="display: none;">
            @csrf
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Penerima <span class="text-red-500">*</span></label>
                        <input type="text" name="penerima" value="{{ old('penerima') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" placeholder="Nama penerima di perusahaan" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Jabatan Penerima <span class="text-red-500">*</span></label>
                        <input type="text" name="jabatan_penerima" value="{{ old('jabatan_penerima') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" placeholder="Jabatan penerima" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Bagaimana menurut saudara, sikap/perilaku dari peserta magang selama menjalani magang?<span class="text-red-500">*</span></label>
                    <textarea name="sikap_perilaku" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('sikap_perilaku') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Bagaimana menurut saudara, kedisiplinan dan motivasi dari peserta magang?<span class="text-red-500">*</span></label>
                    <textarea name="kedisiplinan_motivasi" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('kedisiplinan_motivasi') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Apakah ada persoalan-persoalan dan kekurangan dari peserta dalam menjalani magang?<span class="text-red-500">*</span></label>
                    <textarea name="kekurangan_peserta" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('kekurangan_peserta') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Bagaimana menurut saudara, peran serta dari peserta magang membantu atau tidak?<span class="text-red-500">*</span></label>
                    <textarea name="peran_peserta" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('peran_peserta') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Menurut saudara, bagaimana kemampuan beradaptasi dan komunikasi dari peserta magang?<span class="text-red-500">*</span></label>
                    <textarea name="adaptasi_komunikasi" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('adaptasi_komunikasi') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Apakah kemampuan dan kompetensi peserta sudah memenuhi syarat dari perusahaan?<span class="text-red-500">*</span></label>
                    <textarea name="kompetensi_kesesuaian" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('kompetensi_kesesuaian') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Dimanakah penempatan peserta magang dan apa tugas-tugas yang dilakukan oleh peserta selama magang di perusahaan?<span class="text-red-500">*</span></label>
                    <textarea name="penempatan_dan_tugas" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('penempatan_dan_tugas') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Apakah saudara sudah puas dengan hasil kerja dari peserta magang?<span class="text-red-500">*</span></label>
                    <textarea name="kepuasan_perusahaan" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('kepuasan_perusahaan') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kira-kira apa saja yang dapat menjadi saran dan masukan dari perusahaan terkait pelaksanaan magang?<span class="text-red-500">*</span></label>
                    <textarea name="saran_masukan" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('saran_masukan') }}</textarea>
                </div>
            </div>

            <div class="bg-gray-50 px-8 py-4 border-t flex justify-end">
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-md transition flex items-center gap-2">
                    Selanjutnya <span class="text-xl">→</span>
                </button>
            </div>
        </form>

        {{-- ============================================ --}}
        {{-- FORM B (Step 2) --}}
        {{-- ============================================ --}}
        <form id="form-step-2" action="{{ route('dosen-visitasi.interview-store-b', $perusahaan->id_perusahaan) }}" method="POST">
            @csrf
            <div class="p-8 space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Skala perusahaan? (kecil, sedang, besar dan regional, nasional, internasional)<span class="text-red-500">*</span></label>
                    <textarea name="skala_perusahaan" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('skala_perusahaan') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Bidang usaha/produksi dari perusahaan?<span class="text-red-500">*</span></label>
                    <textarea name="bidang_usaha" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('bidang_usaha') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Bagaimana kondisi lingkungan kerja di perusahaan tersebut?<span class="text-red-500">*</span></label>
                    <textarea name="lingkungan_kerja" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('lingkungan_kerja') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Bagaimana sistem pembimbingan (mentoring) yang diterapkan kepada peserta magang?<span class="text-red-500">*</span></label>
                    <textarea name="sistem_pembimbingan" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('sistem_pembimbingan') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Apakah pekerjaan yang dilakukan peserta sesuai dengan kurikulum di kampus?<span class="text-red-500">*</span></label>
                    <textarea name="kesesuaian_kurikulum" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('kesesuaian_kurikulum') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Penerapan K3 dari perusahaan?<span class="text-red-500">*</span></label>
                    <textarea name="penerapan_k3" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('penerapan_k3') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Fasilitas untuk peserta magang dari perusahaan?<span class="text-red-500">*</span></label>
                    <textarea name="fasilitas_magang" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('fasilitas_magang') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggapan dan respon dari perusahaan?<span class="text-red-500">*</span></label>
                    <textarea name="tanggapan_perusahaan" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('tanggapan_perusahaan') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kelebihan Perusahaan?<span class="text-red-500">*</span></label>
                    <textarea name="kelebihan_perusahaan" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('kelebihan_perusahaan') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kekurangan Perusahaan? <span class="text-red-500">*</span></label>
                    <textarea name="kekurangan_perusahaan" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('kekurangan_perusahaan') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Rekomendasi/Tidak untuk magang selanjutnya?<span class="text-red-500">*</span></label>
                    <textarea name="rekomendasi" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" required>{{ old('rekomendasi') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
                    <textarea name="catatan" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500">{{ old('catatan') }}</textarea>
                </div>
            </div>

            <div class="bg-gray-50 px-8 py-4 border-t flex justify-between items-center">
                <button type="button" onclick="switchStep(1)" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded-md transition flex items-center gap-2">
                    <span class="text-xl">←</span> Kembali
                </button>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-md transition flex items-center gap-2">
                    Selesai & Kirim
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    function switchStep(step) {
        document.getElementById('form-step-1').style.display = 'none';
        document.getElementById('form-step-2').style.display = 'none';

        if (step === 1) document.getElementById('form-step-1').style.display = 'block';
        else if (step === 2) document.getElementById('form-step-2').style.display = 'block';

        [1, 2].forEach(t => {
            const tab = document.getElementById('tab-step-' + t);
            tab.classList.remove('border-yellow-500', 'text-yellow-700');
            tab.classList.add('border-transparent', 'text-gray-400');
        });

        const activeTab = document.getElementById('tab-step-' + step);
        activeTab.classList.add('border-yellow-500', 'text-yellow-700');
        activeTab.classList.remove('border-transparent', 'text-gray-400');

        window.scrollTo(0, 0);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const stepFromUrl = urlParams.get('step');

        if (stepFromUrl) {
            switchStep(parseInt(stepFromUrl));
        } else {
            @if($errors->any() && old('_token'))
                @if(old('skala_perusahaan'))
                    switchStep(2);
                @elseif(old('penerima'))
                    switchStep(1);
                @endif
            @elseif(isset($existingA) && $existingA)
                switchStep(2);
            @else
                switchStep(1);
            @endif
        }
    });
</script>
@endsection