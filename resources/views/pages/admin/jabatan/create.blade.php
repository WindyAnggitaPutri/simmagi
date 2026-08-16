<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMMAGI - Tambah Jabatan</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-10">

    <div class="w-full max-w-2xl">

        <div class="bg-white shadow-lg p-10 rounded-lg border border-gray-200">
            <h2 class="text-center text-3xl font-bold text-gray-800 mb-8">.:: Tambah Jabatan ::.</h2>
            <form action="{{ route('admin.jabatan.store') }}" method="POST">
                @csrf

                <div class="space-y-6">

                    {{-- Kode Jabatan (Auto-generated, DISABLED) --}}
                    <div>
                        <label for="kode_jabatan" class="block text-sm font-medium text-gray-700 mb-2">Kode Jabatan <span class="text-red-500">*</span></label>
                        <input type="text" id="kode_jabatan" value="{{ $nextKodeJabatan }}" 
                            class="w-full border border-gray-300 rounded-lg p-3 bg-gray-100 text-gray-500 cursor-not-allowed"
                            disabled>
                        <p class="text-xs text-gray-400 mt-1"><i class="fas fa-info-circle mr-1"></i>Kode jabatan dibuat otomatis</p>
                    </div>

                    {{-- Nama Jabatan --}}
                    <div>
                        <label for="nama_jabatan" class="block text-sm font-medium text-gray-700 mb-2">Nama Jabatan <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_jabatan" id="nama_jabatan" value="{{ old('nama_jabatan') }}" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('nama_jabatan') border-red-500 @enderror"
                            placeholder="Contoh: Bagian Akademik dan Kemahasiswa" autofocus>
                        @error('nama_jabatan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- Tombol --}}
                <div class="flex justify-between mt-8">
                    <a href="{{ route('admin.jabatan.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    <button type="submit" class="bg-orange-500 text-white px-8 py-2.5 rounded-lg hover:bg-orange-600 transition">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">SIMMAGI - Sistem Informasi Monitoring Magang Industri</p>
    </div>

</body>
</html>