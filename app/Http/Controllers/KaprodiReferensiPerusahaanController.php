<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use Illuminate\Http\Request;
use DB;

/**
 * Controller ini khusus buat Kaprodi
 * Fungsinya buat ngelola daftar perusahaan yang bakal ditampilkan 
 * sebagai referensi di halaman publik (misalnya buat mahasiswa liat pilihan tempat magang)
 */
class KaprodiReferensiPerusahaanController extends Controller
{
    /**
     * Menampilkan halaman utama daftar referensi perusahaan
     * 
     * Di halaman ini ada:
     * - Tabel daftar perusahaan (bisa paginasi & search)
     * - Filter berdasarkan status tampil/sembunyikan
     * - Statistik ringkas (total perusahaan, yang memenuhi syarat, yang ditampilkan)
     */
    public function index(Request $request)
    {
        // Ambil jumlah data per halaman dari request, defaultnya 10
        $perPage = $request->get('per_page', 10);
        
        // Ambil kata kunci pencarian dari request, defaultnya kosong
        $search = $request->get('search', '');

        // Validasi: cuma boleh pilih 5, 10, 15, atau 'all'. Selain itu, balik ke 10.
        if (!in_array($perPage, [5, 10, 15, 'all'])) {
            $perPage = 10;
        }

        // ============================================
        // QUERY UTAMA: Ambil data perusahaan yang akan ditampilkan di tabel
        // ============================================
        $query = Perusahaan::select('perusahaan.*')
            // Hitung jumlah mahasiswa yang diterima di perusahaan ini
            // DISTINCT biar gak dobel kalo satu mahasiswa punya beberapa pengajuan
            ->selectRaw('COUNT(DISTINCT pm.id_pengajuan_mahasiswa) as jumlah_mahasiswa')
            
            // Gabungkan dengan tabel pengajuan_magang (relasi perusahaan - pengajuan)
            ->leftJoin('pengajuan_magang as pmg', 'perusahaan.id_perusahaan', '=', 'pmg.id_perusahaan')
            
            // Gabungkan dengan tabel pengajuan_mahasiswa, tapi HANYA yang statusnya "diterima"
            // Ini buat ngitung mahasiswa yang beneran diterima magang di perusahaan tersebut
            ->leftJoin('pengajuan_mahasiswa as pm', function ($join) {
                $join->on('pmg.id_pengajuan', '=', 'pm.id_pengajuan')
                     ->where('pm.status_surat_balasan', 'diterima');
            })
            
            // Gabungkan dengan tabel surat_pengajuan, tapi HANYA yang jenisnya "pengantar"
            // Ini buat ngefilter cuma pengajuan yang sudah punya surat pengantar resmi
            ->leftJoin('surat_pengajuan as sp', function ($join) {
                $join->on('pmg.id_pengajuan', '=', 'sp.id_pengajuan')
                     ->where('sp.jenis_surat', 'pengantar');
            })
            
            // Hanya ambil perusahaan yang punya surat pengantar (artinya sudah lengkap berkasnya)
            ->whereNotNull('sp.id_pengajuan')
            
            // Grouping biar hasil count-nya akurat per perusahaan
            ->groupBy('perusahaan.id_perusahaan', 'perusahaan.nama_perusahaan', 'perusahaan.alamat', 'perusahaan.tampilkan_publik')
            
            // Urutkan: yang ditampilkan di publik duluan, lalu yang jumlah mahasiswanya lebih banyak duluan
            ->orderByDesc('tampilkan_publik')
            ->orderByDesc('jumlah_mahasiswa');

        // ============================================
        // FILTER SEARCH: Kalau user ngetik sesuatu di kolom pencarian
        // ============================================
        if (!empty($search)) {
            // Cari berdasarkan nama perusahaan (bisa partial/cuma sebagian kata)
            $query->where('perusahaan.nama_perusahaan', 'like', '%' . $search . '%');
        }

        // ============================================
        // FILTER STATUS TAMPIL: Kalau user milih filter "ditampilkan" atau "disembunyikan"
        // ============================================
        if ($request->filled('filter_tampil')) {
            $query->where('perusahaan.tampilkan_publik', $request->filter_tampil);
        }

        // ============================================
        // PAGINASI: Tentukan ambil semua atau per halaman
        // ============================================
        if ($perPage === 'all') {
            // Kalau user pilih "Semua", ambil semua data sekaligus tanpa paginasi
            $perusahaans = $query->get();
        } else {
            // Kalau user pilih angka (5/10/15), pakai paginasi bawaan Laravel
            // withQueryString() biar filter & search tetap kebawa saat pindah halaman
            $perusahaans = $query->paginate($perPage)->withQueryString();
        }

        // ============================================
        // STATISTIK: Hitung angka-angka buat kartu ringkas di atas tabel
        // Penting: query ini PAKE DATA SEMUA, tanpa filter search/per_page
        // Jadi angka statistiknya gak berubah-ubah walau user lagi search atau pindah halaman
        // ============================================
        $statQuery = Perusahaan::select('perusahaan.*')
            ->selectRaw('COUNT(DISTINCT pm.id_pengajuan_mahasiswa) as jumlah_mahasiswa')
            ->leftJoin('pengajuan_magang as pmg', 'perusahaan.id_perusahaan', '=', 'pmg.id_perusahaan')
            ->leftJoin('pengajuan_mahasiswa as pm', function ($join) {
                $join->on('pmg.id_pengajuan', '=', 'pm.id_pengajuan')
                     ->where('pm.status_surat_balasan', 'diterima');
            })
            ->leftJoin('surat_pengajuan as sp', function ($join) {
                $join->on('pmg.id_pengajuan', '=', 'sp.id_pengajuan')
                     ->where('sp.jenis_surat', 'pengantar');
            })
            ->whereNotNull('sp.id_pengajuan')
            ->groupBy('perusahaan.id_perusahaan', 'perusahaan.nama_perusahaan', 'perusahaan.alamat', 'perusahaan.tampilkan_publik')
            ->get();

        // Total perusahaan yang memenuhi syarat (punya surat pengantar & mahasiswa diterima)
        $totalMemenuhiSyarat = $statQuery->count();
        
        // Total perusahaan yang statusnya "ditampilkan" di halaman publik
        $totalDitampilkan = $statQuery->where('tampilkan_publik', 1)->count();
        
        // Total semua perusahaan yang ada di database (tanpa filter apapun)
        $totalPerusahaan = Perusahaan::count();

        // Kirim semua data ke view
        return view('pages.kaprodi.referensi_perusahaan.index', compact(
            'perusahaans', 'totalPerusahaan', 'totalMemenuhiSyarat', 'totalDitampilkan',
            'perPage', 'search'
        ));
    }

