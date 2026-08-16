<?php

namespace App\Http\Controllers;
use Barryvdh\DomPDF\Facade\Pdf; 
use Illuminate\Http\Request;
use App\Models\Logbook;
use App\Models\Dosen;
use App\Models\Notifikasi;
use App\Models\DosenPembimbing;
use App\Models\ApprovalLogbook;
use Illuminate\Support\Facades\Storage;

/**
 * Controller ini ngurus semuanya yang berhubungan dengan Logbook (jurnal harian magang)
 * 
 * Ada 3 sisi pengguna:
 * 1. Dosen Pembimbing (Dospem) -> lihat & approve logbook mahasiswa bimbingannya
 * 2. Mahasiswa -> isi, edit, hapus, dan lihat logbook sendiri
 * 3. Admin -> monitoring semua logbook dari semua mahasiswa
 */
class LogbookController extends Controller
{
    // ==================== DOSEN PEMBIMBING - LOGBOOK ====================

    /**
     * Halaman index Dospem: daftar semua mahasiswa bimbingan + statistik logbook
     * 
     * Dospem bisa:
     * - Lihat daftar mahasiswa yang dia bimbing
     * - Search berdasarkan nama/npm mahasiswa atau nama perusahaan
     * - Lihat statistik per mahasiswa (total logbook, pending, disetujui, ditolak)
     * - Lihat statistik total (jumlah mahasiswa bimbingan & total logbook pending)
     */
    public function indexDospem(Request $request)
    {
        // Ambil data dosen yang sedang login
        $user = auth()->user();
        $dosen = Dosen::where('id_user', $user->id_user)->first();

        // Ambil parameter pencarian dan jumlah data per halaman dari request
        $search = $request->input('search');
        $perPage = $request->input('per_page', 5);

        // Query dasar: ambil data mahasiswa bimbingan dosen ini
        // Relasi yang di-load: mahasiswa (info mahasiswa) dan pengajuanMagang.perusahaan (tempat magang)
        $query = DosenPembimbing::where('id_dosen', $dosen->id_dosen)
            ->with(['mahasiswa', 'pengajuanMagang.perusahaan']);

        // Filter search: cari di nama/npm mahasiswa ATAU nama perusahaan
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('mahasiswa', function ($q2) use ($search) {
                    $q2->where('nama', 'like', '%' . $search . '%')
                       ->orWhere('npm', 'like', '%' . $search . '%');
                })
                ->orWhereHas('pengajuanMagang.perusahaan', function ($q2) use ($search) {
                    $q2->where('nama_perusahaan', 'like', '%' . $search . '%');
                });
            });
        }

        // Paginate dengan membawa query string (biar filter & search tetap aktif saat pindah halaman)
        $mahasiswaBimbingan = $query->paginate($perPage)->withQueryString();

        // Tambahkan statistik logbook untuk SETIAP mahasiswa yang tampil di halaman ini
        // Jadi di tabel nanti bisa ditampilkan kolom: total, pending, disetujui, ditolak, tanggal terakhir
        $mahasiswaBimbingan->each(function($item) {
            // Ambil semua logbook milik mahasiswa ini
            $logbooks = Logbook::where('id_dosen_pembimbing', $item->id_dosen_pembimbing)->get();
            
            // Hitung masing-masing status
            $item->total_logbook = $logbooks->count();
            $item->logbook_pending = $logbooks->where('status', 'pending')->count();
            $item->logbook_disetujui = $logbooks->where('status', 'disetujui')->count();
            $item->logbook_ditolak = $logbooks->where('status', 'ditolak')->count();
            $item->last_logbook_date = $logbooks->max('tanggal');
        });

        // Statistik TOTAL: dihitung dari SEMUA mahasiswa bimbingan, bukan cuma yang di halaman ini
        // Jadi angkanya gak berubah walau user pindah halaman atau search
        $semuaIdDosenPembimbing = DosenPembimbing::where('id_dosen', $dosen->id_dosen)->pluck('id_dosen_pembimbing');
        $totalMhs = $semuaIdDosenPembimbing->count();
        $totalPending = Logbook::whereIn('id_dosen_pembimbing', $semuaIdDosenPembimbing)->where('status', 'pending')->count();

        return view('pages.dospem.logbook.index', compact('dosen', 'mahasiswaBimbingan', 'search', 'perPage', 'totalMhs', 'totalPending'));
    }

    /**
     * Halaman detail Dospem: lihat semua logbook dari SATU mahasiswa bimbingan
     * 
     * Dospem bisa melihat logbook per-mahasiswa, dikelompokkan per minggu
     * Dari sini dospem bisa klik logbook untuk detail lalu approve/tolak
     */
    public function detailDospem($id_dosen_pembimbing)
    {
        // Ambil data dosen yang login
        $user = auth()->user();
        $dosen = Dosen::where('id_user', $user->id_user)->first();

        // Ambil data dosen pembimbing, tapi PASTIKAN ini memang bimbingan dosen yang login
        // Kalau bukan -> 404 (keamanan, biar dosen gak bisa lihat mahasiswa orang lain)
        $dosenPembimbing = DosenPembimbing::where('id_dosen_pembimbing', $id_dosen_pembimbing)
            ->where('id_dosen', $dosen->id_dosen)
            ->with(['mahasiswa', 'pengajuanMagang.perusahaan'])
            ->firstOrFail();

        // Ambil semua logbook mahasiswa ini, diurutkan dari minggu awal ke akhir, lalu per tanggal
        $logbooks = Logbook::where('id_dosen_pembimbing', $id_dosen_pembimbing)
            ->orderBy('minggu_ke', 'asc')
            ->orderBy('tanggal', 'asc')
            ->get();

        // Kelompokkan logbook berdasarkan minggu_ke
        // Jadi nanti di view bisa ditampilkan per "Minggu ke-1", "Minggu ke-2", dst
        $groupedByMinggu = $logbooks->groupBy('minggu_ke');

        return view('pages.dospem.logbook.per_mahasiswa', compact('dosen', 'dosenPembimbing', 'logbooks', 'groupedByMinggu'));
    }

    /**
     * Halaman index Mahasiswa: daftar semua minggu logbook yang sudah/tidak diisi
     * 
     * Mahasiswa melihat ringkasan per minggu, bukan per entri harian
     * Juga dicek apakah mahasiswa sudah memenuhi syarat semester untuk mengisi logbook
     */
    public function indexMahasiswa()
    {
        // Ambil data mahasiswa yang login, beserta relasi prodi-nya
        $user = auth()->user();
        $mahasiswa = \App\Models\Mahasiswa::with('prodi')->where('id_user', $user->id_user)->first();

        // CEK: Apakah mahasiswa sudah di semester yang diizinkan untuk mengisi logbook?
        // (Logbook biasanya baru dibuka 1 semester sebelum semester magang)
        $statusSemester = $this->cekSyaratSemesterLogbook($mahasiswa);

        // Ambil data dosen pembimbing mahasiswa ini (untuk info di halaman)
        $dosenPembimbing = \App\Models\DosenPembimbing::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->with('dosen')
            ->first();

        // Ambil semua logbook mahasiswa ini, diurutkan per minggu lalu per tanggal
        $logbooks = Logbook::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->orderBy('minggu_ke', 'asc')
            ->orderBy('tanggal', 'asc')
            ->get();

        // Kelompokkan per minggu buat ditampilkan di view
        $groupedByMinggu = $logbooks->groupBy('minggu_ke');

        return view('pages.mahasiswa.logbook.index', compact('mahasiswa', 'groupedByMinggu', 'dosenPembimbing', 'statusSemester'));
    }

    /**
     * Halaman detail per minggu dari sisi Mahasiswa
     * 
     * Menampilkan semua entri harian di minggu tertentu
     * Juga dicek apakah minggu ini sudah di-approve mingguan oleh dosen
     * (kalau sudah di-approve, mahasiswa gak bisa tambah/edit logbook di minggu itu)
     */
    public function showMingguMahasiswa($minggu)
    {
        $user = auth()->user();
        $mahasiswa = \App\Models\Mahasiswa::where('id_user', $user->id_user)->first();

        // Ambil semua logbook di minggu ini, urutkan per tanggal
        $logbooks = Logbook::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->where('minggu_ke', $minggu)
            ->orderBy('tanggal', 'asc')
            ->get();

        // CEK: Apakah minggu ini sudah di-approve secara mingguan oleh dosen?
        // Approval mingguan berbeda dengan approval harian:
        // - Approval harian: dosen approve SATU-SATU entri logbook
        // - Approval mingguan: dosen approve SELURUH minggu (setelah semua harian disetujui)
        $isMingguDisetujui = ApprovalLogbook::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->where('minggu_ke', $minggu)
            ->where('status', 'disetujui')
            ->exists();

        return view('pages.mahasiswa.logbook.minggu', compact('mahasiswa', 'logbooks', 'minggu', 'isMingguDisetujui'));
    }

    /**
     * Form tambah logbook baru dari sisi Mahasiswa
     * 
     * Di sini ada banyak logic penentuan "minggu ke berapa" yang boleh diisi:
     * - Kalau belum ada logbook sama sekali -> minggu 1
     * - Kalau minggu terakhir SUDAH di-approve -> lanjut ke minggu berikutnya
     * - Kalau minggu terakhir BELUM di-approve -> tetap di minggu yang sama
     * - Kalau minggu yang akan diisi sudah punya 7 entri -> diblokir, harus nunggu approve dulu
     * 
     * Juga ada pengecekan syarat semester (gak boleh isi kalau belum di semester yang ditentukan)
     */
    public function createMahasiswa()
    {
        $user = auth()->user();
        $mahasiswa = \App\Models\Mahasiswa::with('prodi')->where('id_user', $user->id_user)->first();
        
        // BLOKIR: Kalau mahasiswa belum di semester yang diizinkan, langsung tendang
        $statusSemester = $this->cekSyaratSemesterLogbook($mahasiswa);
        if (!$statusSemester['boleh_tambah']) {
            return redirect()->route('mahasiswa.logbook.index')
                ->with('error', 'Anda belum memasuki semester yang diizinkan untuk membuat logbook. Logbook dibuka mulai Semester ' . $statusSemester['min_semester'] . '.');
        }

        // Ambil data dosen pembimbing untuk info di form
        $dosenPembimbing = \App\Models\DosenPembimbing::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->first();

        // LOGIKA PENENTUAN MINGGU:
        // Cari logbook terakhir yang pernah diisi mahasiswa ini
        $lastLogbook = Logbook::where('id_mahasiswa', $mahasiswa->id_mahasiswa)->orderBy('minggu_ke', 'desc')->first();
        $nextMinggu = 1; // Default: mulai dari minggu 1
        $isFull = false; // Apakah minggu ini sudah penuh 7 hari?
        $infoMessage = null; // Pesan info yang akan ditampilkan ke mahasiswa
        
        if ($lastLogbook) {
            // Cek: apakah minggu terakhir yang diisi SUDAH di-approve mingguan oleh dosen?
            $lastMingguApproved = \App\Models\ApprovalLogbook::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
                ->where('minggu_ke', $lastLogbook->minggu_ke)
                ->where('status', 'disetujui')
                ->exists();
            
            if ($lastMingguApproved) {
                // Kalau sudah di-approve -> mahasiswa boleh lanjut ke minggu berikutnya
                $nextMinggu = $lastLogbook->minggu_ke + 1;
                // Kasih pesan info biar mahasiswa paham kenapa dia diarahkan ke minggu baru
                $infoMessage = 'Minggu ke-' . $lastLogbook->minggu_ke . ' sudah di-approve oleh Dosen. Anda tidak dapat menambah logbook lagi di minggu tersebut. Silakan lanjutkan di Minggu ke-' . $nextMinggu . '.';
            } else {
                // Kalau belum di-approve -> mahasiswa tetap di minggu yang sama (bisa nambah entri harian)
                $nextMinggu = $lastLogbook->minggu_ke;
            }
        }

        // CEK: Apakah minggu yang akan diisi sudah punya 7 entri? (1 minggu = 7 hari maksimal)
        $jumlahHariIni = Logbook::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->where('minggu_ke', $nextMinggu)
            ->count();

        if ($jumlahHariIni >= 7) {
            // Kalau sudah 7 -> blokir, kasih pesan biar tunggu approve dosen dulu
            $isFull = true;
            $infoMessage = 'Minggu ke-' . $nextMinggu . ' sudah berisi 7 hari logbook. Harap tunggu Dosen Pembimbing melakukan approve mingguan sebelum Anda bisa melanjutkan ke minggu berikutnya.';
        }

        // Cek apakah datang dari absen pulang (untuk pre-fill form)
// Cek apakah datang dari absen pulang (untuk pre-fill form)
 $prefillData = null;

// Pertama cek session flash (dari redirect setelah absen pulang)
if (session()->has('from_absen_pulang')) {
    $prefillData = [
        'tanggal' => session('absen_tanggal'),
        'jam_mulai' => session('absen_jam_mulai'),
        'jam_selesai' => session('absen_jam_selesai'),
    ];
} else {
    // Fallback: jika tidak ada session, cek absensi lengkap hari ini
    $absenHariIni = \App\Models\Absensi::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
        ->whereDate('tanggal', \Carbon\Carbon::today())
        ->where('status', 'hadir')
        ->whereNotNull('jam_pulang')
        ->first();
    
    if ($absenHariIni) {
        $prefillData = [
            'tanggal' => $absenHariIni->tanggal->format('Y-m-d'),
            'jam_mulai' => $absenHariIni->jam_masuk->format('H:i'),
            'jam_selesai' => $absenHariIni->jam_pulang->format('H:i'),
        ];
    }
}

return view('pages.mahasiswa.logbook.create', compact(
    'mahasiswa', 'dosenPembimbing', 'nextMinggu', 'isFull', 'infoMessage', 'prefillData'
));
    }

    /**
     * Proses menyimpan logbook baru dari sisi Mahasiswa
     * 
     * Validasinya KETAT, ada 4 lapis:
     * 1. Kalau mengisi minggu > 1, minggu sebelumnya HARUS sudah di-approve dosen
     * 2. Gak boleh tambah logbook di minggu yang SUDAH di-approve
     * 3. Maksimal 7 entri per minggu
     * 4. Tidak boleh tanggal yang sama dalam 1 minggu
     */
    public function storeMahasiswa(Request $request)
    {
        // Validasi input dasar dari form
        $request->validate([
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai', // Jam selesai harus setelah jam mulai
            'minggu_ke' => 'required|integer',
            'kegiatan' => 'required|string',
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Foto wajib, maks 2MB
            'ttd_setuju' => 'accepted', // Checkbox persetujuan harus dicentang
        ]);

        $user = auth()->user();
        $mahasiswa = \App\Models\Mahasiswa::where('id_user', $user->id_user)->first();
        $dosenPembimbing = \App\Models\DosenPembimbing::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->first();

        // ============================================
        // VALIDASI KETAT 1: Berurutan!
        // Kalau mengisi minggu > 1, cek apakah minggu SEBELUMNYA sudah di-approve dosen
        // Tujuannya: mahasiswa gak bisa loncat-loncat minggu
        // ============================================
        if ($request->minggu_ke > 1) {
            $prevWeekApproved = \App\Models\ApprovalLogbook::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
                ->where('minggu_ke', $request->minggu_ke - 1)
                ->where('status', 'disetujui')
                ->exists();

            if (!$prevWeekApproved) {
                return redirect()->route('mahasiswa.logbook.index')
                    ->with('error', 'Anda tidak bisa mengisi Minggu ke-' . $request->minggu_ke . '. Minggu ke-' . ($request->minggu_ke - 1) . ' belum disetujui Dosen Pembimbing.');
            }
        }

        // ============================================
        // VALIDASI KETAT 2: Gak boleh tambah di minggu yang sudah di-approve
        // Kalau dosen sudah approve mingguan -> minggu itu dikunci
        // ============================================
        $currentWeekApproved = \App\Models\ApprovalLogbook::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->where('minggu_ke', $request->minggu_ke)
            ->where('status', 'disetujui')
            ->exists();
            
        if ($currentWeekApproved) {
            return redirect()->route('mahasiswa.logbook.index')
                ->with('error', 'Minggu ke-' . $request->minggu_ke . ' sudah di-approve Dosen. Anda tidak bisa menambah logbook lagi untuk minggu ini.');
        }

        // ============================================
        // VALIDASI KETAT 3: Maksimal 7 entri per minggu (1 minggu = 7 hari)
        // ============================================
        $jumlahLogbookMingguIni = Logbook::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->where('minggu_ke', $request->minggu_ke)
            ->count();

        if ($jumlahLogbookMingguIni >= 7) {
            return redirect()->route('mahasiswa.logbook.index')
                ->with('error', 'Minggu ke-' . $request->minggu_ke . ' sudah memiliki 7 catatan harian. Anda tidak bisa menambah hari lagi di minggu ini.');
        }

        // ============================================
        // VALIDASI KETAT 4: Tidak boleh tanggal yang sama dalam 1 minggu
        // Jadi dalam 1 minggu, setiap tanggal harus unik
        // ============================================
        $tanggalSudahAda = Logbook::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->where('minggu_ke', $request->minggu_ke)
            ->where('tanggal', $request->tanggal)
            ->exists();

        if ($tanggalSudahAda) {
            return back()->with('error', 'Anda sudah mengisi logbook untuk tanggal ini pada Minggu ke-' . $request->minggu_ke . '. Dalam 1 minggu, tanggal tidak boleh sama.')->withInput();
        }

        // Proses upload foto jika ada
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('logbook_fotos', 'public');
        }

        // Simpan logbook baru dengan status "pending" (menunggu approve dosen)
        Logbook::create([
            'id_mahasiswa' => $mahasiswa->id_mahasiswa,
            'id_dosen_pembimbing' => $dosenPembimbing ? $dosenPembimbing->id_dosen_pembimbing : null,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'minggu_ke' => $request->minggu_ke,
            'kegiatan' => $request->kegiatan,
            'foto' => $fotoPath,
            'status' => 'pending',
        ]);

        // Redirect ke halaman minggu yang baru diisi
        // return redirect()->route('mahasiswa.logbook.minggu', $request->minggu_ke)
        //     ->with('success', 'Logbook berhasil ditambahkan!');
        // Yang baru (ngarah ke absensi index)
