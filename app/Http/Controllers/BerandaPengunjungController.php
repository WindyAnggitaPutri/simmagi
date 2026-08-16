<?php

namespace App\Http\Controllers;

use App\Models\JadwalSemgang;
use App\Models\Perusahaan;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\SuratPengajuan;
use App\Models\PengajuanMagang;
use Illuminate\Http\Request;
use DB;

/**
 * Controller untuk halaman-halaman yang bisa diakses oleh pengunjung (publik)
 * Jadi ini halaman-halaman yang nggak perlu login dulu untuk melihatnya
 */
class BerandaPengunjungController extends Controller
{
    // ✅ HALAMAN BERANDA (GABUNGAN)
    // ✅ HALAMAN BERANDA (GABUNGAN)
    public function index()
    {
        // =============================================
        // DATA UTAMA (Dipakai semua role)
        // =============================================
        
        // Ambil 10 jadwal seminar terbaru, beserta relasinya:
        // - mahasiswa.prodi -> data mahasiswa dan prodi-nya
        // - dosenPembimbing.dosen -> data dosen pembimbing
        // - perusahaan -> data perusahaan tempat magang
        // Diurutkan dari tanggal terbaru
        $jadwal = JadwalSemgang::with(['mahasiswa.prodi', 'dosenPembimbing.dosen', 'perusahaan'])
                    ->orderBy('tgl_semgang', 'desc')
                    ->limit(10)
                    ->get();

        // =============================================
        // DATA PERUSAHAAN UNTUK BERANDA
        // =============================================
        // Query ini cukup kompleks, jadi aku jelasin step by step:
        // 
        // 1. selectRaw('COUNT(DISTINCT pm.id_pengajuan_mahasiswa) as jumlah_mahasiswa')
        //    -> Hitung jumlah mahasiswa UNIK yang magang di perusahaan tersebut
        // 
        // 2. leftJoin ke pengajuan_magang -> hubungkan perusahaan dengan pengajuan magang
        // 
        // 3. leftJoin ke pengajuan_mahasiswa dengan kondisi status_surat_balasan = 'diterima'
        //    -> Jadi cuma menghitung mahasiswa yang SUDAH DITERIMA magangnya
        // 
        // 4. whereExists -> pastikan pengajuan tersebut punya surat pengantar
        //    -> Ini untuk filter pengajuan yang sudah lengkap administrasinya
        // 
        // 5. where('tampilkan_publik', 1) -> cuma ambil perusahaan yang diizinkan tampil di publik
        // 
        // 6. groupBy -> grouping berdasarkan kolom-kolom perusahaan
        // 
        // 7. orderByDesc('jumlah_mahasiswa') -> urutkan dari yang paling banyak mahasiswanya
        // 
        // 8. limit(8) -> ambil 8 perusahaan teratas
        $perusahaans = Perusahaan::select('perusahaan.*')
            ->selectRaw('COUNT(DISTINCT pm.id_pengajuan_mahasiswa) as jumlah_mahasiswa')
            ->leftJoin('pengajuan_magang as pmg', 'perusahaan.id_perusahaan', '=', 'pmg.id_perusahaan')
            ->leftJoin('pengajuan_mahasiswa as pm', function ($join) {
                $join->on('pmg.id_pengajuan', '=', 'pm.id_pengajuan')
                     ->where('pm.status_surat_balasan', 'diterima');
            })
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('surat_pengajuan as sp')
                      ->whereColumn('sp.id_pengajuan', 'pmg.id_pengajuan')
                      ->where('sp.jenis_surat', 'pengantar');
            })
            ->where('perusahaan.tampilkan_publik', 1)
            ->groupBy('perusahaan.id_perusahaan', 'perusahaan.nama_perusahaan', 'perusahaan.alamat')
            ->orderByDesc('jumlah_mahasiswa')
            ->limit(8)
            ->get();

        // =============================================
        // ANGKA-ANGKA STATISTIK UNTUK CARD DI BERANDA
        // =============================================
        $jumlahMahasiswa = Mahasiswa::count();     // Total semua mahasiswa
        $jumlahProdi = Prodi::count();             // Total semua prodi
        $jumlahPerusahaan = Perusahaan::count();   // Total semua perusahaan

        // ✅ HAPUS QUERY DOSPEM KARENA SUDAH TIDAK DIPAKAI DI VIEW
        
        // Kirim semua data ke view beranda
        return view('pages.beranda', compact(
            'jadwal', 'perusahaans', 'jumlahMahasiswa', 'jumlahProdi', 'jumlahPerusahaan'
        ));
    }

    /**
     * Halaman daftar jadwal seminar yang bisa diakses pengunjung
     * Ada fitur filter berdasarkan prodi
     */
    public function jadwalSeminar(Request $request)
    {
        // Ambil semua prodi untuk dropdown filter
        $prodis = Prodi::all();

        // Query dasar untuk jadwal seminar, beserta relasinya
        // Diurutkan dari tanggal terbaru
        $jadwalQuery = JadwalSemgang::with(['mahasiswa.prodi', 'dosenPembimbing.dosen', 'perusahaan'])
                        ->orderBy('tgl_semgang', 'desc');

        // =============================================
        // FILTER BERDASARKAN PRODI
        // =============================================
        // Kalau user memilih prodi di dropdown, filter jadwal berdasarkan prodi mahasiswa-nya
        // whereHas artinya: "cari jadwal yang MAHASISWA-nya punya id_prodi = ..."
        if ($request->has('id_prodi') && $request->id_prodi != '') {
            $jadwalQuery->whereHas('mahasiswa', function ($q) use ($request) {
                $q->where('id_prodi', $request->id_prodi);
            });
        }

        // Paginate 15 data per halaman
        $jadwal = $jadwalQuery->paginate(15);

        return view('pages.pengunjung.jadwal_seminar', compact('prodis', 'jadwal'));
    }

    // ✅ HALAMAN REFERENSI PERUSAHAAN (TABEL)
    /**
     * Halaman tabel referensi perusahaan untuk pengunjung
     * Menampilkan daftar perusahaan yang bisa dijadikan tempat magang
     * Ada fitur pencarian berdasarkan nama perusahaan
     */
    public function referensiPerusahaan(Request $request)
    {
        // Query dasar sama persis dengan yang di method index() beranda
        // Bedanya di sini nggak ada limit(), karena mau ditampilkan semua (dengan pagination)
        $query = Perusahaan::select('perusahaan.*')
    ->selectRaw('COUNT(DISTINCT pm.id_pengajuan_mahasiswa) as jumlah_mahasiswa')
    ->leftJoin('pengajuan_magang as pmg', 'perusahaan.id_perusahaan', '=', 'pmg.id_perusahaan')
    ->leftJoin('pengajuan_mahasiswa as pm', function ($join) {
        // Join ke pengajuan_mahasiswa, tapi hanya yang statusnya 'diterima'
        $join->on('pmg.id_pengajuan', '=', 'pm.id_pengajuan')
             ->where('pm.status_surat_balasan', 'diterima');
    })
    ->whereExists(function ($q) {
        // Pastikan pengajuan tersebut punya surat pengantar
        $q->select(DB::raw(1))
          ->from('surat_pengajuan as sp')
          ->whereColumn('sp.id_pengajuan', 'pmg.id_pengajuan')
          ->where('sp.jenis_surat', 'pengantar');
    })
    ->where('perusahaan.tampilkan_publik', 1) // ✅ TAMBAHKAN INI -> Hanya perusahaan yang diizinkan tampil di publik
    ->groupBy('perusahaan.id_perusahaan', 'perusahaan.nama_perusahaan', 'perusahaan.alamat')
    ->orderByDesc('jumlah_mahasiswa'); // Urutkan dari yang paling banyak mahasiswanya

        // =============================================
        // FILTER PENCARIAN
        // =============================================
        // Kalau user mengetik sesuatu di search box, filter berdasarkan nama perusahaan
        // Menggunakan LIKE agar pencarian bersifat fleksibel (partial match)
        if ($request->has('search') && $request->search != '') {
            $query->where('perusahaan.nama_perusahaan', 'like', '%' . $request->search . '%');
        }

        // Paginate 10 data per halaman
        $perusahaans = $query->paginate(10);

        return view('pages.pengunjung.referensi_perusahaan', compact('perusahaans'));
    }

    // ✅ DETAIL PERUSAHAAN
