<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMMAGI - Edit Perusahaan</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4 sm:p-10">

    <div class="w-full max-w-2xl">

        <div class="bg-white shadow-lg p-6 sm:p-10 rounded-lg border border-gray-200">
            <h2 class="text-center text-2xl sm:text-3xl font-bold text-gray-800 mb-2">.:: Edit Perusahaan ::.</h2>
            <p class="text-center text-sm text-gray-500 mb-8">Ubah data perusahaan beserta koordinat lokasinya</p>

            <form action="{{ route('admin.perusahaan.update', $perusahaan->id_perusahaan) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-5">

                    {{-- Nama Perusahaan --}}
                    <div>
                        <label for="nama_perusahaan" class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Perusahaan <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                         
                            <input type="text" name="nama_perusahaan" id="nama_perusahaan" 
                                value="{{ old('nama_perusahaan', $perusahaan->nama_perusahaan) }}" 
                                class="w-full border border-gray-300 rounded-lg p-3 pl-2 focus:ring-orange-500 focus:border-orange-500 outline-none @error('nama_perusahaan') border-red-500 @enderror"
                                placeholder="Contoh: PT. Telkom Indonesia">
                        </div>
                        @error('nama_perusahaan')
                            <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Alamat --}}
                    <div>
                        <label for="alamat" class="block text-sm font-medium text-gray-700 mb-2">
                            Alamat Lengkap <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                           
                            <textarea name="alamat" id="alamat" rows="3" 
                                class="w-full border border-gray-300 rounded-lg p-3 pl-2 focus:ring-orange-500 focus:border-orange-500 outline-none @error('alamat') border-red-500 @enderror"
                                placeholder="Masukkan alamat lengkap perusahaan">{{ old('alamat', $perusahaan->alamat) }}</textarea>
                        </div>
                        @error('alamat')
                            <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Latitude & Longitude --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="latitude" class="block text-sm font-medium text-gray-700 mb-2">
                                Latitude <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <!-- <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-mono">LAT</span> -->
                                <input type="number" name="latitude" id="latitude" step="any"
                                    value="{{ old('latitude', $perusahaan->latitude) }}" 
                                    class="w-full border border-gray-300 rounded-lg p-3 pl-2 focus:ring-orange-500 focus:border-orange-500 outline-none font-mono text-sm @error('latitude') border-red-500 @enderror"
                                    placeholder="-7.71806211">
                            </div>
                            @error('latitude')
                                <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                            <p class="text-gray-400 text-xs mt-1">Rentang: -90 sampai 90</p>
                        </div>

                        <div>
                            <label for="longitude" class="block text-sm font-medium text-gray-700 mb-2">
                                Longitude <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <!-- <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-mono">LNG</span> -->
                                <input type="number" name="longitude" id="longitude" step="any"
                                    value="{{ old('longitude', $perusahaan->longitude) }}" 
                                    class="w-full border border-gray-300 rounded-lg p-3 pl-2 focus:ring-orange-500 focus:border-orange-500 outline-none font-mono text-sm @error('longitude') border-red-500 @enderror"
                                    placeholder="109.01805414">
                            </div>
                            @error('longitude')
                                <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                            <p class="text-gray-400 text-xs mt-1">Rentang: -180 sampai 180</p>
                        </div>
                    </div>

                    {{-- Info koordinat --}}
                    <div class="border border-blue-200 rounded-lg p-3">
                        <p class="text-xs">
                            
                            <strong>Cara mendapatkan koordinat:</strong> Buka Google Maps → Klik lokasi → Koordinat muncul di kolom pencarian
                        </p>
                    </div>

                </div>

                {{-- Tombol Aksi --}}
                <div class="flex justify-between mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.perusahaan.index') }}" 
                        class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali</span>
                    </a>
                    <button type="submit" 
                        class="bg-orange-500 text-white px-8 py-2.5 rounded-lg hover:bg-orange-600 transition flex items-center gap-2">
                        <i class="fas fa-edit"></i>
                        <span>Update</span>
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">SIMMAGI - Sistem Informasi Monitoring Magang Industri</p>
    </div>

</body>
</html>