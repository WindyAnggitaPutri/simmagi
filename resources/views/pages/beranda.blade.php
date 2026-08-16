@extends('layouts.app')

@section('content')
<style>
/* ==================== BERANDA ==================== */
.beranda-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
    align-items: center;
}
@media (min-width: 768px) {
    .beranda-grid { gap: 2.5rem; }
}

.beranda-title {
    font-size: 0.75rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    line-height: 1.25;
}
@media (min-width: 640px) {
    .beranda-title { font-size: 1.5rem; }
}
@media (min-width: 768px) {
    .beranda-title { font-size: 3rem; margin-bottom: 1rem; }
}

.beranda-desc {
    font-size: 7px;
    color: #4b5563;
    margin-bottom: 0.5rem;
    line-height: 1.625;
}
@media (min-width: 640px) {
    .beranda-desc { font-size: 0.75rem; }
}
@media (min-width: 768px) {
    .beranda-desc { font-size: 1rem; margin-bottom: 1.5rem; }
}

.beranda-image {
    width: 100%;
    height: auto;
    object-fit: contain;
}
@media (min-width: 768px) {
    .beranda-image { object-fit: cover; }
}

.min-w-0 { min-width: 0; }

/* ==================== STAT CARDS ==================== */
.stat-container {
    display: flex;
    gap: 6px;
}
@media (min-width: 768px) {
    .stat-container { gap: 10px; }
}

