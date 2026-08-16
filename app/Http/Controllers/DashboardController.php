<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Perusahaan;
use App\Models\JadwalSemgang;
use App\Models\DosenPembimbing;
use App\Models\Dosen;
use App\Models\DosenVisitasi;
use Carbon\Carbon;

/**
 * Controller untuk halaman Dashboard
 * Saat ini baru ada dashboard untuk Admin
 * Kalau nanti butuh dashboard untuk role lain (kaprodi, dosen, dll), bisa ditambah di sini
 */
class DashboardController extends Controller
{
    /**
     * Dashboard untuk Admin
     * Menampilkan statistik dan data ringkas yang dibutuhkan admin
     */
    public function adminDashboard()
    {
        // ==================== CARD STATISTIK ====================
        // Angka-angka yang ditampilkan di card-card di atas dashboard

        // 1. Total Mahasiswa yang terdaftar di sistem
        $totalMahasiswa = Mahasiswa::count();

        // 2. Total Perusahaan Mitra yang sudah terdaftar
        $totalPerusahaan = Perusahaan::count();

        // 3. Total Jadwal Seminar yang pernah dibuat
        $totalSeminar = JadwalSemgang::count();
        
        // Ubah query ini agar menghitung semua yang BUKAN selesai (on going)
        // Jadi ini menghitung seminar yang masih akan datang atau sedang berlangsung
        $seminarMendatang = JadwalSemgang::where('status_semgang', '!=', 'selesai')->count();

        // 4. Total Dosen yang terdaftar di sistem
        $totalDosen = Dosen::count();

        // ==================== TABEL: SEMINAR TERDEKAT ====================
        // Menampilkan 5 jadwal seminar yang akan datang, diurutkan dari yang paling dekat
        
        $seminarTerdekat = JadwalSemgang::with(['mahasiswa.prodi', 'perusahaan', 'dosenPembimbing.dosen'])
            ->where('status_semgang', '!=', 'selesai') // Hanya ambil yang statusnya BUKAN selesai
            ->orderBy('tgl_semgang', 'asc')   // Urutkan dari tanggal terdekat
            ->orderBy('jam_mulai', 'asc')     // Kalau tanggalnya sama, urutkan dari jam paling pagi
            ->limit(5)                        // Ambil 5 data saja
            ->get();

        // ==================== STATISTIK TAMBAHAN ====================
        // Ini untuk data tambahan di dashboard
        // Yang dikomentari berarti sudah tidak dipakai di view
        
        // $totalDospem = DosenPembimbing::where('status', 'selesai')->count();
        // $totalVisitasi = DosenVisitasi::where('status', 'selesai')->count();
        
        // Total seminar yang sudah selesai
        $seminarSelesai = JadwalSemgang::where('status_semgang', 'selesai')->count();

        // Kirim semua data ke view dashboard admin
        return view('pages.admin.dashboard', compact(
            'totalMahasiswa',
            'totalPerusahaan',
            'totalSeminar',
            'seminarMendatang',
            'totalDosen',
            'seminarTerdekat',
            // 'totalVisitasi',   // Sudah tidak dipakai
            'seminarSelesai'
        ));
    }
}