// ✅ DETAIL PERUSAHAAN
/**
 * Halaman detail satu perusahaan
 * Menampilkan info perusahaan dan daftar mahasiswa yang pernah/sedang magang di sana
 * Ada fitur filter berdasarkan prodi dan pencarian nama/NIM
 */
public function detailPerusahaan(Request $request, $id_perusahaan)
{
    // Cari perusahaan berdasarkan ID, kalau nggak ketemu langsung 404
    $perusahaan = Perusahaan::findOrFail($id_perusahaan);

    // Ambil semua prodi untuk dropdown filter
    $prodis = Prodi::all();

    // =============================================
    // QUERY MAHASISWA MAGANG DI PERUSAHAAN INI
    // =============================================
    // Cari semua pengajuan magang yang mengarah ke perusahaan ini
    $mahasiswaQuery = PengajuanMagang::where('id_perusahaan', $id_perusahaan)
        // Filter: hanya pengajuan yang punya mahasiswa dengan status 'diterima'
        ->whereHas('mahasiswa', function ($q) {
            $q->where('status_surat_balasan', 'diterima');
        })
        // Pastikan pengajuan tersebut punya surat pengantar (administrasi lengkap)
        ->whereExists(function ($q) {
            $q->select(DB::raw(1))
              ->from('surat_pengajuan as sp')
              ->whereColumn('sp.id_pengajuan', 'pengajuan_magang.id_pengajuan')
              ->where('sp.jenis_surat', 'pengantar');
        })
        // Load relasi mahasiswa (nested: mahasiswa -> mahasiswa -> prodi)
        // Struktur relasinya: PengajuanMagang -> PengajuanMahasiswa -> Mahasiswa -> Prodi
        ->with(['mahasiswa.mahasiswa.prodi']);

    // Urutkan dari tanggal mulai terbaru
    $pengajuans = $mahasiswaQuery->orderBy('tgl_mulai', 'desc')->get();

    // =============================================
    // FLATTEN DATA
    // =============================================
    // Data dari query di atas strukturnya nested (pengajuan > mahasiswa > detail)
    // Kita perlu "flatten" jadi satu list datar yang berisi objek mahasiswa
    // 
    // Struktur asli:
    // Pengajuan 1
    //   └── Mahasiswa A (status: diterima)
    //   └── Mahasiswa B (status: ditolak) -> akan di-skip
    // Pengajuan 2
    //   └── Mahasiswa C (status: diterima)
    //
    // Setelah flatten:
    // [Mahasiswa A, Mahasiswa C]
    $daftarMahasiswa = collect();
    foreach ($pengajuans as $pengajuan) {
        foreach ($pengajuan->mahasiswa as $pm) {
            // Hanya ambil yang statusnya 'diterima' dan datanya nggak null
            if ($pm->status_surat_balasan === 'diterima' && $pm->mahasiswa) {
                // Buat objek baru dengan data yang dibutuhkan view
                $daftarMahasiswa->push((object)[
                    'nama' => $pm->mahasiswa->nama,
                    'npm' => $pm->mahasiswa->npm,
                    'prodi' => $pm->mahasiswa->prodi->nama_prodi ?? '-',  // ?? '-' kalau prodi-nya null
                    'id_prodi' => $pm->mahasiswa->id_prodi,
                    'tgl_mulai' => $pengajuan->tgl_mulai,     // Ambil dari pengajuan, bukan dari mahasiswa
                    'tgl_selesai' => $pengajuan->tgl_selesai, // Ambil dari pengajuan, bukan dari mahasiswa
                ]);
            }
        }
    }

    // ✅ Filter berdasarkan prodi
    // Kalau user memilih prodi di dropdown, filter daftar mahasiswa
    if ($request->has('id_prodi') && $request->id_prodi != '') {
        $daftarMahasiswa = $daftarMahasiswa->filter(function ($mhs) use ($request) {
            return $mhs->id_prodi == $request->id_prodi;
        });
    }

    // ✅ Filter berdasarkan search (nama / NIM)
    // Kalau user mengetik di search box, cari berdasarkan nama atau NIM
    if ($request->has('search') && $request->search != '') {
        $search = strtolower($request->search);
        $daftarMahasiswa = $daftarMahasiswa->filter(function ($mhs) use ($search) {
            return str_contains(strtolower($mhs->nama), $search)
                || str_contains(strtolower($mhs->nim ?? ''), $search);  // nim pakai ?? '' karena di objek di atas pakai nama 'npm'
        });
    }

    // Re-index collection
    // Setelah di-filter, index-nya mungkin jadi acak (1, 3, 7, dll)
    // values() buat bikin index-nya rapi lagi (0, 1, 2, 3, ...)
    $daftarMahasiswa = $daftarMahasiswa->values();

    // Hitung total mahasiswa setelah filter
    $totalMahasiswa = $daftarMahasiswa->count();

    // =============================================
    // CONTACT PERSON
    // =============================================
    // Ambil data pengajuan terakhir untuk menampilkan contact person perusahaan
    // Biasanya contact person ada di data pengajuan
    $pengajuanTerakhir = PengajuanMagang::where('id_perusahaan', $id_perusahaan)
        ->orderBy('tgl_pengajuan', 'desc')  // Ambil yang paling baru
        ->first();

    return view('pages.pengunjung.detail_perusahaan', compact(
        'perusahaan', 'daftarMahasiswa', 'prodis', 'totalMahasiswa', 'pengajuanTerakhir'
    ));
}
}