.stat-card {
    width: 50px;
    height: 38px;
    background-color: #fff;
    border: 2px solid;
    border-radius: 0.5rem;
    padding: 0.25rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: box-shadow 0.15s ease;
}
.stat-card:hover { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
@media (min-width: 768px) {
    .stat-card { width: 100px; height: auto; aspect-ratio: 1/1; padding: 0.625rem; }
}

.stat-card--amber { border-color: #fbbf24; }
.stat-card--orange { border-color: #fb923c; }
.stat-card--yellow { border-color: #facc15; }

.stat-icon-box {
    width: 12px;
    height: 12px;
    border-radius: 0.25rem;
    border: 1px solid;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1px;
}
@media (min-width: 768px) {
    .stat-icon-box { width: 28px; height: 28px; margin-bottom: 6px; }
}

.stat-icon-box--amber { background-color: #fffbeb; border-color: #fde68a; }
.stat-icon-box--orange { background-color: #fff7ed; border-color: #fed7aa; }
.stat-icon-box--yellow { background-color: #fefce8; border-color: #fef08a; }

.stat-icon { width: 8px; height: 8px; }
@media (min-width: 768px) {
    .stat-icon { width: 16px; height: 16px; }
}

.stat-icon--amber { color: #d97706; }
.stat-icon--orange { color: #ea580c; }
.stat-icon--yellow { color: #ca8a04; }

.stat-number {
    font-size: 10px;
    font-weight: 800;
    line-height: 1;
}
@media (min-width: 768px) {
    .stat-number { font-size: 1.25rem; }
}

.stat-number--amber { color: #b45309; }
.stat-number--orange { color: #c2410c; }
.stat-number--yellow { color: #a16207; }

.stat-label {
    font-size: 5px;
    font-weight: 600;
    margin-top: 1px;
    line-height: 1.25;
}
@media (min-width: 768px) {
    .stat-label { font-size: 9px; margin-top: 2px; }
}

.stat-label--amber { color: #d97706; }
.stat-label--orange { color: #ea580c; }
.stat-label--yellow { color: #ca8a04; }

/* ==================== SECTION SPACING ==================== */
.section-spacing {
    margin-top: 2.5rem;
    margin-bottom: 2.5rem;
}
@media (min-width: 768px) {
    .section-spacing { margin-top: 4rem; margin-bottom: 4rem; }
}

.section-title-center {
    text-align: center;
    margin-bottom: 1rem;
}
@media (min-width: 768px) {
    .section-title-center { margin-bottom: 2rem; }
}

.section-title {
    font-size: 1rem;
    font-weight: 600;
}
@media (min-width: 768px) {
    .section-title { font-size: 1.25rem; }
}

.section-title--underline {
    text-decoration: underline;
    text-decoration-color: #f59e0b;
    text-underline-offset: 1rem;
}

/* ==================== JADWAL TABLE ==================== */
.table-container {
    display: flex;
    justify-content: center;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}
@media (min-width: 768px) {
    .table-container { padding-left: 0; padding-right: 0; }
}

.table-wrapper {
    width: 100%;
    max-width: 72rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
}

.jadwal-table {
    width: 100%;
    font-size: 11px;
    text-align: left;
    color: #4b5563;
    min-width: 500px;
}
@media (min-width: 768px) {
    .jadwal-table { font-size: 0.875rem; }
}

.jadwal-thead {
    font-size: 10px;
    color: #92400e;
    text-transform: uppercase;
    background-color: #fffbeb;
    border-bottom: 1px solid #fef3c7;
}
@media (min-width: 768px) {
    .jadwal-thead { font-size: 0.75rem; }
}

.jadwal-th {
    padding: 10px 12px;
}
@media (min-width: 768px) {
    .jadwal-th { padding: 12px 16px; }
}

.jadwal-tr {
    background-color: #fff;
    border-bottom: 1px solid #f3f4f6;
    transition: background-color 0.15s ease;
}
.jadwal-tr:hover { background-color: rgba(255,251,235,0.4); }

.jadwal-td {
    padding: 8px 12px;
}
@media (min-width: 768px) {
    .jadwal-td { padding: 12px 16px; }
}

.jadwal-td--name { font-weight: 500; color: #111827; white-space: nowrap; }
.jadwal-td--nowrap { white-space: nowrap; }

.jadwal-empty {
    padding: 1.5rem 1rem;
    text-align: center;
    color: #9ca3af;
}

/* ==================== LINK SELANJUTNYA ==================== */
.link-end {
    display: flex;
    justify-content: flex-end;
    margin-top: 0.75rem;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}
@media (min-width: 768px) {
    .link-end { padding-left: 0; padding-right: 0; }
}

.link-end--bottom {
    margin-top: 1rem;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}
@media (min-width: 768px) {
    .link-end--bottom { padding-left: 2.5rem; padding-right: 2.5rem; }
}

.link-selanjutnya {
    display: flex;
    align-items: center;
    gap: 4px;
    color: #b45309;
    font-size: 0.75rem;
    font-weight: 500;
    transition: color 0.15s ease;
}
.link-selanjutnya:hover { color: #78350f; }
@media (min-width: 768px) {
    .link-selanjutnya { gap: 6px; font-size: 0.875rem; }
}

.link-selanjutnya__arrow { font-size: 1.125rem; }
@media (min-width: 768px) {
    .link-selanjutnya__arrow { font-size: 1.125rem; }
}

.link-selanjutnya__arrow--small { font-size: 0.75rem; }
@media (min-width: 768px) {
    .link-selanjutnya__arrow--small { font-size: 1.125rem; }
}

/* ==================== PERUSAHAAN GRID ==================== */
.perusahaan-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}
@media (min-width: 640px) {
    .perusahaan-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (min-width: 768px) {
    .perusahaan-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.25rem; padding-left: 2.5rem; padding-right: 2.5rem; }
}

.perusahaan-card {
    border: 1px solid #e5e7eb;
    padding: 0.75rem;
    text-align: center;
    border-radius: 0.75rem;
    background-color: #fff;
    transition: all 0.15s ease;
}
.perusahaan-card:hover { border-color: #fcd34d; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
@media (min-width: 768px) {
    .perusahaan-card { padding: 1.25rem; }
}

.perusahaan-icon-box {
    width: 56px;
    height: 56px;
    background-color: #fffbeb;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.75rem;
    margin-left: auto;
    margin-right: auto;
    transition: background-color 0.15s ease;
}
.perusahaan-card:hover .perusahaan-icon-box { background-color: #fef3c7; }
@media (min-width: 768px) {
    .perusahaan-icon-box { width: 80px; height: 80px; }
}

.perusahaan-icon {
    width: 28px;
    height: 28px;
    color: #fbbf24;
    transition: color 0.15s ease;
}
.perusahaan-card:hover .perusahaan-icon { color: #d97706; }
@media (min-width: 768px) {
    .perusahaan-icon { width: 40px; height: 40px; }
}

.perusahaan-name {
    font-weight: 600;
    font-size: 10px;
    color: #1f2937;
    line-height: 1.25;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 2em;
}
@media (min-width: 768px) {
    .perusahaan-name { font-size: 0.875rem; min-height: 2.5em; }
}

.perusahaan-count {
    font-size: 9px;
    color: #d97706;
    margin-top: 6px;
    font-weight: 500;
}
@media (min-width: 768px) {
    .perusahaan-count { font-size: 0.75rem; }
}

.perusahaan-alamat {
    font-size: 8px;
    color: #9ca3af;
    margin-top: 2px;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
@media (min-width: 768px) {
    .perusahaan-alamat { font-size: 0.75rem; }
}

.perusahaan-empty {
    grid-column: span 4;
    text-align: center;
    color: #9ca3af;
    padding: 2rem 0;
}

/* ==================== ALUR ==================== */
.alur-container {
    width: 100%;
    max-width: 56rem;
    margin-left: auto;
    margin-right: auto;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}
@media (min-width: 768px) {
    .alur-container { padding-left: 1rem; padding-right: 1rem; }
}

.alur-row {
    display: flex;
    align-items: center;
    gap: 6px;
    justify-content: center;
}
@media (min-width: 768px) {
    .alur-row { gap: 1rem; }
}

.alur-arrow {
    width: 12px;
    height: 12px;
    color: #fcd34d;
    flex-shrink: 0;
}
@media (min-width: 768px) {
    .alur-arrow { width: 20px; height: 20px; }
}

.alur-arrow-down-wrapper {
    display: flex;
    justify-content: flex-end;
    padding-right: 8px;
    margin-top: 2px;
    margin-bottom: 2px;
}
@media (min-width: 768px) {
    .alur-arrow-down-wrapper { padding-right: 2.5rem; margin-top: 8px; margin-bottom: 8px; }
}

.alur-arrow-down {
    width: 12px;
    height: 12px;
    color: #fcd34d;
}
@media (min-width: 768px) {
    .alur-arrow-down { width: 20px; height: 20px; }
}

.alur-step {
    flex: 1;
    max-width: 140px;
    padding: 8px;
    border-radius: 0.5rem;
    border: 1px solid;
}
@media (min-width: 768px) {
    .alur-step { padding: 1.25rem; border-radius: 0.75rem; max-width: 20rem; }
}

.alur-step--amber { background-color: #fffbeb; border-color: #fde68a; }
.alur-step--orange { background-color: #fff7ed; border-color: #fed7aa; }
.alur-step--yellow { background-color: #fefce8; border-color: #fef08a; }
.alur-step--lime { background-color: #f7fee7; border-color: #bef264; }

.alur-step-header {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 4px;
}
@media (min-width: 768px) {
    .alur-step-header { gap: 8px; margin-bottom: 6px; }
}

.alur-step-number {
    width: 16px;
    height: 16px;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
    font-weight: 700;
    flex-shrink: 0;
}
@media (min-width: 768px) {
    .alur-step-number { width: 32px; height: 32px; font-size: 0.875rem; }
}

.alur-step-number--amber { background-color: #fde68a; color: #92400e; }
.alur-step-number--orange { background-color: #fed7aa; color: #9a3412; }
.alur-step-number--yellow { background-color: #fef08a; color: #854d0e; }
.alur-step-number--lime { background-color: #bef264; color: #365314; }

.alur-step-title {
    font-weight: 700;
    color: #1f2937;
    font-size: 7px;
}
@media (min-width: 768px) {
    .alur-step-title { font-size: 0.875rem; }
}

.alur-step-desc {
    font-size: 5px;
    color: #4b5563;
    line-height: 1.625;
}
@media (min-width: 768px) {
    .alur-step-desc { font-size: 0.75rem; }
}

.alur-spacer {
    flex: 1;
    max-width: 140px;
}
@media (min-width: 768px) {
    .alur-spacer { max-width: 20rem; }
}

/* ==================== BOTTOM SPACING ==================== */
.bottom-spacing {
    margin-bottom: 2.5rem;
}
@media (min-width: 768px) {
    .bottom-spacing { margin-bottom: 5rem; }
}
</style>

<!-- beranda -->
<div class="beranda-grid">
    {{-- Kiri --}}
    <div class="min-w-0">
        <h1 class="beranda-title">
            @guest
                SISTEM INFORMASI <br>
                MONITORING MAGANG <br>
                INDUSTRI
            @endguest

            @auth
                @php
                    $role = auth()->user()->role;
                    $activeRole = session('dosen_active_role');
                    $rolePegawai = session('role_pegawai');
                    
 $roleName = match(true) {
    $role == 'mahasiswa' => 'Mahasiswa',
    $role == 'dosen' && $activeRole == 'kajur' => 'Ketua Jurusan',
    $role == 'dosen' && $activeRole == 'kaprodi' => 'Ketua Program Studi',
    $role == 'dosen' && $activeRole == 'dosen_visitasi' => 'Dosen Visitasi',
    $role == 'dosen' && $activeRole == 'dospem' => 'Dosen Pembimbing',
    $role == 'dosen' => 'Dosen',
    $role == 'pegawai' && $rolePegawai == 'admin_kajur' => 'Admin Ketua Jurusan', // ✅ TAMBAH
    $role == 'pegawai' && $rolePegawai == 'baak' => 'Bagian Akademik dan Kemahasiswaan',
    $role == 'pegawai' && $rolePegawai == 'baup' => 'Bagian Umum dan Perencanaan',
    default => 'Pengguna'
};
                @endphp
                SIMMAGI <br>
                Selamat Datang, Anda Login Sebagai <br>
                {{ $roleName }}
            @endauth
        </h1>

        <p class="beranda-desc">
            Sistem Informasi Monitoring Magang Industri adalah platform digital
            berbasis web yang dirancang untuk mengelola, memantau, dan mengevaluasi
            kinerja serta aktivitas peserta magang secara real-time.
        </p>

        {{--  STATISTIK DINAMIS (Kartu Persegi Kotak - Putih + Border Warna) --}}
        <div class="stat-container">
            {{-- Mahasiswa --}}
            <div class="stat-card stat-card--amber">
                <div class="stat-icon-box stat-icon-box--amber">
                    <svg class="stat-icon stat-icon--amber" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                    </svg>
                </div>
                <p class="stat-number stat-number--amber">{{ $jumlahMahasiswa }}</p>
                <p class="stat-label stat-label--amber">Mahasiswa Aktif</p>
            </div>

            {{-- Prodi --}}
            <div class="stat-card stat-card--orange">
                <div class="stat-icon-box stat-icon-box--orange">
                    <svg class="stat-icon stat-icon--orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                    </svg>
                </div>
                <p class="stat-number stat-number--orange">{{ $jumlahProdi }}</p>
                <p class="stat-label stat-label--orange">Program Studi</p>
            </div>

            {{-- Perusahaan --}}
            <div class="stat-card stat-card--yellow">
                <div class="stat-icon-box stat-icon-box--yellow">
                    <svg class="stat-icon stat-icon--yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21"/>
                    </svg>
                </div>
                <p class="stat-number stat-number--yellow">{{ $jumlahPerusahaan }}</p>
                <p class="stat-label stat-label--yellow">Perusahaan</p>
            </div>
        </div>
    </div>

    {{-- Kanan - Gambar ukuran penuh seperti semula --}}
    <img src="{{ asset('images/profil.png') }}" class="beranda-image">
</div>

<!-- jadwal -->
<div class="section-spacing"></div>
<div class="section-title-center">
    <h2 class="section-title section-title">
        Jadwal Seminar Magang Mahasiswa <br>
        Jurusan Komputer dan Bisnis
    </h2>
</div>

<div class="table-container">
    <div class="table-wrapper">
        <table class="jadwal-table">
            <thead class="jadwal-thead">
                <tr>
                    <th class="jadwal-th">No</th>
                    <th class="jadwal-th">Nama Mahasiswa</th>
                    <th class="jadwal-th">Prodi</th>
                    <th class="jadwal-th">Perusahaan</th>
                    <th class="jadwal-th">Tanggal</th>
                    <th class="jadwal-th">Waktu</th>
                    <th class="jadwal-th">Ruang</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwal as $key => $item)
                <tr class="jadwal-tr">
                    <td class="jadwal-td">{{ $key + 1 }}</td>
                    <td class="jadwal-td jadwal-td--name">
                        {{ $item->mahasiswa->nama ?? '-' }}
                    </td>
                    <td class="jadwal-td jadwal-td--nowrap">
                        {{ $item->mahasiswa->prodi->nama_prodi ?? '-' }}
                    </td>
                    <td class="jadwal-td jadwal-td--nowrap">
                        {{ $item->perusahaan->nama_perusahaan ?? '-' }}
                    </td>
                    <td class="jadwal-td jadwal-td--nowrap">
                        {{ \Carbon\Carbon::parse($item->tgl_semgang)->translatedFormat('d M Y') }}
                    </td>
                    <td class="jadwal-td jadwal-td--nowrap">
                        {{ $item->jam_mulai }} - {{ $item->jam_selesai }}
                    </td>
                    <td class="jadwal-td">
                        {{ $item->ruang ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="jadwal-empty">
                        Belum ada jadwal seminar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="link-end">
    <a href="{{ route('pengunjung.jadwal-seminar') }}" class="link-selanjutnya">
        Selanjutnya
        <span class="link-selanjutnya__arrow">→</span>
    </a>
</div>

<!-- ✅ REFERENSI PERUSAHAAN DINAMIS -->
<div class="section-spacing"></div>

<div class="section-title-center">
    <h2 class="section-title">
        Referensi Perusahaan Magang Industri <br>
        Mahasiswa Jurusan Komputer dan Bisnis
    </h2>
</div>

<div class="perusahaan-grid">
    @forelse($perusahaans as $perusahaan)
    <a href="{{ route('pengunjung.detail-perusahaan', $perusahaan->id_perusahaan) }}" 
       class="perusahaan-card">
        <div class="perusahaan-icon-box">
            <svg class="perusahaan-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
        </div>
        <p class="perusahaan-name">{{ $perusahaan->nama_perusahaan }}</p>
        <p class="perusahaan-count">{{ $perusahaan->jumlah_mahasiswa }} magang</p>
        <p class="perusahaan-alamat">{{ Str::limit($perusahaan->alamat ?? '-', 25) }}</p>
    </a>
    @empty
    <div class="perusahaan-empty">
        Belum ada data perusahaan.
    </div>
    @endforelse
</div>

<div class="link-end link-end--bottom">
   <a href="{{ route('pengunjung.referensi-perusahaan') }}" class="link-selanjutnya">
    Selanjutnya
    <span class="link-selanjutnya__arrow--small">→</span>
</a>
</div>

<!-- Alur -->
<div class="section-spacing"></div>

<div class="section-title-center" style="margin-bottom: 1rem;">
    <!-- @media (min-width: 768px) { margin-bottom: 2.5rem; } -->
    <h2 class="section-title">
        Alur Pengajuan Permohonan Magang
    </h2>
</div>

<div class="alur-container">
    
    {{-- Baris 1: Langkah 1 → 2 → 3 --}}
    <div class="alur-row">
        <div class="alur-step alur-step--amber">
            <div class="alur-step-header">
                <div class="alur-step-number alur-step-number--amber">1</div>
                <h3 class="alur-step-title">Permohonan</h3>
            </div>
            <p class="alur-step-desc">Membuat surat permohonan magang dan surat izin orang tua</p>
        </div>
        
        <svg class="alur-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>

        <div class="alur-step alur-step--orange">
            <div class="alur-step-header">
                <div class="alur-step-number alur-step-number--orange">2</div>
                <h3 class="alur-step-title">Persetujuan Kaprodi</h3>
            </div>
            <p class="alur-step-desc">Menunggu persetujuan kaprodi</p>
        </div>

        <svg class="alur-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>

<div class="alur-step alur-step--yellow">
    <div class="alur-step-header">
        <div class="alur-step-number alur-step-number--yellow">3</div>
        <h3 class="alur-step-title">Persetujuan Admin Kajur</h3>
    </div>
    <p class="alur-step-desc">Menunggu persetujuan admin kajur</p>
</div>
    </div>

    {{-- Panah Bawah --}}
    <div class="alur-arrow-down-wrapper">
        <svg class="alur-arrow-down" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
    </div>

    {{-- Baris 2: Langkah 5 ← 4 --}}
    <div class="alur-row">
        <div class="alur-spacer"></div>

        <div class="alur-step alur-step--lime">
            <div class="alur-step-header">
                <div class="alur-step-number alur-step-number--lime">5</div>
                <h3 class="alur-step-title">Surat Balasan</h3>
            </div>
            <p class="alur-step-desc">Upload surat balasan dari perusahaan ke sistem</p>
        </div>

        <svg class="alur-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path></svg>

        <div class="alur-step alur-step--amber">
            <div class="alur-step-header">
                <div class="alur-step-number alur-step-number--amber">4</div>
                <h3 class="alur-step-title">Surat Resmi</h3>
            </div>
            <p class="alur-step-desc">Menunggu surat permohonan resmi dari BAAK</p>
        </div>
    </div>

</div>

<div class="bottom-spacing"></div>
@endsection