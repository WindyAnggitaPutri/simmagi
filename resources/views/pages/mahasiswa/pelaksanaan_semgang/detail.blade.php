@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-6">
    <div class="mb-8">
        <a href="{{ route('mahasiswa.pelaksanaan-seminar.index') }}" class="text-sm text-yellow-600 hover:underline mb-2 inline-block">&larr; Kembali ke Jadwal</a>
        <h1 class="text-2xl font-bold text-yellow-600">Detail & Pengisian Seminar</h1>
        <p class="text-gray-500 text-sm mt-1">Lengkapi judul laporan dan ruang seminar Anda</p>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-yellow-50 border border-yellow-300 text-yellow-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-md border border-gray-200 rounded-lg p-8">
        <form action="{{ route('mahasiswa.pelaksanaan-seminar.update', $jadwal->id_jadwal_semgang) }}" method="POST">
            @csrf @method('PUT')

            {{-- INFO STATIS --}}
            <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Informasi Jadwal (Tidak dapat diubah)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Dosen Pembimbing</label>
                    <input type="text" value="{{ $jadwal->dosenPembimbing->dosen->nama ?? '-' }}" class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-gray-600" readonly>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Perusahaan Magang</label>
                    <input type="text" value="{{ $jadwal->perusahaan->nama_perusahaan ?? '-' }}" class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-gray-600" readonly>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Seminar</label>
                    <input type="date" value="{{ $jadwal->tgl_semgang }}" class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-gray-600 cursor-not-allowed" readonly>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Waktu</label>
                    <input type="text" value="{{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}" class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-gray-600 cursor-not-allowed" readonly>
                </div>
            </div>

            {{-- FORM YANG BISA DIISI MAHASISWA --}}
            <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Form Pengisian Mahasiswa</h3>
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Judul Laporan <span class="text-red-500">*</span></label>
                <input type="text" name="judul_laporan" value="{{ old('judul_laporan', $jadwal->judul_laporan) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent" placeholder="Masukkan judul laporan magang Anda" required>
            </div>

            <div class="mb-8">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Ruang Seminar <span class="text-red-500">*</span></label>
                <input type="text" name="ruang" value="{{ old('ruang', $jadwal->ruang) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent" placeholder="Contoh: Ruang A.1.1 / Online via Zoom" required>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2.5 px-6 rounded-lg transition duration-200 shadow-md">
                    Simpan Perubahan
                </button>
                <a href="{{ route('mahasiswa.pelaksanaan-seminar.index') }}" class="border border-gray-300 text-gray-700 font-bold py-2.5 px-6 rounded-lg hover:bg-gray-50 transition duration-200">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection