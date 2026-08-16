<?php

namespace App\Http\Controllers;

use App\Models\PenilaianAkhir;
use App\Models\JadwalSemgang;
use App\Models\PenilaianMahasiswa;
use App\Models\Dosen;
use Illuminate\Http\Request;

/**
 * Controller ini ngurus perhitungan dan penyimpanan Nilai Akhir mahasiswa magang
 * 
 * Rumus nilai akhir:
 * Nilai Akhir = (Nilai Perusahaan × 70%) + (Nilai Dosen Pembimbing × 30%)
 * 
 * Nilai Perusahaan = rata-rata dari penilaian 18 aspek (dari form yang diisi perusahaan via token)
 * Nilai Dosen Pembimbing = rata-rata dari penilaian 6 aspek (dari form seminar magang)
 * 
 * Grade: A(≥80), AB(≥75), B(≥70), BC(≥65), C(≥60), D(≥50), E(<50)
 * 
 * Ada 2 sisi:
 * 1. Kaprodi -> proses nilai akhir untuk mahasiswa prodi-nya (satu-satu atau sekaligus)
 * 2. Admin -> lihat semua nilai akhir (read-only)
 */
class NilaiAkhirController extends Controller
{
    // Bobot penilaian (tetap, gak bisa diubah)
    private $bobot_perusahaan = 70;  // 70% dari nilai perusahaan
    private $bobot_pembimbing = 30;  // 30% dari nilai dosen pembimbing

    /**
     * Helper: Konversi nilai angka ke grade huruf
     */
    private function hitungGrade($nilai)
    {
        if ($nilai >= 80) return 'A';
        if ($nilai >= 75) return 'AB';
        if ($nilai >= 70) return 'B';
        if ($nilai >= 65) return 'BC';
        if ($nilai >= 60) return 'C';
        if ($nilai >= 50) return 'D';
        return 'E';
    }

    // Label untuk 18 aspek penilaian perusahaan (dipakai di halaman detail)
    private $labelPerusahaan = [
        'kedisiplinan'          => 'Kedisiplinan',
        'adaptasi'              => 'Adaptasi',
        'tanggung_jawab'        => 'Tanggung Jawab',
        'kemandirian'           => 'Kemandirian',
        'inisiatif_kreativitas' => 'Inisiatif & Kreativitas',
        'kesesuaian_instruksi'  => 'Kesesuaian Instruksi',
        'kualitas_pekerjaan'    => 'Kualitas Pekerjaan',
        'ketepatan_waktu'       => 'Ketepatan Waktu',
        'problem_solving'       => 'Problem Solving',
        'teamwork'              => 'Teamwork',
        'hubungan_atasan'       => 'Hubungan dengan Atasan',
        'hubungan_rekan'        => 'Hubungan dengan Rekan',
        'hubungan_relasi'       => 'Hubungan Relasi',
        'sikap_kerja'           => 'Sikap Kerja',
        'disiplin_kerja'        => 'Disiplin Kerja',
        'loyalitas'             => 'Loyalitas',
        'motivasi'              => 'Motivasi',
        'penampilan'            => 'Penampilan',
    ];

    // Label untuk 6 aspek penilaian dosen pembimbing (dipakai di halaman detail)
    private $labelPembimbing = [
        'nilai_persiapan'          => 'Persiapan',
        'nilai_sikap'              => 'Sikap',
        'nilai_penguasaan'         => 'Penguasaan Materi',
        'nilai_mempertahankan_ide' => 'Mempertahankan Ide',
        'nilai_hasil_magang'       => 'Hasil Magang',
        'nilai_penulisan'          => 'Penulisan',
    ];