    /**
     * Toggle status tampil/sembunyikan untuk SATU perusahaan
     * 
     * Kalau sekarang ditampilkan -> jadi disembunyikan
     * Kalau sekarang disembunyikan -> jadi ditampilkan
     * Biasanya dipanggil lewat tombol switch/toggle di tabel
     */
    public function toggleTampil(Request $request, $id_perusahaan)
    {
        // Cari perusahaan berdasarkan ID, kalau gak ketemu langsung error 404
        $perusahaan = Perusahaan::findOrFail($id_perusahaan);
        
        // Balikkan nilainya: kalau 1 jadi 0, kalau 0 jadi 1
        $perusahaan->tampilkan_publik = $perusahaan->tampilkan_publik ? 0 : 1;
        $perusahaan->save();

        // Tentukan pesan sukses berdasarkan status baru
        $status = $perusahaan->tampilkan_publik ? 'ditampilkan' : 'disembunyikan';
        return redirect()->back()->with('success', "Perusahaan {$perusahaan->nama_perusahaan} berhasil {$status} di halaman publik");
    }

    /**
     * Ubah status tampil/sembunyikan secara MASSAL (banyak perusahaan sekaligus)
     * 
     * Biasanya dipakai lewat checkbox di tabel, terus ada tombol "Tampilkan yang dipilih"
     * atau "Sembunyikan yang dipilih"
     */
    public function setMassal(Request $request)
    {
        // Validasi input:
        // - action: harus "tampilkan" atau "sembunyikan"
        // - id_perusahaan: harus berupa array, dan setiap isinya harus angka yang ada di tabel perusahaan
        $request->validate([
            'action' => 'required|in:tampilkan,sembunyikan',
            'id_perusahaan' => 'required|array',
            'id_perusahaan.*' => 'required|integer|exists:perusahaan,id_perusahaan'
        ]);

        // Ubah action jadi angka: "tampilkan" -> 1, "sembunyikan" -> 0
        $status = $request->action === 'tampilkan' ? 1 : 0;
        
        // Update semua perusahaan yang ID-nya ada di array sekaligus
        // Menggunakan whereIn biar sekali query, bukan loop update satu-satu
        $updated = Perusahaan::whereIn('id_perusahaan', $request->id_perusahaan)
            ->update(['tampilkan_publik' => $status]);

        // Tentukan pesan sukses berdasarkan action
        $pesan = $request->action === 'tampilkan' 
            ? "{$updated} perusahaan berhasil ditampilkan"
            : "{$updated} perusahaan berhasil disembunyikan";

        return redirect()->back()->with('success', $pesan);
    }
}