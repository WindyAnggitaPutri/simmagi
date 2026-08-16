<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BerkasMagang;
use Illuminate\Support\Facades\Storage;

/**
 * Controller untuk mengelola berkas-berkas magang
 * 
 * Berkas ini bisa diakses oleh 3 role:
 * 1. Kaprodi -> untuk mengelola template berkas
 * 2. Admin -> untuk mengelola template berkas (halaman terpisah)
 * 3. Admin Kajur -> untuk mengelola template berkas (halaman terpisah)
 * 
 * Logikanya sama semua, cuma beda view dan route-nya aja
 */
class BerkasMagangController extends Controller
{
    // =============================================
    // DAFTAR JENIS BERKAS
    // =============================================
    // List jenis berkas yang tersedia (Bisa ditambah suatu saat nanti)
    // Ini dipakai untuk dropdown di form create/edit
    private $jenisBerkas = [
        'Surat Izin Orang Tua',
        'Surat Permohonan',
        'Proposal Magang',
        'Surat Balasan',
        'Sertifikat',
        'Absensi',
        'Dokumen Pendukung Lainnya'
    ];

    // ==================== FITUR KAPRODI ====================

    /**
     * Halaman daftar berkas magang untuk Kaprodi
     * Menampilkan semua berkas yang sudah diupload
     */
    public function index()
    {
        // Ambil semua berkas, diurutkan dari yang terbaru
        $berkasMagang = BerkasMagang::orderBy('created_at', 'desc')->get();
        $jenisBerkas = $this->jenisBerkas; // Kirim array ke view untuk dropdown

        return view('pages.kaprodi.berkas_magang', compact('berkasMagang', 'jenisBerkas'));
    }

    /**
     * Proses upload berkas baru (Kaprodi)
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'nama_berkas'  => 'required|string|max:255',                    // Nama berkas wajib, maks 255 karakter
            'jenis_berkas' => 'required|string|max:255',                    // Jenis berkas wajib, harus dari list di atas
            'file'         => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',  // File wajib, jenis tertentu, maks 2MB
            'deskripsi'    => 'nullable|string',                            // Deskripsi boleh kosong
        ]);

        // Simpan file ke folder storage/app/public/berkas_magang
        // Method store() akan otomatis generate nama file yang unik
        $filePath = $request->file('file')->store('berkas_magang', 'public');

        // Simpan data berkas ke database
        BerkasMagang::create([
            'nama_berkas'  => $request->nama_berkas,
            'jenis_berkas' => $request->jenis_berkas,
            'file'         => $filePath,    // Path relatif dari storage
            'deskripsi'    => $request->deskripsi,
        ]);

        // Redirect ke halaman daftar berkas dengan pesan sukses
        return redirect()->route('kaprodi.berkas-magang')
            ->with('success', 'Berkas berhasil diupload!');
    }

    /**
     * Proses update berkas (Kaprodi)
     */
    public function update(Request $request, $id_berkas_magang)
    {
        // Validasi input dari form
        // Beda dengan store: file-nya nullable (boleh kosong) karena user mungkin cuma mau ganti nama/deskripsi
        $request->validate([
            'nama_berkas'  => 'required|string|max:255',
            'jenis_berkas' => 'required|string|max:255',
            'file'         => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',  // nullable = boleh kosong
            'deskripsi'    => 'nullable|string',
        ]);

        // Cari berkas berdasarkan ID, kalau nggak ketemu langsung 404
        $berkas = BerkasMagang::findOrFail($id_berkas_magang);
        
        // Ambil hanya data yang boleh diupdate (nama, jenis, deskripsi)
        $data = $request->only('nama_berkas', 'jenis_berkas', 'deskripsi');

        // =============================================
        // HANDLE UPLOAD FILE BARU
        // =============================================
        // Kalau user upload file baru, kita perlu:
        // 1. Hapus file lama dari storage
        // 2. Simpan file baru ke storage
        // 3. Update path file di database
        if ($request->hasFile('file')) {
            // Cek apakah file lama ada di storage, kalau ada hapus
            if ($berkas->file && Storage::disk('public')->exists($berkas->file)) {
                Storage::disk('public')->delete($berkas->file);
            }
            // Simpan file baru
            $data['file'] = $request->file('file')->store('berkas_magang', 'public');
        }

        // Update data di database
        $berkas->update($data);

        return redirect()->route('kaprodi.berkas-magang')
            ->with('success', 'Berkas berhasil diperbarui!');
    }

