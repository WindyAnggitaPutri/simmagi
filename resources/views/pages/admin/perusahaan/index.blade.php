@extends('layouts.admin') {{-- Extends layout admin yang sudah ada --}}

@section('title', 'SIMMAGI - Kelola Perusahaan') {{-- Judul tab browser --}}
@section('page-title', 'Data Perusahaan') {{-- Judul halaman di layout --}}

@section('content') {{-- Mulai section content --}}
<h2 class="text-center text-4xl font-bold text-gray-800 mb-6">.:: Kelola Perusahaan ::.</h2> {{-- Judul utama halaman --}}

<div class="bg-white shadow-md p-4 rounded-lg"> {{-- Container utama --}}

    {{-- =============================== --}}
    {{-- BARIS TOOLBAR: Tombol + Filter + Search --}}
    {{-- =============================== --}}
    <div class="flex justify-between mb-4">
        <div class="flex items-center space-x-4"> {{-- Bagian kiri: tombol dan rows per page --}}
            
            {{-- Tombol Tambah Perusahaan baru --}}
            <a href="{{ route('admin.perusahaan.create') }}" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 flex items-center transition">
                <i class="fas fa-plus mr-2"></i>Tambah
            </a>
            
            {{-- Dropdown jumlah baris per halaman --}}
            <div class="flex items-center">
                <label for="rowsPerPage" class="mr-2 text-sm text-gray-600">Tampilkan:</label>
                <select id="rowsPerPage" class="border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="5">5</option>
                    <option value="10" selected>10</option> {{-- Default 10 baris --}}
                    <option value="15">15</option>
                </select>
            </div>
        </div>
        
        {{-- Form pencarian --}}
        <form id="searchForm" action="{{ route('admin.perusahaan.index') }}" method="GET" class="w-1/3">
            <input type="text" name="search" id="searchInput" placeholder="Cari perusahaan atau alamat..." value="{{ request('search') }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500">
        </form>
    </div>

    {{-- =============================== --}}
    {{-- TABEL DATA PERUSAHAAN --}}
    {{-- =============================== --}}
    <div class="overflow-x-auto"> {{-- Wrapper untuk horizontal scroll pada layar kecil --}}
        <table class="w-full border-collapse border border-gray-300" id="dataTable">
            
            {{-- Header tabel dengan warna kuning muda --}}
            <thead class="bg-[#fffbeb]">
                <tr>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-16">No</th> {{-- Kolom nomor, lebar fix 16 --}}
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Nama Perusahaan</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Alamat</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-32">Titik Lokasi</th> {{-- Kolom status lokasi --}}
                     <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-32">Latitude</th>
                      <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-32">Longitude</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase w-40">Aksi</th> {{-- Kolom tombol edit/hapus --}}
                </tr>
            </thead>
            
            {{-- Body tabel --}}
            <tbody id="tableBody">
                @forelse ($perusahaan as $index => $p) {{-- Loop data perusahaan --}}
                <tr class="data-row hover:bg-[#fffbeb]/30 transition"> {{-- Baris data dengan efek hover --}}
                    <td class="border border-gray-300 p-3 text-center text-sm text-gray-900">{{ $index + 1 }}</td> {{-- Nomor urut --}}
                    <td class="border border-gray-300 p-3 text-sm text-gray-900 font-medium">{{ $p->nama_perusahaan }}</td> {{-- Nama perusahaan --}}
                    <td class="border border-gray-300 p-3 text-sm text-gray-900">{{ $p->alamat }}</td> {{-- Alamat --}}
        
                    
                    {{-- Kolom Status Titik Lokasi: badge hijau/merah --}}
                    <td class="border border-gray-300 p-3 text-center">
                        @if($p->latitude && $p->longitude) {{-- Jika sudah ada koordinat --}}
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                <i class="fas fa-check-circle mr-1"></i>Ada
                            </span>
                        @else {{-- Jika belum ada koordinat --}}
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                <i class="fas fa-times-circle mr-1"></i>Belum
                            </span>
                        @endif
                    </td>
                    <td class="border border-gray-300 p-3 text-sm text-gray-900">{{ $p->latitude }}</td>
                    <td class="border border-gray-300 p-3 text-sm text-gray-900">{{ $p->longitude }}</td>
                    
                    {{-- Kolom Aksi: Edit dan Hapus --}}
                    <td class="border border-gray-300 p-3 text-center text-sm">
                        <a href="{{ route('admin.perusahaan.edit', $p->id_perusahaan) }}" class="text-orange-500 hover:underline mr-2 font-medium">Edit</a> {{-- Link edit --}}
                        
                        {{-- Form hapus dengan method DELETE --}}
                        <form action="{{ route('admin.perusahaan.destroy', $p->id_perusahaan) }}" method="POST" class="inline-block delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline font-medium">Hapus</button> {{-- Tombol hapus --}}
                        </form>
                    </td>
                </tr>
                @empty {{-- Jika tidak ada data --}}
                <tr id="noDataRow">
                    <td colspan="5" class="border border-gray-300 p-3 text-center text-sm text-gray-900">Tidak ada data ditemukan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        {{-- Pesan jika pencarian tidak menemukan hasil --}}
        <div id="noSearchResults" class="text-center text-sm text-gray-900 mt-4 hidden">Data tidak ditemukan</div>
    </div>

    {{-- =============================== --}}
    {{-- PAGINATION: Previous - Info - Next --}}
    {{-- =============================== --}}
    <div class="flex justify-between mt-4">
        <button id="prevPage" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg disabled:opacity-50" disabled>Previous</button> {{-- Disabled awal karena halaman 1 --}}
        <span id="pageInfo" class="text-sm text-gray-900 self-center"></span> {{-- Info "Page X of Y" --}}
        <button id="nextPage" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 disabled:opacity-50" disabled>Next</button>
    </div>
