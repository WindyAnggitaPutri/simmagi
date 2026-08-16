<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMMAGI - Edit Jabatan Tendik</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-10">

    <div class="w-full max-w-2xl">

        <div class="bg-white shadow-lg p-10 rounded-lg border border-gray-200">
            <h2 class="text-center text-3xl font-bold text-gray-800 mb-8">.:: Edit Jabatan Tendik ::.</h2>
            <form action="{{ route('admin.jabatan-dosen.update', $jabatanTendik->id_jabatan_tendik) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">

                    {{-- Pilih Tendik --}}
                    <div>
                        <label for="orang" class="block text-sm font-medium text-gray-700 mb-2">
                            Tendik <span class="text-red-500">*</span>
                        </label>

                        <select name="orang" id="orang"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('orang') border-red-500 @enderror">

                            <option value="">-- Pilih Tendik --</option>

                            @foreach ($orang as $o)
                                @php
                                    $selectedValue = old('orang');

                                    if (!$selectedValue) {
                                        if ($jabatanTendik->id_dosen == $o->id && $o->type == 'dosen') {
                                            $selectedValue = 'dosen-'.$o->id;
                                        }
                                        if ($jabatanTendik->id_pegawai == $o->id && $o->type == 'pegawai') {
                                            $selectedValue = 'pegawai-'.$o->id;
                                        }
                                    }
                                @endphp

                                <option value="{{ $o->type }}-{{ $o->id }}"
                                    {{ $selectedValue == $o->type.'-'.$o->id ? 'selected' : '' }}>
                                    {{ $o->nama }} ({{ ucfirst($o->type) }})
                                </option>
                            @endforeach

                        </select>

                        @error('orang')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pilih Jabatan --}}
                    <div>
                        <label for="id_jabatan" class="block text-sm font-medium text-gray-700 mb-2">Jabatan <span class="text-red-500">*</span></label>
                        <select name="id_jabatan" id="id_jabatan" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('id_jabatan') border-red-500 @enderror">
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach ($jabatan as $j)
                                <option value="{{ $j->id_jabatan }}" {{ old('id_jabatan', $jabatanTendik->id_jabatan) == $j->id_jabatan ? 'selected' : '' }}>{{ $j->nama_jabatan }}</option>
                            @endforeach
                        </select>
                        @error('id_jabatan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- TTD Digital --}}
                    <!-- <div>
                        <label for="ttd_digital" class="block text-sm font-medium text-gray-700 mb-2">TTD Digital <span class="text-gray-400 font-normal">(Opsional)</span></label>
                        <input type="text" name="ttd_digital" id="ttd_digital" value="{{ old('ttd_digital', $jabatanTendik->ttd_digital) }}" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('ttd_digital') border-red-500 @enderror"
                            placeholder="Masukkan teks atau path TTD">
                        @error('ttd_digital')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div> -->

                </div>

                {{-- Tombol --}}
                <div class="flex justify-between mt-8">
                    <a href="{{ route('admin.jabatan-dosen.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition">
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