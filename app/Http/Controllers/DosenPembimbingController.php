<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DosenPembimbing;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\PengajuanMahasiswa;
use App\Models\PengajuanMagang;
use App\Models\Prodi;

/**
 * Controller untuk mengelola data Dosen Pembimbing
 * 
 * Controller ini digunakan oleh beberapa role:
 * - KAPRODI: Mengelola pembagian dosen pembimbing untuk prodi-nya
 * - KAJUR: Memantau pembagian dosen pembimbing se-jurusan
 * - DOSEN PEMBIMBING: Melihat mahasiswa yang dibimbing
 * - MAHASISWA: Melihat siapa dosen pembimbingnya
 * - ADMIN: Melihat semua data dosen pembimbing
 * - BAAK: Upload dan kelola SK dosen pembimbing
 */
class DosenPembimbingController extends Controller
{
    // ==================== FITUR UNTUK KAPRODI ====================
    
    /**
     * Menampilkan halaman daftar pembagian dosen pembimbing (Untuk Kaprodi)
     * 
     * Fitur:
     * - Filter berdasarkan tahun angkatan
     * - Filter berdasarkan kelas (A, B, C, D)
     * - Pencarian berdasarkan nama/NIDN dosen, nama/NPM mahasiswa, atau nama perusahaan
     * - Pagination
     * - Preview dikelompokkan per dosen
     */
    public function indexKaprodi(Request $request)
    {
        // Ambil ID user yang sedang login
        $idUser = auth()->user()->id_user;
        // Cari data dosen berdasarkan ID user
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        // Kalau bukan dosen, tolak akses
        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        // Ambil ID prodi dari kaprodi yang login
        $idProdiKaprodi = $dosenLogin->id_prodi;

        // Ambil parameter filter dan pencarian dari URL
        $search       = $request->input('search');
        $filterTahun = $request->input('filter_tahun');
        $filterKelas = $request->input('filter_kelas');
        $perPage     = $request->input('per_page', 5);  // Default 5 data per halaman

        // ✅ AMBIL DAFTAR TAHUN ANGKATAN UNIK (untuk dropdown filter)
        $daftarTahun = Mahasiswa::where('id_prodi', $idProdiKaprodi)
            ->whereNotNull('angkatan')
            ->distinct()
            ->orderBy('angkatan', 'desc')  // Urutkan dari tahun terbaru
            ->pluck('angkatan')
            ->toArray();

        // ✅ AMBIL DAFTAR KELAS UNIK (hanya A, B, C, D) dari mahasiswa prodi ini
        $daftarKelas = Mahasiswa::where('id_prodi', $idProdiKaprodi)
            ->whereNotNull('kelas')
            ->whereIn('kelas', ['A', 'B', 'C', 'D'])
            ->distinct()
            ->orderBy('kelas', 'asc')  // Urutkan abjad
            ->pluck('kelas')
            ->toArray();

        // Query utama: ambil data dosen pembimbing dengan relasi
        $query = DosenPembimbing::with(['dosen', 'mahasiswa', 'pengajuanMagang.perusahaan'])
            // Hanya dosen dari prodi ini
            ->whereHas('dosen', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            // Hanya mahasiswa dari prodi ini
            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            });

        // ✅ FILTER TAHUN ANGKATAN - kalau dipilih, tambahkan kondisi where
        if ($filterTahun) {
            $query->whereHas('mahasiswa', function ($q) use ($filterTahun) {
                $q->where('angkatan', $filterTahun);
            });
        }

        // ✅ FILTER KELAS (BARU) - kalau dipilih, tambahkan kondisi where
        if ($filterKelas) {
            $query->whereHas('mahasiswa', function ($q) use ($filterKelas) {
                $q->where('kelas', $filterKelas);
            });
        }

