@extends('layouts.app')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="javascript:history.back()" class="text-yellow-600 hover:text-yellow-700 font-semibold text-sm mb-2 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
        <h1 class="text-2xl font-bold text-gray-800">Tambah Logbook Baru</h1>
        <p class="text-gray-500 text-sm">Isi kegiatan magang Anda hari ini</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-yellow-500">
        
        {{-- Alert Error dari Controller --}}
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('mahasiswa.logbook.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                <div>
    <label class="block text-gray-700 text-sm font-bold mb-2" for="tanggal">Tanggal Kegiatan</label>
    <input type="date" name="tanggal" id="tanggal" 
           value="{{ old('tanggal', $prefillData['tanggal'] ?? date('Y-m-d')) }}" 
           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent @error('tanggal') border-red-500 @enderror" required>
    @error('tanggal') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
</div>
                
                {{-- Input Minggu Ke- (Dikunci/Readonly) --}}
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="minggu_ke">Minggu Ke-</label>
                    <input type="number" name="minggu_ke" id="minggu_ke" value="{{ old('minggu_ke', $nextMinggu ?? 1) }}" 
                           readonly 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-500 bg-gray-100 cursor-not-allowed leading-tight focus:outline-none @error('minggu_ke') border-red-500 @enderror" required>
                    <p class="text-xs text-gray-400 mt-1 italic">Minggu diatur otomatis oleh sistem.</p>
                    @error('minggu_ke') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- SETELAH INPUT TANGGAL & MINGGU KE -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                <div>
    <label class="block text-gray-700 text-sm font-bold mb-2" for="jam_mulai">Jam Mulai</label>
    <input type="time" name="jam_mulai" id="jam_mulai" 
           value="{{ old('jam_mulai', $prefillData['jam_mulai'] ?? '') }}" 
           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('jam_mulai') border-red-500 @enderror" required>
    @error('jam_mulai') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
</div>
               <div>
    <label class="block text-gray-700 text-sm font-bold mb-2" for="jam_selesai">Jam Selesai</label>
    <input type="time" name="jam_selesai" id="jam_selesai" 
           value="{{ old('jam_selesai', $prefillData['jam_selesai'] ?? '') }}" 
           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('jam_selesai') border-red-500 @enderror" required>
    @error('jam_selesai') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
</div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="kegiatan">Deskripsi Kegiatan</label>
                <textarea name="kegiatan" id="kegiatan" rows="6" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent @error('kegiatan') border-red-500 @enderror" required placeholder="Tuliskan kegiatan magang Anda secara detail...">{{ old('kegiatan') }}</textarea>
                @error('kegiatan') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- TAMBAHKAN INPUT FOTO INI -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="foto">Foto Kegiatan</label>
                
                <input type="file" name="foto" id="foto" required accept="image/png, image/jpeg, image/jpg" 
                class="shadow appearance-none border rounded w-full text-sm text-gray-500 
                       file:mr-4 file:py-2 file:px-4 
                       file:rounded-l-lg file:border-0 
                       file:text-sm file:font-semibold 
                       file:bg-gray-200 file:text-gray-700 
                       hover:file:bg-gray-300 
                       focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent
                       @error('foto') border-red-500 @enderror">
                       
                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG. Maks: 2MB</p>
                @error('foto') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- CHECKBOX TANDA TANGAN DIGITAL -->
            <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="checkbox" name="ttd_setuju" id="ttd_setuju" class="mt-1 w-5 h-5 text-yellow-500 border-gray-300 rounded focus:ring-yellow-500" required>
                    <div>
                        <span class="text-gray-700 text-sm font-semibold">Setuju untuk menandatangani logbook ini dengan Tanda Tangan Digital</span>
                        <p class="text-xs text-gray-500 mt-1">Atas nama: <span class="font-bold text-gray-700">{{ $mahasiswa->nama }}</span></p>
                    </div>
                </label>
            </div>

            {{-- Tombol Submit & Notif Penuh --}}
            @if(isset($isFull) && $isFull)
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Minggu Ke-{{ $nextMinggu }} Sudah Penuh!</p>
                    <p>Anda sudah mengisi 7 hari logbook untuk minggu ini. Silakan menunggu Dosen Pembimbing melakukan approve mingguan sebelum Anda bisa melanjutkan ke minggu berikutnya.</p>
                </div>
                <div class="flex items-center justify-end">
                    <button type="button" disabled class="bg-gray-400 text-white font-bold py-2 px-6 rounded-lg shadow-md cursor-not-allowed">
                        <i class="fas fa-ban mr-2"></i> Simpan Logbook
                    </button>
                </div>
            @else
                <div class="flex items-center justify-end">
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-lg shadow-md transition duration-300 focus:outline-none focus:shadow-outline">
                        <i class="fas fa-save mr-2"></i> Simpan Logbook
                    </button>
                </div>
            @endif

        </form>
    </div>
</div>
@endsection