return redirect()->route('mahasiswa.absensi.index')
    ->with('success', 'Logbook berhasil ditambahkan!');
    }

    /**
     * Form Edit Logbook dari sisi Mahasiswa
     * 
     * Mahasiswa bisa edit logbook yang masih pending atau ditolak
     * Logbook yang sudah disetujui TIDAK bisa diedit (dicek di method update)
     */
    public function editMahasiswa($id_logbook)
    {
        $user = auth()->user();
        $mahasiswa = \App\Models\Mahasiswa::where('id_user', $user->id_user)->first();
        
        // Ambil logbook, tapi PASTIKAN ini milik mahasiswa yang login
        $logbook = Logbook::where('id_logbook', $id_logbook)
            ->where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->firstOrFail();

        return view('pages.mahasiswa.logbook.edit', compact('mahasiswa', 'logbook'));
    }

    /**
     * Proses update logbook dari sisi Mahasiswa
     * 
     * Yang berubah: data logbook di-update, status di-reset jadi "pending" lagi
     * Kenapa di-reset? Karena kontennya berubah, jadi perlu approve ulang dari dosen
     */
    public function updateMahasiswa(Request $request, $id_logbook)
    {
        // Validasi input (sama seperti store, tapi tanpa minggu_ke dan ttd_setuju karena gak diubah)
        $request->validate([
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'kegiatan' => 'required|string',
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = auth()->user();
        $mahasiswa = \App\Models\Mahasiswa::where('id_user', $user->id_user)->first();

        // Pastikan logbook ini milik mahasiswa yang login
        $logbook = Logbook::where('id_logbook', $id_logbook)
            ->where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->firstOrFail();

        // BLOKIR: Logbook yang sudah disetujui TIDAK boleh diedit
        if ($logbook->status === 'disetujui') {
            return back()->with('error', 'Logbook yang sudah disetujui tidak bisa diedit!');
        }

        // VALIDASI: Tanggal tidak boleh sama dalam 1 minggu
        // Tapi abaikan logbook yang sedang diedit ini (karena tanggalnya mungkin gak berubah)
        $tanggalSudahAda = Logbook::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->where('minggu_ke', $logbook->minggu_ke)
            ->where('tanggal', $request->tanggal)
            ->where('id_logbook', '!=', $id_logbook)
            ->exists();

        if ($tanggalSudahAda) {
            return back()->with('error', 'Tanggal ini sudah digunakan pada entri lain di Minggu ke-' . $logbook->minggu_ke . '. Dalam 1 minggu, tanggal tidak boleh sama.')->withInput();
        }

        // Siapkan data yang akan di-update
        $data = [
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'kegiatan' => $request->kegiatan,
            'status' => 'pending', // Status di-reset ke pending karena isinya berubah
        ];

        // Kalau ada foto baru, hapus foto lama lalu upload yang baru
        if ($request->hasFile('foto')) {
            // Hapus foto lama dari storage kalau ada
            if ($logbook->foto && Storage::exists('public/' . $logbook->foto)) {
                Storage::delete('public/' . $logbook->foto);
            }
            $data['foto'] = $request->file('foto')->store('logbook_fotos', 'public');
        }

        $logbook->update($data);

        return redirect()->route('mahasiswa.logbook.minggu', $logbook->minggu_ke)
            ->with('success', 'Logbook berhasil diperbarui!');
    }

    /**
     * Hapus logbook dari sisi Mahasiswa
     * 
     * Syarat: logbook yang sudah disetujui TIDAK boleh dihapus
     * Jika ada foto, foto juga ikut dihapus dari storage
     */
    public function destroyMahasiswa($id_logbook)
    {
        $user = auth()->user();
        $mahasiswa = \App\Models\Mahasiswa::where('id_user', $user->id_user)->first();

        // Pastikan logbook ini milik mahasiswa yang login
        $logbook = Logbook::where('id_logbook', $id_logbook)
            ->where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->firstOrFail();

        // BLOKIR: Logbook yang sudah disetujui TIDAK boleh dihapus
        if ($logbook->status === 'disetujui') {
            return back()->with('error', 'Logbook yang sudah disetujui tidak bisa dihapus!');
        }

        // Hapus foto dari storage jika ada
        if ($logbook->foto && Storage::exists('public/' . $logbook->foto)) {
            Storage::delete('public/' . $logbook->foto);
        }

        $logbook->delete();

        // Redirect ke halaman index (daftar minggu) bukan ke halaman minggu
        return redirect()->route('mahasiswa.logbook.index')
            ->with('success', 'Logbook berhasil dihapus!');
    }

    /**
     * Halaman detail SATU entri logbook dari sisi Mahasiswa
     * 
     * Menampilkan detail lengkap: tanggal, jam, kegiatan, foto, status, catatan dosen
     */
    public function detailMahasiswa($id_logbook)
    {
        $user = auth()->user();
        $mahasiswa = \App\Models\Mahasiswa::where('id_user', $user->id_user)->first();

        // Ambil logbook, pastikan ini milik mahasiswa yang login
        // Load relasi dosenPembimbing.dosen untuk info tambahan (siapa dosen pembimbingnya)
        $logbook = Logbook::where('id_logbook', $id_logbook)
            ->where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->with(['dosenPembimbing.dosen'])
            ->firstOrFail();

        return view('pages.mahasiswa.logbook.detail', compact('mahasiswa', 'logbook'));
    }

    /**
     * Halaman rekap logbook per minggu dari sisi Mahasiswa
     * 
     * Rekap ini hanya bisa diakses SETELAH dosen melakukan approve mingguan
     * Biasanya berupa halaman yang rapi untuk dicetak/diunduh sebagai bukti
     */
    public function rekapMingguMahasiswa(Request $request, $minggu)
    {
        $user = auth()->user();
        $mahasiswa = \App\Models\Mahasiswa::where('id_user', $user->id_user)->first();

        // Ambil semua logbook di minggu ini
        $logbooks = Logbook::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->where('minggu_ke', $minggu)
            ->orderBy('tanggal', 'asc')
            ->get();

        // CEK: Rekap hanya bisa diakses kalau minggu ini SUDAH di-approve dosen
        $isMingguDisetujui = ApprovalLogbook::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->where('minggu_ke', $minggu)
            ->where('status', 'disetujui')
            ->exists();

        if (!$isMingguDisetujui) {
            return redirect()->route('mahasiswa.logbook.minggu', $minggu)
                ->with('error', 'Rekap logbook hanya bisa diakses setelah Dosen Pembimbing melakukan approve mingguan untuk Minggu ke-' . $minggu . '.');
        }

        // Ambil data pendukung untuk ditampilkan di rekap (dosen, perusahaan, dll)
        $dosenPembimbing = \App\Models\DosenPembimbing::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->with('dosen')
            ->first();

        $pengajuanMagang = $dosenPembimbing ? $dosenPembimbing->pengajuanMagang : null;
        $perusahaan = $pengajuanMagang ? $pengajuanMagang->perusahaan : null;

        // Hitung tanggal mulai dan selesai dari logbook minggu ini
        $tanggalMulai = $logbooks->min('tanggal');
        $tanggalSelesai = $logbooks->max('tanggal');

        // URL untuk tombol "Kembali" -> ke halaman minggu mahasiswa
        $backUrl = route('mahasiswa.logbook.minggu', $minggu);

        return view('pages.mahasiswa.logbook.rekap_minggu', compact(
            'mahasiswa', 'logbooks', 'minggu', 'dosenPembimbing', 
            'pengajuanMagang', 'perusahaan', 'tanggalMulai', 'tanggalSelesai', 'backUrl'
        ));
    }

    /**
     * Halaman detail SATU entri logbook dari sisi Dosen Pembimbing
     * 
     * Dari sini dosen bisa melihat detail lalu menekan tombol setujui/tolak
     */
    public function detailLogbookDospem($id_logbook)
    {
        $user = auth()->user();
        $dosen = Dosen::where('id_user', $user->id_user)->first();

        // Ambil logbook, tapi PASTIKAN ini logbook mahasiswa yang dibimbing dosen yang login
        // whereHas dipakai untuk cek relasi: logbook -> dosenPembimbing -> dosen
        $logbook = Logbook::where('id_logbook', $id_logbook)
            ->whereHas('dosenPembimbing', function($q) use ($dosen) {
                $q->where('id_dosen', $dosen->id_dosen);
            })
            ->with(['dosenPembimbing.dosen', 'dosenPembimbing.mahasiswa'])
            ->firstOrFail();

        return view('pages.dospem.logbook.detail_logbook_mahasiswa', compact('logbook', 'dosen'));
    }

    /**
     * Proses konfirmasi (setujui/tolak) SATU entri logbook dari sisi Dosen Pembimbing
     * 
     * Setelah konfirmasi, dikirim notifikasi ke mahasiswa
     * Catatan wajib diisi kalau menolak
     */
    public function konfirmasiLogbookDospem(Request $request, $id_logbook)
    {
        // Validasi: status harus "disetujui" atau "ditolak", catatan opsional
        $request->validate([
            'status' => 'required|in:disetujui,ditolak',
            'catatan' => 'nullable|string',
        ]);

        $user = auth()->user();
        $dosen = Dosen::where('id_user', $user->id_user)->first();

        // Pastikan logbook ini milik mahasiswa bimbingan dosen yang login
        $logbook = Logbook::where('id_logbook', $id_logbook)
            ->whereHas('dosenPembimbing', function($q) use ($dosen) {
                $q->where('id_dosen', $dosen->id_dosen);
            })
            ->firstOrFail();

        // Cegah double confirm: kalau statusnya bukan pending lagi, artinya sudah diproses
        if ($logbook->status !== 'pending') {
            return back()->with('error', 'Logbook ini sudah diproses sebelumnya!');
        }

        // Kalau menolak, catatan WAJIB diisi (supaya mahasiswa tahu alasannya)
        if ($request->status === 'ditolak' && empty($request->catatan)) {
            return back()->with('error', 'Alasan penolakan wajib diisi!')->withInput();
        }

        // Update status dan catatan di logbook
        $logbook->update([
            'status' => $request->status,
            'catatan' => $request->catatan ?? $logbook->catatan,
        ]);

        // ===================== KIRIM NOTIFIKASI KE MAHASISWA ====================
        // Setelah dosen approve/tolak, mahasiswa dapet notifikasi real-time
        $logbook->load('mahasiswa');
        if ($logbook->mahasiswa && $logbook->mahasiswa->id_user) {
            if ($request->status === 'disetujui') {
                Notifikasi::kirim(
                    $logbook->mahasiswa->id_user,
                    'Logbook Disetujui',
                    'Logbook tanggal ' . $logbook->tanggal->format('d M Y') . ' (Minggu ke-' . $logbook->minggu_ke . ') telah disetujui oleh Dosen Pembimbing.',
                    'logbook_disetujui',
                    $logbook->minggu_ke
                );
            } else {
                Notifikasi::kirim(
                    $logbook->mahasiswa->id_user,
                    'Logbook Ditolak',
                    'Logbook tanggal ' . $logbook->tanggal->format('d M Y') . ' (Minggu ke-' . $logbook->minggu_ke . ') ditolak. Alasan: ' . ($request->catatan ?? '-'),
                    'logbook_ditolak',
                    $logbook->minggu_ke
                );
            }
        }
        // =======================================================================

        $pesan = $request->status === 'disetujui' 
            ? 'Logbook berhasil disetujui!' 
            : 'Logbook berhasil ditolak.';

        // Redirect kembali ke halaman detail mahasiswa bimbingan
        return redirect()->route('dospem.logbook.per_mahasiswa', $logbook->id_dosen_pembimbing)
            ->with('success', $pesan);
    }

    /**
     * Approve MINGGUAN dari sisi Dosen Pembimbing
     * 
     * Berbeda dengan konfirmasi harian (satu-satu), ini approve SELURUH minggu
     * Syaratnya: SEMUA logbook harian di minggu itu harus sudah disetujui dulu
     * 
     * Setelah approve mingguan, mahasiswa bisa lanjut mengisi minggu berikutnya
     */
    public function approveMingguDospem(Request $request, $id_dosen_pembimbing, $minggu)
    {
        $user = auth()->user();
        $dosen = Dosen::where('id_user', $user->id_user)->first();

        // Pastikan ini memang mahasiswa bimbingan dosen yang login
        $dosenPembimbing = DosenPembimbing::where('id_dosen_pembimbing', $id_dosen_pembimbing)
            ->where('id_dosen', $dosen->id_dosen)
            ->firstOrFail();

        // Ambil semua logbook harian di minggu ini
        $logbooksMingguIni = Logbook::where('id_dosen_pembimbing', $id_dosen_pembimbing)
            ->where('minggu_ke', $minggu)
            ->get();

        // CEK KRUSIAL: Semua logbook harian harus SUDAH disetujui
        // Kalau ada 1 aja yang belum -> tolak
        $semuaHarianDisetujui = $logbooksMingguIni->every(function ($log) {
            return $log->status === 'disetujui';
        });

        if (!$semuaHarianDisetujui) {
            return back()->with('error', "Semua logbook harian untuk Minggu ke-$minggu harus disetujui terlebih dahulu sebelum mengapprove mingguan!");
        }

        // Cegah double approve: kalau minggu ini sudah di-approve sebelumnya -> tolak
        $sudahApprove = \App\Models\ApprovalLogbook::where('id_dosen_pembimbing', $id_dosen_pembimbing)
            ->where('minggu_ke', $minggu)
            ->where('status', 'disetujui')
            ->exists();

        if ($sudahApprove) {
            return back()->with('error', "Minggu ke-$minggu sudah di-approve sebelumnya!");
        }

        // Simpan record approval mingguan ke tabel approval_logbook
        // Pakai updateOrCreate biar kalau ada record lama (misalnya statusnya ditolak sebelumnya), langsung di-update
        \App\Models\ApprovalLogbook::updateOrCreate(
            [
                'id_dosen_pembimbing' => $id_dosen_pembimbing,
                'minggu_ke' => $minggu,
            ],
            [
                'id_mahasiswa' => $dosenPembimbing->id_mahasiswa,
                'status' => 'disetujui',
                'approved_at' => now(),
                'catatan' => null,
            ]
        );

        // ===================== KIRIM NOTIFIKASI KE MAHASISWA ====================
        // Kasih tau mahasiswa kalau minggunya sudah di-approve, jadi dia bisa lanjut ke minggu berikutnya
        $dosenPembimbing->load('mahasiswa');
        if ($dosenPembimbing->mahasiswa && $dosenPembimbing->mahasiswa->id_user) {
            Notifikasi::kirim(
                $dosenPembimbing->mahasiswa->id_user,
                'Logbook Minggu Ke-' . $minggu . ' Diapprove',
                'Semua logbook Minggu ke-' . $minggu . ' telah di-approve oleh Dosen Pembimbing. Anda dapat melanjutkan pengisian logbook Minggu ke-' . ($minggu + 1) . '.',
                'logbook_minggu',
                $minggu
            );
        }
        // =======================================================================

        return back()->with('success', "Minggu ke-$minggu berhasil di-approve. Mahasiswa kini bisa melanjutkan pengisian logbook.");
    }

    /**
     * Halaman rekap logbook per minggu dari sisi Dosen Pembimbing
     * 
     * Sama seperti rekapMingguMahasiswa, tapi diakses dari sisi dosen
     * Hanya bisa diakses kalau minggu sudah di-approve
     */
    public function rekapMingguDospem($id_dosen_pembimbing, $minggu)
    {
        $user = auth()->user();
        $dosen = Dosen::where('id_user', $user->id_user)->firstOrFail();

        // Ambil data dosen pembimbing + relasi mahasiswa, dosen, perusahaan
        $dosenPembimbing = DosenPembimbing::where('id_dosen_pembimbing', $id_dosen_pembimbing)
            ->where('id_dosen', $dosen->id_dosen)
            ->with(['mahasiswa.prodi', 'dosen', 'pengajuanMagang.perusahaan'])
            ->firstOrFail();

        $mahasiswa = $dosenPembimbing->mahasiswa;

        // Ambil logbook di minggu ini
        $logbooks = Logbook::where('id_dosen_pembimbing', $id_dosen_pembimbing)
            ->where('minggu_ke', $minggu)
            ->orderBy('tanggal', 'asc')
            ->get();

        // CEK: Rekap hanya bisa diakses kalau minggu sudah di-approve
        $isMingguDisetujui = ApprovalLogbook::where('id_dosen_pembimbing', $id_dosen_pembimbing)
            ->where('minggu_ke', $minggu)
            ->where('status', 'disetujui')
            ->exists();

        if (!$isMingguDisetujui) {
            return back()->with('error', 'Rekap logbook hanya bisa diakses untuk minggu yang sudah di-approve.');
        }

        // Data pendukung untuk rekap
        $pengajuanMagang = $dosenPembimbing->pengajuanMagang;
        $perusahaan = $pengajuanMagang ? $pengajuanMagang->perusahaan : null;

        $tanggalMulai = $logbooks->min('tanggal');
        $tanggalSelesai = $logbooks->max('tanggal');

        // URL untuk tombol "Kembali" -> ke halaman detail dospem
        $backUrl = route('dospem.logbook.per_mahasiswa', $id_dosen_pembimbing);

        return view('pages.mahasiswa.logbook.rekap_minggu', compact(
            'mahasiswa', 'logbooks', 'minggu', 'dosenPembimbing', 
            'pengajuanMagang', 'perusahaan', 'tanggalMulai', 'tanggalSelesai', 'backUrl'
        ));
    }

    // ==================== HELPER: CEK SYARAT SEMESTER LOGBOOK ====================

    /**
     * Helper function untuk mengecek apakah mahasiswa sudah boleh mengisi logbook
     * 
     * Aturannya: Logbook boleh diisi mulai 1 SEMESTER SEBELUM semester magang
     * Tiap prodi punya aturan berbeda:
     * - Teknik Informatika: magang semester 5, logbook mulai semester 4
     * - Rekayasa Keamanan Siber: magang semester 6, logbook mulai semester 5
     * - Analisis Kecerdasan Buatan: magang semester 7, logbook mulai semester 6
     * - Teknologi Rekayasa Multimedia: magang semester 7, logbook mulai semester 6
     */
    private function cekSyaratSemesterLogbook($mahasiswa)
    {
        // Pastikan relasi prodi sudah di-load
        if (!$mahasiswa->relationLoaded('prodi')) {
            $mahasiswa->load('prodi');
        }

        $namaProdi = strtoupper($mahasiswa->prodi->nama_prodi ?? '');
        $semesterSekarang = $mahasiswa->semester;

        // Pemetaan: nama prodi -> semester minimal boleh isi logbook
        // Ada nama lengkap dan singkatannya
        $aturan = [
            'TEKNIK INFORMATIKA' => 4,
            'TI'                 => 4,
            'REKAYASA KEAMANAN SIBER' => 5,
            'RKS'                => 5,
            'ANALISIS KECERDASAN BUATAN' => 6,
            'ALKS'               => 6,
            'TEKNOLOGI REKAYASA MULTIMEDIA' => 6,
            'TRM'                => 6,
        ];

        // Default: 99 (artinya gak ada aturan yang cocok, jadi gak boleh isi)
        $minSemester = 99; 

        // Cari aturan yang cocok berdasarkan nama prodi
        foreach ($aturan as $key => $minSem) {
            if (strpos($namaProdi, $key) !== false) {
                $minSemester = $minSem;
                break;
            }
        }

        return [
            'boleh_tambah'      => $semesterSekarang >= $minSemester, // true kalau sudah memenuhi
            'semester_sekarang' => $semesterSekarang,
            'min_semester'      => $minSemester,
            'nama_prodi'        => $mahasiswa->prodi->nama_prodi ?? 'Unknown',
        ];
    }

    // ==================== ADMIN - MONITORING LOGBOOK ====================

    /**
     * Halaman monitoring logbook dari sisi Admin
     * 
     * Admin bisa melihat SEMUA logbook dari SEMUA mahasiswa (tanpa filter server-side)
     * Data dikelompokkan per perusahaan, lalu diurutkan per mahasiswa
     * Ada progress percentage berdasarkan target 16 minggu x 7 hari
     */
    public function indexAdmin()
    {
        // Ambil data prodi dan dosen untuk dropdown filter di view
        $prodis = \App\Models\Prodi::orderBy('nama_prodi')->get();
        $dosens = Dosen::has('dosenPembimbing')->orderBy('nama')->get();

        // Query utama: ambil semua dosen pembimbing beserta statistik logbook-nya
        // withCount dipakai untuk menghitung jumlah logbook berdasarkan status SEKALIGUS
        $query = DosenPembimbing::with(['mahasiswa.prodi', 'dosen', 'pengajuanMagang.perusahaan'])
            ->withCount([
                'logbooks as total_logbook', // Hitung semua logbook
                'logbooks as logbook_pending' => function($q) {
                    $q->where('status', 'pending');
                },
                'logbooks as logbook_disetujui' => function($q) {
                    $q->where('status', 'disetujui');
                },
                'logbooks as logbook_ditolak' => function($q) {
                    $q->where('status', 'ditolak');
                }
            ])
            // Subquery untuk ambil minggu ke tertinggi (buat tau progres mahasiswa sampai mana)
            ->addSelect([
                'max_minggu' => Logbook::selectRaw('MAX(minggu_ke)')
                    ->whereColumn('logbook.id_dosen_pembimbing', 'dosen_pembimbing.id_dosen_pembimbing')
            ]);

        // get() bukan paginate() -> ambil SEMUA data sekaligus (filter dilakukan di client-side/view)
        $allData = $query->get();

        // Urutkan: per perusahaan dulu, lalu per nama mahasiswa
        $sorted = $allData->sortBy(function($item) {
            return strtolower(
                ($item->pengajuanMagang->perusahaan->nama_perusahaan ?? 'zzz') . 
                ($item->mahasiswa->nama ?? 'zzz')
            );
        });

        // Tambahkan persentase progress (target: 16 minggu x 7 hari = 112 entri)
        $sorted->transform(function($item) {
            $targetHari = 16 * 7;
            $item->progress_percentage = $targetHari > 0 
                ? min(100, (($item->logbook_disetujui ?? 0) / $targetHari) * 100) 
                : 0;
            $item->perusahaan = $item->pengajuanMagang ? $item->pengajuanMagang->perusahaan : null;
            return $item;
        });

        // Kelompokkan data berdasarkan nama perusahaan
        $groupedLogbook = $sorted->groupBy(function($item) {
            return $item->pengajuanMagang->perusahaan->nama_perusahaan ?? '-';
        });

        // Statistik total keseluruhan
        $totalMahasiswa = DosenPembimbing::count();
        $totalPending = Logbook::where('status', 'pending')->count();
        $totalDisetujui = Logbook::where('status', 'disetujui')->count();
        $totalDitolak = Logbook::where('status', 'ditolak')->count();

        return view('pages.admin.logbook.index', compact(
            'prodis', 'dosens', 'groupedLogbook', 
            'totalMahasiswa', 'totalPending', 'totalDisetujui', 'totalDitolak'
        ));
    }

    /**
     * Halaman detail logbook dari sisi Admin
     * 
     * Admin bisa melihat detail logbook SETIAP mahasiswa (tanpa batas prodi)
     * Data dikelompokkan per minggu, sama seperti tampilan dosen/mahasiswa
     */
    public function detailAdmin($id_dosen_pembimbing)
    {
        // Ambil data dosen pembimbing beserta relasinya
        $dosenPembimbing = DosenPembimbing::where('id_dosen_pembimbing', $id_dosen_pembimbing)
            ->with(['mahasiswa.prodi', 'dosen', 'pengajuanMagang.perusahaan'])
            ->firstOrFail();

        // Ambil semua logbook, dikelompokkan per minggu
        $logbooks = Logbook::where('id_dosen_pembimbing', $id_dosen_pembimbing)
            ->orderBy('minggu_ke', 'asc')
            ->orderBy('tanggal', 'asc')
            ->get();

        $groupedByMinggu = $logbooks->groupBy('minggu_ke');

        return view('pages.admin.logbook.detail_logbook', compact('dosenPembimbing', 'logbooks', 'groupedByMinggu'));
    }
}