    /**
     * Proses hapus berkas (Kaprodi)
     */
    public function destroy($id_berkas_magang)
    {
        // Cari berkas berdasarkan ID
        $berkas = BerkasMagang::findOrFail($id_berkas_magang);

        // Hapus file fisik dari storage
        // Penting! Kalau cuma hapus dari database, file-nya masih numpuk di server
        if ($berkas->file && Storage::disk('public')->exists($berkas->file)) {
            Storage::disk('public')->delete($berkas->file);
        }

        // Hapus data dari database
        $berkas->delete();

        return redirect()->route('kaprodi.berkas-magang')
            ->with('success', 'Berkas berhasil dihapus!');
    }

/**
 * Halaman detail berkas berdasarkan jenis berkas
 * Dipakai di halaman pengunjung untuk menampilkan template berkas
 */
public function showByJenis($jenis)
{
    // Cari berkas berdasarkan jenis, kalau nggak ketemu langsung 404
    // Ganti get() jadi firstOrFail() agar cuma ambil 1 data
    $berkas = BerkasMagang::where('jenis_berkas', $jenis)->firstOrFail();

    // Ganti nama view-nya sesuai file yang kamu buat
    return view('pages.pengunjung.detail_berkas', compact('berkas'));
}

    // ==================== FITUR ADMIN (HALAMAN TERPISAH) ====================
    // Catatan: Logikanya sama persis dengan fitur Kaprodi di atas
    // Bedanya cuma di view dan route yang dipakai

    /**
     * Halaman daftar berkas untuk Admin
     */
    public function indexAdmin()
    {
        $berkasMagang = BerkasMagang::orderBy('created_at', 'desc')->get();
        return view('pages.admin.berkas_magang.index', compact('berkasMagang'));
    }

    /**
     * Halaman form tambah berkas untuk Admin
     */
    public function createAdmin()
    {
        $jenisBerkas = $this->jenisBerkas;
        return view('pages.admin.berkas_magang.create', compact('jenisBerkas'));
    }

    /**
     * Proses upload berkas baru (Admin)
     * Logikanya sama dengan store() di atas
     */
    public function storeAdmin(Request $request)
    {
        $request->validate([
            'nama_berkas'  => 'required|string|max:255',
            'jenis_berkas' => 'required|string|max:255',
            'file'         => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
            'deskripsi'    => 'nullable|string',
        ]);

        $filePath = $request->file('file')->store('berkas_magang', 'public');

        BerkasMagang::create([
            'nama_berkas'  => $request->nama_berkas,
            'jenis_berkas' => $request->jenis_berkas,
            'file'         => $filePath,
            'deskripsi'    => $request->deskripsi,
        ]);

        return redirect()->route('admin.berkas-magang.index')
            ->with('success', 'Berkas berhasil diupload!');
    }

    /**
     * Halaman form edit berkas untuk Admin
     */
    public function editAdmin($id_berkas_magang)
    {
        $berkas = BerkasMagang::findOrFail($id_berkas_magang);
        $jenisBerkas = $this->jenisBerkas;
        return view('pages.admin.berkas_magang.edit', compact('berkas', 'jenisBerkas'));
    }

    /**
     * Proses update berkas (Admin)
     * Logikanya sama dengan update() di atas
     */
    public function updateAdmin(Request $request, $id_berkas_magang)
    {
        $request->validate([
            'nama_berkas'  => 'required|string|max:255',
            'jenis_berkas' => 'required|string|max:255',
            'file'         => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
            'deskripsi'    => 'nullable|string',
        ]);

        $berkas = BerkasMagang::findOrFail($id_berkas_magang);
        $data = $request->only('nama_berkas', 'jenis_berkas', 'deskripsi');

        // Kalau ada upload file baru, hapus file lama & ganti yang baru
        if ($request->hasFile('file')) {
            if ($berkas->file && Storage::disk('public')->exists($berkas->file)) {
                Storage::disk('public')->delete($berkas->file);
            }
            $data['file'] = $request->file('file')->store('berkas_magang', 'public');
        }

        $berkas->update($data);

        return redirect()->route('admin.berkas-magang.index')
            ->with('success', 'Berkas berhasil diperbarui!');
    }

