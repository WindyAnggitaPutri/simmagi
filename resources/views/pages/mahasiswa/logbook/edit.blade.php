@extends('layouts.app')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="javascript:history.back()" class="text-yellow-600 hover:text-yellow-700 font-semibold text-sm mb-2 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
        <h1 class="text-2xl font-bold text-gray-800">Edit Logbook</h1>
        <p class="text-gray-500 text-sm">Perbarui catatan kegiatan magang Anda</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-yellow-500">
        @if($logbook->status == 'disetujui')
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p class="font-bold">Logbook Sudah Disetujui</p>
                <p>Perubahan pada logbook yang sudah disetujui akan mengubah status kembali menjadi <b>Pending</b> untuk direview ulang oleh Dosen Pembimbing.</p>
            </div>
        @endif

        @if($logbook->catatan)
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                <p class="text-sm text-red-700 font-bold">Catatan Dosen:</p>
                <p class="text-sm text-red-600">{{ $logbook->catatan }}</p>
            </div>
        @endif

        <form action="{{ route('mahasiswa.logbook.update', $logbook->id_logbook) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="tanggal">Tanggal Kegiatan</label>
                    <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', $logbook->tanggal->format('Y-m-d')) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Minggu Ke-</label>
                    <input type="number" value="{{ $logbook->minggu_ke }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-400 bg-gray-100 cursor-not-allowed" disabled>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
    <div>
        <label class="block text-gray-700 text-sm font-bold mb-2" for="jam_mulai">Jam Mulai</label>
        <input type="time" name="jam_mulai" id="jam_mulai" 
            value="{{ old('jam_mulai', $logbook->jam_mulai ? \Carbon\Carbon::parse($logbook->jam_mulai)->format('H:i') : '') }}" 
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
    </div>
    <div>
        <label class="block text-gray-700 text-sm font-bold mb-2" for="jam_selesai">Jam Selesai</label>
        <input type="time" name="jam_selesai" id="jam_selesai" 
            value="{{ old('jam_selesai', $logbook->jam_selesai ? \Carbon\Carbon::parse($logbook->jam_selesai)->format('H:i') : '') }}" 
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
    </div>
</div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="kegiatan">Deskripsi Kegiatan</label>
                <textarea name="kegiatan" id="kegiatan" rows="6" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" required>{{ old('kegiatan', $logbook->kegiatan) }}</textarea>
            </div>
             <!-- TAMBAHKAN INPUT FOTO INI -->
    <div class="mb-6">
        <!-- <label class="block text-gray-700 text-sm font-bold mb-2" for="foto">Foto Kegiatan (Opsional)</label> -->
        
<!-- TAMBAHKAN INPUT FOTO INI -->
<div class="mb-6">
    <label class="block text-gray-700 text-sm font-bold mb-2" for="foto">Foto Kegiatan (Opsional)</label>
    
    <!-- Tampilkan foto lama jika ada -->
    @if($logbook->foto)
        <div class="mb-2">
            <img src="{{ asset('storage/' . $logbook->foto) }}" class="w-32 h-32 object-cover rounded-lg border shadow-sm">
            <p class="text-xs text-gray-500 mt-1">Foto saat ini</p>
        </div>
    @endif

    <input type="file" name="foto" id="foto" accept="image/png, image/jpeg, image/jpg" 
    class="shadow appearance-none border rounded w-full text-sm text-gray-500 
           file:mr-4 file:py-2 file:px-4 
           file:rounded-l-lg file:border-0 
           file:text-sm file:font-semibold 
           file:bg-gray-200 file:text-gray-700 
           hover:file:bg-gray-300 
           focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent
           @error('foto') border-red-500 @enderror">
           
    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah foto. Format: JPG, PNG. Maks: 2MB</p>
    @error('foto') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
</div>

            <div class="flex items-center justify-end">
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-lg shadow-md transition duration-300">
                    <i class="fas fa-save mr-2"></i> Update Logbook
                </button>
            </div>
        </form>
    </div>
</div>
@endsection