<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PengajuanMagang;
use App\Models\PengajuanMahasiswa;
use App\Models\Perusahaan;
use App\Models\Notifikasi;
use App\Models\SuratPengajuan;
use App\Models\Mahasiswa;
use App\Models\Pegawai;
use App\Models\Dosen;
use App\Models\Jabatan;
use App\Models\DosenPembimbing;
use App\Models\ApprovalPengajuan;
use App\Models\JabatanTendik;

class PengajuanMagangController extends Controller
{
    // ==================== MAHASISWA ====================

    public function index()
    {
        $idUser = auth()->user()->id_user;
        $userLogin = Mahasiswa::with('prodi')->where('id_user', $idUser)->first();

        if (!$userLogin) {
            abort(403, 'Data mahasiswa tidak ditemukan untuk akun ini.');
        }

        $idMahasiswa = $userLogin->id_mahasiswa;

        $statusSemester = $this->cekSyaratSemesterPengajuan($userLogin);

        $allPengajuans = PengajuanMagang::whereHas('mahasiswa', function ($q) use ($idMahasiswa) {
            $q->where('id_mahasiswa', $idMahasiswa);
        })
        ->with(['perusahaan', 'mahasiswa.mahasiswa', 'approvals.jabatan', 'suratPermohonan', 'suratPengantar'])
        ->orderBy('tgl_pengajuan', 'desc')
        ->get();

        $pengajuanSaatIni = collect();
        $riwayatPengajuan = collect();
        $adaPengajuanBerjalan = false;

        foreach ($allPengajuans as $p) {
            $p->status_alur = $this->hitungStatusAlur($p);

            $myData = $p->mahasiswa->firstWhere('id_mahasiswa', $idMahasiswa);

            $p->my_surat_balasan_status = $myData ? $myData->status_surat_balasan : 'belum_upload';
            $p->my_surat_balasan_file = $myData ? $myData->surat_balasan : null;
            $p->my_status = $myData ? $myData->status : null;
            $p->ada_surat_balasan_kelompok = $p->mahasiswa->whereNotNull('surat_balasan')->count() > 0;

            $p->sudah_ada_surat_permohonan = $p->suratPermohonan != null;
            $p->bisa_upload_surat_balasan = $p->sudah_ada_surat_permohonan && !$p->ada_surat_balasan_kelompok;

            $sayaDitolakDiBalasan = $myData && $myData->status_surat_balasan === 'ditolak';
            $sayaDitolak = $myData && $myData->status === 'ditolak';
            $aplikasiDitolakAtas = str_contains($p->status_alur['text'], 'Ditolak') || str_contains($p->status_alur['text'], 'Dikembalikan');
            $prosesSelesai = $p->suratPengantar != null;

            $masukRiwayat = $sayaDitolak || $sayaDitolakDiBalasan || $aplikasiDitolakAtas || $prosesSelesai;
            if ($masukRiwayat) {
                $riwayatPengajuan->push($p);
            } else {
                $pengajuanSaatIni->push($p);
            }

            if (!$masukRiwayat) {
                $adaPengajuanBerjalan = true;
            }

            // ==========================================================
            // LOGIKA: CEK APAKAH MAHASISWA BOLEH MEMBATALKAN PENGAJUAN
            // ==========================================================
            $p->boleh_batal = false;
            $p->alasan_tidak_bisa_batal = null;

            $syaratDasarBatal = is_null($p->suratPengantar)
                && $p->suratPermohonan
                && !$aplikasiDitolakAtas
                && !$sayaDitolakDiBalasan
                && !$sayaDitolak;

            if ($syaratDasarBatal) {
                $tglSuratTurun = $p->suratPermohonan
                    ? \Carbon\Carbon::parse($p->suratPermohonan->uploaded_at)->startOfDay()
                    : null;

                if (is_null($tglSuratTurun)) {
                    $tglSuratTurun = \Carbon\Carbon::parse($p->tgl_pengajuan)->startOfDay();
                }

                $today = \Carbon\Carbon::now()->startOfDay();

                if (is_null($p->tgl_mulai)) {
                    $p->boleh_batal = false;
                    $p->alasan_tidak_bisa_batal = 'Tanggal magang belum ditentukan Kaprodi';
                } else {
                    $tglMulai = \Carbon\Carbon::parse($p->tgl_mulai)->startOfDay();
                    $jarakKeMulai = $tglSuratTurun->diffInDays($tglMulai);

                    if ($jarakKeMulai <= 50) {
                        $durasiTunggu = 14;
                        $ketTunggu = '2 minggu';
                    } else {
                        $durasiTunggu = 30;
                        $ketTunggu = '1 bulan';
                    }

                    $batasWaktuBatal = $tglSuratTurun->copy()->addDays($durasiTunggu);
                    $sisaHari = $today->diffInDays($batasWaktuBatal, false);

                    if ($today->greaterThanOrEqualTo($batasWaktuBatal)) {
                        $p->boleh_batal = true;
                    } else {
                        $p->boleh_batal = false;
                        $p->alasan_tidak_bisa_batal = 'Bisa dibatalkan setelah ' . $batasWaktuBatal->locale('id')->translatedFormat('d F Y')
                            . ' (tunggu ' . max($sisaHari, 1) . ' hari lagi, masa tunggu ' . $ketTunggu . ')';
                    }
                }
            } else {
                if (!is_null($p->suratPengantar)) {
                    $p->alasan_tidak_bisa_batal = 'Sudah ada Surat Pengantar';
                } elseif (!$p->suratPermohonan) {
                    $p->alasan_tidak_bisa_batal = 'Belum ada Surat Permohonan';
                } elseif ($sayaDitolak) {
                    $p->alasan_tidak_bisa_batal = 'Sudah dibatalkan';
                }
            }
        }

        $bisaAjukanBaru = !$adaPengajuanBerjalan && $statusSemester['boleh_ajukan'];

        return view('pages.mahasiswa.pengajuan_magang.index', compact('pengajuanSaatIni', 'riwayatPengajuan', 'bisaAjukanBaru', 'statusSemester', 'adaPengajuanBerjalan'));
    }

    public function create()
    {
        $idUser = auth()->user()->id_user;
        $userLogin = Mahasiswa::with('prodi')->where('id_user', $idUser)->first();

        $statusSemester = $this->cekSyaratSemesterPengajuan($userLogin);

        if (!$statusSemester['boleh_ajukan']) {
            return redirect()->route('mahasiswa.pengajuan_magang.index')
                ->with('error', 'Anda belum memasuki semester yang diizinkan untuk mengajukan permohonan magang. Pengajuan dibuka mulai Semester ' . $statusSemester['min_semester'] . '.');
        }

        $pengajuansAktif = PengajuanMagang::whereHas('mahasiswa', function ($q) use ($userLogin) {
            $q->where('id_mahasiswa', $userLogin->id_mahasiswa);
        })
        ->with(['approvals.jabatan', 'mahasiswa', 'suratPengantar'])
        ->get();

        $adaPengajuanBerjalan = false;
        foreach ($pengajuansAktif as $p) {
            $status = $this->hitungStatusAlur($p);
            $myData = $p->mahasiswa->firstWhere('id_mahasiswa', $userLogin->id_mahasiswa);

            $sayaDitolakDiBalasan = $myData && $myData->status_surat_balasan === 'ditolak';
            $sayaDitolak = $myData && $myData->status === 'ditolak';
            $aplikasiDitolakAtas = str_contains($status['text'], 'Ditolak') || str_contains($status['text'], 'Dikembalikan');
            $prosesSelesai = $p->suratPengantar != null;
            $masukRiwayat = $sayaDitolak || $sayaDitolakDiBalasan || $aplikasiDitolakAtas || $prosesSelesai;

            if (!$masukRiwayat) {
                $adaPengajuanBerjalan = true;
                break;
            }
        }

        if ($adaPengajuanBerjalan) {
            return redirect()->route('mahasiswa.pengajuan_magang.index')
                ->with('error', 'Anda belum diizinkan untuk mengajukan permohonan lagi. Pengajuan sebelumnya masih dalam proses.');
        }

        $perusahaan = Perusahaan::all();

        $idPengajuanAktif = PengajuanMagang::whereDoesntHave('suratPengantar')->pluck('id_pengajuan')->toArray();

        $idMahasiswaSibuk = PengajuanMahasiswa::whereIn('id_pengajuan', $idPengajuanAktif)
            ->where('status', 'diajukan')
            ->where(function ($query) {
                $query->where('status_surat_balasan', '!=', 'ditolak')
                    ->orWhereNull('status_surat_balasan');
            })
            ->pluck('id_mahasiswa')
            ->toArray();

        $mahasiswaLain = Mahasiswa::where('id_user', '!=', $idUser)
            ->whereNotIn('id_mahasiswa', $idMahasiswaSibuk)
            ->where('id_prodi', $userLogin->id_prodi)
            ->where('angkatan', $userLogin->angkatan)
            ->whereNotNull('id_user')
            ->get();
 $sudahUploadTTD = !empty($userLogin->ttd_digital);

return view('pages.mahasiswa.pengajuan_magang.create', compact('perusahaan', 'userLogin', 'mahasiswaLain', 'sudahUploadTTD'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_perusahaan'        => 'required',
            'ditujukan_kepada'     => 'required|string|max:255',
            'contact_person'       => 'required|string|max:100',
            'no_hp_cp'             => 'required|string|max:20',
            'email_perusahaan'     => 'nullable|email|max:100',
            'deskripsi_perusahaan' => 'nullable|string',
            'catatan'              => 'nullable|string',
            'mahasiswa'            => 'required|array|min:1',
            'mahasiswa.*.id'       => 'required',
            'mahasiswa.*.ipk'      => 'required|numeric|min:0|max:4.00',
            'mahasiswa.*.surat_ortu' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $idPerusahaan = $request->id_perusahaan;

        if ($idPerusahaan === 'new') {
            $request->validate([
                'nama_perusahaan_baru' => 'required|string|max:255',
                'alamat_perusahaan_baru' => 'required|string',
            ]);

            $namaBaru = $request->nama_perusahaan_baru;
            $sudahAda = Perusahaan::where('nama_perusahaan', $namaBaru)->exists();

            if ($sudahAda) {
                return back()
                    ->withErrors(['nama_perusahaan_baru' => 'Gagal menambahkan! Perusahaan dengan nama "' . $namaBaru . '" sudah terdaftar di sistem. Silakan pilih dari dropdown.'])
                    ->withInput();
            }

            $perusahaanBaru = Perusahaan::create([
                'nama_perusahaan' => $namaBaru,
                'alamat'          => $request->alamat_perusahaan_baru,
            ]);

            $idPerusahaan = $perusahaanBaru->id_perusahaan;
        } else {
            $request->validate([
                'id_perusahaan' => 'required|exists:perusahaan,id_perusahaan'
            ]);
        }

        $pengajuan = PengajuanMagang::create([
            'id_user'              => auth()->user()->id_user,
            'id_perusahaan'        => $idPerusahaan,
            'tgl_pengajuan'        => now()->format('Y-m-d'),
            'ditujukan_kepada'     => $request->ditujukan_kepada,
            'email_perusahaan'     => $request->email_perusahaan,
            'contact_person'       => $request->contact_person,
            'no_hp_cp'             => $request->no_hp_cp,
            'deskripsi_perusahaan' => $request->deskripsi_perusahaan,
            'catatan'              => $request->catatan,
        ]);

        $idUser = auth()->user()->id_user;
        $userLogin = Mahasiswa::where('id_user', $idUser)->first();

        foreach ($request->mahasiswa as $key => $mhs) {
            $namaFileOrtu = null;
            if (isset($mhs['surat_ortu']) && $mhs['surat_ortu']) {
                $fileOrtu = $mhs['surat_ortu'];
                $namaFileOrtu = time() . '_ortu_' . $key . '_' . $fileOrtu->getClientOriginalName();
                $fileOrtu->storeAs('surat_ortu', $namaFileOrtu, 'public');
            }

            $idMahasiswa = ($key == 1) ? $userLogin->id_mahasiswa : $mhs['id'];

            PengajuanMahasiswa::create([
                'id_pengajuan' => $pengajuan->id_pengajuan,
                'id_mahasiswa' => $idMahasiswa,
                'ipk'          => $mhs['ipk'],
                'status'       => 'diajukan',
                'surat_ortu'   => $namaFileOrtu,
            ]);
        }

        $idProdiPengaju = $userLogin->id_prodi;
        Notifikasi::kirimKeKaprodiProdi(
            $idProdiPengaju,
            'Pengajuan Magang Baru',
            'Mahasiswa ' . $userLogin->nama . ' mengajukan magang ke ' . (Perusahaan::find($idPerusahaan)->nama_perusahaan ?? '-'),
            'kaprodi_pengajuan_baru',
            $pengajuan->id_pengajuan
        );

        return redirect()->route('mahasiswa.pengajuan_magang.index')
            ->with('success', 'Pengajuan berhasil dikirim!');
    }

    public function show($id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $userLogin = Mahasiswa::where('id_user', $idUser)->first();

        if (!$userLogin) {
            abort(403, 'Data mahasiswa tidak ditemukan.');
        }

        $adaDiSurat = PengajuanMahasiswa::where('id_pengajuan', $id_pengajuan)
            ->where('id_mahasiswa', $userLogin->id_mahasiswa)
            ->exists();

        if (!$adaDiSurat) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $pengajuan = PengajuanMagang::with([
            'perusahaan',
            'mahasiswa.mahasiswa.prodi',
            'approvals.jabatan',
            'approvals.dosen',
            'suratPermohonan',
            'suratPengantar'
        ])->findOrFail($id_pengajuan);

        $statusAlur = $this->hitungStatusAlur($pengajuan);

        $jabatanKaprodi = Jabatan::where('kode_jabatan', 'Kaprodi')
            ->orWhere('nama_jabatan', 'LIKE', '%ketua program studi%')
            ->first();
        $approvalKaprodi = null;
        $dosen = null;

        if ($jabatanKaprodi) {
            $approvalKaprodi = ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)
                ->where('id_jabatan', $jabatanKaprodi->id_jabatan)
                ->where('status', 'disetujui')
                ->first();
        }

        if ($approvalKaprodi) {
            $dosen = $approvalKaprodi->dosen;
        }

        return view('pages.mahasiswa.pengajuan_magang.detail', compact('pengajuan', 'statusAlur', 'approvalKaprodi', 'dosen'));
    }

    public function formUploadSuratBalasan($id_pengajuan)
    {
        $pengajuan = PengajuanMagang::with('perusahaan', 'mahasiswa.mahasiswa', 'suratPermohonan')
            ->findOrFail($id_pengajuan);

        $idUser = auth()->user()->id_user;
        $userLogin = Mahasiswa::where('id_user', $idUser)->first();

        if (!$pengajuan->suratPermohonan) {
            return redirect()->route('mahasiswa.pengajuan_magang.index')
                ->with('error', 'Surat balasan belum bisa diupload. Surat permohonan dari BAAK belum tersedia.');
        }

        $suratBalasan = null;
        $statusSuratBalasan = 'belum_upload';

        $mhsDenganSurat = $pengajuan->mahasiswa->firstWhere('surat_balasan', '!=', null);
        if ($mhsDenganSurat) {
            $suratBalasan = $mhsDenganSurat->surat_balasan;

            $myData = $pengajuan->mahasiswa->firstWhere('id_mahasiswa', $userLogin->id_mahasiswa);
            if ($myData) {
                $statusSuratBalasan = $myData->status_surat_balasan;

                if ($statusSuratBalasan == 'belum_upload' && $suratBalasan) {
                    PengajuanMahasiswa::where('id_pengajuan', $id_pengajuan)
                        ->where('id_mahasiswa', $userLogin->id_mahasiswa)
                        ->update([
                            'status_surat_balasan' => 'menunggu_verifikasi',
                            'surat_balasan'        => $suratBalasan
                        ]);
                    $statusSuratBalasan = 'menunggu_verifikasi';
                }
            }
        }

        return view(
            'pages.mahasiswa.pengajuan_magang.upload_surat_balasan',
            compact('pengajuan', 'suratBalasan', 'userLogin', 'statusSuratBalasan')
        );
    }

    public function uploadSuratBalasan(Request $request, $id_pengajuan)
    {
        $pengajuan = PengajuanMagang::with('suratPermohonan')->findOrFail($id_pengajuan);

        if (!$pengajuan->suratPermohonan) {
            return redirect()->route('mahasiswa.pengajuan_magang.index')
                ->with('error', 'Gagal upload! Surat permohonan dari BAAK belum tersedia.');
        }

        $request->validate([
            'surat_balasan' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $idUser = auth()->user()->id_user;
        $userLogin = Mahasiswa::where('id_user', $idUser)->first();
        $file = $request->file('surat_balasan');
        $namaFile = time() . '_surat_balasan_' . $file->getClientOriginalName();
        $file->storeAs('surat_balasan', $namaFile, 'public');

        PengajuanMahasiswa::where('id_pengajuan', $id_pengajuan)
            ->whereNotIn('status_surat_balasan', ['diterima'])
            ->update([
                'surat_balasan'        => $namaFile,
                'status_surat_balasan' => 'menunggu_verifikasi'
            ]);

        PengajuanMahasiswa::where('id_pengajuan', $id_pengajuan)
            ->where('status_surat_balasan', 'diterima')
            ->whereNull('surat_balasan')
            ->update([
                'surat_balasan' => $namaFile
            ]);

        Notifikasi::kirimKeKaprodiProdi(
            $userLogin->id_prodi,
            'Surat Balasan Diupload',
            'Surat balasan dari ' . ($pengajuan->perusahaan->nama_perusahaan ?? '-') . ' telah diupload. Menunggu verifikasi Anda.',
            'kaprodi_surat_balasan',
            $id_pengajuan
        );

        return redirect()
            ->route('mahasiswa.pengajuan_magang.index')
            ->with('success', 'Surat balasan berhasil diupload. Menunggu verifikasi Kaprodi.');
    }

    // ==================== HELPER: HITUNG STATUS ALUR ====================
    // ====================================================================
    // DIAGRAM STATUS YANG DITERAPKAN:
    // ====================================================================
    // 1. "Dibatalkan" (RED)           → Semua mahasiswa status = 'ditolak'
    // 2. "Menunggu Kaprodi" (YELLOW)  → Belum ada approval Kaprodi
    // 3. "Ditolak Kaprodi" (RED)      → Kaprodi menolak
    // 4. "Menunggu Admin Kajur" (BLUE) → Kaprodi setuju, belum ada Admin Kajur
    // 5. "Dikembalikan Admin Kajur" (ORANGE) → Admin Kajur menolak
    // 6. "Menunggu BAAK" (GREEN)     → Admin Kajur setuju, belum ada BAAK
    // 7. "Ditolak BAAK" (RED)        → BAAK menolak
    // 8. "Selesai" (GREEN)           → Semua approval disetujui
    // ====================================================================
    private function hitungStatusAlur($pengajuan)
    {
        $totalMhs = $pengajuan->mahasiswa->count();
        $mhsDitolak = $pengajuan->mahasiswa->where('status', 'ditolak')->count();

        // 1. CEK: Apakah semua mahasiswa sudah ditolak/dibatalkan
        if ($totalMhs > 0 && $totalMhs === $mhsDitolak) {
            return ['text' => 'Dibatalkan', 'color' => 'bg-red-100 text-red-700 border-red-300'];
        }

        // Cari jabatan — pakai kedua cara biar cocok data lama & baru
        $jabatanKaprodi = Jabatan::where('kode_jabatan', 'Kaprodi')
            ->orWhere('nama_jabatan', 'LIKE', '%ketua program studi%')
            ->first();
         $jabatanAdminKajur = Jabatan::where('kode_jabatan', 'J004')->first();
        $jabatanKajurLama = Jabatan::where('nama_jabatan', 'LIKE', '%ketua jurusan%')->first();
       $jabatanBaak = Jabatan::where('kode_jabatan', 'J001')
    ->orWhere('kode_jabatan', 'BAAK')
    ->first();

        // 2. CEK KAPRODI
        $approvalKaprodi = null;
        if ($jabatanKaprodi) {
            $approvalKaprodi = $pengajuan->approvals->firstWhere('id_jabatan', $jabatanKaprodi->id_jabatan);
        }

        if (!$approvalKaprodi) {
            return ['text' => 'Menunggu Kaprodi', 'color' => 'bg-yellow-100 text-yellow-700 border-yellow-300'];
        }

        if ($approvalKaprodi->status === 'ditolak') {
            return ['text' => 'Ditolak Kaprodi', 'color' => 'bg-red-100 text-red-700 border-red-300'];
        }

        // 3. CEK ADMIN KAJUR — fallback ke Kajur lama untuk data migrasi
        $approvalAdminKajur = null;
        if ($jabatanAdminKajur) {
            $approvalAdminKajur = $pengajuan->approvals->firstWhere('id_jabatan', $jabatanAdminKajur->id_jabatan);
        }
        if (!$approvalAdminKajur && $jabatanKajurLama) {
            $approvalAdminKajur = $pengajuan->approvals->firstWhere('id_jabatan', $jabatanKajurLama->id_jabatan);
        }

        if (!$approvalAdminKajur) {
            return ['text' => 'Menunggu Admin Kajur', 'color' => 'bg-blue-100 text-blue-700 border-blue-300'];
        }

        //  PENTING: Admin Kajur "ditolak" → tampil "Dikembalikan" (ORANGE), bukan "Ditolak" (RED)
        if ($approvalAdminKajur->status === 'ditolak') {
            return ['text' => 'Dikembalikan Admin Kajur', 'color' => 'bg-orange-100 text-orange-700 border-orange-300'];
        }

        // 4. CEK BAAK
        $approvalBaak = null;
        if ($jabatanBaak) {
            $approvalBaak = $pengajuan->approvals->firstWhere('id_jabatan', $jabatanBaak->id_jabatan);
        }

        if (!$approvalBaak) {
            return ['text' => 'Menunggu BAAK', 'color' => 'bg-green-100 text-green-700 border-green-300'];
        }

        if ($approvalBaak->status === 'ditolak') {
            return ['text' => 'Ditolak BAAK', 'color' => 'bg-red-100 text-red-700 border-red-300'];
        }

        // 5. SEMUA SETUJUI → SELESAI
        return ['text' => 'Selesai', 'color' => 'bg-green-100 text-green-700 border-green-300'];
    }

    // ==================== HELPER: HITUNG STATUS ALUR KHUSUS BAAK ====================
    // ====================================================================
    // BAAK hanya melihat yang sudah lewat Kaprodi & Admin Kajur
    // ====================================================================
    // 1. "Menunggu BAAK" (BLUE)     → Belum ada approval BAAK
    // 2. "Ditolak BAAK" (RED)        → BAAK menolak
    // 3. "Ditolak" (RED)            → Semua mahasiswa ditolak
    // 4. "Selesai" (GREEN)          → Semua approval disetujui
    // ====================================================================
    private function hitungStatusAlurBaak($pengajuan)
    {
         $jabatanBaak = Jabatan::where('kode_jabatan', 'J001')
        ->orWhere('kode_jabatan', 'BAAK')
        ->first();

        $approvalBaak = null;
        if ($jabatanBaak) {
            $approvalBaak = $pengajuan->approvals->firstWhere('id_jabatan', $jabatanBaak->id_jabatan);
        }

        // 1. Menunggu BAAK
        if (!$approvalBaak) {
            return ['text' => 'Menunggu BAAK', 'color' => 'bg-blue-100 text-blue-700 border-blue-300'];
        }

        // 2. Ditolak BAAK
        if ($approvalBaak->status === 'ditolak') {
            return ['text' => 'Ditolak BAAK', 'color' => 'bg-red-100 text-red-700 border-red-300'];
        }

        // 3. Cek apakah semua mahasiswa ditolak
        $totalMhs = $pengajuan->mahasiswa->count();
        $mhsDitolak = $pengajuan->mahasiswa->where('status', 'ditolak')->count();

        if ($totalMhs > 0 && $totalMhs === $mhsDitolak) {
            return ['text' => 'Ditolak', 'color' => 'bg-red-100 text-red-700 border-red-300'];
        }

        // 4. Selesai
        return ['text' => 'Selesai', 'color' => 'bg-green-100 text-green-700 border-green-300'];
    }

    // ==================== KAJUR (MONITORING ONLY - TIDAK BISA APPROVE) ====================

    public function indexKajur(Request $request)
    {
        $idUser = auth()->user()->id_user;
        $dosen = Dosen::where('id_user', $idUser)->first();

        if (!$dosen) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $prodiKajur = \App\Models\Prodi::find($dosen->id_prodi);
        $idJurusanKajur = $prodiKajur ? $prodiKajur->id_jurusan : null;

        if (!$idJurusanKajur) {
            abort(403, 'Jurusan tidak ditemukan.');
        }

        $search          = $request->input('search');
        $menungguPerPage = in_array($request->input('menunggu_per_page'), [5, 10, 15]) ? $request->input('menunggu_per_page') : 5;
        $riwayatPerPage  = in_array($request->input('riwayat_per_page'), [5, 10, 15]) ? $request->input('riwayat_per_page') : 5;

        $baseWith = ['perusahaan', 'mahasiswa.mahasiswa.prodi', 'approvals.jabatan', 'suratPermohonan', 'suratPengantar'];

        $filterJurusan = function ($q) use ($idJurusanKajur) {
            $q->where('id_jurusan', $idJurusanKajur);
        };

        // MENUNGGU: belum ada surat pengantar + masih ada mhs aktif
        $menungguQuery = PengajuanMagang::with($baseWith)
            ->whereHas('mahasiswa.mahasiswa.prodi', $filterJurusan)
            ->whereDoesntHave('suratPengantar')
            ->whereHas('mahasiswa', function ($q) {
                $q->where('status', '!=', 'ditolak');
            });

        // RIWAYAT: sudah ada surat pengantar ATAU semua mhs ditolak
        $riwayatQuery = PengajuanMagang::with($baseWith)
            ->whereHas('mahasiswa.mahasiswa.prodi', $filterJurusan)
            ->where(function ($q) {
                $q->whereHas('suratPengantar')
                  ->orWhereDoesntHave('mahasiswa', function ($q2) {
                      $q2->where('status', '!=', 'ditolak');
                  });
            });

        if ($search) {
            $searchClosure = function ($q) use ($search) {
                $q->whereHas('perusahaan', function ($q2) use ($search) {
                    $q2->where('nama_perusahaan', 'like', '%' . $search . '%');
                })
                ->orWhereHas('mahasiswa.mahasiswa', function ($q2) use ($search) {
                    $q2->where('nama', 'like', '%' . $search . '%')
                       ->orWhere('npm', 'like', '%' . $search . '%');
                });
            };
            $menungguQuery->where($searchClosure);
            $riwayatQuery->where($searchClosure);
        }

        $menungguList = $menungguQuery->orderBy('tgl_pengajuan', 'desc')
            ->paginate($menungguPerPage, ['*'], 'menunggu_page')
            ->withQueryString();

        $riwayatList = $riwayatQuery->orderBy('tgl_pengajuan', 'desc')
            ->paginate($riwayatPerPage, ['*'], 'riwayat_page')
            ->withQueryString();

        foreach ($menungguList as $p) {
            $p->status_alur = $this->hitungStatusAlur($p);
        }
        foreach ($riwayatList as $p) {
            $p->status_alur = $this->hitungStatusAlur($p);
        }

        return view('pages.kajur.pengajuan_magang.index',
            compact('menungguList', 'riwayatList', 'search', 'menungguPerPage', 'riwayatPerPage'));
    }

    public function detailKajur($id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $dosen = Dosen::where('id_user', $idUser)->first();

        if (!$dosen) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $pengajuan = PengajuanMagang::with([
            'perusahaan',
            'mahasiswa.mahasiswa.prodi',
            'approvals.dosen',
            'approvals.pegawai',
            'approvals.jabatan',
            'suratPermohonan',
            'suratPengantar'
        ])->findOrFail($id_pengajuan);

        $prodiKajur = \App\Models\Prodi::find($dosen->id_prodi);
        $idJurusanKajur = $prodiKajur ? $prodiKajur->id_jurusan : null;

        $adaMahasiswaSeJurusan = $pengajuan->mahasiswa->contains(function ($pm) use ($idJurusanKajur) {
            return $pm->mahasiswa && $pm->mahasiswa->prodi && $pm->mahasiswa->prodi->id_jurusan == $idJurusanKajur;
        });

        if (!$adaMahasiswaSeJurusan) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini. Pengajuan bukan dari jurusan Anda.');
        }

         $jabatanAdminKajur = Jabatan::where('kode_jabatan', 'J004')->first();
        $jabatanKajurLama = Jabatan::where('nama_jabatan', 'LIKE', '%ketua jurusan%')->first();
        $jabatanKaprodi = Jabatan::where('kode_jabatan', 'Kaprodi')
            ->orWhere('nama_jabatan', 'LIKE', '%ketua program studi%')
            ->first();

        $approvalAdminKajur = null;
        if ($jabatanAdminKajur) {
            $approvalAdminKajur = ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)
                ->where('id_jabatan', $jabatanAdminKajur->id_jabatan)
                ->first();
        }
        if (!$approvalAdminKajur && $jabatanKajurLama) {
            $approvalAdminKajur = ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)
                ->where('id_jabatan', $jabatanKajurLama->id_jabatan)
                ->first();
        }

        $approvalKaprodi = null;
        if ($jabatanKaprodi) {
            $approvalKaprodi = ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)
                ->where('id_jabatan', $jabatanKaprodi->id_jabatan)
                ->where('status', 'disetujui')
                ->first();
        }

        return view('pages.kajur.pengajuan_magang.detail', compact('pengajuan', 'dosen', 'approvalAdminKajur', 'approvalKaprodi'));
    }

    // ==================== ADMIN KAJUR (YANG APPROVE) ====================

    public function indexAdminKajur(Request $request)
    {
        $idUser = auth()->user()->id_user;
        $pegawai = Pegawai::where('id_user', $idUser)->first();

        if (!$pegawai) {
            abort(403, 'Data pegawai tidak ditemukan.');
        }

        $isAdminKajur = JabatanTendik::where('id_pegawai', $pegawai->id_pegawai)
            ->whereHas('jabatan', function ($q) {
                $q->where('kode_jabatan', 'J004');
            })
            ->exists();

        if (!$isAdminKajur) {
            abort(403, 'Anda tidak memiliki akses Admin Kajur.');
        }

        $jabatanKaprodi = Jabatan::where('kode_jabatan', 'Kaprodi')
            ->orWhere('nama_jabatan', 'LIKE', '%ketua program studi%')
            ->first();
         $jabatanAdminKajur = Jabatan::where('kode_jabatan', 'J004')->first();

        $search          = $request->input('search');
        $menungguPerPage = in_array($request->input('menunggu_per_page'), [5, 10, 15]) ? $request->input('menunggu_per_page') : 5;
        $riwayatPerPage  = in_array($request->input('riwayat_per_page'), [5, 10, 15]) ? $request->input('riwayat_per_page') : 5;

        $baseWith = ['perusahaan', 'mahasiswa.mahasiswa.prodi', 'approvals.jabatan', 'approvals.pegawai', 'suratPermohonan', 'suratPengantar'];

        // MENUNGGU: Kaprodi sudah setuju, Admin Kajur belum approve/ditolak, belum ada surat pengantar
        $menungguQuery = PengajuanMagang::with($baseWith);

        if ($jabatanKaprodi) {
            $menungguQuery->whereHas('approvals', function ($q) use ($jabatanKaprodi) {
                $q->where('id_jabatan', $jabatanKaprodi->id_jabatan)
                  ->where('status', 'disetujui');
            });
        } else {
            $menungguQuery->whereRaw('1 = 0');
        }

        $menungguQuery->whereDoesntHave('suratPengantar')
            ->whereHas('mahasiswa', function ($q) {
                $q->where('status', '!=', 'ditolak');
            });

        if ($jabatanAdminKajur) {
            $menungguQuery->whereDoesntHave('approvals', function ($q) use ($jabatanAdminKajur) {
                $q->where('id_jabatan', $jabatanAdminKajur->id_jabatan)
                  ->where('status', 'ditolak');
            });
        }

        // RIWAYAT: sudah ada surat pengantar ATAU semua mhs ditolak ATAU Admin Kajur sudah tolak
        $riwayatQuery = PengajuanMagang::with($baseWith);

        if ($jabatanKaprodi) {
            $riwayatQuery->whereHas('approvals', function ($q) use ($jabatanKaprodi) {
                $q->where('id_jabatan', $jabatanKaprodi->id_jabatan)
                  ->where('status', 'disetujui');
            });
        } else {
            $riwayatQuery->whereRaw('1 = 0');
        }

        $riwayatQuery->where(function ($q) use ($jabatanAdminKajur) {
                $q->whereHas('suratPengantar')
                  ->orWhereDoesntHave('mahasiswa', function ($q2) {
                      $q2->where('status', '!=', 'ditolak');
                  });
                if ($jabatanAdminKajur) {
                    $q->orWhereHas('approvals', function ($q2) use ($jabatanAdminKajur) {
                        $q2->where('id_jabatan', $jabatanAdminKajur->id_jabatan)
                          ->where('status', 'ditolak');
                    });
                }
            });

        if ($search) {
            $searchClosure = function ($q) use ($search) {
                $q->whereHas('perusahaan', function ($q2) use ($search) {
                    $q2->where('nama_perusahaan', 'like', '%' . $search . '%');
                })
                ->orWhereHas('mahasiswa.mahasiswa', function ($q2) use ($search) {
                    $q2->where('nama', 'like', '%' . $search . '%')
                       ->orWhere('npm', 'like', '%' . $search . '%');
                });
            };
            $menungguQuery->where($searchClosure);
            $riwayatQuery->where($searchClosure);
        }

        $menungguList = $menungguQuery->orderBy('tgl_pengajuan', 'desc')
            ->paginate($menungguPerPage, ['*'], 'menunggu_page')
            ->withQueryString();

        $riwayatList = $riwayatQuery->orderBy('tgl_pengajuan', 'desc')
            ->paginate($riwayatPerPage, ['*'], 'riwayat_page')
            ->withQueryString();

        foreach ($menungguList as $p) {
            $p->status_alur = $this->hitungStatusAlur($p);
        }
        foreach ($riwayatList as $p) {
            $p->status_alur = $this->hitungStatusAlur($p);
        }

        return view('pages.admin_kajur.pengajuan_magang.index',
            compact('menungguList', 'riwayatList', 'search', 'menungguPerPage', 'riwayatPerPage'));
    }

    public function detailAdminKajur($id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $pegawai = Pegawai::where('id_user', $idUser)->first();

        if (!$pegawai) {
            abort(403, 'Data pegawai tidak ditemukan.');
        }

        $isAdminKajur = JabatanTendik::where('id_pegawai', $pegawai->id_pegawai)
            ->whereHas('jabatan', function ($q) {
                $q->where('kode_jabatan', 'J004');
            })
            ->exists();

        if (!$isAdminKajur) {
            abort(403, 'Anda tidak memiliki akses Admin Kajur.');
        }

        $pengajuan = PengajuanMagang::with([
            'perusahaan',
            'mahasiswa.mahasiswa.prodi',
            'approvals.dosen',
            'approvals.pegawai',
            'approvals.jabatan',
            'suratPermohonan',
            'suratPengantar'
        ])->findOrFail($id_pengajuan);

         $jabatanAdminKajur = Jabatan::where('kode_jabatan', 'J004')->first();

        $approvalAdminKajur = null;
        if ($pegawai && $jabatanAdminKajur) {
            $approvalAdminKajur = ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)
                                ->where('id_pegawai', $pegawai->id_pegawai)
                                ->where('id_jabatan', $jabatanAdminKajur->id_jabatan)
                                ->first();
        }

        $jabatanKaprodi = Jabatan::where('kode_jabatan', 'Kaprodi')
            ->orWhere('nama_jabatan', 'LIKE', '%ketua program studi%')
            ->first();
        $approvalKaprodi = null;
        if ($jabatanKaprodi) {
            $approvalKaprodi = ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)
                                ->where('id_jabatan', $jabatanKaprodi->id_jabatan)
                                ->where('status', 'disetujui')
                                ->first();
        }

        return view('pages.admin_kajur.pengajuan_magang.detail', compact('pengajuan', 'pegawai', 'approvalAdminKajur', 'approvalKaprodi'));
    }

    public function prosesApprovalAdminKajur(Request $request, $id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $pegawai = Pegawai::where('id_user', $idUser)->first();

        if (!$pegawai) {
            abort(403, 'Data pegawai tidak ditemukan.');
        }

         $jabatanAdminKajur = Jabatan::where('kode_jabatan', 'J004')->first();

        if (!$jabatanAdminKajur) {
            abort(403, 'Jabatan Admin Kajur tidak ditemukan di sistem.');
        }

        $request->validate([
            'status_approval' => 'required|in:disetujui,ditolak',
            'catatan_approval' => 'nullable|string',
        ]);

        $pengajuan = PengajuanMagang::with('mahasiswa.mahasiswa', 'perusahaan')->findOrFail($id_pengajuan);

        $sudahAda = ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)
                    ->where('id_pegawai', $pegawai->id_pegawai)
                    ->where('id_jabatan', $jabatanAdminKajur->id_jabatan)
                    ->exists();

        if ($sudahAda) {
            return back()->withErrors('Anda sudah melakukan approval untuk pengajuan ini.');
        }

        ApprovalPengajuan::create([
            'id_pengajuan' => $id_pengajuan,
            'id_pegawai'   => $pegawai->id_pegawai,
            'id_jabatan'   => $jabatanAdminKajur->id_jabatan,
            'status'       => $request->status_approval,
            'catatan'      => $request->catatan_approval,
            'approved_at'  => now(),
        ]);

        if ($request->status_approval === 'ditolak') {
            PengajuanMahasiswa::where('id_pengajuan', $id_pengajuan)
                ->where('status', 'diajukan')
                ->update(['status' => 'ditolak']);
        }

 $namaPerusahaan = $pengajuan->perusahaan->nama_perusahaan ?? 'Perusahaan';
if ($request->status_approval === 'disetujui') {
    // Notifikasi ke mahasiswa
    Notifikasi::kirimKeMahasiswaPengajuan(
        $pengajuan,
        'Pengajuan Magang Disetujui Admin Kajur',
        'Pengajuan magang Anda telah disetujui oleh Admin Kajur.',
        'approval_admin_kajur'
    );
    // Notifikasi ke BAAK
    Notifikasi::kirimKeBaak(
        'Pengajuan Magang Menunggu Persetujuan',
        'Pengajuan magang ke ' . $namaPerusahaan . ' telah disetujui Kaprodi & Admin Kajur. Menunggu persetujuan Anda.',
        'baak_pengajuan_magang',
        $id_pengajuan
    );
} else {
    // Notifikasi ke mahasiswa - "DIKEMBALIKAN"
    Notifikasi::kirimKeMahasiswaPengajuan(
        $pengajuan,
        'Pengajuan Magang Dikembalikan Admin Kajur',
        'Pengajuan magang Anda dikembalikan oleh Admin Kajur. Silakan perbaiki dan ajukan kembali.',
        'dikembalikan_admin_kajur'
    );
}

        $pesan = $request->status_approval === 'disetujui'
            ? 'Pengajuan berhasil diteruskan ke BAAK.'
            : 'Pengajuan dikembalikan ke mahasiswa.';

        return redirect()
                ->route('admin_kajur.pengajuan_magang.detail', $id_pengajuan)
                ->with('success', $pesan);
    }

    // ==================== LIHAT SURAT ADMIN KAJUR ====================

    public function lihatSuratPermohonanAdminKajur($id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $pegawai = Pegawai::where('id_user', $idUser)->first();

        if (!$pegawai) {
            abort(403, 'Data pegawai tidak ditemukan.');
        }

        $pengajuan = PengajuanMagang::with(['perusahaan', 'mahasiswa.mahasiswa.prodi', 'approvals.jabatan', 'suratPermohonan'])
                    ->findOrFail($id_pengajuan);

        return view('pages.baak.upload_surat_permohonan', [
            'pengajuan'       => $pengajuan,
            'suratPermohonan' => $pengajuan->suratPermohonan,
            'backUrl'         => route('admin_kajur.pengajuan_magang.index'),
            'isViewOnly'      => true,
        ]);
    }

    public function lihatSuratBalasanAdminKajur($id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $pegawai = Pegawai::where('id_user', $idUser)->first();

        if (!$pegawai) {
            abort(403, 'Data pegawai tidak ditemukan.');
        }

        $pengajuan = PengajuanMagang::with('perusahaan', 'mahasiswa.mahasiswa.prodi')
                        ->findOrFail($id_pengajuan);

        $mhsDenganSurat = $pengajuan->mahasiswa->firstWhere('surat_balasan', '!=', null);

        if (!$mhsDenganSurat) {
            abort(404, 'Surat balasan belum diupload oleh mahasiswa.');
        }

        return view('pages.kajur.pengajuan_magang.lihat_surat_balasan', [
            'pengajuan'    => $pengajuan,
            'suratBalasan' => $mhsDenganSurat->surat_balasan,
            'backUrl'      => route('admin_kajur.pengajuan_magang.index'),
        ]);
    }

    public function lihatSuratPengantarAdminKajur($id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $pegawai = Pegawai::where('id_user', $idUser)->first();

        if (!$pegawai) {
            abort(403, 'Data pegawai tidak ditemukan.');
        }

        $pengajuan = PengajuanMagang::with(['perusahaan', 'mahasiswa.mahasiswa.prodi', 'approvals.jabatan', 'suratPengantar'])
                    ->findOrFail($id_pengajuan);

        return view('pages.baak.upload_surat_pengantar', [
            'pengajuan'      => $pengajuan,
            'suratPengantar' => $pengajuan->suratPengantar,
            'backUrl'        => route('admin_kajur.pengajuan_magang.index'),
            'isViewOnly'     => true,
        ]);
    }

    // ==================== KAPRODI ====================

    public function indexKaprodi(Request $request)
    {
        $idUser = auth()->user()->id_user;
        $dosen = Dosen::where('id_user', $idUser)->first();

        if (!$dosen) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $idProdiKaprodi = $dosen->id_prodi;

        $filterProdi = function ($q) use ($idProdiKaprodi) {
            $q->where('id_prodi', $idProdiKaprodi);
        };

        $filterTahun = $request->input('filter_tahun');

        $daftarTahun = Mahasiswa::where('id_prodi', $idProdiKaprodi)
            ->whereNotNull('angkatan')
            ->distinct()
            ->orderBy('angkatan', 'desc')
            ->pluck('angkatan')
            ->toArray();

        $masukPerPage = in_array(request('masuk_per_page'), [5, 10]) ? request('masuk_per_page') : 5;
        $riwayatPerPage = in_array(request('riwayat_per_page'), [5, 10]) ? request('riwayat_per_page') : 5;

        $pengajuanMasukQuery = PengajuanMagang::whereHas('mahasiswa.mahasiswa', $filterProdi)
            ->whereDoesntHave('suratPengantar')
            ->whereHas('mahasiswa', function ($q) {
                $q->where('status', '!=', 'ditolak');
            })
            ->with(['perusahaan', 'mahasiswa.mahasiswa' => $filterProdi, 'approvals.jabatan', 'suratPermohonan', 'suratPengantar']);

        if ($filterTahun) {
            $pengajuanMasukQuery->whereHas('mahasiswa.mahasiswa', function ($q) use ($filterTahun, $idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi)
                  ->where('angkatan', $filterTahun);
            });
        }

        $pengajuanMasuk = $pengajuanMasukQuery->orderBy('tgl_pengajuan', 'desc')
            ->paginate($masukPerPage, ['*'], 'masuk_page')
            ->withQueryString();

        $riwayatPengajuanQuery = PengajuanMagang::whereHas('mahasiswa.mahasiswa', $filterProdi)
            ->where(function ($q) {
                $q->whereHas('suratPengantar')
                  ->orWhereDoesntHave('mahasiswa', function ($q2) {
                      $q2->where('status', '!=', 'ditolak');
                  });
            })
            ->with(['perusahaan', 'mahasiswa.mahasiswa' => $filterProdi, 'approvals.jabatan', 'suratPermohonan', 'suratPengantar']);

        if ($filterTahun) {
            $riwayatPengajuanQuery->whereHas('mahasiswa.mahasiswa', function ($q) use ($filterTahun, $idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi)
                  ->where('angkatan', $filterTahun);
            });
        }

        $riwayatPengajuan = $riwayatPengajuanQuery->orderBy('tgl_pengajuan', 'desc')
            ->paginate($riwayatPerPage, ['*'], 'riwayat_page')
            ->withQueryString();

        foreach ($pengajuanMasuk as $p) {
            $p->status_alur = $this->hitungStatusAlur($p);
        }
        foreach ($riwayatPengajuan as $p) {
            $p->status_alur = $this->hitungStatusAlur($p);
        }

    // Ambil tanggal dari pengajuan pertama yang punya tanggal, atau buat object kosong
 $tglProdi = PengajuanMagang::whereHas('mahasiswa.mahasiswa', $filterProdi)
    ->whereNotNull('tgl_mulai')
    ->first();

// Jika belum ada yang punya tanggal, buat object kosong agar view tidak error
if (!$tglProdi) {
    $tglProdi = new \stdClass();
    $tglProdi->tgl_mulai = null;
    $tglProdi->tgl_selesai = null;
}

        return view('pages.kaprodi.pengajuan_magang.index', compact('pengajuanMasuk', 'riwayatPengajuan', 'tglProdi', 'daftarTahun', 'filterTahun'));
    }

    public function detailKaprodi($id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $dosen = Dosen::where('id_user', $idUser)->first();

        if (!$dosen) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $pengajuan = PengajuanMagang::with([
            'perusahaan',
            'mahasiswa.mahasiswa.prodi',
            'approvals.dosen',
            'approvals.jabatan',
            'suratPermohonan',
            'suratPengantar'
        ])->findOrFail($id_pengajuan);

        $idProdiKaprodi = $dosen->id_prodi;
        $adaMahasiswaSeProdi = $pengajuan->mahasiswa->contains(function ($pm) use ($idProdiKaprodi) {
            return $pm->mahasiswa && $pm->mahasiswa->id_prodi == $idProdiKaprodi;
        });

        if (!$adaMahasiswaSeProdi) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini. Pengajuan bukan dari program studi Anda.');
        }

        $jabatanKaprodi = Jabatan::where('kode_jabatan', 'Kaprodi')
            ->orWhere('nama_jabatan', 'LIKE', '%ketua program studi%')
            ->first();

        $approvalKaprodi = null;
        if ($dosen && $jabatanKaprodi) {
            $approvalKaprodi = ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)
                ->where('id_dosen', $dosen->id_dosen)
                ->where('id_jabatan', $jabatanKaprodi->id_jabatan)
                ->first();
        }

        return view('pages.kaprodi.pengajuan_magang.detail', compact('pengajuan', 'dosen', 'approvalKaprodi'));
    }

    public function prosesApprovalKaprodi(Request $request, $id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $dosen = Dosen::where('id_user', $idUser)->first();

        if (!$dosen) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $jabatanKaprodi = Jabatan::where('kode_jabatan', 'Kaprodi')
            ->orWhere('nama_jabatan', 'LIKE', '%ketua program studi%')
            ->first();

        if (!$jabatanKaprodi) {
            abort(403, 'Jabatan Kaprodi tidak ditemukan di sistem.');
        }

        $pengajuan = PengajuanMagang::with('mahasiswa.mahasiswa', 'perusahaan')->findOrFail($id_pengajuan);

        $idProdiKaprodi = $dosen->id_prodi;
        $adaMahasiswaSeProdi = $pengajuan->mahasiswa->contains(function ($pm) use ($idProdiKaprodi) {
            return $pm->mahasiswa && $pm->mahasiswa->id_prodi == $idProdiKaprodi;
        });

        if (!$adaMahasiswaSeProdi) {
            abort(403, 'Anda tidak memiliki akses untuk menyetujui pengajuan ini.');
        }

        $request->validate([
            'status_approval' => 'required|in:disetujui,ditolak',
            'catatan_approval' => 'nullable|string',
        ]);

        $sudahAda = ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)
            ->where('id_dosen', $dosen->id_dosen)
            ->where('id_jabatan', $jabatanKaprodi->id_jabatan)
            ->exists();

        if ($sudahAda) {
            return back()->withErrors('Anda sudah melakukan approval untuk pengajuan ini.');
        }

        ApprovalPengajuan::create([
            'id_pengajuan' => $id_pengajuan,
            'id_dosen' => $dosen->id_dosen,
            'id_jabatan' => $jabatanKaprodi->id_jabatan,
            'status' => $request->status_approval,
            'catatan' => $request->catatan_approval,
            'approved_at' => now(),
        ]);

        if ($request->status_approval === 'ditolak') {
            PengajuanMahasiswa::where('id_pengajuan', $id_pengajuan)
                ->where('status', 'diajukan')
                ->update(['status' => 'ditolak']);
        }

 $namaPerusahaan = $pengajuan->perusahaan->nama_perusahaan ?? 'Perusahaan';
if ($request->status_approval === 'disetujui') {
    // Notifikasi ke mahasiswa
    Notifikasi::kirimKeMahasiswaPengajuan(
        $pengajuan,
        'Pengajuan Magang Disetujui Kaprodi',
        'Pengajuan magang Anda telah disetujui oleh Kaprodi. Menunggu persetujuan Admin Kajur.',
        'approval_kaprodi'
    );
        // Notifikasi ke Kajur
    Notifikasi::kirimKeKajurJurusan(
        $dosen->id_prodi,
        'Pengajuan Magang Disetujui Kaprodi',
        'Pengajuan magang ke ' . $namaPerusahaan . ' telah disetujui oleh Kaprodi Prodi.',
        'kajur_pengajuan_magang',
        $id_pengajuan
    );

    //  Notifikasi ke Admin Kajur (INI YANG PENTING)
    Notifikasi::kirimKeAdminKajur(
        'Pengajuan Magang Menunggu Persetujuan',
        'Pengajuan magang ke ' . $namaPerusahaan . ' telah disetujui Kaprodi. Silakan lakukan persetujuan.',
        'admin_kajur_pengajuan_magang',
        $id_pengajuan
    );
} else {
    // Notifikasi ke mahasiswa
    Notifikasi::kirimKeMahasiswaPengajuan(
        $pengajuan,
        'Pengajuan Magang Ditolak Kaprodi',
        'Pengajuan magang Anda ditolak oleh Kaprodi.',
        'ditolak'
    );
}

        $pesan = $request->status_approval === 'disetujui'
            ? 'Pengajuan berhasil disetujui.'
            : 'Pengajuan ditolak.';

        return redirect()
            ->route('kaprodi.pengajuan_magang.detail', $id_pengajuan)
            ->with('success', $pesan);
    }

    // ==================== VERIFIKASI SURAT BALASAN KAPRODI ====================

    public function verifikasiSuratBalasan($id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $dosen = Dosen::where('id_user', $idUser)->first();

        if (!$dosen) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $pengajuan = PengajuanMagang::with('perusahaan', 'mahasiswa.mahasiswa')
            ->findOrFail($id_pengajuan);

        $idProdiKaprodi = $dosen->id_prodi;
        $adaMahasiswaSeProdi = $pengajuan->mahasiswa->contains(function ($pm) use ($idProdiKaprodi) {
            return $pm->mahasiswa && $pm->mahasiswa->id_prodi == $idProdiKaprodi;
        });

        if (!$adaMahasiswaSeProdi) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $suratBalasan = null;
        $statusSuratBalasan = 'belum_upload';

        if ($pengajuan->mahasiswa->whereNotNull('surat_balasan')->count() > 0) {
            $mhsPertama = $pengajuan->mahasiswa->firstWhere('surat_balasan', '!=', null);
            $suratBalasan = $mhsPertama->surat_balasan;

            foreach ($pengajuan->mahasiswa as $pm) {
                if (!is_null($pm->surat_balasan) && $pm->status_surat_balasan == 'belum_upload') {
                    PengajuanMahasiswa::where('id_pengajuan', $id_pengajuan)
                        ->where('id_mahasiswa', $pm->id_mahasiswa)
                        ->update(['status_surat_balasan' => 'menunggu_verifikasi']);
                }
            }

            $pengajuan->load('mahasiswa');

            $totalMhs = $pengajuan->mahasiswa->count();
            $mhsSudahVerif = $pengajuan->mahasiswa->whereIn('status_surat_balasan', ['diterima', 'ditolak'])->count();

            if ($totalMhs > 0 && $mhsSudahVerif === $totalMhs) {
                $statusSuratBalasan = 'sudah_diverifikasi';
            } else {
                $statusSuratBalasan = 'menunggu_verifikasi';
            }
        }

        return view('pages.kaprodi.pengajuan_magang.verifikasi_surat_balasan', compact('pengajuan', 'suratBalasan', 'statusSuratBalasan'));
    }

    public function prosesVerifikasiSuratBalasan(Request $request, $id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $dosen = Dosen::where('id_user', $idUser)->first();

        if (!$dosen) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $pengajuanCheck = PengajuanMagang::with('mahasiswa.mahasiswa')->findOrFail($id_pengajuan);
        $idProdiKaprodi = $dosen->id_prodi;
        $adaMahasiswaSeProdi = $pengajuanCheck->mahasiswa->contains(function ($pm) use ($idProdiKaprodi) {
            return $pm->mahasiswa && $pm->mahasiswa->id_prodi == $idProdiKaprodi;
        });

        if (!$adaMahasiswaSeProdi) {
            abort(403, 'Anda tidak memiliki akses untuk memverifikasi pengajuan ini.');
        }

        $request->validate([
            'mahasiswa_diterima' => 'nullable|array',
            'mahasiswa_diterima.*' => 'exists:mahasiswa,id_mahasiswa',
            'catatan_verifikasi' => 'nullable|string',
        ]);

        $mahasiswaDiterima = $request->mahasiswa_diterima ?? [];

        $semuaMahasiswaDiPengajuan = PengajuanMahasiswa::with('mahasiswa')->where('id_pengajuan', $id_pengajuan)->get();

        $fileSuratBalasan = $semuaMahasiswaDiPengajuan->whereNotNull('surat_balasan')->first()->surat_balasan ?? null;

        foreach ($semuaMahasiswaDiPengajuan as $pm) {
            if (in_array($pm->id_mahasiswa, $mahasiswaDiterima)) {
                $pm->update([
                    'status_surat_balasan' => 'diterima',
                    'status' => 'diajukan',
                    'surat_balasan' => $pm->surat_balasan ?? $fileSuratBalasan,
                    'verified_at' => now(),
                    'verified_by' => $idUser,
                    'catatan_verifikasi' => $request->catatan_verifikasi,
                ]);
            } else {
                $pm->update([
                    'status_surat_balasan' => 'ditolak',
                    'status' => 'ditolak',
                    'verified_at' => now(),
                    'verified_by' => $idUser,
                    'catatan_verifikasi' => $request->catatan_verifikasi,
                ]);
            }

            $mhsModel = \App\Models\Mahasiswa::find($pm->id_mahasiswa);
            if ($mhsModel && $mhsModel->id_user) {
                if (in_array($pm->id_mahasiswa, $mahasiswaDiterima)) {
                    \App\Models\Notifikasi::kirim(
                        $mhsModel->id_user,
                        'Surat Balasan Diverifikasi Kaprodi',
                        'Surat balasan magang Anda telah diverifikasi oleh Kaprodi.',
                        'verifikasi_balasan',
                        $id_pengajuan
                    );
                } else {
                    \App\Models\Notifikasi::kirim(
                        $mhsModel->id_user,
                        'Surat Balasan Ditolak Kaprodi',
                        'Surat balasan magang Anda ditolak oleh Kaprodi.',
                        'verifikasi_balasan',
                        $id_pengajuan
                    );
                }
            }
        }

        $namaPerusahaan = $pengajuanCheck->perusahaan->nama_perusahaan ?? '-';
        Notifikasi::kirimKeBaak(
            'Surat Balasan Terverifikasi',
            'Surat balasan magang ke ' . $namaPerusahaan . ' telah diverifikasi Kaprodi. Siap upload surat pengantar.',
            'baak_surat_balasan_verifikasi',
            $id_pengajuan
        );

        return redirect()->route('kaprodi.pengajuan_magang.index')
            ->with('success', 'Verifikasi surat balasan berhasil disimpan.');
    }

    // ==================== BAAK ====================

    // public function indexBaak()
    // {
    //     $jabatanKaprodi = Jabatan::where('kode_jabatan', 'Kaprodi')
    //         ->orWhere('nama_jabatan', 'LIKE', '%ketua program studi%')
    //         ->first();
    //     $jabatanAdminKajur = Jabatan::where('kode_jabatan', 'Admin_Kajur')->first();
    //     $jabatanBaak = Jabatan::where('kode_jabatan', 'BAAK')->first();

    //     // Kalau jabatan belum ada di DB, return kosong
    //     if (!$jabatanKaprodi || !$jabatanAdminKajur || !$jabatanBaak) {
    //         return view('pages.baak.pengajuan_magang.index', [
    //             'pengajuanMenunggu' => collect(),
    //             'riwayatPengajuan' => collect(),
    //         ]);
    //     }

    //     $idJabatanKaprodi   = $jabatanKaprodi->id_jabatan;
    //     $idJabatanAdminKajur = $jabatanAdminKajur->id_jabatan;
    //     $idJabatanBaak       = $jabatanBaak->id_jabatan;

    //     $query = PengajuanMagang::with([
    //         'perusahaan',
    //         'mahasiswa.mahasiswa',
    //         'approvals.jabatan',
    //         'suratPermohonan',
    //         'suratPengantar'
    //     ])->orderBy('tgl_pengajuan', 'desc');

    //     // Hanya data yang sudah lewat Kaprodi + Admin Kajur
    //     $query->whereHas('approvals', function ($q) use ($idJabatanKaprodi) {
    //             $q->where('id_jabatan', $idJabatanKaprodi)
    //             ->where('status', 'disetujui');
    //         })
    //         ->whereHas('approvals', function ($q) use ($idJabatanAdminKajur) {
    //             $q->where('id_jabatan', $idJabatanAdminKajur)
    //             ->where('status', 'disetujui');
    //         });

    //     $pengajuans = $query->get();

    //     $pengajuanMenunggu = collect();
    //     $riwayatPengajuan = collect();

    //     // Kumpulkan semua ID pengajuan yang DITOLAK BAAK
    //     $idBaakRejected = ApprovalPengajuan::where('id_jabatan', $idJabatanBaak)
    //         ->where('status', 'ditolak')
    //         ->pluck('id_pengajuan')
    //         ->toArray();

    //     foreach ($pengajuans as $p) {
    //         $p->status_alur = $this->hitungStatusAlurBaak($p);

    //         $idP = $p->id_pengajuan;

    //         // --- Cek mahasiswa ditolak ---
    //         $totalMhs = $p->mahasiswa->count();
    //         $mhsDitolak = $p->mahasiswa->where('status', 'ditolak')->count();
    //         $semuaMhsDitolak = $totalMhs > 0 && $totalMhs === $mhsDitolak;

    //         // --- Cek surat balasan ---
    //         $p->ada_file_surat_balasan = $p->mahasiswa->whereNotNull('surat_balasan')->count() > 0;
    //         $p->sudah_verifikasi_kaprodi = $p->mahasiswa->whereIn('status_surat_balasan', ['diterima', 'ditolak'])->count() > 0;
    //         $p->ada_diterima_kaprodi = $p->mahasiswa->where('status_surat_balasan', 'diterima')->count() > 0;

    //         // ============================================================
    //         // PEMISAHAN LOGIKA — DIPERBAIKI
    //         // ============================================================
    //         // TABEL 2 (Riwayat): Hanya jika SUDAH ADA surat pengantar 
    //         //                   ATAU BAAK menolak ATAU semua mhs ditolak
    //         // TABEL 1 (Menunggu): SELAIN kondisi di atas (termasuk "Menunggu BAAK")
    //         // ============================================================
            
    //         $sudahAdaPengantar = $p->suratPengantar != null;
    //         $baakTolak = in_array($idP, $idBaakRejected);

    //         if ($sudahAdaPengantar || $baakTolak || $semuaMhsDitolak) {
    //             // TABEL 2: Riwayat
    //             $riwayatPengajuan->push($p);
    //         } else {
    //             // TABEL 1: Menunggu
    //             // Termasuk: "Menunggu BAAK", "Selesai" (belum upload surat)
    //             $pengajuanMenunggu->push($p);
    //         }
    //     }

    //     return view('pages.baak.pengajuan_magang.index', compact(
    //         'pengajuanMenunggu',
    //         'riwayatPengajuan'
    //     ));
    // }
    // ==================== BAAK ====================

// ==================== BAAK ====================