    /**
     * Proses hapus berkas (Admin)
     * Logikanya sama dengan destroy() di atas
     */
    public function destroyAdmin($id_berkas_magang)
    {
        $berkas = BerkasMagang::findOrFail($id_berkas_magang);

        // Hapus file dari storage
        if ($berkas->file && Storage::disk('public')->exists($berkas->file)) {
            Storage::disk('public')->delete($berkas->file);
        }

        $berkas->delete();

        return redirect()->route('admin.berkas-magang.index')
            ->with('success', 'Berkas berhasil dihapus!');
    }

        // ==================== FITUR ADMIN KAJUR ====================
        // Catatan: Logikanya sama persis dengan fitur Admin di atas
        // Bedanya cuma di view dan route yang dipakai

    /**
     * Halaman daftar berkas untuk Admin Kajur
     */
    public function indexAdminKajur()
    {
        $berkasMagang = BerkasMagang::orderBy('created_at', 'desc')->get();
        $jenisBerkas = $this->jenisBerkas;
        return view('pages.admin_kajur.berkas_magang.index', compact('berkasMagang', 'jenisBerkas'));
    }

    /**
     * Halaman form tambah berkas untuk Admin Kajur
     */
    public function createAdminKajur()
    {
        $jenisBerkas = $this->jenisBerkas;
        return view('pages.admin_kajur.berkas_magang.create', compact('jenisBerkas'));
    }

    /**
     * Proses upload berkas baru (Admin Kajur)
     * Logikanya sama dengan store() dan storeAdmin() di atas
     */
    public function storeAdminKajur(Request $request)
    {
        $request->validate([
            'nama_berkas'  => 'required|string|max:255',
            'jenis_berkas' => 'required|string|max:255',
            'file'         => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
            'deskripsi'    => 'nullable|string',
        ]);

        $filePath = $request->file('file')->store('berkas_magang', 'public');

        BerkasMagang::create([
            'nama_berkas'  => $request->nama_berkas,
            'jenis_berkas' => $request->jenis_berkas,
            'file'         => $filePath,
            'deskripsi'    => $request->deskripsi,
        ]);

        return redirect()->route('admin_kajur.berkas-magang.index')
            ->with('success', 'Berkas berhasil diupload!');
    }

    /**
     * Halaman form edit berkas untuk Admin Kajur
     */
    public function editAdminKajur($id_berkas_magang)
    {
        $berkas = BerkasMagang::findOrFail($id_berkas_magang);
        $jenisBerkas = $this->jenisBerkas;
        return view('pages.admin_kajur.berkas_magang.edit', compact('berkas', 'jenisBerkas'));
    }

    /**
     * Proses update berkas (Admin Kajur)
     * Logikanya sama dengan update() dan updateAdmin() di atas
     */
    public function updateAdminKajur(Request $request, $id_berkas_magang)
    {
        $request->validate([
            'nama_berkas'  => 'required|string|max:255',
            'jenis_berkas' => 'required|string|max:255',
            'file'         => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
            'deskripsi'    => 'nullable|string',
        ]);

        $berkas = BerkasMagang::findOrFail($id_berkas_magang);
        $data = $request->only('nama_berkas', 'jenis_berkas', 'deskripsi');

        // Kalau ada upload file baru, hapus file lama & ganti yang baru
        if ($request->hasFile('file')) {
            if ($berkas->file && Storage::disk('public')->exists($berkas->file)) {
                Storage::disk('public')->delete($berkas->file);
            }
            $data['file'] = $request->file('file')->store('berkas_magang', 'public');
        }

        $berkas->update($data);

        return redirect()->route('admin_kajur.berkas-magang.index')
            ->with('success', 'Berkas berhasil diperbarui!');
    }

    /**
     * Proses hapus berkas (Admin Kajur)
     * Logikanya sama dengan destroy() dan destroyAdmin() di atas
     */
    public function destroyAdminKajur($id_berkas_magang)
    {
        $berkas = BerkasMagang::findOrFail($id_berkas_magang);

        // Hapus file dari storage
        if ($berkas->file && Storage::disk('public')->exists($berkas->file)) {
            Storage::disk('public')->delete($berkas->file);
        }

        $berkas->delete();

        return redirect()->route('admin_kajur.berkas-magang.index')
            ->with('success', 'Berkas berhasil dihapus!');
    }
}