        // Kalau ada kata kunci pencarian, cari di beberapa kolom
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Cari di nama atau NIDN dosen
                $q->whereHas('dosen', function ($q2) use ($search) {
                    $q2->where('nama', 'like', '%' . $search . '%')
                       ->orWhere('nidn', 'like', '%' . $search . '%');
                })
                // Atau cari di nama atau NPM mahasiswa
                ->orWhereHas('mahasiswa', function ($q2) use ($search) {
                    $q2->where('nama', 'like', '%' . $search . '%')
                       ->orWhere('npm', 'like', '%' . $search . '%');
                })
                // Atau cari di nama perusahaan tempat magang
                ->orWhereHas('pengajuanMagang.perusahaan', function ($q2) use ($search) {
                    $q2->where('nama_perusahaan', 'like', '%' . $search . '%');
                });
            });
        }

        // Ambil data dengan pengurutan yang kompleks:
        // 1. Urutkan berdasarkan ID dosen (dikelompokkan per dosen)
        // 2. Urutkan berdasarkan angkatan (terbaru dulu)
        // 3. Urutkan berdasarkan kelas (A, B, C, D)
        // 4. Urutkan berdasarkan nama mahasiswa
        $semuaDospem = $query
            ->orderBy('id_dosen', 'asc')
            ->orderByDesc(function($q) {
                $q->select('angkatan')
                  ->from('mahasiswa')
                  ->whereColumn('mahasiswa.id_mahasiswa', 'dosen_pembimbing.id_mahasiswa')
                  ->limit(1);
            })
            ->orderBy(function($q) {
                $q->select('kelas')
                  ->from('mahasiswa')
                  ->whereColumn('mahasiswa.id_mahasiswa', 'dosen_pembimbing.id_mahasiswa')
                  ->limit(1);
            })
            ->orderBy(function($q) {
                // ✅ Tambah orderBy nama mahasiswa biar rapi
                $q->select('nama')
                  ->from('mahasiswa')
                  ->whereColumn('mahasiswa.id_mahasiswa', 'dosen_pembimbing.id_mahasiswa')
                  ->limit(1);
            })
            ->paginate($perPage)
            ->withQueryString();  // Pertahankan parameter URL saat pindah halaman

        // Ambil semua data untuk preview (dikelompokkan per dosen)
        $previewDospem = DosenPembimbing::with(['dosen', 'mahasiswa'])
            ->whereHas('dosen', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->get()
            ->groupBy('id_dosen');  // Kelompokkan berdasarkan dosen

        // ✅ KIRIM $daftarTahun DAN $filterTahun KE VIEW
        return view('pages.kaprodi.pembagian_dosen_pembimbing.index', 
            compact('semuaDospem', 'previewDospem', 'dosenLogin', 'search', 'perPage', 
                    'daftarTahun', 'filterTahun', 'daftarKelas', 'filterKelas'));
    } 

    /**
     * Menampilkan form untuk menugaskan dosen pembimbing baru (Untuk Kaprodi)
     * 
     * Yang ditampilkan:
     * - Dropdown pilihan dosen (hanya dari prodi ini)
     * - Checklist mahasiswa yang bisa dibimbing (sudah diterima magang & belum punya dospem)
     */
    public function createKaprodi()
    {
        // Ambil data kaprodi yang login
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $idProdiKaprodi = $dosenLogin->id_prodi;

        // Ambil semua dosen dari prodi ini
        $dosen = Dosen::where('id_prodi', $idProdiKaprodi)
            ->orderBy('nama', 'asc')
            ->get();

        // ✅ Syarat: diterima + belum punya dospem + 1 prodi
        $mahasiswaTersedia = PengajuanMahasiswa::where('status_surat_balasan', 'diterima')
            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            // Kecualikan mahasiswa yang sudah ada di tabel dosen_pembimbing
            ->whereNotIn('id_mahasiswa', function($query) {
                $query->select('id_mahasiswa')->from('dosen_pembimbing');
            })
            ->with(['mahasiswa', 'pengajuan.perusahaan'])
            ->orderBy('id_pengajuan_mahasiswa', 'desc')
            ->get();

        return view('pages.kaprodi.pembagian_dosen_pembimbing.create', compact('dosen', 'mahasiswaTersedia'));
    }

    /**
     * Menyimpan penugasan dosen pembimbing baru (Untuk Kaprodi)
     * 
     * Yang dilakukan:
     * 1. Validasi input (dosen wajib dipilih, minimal 1 mahasiswa)
     * 2. Looping setiap mahasiswa yang dipilih
     * 3. Cek lagi apakah mahasiswa benar-benar belum punya dospem
     * 4. Simpan data dosen pembimbing
     */
    public function storeKaprodi(Request $request)
    {
        // Validasi input
        $request->validate([
            'id_dosen' => 'required|exists:dosen,id_dosen',                    // Dosen wajib, harus ada di DB
            'mahasiswa' => 'required|array|min:1',                              // Array mahasiswa wajib, minimal 1
            'mahasiswa.*' => 'exists:pengajuan_mahasiswa,id_pengajuan_mahasiswa', // Setiap ID harus valid
        ]);

        // Hilangkan duplikat mahasiswa (kalau ada yang dicentang 2x)
        $mahasiswaUnik = array_unique($request->mahasiswa);

        // Looping setiap mahasiswa yang dipilih
        foreach ($mahasiswaUnik as $idPm) {
            // Cari data pengajuan mahasiswa
            $pm = PengajuanMahasiswa::find($idPm);
            
            // Kalau datanya ada DAN mahasiswa belum punya dospem, buat data baru
            if ($pm && !DosenPembimbing::where('id_mahasiswa', $pm->id_mahasiswa)->exists()) {
                DosenPembimbing::create([
                    'id_dosen'     => $request->id_dosen,      // ID dosen yang dipilih
                    'id_mahasiswa' => $pm->id_mahasiswa,        // ID mahasiswa
                    'id_pengajuan' => $pm->id_pengajuan,        // ID pengajuan magang
                ]);
            }
        }

        // Kembali ke halaman daftar dengan pesan sukses
        return redirect()->route('kaprodi.dosen-pembimbing.index')
            ->with('success', 'Dosen pembimbing berhasil ditugaskan!');
    }

    /**
     * Menampilkan form edit penugasan dosen pembimbing (Untuk Kaprodi)
     * 
     * Yang ditampilkan:
     * - Data dosen pembimbing yang sedang diedit
     * - Semua mahasiswa dalam kelompok yang sama (satu dosen + satu pengajuan)
     * - Dropdown dosen (bisa ganti dosen)
     * - Checklist mahasiswa yang bisa ditambah/hapus dari kelompok
     * 
     * @param int $id_dosen_pembimbing - ID dosen pembimbing yang akan diedit
     */
    public function editKaprodi($id_dosen_pembimbing)
    {
        // Ambil data kaprodi yang login
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $idProdiKaprodi = $dosenLogin->id_prodi;

        // Ambil data dosen pembimbing yang akan diedit
        $dosenPembimbing = DosenPembimbing::with(['dosen', 'mahasiswa', 'pengajuanMagang.perusahaan'])
                            ->findOrFail($id_dosen_pembimbing);

        // Cek akses: dosen dan mahasiswa harus dari prodi yang sama dengan kaprodi
        if ($dosenPembimbing->dosen->id_prodi != $idProdiKaprodi || $dosenPembimbing->mahasiswa->id_prodi != $idProdiKaprodi) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit penugasan ini.');
        }

        // Ambil semua mahasiswa dalam kelompok yang sama (dosen + pengajuan yang sama)
        $kelompokDosen = DosenPembimbing::where('id_dosen', $dosenPembimbing->id_dosen)
            ->where('id_pengajuan', $dosenPembimbing->id_pengajuan)
            ->with(['mahasiswa', 'pengajuanMagang.perusahaan'])
            ->get();

        // Tambahkan ID pengajuan mahasiswa ke setiap item (untuk checkbox)
        $kelompokDosen->each(function($kd) {
            $pm = PengajuanMahasiswa::where('id_mahasiswa', $kd->id_mahasiswa)
                ->where('id_pengajuan', $kd->id_pengajuan)
                ->first();
            $kd->current_id_pm = $pm ? $pm->id_pengajuan_mahasiswa : null;
        });

        // Ambil semua dosen dari prodi ini (untuk dropdown ganti dosen)
        $dosen = Dosen::where('id_prodi', $idProdiKaprodi)
            ->orderBy('nama', 'asc')
            ->get();

        // Ambil ID mahasiswa yang sudah ada di kelompok ini
        $currentMhsIds = $kelompokDosen->pluck('id_mahasiswa')->toArray();

        // Ambil mahasiswa yang bisa ditambahkan ke kelompok:
        // - Sudah diterima magang
        // - Belum punya dospem ATAU sudah ada di kelompok ini (biar tetap muncul di checklist)
        $mahasiswaTersedia = PengajuanMahasiswa::where('status_surat_balasan', 'diterima')
            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->where(function($query) use ($currentMhsIds) {
                // Belum punya dospem...
                $query->whereNotIn('id_mahasiswa', function($q) {
                    $q->select('id_mahasiswa')->from('dosen_pembimbing');
                })
                // ...ATAU sudah ada di kelompok ini
                ->orWhereIn('id_mahasiswa', $currentMhsIds);
            })
            ->with(['mahasiswa', 'pengajuan.perusahaan'])
            ->get();

        return view('pages.kaprodi.pembagian_dosen_pembimbing.edit', compact('dosenPembimbing', 'kelompokDosen', 'dosen', 'mahasiswaTersedia'));
    }

    /**
     * Mengupdate penugasan dosen pembimbing (Untuk Kaprodi)
     * 
     * Cara kerja:
     * 1. HAPUS SEMUA data dosen pembimbing lama di kelompok ini
     * 2. INSERT ULANG hanya mahasiswa yang masih dicentang
     * 
     * @param int $id_dosen_pembimbing - ID dosen pembimbing yang diupdate
     */
    public function updateKaprodi(Request $request, $id_dosen_pembimbing)
    {
        // Validasi input (mahasiswa bisa kosong/nullable, bukan required)
        $request->validate([
            'id_dosen' => 'required|exists:dosen,id_dosen',
            'mahasiswa' => 'nullable|array', // ✅ nullable, bukan required|min:1
            'mahasiswa.*' => 'exists:pengajuan_mahasiswa,id_pengajuan_mahasiswa',
        ]);

        // Ambil data dosen pembimbing lama
        $dosenPembimbing = DosenPembimbing::findOrFail($id_dosen_pembimbing);

        // Hapus semua bimbingan lama di kelompok ini (dosen + pengajuan yang sama)
        DosenPembimbing::where('id_dosen', $dosenPembimbing->id_dosen)
            ->where('id_pengajuan', $dosenPembimbing->id_pengajuan)
            ->delete();

        // Insert ulang yang dicentang (jika ada)
        $mahasiswaUnik = array_unique($request->mahasiswa ?? []);

        foreach ($mahasiswaUnik as $idPm) {
            $pm = PengajuanMahasiswa::find($idPm);

            // Cek lagi apakah mahasiswa belum punya dospem (pengaman)
            if ($pm && !DosenPembimbing::where('id_mahasiswa', $pm->id_mahasiswa)->exists()) {
                DosenPembimbing::create([
                    'id_dosen'     => $request->id_dosen,  // Bisa dosen yang sama atau berbeda
                    'id_mahasiswa' => $pm->id_mahasiswa,
                    'id_pengajuan' => $pm->id_pengajuan,
                ]);
            }
        }

        return redirect()->route('kaprodi.dosen-pembimbing.index')
            ->with('success', 'Dosen pembimbing dan mahasiswa berhasil diperbarui!');
    }

    /**
     * Menghapus satu data dosen pembimbing (Untuk Kaprodi)
     * 
     * @param int $id_dosen_pembimbing - ID yang akan dihapus
     */
    public function destroyKaprodi($id_dosen_pembimbing)
    {
        // Cari dan hapus data
        $dosenPembimbing = DosenPembimbing::findOrFail($id_dosen_pembimbing);
        $dosenPembimbing->delete();

        return redirect()->route('kaprodi.dosen-pembimbing.index')
            ->with('success', 'Penugasan dosen pembimbing berhasil dihapus!');
    }

    /**
     * Menampilkan detail satu data dosen pembimbing (Untuk Kaprodi)
     * 
     * @param int $id_dosen_pembimbing - ID yang akan dilihat detailnya
     */
    public function detailKaprodi($id_dosen_pembimbing)
    {
        // Ambil data dengan relasi lengkap
        $dosenPembimbing = DosenPembimbing::with(['dosen', 'mahasiswa', 'pengajuanMagang.perusahaan'])
                            ->findOrFail($id_dosen_pembimbing);

        return view('pages.kaprodi.pembagian_dosen_pembimbing.detail', compact('dosenPembimbing'));
    }

    /**
     * Menampilkan preview surat penugasan dosen pembimbing (Untuk Kaprodi)
     * 
     * Yang ditampilkan:
     * - Daftar dosen pembimbing dikelompokkan per dosen
     * - Data ketua jurusan (untuk tanda tangan)
     * - Nomor dan tanggal surat
     * - Total mahasiswa dan total dosen pembimbing
     */
    public function previewSuratFinalKaprodi()
    {
        // Ambil data kaprodi yang login
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $idProdiKaprodi = $dosenLogin->id_prodi;

        // Ambil semua dosen pembimbing dari prodi ini, dikelompokkan per dosen
        $previewDospem = DosenPembimbing::with(['dosen', 'mahasiswa'])
            ->whereHas('dosen', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->orderBy('id_dosen')
            ->get()
            ->groupBy('id_dosen');  // Kelompokkan per dosen

        // Cari data ketua jurusan (untuk tanda tangan surat)
        $ketuaJurusan = Dosen::whereHas('jabatanTendik.jabatan', function ($q) {
            $q->where('nama_jabatan', 'LIKE', '%ketua jurusan%');
        })->first();

        // Hitung total mahasiswa (dari semua kelompok)
        $totalMhs = $previewDospem->flatten()->count();
        // Hitung total dosen pembimbing (jumlah kelompok)
        $totalDosen = $previewDospem->count();
        
        // Buat nomor surat
        $nomorSurat = '199/DST/PL43.J02/PP.02.10/' . date('Y');
        // Buat tanggal surat dalam format Indonesia
        $tanggalSurat = \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');

        // Tombol kembali
        $backUrl = route('kaprodi.dosen-pembimbing.index');
        $backText = 'Kembali ke Pembagian Dosen Pembimbing';

        return view('pages.kaprodi.pembagian_dosen_pembimbing.preview_surat_final', compact(
            'previewDospem', 'dosenLogin', 'backUrl', 'backText', 'ketuaJurusan',
            'totalMhs', 'totalDosen', 'nomorSurat', 'tanggalSurat'
        ));
    }

    /**
     * Menampilkan file SK dosen pembimbing (Untuk Kaprodi)
     * 
     * @param int $id_dosen_pembimbing - ID dosen pembimbing
     */
    public function lihatSkKaprodi($id_dosen_pembimbing)
    {
        // Ambil data dosen pembimbing
        $dosenPembimbing = DosenPembimbing::with(['dosen'])
                            ->findOrFail($id_dosen_pembimbing);

        // Cek apakah file SK sudah ada
        if (!$dosenPembimbing->file_sk) {
            return redirect()->route('kaprodi.dosen-pembimbing.index')
                ->withErrors('SK Dosen Pembimbing belum tersedia.');
        }

        $fileSk = $dosenPembimbing->file_sk;

        // Cek apakah file fisiknya ada di storage
        if (!file_exists(storage_path('app/public/sk_dospem/' . $fileSk))) {
            return redirect()->route('kaprodi.dosen-pembimbing.index')
                ->withErrors('File SK tidak ditemukan di storage.');
        }

        // Ambil data prodi
        $prodi = Prodi::find($dosenPembimbing->dosen->id_prodi);

        // Tentukan tombol kembali berdasarkan halaman asal
        if (request()->query('from') === 'monitoring') {
            $backUrl = route('kaprodi.monitoring.pembimbing');
        } else {
            $backUrl = route('kaprodi.dosen-pembimbing.index');
        }

        return view('pages.baak.dosen_pembimbing.lihat_sk_dospem', compact(
            'prodi', 'fileSk', 'backUrl'
        ));
    }

    // ==================== MONITORING ====================

    /**
     * Halaman monitoring dosen pembimbing (Untuk Kaprodi)
     * 
     * Mirip dengan indexKaprodi tapi lebih simpel, untuk pemantauan
     */
    public function monitoringPembimbing(Request $request)
    {
        // Ambil data kaprodi yang login
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $idProdiKaprodi = $dosenLogin->id_prodi;

        // Ambil parameter filter dan pencarian
        $search       = $request->input('search');
        $filterTahun  = $request->input('filter_tahun');
        $filterKelas  = $request->input('filter_kelas');
        $perPage      = $request->input('per_page', 5);

        // ✅ AMBIL DAFTAR TAHUN ANGKATAN UNIK (untuk dropdown filter)
        $daftarTahun = Mahasiswa::where('id_prodi', $idProdiKaprodi)
            ->whereNotNull('angkatan')
            ->distinct()
            ->orderBy('angkatan', 'desc')
            ->pluck('angkatan')
            ->toArray();

        // ✅ AMBIL DAFTAR KELAS UNIK (A, B, C, D)
        $daftarKelas = Mahasiswa::where('id_prodi', $idProdiKaprodi)
            ->whereNotNull('kelas')
            ->whereIn('kelas', ['A', 'B', 'C', 'D'])
            ->distinct()
            ->orderBy('kelas', 'asc')
            ->pluck('kelas')
            ->toArray();

        // Query utama
        $query = DosenPembimbing::with(['dosen', 'mahasiswa', 'pengajuanMagang.perusahaan'])
            ->whereHas('dosen', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            });

        // ✅ FILTER TAHUN ANGKATAN
        if ($filterTahun) {
            $query->whereHas('mahasiswa', function ($q) use ($filterTahun) {
                $q->where('angkatan', $filterTahun);
            });
        }

        // ✅ FILTER KELAS
        if ($filterKelas) {
            $query->whereHas('mahasiswa', function ($q) use ($filterKelas) {
                $q->where('kelas', $filterKelas);
            });
        }

        // Pencarian
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('dosen', function ($q2) use ($search) {
                    $q2->where('nama', 'like', '%' . $search . '%')
                       ->orWhere('nidn', 'like', '%' . $search . '%');
                })
                ->orWhereHas('mahasiswa', function ($q2) use ($search) {
                    $q2->where('nama', 'like', '%' . $search . '%')
                       ->orWhere('npm', 'like', '%' . $search . '%');
                })
                ->orWhereHas('pengajuanMagang.perusahaan', function ($q2) use ($search) {
                    $q2->where('nama_perusahaan', 'like', '%' . $search . '%');
                });
            });
        }

        // Ambil data dengan pagination
        $dospemList = $query->orderBy('id_dosen', 'asc')
                            ->paginate($perPage)
                            ->withQueryString();

        // ✅ KIRIM SEMUA VARIABEL FILTER KE VIEW
        return view('pages.kaprodi.monitoring.pembimbing_mahasiswa', 
            compact('dospemList', 'search', 'perPage', 'daftarTahun', 'filterTahun', 'daftarKelas', 'filterKelas'));
    }

    /**
     * Halaman monitoring dosen pembimbing (Untuk Kajur)
     * 
     * Sama seperti monitoringPembimbing tapi menampilkan data SE-JURUSAN
     * (bukan hanya satu prodi)
     */
    public function monitoringPembimbingKajur(Request $request)
    {
        // Ambil data kajur yang login
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        // Cari ID jurusan dari prodi kajur
        $idJurusanKajur = Prodi::find($dosenLogin->id_prodi)?->id_jurusan;

        if (!$idJurusanKajur) {
            abort(403, 'Jurusan tidak ditemukan.');
        }

        // Ambil semua ID prodi yang ada di jurusan ini
        $idProdiList = Prodi::where('id_jurusan', $idJurusanKajur)->pluck('id_prodi');

        // Ambil parameter pencarian
        $search  = $request->input('search');
        $perPage = $request->input('per_page', 5);

        // Query: ambil dosen pembimbing dari SEMUA prodi di jurusan ini
        $query = DosenPembimbing::whereHas('dosen', function ($q) use ($idProdiList) {
                $q->whereIn('id_prodi', $idProdiList);
            })
            ->whereHas('mahasiswa', function ($q) use ($idProdiList) {
                $q->whereIn('id_prodi', $idProdiList);
            })
            ->with(['dosen', 'mahasiswa', 'pengajuanMagang.perusahaan']);

        // Pencarian
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('dosen', function ($q2) use ($search) {
                    $q2->where('nama', 'like', '%' . $search . '%')
                       ->orWhere('nidn', 'like', '%' . $search . '%');
                })
                ->orWhereHas('mahasiswa', function ($q2) use ($search) {
                    $q2->where('nama', 'like', '%' . $search . '%')
                       ->orWhere('npm', 'like', '%' . $search . '%');
                })
                ->orWhereHas('pengajuanMagang.perusahaan', function ($q2) use ($search) {
                    $q2->where('nama_perusahaan', 'like', '%' . $search . '%');
                });
            });
        }

        // Ambil data dengan pagination
        $dospemList = $query->orderBy('id_dosen', 'asc')
                            ->paginate($perPage)
                            ->withQueryString();

        // Route untuk lihat SK (beda dengan kaprodi)
        $skRoute = 'kajur.monitoring.lihat_sk';

        return view('pages.kaprodi.monitoring.pembimbing_mahasiswa', 
            compact('dospemList', 'search', 'perPage', 'skRoute'));
    }

    /**
     * Menampilkan file SK dosen pembimbing dari halaman monitoring Kajur
     * 
     * @param int $id_dosen_pembimbing - ID dosen pembimbing
     */
    public function lihatSkKajurMonitoring($id_dosen_pembimbing)
    {
        // Ambil data dosen pembimbing
        $dosenPembimbing = DosenPembimbing::with(['dosen'])
            ->findOrFail($id_dosen_pembimbing);

        // Validasi akses: pastikan prodi dospem masih dalam jurusan kajur
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();
        $idJurusanKajur = Prodi::find($dosenLogin->id_prodi)?->id_jurusan;
        $prodiDospem = Prodi::find($dosenPembimbing->dosen->id_prodi);

        if (!$prodiDospem || $prodiDospem->id_jurusan != $idJurusanKajur) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        // Cek file SK
        if (!$dosenPembimbing->file_sk) {
            return redirect()->route('kajur.monitoring.pembimbing')
                ->withErrors('SK Dosen Pembimbing belum tersedia.');
        }

        $fileSk = $dosenPembimbing->file_sk;

        // Cek file fisik
        if (!file_exists(storage_path('app/public/sk_dospem/' . $fileSk))) {
            return redirect()->route('kajur.monitoring.pembimbing')
                ->withErrors('File SK tidak ditemukan di storage.');
        }

        $prodi = $prodiDospem;
        $backUrl = route('kajur.monitoring.pembimbing');

        return view('pages.baak.dosen_pembimbing.lihat_sk_dospem', compact(
            'prodi', 'fileSk', 'backUrl'
        ));
    }

    // ==================== DOSEN PEMBIMBING ====================

    /**
     * Menampilkan daftar mahasiswa yang dibimbing oleh dosen yang login
     * 
     * Data dikelompokkan berdasarkan angkatan mahasiswa
     */
    public function indexDospem()
    {
        // Ambil data dosen yang sedang login
        $user = auth()->user();
        $dosen = Dosen::where('id_user', $user->id_user)->first();

        // Ambil semua mahasiswa yang dibimbing oleh dosen ini
        $mahasiswaBimbingan = DosenPembimbing::where('id_dosen', $dosen->id_dosen)
            ->with(['mahasiswa', 'pengajuanMagang.perusahaan'])
            ->get();

        // Kelompokkan berdasarkan angkatan, urutkan dari angkatan terbaru
        $groupedByAngkatan = $mahasiswaBimbingan->groupBy(function($item) {
            return $item->mahasiswa->angkatan ?? 'Tidak Diketahui';
        })->sortKeysDesc();

        return view('pages.dospem.mahasiswa_bimbingan.index', compact('dosen', 'groupedByAngkatan'));
    }

    /**
     * Menampilkan detail satu mahasiswa bimbingan (Untuk Dosen Pembimbing)
     * 
     * @param int $id_dosen_pembimbing - ID data bimbingan
     */
    public function detailDospem($id_dosen_pembimbing)
    {
        // Ambil data dosen yang login
        $user = auth()->user();
        $dosen = Dosen::where('id_user', $user->id_user)->first();

        // Ambil data bimbingan (hanya milik dosen ini - keamanan)
        $dosenPembimbing = DosenPembimbing::where('id_dosen_pembimbing', $id_dosen_pembimbing)
            ->where('id_dosen', $dosen->id_dosen)
            ->with(['dosen', 'mahasiswa', 'pengajuanMagang.perusahaan'])
            ->firstOrFail();

       return view('pages.dospem.mahasiswa_bimbingan.detail', compact('dosenPembimbing', 'dosen'));
    }

    /**
     * Menampilkan file SK dosen pembimbing (Untuk Dosen Pembimbing)
     * 
     * @param int $id_dosen_pembimbing - ID data bimbingan
     */
    public function lihatSkDospem($id_dosen_pembimbing)
    {
        // Ambil data dosen yang login
        $user = auth()->user();
        $dosen = Dosen::where('id_user', $user->id_user)->first();

        // Ambil data bimbingan (hanya milik dosen ini)
        $dosenPembimbing = DosenPembimbing::where('id_dosen_pembimbing', $id_dosen_pembimbing)
            ->where('id_dosen', $dosen->id_dosen)
            ->firstOrFail();

        // Cek file SK
        if (!$dosenPembimbing->file_sk) {
            return redirect()->route('dospem.mahasiswa-bimbingan')
                ->withErrors('SK Dosen Pembimbing belum tersedia.');
        }

        // Mode view only (tidak bisa edit)
        $isViewOnly = true;
        $backUrl = route('dospem.mahasiswa-bimbingan');

        return view('pages.baup.dosen_pembimbing.sk_dosen_pembimbing', compact('dosenPembimbing', 'isViewOnly', 'backUrl'));
    }

    // ==================== MAHASISWA ====================

    /**
     * Menampilkan daftar dosen pembimbing dari prodi mahasiswa yang login
     * 
     * Mahasiswa bisa melihat siapa saja dosen pembimbing di prodi-nya
     * (termasuk siapa dosen pembimbingnya sendiri)
     */
    public function indexMahasiswa(Request $request)
    {
        // Ambil data mahasiswa yang login
        $idUser = auth()->user()->id_user;
        $mahasiswa = Mahasiswa::where('id_user', $idUser)->first();

        if (!$mahasiswa) {
            abort(403, 'Data mahasiswa tidak ditemukan.');
        }

        $idProdiMahasiswa = $mahasiswa->id_prodi;

        // Ambil parameter pencarian
        $search  = $request->input('search');
        $perPage = $request->input('per_page', 5);

        // Query: ambil dosen pembimbing dari prodi mahasiswa
        $query = DosenPembimbing::whereHas('dosen', function ($q) use ($idProdiMahasiswa) {
                $q->where('id_prodi', $idProdiMahasiswa);
            })
            ->whereHas('mahasiswa', function ($q) use ($idProdiMahasiswa) {
                $q->where('id_prodi', $idProdiMahasiswa);
            })
            ->with(['dosen', 'mahasiswa', 'pengajuanMagang.perusahaan']);

        // Pencarian
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('dosen', function ($q2) use ($search) {
                    $q2->where('nama', 'like', '%' . $search . '%');
                })
                ->orWhereHas('mahasiswa', function ($q2) use ($search) {
                    $q2->where('nama', 'like', '%' . $search . '%')
                       ->orWhere('npm', 'like', '%' . $search . '%');
                })
                ->orWhereHas('pengajuanMagang.perusahaan', function ($q2) use ($search) {
                    $q2->where('nama_perusahaan', 'like', '%' . $search . '%');
                });
            });
        }

        // Ambil data dengan pagination
        $dospemList = $query->orderBy('id_dosen', 'asc')
                           ->paginate($perPage)
                           ->withQueryString();

        return view('pages.kaprodi.monitoring.pembimbing_mahasiswa', 
            compact('dospemList', 'search', 'perPage'));
    }

    // ==================== ADMIN ====================

    /**
     * Menampilkan semua data dosen pembimbing (Untuk Admin)
     * 
     * Admin bisa melihat SEMUA data tanpa filter prodi
     */
    public function indexAdmin()
    {
        // Ambil semua data dosen pembimbing, urutkan dari terbaru
        $dosenPembimbing = DosenPembimbing::with(['dosen', 'mahasiswa', 'pengajuanMagang.perusahaan'])
            ->orderBy('id_dosen_pembimbing', 'desc')
            ->get();

        return view('pages.admin.dosen_pembimbing.index', compact('dosenPembimbing'));
    }

    /**
     * Export data dosen pembimbing ke Excel (Untuk Kaprodi)
     * 
     * Fitur:
     * - Filter berdasarkan tahun angkatan
     * - Filter berdasarkan kelas
     * - Pencarian
     * - Nama file otomatis berdasarkan filter yang dipilih
     */
    public function exportKaprodi(Request $request)
    {
        // Ambil data kaprodi yang login
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $idProdiKaprodi = $dosenLogin->id_prodi;

        // Query utama
        $query = DosenPembimbing::with(['dosen', 'mahasiswa', 'pengajuanMagang.perusahaan'])
            ->whereHas('dosen', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            });

        // ✅ Terapkan filter tahun jika ada
        if ($request->filter_tahun) {
            $query->whereHas('mahasiswa', function ($q) use ($request) {
                $q->where('angkatan', $request->filter_tahun);
            });
        }

        // ✅ Terapkan filter kelas jika ada (BARU)
        if ($request->filter_kelas) {
            $query->whereHas('mahasiswa', function ($q) use ($request) {
                $q->where('kelas', $request->filter_kelas);
            });
        }
        
        // Terapkan search jika ada
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $search = $request->search;
                $q->whereHas('dosen', function ($q2) use ($search) {
                    $q2->where('nama', 'like', '%' . $search . '%')
                       ->orWhere('nidn', 'like', '%' . $search . '%');
                })
                ->orWhereHas('mahasiswa', function ($q2) use ($search) {
                    $q2->where('nama', 'like', '%' . $search . '%')
                       ->orWhere('npm', 'like', '%' . $search . '%');
                })
                ->orWhereHas('pengajuanMagang.perusahaan', function ($q2) use ($search) {
                    $q2->where('nama_perusahaan', 'like', '%' . $search . '%');
                });
            });
        }

        // Ambil semua data yang cocok
        $data = $query->orderBy('id_dosen', 'asc')->get();

        // Jika kosong, kembalikan dengan pesan error
        if ($data->isEmpty()) {
            return redirect()->route('kaprodi.dosen-pembimbing.index')
                ->with('error', 'Tidak ada data untuk diekspor.');
        }

        // ✅ Buat nama file yang informatif berdasarkan filter
        $fileName = 'Pembagian_Dosen_Pembimbing';
        if ($request->filter_tahun) {
            $fileName .= '_Tahun_' . $request->filter_tahun;
        }

        if ($request->filter_kelas) {
            $fileName .= '_Kelas_' . $request->filter_kelas;
        }
        // Tambahkan tanggal di nama file
        $fileName .= '_' . date('d-m-Y') . '.xlsx';

        // Export Excel
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\DosenPembimbingExport($data), 
            $fileName
        );
    }

    // ==================== BAAK ====================

    /**
     * Menampilkan halaman dosen pembimbing per prodi (Untuk BAAK)
     * 
     * Data dikelompokkan per prodi, dan di dalamnya dikelompokkan lagi per dosen
     */
    public function indexBaak()
    {
        // Ambil semua prodi, urutkan abjad
        $daftarProdi = Prodi::orderBy('nama_prodi', 'asc')->get();

        // Siapkan array untuk menyimpan data yang dikelompokkan
        $grouped = [];
        
        // Looping setiap prodi
        foreach ($daftarProdi as $prodi) {
            // Ambil dosen pembimbing dari prodi ini
            $dospem = DosenPembimbing::with(['dosen', 'mahasiswa', 'pengajuanMagang.perusahaan'])
                ->whereHas('dosen', function ($q) use ($prodi) {
                    $q->where('id_prodi', $prodi->id_prodi);
                })
                ->whereHas('mahasiswa', function ($q) use ($prodi) {
                    $q->where('id_prodi', $prodi->id_prodi);
                })
                ->orderBy('id_dosen', 'asc')
                ->get();

            // Kalau ada datanya, masukkan ke array grouped
            if ($dospem->count() > 0) {
                $grouped[$prodi->id_prodi] = [
                    'prodi'  => $prodi,
                    'dospem' => $dospem->groupBy('id_dosen'),  // Kelompokkan per dosen
                ];
            }
        }

        return view('pages.kajur.dosen_pembimbing.index', compact('grouped', 'daftarProdi'));
    }

    /**
     * Menampilkan preview surat penugasan dosen pembimbing per prodi (Untuk BAAK)
     * 
     * @param int $id_prodi - ID prodi yang akan dilihat suratnya
     */
    public function previewSuratFinalBaak($id_prodi)
    {
        // Ambil data prodi
        $prodi = Prodi::findOrFail($id_prodi);

        // Ambil dosen pembimbing dari prodi ini, dikelompokkan per dosen
        $previewDospem = DosenPembimbing::with(['dosen', 'mahasiswa'])
            ->whereHas('dosen', function ($q) use ($prodi) {
                $q->where('id_prodi', $prodi->id_prodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($prodi) {
                $q->where('id_prodi', $prodi->id_prodi);
            })
            ->orderBy('id_dosen')
            ->get()
            ->groupBy('id_dosen');

        // Cari data ketua jurusan
        $ketuaJurusan = Dosen::whereHas('jabatanTendik.jabatan', function ($q) {
            $q->where('nama_jabatan', 'LIKE', '%ketua jurusan%');
        })->first();

        // Hitung total
        $totalMhs = $previewDospem->flatten()->count();
        $totalDosen = $previewDospem->count();
        
        // Buat nomor dan tanggal surat
        $nomorSurat = '199/DST/PL43.J02/PP.02.10/' . date('Y');
        $tanggalSurat = \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');

        // Tombol kembali
        $backUrl = route('baak.dosen-pembimbing.index');
        $backText = 'Kembali ke Dosen Pembimbing';

        return view('pages.kaprodi.pembagian_dosen_pembimbing.preview_surat_final', compact(
            'previewDospem', 'prodi', 'backUrl', 'backText', 'ketuaJurusan',
            'totalMhs', 'totalDosen', 'nomorSurat', 'tanggalSurat'
        ));
    }

    /**
     * Menampilkan form upload SK dosen pembimbing (Untuk BAAK)
     * 
     * @param int $id_prodi - ID prodi yang akan diupload SK-nya
     */
    public function formUploadSkBaak($id_prodi)
    {
        $prodi = Prodi::findOrFail($id_prodi);
        $backUrl = route('baak.dosen-pembimbing.preview-per-prodi', $id_prodi);

        return view('pages.baak.dosen_pembimbing.form_upload_sk', compact('prodi', 'backUrl'));
    }

    /**
     * Proses upload SK dosen pembimbing (Untuk BAAK)
     * 
     * Yang dilakukan:
     * 1. Validasi file (harus PDF, maks 5MB)
     * 2. Simpan file ke storage
     * 3. Update SEMUA dosen pembimbing di prodi ini dengan file SK yang sama
     * 
     * @param int $id_prodi - ID prodi
     */
    public function uploadSkBaak(Request $request, $id_prodi)
    {
        // Validasi file SK
        $request->validate([
            'file_sk' => 'required|file|mimes:pdf|max:5120',  // Wajib, PDF, maks 5MB
        ]);

        $prodi = Prodi::findOrFail($id_prodi);

        // Ambil semua dosen pembimbing dari prodi ini
        $dospemList = DosenPembimbing::whereHas('dosen', function ($q) use ($prodi) {
            $q->where('id_prodi', $prodi->id_prodi);
        })->get();

        // Kalau tidak ada data dospem, batalkan
        if ($dospemList->isEmpty()) {
            return back()->withErrors('Tidak ada data dosen pembimbing untuk prodi ini.');
        }

        // Upload file SK
        $file = $request->file('file_sk');
        // Buat nama file yang unik dan informatif
        $filename = 'SK_Dospem_' . str_replace(' ', '_', $prodi->nama_prodi) . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/sk_dospem', $filename);

        // Update semua record dospem di prodi ini dengan file SK yang sama
        foreach ($dospemList as $dospem) {
            $dospem->update(['file_sk' => $filename]);
        }

        return redirect()->route('baak.dosen-pembimbing.index')
            ->with('success', 'SK Dosen Pembimbing berhasil diupload untuk Prodi ' . $prodi->nama_prodi);
    }

    /**
     * Menampilkan file SK dosen pembimbing per prodi (Untuk BAAK)
     * 
     * @param int $id_prodi - ID prodi
     */
    public function lihatSkBaak($id_prodi)
    {
        $prodi = Prodi::findOrFail($id_prodi);

        // Ambil satu data dospem dari prodi ini (semua punya file SK yang sama)
        $dospem = DosenPembimbing::whereHas('dosen', function ($q) use ($prodi) {
            $q->where('id_prodi', $prodi->id_prodi);
        })->first();

        // Cek apakah file SK sudah diupload
        if (!$dospem || !$dospem->file_sk) {
            return redirect()->route('baak.dosen-pembimbing.index')
                ->withErrors('SK Dosen Pembimbing untuk Prodi ' . $prodi->nama_prodi . ' belum tersedia.');
        }

        $fileSk = $dospem->file_sk;

        // Cek file fisik
        if (!file_exists(storage_path('app/public/sk_dospem/' . $fileSk))) {
            return redirect()->route('baak.dosen-pembimbing.index')
                ->withErrors('File SK tidak ditemukan di storage.');
        }

        $backUrl = route('baak.dosen-pembimbing.preview-per-prodi', $id_prodi);

        return view('pages.baak.dosen_pembimbing.lihat_sk_dospem', compact(
            'prodi', 'fileSk', 'backUrl'
        ));
    }

    // ==================== BAAK - MONITORING DOSEN PEMBIMBING ====================

    /**
     * Halaman monitoring dosen pembimbing (Untuk BAAK)
     * 
     * BAAK bisa melihat SEMUA data dosen pembimbing dari semua prodi
     */
    public function monitoringPembimbingBaak(Request $request)
    {
        // Ambil parameter pencarian
        $search  = $request->input('search');
        $perPage = $request->input('per_page', 5);

        // Query: ambil semua data dosen pembimbing (tanpa filter prodi)
        $query = DosenPembimbing::with(['dosen', 'mahasiswa', 'pengajuanMagang.perusahaan']);

        // Pencarian
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('dosen', function ($q2) use ($search) {
                    $q2->where('nama', 'like', '%' . $search . '%')
                       ->orWhere('nidn', 'like', '%' . $search . '%');
                })
                ->orWhereHas('mahasiswa', function ($q2) use ($search) {
                    $q2->where('nama', 'like', '%' . $search . '%')
                       ->orWhere('npm', 'like', '%' . $search . '%');
                })
                ->orWhereHas('pengajuanMagang.perusahaan', function ($q2) use ($search) {
                    $q2->where('nama_perusahaan', 'like', '%' . $search . '%');
                });
            });
        }

        // Ambil data dengan pagination
        $dospemList = $query->orderBy('id_dosen', 'asc')
                            ->paginate($perPage)
                            ->withQueryString();

        // Route untuk lihat SK
        $skRoute = 'baak.monitoring.lihat_sk';

        return view('pages.kaprodi.monitoring.pembimbing_mahasiswa', 
            compact('dospemList', 'search', 'perPage', 'skRoute'));
    }

    /**
     * Menampilkan file SK dosen pembimbing dari halaman monitoring BAAK
     * 
     * @param int $id_dosen_pembimbing - ID dosen pembimbing
     */
    public function lihatSkBaakMonitoring($id_dosen_pembimbing)
    {
        // Ambil data dosen pembimbing
        $dosenPembimbing = DosenPembimbing::with(['dosen'])
            ->findOrFail($id_dosen_pembimbing);

        // Cek file SK
        if (!$dosenPembimbing->file_sk) {
            return redirect()->route('baak.monitoring.pembimbing')
                ->withErrors('SK Dosen Pembimbing belum tersedia.');
        }

        $fileSk = $dosenPembimbing->file_sk;

        // Cek file fisik
        if (!file_exists(storage_path('app/public/sk_dospem/' . $fileSk))) {
            return redirect()->route('baak.monitoring.pembimbing')
                ->withErrors('File SK tidak ditemukan di storage.');
        }

        // Ambil data prodi
        $prodi = Prodi::find($dosenPembimbing->dosen->id_prodi);
        $backUrl = route('baak.monitoring.pembimbing');

        return view('pages.baak.dosen_pembimbing.lihat_sk_dospem', compact(
            'prodi', 'fileSk', 'backUrl'
        ));
    }
}