public function indexBaak(Request $request)
{
    $idUser = auth()->user()->id_user;
    $pegawai = Pegawai::where('id_user', $idUser)->first();

    if (!$pegawai) {
        abort(403, 'Data pegawai tidak ditemukan.');
    }

    // Verifikasi role BAAK
    $isBaak = JabatanTendik::where('id_pegawai', $pegawai->id_pegawai)
        ->whereHas('jabatan', function ($q) {
            $q->where('kode_jabatan', 'J001')
              ->orWhere('kode_jabatan', 'BAAK');
        })
        ->exists();

    if (!$isBaak) {
        abort(403, 'Anda tidak memiliki akses BAAK.');
    }

    // Ambil ID jabatan yang dibutuhkan
    $jabatanKaprodi = Jabatan::where('kode_jabatan', 'Kaprodi')
        ->orWhere('nama_jabatan', 'LIKE', '%ketua program studi%')
        ->first();

    $jabatanAdminKajur = Jabatan::where('kode_jabatan', 'J004')->first();

    $jabatanBaak = Jabatan::where('kode_jabatan', 'J001')
        ->orWhere('kode_jabatan', 'BAAK')
        ->first();

    // Relasi yang selalu di-load
    $baseWith = [
        'perusahaan',
        'mahasiswa.mahasiswa.prodi',
        'approvals.jabatan',
        'suratPermohonan',
        'suratPengantar'
    ];

    // ============================================================
    // TABEL 1: MENUNGGU UPLOAD SURAT
    // Syarat:
    //   1. Kaprodi SUDAH setujui
    //   2. Admin Kajur SUDAH setujui
    //   3. BAAK BELUM menolak
    //   4. BELUM ada surat pengantar
    //   5. Masih ada mahasiswa aktif (bukan semua ditolak)
    //
    // CATATAN PENTING:
    //   - TIDAK perlu filter "belum ada surat permohonan"
    //     karena BAAK lah yang upload surat permohonan
    //   - Pengajuan tanpa surat permohonan HARUS tetap muncul
    //     supaya BAAK bisa klik "Upload"
    // ============================================================
    $pengajuanMenunggu = PengajuanMagang::with($baseWith);

    // 1. Kaprodi sudah setujui
    if ($jabatanKaprodi) {
        $pengajuanMenunggu->whereHas('approvals', function ($q) use ($jabatanKaprodi) {
            $q->where('id_jabatan', $jabatanKaprodi->id_jabatan)
              ->where('status', 'disetujui');
        });
    } else {
        // Kalau jabatan Kaprodi tidak ada di DB, tidak ada yang bisa ditampilkan
        $pengajuanMenunggu->whereRaw('1 = 0');
    }

    // 2. Admin Kajur sudah setujui
    if ($jabatanAdminKajur) {
        $pengajuanMenunggu->whereHas('approvals', function ($q) use ($jabatanAdminKajur) {
            $q->where('id_jabatan', $jabatanAdminKajur->id_jabatan)
              ->where('status', 'disetujui');
        });
    } else {
        $pengajuanMenunggu->whereRaw('1 = 0');
    }

    // 3. BAAK BELUM menolak (kalau sudah ditolak, masuk riwayat)
    if ($jabatanBaak) {
        $pengajuanMenunggu->whereDoesntHave('approvals', function ($q) use ($jabatanBaak) {
            $q->where('id_jabatan', $jabatanBaak->id_jabatan)
              ->where('status', 'ditolak');
        });
    }

    // 4. BELUM ada surat pengantar (kalau sudah ada, masuk riwayat)
    $pengajuanMenunggu->whereDoesntHave('suratPengantar');

    // 5. Masih ada mahasiswa aktif (tidak semuanya ditolak/batal)
    $pengajuanMenunggu->whereHas('mahasiswa', function ($q) {
        $q->where('status', '!=', 'ditolak');
    });

    $pengajuanMenunggu = $pengajuanMenunggu->orderBy('tgl_pengajuan', 'desc')->get();


    // ============================================================
    // TABEL 2: RIWAYAT PENGAJUAN
    // Syarat (salah satu):
    //   A. Sudah ada surat pengantar (BAAK sudah selesai proses)
    //   B. BAAK menolak pengajuan
    // 
    // Kedua syarat ini tetap membutuhkan:
    //   - Kaprodi sudah setujui
    //   - Admin Kajur sudah setujui
    // ============================================================
    $riwayatPengajuan = PengajuanMagang::with($baseWith);

    // Kaprodi sudah setujui
    if ($jabatanKaprodi) {
        $riwayatPengajuan->whereHas('approvals', function ($q) use ($jabatanKaprodi) {
            $q->where('id_jabatan', $jabatanKaprodi->id_jabatan)
              ->where('status', 'disetujui');
        });
    } else {
        $riwayatPengajuan->whereRaw('1 = 0');
    }

    // Admin Kajur sudah setujui
    if ($jabatanAdminKajur) {
        $riwayatPengajuan->whereHas('approvals', function ($q) use ($jabatanAdminKajur) {
            $q->where('id_jabatan', $jabatanAdminKajur->id_jabatan)
              ->where('status', 'disetujui');
        });
    } else {
        $riwayatPengajuan->whereRaw('1 = 0');
    }

    // Syarat riwayat: sudah ada surat pengantar ATAU BAAK menolak
    $riwayatPengajuan->where(function ($q) use ($jabatanBaak) {
        // A. Sudah ada surat pengantar
        $q->whereHas('suratPengantar');

        // B. BAAK menolak
        if ($jabatanBaak) {
            $q->orWhereHas('approvals', function ($q2) use ($jabatanBaak) {
                $q2->where('id_jabatan', $jabatanBaak->id_jabatan)
                  ->where('status', 'ditolak');
            });
        }
    });

    $riwayatPengajuan = $riwayatPengajuan->orderBy('tgl_pengajuan', 'desc')->get();


    // ============================================================
    // HITUNG PROPERTI DINAMIS UNTUK VIEW
    // ============================================================
    $semuaPengajuan = $pengajuanMenunggu->merge($riwayatPengajuan);

    foreach ($semuaPengajuan as $p) {
        // Status alur (dari helper yang sudah ada)
        $p->status_alur = $this->hitungStatusAlur($p);

        // Apakah ADA file surat balasan dari manapun (mahasiswa manapun)
        $p->ada_file_surat_balasan = $p->mahasiswa
            ->whereNotNull('surat_balasan')
            ->isNotEmpty();

        // Apakah Kaprodi SUDAH verifikasi SEMUA surat balasan?
        // = semua mahasiswa yang punya surat_balasan, status_surat_balasannya = 'diterima'
        $mhsDenganBalasan = $p->mahasiswa->whereNotNull('surat_balasan');
        $p->sudah_verifikasi_kaprodi = $mhsDenganBalasan->isNotEmpty()
            && $mhsDenganBalasan->every(function ($pm) {
                return $pm->status_surat_balasan === 'diterima';
            });

        // Apakah ADA minimal 1 mahasiswa yang surat balasannya DITERIMA Kaprodi?
        $p->ada_diterima_kaprodi = $p->mahasiswa->contains(function ($pm) {
            return $pm->status_surat_balasan === 'diterima';
        });
    }

    return view('pages.baak.pengajuan_magang.index', compact(
        'pengajuanMenunggu',
        'riwayatPengajuan'
    ));
}

  public function detailBaak($id_pengajuan)
{
    $idUser = auth()->user()->id_user;
    $pegawai = Pegawai::where('id_user', $idUser)->first();

    if (!$pegawai) {
        abort(403, 'Data pegawai tidak ditemukan.');
    }

    $pengajuan = PengajuanMagang::with([
        'perusahaan',
        'mahasiswa.mahasiswa.prodi',
        'approvals.dosen',
        'approvals.pegawai',
        'approvals.jabatan',
        'suratPermohonan',
        'suratPengantar'
    ])->findOrFail($id_pengajuan);

    // ✅ PERBAIKAN: Cari J001 dulu, fallback ke BAAK
    $jabatanBaak = Jabatan::where('kode_jabatan', 'J001')
        ->orWhere('kode_jabatan', 'BAAK')
        ->first();

    $approvalBaak = null;
    if ($jabatanBaak) {
        $approvalBaak = ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)
                                ->where('id_pegawai', $pegawai->id_pegawai)
                                ->where('id_jabatan', $jabatanBaak->id_jabatan)
                                ->first();
    }

    $jabatanKaprodi = Jabatan::where('kode_jabatan', 'Kaprodi')
        ->orWhere('nama_jabatan', 'LIKE', '%ketua program studi%')
        ->first();
    $approvalKaprodi = null;
    if ($jabatanKaprodi) {
        $approvalKaprodi = ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)
                                ->where('id_jabatan', $jabatanKaprodi->id_jabatan)
                                ->where('status', 'disetujui')
                                ->first();
    }

    // ✅ PERBAIKAN: Cari J004 dulu, fallback ke Admin_Kajur lama
    $jabatanAdminKajur = Jabatan::where('kode_jabatan', 'J004')
        ->orWhere('kode_jabatan', 'Admin_Kajur')
        ->first();
    $approvalAdminKajur = null;
    if ($jabatanAdminKajur) {
        $approvalAdminKajur = ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)
                                ->where('id_jabatan', $jabatanAdminKajur->id_jabatan)
                                ->where('status', 'disetujui')
                                ->first();
    }

    return view('pages.baak.pengajuan_magang.detail', compact('pengajuan', 'pegawai', 'approvalBaak', 'approvalKaprodi', 'approvalAdminKajur'));
}

    public function prosesApprovalBaak(Request $request, $id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $pegawai = Pegawai::where('id_user', $idUser)->first();

        if (!$pegawai) {
            abort(403, 'Data pegawai tidak ditemukan.');
        }

       $jabatanBaak = Jabatan::where('kode_jabatan', 'J001')
        ->orWhere('kode_jabatan', 'BAAK')
        ->first();

        if (!$jabatanBaak) {
            abort(403, 'Jabatan BAAK tidak ditemukan di sistem.');
        }

        $request->validate([
            'status_approval' => 'required|in:disetujui,ditolak',
            'catatan_approval' => 'nullable|string',
        ]);

        $pengajuan = PengajuanMagang::findOrFail($id_pengajuan);

        $sudahAda = ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)
            ->where('id_pegawai', $pegawai->id_pegawai)
            ->where('id_jabatan', $jabatanBaak->id_jabatan)
            ->exists();

        if ($sudahAda) {
            return back()->withErrors('Anda sudah melakukan approval untuk pengajuan ini.');
        }

        ApprovalPengajuan::create([
            'id_pengajuan' => $id_pengajuan,
            'id_pegawai' => $pegawai->id_pegawai,
            'id_jabatan' => $jabatanBaak->id_jabatan,
            'status' => $request->status_approval,
            'catatan' => $request->catatan_approval,
            'approved_at' => now(),
        ]);

        if ($request->status_approval === 'ditolak') {
            PengajuanMahasiswa::where('id_pengajuan', $id_pengajuan)
                ->where('status', 'diajukan')
                ->update(['status' => 'ditolak']);
        }

        $pengajuanNotif = PengajuanMagang::with('mahasiswa.mahasiswa', 'perusahaan')->findOrFail($id_pengajuan);
        if ($request->status_approval === 'disetujui') {
            Notifikasi::kirimKeMahasiswaPengajuan(
                $pengajuanNotif,
                'Pengajuan Magang Disetujui BAAK',
                'Pengajuan magang Anda telah disetujui oleh BAAK.',
                'approval_baak'
            );
        } else {
            Notifikasi::kirimKeMahasiswaPengajuan(
                $pengajuanNotif,
                'Pengajuan Magang Ditolak BAAK',
                'Pengajuan magang Anda ditolak oleh BAAK.',
                'ditolak'
            );
        }

        $pesan = $request->status_approval === 'disetujui'
            ? 'Pengajuan berhasil disetujui. Silakan upload Surat Permohonan.'
            : 'Pengajuan ditolak.';

        return redirect()
            ->route('baak.pengajuan_magang.detail', $id_pengajuan)
            ->with('success', $pesan);
    }

    // ==================== UPLOAD SURAT BAAK ====================

    public function formUploadSuratPermohonan($id_pengajuan)
    {
        $pengajuan = PengajuanMagang::with(['perusahaan', 'mahasiswa.mahasiswa', 'approvals.jabatan', 'suratPermohonan'])
            ->findOrFail($id_pengajuan);

            $jabatanBaak = Jabatan::where('kode_jabatan', 'J001')
        ->orWhere('kode_jabatan', 'BAAK')
        ->first();

        $isBaakApproved = false;

        if ($jabatanBaak) {
            $isBaakApproved = $pengajuan->approvals->contains(function ($approval) use ($jabatanBaak) {
                return $approval->id_jabatan == $jabatanBaak->id_jabatan && $approval->status == 'disetujui';
            });
        }

        if (!$isBaakApproved) {
            abort(403, 'Pengajuan belum disetujui oleh BAAK.');
        }

        return view('pages.baak.upload_surat_permohonan', [
            'pengajuan'       => $pengajuan,
            'suratPermohonan' => $pengajuan->suratPermohonan,
            'backUrl'         => url()->previous(),
            'isViewOnly'      => false,
        ]);
    }

    public function uploadSuratPermohonan(Request $request, $id_pengajuan)
    {
        $request->validate([
            'file_surat' => 'required|file|mimes:pdf|max:5120',
        ]);

        $idUser = auth()->user()->id_user;
        $pegawai = Pegawai::where('id_user', $idUser)->first();

        $suratLama = SuratPengajuan::where('id_pengajuan', $id_pengajuan)
            ->where('jenis_surat', 'permohonan')
            ->first();

        if ($suratLama && $suratLama->file_surat) {
            $oldPath = storage_path('app/public/surat_permohonan/' . $suratLama->file_surat);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $file = $request->file('file_surat');
        $namaFile = time() . '_surat_permohonan_' . $file->getClientOriginalName();
        $file->storeAs('surat_permohonan', $namaFile, 'public');

        SuratPengajuan::updateOrCreate(
            ['id_pengajuan' => $id_pengajuan, 'jenis_surat' => 'permohonan'],
            [
                'file_surat' => $namaFile,
                'uploaded_by' => $pegawai ? $pegawai->id_pegawai : null,
                'uploaded_at' => now(),
            ]
        );

        $pengajuanNotif = PengajuanMagang::with('mahasiswa.mahasiswa', 'perusahaan')->findOrFail($id_pengajuan);
        $namaPerusahaan = $pengajuanNotif->perusahaan->nama_perusahaan ?? 'Perusahaan';
        
        Notifikasi::kirimKeMahasiswaPengajuan(
            $pengajuanNotif,
            'Surat Permohonan Diupload BAAK',
            'BAAK telah mengupload surat permohonan magang Anda.',
            'surat_permohonan'
        );

        $firstMhs = $pengajuanNotif->mahasiswa->first();
        if ($firstMhs && $firstMhs->mahasiswa) {
            Notifikasi::kirimKeKaprodiProdi(
                $firstMhs->mahasiswa->id_prodi,
                'Surat Permohonan Diupload BAAK',
                'Surat permohonan magang ke ' . $namaPerusahaan . ' telah diupload oleh BAAK.',
                'kaprodi_surat_permohonan',
                $id_pengajuan
            );
        }

        $kirimEmail = $request->has('kirim_email');
        $emailPerusahaan = $pengajuanNotif->email_perusahaan;
        $emailTerkirim = false;
        $emailError = null;

        if ($kirimEmail && !empty($emailPerusahaan)) {
            try {
                $filePath = storage_path('app/public/surat_permohonan/' . $namaFile);
                \Mail::to($emailPerusahaan)->send(new \App\Mail\SuratMagangMail($pengajuanNotif, $filePath, 'permohonan'));
                $emailTerkirim = true;
            } catch (\Exception $e) {
                \Log::error('Gagal kirim email surat permohonan: ' . $e->getMessage());
                $emailError = $e->getMessage();
            }
        }

        $pesan = 'Surat Permohonan berhasil diupload.';
        if ($emailTerkirim) {
            $pesan .= '  Surat juga telah dikirim ke email ' . $emailPerusahaan;
        } elseif ($kirimEmail && !$emailTerkirim && $emailError) {
            $pesan .= '  Email gagal dikirim ke perusahaan. Silakan kirim manual.';
        }

        return redirect()->route('baak.pengajuan_magang.index')->with('success', $pesan);
    }

    public function formUploadSuratPengantar($id_pengajuan)
    {
        $pengajuan = PengajuanMagang::with(['perusahaan', 'mahasiswa.mahasiswa', 'approvals.jabatan', 'suratPengantar'])
            ->findOrFail($id_pengajuan);

        $adaFileSuratBalasan = $pengajuan->mahasiswa->contains(function ($m) {
            return !is_null($m->surat_balasan);
        });

        if (!$adaFileSuratBalasan) {
            abort(403, 'Belum ada surat balasan yang diupload. Surat pengantar belum bisa diproses.');
        }

        return view('pages.baak.upload_surat_pengantar', [
            'pengajuan'      => $pengajuan,
            'suratPengantar' => $pengajuan->suratPengantar,
            'backUrl'        => url()->previous(),
            'isViewOnly'     => false,
        ]);
    }

    public function uploadSuratPengantar(Request $request, $id_pengajuan)
    {
        $request->validate([
            'file_surat' => 'required|file|mimes:pdf|max:5120',
        ]);

        $idUser = auth()->user()->id_user;
        $pegawai = Pegawai::where('id_user', $idUser)->first();

        $suratLama = SuratPengajuan::where('id_pengajuan', $id_pengajuan)
            ->where('jenis_surat', 'pengantar')
            ->first();

        if ($suratLama && $suratLama->file_surat) {
            $oldPath = storage_path('app/public/surat_pengantar_upload/' . $suratLama->file_surat);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $file = $request->file('file_surat');
        $namaFile = time() . '_surat_pengantar_' . $file->getClientOriginalName();
        $file->storeAs('surat_pengantar_upload', $namaFile, 'public');

        SuratPengajuan::updateOrCreate(
            ['id_pengajuan' => $id_pengajuan, 'jenis_surat' => 'pengantar'],
            [
                'file_surat' => $namaFile,
                'uploaded_by' => $pegawai ? $pegawai->id_pegawai : null,
                'uploaded_at' => now(),
            ]
        );

        $pengajuanNotif = PengajuanMagang::with('mahasiswa.mahasiswa', 'perusahaan')->findOrFail($id_pengajuan);
        $namaPerusahaan = $pengajuanNotif->perusahaan->nama_perusahaan ?? 'Perusahaan';
        
        Notifikasi::kirimKeMahasiswaPengajuan(
            $pengajuanNotif,
            'Surat Pengantar Diupload BAAK',
            'BAAK telah mengupload surat pengantar magang Anda. Proses selesai!',
            'surat_pengantar'
        );

        $kirimEmail = $request->has('kirim_email');
        $emailPerusahaan = $pengajuanNotif->email_perusahaan;
        $emailTerkirim = false;
        $emailError = null;

        if ($kirimEmail && !empty($emailPerusahaan)) {
            try {
                $filePath = storage_path('app/public/surat_pengantar_upload/' . $namaFile);
                \Mail::to($emailPerusahaan)->send(new \App\Mail\SuratMagangMail($pengajuanNotif, $filePath, 'pengantar'));
                $emailTerkirim = true;
            } catch (\Exception $e) {
                \Log::error('Gagal kirim email surat pengantar: ' . $e->getMessage());
                $emailError = $e->getMessage();
            }
        }

        $pesan = 'Surat Pengantar berhasil diupload. Proses selesai. ';
        if ($emailTerkirim) {
            $pesan .= '  Surat juga telah dikirim ke email ' . $emailPerusahaan;
        } elseif ($kirimEmail && !$emailTerkirim && $emailError) {
            $pesan .= '  Email gagal dikirim ke perusahaan. Silakan kirim manual.';
        }

        return redirect()->route('baak.pengajuan_magang.index')->with('success', $pesan);
    }

    // ==================== ADMIN & LIHAT SURAT ====================

    public function indexAdmin()
    {
        $pengajuans = PengajuanMagang::with(['perusahaan', 'mahasiswa.mahasiswa', 'approvals.jabatan', 'suratPermohonan', 'suratPengantar'])
            ->orderBy('tgl_pengajuan', 'desc')
            ->get();

        foreach ($pengajuans as $p) {
            $p->status_alur = $this->hitungStatusAlur($p);
        }

        return view('pages.admin.pengajuan_magang.index', compact('pengajuans'));
    }

// ==================== ADMIN ====================

public function detailAdmin($id_pengajuan)
{
    $pengajuan = PengajuanMagang::with([
        'perusahaan',
        'mahasiswa.mahasiswa.prodi',
        'approvals.dosen',
        'approvals.pegawai',
        'approvals.jabatan',
        'suratPermohonan',
        'suratPengantar'
    ])->findOrFail($id_pengajuan);

    $statusAlur = $this->hitungStatusAlur($pengajuan);

    $jabatanKaprodi = Jabatan::where('kode_jabatan', 'Kaprodi')
        ->orWhere('nama_jabatan', 'LIKE', '%ketua program studi%')
        ->first();
    $jabatanAdminKajur = Jabatan::where('kode_jabatan', 'Admin_Kajur')->first();
    $jabatanKajurLama = Jabatan::where('nama_jabatan', 'LIKE', '%ketua jurusan%')->first();
        $jabatanBaak = Jabatan::where('kode_jabatan', 'J001')
        ->orWhere('kode_jabatan', 'BAAK')
        ->first();


    $approvalKaprodi = null;
    $dosen = null;
    if ($jabatanKaprodi) {
        $approvalKaprodi = ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)
            ->where('id_jabatan', $jabatanKaprodi->id_jabatan)
            ->where('status', 'disetujui')
            ->first();
        if ($approvalKaprodi) {
            $dosen = $approvalKaprodi->dosen;
        }
    }

    $approvalAdminKajur = null;
    if ($jabatanAdminKajur) {
        $approvalAdminKajur = ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)
            ->where('id_jabatan', $jabatanAdminKajur->id_jabatan)
            ->first();
    }
    if (!$approvalAdminKajur && $jabatanKajurLama) {
        $approvalAdminKajur = ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)
            ->where('id_jabatan', $jabatanKajurLama->id_jabatan)
            ->first();
    }

    $approvalBaak = null;
    if ($jabatanBaak) {
        $approvalBaak = ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)
            ->where('id_jabatan', $jabatanBaak->id_jabatan)
            ->first();
    }

    return view('pages.admin.pengajuan_magang.detail', compact(
        'pengajuan', 'statusAlur', 'dosen',
        'approvalKaprodi', 'approvalAdminKajur', 'approvalBaak'
    ));
}
    public function monitoringPenempatanAdmin()
    {
        $pengajuans = PengajuanMagang::whereHas('mahasiswa', function ($q) {
            $q->where('status_surat_balasan', 'diterima');
        })
        ->with([
            'perusahaan',
            'suratPengantar',
            'mahasiswa' => function ($q) {
                $q->where('status_surat_balasan', 'diterima')
                  ->with('mahasiswa');
            }
        ])
        ->orderBy('tgl_pengajuan', 'desc')
        ->get();

        return view('pages.admin.monitoring_penempatan.index', compact('pengajuans'));
    }
