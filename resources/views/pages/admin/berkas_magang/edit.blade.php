
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMMAGI - Edit Berkas Magang</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-10">

<div class="w-full max-w-2xl">
    <div class="bg-white shadow-md p-6 rounded-lg border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-6 border-b pb-3">Form Edit Berkas</h3>
        
        <form action="{{ route('admin.berkas-magang.update', $berkas->id_berkas_magang) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Berkas <span class="text-red-500">*</span></label>
                <input type="text" name="nama_berkas" value="{{ old('nama_berkas', $berkas->nama_berkas) }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
                @error('nama_berkas')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Berkas <span class="text-red-500">*</span></label>
                <select name="jenis_berkas" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
                    <option value="" disabled>-- Pilih Jenis Berkas --</option>
                    @foreach ($jenisBerkas as $jenis)
                        <option value="{{ $jenis }}" {{ old('jenis_berkas', $berkas->jenis_berkas) == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                    @endforeach
                </select>
                @error('jenis_berkas')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ganti File <span class="text-gray-400 font-normal text-xs">(Kosongkan jika tidak ingin mengubah)</span></label>
                <input type="file" name="file"
                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                       class="w-full border border-gray-300 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100 rounded-lg cursor-pointer">
                <p class="text-xs text-gray-400 mt-1">Format: PDF, JPG, PNG, DOC, DOCX (Max 2MB)</p>
                {{-- Info File Lama --}}
                <p class="text-xs text-blue-600 mt-1">
                    File saat ini: <a href="{{ asset('storage/' . $berkas->file) }}" target="_blank" class="underline font-semibold"> {{ basename($berkas->file) }}</a>
                </p>
                @error('file')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi <span class="text-gray-400 font-normal">(Opsional)</span></label>
                <textarea name="deskripsi" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"
                          placeholder="Keterangan tambahan...">{{ old('deskripsi', $berkas->deskripsi) }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('admin.berkas-magang.index') }}" class="bg-gray-200 text-gray-700 font-bold py-2 px-6 rounded-lg hover:bg-gray-300 transition text-sm">
                    Batal
                </a>
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-lg transition text-sm shadow-sm">
                    Update Berkas
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>