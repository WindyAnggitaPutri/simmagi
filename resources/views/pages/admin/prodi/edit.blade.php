<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMMAGI - Edit Prodi</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-10">

    <div class="w-full max-w-2xl">

        <div class="bg-white shadow-lg p-10 rounded-lg border border-gray-200">
            <h2 class="text-center text-3xl font-bold text-gray-800 mb-8">.:: Edit Program Studi ::.</h2>
            <form action="{{ route('admin.prodi.update', $prodi->id_prodi) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">

                    {{-- Dropdown Jurusan --}}
                    <div>
                        <label for="id_jurusan" class="block text-sm font-medium text-gray-700 mb-2">Jurusan <span class="text-red-500">*</span></label>
                        <select name="id_jurusan" id="id_jurusan" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('id_jurusan') border-red-500 @enderror">
                            <option value="">-- Pilih Jurusan --</option>
                            @foreach ($jurusan as $j)
                                <option value="{{ $j->id_jurusan }}" {{ old('id_jurusan', $prodi->id_jurusan) == $j->id_jurusan ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                            @endforeach
                        </select>
                        @error('id_jurusan')
                            <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kode Prodi --}}
                    <div>
                        <label for="kode_prodi" class="block text-sm font-medium text-gray-700 mb-2">Kode Prodi <span class="text-red-500">*</span></label>
                        <input type="text" name="kode_prodi" id="kode_prodi" value="{{ old('kode_prodi', $prodi->kode_prodi) }}" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('kode_prodi') border-red-500 @enderror"
                            placeholder="Contoh: TI" maxlength="30">
                        <p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle mr-1"></i>Kode prodi harus unik dan tidak boleh sama dengan yang sudah terdaftar</p>
                        @error('kode_prodi')
                            <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nama Prodi --}}
                    <div>
                        <label for="nama_prodi" class="block text-sm font-medium text-gray-700 mb-2">Nama Program Studi <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_prodi" id="nama_prodi" value="{{ old('nama_prodi', $prodi->nama_prodi) }}" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('nama_prodi') border-red-500 @enderror"
                            placeholder="Contoh: Teknik Informatika" maxlength="50">
                        <p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle mr-1"></i>Nama prodi harus unik dan tidak boleh sama dengan yang sudah terdaftar</p>
                        @error('nama_prodi')
                            <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Dropdown Jenjang --}}
                    <div>
                        <label for="jenjang" class="block text-sm font-medium text-gray-700 mb-2">Jenjang <span class="text-red-500">*</span></label>
                        <select name="jenjang" id="jenjang" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('jenjang') border-red-500 @enderror">
                            <option value="">-- Pilih Jenjang --</option>
                            <option value="D3" {{ old('jenjang', $prodi->jenjang) == 'D3' ? 'selected' : '' }}>D3</option>
                            <option value="D4" {{ old('jenjang', $prodi->jenjang) == 'D4' ? 'selected' : '' }}>D4</option>
                        </select>
                        @error('jenjang')
                            <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Semester Magang --}}
                    <div>
                        <label for="semester_magang" class="block text-sm font-medium text-gray-700 mb-2">Semester Magang <span class="text-red-500">*</span></label>
                        <input type="number" name="semester_magang" id="semester_magang" 
                            value="{{ old('semester_magang', $prodi->semester_magang) }}" min="1" max="8"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('semester_magang') border-red-500 @enderror"
                            placeholder="Contoh: 5">
                        <p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle mr-1"></i>Masukkan semester dimana mahasiswa menjalani magang (angka 1-8)</p>
                        @error('semester_magang')
                            <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Semester Pengajuan Magang --}}
                    <div>
                        <label for="semester_pengajuan_magang" class="block text-sm font-medium text-gray-700 mb-2">Semester Pengajuan Magang <span class="text-red-500">*</span></label>
                        <input type="number" name="semester_pengajuan_magang" id="semester_pengajuan_magang" 
                            value="{{ old('semester_pengajuan_magang', $prodi->semester_pengajuan_magang) }}" min="1" max="8"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('semester_pengajuan_magang') border-red-500 @enderror"
                            placeholder="Contoh: 4">
                        <p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle mr-1"></i>Semester yang diizinkan untuk pengajuan magang (angka 1-8)</p>
                        @error('semester_pengajuan_magang')
                            <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- Tombol --}}
                <div class="flex justify-between mt-8">
                    <a href="{{ route('admin.prodi.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    <button type="submit" class="bg-orange-500 text-white px-8 py-2.5 rounded-lg hover:bg-orange-600 transition">
                        <i class="fas fa-edit mr-2"></i>Update
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">SIMMAGI - Sistem Informasi Monitoring Magang Industri</p>
    </div>

</body>
</html>