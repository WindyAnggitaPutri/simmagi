@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-6">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-yellow-400">Tambah Jadwal Seminar Magang</h1>
        <p class="text-gray-500 text-sm mt-1">Isi form di bawah untuk membuat jadwal baru</p>
    </div>

    <div class="bg-white shadow-md border border-gray-200 rounded-lg p-8">
        <form action="{{ route('kaprodi.jadwal-semgang.store') }}" method="POST" id="form-jadwal">
            @csrf

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Mahasiswa <span class="text-red-500">*</span></label>
                <select name="id_mahasiswa" id="select_mahasiswa" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent bg-white" required>
                    <option value="">-- Pilih Mahasiswa --</option>
                    @foreach($mahasiswa as $mhs)
                        <option value="{{ $mhs->id_mahasiswa }}" {{ old('id_mahasiswa') == $mhs->id_mahasiswa ? 'selected' : '' }}>{{ $mhs->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Dosen Pembimbing <span class="text-red-500">*</span></label>
                <select name="id_dosen_pembimbing" id="select_dospem" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent bg-white" required>
                    <option value="">-- Otomatis Terisi Saat Pilih Mahasiswa --</option>
                </select>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Perusahaan</label>
                <select name="id_perusahaan" id="select_perusahaan" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent bg-gray-100 cursor-not-allowed" readonly>
                    <option value="">-- Otomatis Terisi --</option>
                </select>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Seminar <span class="text-red-500">*</span></label>
                <input type="date" name="tgl_semgang" id="input_tgl" value="{{ old('tgl_semgang') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent" required>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jam Mulai <span class="text-red-500">*</span></label>
                    <input type="time" name="jam_mulai" id="input_jam_mulai" value="{{ old('jam_mulai') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jam Selesai</label>
                    <input type="time" name="jam_selesai" id="input_jam_selesai" value="{{ old('jam_selesai') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent">
                </div>
            </div>

            <div class="mb-8">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Ruang</label>
                <input type="text" name="ruang" id="input_ruang" value="{{ old('ruang') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent" placeholder="Contoh: Ruang A.1.1">
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2.5 px-6 rounded-lg transition duration-200 shadow-md">
                    Simpan Jadwal
                </button>
                <button type="button" id="btn-reset" class="border border-orange-400 text-orange-600 font-semibold py-2.5 px-6 rounded-lg hover:bg-orange-50 transition duration-200">
                    Reset
                </button>
                <a href="{{ route('kaprodi.jadwal-semgang.index') }}" class="border border-gray-300 text-gray-700 font-semibold py-2.5 px-6 rounded-lg hover:bg-gray-50 transition duration-200">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {

        $('#select_mahasiswa').on('change', function() {
            var idMahasiswa = $(this).val();
            var dospemSelect = $('#select_dospem');
            var perusahaanSelect = $('#select_perusahaan');

            dospemSelect.html('<option value="">Memuat...</option>');
            perusahaanSelect.html('<option value="">Memuat...</option>');

            if (idMahasiswa) {
                $.ajax({
                    url: '{{ url("/kaprodi/jadwal-seminar-magang/get-detail-mahasiswa") }}/' + idMahasiswa,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        dospemSelect.html('<option value="">-- Pilih Dosen Pembimbing --</option>');
                        if (data.dospem) {
                            dospemSelect.append('<option value="'+ data.dospem.id +'" selected>'+ data.dospem.nama +'</option>');
                        } else {
                            dospemSelect.html('<option value="">Dospem belum tersedia</option>');
                        }

                        perusahaanSelect.html('');
                        if (data.perusahaan) {
                            perusahaanSelect.append('<option value="'+ data.perusahaan.id +'" selected>'+ data.perusahaan.nama +'</option>');
                        } else {
                            perusahaanSelect.append('<option value="">Perusahaan tidak ditemukan</option>');
                        }
                    },
                    error: function() {
                        dospemSelect.html('<option value="">Gagal memuat data</option>');
                        perusahaanSelect.html('<option value="">Gagal memuat data</option>');
                    }
                });
            } else {
                dospemSelect.html('<option value="">-- Otomatis Terisi Saat Pilih Mahasiswa --</option>');
                perusahaanSelect.html('<option value="">-- Otomatis Terisi --</option>');
            }
        });

        // ========== RESET ==========
        $('#btn-reset').on('click', function() {
            document.getElementById('form-jadwal').reset();
            $('#select_dospem').html('<option value="">-- Otomatis Terisi Saat Pilih Mahasiswa --</option>');
            $('#select_perusahaan').html('<option value="">-- Otomatis Terisi --</option>');
        });
    });
</script>
@endsection