@extends('layouts.admin')

@section('title', 'SIMMAGI - Kelola User')
@section('page-title', 'Manajemen User')

@section('content')
<h2 class="text-center text-4xl font-bold text-gray-800 mb-6">.:: Manajemen User ::.</h2>

<div class="bg-white shadow-md p-4 rounded-lg">
    <div class="flex justify-between mb-4">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.user.create') }}" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 flex items-center transition">
                <i class="fas fa-user-plus mr-2"></i>Tambah User
            </a>
            <div class="flex items-center">
                <label for="rowsPerPage" class="mr-2 text-sm text-gray-600">Tampilkan:</label>
                <select id="rowsPerPage" class="border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="15">15</option>
                    <option value="20">20</option>
                </select>
            </div>
        </div>
        <form id="searchForm" action="{{ route('admin.user.index') }}" method="GET" class="w-1/3">
            <input type="text" name="search" id="searchInput" placeholder="Cari username, role..." value="{{ request('search') }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500">
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300" id="dataTable">
            <thead class="bg-[#fffbeb]">
                <tr>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">No</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">ID User</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Username</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Email</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Sandi (Hash)</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Role</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Ganti Password</th>
                    <th class="border border-gray-300 p-3 text-center text-xs font-medium text-[#b45309] uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse ($users as $index => $u)
                <tr class="data-row hover:bg-[#fffbeb]/30 transition">
                    <td class="border border-gray-300 p-3 text-center text-sm text-gray-900">{{ $index + 1 }}</td>
                    <td class="border border-gray-300 p-3 text-center text-sm text-gray-500 font-mono">{{ $u->id_user }}</td>
                    <td class="border border-gray-300 p-3 text-sm text-gray-900 font-medium">{{ $u->username }}</td>
                    
                    <!-- ✅ KOLOM EMAIL -->
                    <td class="border border-gray-300 p-3 text-sm text-gray-700">{{ $u->associated_email }}</td>
                    
                    <!-- ✅ KOLOM SANDI HASH (Dipotong biar tabel nggak pecah) -->
                    <td class="border border-gray-300 p-3 text-center text-xs font-mono text-gray-500 cursor-pointer" title="{{ $u->password }}">
                        {{ \Illuminate\Support\Str::limit($u->password, 25, '...') }}
                    </td>
                    
                    <td class="border border-gray-300 p-3 text-center text-sm">
                        @php
                            $roleColor = match($u->role) {
                                'admin'     => 'bg-red-100 text-red-700',
                                'dosen'     => 'bg-blue-100 text-blue-700',
                                'mahasiswa' => 'bg-green-100 text-green-700',
                                'pegawai'   => 'bg-purple-100 text-purple-700',
                                default     => 'bg-gray-100 text-gray-700',
                            };
                            $roleLabel = match($u->role) {
                                'admin'     => 'Admin',
                                'dosen'     => 'Dosen',
                                'mahasiswa' => 'Mahasiswa',
                                'pegawai'   => 'Pegawai',
                                default     => ucfirst($u->role),
                            };
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $roleColor }}">{{ $roleLabel }}</span>
                    </td>
                    <td class="border border-gray-300 p-3 text-center text-sm">
                        @if($u->must_change_password)
                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-700">
                                <i class="fas fa-exclamation-circle mr-1"></i>Wajib
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-500">Sudah</span>
                        @endif
                    </td>
                    <td class="border border-gray-300 p-3 text-center text-sm">
                        <a href="{{ route('admin.user.edit', $u->id_user) }}" class="text-orange-500 hover:underline mr-2 font-medium">Edit</a>
                        <form action="{{ route('admin.user.destroy', $u->id_user) }}" method="POST" class="inline-block delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline font-medium">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr id="noDataRow">
                    <td colspan="8" class="border border-gray-300 p-3 text-center text-sm text-gray-900">Tidak ada data ditemukan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div id="noSearchResults" class="text-center text-sm text-gray-900 mt-4 hidden">Data tidak ditemukan</div>
    </div>

    <div class="flex justify-between mt-4">
        <button id="prevPage" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg disabled:opacity-50" disabled>Previous</button>
        <span id="pageInfo" class="text-sm text-gray-900 self-center"></span>
        <button id="nextPage" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 disabled:opacity-50" disabled>Next</button>
    </div>
</div>

<input type="hidden" id="success-message" value="{{ session('success') ?? '' }}">

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const successMessage = document.getElementById('success-message').value;
    if (successMessage) {
        Swal.fire({ icon: 'success', title: 'Sukses!', text: successMessage, timer: 2500, showConfirmButton: true, confirmButtonText: 'OK' });
    }

    document.querySelectorAll('.delete-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const row = form.closest('tr');
            const username = row.cells[2].textContent.trim(); // Indeks 2: Username
            const role = row.cells[5].textContent.trim();     // Indeks 5: Role

            Swal.fire({
                title: 'Hapus User?',
                html: '<p class="text-gray-700">User: <strong>' + username + '</strong></p><p class="text-gray-700">Role: <strong>' + role + '</strong></p><p class="mt-3 text-red-600 font-semibold">Perhatian: Jika user ini terhubung ke data Mahasiswa/Dosen/Pegawai, data terkait mungkin terpengaruh!</p>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#f97316',
                confirmButtonText: 'Iya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menghapus...', text: 'User sedang dihapus.', icon: 'info', showConfirmButton: false, allowOutsideClick: false, didOpen: () => { form.submit(); } });
                }
            });
        });
    });

    let rowsPerPage = parseInt(document.getElementById('rowsPerPage').value);
    let currentPage = 1;
    let filteredRows = [];

    function updateTable() {
        const rows = document.querySelectorAll('#tableBody .data-row');
        const noDataRow = document.getElementById('noDataRow');
        const noSearchResults = document.getElementById('noSearchResults');
        const prevButton = document.getElementById('prevPage');
        const nextButton = document.getElementById('nextPage');
        const pageInfo = document.getElementById('pageInfo');
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();

        filteredRows = Array.from(rows).filter(row => {
            return row.cells[2].textContent.toLowerCase().includes(searchTerm) || row.cells[5].textContent.toLowerCase().includes(searchTerm);
        });

        rows.forEach(row => row.style.display = 'none');

        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        currentPage = Math.min(currentPage, Math.max(1, totalPages));

        filteredRows.slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage).forEach(row => row.style.display = '');

        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages || totalRows === 0;
        pageInfo.textContent = totalRows > 0 ? 'Page ' + currentPage + ' of ' + totalPages : '';

        if (noDataRow) noDataRow.parentElement.style.display = rows.length === 0 ? '' : 'none';
        noSearchResults.classList.toggle('hidden', totalRows > 0 || rows.length === 0);
    }

    document.getElementById('searchForm').addEventListener('submit', function(e) { e.preventDefault(); });
    document.getElementById('searchInput').addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); e.stopPropagation(); } });
    document.getElementById('searchInput').addEventListener('input', function() { currentPage = 1; updateTable(); });
    document.getElementById('rowsPerPage').addEventListener('change', function() { rowsPerPage = parseInt(this.value); currentPage = 1; updateTable(); });
    document.getElementById('prevPage').addEventListener('click', function() { if (currentPage > 1) { currentPage--; updateTable(); } });
    document.getElementById('nextPage').addEventListener('click', function() { var tp = Math.ceil(filteredRows.length / rowsPerPage); if (currentPage < tp) { currentPage++; updateTable(); } });
    updateTable();
</script>
@endpush
@endsection