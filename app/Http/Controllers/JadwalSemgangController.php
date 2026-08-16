<?php

namespace App\Http\Controllers;

use App\Models\JadwalSemgang;
use App\Models\Mahasiswa;
use App\Models\Notifikasi;
use App\Models\DosenPembimbing;
use App\Models\Dosen;
use App\Models\PengajuanMahasiswa;
use Illuminate\Http\Request;

/**
 * Controller untuk mengelola Jadwal Seminar Magang (Semgang)
 * 
 * Dipakai oleh beberapa role:
 * - KAPRODI: CRUD jadwal, lihat personal interview, pantau penilaian dospem
 * - DOSEN PEMBIMBING: Lihat jadwal bimbingannya, input nilai seminar
 * - MAHASISWA: Lihat jadwal sendiri, update judul laporan & ruang
 * - ADMIN: Lihat semua jadwal & penilaian secara global
 */
class JadwalSemgangController extends Controller
{
    /**
     * Menampilkan halaman daftar jadwal seminar magang (Untuk Kaprodi)
     * 
     * Fitur:
     * - Filter otomatis berdasarkan prodi kaprodi yang login
     * - Pencarian (nama mhs, NIM, nama dospem, perusahaan, ruang, tanggal)
     * - Pagination (bisa pilih 5, 10, 15, atau tampilkan semua)
     * - Cek kelengkapan Personal Interview (Form A & Form B) per mahasiswa-perusahaan
     */
    public function index(Request $request)
    {
        // Cari data dosen yang sedang login
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        // Ambil parameter dari URL
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');

        // Validasi per_page hanya boleh 5, 10, 15, atau 'all'
        if (!in_array($perPage, [5, 10, 15, 'all'])) {
            $perPage = 10;
        }

        // Query utama dengan relasi supaya tidak N+1
        $query = JadwalSemgang::with(['mahasiswa', 'dosenPembimbing.dosen', 'perusahaan'])
                    ->orderBy('tgl_semgang', 'desc');

        // Filter berdasarkan prodi dosen login (kaprodi hanya lihat prodi-nya sendiri)
        if ($dosenLogin) {
            $query->whereHas('mahasiswa', function ($q) use ($dosenLogin) {
                $q->where('id_prodi', $dosenLogin->id_prodi);
            });
        }

        // Search functionality - cari di beberapa kolom sekaligus
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('mahasiswa', function ($q2) use ($search) {
                    $q2->where('nama', 'LIKE', '%' . $search . '%')
                       ->orWhere('id_mahasiswa', 'LIKE', '%' . $search . '%');
                })
                ->orWhereHas('dosenPembimbing.dosen', function ($q2) use ($search) {
                    $q2->where('nama', 'LIKE', '%' . $search . '%');
                })
                ->orWhereHas('perusahaan', function ($q2) use ($search) {
                    $q2->where('nama_perusahaan', 'LIKE', '%' . $search . '%');
                })
                ->orWhere('ruang', 'LIKE', '%' . $search . '%')
                ->orWhere('tgl_semgang', 'LIKE', '%' . $search . '%');
            });
        }
        
        // Pagination atau get all (kalau user pilih "Tampilkan Semua")
        if ($perPage === 'all') {
            $jadwal = $query->get();
        } else {
            $jadwal = $query->paginate($perPage)->withQueryString();
        }

        // ✅ CEK PERSONAL INTERVIEW PER MAHASISWA + PERUSAHAAN
        // Ini untuk menampilkan badge di view: apakah interview A & B sudah lengkap
        $interviewMap = [];
        if ($jadwal->isNotEmpty()) {
            // Kumpulkan ID mahasiswa dan ID perusahaan yang ada di halaman ini
            $mhsIds = $jadwal->pluck('id_mahasiswa')->unique()->toArray();
            $perIds = $jadwal->pluck('id_perusahaan')->filter()->unique()->toArray();

            // Cari data dosen visitasi yang terkait
            $dvList = \App\Models\DosenVisitasi::whereIn('id_mahasiswa', $mhsIds)
                ->when(!empty($perIds), fn($q) => $q->whereIn('id_perusahaan', $perIds))
                ->get();

            // Cek satu per satu apakah punya Interview A dan Interview B
            foreach ($dvList as $dv) {
                // Buat key unik dari kombinasi ID mahasiswa dan ID perusahaan
                $key = $dv->id_mahasiswa . '_' . ($dv->id_perusahaan ?? 0);
                $hasA = \App\Models\InterviewVisitasiMagangA::where('id_dosen_visitasi', $dv->id_dosen_visitasi)->exists();
                $hasB = \App\Models\InterviewVisitasiMagangB::where('id_dosen_visitasi', $dv->id_dosen_visitasi)->exists();
                $interviewMap[$key] = [
                    'complete'      => $hasA && $hasB, // Lengkap kalau A dan B keduanya ada
                    'id_perusahaan' => $dv->id_perusahaan,
                ];
            }
        }

        return view('pages.kaprodi.jadwal_semgang.index', compact('jadwal', 'interviewMap', 'perPage', 'search'));
    }

    /**
     * Menampilkan form untuk membuat jadwal seminar baru (Untuk Kaprodi)
     * 
     * Dropdown mahasiswa yang muncul HARUS memenuhi 3 syarat:
     * 1. Sudah diterima surat balasannya & punya surat pengantar dari BAAK
     * 2. Sudah mendapatkan dosen pembimbing
     * 3. Belum ada di jadwal seminar manapun
     */
    public function create()
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $idProdiKaprodi = $dosenLogin->id_prodi;

        // 1. Ambil ID mahasiswa yang sudah diterima surat balasannya & dapat surat pengantar BAAK
        $idMahasiswaLolosSurat = PengajuanMahasiswa::where('status_surat_balasan', 'diterima')
                            ->whereHas('pengajuan.suratPengantar')
                            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                                $q->where('id_prodi', $idProdiKaprodi);
                            })
                            ->pluck('id_mahasiswa')
                            ->unique()
                            ->toArray();

        // 2. Ambil ID mahasiswa yang SUDAH MENDAPATKAN DOSEN PEMBIMBING
        $idMahasiswaPunyaDospem = DosenPembimbing::whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                                $q->where('id_prodi', $idProdiKaprodi);
                            })
                            ->pluck('id_mahasiswa')
                            ->unique()
                            ->toArray();

        // 3. Ambil ID mahasiswa yang sudah ada di jadwal seminar (untuk dikecualikan)
        $idMahasiswaSudahAdaJadwal = JadwalSemgang::pluck('id_mahasiswa')->toArray();

        // Gabungkan 3 filter di atas
        $mahasiswa = Mahasiswa::where('id_prodi', $idProdiKaprodi)
                      ->whereIn('id_mahasiswa', $idMahasiswaLolosSurat)
                      ->whereIn('id_mahasiswa', $idMahasiswaPunyaDospem)
                      ->whereNotIn('id_mahasiswa', $idMahasiswaSudahAdaJadwal)
                      ->get();

        return view('pages.kaprodi.jadwal_semgang.create', compact('mahasiswa'));
    }

    /**
     * Endpoint AJAX untuk mengambil detail mahasiswa (Untuk Kaprodi)
     * 
     * Dipanggil saat kaprodi memilih mahasiswa di dropdown form jadwal.
     * Return JSON berisi data dosen pembimbing dan perusahaan tempat magang.
     * 
     * @param int $id_mahasiswa - ID mahasiswa yang dipilih
     */
    public function getDetailMahasiswa($id_mahasiswa)
    {
        // Cari data dosen pembimbing dari mahasiswa ini
        $dospem = DosenPembimbing::where('id_mahasiswa', $id_mahasiswa)
                    ->with('dosen')
                    ->first();

        // Cari data pengajuan mahasiswa yang sudah diterima & punya surat pengantar
        $pengajuanMhs = PengajuanMahasiswa::where('id_mahasiswa', $id_mahasiswa)
                        ->where('status_surat_balasan', 'diterima')
                        ->whereHas('pengajuan.suratPengantar')
                        ->with('pengajuan.perusahaan')
                        ->first();

        // Return dalam format JSON
        return response()->json([
            'dospem' => $dospem ? [
                'id' => $dospem->id_dosen_pembimbing,
                'nama' => $dospem->dosen->nama ?? '-'
            ] : null,
            'perusahaan' => $pengajuanMhs && $pengajuanMhs->pengajuan ? [
                'id' => $pengajuanMhs->pengajuan->perusahaan->id_perusahaan ?? null,
                'nama' => $pengajuanMhs->pengajuan->perusahaan->nama_perusahaan ?? '-'
            ] : null
        ]);
    }

    /**
     * Menyimpan jadwal seminar magang baru (Untuk Kaprodi)
     * 
     * Yang dilakukan:
     * 1. Validasi input (mahasiswa, dospem, tanggal, jam, ruang)
     * 2. Simpan jadwal dengan status "dijadwalkan"
     * 3. Kirim notifikasi ke mahasiswa yang bersangkutan
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_mahasiswa' => 'required|integer',
            'id_dosen_pembimbing' => 'required|integer',
            'id_perusahaan' => 'nullable|integer',
            'tgl_semgang' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'nullable',
            'ruang' => 'nullable|string|max:100',
        ]);

        // Simpan data jadwal
        $jadwal = JadwalSemgang::create([
            'id_mahasiswa' => $request->id_mahasiswa,
            'id_dosen_pembimbing' => $request->id_dosen_pembimbing,
            'id_perusahaan' => $request->id_perusahaan,
            'tgl_semgang' => $request->tgl_semgang,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'ruang' => $request->ruang,
            'status_semgang' => 'dijadwalkan',
        ]);

        // Kirim notifikasi ke mahasiswa kalau dia punya akun user
        $mahasiswa = Mahasiswa::find($request->id_mahasiswa);
        if ($mahasiswa && $mahasiswa->id_user) {
            Notifikasi::kirim(
                $mahasiswa->id_user,
                'Jadwal Seminar Magang Dibagikan',
                'Kaprodi telah membagikan jadwal seminar magang Anda.',
                'jadwal_semgang',
                $jadwal->id_jadwal_semgang
            );
        }

        return redirect()->route('kaprodi.jadwal-semgang.index')->with('success', 'Jadwal Seminar Magang berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit jadwal seminar (Untuk Kaprodi)
     * 
     * Sama seperti create, tapi mahasiswa yang sedang diedit TETAP muncul di dropdown
     * (meskipun sebenarnya sudah ada di jadwal)
     * 
     * @param int $id_jadwal_semgang - ID jadwal yang diedit
     */
    public function edit($id_jadwal_semgang)
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $idProdiKaprodi = $dosenLogin->id_prodi;

        // Ambil data jadwal yang sedang diedit
        $jadwal = JadwalSemgang::findOrFail($id_jadwal_semgang);

        // Filter mahasiswa yang tersedia (sama seperti di create)
        $idMahasiswaLolosSurat = PengajuanMahasiswa::where('status_surat_balasan', 'diterima')
                            ->whereHas('pengajuan.suratPengantar')
                            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                                $q->where('id_prodi', $idProdiKaprodi);
                            })
                            ->pluck('id_mahasiswa')->unique()->toArray();

        $idMahasiswaPunyaDospem = DosenPembimbing::whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                                $q->where('id_prodi', $idProdiKaprodi);
                            })
                            ->pluck('id_mahasiswa')->unique()->toArray();

        $idMahasiswaSudahAdaJadwal = JadwalSemgang::pluck('id_mahasiswa')->toArray();

        // Ambil mahasiswa yang memenuhi syarat ATAU mahasiswa yang sedang diedit
        $mahasiswa = Mahasiswa::where('id_prodi', $idProdiKaprodi)
                      ->where(function($q) use ($idMahasiswaLolosSurat, $idMahasiswaPunyaDospem, $idMahasiswaSudahAdaJadwal) {
                          $q->whereIn('id_mahasiswa', $idMahasiswaLolosSurat)
                            ->whereIn('id_mahasiswa', $idMahasiswaPunyaDospem)
                            ->whereNotIn('id_mahasiswa', $idMahasiswaSudahAdaJadwal);
                      })
                      // Tambahkan mahasiswa yang sedang diedit biar tetap muncul di dropdown
                      ->orWhere('id_mahasiswa', $jadwal->id_mahasiswa)
                      ->get();

        return view('pages.kaprodi.jadwal_semgang.edit', compact('jadwal', 'mahasiswa'));
    }

    /**
     * Mengupdate data jadwal seminar (Untuk Kaprodi)
     * 
     * @param int $id_jadwal_semgang - ID jadwal yang diupdate
     */
    public function update(Request $request, $id_jadwal_semgang)
    {
        $request->validate([
            'id_mahasiswa' => 'required|integer',
            'id_dosen_pembimbing' => 'required|integer',
            'id_perusahaan' => 'nullable|integer',
            'tgl_semgang' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'nullable',
            'ruang' => 'nullable|string|max:100',
        ]);

        $jadwal = JadwalSemgang::findOrFail($id_jadwal_semgang);
        $jadwal->update($request->all());

        return redirect()->route('kaprodi.jadwal-semgang.index')->with('success', 'Jadwal Seminar Magang berhasil diperbarui!');
    }

    /**
     * Menghapus jadwal seminar (Untuk Kaprodi)
     * 
     * @param int $id_jadwal_semgang - ID jadwal yang dihapus
     */
    public function destroy($id_jadwal_semgang)
    {
        $jadwal = JadwalSemgang::findOrFail($id_jadwal_semgang);
        $jadwal->delete();

        return redirect()->route('kaprodi.jadwal-semgang.index')->with('success', 'Jadwal Seminar Magang berhasil dihapus!');
    }

    // ==================== DOSEN PEMBIMBING ====================

    /**
     * Menampilkan jadwal seminar mahasiswa yang dibimbing (Untuk Dosen Pembimbing)
     * 
     * Dospem hanya bisa melihat jadwal mahasiswa yang dia bimbing sendiri
     */
    public function indexDospem()
    {
        $idUser = auth()->user()->id_user;
        $dosen = \App\Models\Dosen::where('id_user', $idUser)->first();

        $jadwal = collect(); // Default kosong

        if ($dosen) {
            // Cari semua ID dosen pembimbing milik dosen ini
            $idDospemList = DosenPembimbing::where('id_dosen', $dosen->id_dosen)
                                ->pluck('id_dosen_pembimbing')
                                ->toArray();

            // Ambil jadwal seminar yang dospem-nya adalah dosen ini
            $jadwal = JadwalSemgang::with(['mahasiswa', 'dosenPembimbing.dosen', 'perusahaan'])
                        ->whereIn('id_dosen_pembimbing', $idDospemList)
                        ->orderBy('tgl_semgang', 'desc')
                        ->get();
        }

        return view('pages.dospem.pelaksanaan_semgang.index', compact('jadwal'));
    }

    // ==================== MAHASISWA ====================

    /**
     * Menampilkan jadwal seminar milik mahasiswa yang login
     * 
     * Mahasiswa hanya punya 1 jadwal seminar, jadi ambil yang pertama (first)
     */
    public function indexMahasiswa()
    {
        $idUser = auth()->user()->id_user;
        $mahasiswa = \App\Models\Mahasiswa::where('id_user', $idUser)->first();

        $jadwal = null;
        if ($mahasiswa) {
            $jadwal = JadwalSemgang::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
                        ->with(['dosenPembimbing.dosen', 'perusahaan'])
                        ->first();
        }

        return view('pages.mahasiswa.pelaksanaan_semgang.index', compact('jadwal'));
    }

    /**
     * Menampilkan detail jadwal seminar (Untuk Mahasiswa)
     * 
     * Ada security check: pastikan jadwal ini benar-benar milik mahasiswa yang login
     * 
     * @param int $id - ID jadwal
     */
    public function detailMahasiswa($id)
    {
        $jadwal = JadwalSemgang::with(['dosenPembimbing.dosen', 'perusahaan'])->findOrFail($id);

        // Security check
        $idUser = auth()->user()->id_user;
        $mahasiswa = \App\Models\Mahasiswa::where('id_user', $idUser)->first();
        
        if (!$mahasiswa || $jadwal->id_mahasiswa != $mahasiswa->id_mahasiswa) {
            abort(403, 'Akses ditolak.');
        }

        return view('pages.mahasiswa.pelaksanaan_semgang.detail', compact('jadwal'));
    }

    /**
     * Mahasiswa mengupdate data seminar (judul laporan dan ruang)
     * 
     * Mahasiswa hanya boleh update 2 field ini, bukan jadwal atau nilainya
     * 
     * @param int $id - ID jadwal
     */
    public function updateMahasiswa(Request $request, $id)
    {
        $request->validate([
            'judul_laporan' => 'required|string|max:255',
            'ruang'         => 'required|string|max:100',
        ]);

        $jadwal = JadwalSemgang::findOrFail($id);

        // Security check
        $idUser = auth()->user()->id_user;
        $mahasiswa = \App\Models\Mahasiswa::where('id_user', $idUser)->first();
        
        if (!$mahasiswa || $jadwal->id_mahasiswa != $mahasiswa->id_mahasiswa) {
            abort(403, 'Akses ditolak.');
        }

        $jadwal->update([
            'judul_laporan' => $request->judul_laporan,
            'ruang'         => $request->ruang,
        ]);

        return redirect()->route('mahasiswa.pelaksanaan-seminar.index')->with('success', 'Data seminar berhasil diperbarui!');
    }

    // ==================== NILAI DOSEN PEMBIMBING ====================

    /**
     * Menampilkan form penilaian seminar (Untuk Dosen Pembimbing)
     * 
     * Ada security check: pastikan dosen yang login benar-benar pembimbing di jadwal ini
     * 
     * @param int $id - ID jadwal
     */
    public function formNilaiDospem($id)
    {
        $jadwal = JadwalSemgang::with(['mahasiswa', 'dosenPembimbing.dosen', 'perusahaan'])->findOrFail($id);

        // Security check: pastikan dosen yang login adalah pembimbing di jadwal ini
        $idUser = auth()->user()->id_user;
        $dosen = \App\Models\Dosen::where('id_user', $idUser)->first();
        $idDospemList = DosenPembimbing::where('id_dosen', $dosen->id_dosen)->pluck('id_dosen_pembimbing')->toArray();
        
        if (!in_array($jadwal->id_dosen_pembimbing, $idDospemList)) {
            abort(403, 'Akses ditolak.');
        }

        return view('pages.dospem.pelaksanaan_semgang.form_nilai', compact('jadwal'));
    }

    /**
     * Menyimpan nilai seminar dari dosen pembimbing
     * 
     * Yang dilakukan:
     * 1. Validasi 6 komponen nilai (0-100) + checkbox konfirmasi TTD
     * 2. Hitung total dan rata-rata
     * 3. Simpan semua nilai + ubah status jadi "selesai"
     * 4. Kirim notifikasi ke Kaprodi
     * 
     * @param int $id - ID jadwal
     */
    public function storeNilaiDospem(Request $request, $id)
    {
        // Validasi: 6 komponen nilai wajib diisi (0-100), plus checkbox konfirmasi TTD
        $request->validate([
            'nilai_persiapan'          => 'required|numeric|min:0|max:100',
            'nilai_sikap'              => 'required|numeric|min:0|max:100',
            'nilai_penguasaan'         => 'required|numeric|min:0|max:100',
            'nilai_mempertahankan_ide' => 'required|numeric|min:0|max:100',
            'nilai_hasil_magang'       => 'required|numeric|min:0|max:100',
            'nilai_penulisan'          => 'required|numeric|min:0|max:100',
            'catatan'                  => 'nullable|string',
            'konfirmasi_ttd'           => 'required|accepted', // Checkbox harus dicentang
        ]);

        $jadwal = JadwalSemgang::findOrFail($id);

        // Hitung total dan rata-rata dari 6 komponen
        $total = $request->nilai_persiapan + $request->nilai_sikap + $request->nilai_penguasaan + $request->nilai_mempertahankan_ide + $request->nilai_hasil_magang + $request->nilai_penulisan;
        $rata_rata = $total / 6;

        // Simpan semua nilai ke database
        $jadwal->update([
            'nilai_persiapan'          => $request->nilai_persiapan,
            'nilai_sikap'              => $request->nilai_sikap,
            'nilai_penguasaan'         => $request->nilai_penguasaan,
            'nilai_mempertahankan_ide' => $request->nilai_mempertahankan_ide,
            'nilai_hasil_magang'       => $request->nilai_hasil_magang,
            'nilai_penulisan'          => $request->nilai_penulisan,
            'total_nilai'              => $total,
            'rata_rata'                => round($rata_rata, 2), // Bulatkan 2 desimal
            'catatan'                  => $request->catatan,
            'status_semgang'           => 'selesai', // Ubah status jadi selesai
        ]);

        // Kirim notifikasi ke Kaprodi prodi mahasiswa ini
        $mhsProdi = $jadwal->mahasiswa->id_prodi ?? null;
        if ($mhsProdi) {
            Notifikasi::kirimKeKaprodiProdi(
                $mhsProdi,
                'Seminar Magang Selesai',
                'Seminar magang ' . ($jadwal->mahasiswa->nama ?? '-') . ' telah selesai dinilai oleh dosen pembimbing.',
                'kaprodi_seminar_selesai',
                $jadwal->id_jadwal_semgang
            );
        }

        return redirect()->route('dospem.jadwal-semgang.index')->with('success', 'Nilai seminar berhasil disimpan!');
    }

    /**
     * Menampilkan detail nilai yang sudah diisi (Untuk Dosen Pembimbing)
     * 
     * @param int $id - ID jadwal
     */
    public function detailNilaiDospem($id)
    {
        $jadwal = JadwalSemgang::with(['mahasiswa.prodi', 'dosenPembimbing.dosen', 'perusahaan'])->findOrFail($id);
        return view('pages.dospem.pelaksanaan_semgang.detail_nilai', compact('jadwal'));
    }

    // ==================== KAPRODI - PENILAIAN ====================

    /**
     * Menampilkan halaman pemantauan penilaian dospem (Untuk Kaprodi)
     * 
     * Hanya menampilkan jadwal yang sudah selesai atau sudah ada nilainya
     * Bisa search dan pagination
     */
  public function indexPenilaianDospemKaprodi(Request $request)
{
    $idUser = auth()->user()->id_user;
    $dosenLogin = \App\Models\Dosen::where('id_user', $idUser)->first();

    if (!$dosenLogin) {
        abort(403, 'Data dosen tidak ditemukan.');
    }

    $idProdiKaprodi = $dosenLogin->id_prodi;

    $perPage = $request->get('per_page', 10);
    $search = $request->get('search', '');

    // Validasi per_page hanya boleh 5, 10, 15, atau 'all'
    if (!in_array($perPage, [5, 10, 15, 'all'])) {
        $perPage = 10;
    }

    // Query utama: hanya yang statusnya selesai atau sudah ada nilai
    $query = JadwalSemgang::with(['mahasiswa', 'dosenPembimbing.dosen', 'perusahaan'])
                ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                    $q->where('id_prodi', $idProdiKaprodi);
                })
                ->where(function ($q) {
                    $q->where('status_semgang', 'selesai')
                      ->orWhereNotNull('nilai_persiapan');
                });

    // Search
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->whereHas('mahasiswa', function ($q2) use ($search) {
                $q2->where('nama', 'LIKE', '%' . $search . '%')
                   ->orWhere('npm', 'LIKE', '%' . $search . '%');
            })
            ->orWhereHas('dosenPembimbing.dosen', function ($q2) use ($search) {
                $q2->where('nama', 'LIKE', '%' . $search . '%');
            })
            ->orWhereHas('perusahaan', function ($q2) use ($search) {
                $q2->where('nama_perusahaan', 'LIKE', '%' . $search . '%');
            })
            ->orWhere('tgl_semgang', 'LIKE', '%' . $search . '%');
        });
    }

    $query->orderBy('tgl_semgang', 'desc');

    // Pagination atau get all
    if ($perPage === 'all') {
        $jadwal = $query->get();
    } else {
        $jadwal = $query->paginate($perPage)->withQueryString();
    }

    return view('pages.kaprodi.penilaian_dospem.index', compact('jadwal', 'perPage', 'search'));
}

    /**
     * Melihat detail nilai dospem (Untuk Kaprodi)
     * 
     * @param int $id - ID jadwal
     */
    public function detailNilaiKaprodi($id)
    {
        $jadwal = JadwalSemgang::with(['mahasiswa.prodi', 'dosenPembimbing.dosen', 'perusahaan'])->findOrFail($id);
        return view('pages.dospem.pelaksanaan_semgang.detail_nilai', compact('jadwal'));
    }

    // ==================== ADMIN ====================

    /**
     * Menampilkan halaman semua jadwal seminar (Untuk Admin)
     * 
     * Admin bisa melihat SEMUA data tanpa filter prodi
     * Ditampilkan lengkap dengan statistik ringkas
     */
    public function indexAdmin()
    {
        $jadwal = JadwalSemgang::with(['mahasiswa.prodi', 'dosenPembimbing.dosen', 'perusahaan'])
                    ->orderBy('tgl_semgang', 'desc')
                    ->get();

        // Hitung statistik ringkas
        $totalJadwal = $jadwal->count();
        $dijadwalkan = $jadwal->where('status_semgang', 'dijadwalkan')->count();
        $selesai = $jadwal->where('status_semgang', 'selesai')->count();
        $sudahIsiNilai = $jadwal->whereNotNull('rata_rata')->count();

        return view('pages.admin.jadwal_seminar.index', compact(
            'jadwal', 'totalJadwal', 'dijadwalkan', 'selesai', 'sudahIsiNilai'
        ));
    }

    /**
     * Melihat detail jadwal seminar (Untuk Admin)
     * 
     * @param int $id - ID jadwal
     */
        public function detailAdmin($id)
    {
        $jadwal = JadwalSemgang::with(['mahasiswa.prodi', 'dosenPembimbing.dosen', 'perusahaan'])->findOrFail($id);
        return view('pages.admin.jadwal_seminar.detail', compact('jadwal'));
    }

    /**
     * Menampilkan halaman daftar penilaian dospem (Untuk Admin)
     * 
     * Hanya menampilkan yang sudah selesai atau sudah ada nilainya
     */
    public function indexPenilaianDospemAdmin()
    {
        $jadwal = JadwalSemgang::with(['mahasiswa.prodi', 'dosenPembimbing.dosen', 'perusahaan'])
                    ->where(function($query) {
                        $query->where('status_semgang', 'selesai')
                              ->orWhereNotNull('nilai_persiapan');
                    })
                    ->orderBy('tgl_semgang', 'desc')
                    ->paginate(10);

        return view('pages.admin.nilai_dosen_pembimbing.index', compact('jadwal'));
    }

    /**
     * Melihat detail nilai dospem (Untuk Admin)
     * 
     * @param int $id - ID jadwal
     */
    public function detailNilaiDospemAdmin($id)
    {
        $jadwal = JadwalSemgang::with(['mahasiswa.prodi', 'dosenPembimbing.dosen', 'perusahaan'])->findOrFail($id);
        return view('pages.admin.nilai_dosen_pembimbing.detail', compact('jadwal'));
    }

    /**
     * Melihat data Personal Interview untuk perusahaan tertentu (Untuk Kaprodi)
     * 
     * Dipanggil dari halaman jadwal semgang, menampilkan:
     * - Daftar mahasiswa yang visitasi ke perusahaan ini
     * - Data Interview Form A (terbaru)
     * - Data Interview Form B (terbaru)
     * - TTD digital dosen yang mengisi
     * 
     * @param int $id_perusahaan - ID perusahaan
     */
    public function lihatPersonalInterview($id_perusahaan)
{
    $idUser = auth()->user()->id_user;
    $dosenLogin = Dosen::where('id_user', $idUser)->first();

    if (!$dosenLogin) {
        abort(403, 'Data dosen tidak ditemukan.');
    }

    $idProdiKaprodi = $dosenLogin->id_prodi;

    // Ambil semua dosen visitasi untuk perusahaan ini yang sesuai prodi kaprodi
    $visitasiList = \App\Models\DosenVisitasi::with(['dosen', 'mahasiswa', 'perusahaan'])
        ->where('id_perusahaan', $id_perusahaan)
        ->where('status', 'selesai')
        ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
            $q->where('id_prodi', $idProdiKaprodi);
        })
        ->get();

    if ($visitasiList->isEmpty()) {
        return redirect()->route('kaprodi.jadwal-semgang.index')
            ->withErrors('Data visitasi untuk perusahaan ini tidak ditemukan.');
    }

    // Ambil data perusahaan dari hasil pertama
    $perusahaan = $visitasiList->first()->perusahaan;
    
    // Kumpulkan ID visitasi untuk cari data interview-nya
    $visitasiIds = $visitasiList->pluck('id_dosen_visitasi')->toArray();

    // Ambil interview A (ambil yang terbaru per id_dosen_visitasi)
    $interviewA = \App\Models\InterviewVisitasiMagangA::whereIn('id_dosen_visitasi', $visitasiIds)
        ->where('id_perusahaan', $id_perusahaan)
        ->orderBy('id_interview_magang_a', 'desc')
        ->first();

    // Ambil interview B (ambil yang terbaru)
    $interviewB = \App\Models\InterviewVisitasiMagangB::whereIn('id_dosen_visitasi', $visitasiIds)
        ->where('id_perusahaan', $id_perusahaan)
        ->orderBy('id_interview_magang_b', 'desc')
        ->first();

    // Kalau form A atau B belum diisi, batalkan
    if (!$interviewA || !$interviewB) {
        return redirect()->route('kaprodi.jadwal-semgang.index')
            ->withErrors('Data personal interview belum lengkap (Form A dan/atau Form B belum diisi).');
    }

    // Ambil data dosen yang mengisi interview B (untuk TTD)
    $dosenPengisi = \App\Models\Dosen::find($interviewB->id_dosen);
    $ttdUrl = ($dosenPengisi && $dosenPengisi->ttd_digital)
        ? asset('storage/ttd_digital/' . $dosenPengisi->ttd_digital)
        : null;

    return view('pages.kaprodi.jadwal_semgang.personal_interview', compact(
        'visitasiList', 'perusahaan', 'interviewA', 'interviewB', 'dosenPengisi', 'ttdUrl'
    ));
}
}