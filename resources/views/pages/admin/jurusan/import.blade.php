@extends('layouts.admin')

@section('title', 'SIMMAGI - Import Jurusan')
@section('page-title', 'Import Data Jurusan')

@section('content')
<h2 class="text-center text-4xl font-bold text-gray-800 mb-6">.:: Import Data Jurusan ::.</h2>

<div class="max-w-2xl mx-auto bg-white shadow-md p-6 rounded-lg">
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <h3 class="font-bold text-blue-800 mb-2"><i class="fas fa-info-circle mr-2"></i>Petunjuk Import</h3>
        <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
            <li>Download template terlebih dahulu, isi data sesuai format</li>
            <li>Kolom <strong>kode_jurusan</strong> harus unik (tidak boleh sama dengan yang sudah ada)</li>
            <li>Kolom <strong>nama_jurusan</strong> harus unik (tidak boleh sama dengan yang sudah ada)</li>
        </ul>
    </div>

    <!-- Download Template -->
    <div class="mb-6">
        <a href="{{ route('admin.jurusan.template') }}" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 inline-flex items-center transition">
            <i class="fas fa-download mr-2"></i>Download Template Excel
        </a>
    </div>

    <hr class="my-6">

    <!-- Upload Form -->
    <form action="{{ route('admin.jurusan.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Pilih File Excel</label>
            <input type="file" name="file_excel" accept=".xlsx,.xls,.csv" required
                   class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500">
            <p class="text-xs text-gray-500 mt-1">Format: .xlsx, .xls, .csv (Maks 5MB)</p>
        </div>

        <div class="flex space-x-3">
            <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 flex items-center transition">
                <i class="fas fa-file-import mr-2"></i>Import Data
            </button>
            <a href="{{ route('admin.jurusan.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 flex items-center transition">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </form>
</div>

<input type="hidden" id="success-message" value="{{ session('success') ?? '' }}">
<input type="hidden" id="error-message" value="{{ session('error') ?? '' }}">

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const successMsg = document.getElementById('success-message').value;
    if (successMsg) {
        Swal.fire({ icon: 'success', title: 'Sukses!', text: successMsg, timer: 3000, showConfirmButton: true, confirmButtonText: 'OK' });
    }
    const errorMsg = document.getElementById('error-message').value;
    if (errorMsg) {
        Swal.fire({ icon: 'error', title: 'Gagal!', text: errorMsg, showConfirmButton: true });
    }

    @if(session('import_errors') && count(session('import_errors')) > 0)
        const importErrors = @json(session('import_errors'));

        let errorHtml = '<div class="max-h-96 overflow-y-auto"><table class="w-full text-sm">';
        errorHtml += '<thead class="bg-red-100"><tr><th class="p-2 text-left">Baris</th><th class="p-2 text-left">Kode Jurusan</th><th class="p-2 text-left">Nama Jurusan</th><th class="p-2 text-left">Error</th></tr></thead><tbody>';

        importErrors.forEach(function(err) {
            errorHtml += '<tr class="border-b">';
            errorHtml += '<td class="p-2 font-mono">' + err.row + '</td>';
            errorHtml += '<td class="p-2 font-mono">' + err.kode_jurusan + '</td>';
            errorHtml += '<td class="p-2">' + err.nama_jurusan + '</td>';
            errorHtml += '<td class="p-2 text-red-600"><ul class="list-disc list-inside">';
            err.errors.forEach(function(e) {
                errorHtml += '<li>' + e + '</li>';
            });
            errorHtml += '</ul></td></tr>';
        });

        errorHtml += '</tbody></table></div>';

        Swal.fire({
            title: 'Detail Error Import',
            html: errorHtml,
            icon: 'warning',
            width: '700px',
            confirmButtonText: 'Tutup'
        });
    @endif
</script>
@endpush
@endsection