// ==================== KAPRODI - MONITORING PENEMPATAN ====================
// ==================== KAPRODI - MONITORING PENEMPATAN ====================
public function monitoringPenempatan(Request $request)
{
    $idUser = auth()->user()->id_user;
    $dosen = Dosen::where('id_user', $idUser)->first();

    if (!$dosen) {
        abort(403, 'Data dosen tidak ditemukan.');
    }

    $idProdiKaprodi = $dosen->id_prodi;

    // Ambil parameter filter
    $search = $request->input('search');
    $perPage = $request->input('per_page', 'all');

    // Validasi per_page
    if ($perPage !== 'all' && !in_array((int)$perPage, [5, 10, 15])) {
        $perPage = 'all';
    }

    //  Query langsung ke PengajuanMahasiswa yang surat balasannya SUDAH DIVERIFIKASI (diterima)
    $query = PengajuanMahasiswa::with([
            'mahasiswa.prodi',
            'pengajuan.perusahaan',
            'pengajuan.suratPengantar'
        ])
        ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
            $q->where('id_prodi', $idProdiKaprodi);
        })
        ->where('status_surat_balasan', 'diterima'); //  HANYA YANG SUDAH DIVERIFIKASI

    // Search filter
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->whereHas('mahasiswa', function ($q2) use ($search) {
                $q2->where('nama', 'like', '%' . $search . '%')
                   ->orWhere('npm', 'like', '%' . $search . '%');
            })
            ->orWhereHas('pengajuan.perusahaan', function ($q2) use ($search) {
                $q2->where('nama_perusahaan', 'like', '%' . $search . '%');
            });
        });
    }

    //  PERBAIKAN: pakai id_pengajuan desc (bukan created_at yang tidak ada)
    $query->orderBy('id_pengajuan', 'desc');

    // Pagination
    if ($perPage === 'all') {
        $dataMahasiswa = $query->get();
        $isPaginated = false;
    } else {
        $dataMahasiswa = $query->paginate((int)$perPage)->withQueryString();
        $isPaginated = true;
    }

    return view('pages.kaprodi.monitoring.penempatan_mahasiswa', compact(
        'dataMahasiswa',
        'search',
        'perPage',
        'isPaginated'
    ));
}
    // ==================== LIHAT SURAT - MAHASISWA ====================

    public function lihatSuratPermohonanMahasiswa($id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $userLogin = Mahasiswa::where('id_user', $idUser)->first();

        if (!$userLogin) abort(403, 'Data mahasiswa tidak ditemukan.');

        $adaDiSurat = PengajuanMahasiswa::where('id_pengajuan', $id_pengajuan)
            ->where('id_mahasiswa', $userLogin->id_mahasiswa)->exists();

        if (!$adaDiSurat) abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');

        $pengajuan = PengajuanMagang::with(['perusahaan', 'mahasiswa.mahasiswa', 'approvals.jabatan', 'suratPermohonan'])->findOrFail($id_pengajuan);

        return view('pages.baak.upload_surat_permohonan', [
            'pengajuan'       => $pengajuan,
            'suratPermohonan' => $pengajuan->suratPermohonan,
            'backUrl'         => route('mahasiswa.pengajuan_magang.index'),
            'isViewOnly'      => true,
        ]);
    }

    // ==================== LIHAT SURAT - KAPRODI ====================

    public function lihatSuratPermohonanKaprodi($id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $dosen = Dosen::where('id_user', $idUser)->first();
        if (!$dosen) abort(403, 'Data dosen tidak ditemukan.');

        $pengajuan = PengajuanMagang::with(['perusahaan', 'mahasiswa.mahasiswa', 'approvals.jabatan', 'suratPermohonan'])->findOrFail($id_pengajuan);

        $idProdiKaprodi = $dosen->id_prodi;
        $adaMahasiswaSeProdi = $pengajuan->mahasiswa->contains(function ($pm) use ($idProdiKaprodi) {
            return $pm->mahasiswa && $pm->mahasiswa->id_prodi == $idProdiKaprodi;
        });
        if (!$adaMahasiswaSeProdi) abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');

        return view('pages.baak.upload_surat_permohonan', [
            'pengajuan'       => $pengajuan,
            'suratPermohonan' => $pengajuan->suratPermohonan,
            'backUrl'         => route('kaprodi.pengajuan_magang.index'),
            'isViewOnly'      => true,
        ]);
    }

    // ==================== LIHAT SURAT - KAJUR (MONITORING) ====================

    public function lihatSuratPermohonanKajur($id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $dosen = Dosen::where('id_user', $idUser)->first();
        if (!$dosen) abort(403, 'Data dosen tidak ditemukan.');

        $pengajuan = PengajuanMagang::with(['perusahaan', 'mahasiswa.mahasiswa.prodi', 'approvals.jabatan', 'suratPermohonan'])->findOrFail($id_pengajuan);

        $prodiKajur = \App\Models\Prodi::find($dosen->id_prodi);
        $idJurusanKajur = $prodiKajur ? $prodiKajur->id_jurusan : null;

        $adaMahasiswaSeJurusan = $pengajuan->mahasiswa->contains(function ($pm) use ($idJurusanKajur) {
            return $pm->mahasiswa && $pm->mahasiswa->prodi && $pm->mahasiswa->prodi->id_jurusan == $idJurusanKajur;
        });
        if (!$adaMahasiswaSeJurusan) abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');

        return view('pages.baak.upload_surat_permohonan', [
            'pengajuan'       => $pengajuan,
            'suratPermohonan' => $pengajuan->suratPermohonan,
            'backUrl'         => route('kajur.pengajuan_magang.index'),
            'isViewOnly'      => true,
        ]);
    }

    public function lihatSuratBalasanKajur($id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $dosen = Dosen::where('id_user', $idUser)->first();
        if (!$dosen) abort(403, 'Data dosen tidak ditemukan.');

        $pengajuan = PengajuanMagang::with('perusahaan', 'mahasiswa.mahasiswa.prodi')->findOrFail($id_pengajuan);

        $prodiKajur = \App\Models\Prodi::find($dosen->id_prodi);
        $idJurusanKajur = $prodiKajur ? $prodiKajur->id_jurusan : null;

        $adaMahasiswaSeJurusan = $pengajuan->mahasiswa->contains(function ($pm) use ($idJurusanKajur) {
            return $pm->mahasiswa && $pm->mahasiswa->prodi && $pm->mahasiswa->prodi->id_jurusan == $idJurusanKajur;
        });
        if (!$adaMahasiswaSeJurusan) abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');

        $mhsDenganSurat = $pengajuan->mahasiswa->firstWhere('surat_balasan', '!=', null);
        if (!$mhsDenganSurat) abort(404, 'Surat balasan belum diupload oleh mahasiswa.');

        return view('pages.kajur.pengajuan_magang.lihat_surat_balasan', [
            'pengajuan'    => $pengajuan,
            'suratBalasan' => $mhsDenganSurat->surat_balasan,
            'backUrl'      => route('kajur.pengajuan_magang.index'),
        ]);
    }

    public function lihatSuratBalasanBaak($id_pengajuan)
    {
        $pengajuan = PengajuanMagang::with('perusahaan', 'mahasiswa.mahasiswa')->findOrFail($id_pengajuan);

        $mhsDenganSurat = $pengajuan->mahasiswa->firstWhere('surat_balasan', '!=', null);
        if (!$mhsDenganSurat) abort(404, 'Surat balasan belum diupload oleh mahasiswa.');

        return view('pages.kajur.pengajuan_magang.lihat_surat_balasan', [
            'pengajuan'    => $pengajuan,
            'suratBalasan' => $mhsDenganSurat->surat_balasan,
            'backUrl'      => route('baak.pengajuan_magang.index'),
        ]);
    }

    public function lihatSuratPengantarMahasiswa($id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $userLogin = Mahasiswa::where('id_user', $idUser)->first();
        if (!$userLogin) abort(403, 'Data mahasiswa tidak ditemukan.');

        $adaDiSurat = PengajuanMahasiswa::where('id_pengajuan', $id_pengajuan)->where('id_mahasiswa', $userLogin->id_mahasiswa)->exists();
        if (!$adaDiSurat) abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');

        $pengajuan = PengajuanMagang::with(['perusahaan', 'mahasiswa.mahasiswa', 'approvals.jabatan', 'suratPengantar'])->findOrFail($id_pengajuan);

        return view('pages.baak.upload_surat_pengantar', [
            'pengajuan'      => $pengajuan,
            'suratPengantar' => $pengajuan->suratPengantar,
            'backUrl'        => route('mahasiswa.pengajuan_magang.index'),
            'isViewOnly'     => true,
        ]);
    }

    public function lihatSuratPengantarKaprodi($id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $dosen = Dosen::where('id_user', $idUser)->first();
        if (!$dosen) abort(403, 'Data dosen tidak ditemukan.');

        $pengajuan = PengajuanMagang::with(['perusahaan', 'mahasiswa.mahasiswa', 'approvals.jabatan', 'suratPengantar'])->findOrFail($id_pengajuan);

        $idProdiKaprodi = $dosen->id_prodi;
        $adaMahasiswaSeProdi = $pengajuan->mahasiswa->contains(function ($pm) use ($idProdiKaprodi) {
            return $pm->mahasiswa && $pm->mahasiswa->id_prodi == $idProdiKaprodi;
        });
        if (!$adaMahasiswaSeProdi) abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');

        return view('pages.baak.upload_surat_pengantar', [
            'pengajuan'      => $pengajuan,
            'suratPengantar' => $pengajuan->suratPengantar,
            'backUrl'        => route('kaprodi.pengajuan_magang.index'),
            'isViewOnly'     => true,
        ]);
    }

    public function lihatSuratPengantarKajur($id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $dosen = Dosen::where('id_user', $idUser)->first();
        if (!$dosen) abort(403, 'Data dosen tidak ditemukan.');

        $pengajuan = PengajuanMagang::with(['perusahaan', 'mahasiswa.mahasiswa.prodi', 'approvals.jabatan', 'suratPengantar'])->findOrFail($id_pengajuan);

        $prodiKajur = \App\Models\Prodi::find($dosen->id_prodi);
        $idJurusanKajur = $prodiKajur ? $prodiKajur->id_jurusan : null;

        $adaMahasiswaSeJurusan = $pengajuan->mahasiswa->contains(function ($pm) use ($idJurusanKajur) {
            return $pm->mahasiswa && $pm->mahasiswa->prodi && $pm->mahasiswa->prodi->id_jurusan == $idJurusanKajur;
        });
        if (!$adaMahasiswaSeJurusan) abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');

        return view('pages.baak.upload_surat_pengantar', [
            'pengajuan'      => $pengajuan,
            'suratPengantar' => $pengajuan->suratPengantar,
            'backUrl'        => route('kajur.pengajuan_magang.index'),
            'isViewOnly'     => true,
        ]);
    }

    // ==================== MONITORING PENEMPATAN KAPRODI ====================

   

    // ==================== HELPER LAINNYA ====================

private function cekSyaratSemesterPengajuan($mahasiswa)
{
    if (!$mahasiswa->relationLoaded('prodi')) {
        $mahasiswa->load('prodi');
    }

    $prodi = $mahasiswa->prodi;
    $semesterSekarang = $mahasiswa->semester;

    // Ambil langsung dari kolom semester_pengajuan_magang di tabel prodi
    // Kalau belum diisi (null), fallback ke 99 = otomatis tidak boleh ajukan
    $minSemester = $prodi->semester_pengajuan_magang ?? 99;

    // Deteksi apakah admin belum mengisi aturan semester
    $belumDiatur = is_null($prodi->semester_pengajuan_magang);

    return [
        'boleh_ajukan'      => $semesterSekarang >= $minSemester,
        'semester_sekarang' => $semesterSekarang,
        'min_semester'      => $minSemester,
        'nama_prodi'        => $prodi->nama_prodi ?? 'Unknown',
        'belum_diatur'      => $belumDiatur,
    ];
}

// ==================== KAPRODI - UPDATE TANGGAL MAGANG GLOBAL ====================

 // ==================== KAPRODI - UPDATE TANGGAL MAGANG GLOBAL ====================

public function updateTanggalMagangProdi(Request $request)
{
    $request->validate([
        'tgl_mulai' => 'required|date',
        'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai',
    ]);

    $idUser = auth()->user()->id_user;
    $dosen = Dosen::where('id_user', $idUser)->first();

    if (!$dosen) {
        abort(403, 'Data dosen tidak ditemukan.');
    }

    $idProdiKaprodi = $dosen->id_prodi;

    // UPDATE SEMUA pengajuan yang ada di prodi ini
    // Ambil semua id_pengajuan yang memiliki mahasiswa dari prodi ini
    $idPengajuans = PengajuanMahasiswa::whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
        $q->where('id_prodi', $idProdiKaprodi);
    })
    ->pluck('id_pengajuan')
    ->unique()
    ->toArray();

    // Update semua pengajuan tersebut
    $updated = PengajuanMagang::whereIn('id_pengajuan', $idPengajuans)
        ->update([
            'tgl_mulai' => $request->tgl_mulai,
            'tgl_selesai' => $request->tgl_selesai,
        ]);

    return redirect()
        ->route('kaprodi.pengajuan_magang.index')
        ->with('success', "Tanggal magang berhasil diperbarui untuk {$updated} pengajuan.");
}

    public function batalkanPengajuan($id_pengajuan)
    {
        $idUser = auth()->user()->id_user;
        $userLogin = Mahasiswa::where('id_user', $idUser)->first();
        if (!$userLogin) abort(403, 'Data mahasiswa tidak ditemukan.');

        $pengajuan = PengajuanMagang::with(['suratPermohonan', 'suratPengantar', 'mahasiswa'])->findOrFail($id_pengajuan);

        $pengajuanMhs = PengajuanMahasiswa::where('id_pengajuan', $id_pengajuan)
            ->where('id_mahasiswa', $userLogin->id_mahasiswa)->first();

        if (!$pengajuanMhs) abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');

        if ($pengajuan->suratPengantar) {
            return redirect()->route('mahasiswa.pengajuan_magang.index')->with('error', 'Pengajuan tidak bisa dibatalkan karena Surat Pengantar sudah diterbitkan.');
        }

        if (!$pengajuan->suratPermohonan) {
            return redirect()->route('mahasiswa.pengajuan_magang.index')->with('error', 'Pengajuan tidak bisa dibatalkan karena Anda belum menerima Surat Permohonan dari BAAK.');
        }

        $tglSuratTurun = $pengajuan->suratPermohonan
            ? \Carbon\Carbon::parse($pengajuan->suratPermohonan->uploaded_at)->startOfDay()
            : \Carbon\Carbon::parse($pengajuan->tgl_pengajuan)->startOfDay();
        $today = \Carbon\Carbon::now()->startOfDay();

        if (!is_null($pengajuan->tgl_mulai)) {
            $tglMulai = \Carbon\Carbon::parse($pengajuan->tgl_mulai)->startOfDay();
            $jarakKeMulai = $tglSuratTurun->diffInDays($tglMulai);
            $durasiTunggu = ($jarakKeMulai <= 50) ? 14 : 30;
            $batasWaktuBatal = $tglSuratTurun->copy()->addDays($durasiTunggu);

            if ($today->lessThan($batasWaktuBatal)) {
                return redirect()->route('mahasiswa.pengajuan_magang.index')
                    ->with('error', 'Pengajuan belum bisa dibatalkan. Anda baru bisa membatalkan setelah ' . $batasWaktuBatal->locale('id')->translatedFormat('d F Y') . '.');
            }
        } else {
            return redirect()->route('mahasiswa.pengajuan_magang.index')->with('error', 'Pengajuan belum bisa dibatalkan karena tanggal magang belum ditentukan oleh Kaprodi.');
        }

        $pengajuanMhs->update(['status' => 'ditolak', 'status_surat_balasan' => 'ditolak']);

        $semuaDitolak = PengajuanMahasiswa::where('id_pengajuan', $id_pengajuan)->where('status', '!=', 'ditolak')->doesntExist();
        if ($semuaDitolak) {
            ApprovalPengajuan::where('id_pengajuan', $id_pengajuan)->delete();
        }

        return redirect()->route('mahasiswa.pengajuan_magang.index')->with('success', 'Pengajuan magang berhasil dibatalkan.');
    }

    public function kirimEmailSuratPermohonan($id_pengajuan)
    {
        $pengajuan = PengajuanMagang::with('perusahaan', 'mahasiswa.mahasiswa', 'suratPermohonan')->findOrFail($id_pengajuan);
        if (!$pengajuan->suratPermohonan) return back()->with('error', 'Surat Permohonan belum diupload.');
        $emailPerusahaan = $pengajuan->email_perusahaan;
        if (empty($emailPerusahaan)) return back()->with('error', 'Email perusahaan belum diisi.');

        try {
            $filePath = storage_path('app/public/surat_permohonan/' . $pengajuan->suratPermohonan->file_surat);
            \Mail::to($emailPerusahaan)->send(new \App\Mail\SuratMagangMail($pengajuan, $filePath, 'permohonan'));
            return back()->with('success', ' Surat Permohonan berhasil dikirim ke email ' . $emailPerusahaan);
        } catch (\Exception $e) {
            \Log::error('Gagal kirim email: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengirim email. Error: ' . $e->getMessage());
        }
    }

    public function kirimEmailSuratPengantar($id_pengajuan)
    {
        $pengajuan = PengajuanMagang::with('perusahaan', 'mahasiswa.mahasiswa', 'suratPengantar')->findOrFail($id_pengajuan);
        if (!$pengajuan->suratPengantar) return back()->with('error', 'Surat Pengantar belum diupload.');
        $emailPerusahaan = $pengajuan->email_perusahaan;
        if (empty($emailPerusahaan)) return back()->with('error', 'Email perusahaan belum diisi.');

        try {
            $filePath = storage_path('app/public/surat_pengantar_upload/' . $pengajuan->suratPengantar->file_surat);
            \Mail::to($emailPerusahaan)->send(new \App\Mail\SuratMagangMail($pengajuan, $filePath, 'pengantar'));
            return back()->with('success', ' Surat Pengantar berhasil dikirim ke email ' . $emailPerusahaan);
        } catch (\Exception $e) {
            \Log::error('Gagal kirim email: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengirim email. Error: ' . $e->getMessage());
        }
    }
    // ==================== LIHAT SURAT BALASAN - MAHASISWA ====================

public function lihatSuratBalasanMahasiswa($id_pengajuan)
{
    $idUser = auth()->user()->id_user;
    $userLogin = Mahasiswa::where('id_user', $idUser)->first();
    if (!$userLogin) abort(403, 'Data mahasiswa tidak ditemukan.');

    $adaDiSurat = PengajuanMahasiswa::where('id_pengajuan', $id_pengajuan)
        ->where('id_mahasiswa', $userLogin->id_mahasiswa)->exists();
    if (!$adaDiSurat) abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');

    $pengajuan = PengajuanMagang::with('perusahaan', 'mahasiswa.mahasiswa.prodi')
                    ->findOrFail($id_pengajuan);

    $mhsDenganSurat = $pengajuan->mahasiswa->firstWhere('surat_balasan', '!=', null);
    if (!$mhsDenganSurat) abort(404, 'Surat balasan belum diupload.');

    return view('pages.mahasiswa.pengajuan_magang.lihat_surat_balasan', [
        'pengajuan'    => $pengajuan,
        'suratBalasan' => $mhsDenganSurat->surat_balasan,
        'backUrl'      => route('mahasiswa.pengajuan_magang.index'),
    ]);
}
}