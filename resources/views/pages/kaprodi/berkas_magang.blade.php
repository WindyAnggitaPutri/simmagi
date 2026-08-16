@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Berkas Magang</h1>
            <p class="text-gray-500 text-sm mt-1">Kelola dokumen persyaratan magang mahasiswa</p>
        </div>
        
        <button onclick="openModal()" 
           class="border-2 border-yellow-500 text-yellow-600 font-bold text-sm px-5 py-2.5 hover:bg-yellow-500 hover:text-white transition-all duration-200">
            + Upload Berkas
        </button>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-md border border-gray-200 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-yellow-500 text-white">
                    <tr>
                        <th class="p-4 text-center w-12">No</th>
                        <th class="p-4">Nama Berkas</th>
                        <th class="p-4">Jenis Berkas</th>
                        <th class="p-4">File</th>
                        <th class="p-4 text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @if($berkasMagang->isEmpty())
                        <tr>
                            <td colspan="5" class="p-10 text-center text-gray-400">Belum ada berkas yang diupload.</td>
                        </tr>
                    @else
                        @foreach ($berkasMagang as $item)
                            <tr class="border-b border-gray-100 hover:bg-yellow-50 transition">
                                <td class="p-4 text-center text-gray-500 font-medium">{{ $loop->iteration }}</td>
                                <td class="p-4 font-semibold text-gray-900">{{ $item->nama_berkas }}</td>
                                <td class="p-4">
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-1 rounded">
                                        {{ $item->jenis_berkas }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <a href="{{ asset('storage/' . $item->file) }}" target="_blank" class="text-blue-600 hover:underline text-xs font-semibold">
                                          Lihat File
                                    </a>
                                </td>
                                <td class="p-4 text-center flex justify-center gap-2">
                                    <button onclick="editModal({{ $item->id_berkas_magang }}, '{{ $item->nama_berkas }}', '{{ $item->jenis_berkas }}', '{{ $item->deskripsi }}')" class="text-blue-600 hover:underline text-xs font-semibold">
                                        Edit
                                    </button>
                                    <form action="{{ route('kaprodi.berkas-magang.destroy', $item->id_berkas_magang) }}" method="POST" onsubmit="return confirm('Hapus berkas ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-xs font-semibold">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ==================== MODAL CREATE / EDIT ==================== --}}
<div id="modalBerkas" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full">
        
        {{-- HEADER MODAL --}}
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 id="modalTitle" class="text-lg font-bold text-gray-800">Upload Berkas Baru</h3>
            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        {{-- BODY MODAL --}}
        <form id="formBerkas" action="" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="_method" value="POST">
            <div class="p-6 space-y-4">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Berkas <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_berkas" name="nama_berkas" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500"
                           placeholder="Contoh: Surat Izin Orang Tua - Andi">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Berkas <span class="text-red-500">*</span></label>
                    <select name="jenis_berkas" id="jenis_berkas" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <option value="" disabled selected>-- Pilih Jenis Berkas --</option>
                        @foreach ($jenisBerkas as $jenis)
                            <option value="{{ $jenis }}">{{ $jenis }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload File <span class="text-red-500">*</span></label>
                    <input type="file" name="file" id="fileInput"
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                           class="w-full border border-gray-300 text-sm text-gray-500
                                  file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold
                                  file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">
                    <p class="text-xs text-gray-400 mt-1">Format: PDF, JPG, PNG, DOC, DOCX (Max 2MB)</p>
                    <p id="fileInfo" class="text-xs text-blue-600 mt-1 hidden"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <textarea name="deskripsi" id="deskripsi" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500"
                              placeholder="Keterangan tambahan..."></textarea>
                </div>

            </div>

            {{-- FOOTER MODAL --}}
            <div class="p-6 border-t border-gray-200 flex gap-3">
                <button type="button" onclick="closeModal()" class="flex-1 border border-gray-300 text-gray-700 font-semibold py-2.5 px-4 rounded-lg hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit" id="btnSubmit" class="flex-1 bg-yellow-500 text-white font-semibold py-2.5 px-4 rounded-lg hover:bg-yellow-600 transition">
                    Simpan
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    // Buka Modal untuk CREATE
    function openModal() {
        document.getElementById('modalTitle').textContent = 'Upload Berkas Baru';
        document.getElementById('formBerkas').action = '{{ route("kaprodi.berkas-magang.store") }}';
        document.getElementById('_method').value = 'POST';   // POST untuk create
        document.getElementById('formBerkas').reset();
        document.getElementById('fileInput').setAttribute('required', 'true');
        document.getElementById('fileInfo').classList.add('hidden');
        document.getElementById('btnSubmit').textContent = 'Simpan';
        document.getElementById('modalBerkas').classList.remove('hidden');
    }

    // Buka Modal untuk EDIT
    function editModal(id, nama, jenis, deskripsi) {
        document.getElementById('modalTitle').textContent = 'Edit Berkas';
        document.getElementById('formBerkas').action = '{{ route("kaprodi.berkas-magang.update", ["id_berkas_magang" => "ID_PLACEHOLDER"]) }}'.replace('ID_PLACEHOLDER', id);
        document.getElementById('_method').value = 'PUT';    // PUT untuk update ← INI YANG FIX ERROR
        
        document.getElementById('nama_berkas').value = nama;
        document.getElementById('jenis_berkas').value = jenis;
        document.getElementById('deskripsi').value = deskripsi;
        
        // File tidak wajib saat edit
        document.getElementById('fileInput').removeAttribute('required');
        document.getElementById('fileInfo').textContent = 'Kosongkan jika tidak ingin mengubah file.';
        document.getElementById('fileInfo').classList.remove('hidden');
        
        document.getElementById('btnSubmit').textContent = 'Update';
        document.getElementById('modalBerkas').classList.remove('hidden');
    }

    // Tutup Modal
    function closeModal() {
        document.getElementById('modalBerkas').classList.add('hidden');
    }

    // Klik luar modal untuk tutup
    document.getElementById('modalBerkas').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
</script>
@endsection