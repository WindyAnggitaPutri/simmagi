@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-6">
    <a href="{{ route('dospem.jadwal-semgang.index') }}" class="text-sm text-yellow-600 hover:underline mb-2 inline-block">&larr; Kembali ke Jadwal</a>
    <h1 class="text-2xl font-bold text-yellow-600 mb-6">Form Penilaian Seminar</h1>
    
    <div class="bg-white shadow-md rounded-lg p-6 mb-6 border border-gray-200">
        <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">Data Mahasiswa</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Nama:</span> <strong>{{ $jadwal->mahasiswa->nama ?? '-' }}</strong></div>
            <div><span class="text-gray-500">Judul:</span> <strong>{{ $jadwal->judul_laporan ?? '-' }}</strong></div>
        </div>
    </div>

    <form action="{{ route('dospem.jadwal-semgang.store-nilai', $jadwal->id_jadwal_semgang) }}" method="POST">
        @csrf @method('PUT')
        <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200">
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">Sasaran Penilaian (Range 0 - 100)</h3>
            
            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    <label class="w-3/4 text-sm font-medium text-gray-700">1. Persiapan seminar magang industri</label>
                    <input type="number" name="nilai_persiapan" min="0" max="100" step="0.01" value="{{ old('nilai_persiapan', $jadwal->nilai_persiapan) }}" class="w-1/4 border border-gray-300 rounded-lg px-3 py-2 text-center focus:ring-2 focus:ring-yellow-400" required>
                </div>
                <div class="flex items-center gap-4">
                    <label class="w-3/4 text-sm font-medium text-gray-700">2. Sikap dan penampilan</label>
                    <input type="number" name="nilai_sikap" min="0" max="100" step="0.01" value="{{ old('nilai_sikap', $jadwal->nilai_sikap) }}" class="w-1/4 border border-gray-300 rounded-lg px-3 py-2 text-center focus:ring-2 focus:ring-yellow-400" required>
                </div>
                <div class="flex items-center gap-4">
                    <label class="w-3/4 text-sm font-medium text-gray-700">3. Penguasaan materi dan objektifitas dalam menanggapi pertanyaan</label>
                    <input type="number" name="nilai_penguasaan" min="0" max="100" step="0.01" value="{{ old('nilai_penguasaan', $jadwal->nilai_penguasaan) }}" class="w-1/4 border border-gray-300 rounded-lg px-3 py-2 text-center focus:ring-2 focus:ring-yellow-400" required>
                </div>
                <div class="flex items-center gap-4">
                    <label class="w-3/4 text-sm font-medium text-gray-700">4. Kemampuan menjelaskan dan mempertahankan ide</label>
                    <input type="number" name="nilai_mempertahankan_ide" min="0" max="100" step="0.01" value="{{ old('nilai_mempertahankan_ide', $jadwal->nilai_mempertahankan_ide) }}" class="w-1/4 border border-gray-300 rounded-lg px-3 py-2 text-center focus:ring-2 focus:ring-yellow-400" required>
                </div>
                <div class="flex items-center gap-4">
                    <label class="w-3/4 text-sm font-medium text-gray-700">5. Hasil yang dicapai dari pelaksanaan magang industri</label>
                    <input type="number" name="nilai_hasil_magang" min="0" max="100" step="0.01" value="{{ old('nilai_hasil_magang', $jadwal->nilai_hasil_magang) }}" class="w-1/4 border border-gray-300 rounded-lg px-3 py-2 text-center focus:ring-2 focus:ring-yellow-400" required>
                </div>
                <div class="flex items-center gap-4">
                    <label class="w-3/4 text-sm font-medium text-gray-700">6. Penyajian penulisan laporan magang industri</label>
                    <input type="number" name="nilai_penulisan" min="0" max="100" step="0.01" value="{{ old('nilai_penulisan', $jadwal->nilai_penulisan) }}" class="w-1/4 border border-gray-300 rounded-lg px-3 py-2 text-center focus:ring-2 focus:ring-yellow-400" required>
                </div>
                
                <div class="mt-4">
                    <label class="text-sm font-medium text-gray-700">Catatan (Opsional)</label>
                    <textarea name="catatan" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-400 mt-1">{{ old('catatan', $jadwal->catatan) }}</textarea>
                </div>
            </div>

                       {{-- ✅ CHECKBOX KONFIRMASI TTD --}}
            <div class="mt-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="konfirmasi_ttd" id="konfirmasi_ttd" required class="w-5 h-5 mt-0.5 text-yellow-600 bg-gray-100 border-gray-300 rounded focus:ring-yellow-500 focus:ring-2">
                    <label for="konfirmasi_ttd" class="text-sm font-medium text-gray-700">
                        Dengan ini saya menyatakan telah mengisi penilaian dengan benar dan bertanggung jawab, serta bersedia <strong>menandatangani</strong> berkas penilaian ini secara digital.
                    </label>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-6 rounded-lg transition">Simpan & Tanda Tangan</button>
                <a href="{{ route('dospem.jadwal-semgang.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded-lg transition">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection
