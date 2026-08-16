<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMMAGI - Tambah Berkas Magang</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-10">

    <div class="w-full max-w-2xl">
        <div class="bg-white shadow-lg p-10 rounded-lg border border-gray-200">
            <h2 class="text-center text-3xl font-bold text-gray-800 mb-8">.:: Tambah Berkas Magang ::.</h2>
            <form action="{{ route('admin_kajur.berkas-magang.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label for="nama_berkas" class="block text-sm font-medium text-gray-700 mb-2">Nama Berkas <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_berkas" id="nama_berkas" value="{{ old('nama_berkas') }}"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('nama_berkas') border-red-500 @enderror"
                            placeholder="Contoh: Surat Izin Orang Tua 2024">
                        @error('nama_berkas')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jenis_berkas" class="block text-sm font-medium text-gray-700 mb-2">Jenis Berkas <span class="text-red-500">*</span></label>
                        <select name="jenis_berkas" id="jenis_berkas"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('jenis_berkas') border-red-500 @enderror">
                            <option value="">-- Pilih Jenis Berkas --</option>
                            @foreach ($jenisBerkas as $jenis)
                                <option value="{{ $jenis }}" {{ old('jenis_berkas') == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                            @endforeach
                        </select>
                        @error('jenis_berkas')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="file" class="block text-sm font-medium text-gray-700 mb-2">File <span class="text-red-500">*</span></label>
                        <input type="file" name="file" id="file"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('file') border-red-500 @enderror"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle mr-1"></i>Format: PDF, JPG, PNG, DOC, DOCX (Maks. 2MB)</p>
                        @error('file')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" rows="3"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('deskripsi') border-red-500 @enderror"
                            placeholder="Deskripsi singkat berkas (opsional)">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <a href="{{ route('admin_kajur.berkas-magang.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition">
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