    /**
     * Helper: Ambil ID prodi milik Kaprodi yang sedang login
     * Dipakai untuk membatasi akses: Kaprodi hanya bisa lihat/proses nilai mahasiswa prodi-nya sendiri
     */
    private function getKaprodiProdi()
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();
        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }
        return $dosenLogin->id_prodi;
    }

    // ============================
    // KAPRODI - INDEX
    // ============================

    /**
     * Halaman daftar nilai akhir dari sisi Kaprodi
     * 
     * Menampilkan semua mahasiswa prodi ini yang sudah selesai seminar magang
     * Untuk setiap mahasiswa ditampilkan:
     * - Nilai perusahaan (dari penilaian via token)
     * - Nilai dosen pembimbing (dari penilaian seminar)
     * - Nilai akhir (hasil perhitungan)
     * - Grade
     * - Status: sudah disimpan / belum diproses / belum lengkap
     * 
     * Statistik di atas tabel dihitung dari SEMUA data (tanpa filter search)
     */
    public function index(Request $request)
    {
        $bobotPerusahaan = $this->bobot_perusahaan;
        $bobotPembimbing = $this->bobot_pembimbing;
        $idProdiKaprodi = $this->getKaprodiProdi();

        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');

        if (!in_array($perPage, [5, 10, 15, 'all'])) {
            $perPage = 10;
        }

        // ==============================
        // STATISTIK: dihitung dari SEMUA data (tanpa filter search/per_page)
        // Jadi angkanya tetap konsisten walau user lagi search
        // ==============================
        $allJadwal = JadwalSemgang::with(['mahasiswa', 'perusahaan'])
            ->where('status_semgang', 'selesai')
            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->get();

        // Hitung masing-masing kategori status
        $statSudahSimpan = 0;
        $statBelumProses = 0;
        $statBelumLengkap = 0;

        foreach ($allJadwal as $j) {
            // Cek apakah nilai perusahaan sudah ada
            $penilaian = PenilaianMahasiswa::where('id_mahasiswa', $j->id_mahasiswa)->first();
            $np = $penilaian ? $penilaian->rata_rata : null;
            
            // Cek apakah nilai dosen pembimbing sudah ada
            $nd = $j->rata_rata;
            
            // "Lengkap" berarti kedua nilai sudah ada
            $lengkap = ($np !== null && $nd !== null);
            
            // Cek apakah nilai akhir sudah disimpan ke tabel penilaian_akhir
            $sudahSimpan = PenilaianAkhir::where('id_mahasiswa', $j->id_mahasiswa)
                ->where('id_jadwal_semgang', $j->id_jadwal_semgang)->exists();

            if (!$lengkap) {
                $statBelumLengkap++;
            } elseif ($sudahSimpan) {
                $statSudahSimpan++;
            } else {
                $statBelumProses++;
            }
        }

        $totalMhs     = $allJadwal->count();
        $sudahProses  = $statSudahSimpan;
        $belumProses  = $statBelumProses;
        $belumLengkap = $statBelumLengkap;

        // ==============================
        // QUERY TABEL: dengan search dan pagination
        // ==============================
        $query = JadwalSemgang::with(['mahasiswa.prodi', 'dosenPembimbing.dosen', 'perusahaan'])
            ->where('status_semgang', 'selesai') // Hanya yang sudah selesai seminar
            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi); // Hanya mahasiswa prodi ini
            });

        // Search: cari di nama/npm mahasiswa atau nama perusahaan
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('mahasiswa', function ($q2) use ($search) {
                    $q2->where('nama', 'LIKE', '%' . $search . '%')
                       ->orWhere('npm', 'LIKE', '%' . $search . '%');
                })
                ->orWhereHas('perusahaan', function ($q2) use ($search) {
                    $q2->where('nama_perusahaan', 'LIKE', '%' . $search . '%');
                });
            });
        }

        $query->orderBy('tgl_semgang', 'desc');

        // Paginate atau get all
        if ($perPage === 'all') {
            $jadwalPaginated = $query->get();
        } else {
            $jadwalPaginated = $query->paginate($perPage)->withQueryString();
        }

        // ==============================
        // MAPPING DATA: Untuk setiap jadwal, hitung nilai akhirnya
        // ==============================
        $dataMahasiswa = $jadwalPaginated->map(function ($jadwal) use ($bobotPerusahaan, $bobotPembimbing) {
            // Ambil nilai perusahaan (dari tabel penilaian_mahasiswa)
            $penilaian = PenilaianMahasiswa::where('id_mahasiswa', $jadwal->id_mahasiswa)->first();
            
            // Cek apakah nilai akhir sudah pernah disimpan
            $nilaiAkhirRecord = PenilaianAkhir::where('id_mahasiswa', $jadwal->id_mahasiswa)
                ->where('id_jadwal_semgang', $jadwal->id_jadwal_semgang)
                ->first();

            // Ambil nilai mentah
            $nilaiPerusahaan = $penilaian ? $penilaian->rata_rata : null;
            $nilaiPembimbing = $jadwal->rata_rata; // Ini dari jadwal seminar (dosen pembimbing)
            $nilaiAkhirCalc = null;
            $gradeCalc = null;

            // Hitung nilai akhir hanya kalau kedua nilai sudah lengkap
            if ($nilaiPerusahaan !== null && $nilaiPembimbing !== null) {
                $nilaiAkhirCalc = round(($nilaiPerusahaan * $bobotPerusahaan / 100) + ($nilaiPembimbing * $bobotPembimbing / 100), 2);
                $gradeCalc = $this->hitungGrade($nilaiAkhirCalc);
            }

            $lengkap = $nilaiPerusahaan !== null && $nilaiPembimbing !== null;
            $sudahDisimpan = $nilaiAkhirRecord !== null;

            return [
                'jadwal'            => $jadwal,
                'penilaian'         => $penilaian,
                'nilaiAkhirRecord'  => $nilaiAkhirRecord,
                'nilai_perusahaan'  => $nilaiPerusahaan,
                'nilai_pembimbing'  => $nilaiPembimbing,
                'nilai_akhir_calc'  => $nilaiAkhirCalc,
                'grade_calc'        => $gradeCalc,
                'lengkap'           => $lengkap,
                'sudah_disimpan'    => $sudahDisimpan,
            ];
        });

        return view('pages.kaprodi.nilai_akhir_mahasiswa.index', compact(
            'dataMahasiswa', 'jadwalPaginated',
            'bobotPerusahaan', 'bobotPembimbing',
            'totalMhs', 'sudahProses', 'belumProses', 'belumLengkap',
            'perPage', 'search'
        ));
    }

    // ============================
    // KAPRODI - PROSES SATU MAHASISWA
    // ============================

    /**
     * Proses nilai akhir untuk SATU mahasiswa
     * 
     * Syarat: kedua nilai (perusahaan & pembimbing) harus sudah ada
     * Setelah diproses, hasilnya disimpan ke tabel penilaian_akhir
     */
    public function prosesNilaiAkhir($id_jadwal_semgang)
    {
        $idProdiKaprodi = $this->getKaprodiProdi();

        // Ambil jadwal seminar yang sudah selesai
        $jadwal = JadwalSemgang::where('status_semgang', 'selesai')
            ->where('id_jadwal_semgang', $id_jadwal_semgang)
            ->firstOrFail();

        // CEK AKSES: pastikan mahasiswa ini dari prodi Kaprodi yang login
        if ($jadwal->mahasiswa->id_prodi != $idProdiKaprodi) {
            abort(403, 'Anda tidak memiliki akses untuk memproses nilai mahasiswa ini.');
        }

        // Cek apakah nilai perusahaan sudah ada
        $penilaian = PenilaianMahasiswa::where('id_mahasiswa', $jadwal->id_mahasiswa)->first();
        if (!$penilaian) {
            return redirect()->back()->with('error', 'Nilai perusahaan belum tersedia untuk mahasiswa ini.');
        }

        // Cek apakah nilai dosen pembimbing sudah ada
        if (!$jadwal->rata_rata) {
            return redirect()->back()->with('error', 'Nilai dosen pembimbing belum tersedia untuk mahasiswa ini.');
        }

        // Hitung nilai akhir
        $nilaiPerusahaan = $penilaian->rata_rata;
        $nilaiPembimbing = $jadwal->rata_rata;
        $nilaiAkhir = round(($nilaiPerusahaan * $this->bobot_perusahaan / 100) + ($nilaiPembimbing * $this->bobot_pembimbing / 100), 2);
        $grade = $this->hitungGrade($nilaiAkhir);

        // Simpan ke tabel penilaian_akhir
        // updateOrCreate: kalau sudah ada record (misalnya mau di-reprocess), di-update; kalau belum, dibuat baru
        PenilaianAkhir::updateOrCreate(
            [
                'id_mahasiswa'       => $jadwal->id_mahasiswa,
                'id_jadwal_semgang'  => $jadwal->id_jadwal_semgang,
            ],
            [
                'id_dosen_pembimbing' => $jadwal->id_dosen_pembimbing,
                'id_penilaian'        => $penilaian->id_penilaian,
                'nilai_perusahaan'    => $nilaiPerusahaan,
                'nilai_pembimbing'    => $nilaiPembimbing,
                'nilai_akhir'         => $nilaiAkhir,
                'grade'               => $grade,
            ]
        );

        return redirect()->route('kaprodi.nilai-akhir.index')
            ->with('success', 'Nilai akhir berhasil diproses!');
    }

    // ============================
    // KAPRODI - PROSES SEMUA SEKALI
    // ============================

    /**
     * Proses nilai akhir untuk SEMUA mahasiswa sekaligus
     * 
     * Sama seperti prosesNilaiAkhir tapi di-loop untuk semua mahasiswa
     * Mahasiswa yang nilainya belum lengkap akan dilewati (skip)
     */
    public function prosesSemua()
    {
        $idProdiKaprodi = $this->getKaprodiProdi();

        // Ambil semua jadwal seminar yang sudah selesai dari prodi ini
        $jadwalSelesai = JadwalSemgang::where('status_semgang', 'selesai')
            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->get();

        $prosesCount = 0;

        foreach ($jadwalSelesai as $jadwal) {
            // Ambil nilai perusahaan
            $penilaian = PenilaianMahasiswa::where('id_mahasiswa', $jadwal->id_mahasiswa)->first();

            // Skip kalau salah satu nilai belum ada
            if (!$penilaian || !$jadwal->rata_rata) continue;

            // Hitung & simpan (sama persis seperti prosesNilaiAkhir)
            $nilaiPerusahaan = $penilaian->rata_rata;
            $nilaiPembimbing = $jadwal->rata_rata;
            $nilaiAkhir = round(($nilaiPerusahaan * $this->bobot_perusahaan / 100) + ($nilaiPembimbing * $this->bobot_pembimbing / 100), 2);
            $grade = $this->hitungGrade($nilaiAkhir);

            PenilaianAkhir::updateOrCreate(
                [
                    'id_mahasiswa'       => $jadwal->id_mahasiswa,
                    'id_jadwal_semgang'  => $jadwal->id_jadwal_semgang,
                ],
                [
                    'id_dosen_pembimbing' => $jadwal->id_dosen_pembimbing,
                    'id_penilaian'        => $penilaian->id_penilaian,
                    'nilai_perusahaan'    => $nilaiPerusahaan,
                    'nilai_pembimbing'    => $nilaiPembimbing,
                    'nilai_akhir'         => $nilaiAkhir,
                    'grade'               => $grade,
                ]
            );

            $prosesCount++;
        }

        return redirect()->route('kaprodi.nilai-akhir.index')
            ->with('success', "Berhasil memproses nilai akhir untuk {$prosesCount} mahasiswa!");
    }

    // ============================
    // KAPRODI - DETAIL
    // ============================

    /**
     * Halaman detail nilai akhir: breakdown lengkap per aspek
     * 
     * Menampilkan:
     * - Info mahasiswa, perusahaan, dosen pembimbing
     * - 18 aspek penilaian perusahaan (beserta nilainya)
     * - 6 aspek penilaian dosen pembimbing (beserta nilainya)
     * - Perhitungan akhir: rata-rata × bobot = nilai akhir + grade
     * - Status: apakah sudah disimpan ke tabel penilaian_akhir
     */
    public function detail($id_jadwal_semgang)
    {
        $idProdiKaprodi = $this->getKaprodiProdi();

        // Ambil data jadwal seminar beserta relasinya
        $jadwal = JadwalSemgang::with(['mahasiswa.prodi', 'dosenPembimbing.dosen', 'perusahaan'])
            ->where('id_jadwal_semgang', $id_jadwal_semgang)
            ->firstOrFail();

        // CEK AKSES
        if ($jadwal->mahasiswa->id_prodi != $idProdiKaprodi) {
            abort(403, 'Anda tidak memiliki akses untuk melihat data ini.');
        }

        // Ambil nilai perusahaan
        $penilaian = PenilaianMahasiswa::where('id_mahasiswa', $jadwal->id_mahasiswa)->first();

        // Cek apakah sudah disimpan
        $nilaiAkhirRecord = PenilaianAkhir::where('id_mahasiswa', $jadwal->id_mahasiswa)
            ->where('id_jadwal_semgang', $jadwal->id_jadwal_semgang)
            ->first();

        // Hitung nilai akhir
        $nilaiPerusahaan = $penilaian ? $penilaian->rata_rata : null;
        $nilaiPembimbing = $jadwal->rata_rata;
        $nilaiAkhirCalc = null;
        $gradeCalc = null;

        if ($nilaiPerusahaan !== null && $nilaiPembimbing !== null) {
            $nilaiAkhirCalc = round(($nilaiPerusahaan * $this->bobot_perusahaan / 100) + ($nilaiPembimbing * $this->bobot_pembimbing / 100), 2);
            $gradeCalc = $this->hitungGrade($nilaiAkhirCalc);
        }

        // breakdown detail perusahaan: ambil nilai tiap aspek, tampilkan dengan label yang rapi
        $detailPerusahaan = null;
        if ($penilaian) {
            $detailPerusahaan = [];
            foreach ($this->labelPerusahaan as $field => $label) {
                $detailPerusahaan[$label] = $penilaian->$field;
            }
        }

        // breakdown detail dosen pembimbing: ambil nilai tiap aspek
        $detailPembimbing = null;
        if ($jadwal->nilai_persiapan !== null) {
            $detailPembimbing = [];
            foreach ($this->labelPembimbing as $field => $label) {
                $detailPembimbing[$label] = $jadwal->$field;
            }
        }

        $bobotPerusahaan = $this->bobot_perusahaan;
        $bobotPembimbing = $this->bobot_pembimbing;

        return view('pages.kaprodi.nilai_akhir_mahasiswa.detail', compact(
            'jadwal', 'penilaian', 'nilaiAkhirRecord',
            'nilaiPerusahaan', 'nilaiPembimbing',
            'nilaiAkhirCalc', 'gradeCalc',
            'detailPerusahaan', 'detailPembimbing',
            'bobotPerusahaan', 'bobotPembimbing'
        ));
    }

    // ============================
    // ADMIN - INDEX
    // ============================

    /**
     * Halaman daftar nilai akhir dari sisi Admin
     * 
     * Sama seperti index Kaprodi, tapi:
     * - Tanpa filter prodi (admin lihat semua)
     * - Tanpa tombol proses (admin read-only)
     * - Paginate fix 10 per halaman
     */
    public function indexAdmin()
    {
        $bobotPerusahaan = $this->bobot_perusahaan;
        $bobotPembimbing = $this->bobot_pembimbing;

        // Ambil semua jadwal seminar yang sudah selesai, dari SEMUA prodi
        $jadwalSelesai = JadwalSemgang::with(['mahasiswa.prodi', 'dosenPembimbing.dosen', 'perusahaan'])
            ->where('status_semgang', 'selesai')
            ->orderBy('tgl_semgang', 'desc')
            ->paginate(10);

        // Transform setiap item: hitung nilai akhir dan tambahkan property baru
        $jadwalSelesai->getCollection()->transform(function ($jadwal) use ($bobotPerusahaan, $bobotPembimbing) {
            $penilaian = PenilaianMahasiswa::where('id_mahasiswa', $jadwal->id_mahasiswa)->first();
            $nilaiAkhirRecord = PenilaianAkhir::where('id_mahasiswa', $jadwal->id_mahasiswa)
                ->where('id_jadwal_semgang', $jadwal->id_jadwal_semgang)
                ->first();

            $nilaiPerusahaan = $penilaian ? $penilaian->rata_rata : null;
            $nilaiPembimbing = $jadwal->rata_rata;
            $nilaiAkhirCalc = null;
            $gradeCalc = null;

            if ($nilaiPerusahaan !== null && $nilaiPembimbing !== null) {
                $nilaiAkhirCalc = round(($nilaiPerusahaan * $bobotPerusahaan / 100) + ($nilaiPembimbing * $bobotPembimbing / 100), 2);
                $gradeCalc = $this->hitungGrade($nilaiAkhirCalc);
            }

            // Tambahkan property langsung ke object jadwal
            $jadwal->nilai_perusahaan = $nilaiPerusahaan;
            $jadwal->nilai_pembimbing = $nilaiPembimbing;
            $jadwal->nilai_akhir_calc = $nilaiAkhirCalc;
            $jadwal->grade_calc = $gradeCalc;
            $jadwal->lengkap = ($nilaiPerusahaan !== null && $nilaiPembimbing !== null);
            $jadwal->sudah_disimpan = ($nilaiAkhirRecord !== null);

            return $jadwal;
        });

        return view('pages.admin.nilai_akhir_mahasiswa.index', compact('jadwalSelesai', 'bobotPerusahaan', 'bobotPembimbing'));
    }

    // ============================
    // ADMIN - DETAIL
    // ============================

    /**
     * Halaman detail nilai akhir dari sisi Admin
     * 
     * Sama seperti detail Kaprodi, tapi tanpa cek prodi (admin bisa lihat semua)
     */
    public function detailAdmin($id_jadwal_semgang)
    {
        // Ambil data jadwal seminar (tanpa filter prodi)
        $jadwal = JadwalSemgang::with(['mahasiswa.prodi', 'dosenPembimbing.dosen', 'perusahaan'])
            ->where('id_jadwal_semgang', $id_jadwal_semgang)
            ->firstOrFail();

        $penilaian = PenilaianMahasiswa::where('id_mahasiswa', $jadwal->id_mahasiswa)->first();

        $nilaiAkhirRecord = PenilaianAkhir::where('id_mahasiswa', $jadwal->id_mahasiswa)
            ->where('id_jadwal_semgang', $jadwal->id_jadwal_semgang)
            ->first();

        // Hitung nilai akhir
        $nilaiPerusahaan = $penilaian ? $penilaian->rata_rata : null;
        $nilaiPembimbing = $jadwal->rata_rata;
        $nilaiAkhirCalc = null;
        $gradeCalc = null;

        if ($nilaiPerusahaan !== null && $nilaiPembimbing !== null) {
            $nilaiAkhirCalc = round(($nilaiPerusahaan * $this->bobot_perusahaan / 100) + ($nilaiPembimbing * $this->bobot_pembimbing / 100), 2);
            $gradeCalc = $this->hitungGrade($nilaiAkhirCalc);
        }

        // Breakdown per aspek (sama seperti di detail Kaprodi)
        $detailPerusahaan = null;
        if ($penilaian) {
            $detailPerusahaan = [];
            foreach ($this->labelPerusahaan as $field => $label) {
                $detailPerusahaan[$label] = $penilaian->$field;
            }
        }

        $detailPembimbing = null;
        if ($jadwal->nilai_persiapan !== null) {
            $detailPembimbing = [];
            foreach ($this->labelPembimbing as $field => $label) {
                $detailPembimbing[$label] = $jadwal->$field;
            }
        }

        $bobotPerusahaan = $this->bobot_perusahaan;
        $bobotPembimbing = $this->bobot_pembimbing;

        return view('pages.admin.nilai_akhir_mahasiswa.detail', compact(
            'jadwal', 'penilaian', 'nilaiAkhirRecord',
            'nilaiPerusahaan', 'nilaiPembimbing',
            'nilaiAkhirCalc', 'gradeCalc',
            'detailPerusahaan', 'detailPembimbing',
            'bobotPerusahaan', 'bobotPembimbing'
        ));
    }
}