</div>

{{-- Hidden input untuk menyimpan pesan sukses dari session (setelah tambah/edit/hapus) --}}
<input type="hidden" id="success-message" value="{{ session('success') ?? '' }}">

@push('scripts') {{-- Push script ke section scripts di layout --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> {{-- Library SweetAlert2 untuk popup konfirmasi --}}
<script>
    // ===============================
    // TAMPILKAN PESAN SUKSES (jika ada dari session)
    // ===============================
    const successMessage = document.getElementById('success-message').value;
    if (successMessage) { // Jika ada pesan sukses
        Swal.fire({ 
            icon: 'success', 
            title: 'Sukses!', 
            text: successMessage, 
            timer: 2000,          // Auto-tutup 2 detik
            showConfirmButton: true, 
            confirmButtonText: 'OK' 
        });
    }

    // ===============================
    // KONFIRMASI HAPUS DENGAN SWEETALERT2
    // ===============================
    document.querySelectorAll('.delete-form').forEach(function(form) { // Loop semua form hapus
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Cegah submit langsung
            
            // Tampilkan popup konfirmasi dengan peringatan dampak penghapusan
            Swal.fire({
                title: 'Peringatan!',
                html: '<p class="text-red-600 font-semibold">Jika anda menghapus Perusahaan ini maka data pada:</p><ul class="text-left mt-2 text-gray-700 list-disc list-inside"><li>Data Mahasiswa Magang</li><li>Data Logbook</li></ul><p class="mt-3 text-red-600 font-bold">akan ikut terhapus!</p>',
                icon: 'warning', 
                showCancelButton: true, 
                confirmButtonColor: '#d33',         // Tombol konfirmasi merah
                cancelButtonColor: '#f97316',       // Tombol batal oranye
                confirmButtonText: 'Iya, Lanjut', 
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) { // Jika user klik "Iya, Lanjut"
                    // Tampilkan popup loading, lalu submit form
                    Swal.fire({ 
                        title: 'Menghapus...', 
                        text: 'Data perusahaan sedang dihapus.', 
                        icon: 'info', 
                        showConfirmButton: false, 
                        allowOutsideClick: false, // Cegah tutup dengan klik luar
                        didOpen: () => { form.submit(); } // Submit form saat popup terbuka
                    });
                }
            });
        });
    });

    // ===============================
    // VARIABEL PAGINATION & SEARCH (client-side)
    // ===============================
    let rowsPerPage = parseInt(document.getElementById('rowsPerPage').value); // Jumlah baris per halaman
    let currentPage = 1; // Halaman aktif
    let filteredRows = []; // Array untuk menyimpan baris yang cocok dengan pencarian

    /**
     * Fungsi utama untuk update tampilan tabel
     * Menghandle filtering, pagination, dan visibility baris
     */
    function updateTable() {
        const rows = document.querySelectorAll('#tableBody .data-row'); // Semua baris data
        const noDataRow = document.getElementById('noDataRow'); // Baris "tidak ada data"
        const noSearchResults = document.getElementById('noSearchResults'); // Pesan "tidak ditemukan"
        const prevButton = document.getElementById('prevPage'); // Tombol previous
        const nextButton = document.getElementById('nextPage'); // Tombol next
        const pageInfo = document.getElementById('pageInfo');   // Span info halaman
        const searchTerm = document.getElementById('searchInput').value.toLowerCase(); // Kata kunci pencarian
        
        // Filter baris berdasarkan kolom Nama (index 1) dan Alamat (index 2)
        filteredRows = Array.from(rows).filter(row => {
            return row.cells[1].textContent.toLowerCase().includes(searchTerm) || // Cari di nama
                   row.cells[2].textContent.toLowerCase().includes(searchTerm);    // Cari di alamat
        });
        
        // Sembunyikan semua baris terlebih dahulu
        rows.forEach(row => row.style.display = 'none');
        
        // Hitung total halaman
        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        
        // Pastikan halaman aktif tidak melebihi total halaman
        currentPage = Math.min(currentPage, Math.max(1, totalPages));
        
        // Tampilkan baris yang sesuai dengan halaman aktif
        filteredRows.slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage).forEach(row => row.style.display = '');
        
        // Update status tombol previous/next
        prevButton.disabled = currentPage === 1; // Disabled di halaman pertama
        nextButton.disabled = currentPage === totalPages || totalRows === 0; // Disabled di halaman terakhir
        
        // Update teks info halaman
        pageInfo.textContent = totalRows > 0 ? 'Page ' + currentPage + ' of ' + totalPages : '';
        
        // Tampilkan/sembunyikan baris "tidak ada data" (hanya jika DB kosong)
        if (noDataRow) noDataRow.parentElement.style.display = rows.length === 0 ? '' : 'none';
        
        // Tampilkan/sembunyikan pesan pencarian tidak ditemukan
        noSearchResults.classList.toggle('hidden', totalRows > 0 || rows.length === 0);
    }

    // ===============================
    // EVENT LISTENERS
    // ===============================
    
    // Cegah form search melakukan reload halaman (karena search dilakukan client-side)
    document.getElementById('searchForm').addEventListener('submit', function(e) { e.preventDefault(); });
    
    // Cegah Enter pada search input melakukan submit form
    document.getElementById('searchInput').addEventListener('keydown', function(e) { 
        if (e.key === 'Enter') { e.preventDefault(); e.stopPropagation(); } 
    });
    
    // Saat mengetik di search: reset ke halaman 1 dan update tabel
    document.getElementById('searchInput').addEventListener('input', function() { 
        currentPage = 1; 
        updateTable(); 
    });
    
    // Saat ganti jumlah baris per halaman: reset ke halaman 1 dan update tabel
    document.getElementById('rowsPerPage').addEventListener('change', function() { 
        rowsPerPage = parseInt(this.value); 
        currentPage = 1; 
        updateTable(); 
    });
    
    // Tombol previous: kurangi halaman jika bukan halaman pertama
    document.getElementById('prevPage').addEventListener('click', function() { 
        if (currentPage > 1) { currentPage--; updateTable(); } 
    });
    
    // Tombol next: tambah halaman jika bukan halaman terakhir
    document.getElementById('nextPage').addEventListener('click', function() { 
        var tp = Math.ceil(filteredRows.length / rowsPerPage); // Hitung total halaman
        if (currentPage < tp) { currentPage++; updateTable(); } 
    });
    
    // Inisialisasi tabel saat pertama kali load
    updateTable();
</script>
@endpush
@endsection