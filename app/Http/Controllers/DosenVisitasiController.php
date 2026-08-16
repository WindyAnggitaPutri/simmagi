<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DosenVisitasi;
use App\Models\Dosen;
use App\Models\Notifikasi;
use App\Models\DosenPembimbing;
use App\Models\Mahasiswa;
use App\Models\PengajuanMagang;
use App\Models\Prodi;
use App\Models\InterviewVisitasiMagangA;
use App\Models\InterviewVisitasiMagangB;

class DosenVisitasiController extends Controller
{
    // ==================== KAPRODI CRUD ====================
    public function indexKaprodi(Request $request)
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $idProdiKaprodi = $dosenLogin->id_prodi;

        $search       = $request->input('search');
        $filterTahun  = $request->input('filter_tahun');
        $filterKelas  = $request->input('filter_kelas');
        $perPage      = $request->input('per_page', 5);
        $filterTahunVisitasi = $request->input('filter_tahun_visitasi'); 

        // ✅ AMBIL DAFTAR TAHUN ANGKATAN UNIK
        $daftarTahun = DosenVisitasi::whereHas('dosen', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->whereHas('mahasiswa', function ($q) {
                $q->whereNotNull('angkatan');
            })
            ->with('mahasiswa')
            ->get()
            ->pluck('mahasiswa.angkatan')
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();
        
          // ✅ TAMBAH: Ambil Daftar Tahun Visitasi Unik
        $daftarTahunVisitasi = DosenVisitasi::whereHas('dosen', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->whereNotNull('tahun')
            ->distinct()
            ->pluck('tahun')
            ->sortDesc()
            ->values()
            ->toArray();

        // ✅ AMBIL DAFTAR KELAS UNIK (A, B, C, D)
        $daftarKelas = DosenVisitasi::whereHas('dosen', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->whereHas('mahasiswa', function ($q) {
                $q->whereNotNull('kelas')->whereIn('kelas', ['A', 'B', 'C', 'D']);
            })
            ->with('mahasiswa')
            ->get()
            ->pluck('mahasiswa.kelas')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // ✅ QUERY UTAMA
        $query = DosenVisitasi::with(['dosen', 'mahasiswa', 'perusahaan'])
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
         // ✅ TAMBAH: Filter Tahun Visitasi
        if ($filterTahunVisitasi) {
            $query->where('tahun', $filterTahunVisitasi);
        }

        // ✅ FILTER KELAS (BARU)
        if ($filterKelas) {
            $query->whereHas('mahasiswa', function ($q) use ($filterKelas) {
                $q->where('kelas', $filterKelas);
            });
        }
        
        // ✅ SEARCH
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
                ->orWhereHas('perusahaan', function ($q2) use ($search) {
                    $q2->where('nama_perusahaan', 'like', '%' . $search . '%');
                });
            });
        }

        $results = $query->orderBy('id_dosen_visitasi', 'desc')
                         ->paginate($perPage)
                         ->withQueryString();

        // ✅ TAMBAHKAN INI: Ambil data interview untuk cek status kelengkapan
        $visitasiIds = $results->pluck('id_dosen_visitasi')->toArray();

        $hasInterviewA = InterviewVisitasiMagangA::whereIn('id_dosen_visitasi', $visitasiIds)
            ->pluck('id_dosen_visitasi')
            ->unique()
            ->flip();

        $hasInterviewB = InterviewVisitasiMagangB::whereIn('id_dosen_visitasi', $visitasiIds)
            ->pluck('id_dosen_visitasi')
            ->unique()
            ->flip();

        return view('pages.kaprodi.pembagian_dosen_visitasi.index', 
            compact('results', 'dosenLogin', 'search', 'perPage', 'daftarTahun', 'filterTahun', 'hasInterviewA', 'hasInterviewB', 'daftarKelas', 'filterKelas', 'daftarTahunVisitasi', 'filterTahunVisitasi'));
    }

    public function createKaprodi()
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = DB::table('dosen')->where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $idProdi = $dosenLogin->id_prodi;

        $dosen = DB::table('dosen')
            ->where('id_prodi', $idProdi)
            ->orderBy('nama', 'asc')
            ->get();

        $mahasiswaTersedia = DB::table('dosen_pembimbing as dp')
            ->join('pengajuan_magang as pm', 'dp.id_pengajuan', '=', 'pm.id_pengajuan')
            ->join('mahasiswa as m', 'dp.id_mahasiswa', '=', 'm.id_mahasiswa')
            ->where('m.id_prodi', $idProdi)
            ->whereNotIn('dp.id_mahasiswa', function($q) {
                $q->select('id_mahasiswa')->from('dosen_visitasi');
            })
            ->select('dp.id_dosen_pembimbing', 'm.nama as mhs_nama', 'm.npm as mhs_npm',
                   'pm.id_perusahaan', 'dp.id_dosen as id_dosen_dospem')
            ->get();

        $perusahaanList = DB::table('perusahaan')
            ->whereIn('id_perusahaan', $mahasiswaTersedia->pluck('id_perusahaan')->unique())
            ->orderBy('nama_perusahaan', 'asc')
            ->get();

        $mahasiswaJson = $mahasiswaTersedia->map(function ($m) {
             $dospem = DB::table('dosen')->where('id_dosen', $m->id_dosen_dospem)->first();
            $dospemNama = $dospem ? $dospem->nama : '-';
            return [
                'id' => $m->id_dosen_pembimbing,
                'id_perusahaan' => $m->id_perusahaan,
                'nama' => $m->mhs_nama . ' (' . $m->mhs_npm . ') [Dospem: ' . $dospemNama . ']'
            ];
        });

        return view('pages.kaprodi.pembagian_dosen_visitasi.create', compact('dosen', 'perusahaanList', 'mahasiswaJson'));
    }

   
    public function storeKaprodi(Request $request)
    {
        $request->validate([
            'id_dosen'  => 'required|exists:dosen,id_dosen',
            'mahasiswa' => 'required|array|min:1',
            'mahasiswa.*' => 'exists:dosen_pembimbing,id_dosen_pembimbing',
            'tahun'     => 'required|digits:4|integer|min:2020|max:2099',
        ]);

        foreach (array_unique($request->mahasiswa) as $idDospem) {
            $dp = DB::table('dosen_pembimbing')->where('id_dosen_pembimbing', $idDospem)->first();

            if ($dp) {
                $pm = DB::table('pengajuan_magang')
                    ->where('id_pengajuan', $dp->id_pengajuan)
                    ->first();

                $idPerusahaan = $pm ? $pm->id_perusahaan : null;

                $sudahAda = DB::table('dosen_visitasi')
                    ->where('id_mahasiswa', $dp->id_mahasiswa)
                    ->exists();

                if (!$sudahAda) {
                    DB::table('dosen_visitasi')->insert([
                        'id_dosen'            => $request->id_dosen,
                        'id_mahasiswa'        => $dp->id_mahasiswa,
                        'id_perusahaan'       => $idPerusahaan,
                        'id_dosen_pembimbing' => $dp->id_dosen_pembimbing,
                        'tahun'              => $request->tahun,
                        // ✅ HAPUS: 'status' => 'draft',
                    ]);
                }
            }
        }

        return redirect()->route('kaprodi.pembagian-dosen-visitasi.index')
            ->with('success', 'Dosen visitasi berhasil ditugaskan!');
    }
    public function editKaprodi($id_dosen_visitasi)
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = DB::table('dosen')->where('id_user', $idUser)->first();

        if (!$dosenLogin) abort(403);

        $idProdi = $dosenLogin->id_prodi;

        // ✅ GANTI QUERY INI MENJADI ELOQUENT
        // Ini akan mengambil data beserta relasinya (dosen, mahasiswa, perusahaan, dll)
        $visitasi = DosenVisitasi::with([
                'dosen', 
                'mahasiswa', 
                'perusahaan', 
                'dosenPembimbing.dosen'
            ])
            ->where('id_dosen_visitasi', $id_dosen_visitasi)
            ->first();

        if (!$visitasi) abort(404);

        // ✅ Security Check: Pastikan data visitasi ini milik prodi Kaprodi yang login
        // Karena sekarang pakai Eloquent, kita akses relasinya: $visitasi->dosen->id_prodi
        if ($visitasi->dosen->id_prodi != $idProdi) {
            abort(403, 'Akses ditolak.');
        }

        // --- Logic untuk Dropdown (Form) ---
        
        $dosen = DB::table('dosen')
            ->where('id_prodi', $idProdi)
            ->orderBy('nama', 'asc')
            ->get();

        // Ambil ID mahasiswa yang sedang diedit dari object Eloquent
        $currentMhsIds = [$visitasi->id_mahasiswa];

        $mahasiswaTersedia = DB::table('dosen_pembimbing as dp')
            ->join('pengajuan_magang as pm', 'dp.id_pengajuan', '=', 'pm.id_pengajuan')
            ->join('mahasiswa as m', 'dp.id_mahasiswa', '=', 'm.id_mahasiswa')
            ->where('m.id_prodi', $idProdi)
            ->where(function ($q) use ($currentMhsIds) {
                $q->whereNotIn('dp.id_mahasiswa', function($q2) {
                    $q2->select('id_mahasiswa')->from('dosen_visitasi');
                })->orWhereIn('dp.id_mahasiswa', $currentMhsIds);
            })
            ->select('dp.id_dosen_pembimbing', 'm.nama as mhs_nama', 'm.npm as mhs_npm',
                   'pm.id_perusahaan', 'dp.id_dosen as id_dosen_dospem')
            ->get();

        $perusahaanList = DB::table('perusahaan')
            ->whereIn('id_perusahaan', $mahasiswaTersedia->pluck('id_perusahaan')->unique())
            ->orderBy('nama_perusahaan', 'asc')
            ->get();

        $mahasiswaJson = $mahasiswaTersedia->map(function ($m) {
             $dospem = DB::table('dosen')->where('id_dosen', $m->id_dosen_dospem)->first();
            $dospemNama = $dospem ? $dospem->nama : '-';
            return [
                'id' => $m->id_dosen_pembimbing,
                'id_perusahaan' => $m->id_perusahaan,
                'nama' => $m->mhs_nama . ' (' . $m->mhs_npm . ') [Dospem: ' . $dospemNama . ']'
            ];
        });

        $currentMahasiswa = DB::table('mahasiswa')
            ->where('id_mahasiswa', $visitasi->id_mahasiswa)
            ->first();

       return view('pages.kaprodi.pembagian_dosen_visitasi.edit',
    compact('visitasi', 'dosen', 'perusahaanList', 'mahasiswaJson', 'mahasiswaTersedia', 'currentMahasiswa'));
    }

  public function updateKaprodi(Request $request, $id_dosen_visitasi)
    {
        $request->validate([
            'id_dosen'  => 'required|exists:dosen,id_dosen',
            'mahasiswa' => 'required|array|min:1',
            'mahasiswa.*' => 'exists:dosen_pembimbing,id_dosen_pembimbing',
            'tahun'     => 'required|digits:4|integer|min:2020|max:2099',
        ]);

        DB::table('dosen_visitasi')->where('id_dosen_visitasi', $id_dosen_visitasi)->delete();

        foreach (array_unique($request->mahasiswa) as $idDospem) {
            $dp = DB::table('dosen_pembimbing')->where('id_dosen_pembimbing', $idDospem)->first();

            if ($dp) {
                $pm = DB::table('pengajuan_magang')
                    ->where('id_pengajuan', $dp->id_pengajuan)
                    ->first();

                $idPerusahaan = $pm ? $pm->id_perusahaan : null;
   $sudahAda = DB::table('dosen_visitasi')
                    ->where('id_mahasiswa', $dp->id_mahasiswa)
                    ->exists();

                if (!$sudahAda) {
                    DB::table('dosen_visitasi')->insert([
                        'id_dosen'            => $request->id_dosen,
                        'id_mahasiswa'        => $dp->id_mahasiswa,
                        'id_perusahaan'       => $idPerusahaan,
                        'id_dosen_pembimbing'  => $dp->id_dosen_pembimbing,
                        'tahun'              => $request->tahun,
                        // ✅ HAPUS: 'status' => 'draft',
                    ]);
                }
            }
        }

        return redirect()->route('kaprodi.pembagian-dosen-visitasi.index')
            ->with('success', 'Dosen visitasi berhasil diperbarui!');
    }

    public function destroyKaprodi($id_dosen_visitasi)
    {
        DB::table('dosen_visitasi')->where('id_dosen_visitasi', $id_dosen_visitasi)->delete();

        // ✅ FIX: route name diperbaiki
        return redirect()->route('kaprodi.pembagian-dosen-visitasi.index')
            ->with('success', 'Dosen visitasi berhasil dihapus!');
    }

    public function detailKaprodi($id_dosen_visitasi)
    {
        // ✅ Gunakan Eloquent Model agar relasi (dosen, mahasiswa, dll) ter-load sebagai object
        $visitasi = DosenVisitasi::with([
                'dosen', 
                'mahasiswa', 
                'perusahaan', 
                'dosenPembimbing.dosen' // Load relasi dosen di dalam dosenPembimbing
            ])
            ->where('id_dosen_visitasi', $id_dosen_visitasi)
            ->first();

        if (!$visitasi) {
            abort(404);
        }

        return view('pages.kaprodi.pembagian_dosen_visitasi.detail', compact('visitasi'));
    }
    public function lihatSkKaprodi($id_dosen_visitasi)
    {
        $visitasi = DB::table('dosen_visitasi')
            ->where('id_dosen_visitasi', $id_dosen_visitasi)
            ->first();

        if (!$visitasi || !$visitasi->file_sk) {
            return redirect()->route('kaprodi.pembagian-dosen-visitasi.index')
                ->withErrors('SK Dosen Visitasi belum tersedia.');
        }

        $dosen = DB::table('dosen')
            ->where('id_dosen', $visitasi->id_dosen)
            ->first();

        $prodi = DB::table('prodi')
            ->where('id_prodi', $dosen->id_prodi)
            ->first();

        $fileSk = $visitasi->file_sk;

        if (!file_exists(storage_path('app/public/sk_visitasi/' . $fileSk))) {
            return redirect()->route('kaprodi.pembagian-dosen-visitasi.index')
                ->withErrors('File SK tidak ditemukan di storage.');
        }

        $backUrl = request()->query('from', route('kaprodi.pembagian-dosen-visitasi.index'));

        return view('pages.baup.dosen_visitasi.lihat_sk_visitasi', compact('prodi', 'fileSk', 'backUrl'));
    }

    public function ajukanKaprodi()
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $idProdiKaprodi = $dosenLogin->id_prodi;

        $sedangDiproses = DosenVisitasi::whereHas('dosen', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->whereIn('status', ['menunggu_kajur', 'menunggu_baup', 'disetujui_baup'])
            ->exists();

        if ($sedangDiproses) {
            return redirect()->route('kaprodi.pembagian-dosen-visitasi.index')
                ->withErrors('Tidak bisa mengajukan karena masih ada penugasan yang sedang diproses.');
        }

        $visitasiIds = DosenVisitasi::whereHas('dosen', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->whereIn('status', ['draft', 'ditolak_kajur', 'ditolak_baup'])
            ->pluck('id_dosen_visitasi');

        if ($visitasiIds->isEmpty()) {
            return redirect()->route('kaprodi.pembagian-dosen-visitasi.index')
                ->withErrors('Tidak ada penugasan yang bisa diajukan.');
        }

        DosenVisitasi::whereIn('id_dosen_visitasi', $visitasiIds)
            ->update(['status' => 'menunggu_kajur']);

        $idJurusan = Prodi::find($idProdiKaprodi)?->id_jurusan;
        $kajurList = Dosen::whereHas('jabatanTendik.jabatan', function ($q) {
                $q->where('nama_jabatan', 'LIKE', '%ketua jurusan%');
            })
            ->whereHas('prodi', function ($q) use ($idJurusan) {
                $q->where('id_jurusan', $idJurusan);
            })
            ->get();

        $prodi = Prodi::find($idProdiKaprodi);
        foreach ($kajurList as $kajur) {
            if ($kajur->id_user) {
                Notifikasi::kirim(
                    $kajur->id_user,
                    'Pengajuan Dosen Visitasi Baru',
                    'Kaprodi Prodi ' . $prodi->nama_prodi . ' mengajukan pembagian dosen visitasi. Silakan review.',
                    'kajur_visitasi_baru',
                    null
                );
            }
        }

        return redirect()->route('kaprodi.pembagian-dosen-visitasi.index')
            ->with('success', 'Penugasan dosen visitasi berhasil diajukan ke Kajur! Menunggu persetujuan.');
    }

    // ==================== PREVIEW SURAT FINAL KAPRODI ====================

    public function previewSuratFinalKaprodi()
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $idProdiKaprodi = $dosenLogin->id_prodi;

        $previewVisitasi = DosenVisitasi::with(['dosen', 'mahasiswa', 'perusahaan', 'dosenPembimbing.dosen'])
            ->whereHas('dosen', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($idProdiKaprodi) {
                $q->where('id_prodi', $idProdiKaprodi);
            })
            ->orderBy('id_dosen')
            ->get()
            ->groupBy('id_dosen');

        if ($previewVisitasi->isEmpty()) {
            return redirect()->route('kaprodi.pembagian-dosen-visitasi.index')
                ->withErrors('Belum ada data dosen visitasi untuk ditampilkan di surat.');
        }

        $ketuaJurusan = Dosen::whereHas('jabatanTendik.jabatan', function ($q) {
            $q->where('nama_jabatan', 'LIKE', '%ketua jurusan%');
        })->first();

        $totalMhs = $previewVisitasi->flatten()->count();
        $totalDosen = $previewVisitasi->count();
        $nomorSurat = '199/DST/PL43.J02/PP.02.10/' . date('Y');
        $tanggalSurat = \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');

        $allVisitasi = $previewVisitasi->flatten();
        $sudahDiAccKajur = $allVisitasi->every(fn($v) => in_array($v->status, ['menunggu_baup', 'disetujui_baup', 'selesai']));
        $bisaDiApprove = false;
        $idProdi = null;

        $backUrl = route('kaprodi.pembagian-dosen-visitasi.index');
        $backText = 'Kembali ke Pembagian Dosen Visitasi';

        return view('pages.kaprodi.pembagian_dosen_visitasi.preview_surat_final', compact(
            'previewVisitasi', 'dosenLogin', 'backUrl', 'backText', 'ketuaJurusan',
            'totalMhs', 'totalDosen', 'nomorSurat', 'tanggalSurat',
            'sudahDiAccKajur', 'bisaDiApprove', 'idProdi'
        ));
    }

    // ==================== KAJUR ====================

    public function indexKajur()
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $idJurusanKajur = Prodi::find($dosenLogin->id_prodi)?->id_jurusan;

        if (!$idJurusanKajur) {
            abort(403, 'Jurusan tidak ditemukan.');
        }

        $prodiList = Prodi::where('id_jurusan', $idJurusanKajur)
            ->orderBy('nama_prodi', 'asc')
            ->get();

        $prodiData = [];
        foreach ($prodiList as $prodi) {
            $visitasis = DosenVisitasi::whereHas('dosen', function ($q) use ($prodi) {
                    $q->where('id_prodi', $prodi->id_prodi);
                })
                ->whereHas('mahasiswa', function ($q) use ($prodi) {
                    $q->where('id_prodi', $prodi->id_prodi);
                })
                ->where('status', '!=', 'draft')
                ->get();

            $total = $visitasis->count();
            $menungguKajur = $visitasis->where('status', 'menunggu_kajur')->count();
            $selesai = $visitasis->where('status', 'selesai')->count();
            $diproses = $visitasis->whereIn('status', ['menunggu_baup', 'disetujui_baup'])->count();
            $ditolak = $visitasis->whereIn('status', ['ditolak_kajur', 'ditolak_baup'])->count();

            if ($total === 0) {
                $statusProdi = 'belum_ada';
            } elseif ($menungguKajur === $total) {
                $statusProdi = 'menunggu_kajur';
            } elseif ($selesai === $total) {
                $statusProdi = 'selesai';
            } elseif ($ditolak === $total) {
                $statusProdi = 'ditolak';
            } else {
                $statusProdi = 'diproses';
            }

            $skPertama = $visitasis->where('status', 'selesai')->whereNotNull('file_sk')->first();

            $prodiData[] = [
                'prodi'          => $prodi,
                'total'          => $total,
                'menunggu_kajur' => $menungguKajur,
                'diproses'       => $diproses,
                'selesai'        => $selesai,
                'ditolak'        => $ditolak,
                'status'         => $statusProdi,
                'sk_pertama'     => $skPertama,
            ];
        }

        return view('pages.kajur.pembagian_dosen_visitasi.index', compact('prodiData'));
    }

    public function previewSuratFinalKajur($id_prodi)
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $idJurusanKajur = Prodi::find($dosenLogin->id_prodi)?->id_jurusan;

        if (!$idJurusanKajur) {
            abort(403, 'Jurusan tidak ditemukan.');
        }

        $prodi = Prodi::where('id_prodi', $id_prodi)
            ->where('id_jurusan', $idJurusanKajur)
            ->firstOrFail();

        $previewVisitasi = DosenVisitasi::with(['dosen', 'mahasiswa', 'perusahaan', 'dosenPembimbing.dosen'])
            ->whereHas('dosen', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->where('status', '!=', 'draft')
            ->orderBy('id_dosen')
            ->get()
            ->groupBy('id_dosen');

        if ($previewVisitasi->isEmpty()) {
            return redirect()->route('kajur.pembagian-dosen-visitasi.index')
                ->withErrors('Belum ada data dosen visitasi untuk prodi ini.');
        }

        $allVisitasi = $previewVisitasi->flatten();
        $sudahDiAccKajur = $allVisitasi->every(fn($v) => in_array($v->status, ['menunggu_baup', 'disetujui_baup', 'selesai']));
        $bisaDiApprove = $allVisitasi->contains(fn($v) => $v->status === 'menunggu_kajur');

        $ketuaJurusan = Dosen::whereHas('jabatanTendik.jabatan', function ($q) {
            $q->where('nama_jabatan', 'LIKE', '%ketua jurusan%');
        })->first();

        $totalMhs = $allVisitasi->count();
        $totalDosen = $previewVisitasi->count();
        $nomorSurat = '199/DST/PL43.J02/PP.02.10/' . date('Y');
        $tanggalSurat = \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');

        $backUrl = route('kajur.pembagian-dosen-visitasi.index');
        $backText = 'Kembali ke Persetujuan Dosen Visitasi';
        $idProdi = $id_prodi;

        return view('pages.kaprodi.pembagian_dosen_visitasi.preview_surat_final', compact(
            'previewVisitasi', 'dosenLogin', 'backUrl', 'backText', 'ketuaJurusan',
            'totalMhs', 'totalDosen', 'nomorSurat', 'tanggalSurat',
            'sudahDiAccKajur', 'bisaDiApprove', 'idProdi', 'prodi'
        ));
    }

    // ==================== PROSES APPROVAL KAJUR PER PRODI ====================

    public function prosesApprovalKajurPerProdi(Request $request, $id_prodi)
    {
        try {
            $request->validate([
                'status_approval' => 'required|in:disetujui,ditolak',
                'pakai_ttd'       => 'boolean',
                'catatan'         => 'nullable|string',
            ]);

            $idUser = auth()->user()->id_user;
            $dosenLogin = Dosen::where('id_user', $idUser)->first();

            if (!$dosenLogin) {
                return response()->json(['error' => 'Data dosen tidak ditemukan.'], 403);
            }

            $idJurusanKajur = Prodi::find($dosenLogin->id_prodi)?->id_jurusan;
            $prodi = Prodi::where('id_prodi', $id_prodi)
                ->where('id_jurusan', $idJurusanKajur)
                ->firstOrFail();

            $visitasis = DosenVisitasi::whereHas('dosen', function ($q) use ($id_prodi) {
                    $q->where('id_prodi', $id_prodi);
                })
                ->whereHas('mahasiswa', function ($q) use ($id_prodi) {
                    $q->where('id_prodi', $id_prodi);
                })
                ->where('status', 'menunggu_kajur')
                ->get();

            if ($visitasis->isEmpty()) {
                return response()->json(['error' => 'Tidak ada data yang perlu diproses.'], 400);
            }

            if ($request->status_approval === 'disetujui') {
                foreach ($visitasis as $visitasi) {
                    $visitasi->update(['status' => 'menunggu_baup']);
                }

                try {
                    Notifikasi::kirimKeKaprodiProdi(
                        $id_prodi,
                        'Dosen Visitasi Disetujui Kajur',
                        'Pembagian dosen visitasi prodi ' . $prodi->nama_prodi . ' telah disetujui Kajur. Menunggu BAUP upload SK.',
                        'kaprodi_visitasi_disetujui_kajur',
                        null
                    );
                } catch (\Exception $e) {
                    \Log::error('Gagal kirim notif ke Kaprodi (visitasi): ' . $e->getMessage());
                }

                try {
                    Notifikasi::kirimKeBaup(
                        'Dosen Visitasi Disetujui Kajur',
                        'Pembagian dosen visitasi prodi ' . $prodi->nama_prodi . ' telah disetujui Kajur. Siap upload SK.',
                        'baup_visitasi_disetujui_kajur',
                        null
                    );
                } catch (\Exception $e) {
                    \Log::error('Gagal kirim notif ke BAUP (visitasi): ' . $e->getMessage());
                }

                try {
                    Notifikasi::kirimKeBaak(
                        'Dosen Visitasi Disetujui Kajur',
                        'Pembagian dosen visitasi prodi ' . $prodi->nama_prodi . ' telah disetujui Kajur.',
                        'baak_visitasi_disetujui_kajur',
                        null
                    );
                } catch (\Exception $e) {
                    \Log::error('Gagal kirim notif ke BAAK (visitasi): ' . $e->getMessage());
                }

                $ketuaJurusan = Dosen::whereHas('jabatanTendik.jabatan', function ($q) {
                    $q->where('nama_jabatan', 'LIKE', '%ketua jurusan%');
                })->first();

                return response()->json([
                    'disetujui'  => true,
                    'pakai_ttd'  => $request->pakai_ttd ?? false,
                    'ttd_url'    => ($request->pakai_ttd && $ketuaJurusan && $ketuaJurusan->ttd_digital)
                                    ? asset('storage/ttd_digital/' . $ketuaJurusan->ttd_digital)
                                    : null,
                    'nama_kajur' => $ketuaJurusan->nama ?? '-',
                    'nip_kajur'  => $ketuaJurusan->nip ?? '-',
                ]);
            } else {
                foreach ($visitasis as $visitasi) {
                    $visitasi->update(['status' => 'ditolak_kajur']);
                }

                try {
                    Notifikasi::kirimKeKaprodiProdi(
                        $id_prodi,
                        'Dosen Visitasi Ditolak Kajur',
                        'Pembagian dosen visitasi prodi ' . $prodi->nama_prodi . ' ditolak Kajur. Silakan perbaiki dan ajukan ulang.',
                        'kaprodi_visitasi_ditolak_kajur',
                        null
                    );
                } catch (\Exception $e) {
                    \Log::error('Gagal kirim notif ke Kaprodi (visitasi ditolak): ' . $e->getMessage());
                }

                return response()->json([
                    'disetujui' => false,
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Error prosesApprovalKajurPerProdi Visitasi: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Terjadi kesalahan server: ' . $e->getMessage()], 500);
        }
    }

    // ==================== BAAK - DOSEN VISITASI ====================

  // ✅ GANTI dengan method ini
// ==================== BAAK - DOSEN VISITASI (MONITORING) ====================

public function indexBaak(Request $request)
{
    $search              = $request->input('search');
    $filterTahun         = $request->input('filter_tahun');
    $filterKelas         = $request->input('filter_kelas');
    $filterTahunVisitasi = $request->input('filter_tahun_visitasi');
    $perPage             = $request->input('per_page', 10);

    // ✅ Daftar tahun angkatan unik dari data visitasi
    $daftarTahun = DosenVisitasi::whereHas('mahasiswa', function ($q) {
            $q->whereNotNull('angkatan');
        })
        ->with('mahasiswa')
        ->get()
        ->pluck('mahasiswa.angkatan')
        ->filter()
        ->unique()
        ->sortDesc()
        ->values()
        ->toArray();

    // ✅ Daftar kelas unik
    $daftarKelas = DosenVisitasi::whereHas('mahasiswa', function ($q) {
            $q->whereNotNull('kelas')->whereIn('kelas', ['A', 'B', 'C', 'D']);
        })
        ->with('mahasiswa')
        ->get()
        ->pluck('mahasiswa.kelas')
        ->filter()
        ->unique()
        ->sort()
        ->values()
        ->toArray();

    // ✅ Daftar tahun visitasi unik
    $daftarTahunVisitasi = DosenVisitasi::whereNotNull('tahun')
        ->distinct()
        ->pluck('tahun')
        ->sortDesc()
        ->values()
        ->toArray();

    // ✅ QUERY UTAMA — tanpa filter status, tampilkan SEMUA data
    $query = DosenVisitasi::with(['dosen', 'mahasiswa', 'perusahaan']);

    // Filter tahun angkatan
    if ($filterTahun) {
        $query->whereHas('mahasiswa', function ($q) use ($filterTahun) {
            $q->where('angkatan', $filterTahun);
        });
    }

    // Filter tahun visitasi
    if ($filterTahunVisitasi) {
        $query->where('tahun', $filterTahunVisitasi);
    }

    // Filter kelas
    if ($filterKelas) {
        $query->whereHas('mahasiswa', function ($q) use ($filterKelas) {
            $q->where('kelas', $filterKelas);
        });
    }

    // Search
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
            ->orWhereHas('perusahaan', function ($q2) use ($search) {
                $q2->where('nama_perusahaan', 'like', '%' . $search . '%');
            });
        });
    }

    $visitasiList = $query->orderBy('id_dosen', 'asc')
                         ->orderBy('id_perusahaan', 'asc')
                         ->paginate($perPage)
                         ->withQueryString();

    return view('pages.kajur.dosen_visitasi.index', 
        compact('visitasiList', 'search', 'perPage', 'daftarTahun', 'filterTahun', 
                'daftarKelas', 'filterKelas', 'daftarTahunVisitasi', 'filterTahunVisitasi'));
}

    public function previewSuratFinalBaak($id_prodi)
    {
        $prodi = Prodi::findOrFail($id_prodi);

        $previewVisitasi = DosenVisitasi::with(['dosen', 'mahasiswa', 'perusahaan', 'dosenPembimbing.dosen'])
            ->whereHas('dosen', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->where('status', '!=', 'draft')
            ->orderBy('id_dosen')
            ->get()
            ->groupBy('id_dosen');

        if ($previewVisitasi->isEmpty()) {
            return redirect()->route('baak.dosen-visitasi.index')
                ->withErrors('Belum ada data dosen visitasi untuk prodi ini.');
        }

        $ketuaJurusan = Dosen::whereHas('jabatanTendik.jabatan', function ($q) {
            $q->where('nama_jabatan', 'LIKE', '%ketua jurusan%');
        })->first();

        $totalMhs = $previewVisitasi->flatten()->count();
        $totalDosen = $previewVisitasi->count();
        $nomorSurat = '199/DST/PL43.J02/PP.02.10/' . date('Y');
        $tanggalSurat = \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');

        $allVisitasi = $previewVisitasi->flatten();
        $sudahDiAccKajur = $allVisitasi->every(fn($v) => in_array($v->status, ['menunggu_baup', 'disetujui_baup', 'selesai']));
        $bisaDiApprove = false;
        $idProdi = $id_prodi;

        $backUrl = route('baak.dosen-visitasi.index');
        $backText = 'Kembali ke Pengelolaan Dosen Visitasi';

        return view('pages.kaprodi.pembagian_dosen_visitasi.preview_surat_final', compact(
            'previewVisitasi', 'backUrl', 'backText', 'ketuaJurusan',
            'totalMhs', 'totalDosen', 'nomorSurat', 'tanggalSurat',
            'sudahDiAccKajur', 'bisaDiApprove', 'idProdi'
        ));
    }

    public function formUploadSkBaak($id_prodi)
    {
        $prodi = Prodi::findOrFail($id_prodi);

        $visitasiList = DosenVisitasi::with(['dosen', 'mahasiswa', 'perusahaan'])
            ->whereHas('dosen', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->whereIn('status', ['menunggu_baup', 'disetujui_baup', 'selesai'])
            ->get();

        if ($visitasiList->isEmpty()) {
            return redirect()->route('baak.dosen-visitasi.index')
                ->withErrors('Tidak ada data dosen visitasi untuk prodi ini.');
        }

        $visitasiGroups = $visitasiList->groupBy('id_dosen');

        $existingSk = $visitasiList->where('file_sk', '!=', null)->first();
        $sudahUpload = $existingSk !== null;
        $fileSk = $existingSk?->file_sk;

        $totalDosen = $visitasiGroups->count();
        $totalMhs = $visitasiList->count();

        return view('pages.baak.dosen_visitasi.upload_sk_visitasi', compact(
            'prodi', 'visitasiGroups', 'sudahUpload', 'fileSk',
            'totalDosen', 'totalMhs'
        ));
    }

    public function lihatSkBaak($id_prodi)
    {
        $prodi = Prodi::findOrFail($id_prodi);

        $visitasiList = DosenVisitasi::with(['dosen', 'mahasiswa'])
            ->whereHas('dosen', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->where('status', 'selesai')
            ->whereNotNull('file_sk')
            ->get();

        if ($visitasiList->isEmpty()) {
            return redirect()->route('baak.dosen-visitasi.index')
                ->withErrors('SK Dosen Visitasi belum tersedia untuk prodi ini.');
        }

        $first = $visitasiList->first();
        $fileSk = $first->file_sk;

        if (!$fileSk || !file_exists(storage_path('app/public/sk_visitasi/' . $fileSk))) {
            return redirect()->route('baak.dosen-visitasi.index')
                ->withErrors('File SK tidak ditemukan di storage.');
        }

        $backUrl = request()->query('from', route('baak.dosen-visitasi.index'));

        return view('pages.baak.dosen_visitasi.lihat_sk_visitasi', compact(
            'prodi', 'fileSk', 'backUrl'
        ));
    }

    // ==================== BAUP - DOSEN VISITASI ====================

    public function indexBaup()
    {
        $semuaProdi = Prodi::with('jurusan')
            ->orderBy('id_jurusan')
            ->orderBy('nama_prodi')
            ->get();

        $prodiData = [];
        foreach ($semuaProdi as $prodi) {
            $visitasiApproved = DosenVisitasi::whereHas('dosen', function ($q) use ($prodi) {
                    $q->where('id_prodi', $prodi->id_prodi);
                })
                ->whereHas('mahasiswa', function ($q) use ($prodi) {
                    $q->where('id_prodi', $prodi->id_prodi);
                })
                ->whereIn('status', ['menunggu_baup', 'disetujui_baup', 'selesai'])
                ->get();

            if ($visitasiApproved->isEmpty()) continue;

            $total = $visitasiApproved->count();
            $menungguBaup = $visitasiApproved->where('status', 'menunggu_baup')->count();
            $disetujui = $visitasiApproved->where('status', 'disetujui_baup')->count();
            $selesai = $visitasiApproved->where('status', 'selesai')->count();

            if ($selesai === $total) {
                $statusProdi = 'selesai';
            } elseif ($menungguBaup === $total) {
                $statusProdi = 'menunggu_baup';
            } else {
                $statusProdi = 'diproses';
            }

            $existingSk = $visitasiApproved->where('file_sk', '!=', null)->first();
            $fileSk = $existingSk?->file_sk;

            $prodiData[] = [
                'prodi'          => $prodi,
                'total'          => $total,
                'menunggu_baup'  => $menungguBaup,
                'disetujui'      => $disetujui,
                'selesai'        => $selesai,
                'status'         => $statusProdi,
                'file_sk'        => $fileSk,
            ];
        }

        return view('pages.baup.dosen_visitasi.index', compact('prodiData'));
    }

    public function previewSuratFinalBaup($id_prodi)
    {
        $prodi = Prodi::findOrFail($id_prodi);

        $previewVisitasi = DosenVisitasi::with(['dosen', 'mahasiswa', 'perusahaan', 'dosenPembimbing.dosen'])
            ->whereHas('dosen', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->orderBy('id_dosen')
            ->get()
            ->groupBy('id_dosen');

        if ($previewVisitasi->isEmpty()) {
            return redirect()->route('baup.dosen-visitasi.index')
                ->withErrors('Belum ada data dosen visitasi untuk prodi ini.');
        }

        $ketuaJurusan = Dosen::whereHas('jabatanTendik.jabatan', function ($q) {
            $q->where('nama_jabatan', 'LIKE', '%ketua jurusan%');
        })->first();

        $totalMhs = $previewVisitasi->flatten()->count();
        $totalDosen = $previewVisitasi->count();
        $nomorSurat = '199/DST/PL43.J02/PP.02.10/' . date('Y');
        $tanggalSurat = \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');

        $allVisitasi = $previewVisitasi->flatten();
        $sudahDiAccKajur = $allVisitasi->every(fn($v) => in_array($v->status, ['menunggu_baup', 'disetujui_baup', 'selesai']));
        $bisaDiApprove = false;
        $idProdi = $id_prodi;

        $backUrl = route('baup.dosen-visitasi.index');
        $backText = 'Kembali ke Pengelolaan Dosen Visitasi';

        return view('pages.kaprodi.pembagian_dosen_visitasi.preview_surat_final', compact(
            'previewVisitasi', 'backUrl', 'backText', 'ketuaJurusan',
            'totalMhs', 'totalDosen', 'nomorSurat', 'tanggalSurat',
            'sudahDiAccKajur', 'bisaDiApprove', 'idProdi'
        ));
    }

    public function formUploadSkBaup($id_prodi)
    {
        $prodi = Prodi::findOrFail($id_prodi);

        $visitasiList = DosenVisitasi::with(['dosen', 'mahasiswa', 'perusahaan'])
            ->whereHas('dosen', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->whereIn('status', ['menunggu_baup', 'disetujui_baup', 'selesai'])
            ->get();

        if ($visitasiList->isEmpty()) {
            return redirect()->route('baup.dosen-visitasi.index')
                ->withErrors('Tidak ada data dosen visitasi untuk prodi ini.');
        }

        $visitasiGroups = $visitasiList->groupBy('id_dosen');

        $existingSk = $visitasiList->where('file_sk', '!=', null)->first();
        $sudahUpload = $existingSk !== null;
        $fileSk = $existingSk?->file_sk;

        $totalDosen = $visitasiGroups->count();
        $totalMhs = $visitasiList->count();

        return view('pages.baup.dosen_visitasi.upload_sk_visitasi', compact(
            'prodi', 'visitasiGroups', 'sudahUpload', 'fileSk',
            'totalDosen', 'totalMhs'
        ));
    }

    public function uploadSkBaup(Request $request, $id_prodi)
    {
        $request->validate([
            'file_sk' => 'required|file|mimes:pdf|max:5120',
        ]);

        $prodi = Prodi::findOrFail($id_prodi);

        $visitasiList = DosenVisitasi::whereHas('dosen', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->whereIn('status', ['menunggu_baup', 'disetujui_baup', 'selesai'])
            ->get();

        if ($visitasiList->isEmpty()) {
            return redirect()->route('baup.dosen-visitasi.index')
                ->withErrors('Tidak ada data dosen visitasi untuk prodi ini.');
        }

        $oldStatuses = $visitasiList->pluck('status', 'id_dosen_visitasi')->toArray();

        $existingSk = $visitasiList->where('file_sk', '!=', null)->first();
        if ($existingSk && $existingSk->file_sk && file_exists(storage_path('app/public/sk_visitasi/' . $existingSk->file_sk))) {
            unlink(storage_path('app/public/sk_visitasi/' . $existingSk->file_sk));
        }

        $file = $request->file('file_sk');
        $namaFile = time() . '_sk_visitasi_' . $prodi->nama_prodi . '_' . $file->getClientOriginalName();
        $file->storeAs('sk_visitasi', $namaFile, 'public');

        DosenVisitasi::whereHas('dosen', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->whereIn('status', ['menunggu_baup', 'disetujui_baup', 'selesai'])
            ->update([
                'file_sk' => $namaFile,
                'status'  => 'selesai'
            ]);

        $visitasiList->load(['dosen', 'mahasiswa', 'perusahaan']);

        foreach ($visitasiList as $visitasi) {
            $oldStatus = $oldStatuses[$visitasi->id_dosen_visitasi] ?? null;
            if ($oldStatus !== 'selesai' && $visitasi->mahasiswa && $visitasi->mahasiswa->id_user) {
                Notifikasi::kirim(
                    $visitasi->mahasiswa->id_user,
                    'Dosen Visitasi Ditetapkan',
                    'Dosen visitasi Anda telah ditetapkan oleh Kaprodi.',
                    'dosen_visitasi',
                    null
                );
            }
        }

        Notifikasi::kirimKeKaprodiProdi(
            $id_prodi,
            'SK Dosen Visitasi Diupload',
            'SK Dosen Visitasi prodi ' . $prodi->nama_prodi . ' telah diupload oleh BAUP.',
            'kaprodi_visitasi_upload_sk',
            null
        );

        Notifikasi::kirimKeBaak(
            'SK Dosen Visitasi Diupload',
            'SK Dosen Visitasi prodi ' . $prodi->nama_prodi . ' telah diupload oleh BAUP.',
            'baak_visitasi_upload_sk',
            null
        );

        return redirect()->route('baup.dosen-visitasi.index')
            ->with('success', 'SK Dosen Visitasi untuk prodi ' . $prodi->nama_prodi . ' berhasil diupload!');
    }

    public function lihatSkBaup($id_prodi)
    {
        $prodi = Prodi::findOrFail($id_prodi);

        $visitasiList = DosenVisitasi::with(['dosen', 'mahasiswa'])
            ->whereHas('dosen', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->where('status', 'selesai')
            ->whereNotNull('file_sk')
            ->get();

        if ($visitasiList->isEmpty()) {
            return redirect()->route('baup.dosen-visitasi.index')
                ->withErrors('SK Dosen Visitasi belum tersedia untuk prodi ini.');
        }

        $first = $visitasiList->first();
        $fileSk = $first->file_sk;

        if (!$fileSk || !file_exists(storage_path('app/public/sk_visitasi/' . $fileSk))) {
            return redirect()->route('baup.dosen-visitasi.index')
                ->withErrors('File SK tidak ditemukan di storage.');
        }

        $backUrl = request()->query('from', route('baup.dosen-visitasi.index'));

        return view('pages.baup.dosen_visitasi.lihat_sk_visitasi', compact(
            'prodi', 'fileSk', 'backUrl'
        ));
    }

    public function lihatSkKajur($id_prodi)
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $idJurusanKajur = Prodi::find($dosenLogin->id_prodi)?->id_jurusan;

        $prodi = Prodi::where('id_prodi', $id_prodi)
            ->where('id_jurusan', $idJurusanKajur)
            ->firstOrFail();

        $visitasiList = DosenVisitasi::with(['dosen', 'mahasiswa'])
            ->whereHas('dosen', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->whereHas('mahasiswa', function ($q) use ($id_prodi) {
                $q->where('id_prodi', $id_prodi);
            })
            ->where('status', 'selesai')
            ->whereNotNull('file_sk')
            ->get();

        if ($visitasiList->isEmpty()) {
            return redirect()->route('kajur.pembagian-dosen-visitasi.index')
                ->withErrors('SK Dosen Visitasi belum tersedia untuk prodi ini.');
        }

        $first = $visitasiList->first();
        $fileSk = $first->file_sk;

        if (!$fileSk || !file_exists(storage_path('app/public/sk_visitasi/' . $fileSk))) {
            return redirect()->route('kajur.pembagian-dosen-visitasi.index')
                ->withErrors('File SK tidak ditemukan di storage.');
        }

        if (request()->query('from') === 'monitoring') {
            $backUrl = route('kajur.monitoring.visitasi');
        } else {
            $backUrl = route('kajur.pembagian-dosen-visitasi.index');
        }

        return view('pages.baup.dosen_visitasi.lihat_sk_visitasi', compact(
            'prodi', 'fileSk', 'backUrl'
        ));
    }

    // ==================== DOSEN VISITASI (ROLE) ====================

    public function indexDosenVisitasi()
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $visitasiList = DosenVisitasi::with(['mahasiswa', 'perusahaan', 'dosenPembimbing.pengajuanMagang'])
            ->where('id_dosen', $dosenLogin->id_dosen)
            // ->where('status', 'selesai')
            // ->whereNotNull('file_sk')
            ->orderBy('id_perusahaan')
            ->orderBy('id_dosen_visitasi', 'desc')
            ->get();

        $groupedByPerusahaan = $visitasiList->groupBy('id_perusahaan');

        $perusahaanIds = $visitasiList->pluck('id_perusahaan')->unique()->toArray();
        $visitasiIds = $visitasiList->pluck('id_dosen_visitasi')->unique()->toArray();

        $interviewA = InterviewVisitasiMagangA::whereIn('id_perusahaan', $perusahaanIds)
            ->whereIn('id_dosen_visitasi', $visitasiIds)
            ->get()
            ->keyBy('id_perusahaan');

        $interviewB = InterviewVisitasiMagangB::whereIn('id_perusahaan', $perusahaanIds)
            ->where('id_dosen', $dosenLogin->id_dosen)
            ->get()
            ->keyBy('id_perusahaan');

        return view('pages.dosen_visitasi.perusahaan_visitasi.index', compact('visitasiList', 'dosenLogin', 'groupedByPerusahaan', 'interviewA', 'interviewB'));
    }


    public function lihatSkDosenVisitasi($id_dosen)
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin || $dosenLogin->id_dosen != $id_dosen) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $visitasiList = DosenVisitasi::where('id_dosen', $id_dosen)
            ->where('status', 'selesai')
            ->whereNotNull('file_sk')
            ->first();

        if (!$visitasiList) {
            return redirect()->route('beranda-dosen-visitasi')
                ->withErrors('SK Dosen Visitasi belum tersedia.');
        }

        $prodi = Prodi::where('id_prodi', $dosenLogin->id_prodi)->first();
        $fileSk = $visitasiList->file_sk;

        if (!$fileSk || !file_exists(storage_path('app/public/sk_visitasi/' . $fileSk))) {
            return redirect()->route('beranda-dosen-visitasi')
                ->withErrors('File SK tidak ditemukan di storage.');
        }

        $backUrl = route('dosen-visitasi.mahasiswa-visitasi');

        return view('pages.baup.dosen_visitasi.lihat_sk_visitasi', compact(
            'prodi', 'fileSk', 'backUrl'
        ));
    }

    public function detailPerusahaanVisitasi($id_dosen_visitasi)
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $visitasi = DosenVisitasi::with(['mahasiswa', 'perusahaan', 'dosenPembimbing.pengajuanMagang', 'dosenPembimbing.dosen'])
            ->where('id_dosen', $dosenLogin->id_dosen)
            ->findOrFail($id_dosen_visitasi);

        return view('pages.dosen_visitasi.perusahaan_detail', compact('visitasi', 'dosenLogin'));
    }







    // ==================== ADMIN ====================

    public function indexAdmin()
    {
        $dosenVisitasi = DosenVisitasi::with(['dosen', 'mahasiswa', 'perusahaan'])
            ->orderBy('id_dosen_visitasi', 'desc')
            ->get();

        return view('pages.admin.dosen_visitasi.index', compact('dosenVisitasi'));
    }


    public function indexPersonalInterviewAdmin()
    {
        $visitasiList = DosenVisitasi::with(['dosen', 'mahasiswa.prodi', 'perusahaan', 'dosenPembimbing.dosen'])
            // ->where('status', 'selesai')
            // ->whereNotNull('file_sk')
            ->orderBy('id_perusahaan')
            ->orderBy('id_dosen')
            ->orderBy('id_dosen_visitasi', 'desc')
            ->get();

        $visitasiIds = $visitasiList->pluck('id_dosen_visitasi')->toArray();

        $interviewAList = InterviewVisitasiMagangA::whereIn('id_dosen_visitasi', $visitasiIds)
            ->orderBy('id_interview_magang_a', 'desc')
            ->get()
            ->keyBy('id_dosen_visitasi');

        $interviewBList = InterviewVisitasiMagangB::whereIn('id_dosen_visitasi', $visitasiIds)
            ->orderBy('id_interview_magang_b', 'desc')
            ->get()
            ->keyBy('id_dosen_visitasi');

        $groupedByPerusahaan = $visitasiList->groupBy('id_perusahaan');

        return view('pages.admin.personal_interview.index', compact(
            'visitasiList', 'groupedByPerusahaan', 'interviewAList', 'interviewBList'
        ));
    }

    public function detailPersonalInterviewAdmin($id_perusahaan)
    {
        $visitasiList = DosenVisitasi::with(['dosen', 'mahasiswa.prodi', 'perusahaan', 'dosenPembimbing.dosen'])
            ->where('id_perusahaan', $id_perusahaan)
            // ->where('status', 'selesai')
            // ->whereNotNull('file_sk')
            ->get();

        if ($visitasiList->isEmpty()) {
            abort(404, 'Data visitasi tidak ditemukan.');
        }

        $perusahaan = $visitasiList->first()->perusahaan;
        $visitasiIds = $visitasiList->pluck('id_dosen_visitasi')->toArray();

        $interviewA = InterviewVisitasiMagangA::whereIn('id_dosen_visitasi', $visitasiIds)
            ->where('id_perusahaan', $id_perusahaan)
            ->orderBy('id_interview_magang_a', 'desc')
            ->first();

        $interviewB = InterviewVisitasiMagangB::whereIn('id_dosen_visitasi', $visitasiIds)
            ->where('id_perusahaan', $id_perusahaan)
            ->orderBy('id_interview_magang_b', 'desc')
            ->first();

        $dosenPengisi = $interviewB ? Dosen::find($interviewB->id_dosen) : null;
        $ttdUrl = ($dosenPengisi && $dosenPengisi->ttd_digital)
            ? asset('storage/ttd_digital/' . $dosenPengisi->ttd_digital)
            : null;

        return view('pages.admin.personal_interview.detail', compact(
            'visitasiList', 'perusahaan', 'interviewA', 'interviewB', 'dosenPengisi', 'ttdUrl'
        ));
    }

public function monitoringVisitasiKajur(Request $request)
{
    $idUser = auth()->user()->id_user;
    $dosenLogin = Dosen::where('id_user', $idUser)->first();

    if (!$dosenLogin) {
        abort(403, 'Data dosen tidak ditemukan.');
    }

    $idJurusanKajur = Prodi::find($dosenLogin->id_prodi)?->id_jurusan;

    if (!$idJurusanKajur) {
        abort(403, 'Jurusan tidak ditemukan.');
    }

    // Ambil semua ID prodi di bawah jurusan Kajur ini
    $prodiIds = Prodi::where('id_jurusan', $idJurusanKajur)->pluck('id_prodi')->toArray();

    $search       = $request->input('search');
    $filterTahun  = $request->input('filter_tahun');
    $filterKelas  = $request->input('filter_kelas');
    $perPage      = $request->input('per_page', 10);

    // Daftar tahun angkatan unik dari mahasiswa di jurusan ini
    $daftarTahun = Mahasiswa::whereNotNull('angkatan')
        ->whereIn('id_prodi', $prodiIds)
        ->distinct()
        ->orderBy('angkatan', 'desc')
        ->pluck('angkatan')
        ->toArray();

    // Daftar kelas unik
    $daftarKelas = Mahasiswa::whereNotNull('kelas')
        ->whereIn('id_prodi', $prodiIds)
        ->whereIn('kelas', ['A', 'B', 'C', 'D'])
        ->distinct()
        ->orderBy('kelas', 'asc')
        ->pluck('kelas')
        ->toArray();

    // ✅ QUERY UTAMA - TANPA filter status (karena kolom status tidak ada)
    $query = DosenVisitasi::with(['dosen', 'mahasiswa', 'perusahaan'])
        ->whereHas('dosen', function ($q) use ($prodiIds) {
            $q->whereIn('id_prodi', $prodiIds);
        })
        ->whereHas('mahasiswa', function ($q) use ($prodiIds) {
            $q->whereIn('id_prodi', $prodiIds);
        });

    // Filter tahun angkatan
    if ($filterTahun) {
        $query->whereHas('mahasiswa', function ($q) use ($filterTahun) {
            $q->where('angkatan', $filterTahun);
        });
    }

    // Filter kelas
    if ($filterKelas) {
        $query->whereHas('mahasiswa', function ($q) use ($filterKelas) {
            $q->where('kelas', $filterKelas);
        });
    }

    // Search
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
            ->orWhereHas('perusahaan', function ($q2) use ($search) {
                $q2->where('nama_perusahaan', 'like', '%' . $search . '%');
            });
        });
    }

    $visitasiList = $query->orderBy('id_dosen', 'asc')
                         ->orderBy('id_perusahaan', 'asc')
                         ->paginate($perPage)
                         ->withQueryString();

    return view('pages.kajur.dosen_visitasi.index', 
        compact('visitasiList', 'search', 'perPage', 'daftarTahun', 'filterTahun', 'daftarKelas', 'filterKelas'));
}
public function indexMahasiswaVisitasi(Request $request)
{
    $idUser = auth()->user()->id_user;
    $mahasiswaLogin = Mahasiswa::where('id_user', $idUser)->first();

    if (!$mahasiswaLogin) {
        abort(403, 'Data mahasiswa tidak ditemukan.');
    }

    $idProdi = $mahasiswaLogin->id_prodi;

    $search       = $request->input('search');
    $filterTahun  = $request->input('filter_tahun');
    $filterKelas  = $request->input('filter_kelas');
    $perPage      = $request->input('per_page', 10);

    // Daftar tahun angkatan unik di prodi ini
    $daftarTahun = Mahasiswa::whereNotNull('angkatan')
        ->where('id_prodi', $idProdi)
        ->distinct()
        ->orderBy('angkatan', 'desc')
        ->pluck('angkatan')
        ->toArray();

    // Daftar kelas unik di prodi ini
    $daftarKelas = Mahasiswa::whereNotNull('kelas')
        ->where('id_prodi', $idProdi)
        ->whereIn('kelas', ['A', 'B', 'C', 'D'])
        ->distinct()
        ->orderBy('kelas', 'asc')
        ->pluck('kelas')
        ->toArray();

    // ✅ QUERY UTAMA - TANPA filter status
    $query = DosenVisitasi::with(['dosen', 'mahasiswa', 'perusahaan'])
        ->whereHas('dosen', function ($q) use ($idProdi) {
            $q->where('id_prodi', $idProdi);
        })
        ->whereHas('mahasiswa', function ($q) use ($idProdi) {
            $q->where('id_prodi', $idProdi);
        });

    // Filter tahun angkatan
    if ($filterTahun) {
        $query->whereHas('mahasiswa', function ($q) use ($filterTahun) {
            $q->where('angkatan', $filterTahun);
        });
    }

    // Filter kelas
    if ($filterKelas) {
        $query->whereHas('mahasiswa', function ($q) use ($filterKelas) {
            $q->where('kelas', $filterKelas);
        });
    }

    // Search
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
            ->orWhereHas('perusahaan', function ($q2) use ($search) {
                $q2->where('nama_perusahaan', 'like', '%' . $search . '%');
            });
        });
    }

    $visitasiList = $query->orderBy('id_dosen', 'asc')
                         ->orderBy('id_perusahaan', 'asc')
                         ->paginate($perPage)
                         ->withQueryString();

    return view('pages.kajur.dosen_visitasi.index', 
        compact('visitasiList', 'search', 'perPage', 'daftarTahun', 'filterTahun', 'daftarKelas', 'filterKelas'));
}
public function exportKaprodi(Request $request)
{
    $idUser = auth()->user()->id_user;
    $dosenLogin = Dosen::where('id_user', $idUser)->first();

    if (!$dosenLogin) {
        abort(403, 'Data dosen tidak ditemukan.');
    }

    $idProdiKaprodi = $dosenLogin->id_prodi;

     $search                = $request->input('search');
        $filterTahun          = $request->input('filter_tahun'); // Angkatan
        $filterKelas          = $request->input('filter_kelas');
        $filterTahunVisitasi  = $request->input('filter_tahun_visitasi'); 

$query = DosenVisitasi::with(['dosen', 'mahasiswa', 'perusahaan'])
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

        // ✅ FILTER TAHUN VISITASI (SINKRON DENGAN VIEW)
        if ($filterTahunVisitasi) {
            $query->where('tahun', $filterTahunVisitasi);
        }

  // ✅ FILTER KELAS
        if ($filterKelas) {
            $query->whereHas('mahasiswa', function ($q) use ($filterKelas) {
                $q->where('kelas', $filterKelas);
            });
        }
        
        // ✅ SEARCH
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
                ->orWhereHas('perusahaan', function ($q2) use ($search) {
                    $q2->where('nama_perusahaan', 'like', '%' . $search . '%');
                });
            });
        }
    $data = $query->orderBy('id_dosen', 'asc')->get();
         $visitasiIds = $data->pluck('id_dosen_visitasi')->toArray();

    // Jika kosong, kembalikan dengan pesan error
    if ($data->isEmpty()) {
        return redirect()->route('kaprodi.pembagian-dosen-visitasi.index')
            ->with('error', 'Tidak ada data untuk diekspor.');
    }

    // ✅ TAMBAHAN: Ambil data interview untuk cek status visitasi
    $visitasiIds = $data->pluck('id_dosen_visitasi')->toArray();

    $hasInterviewA = InterviewVisitasiMagangA::whereIn('id_dosen_visitasi', $visitasiIds)
        ->pluck('id_dosen_visitasi')
        ->unique()
        ->flip();

    $hasInterviewB = InterviewVisitasiMagangB::whereIn('id_dosen_visitasi', $visitasiIds)
        ->pluck('id_dosen_visitasi')
        ->unique()
        ->flip();

    // ✅ Buat nama file dinamis berdasarkan filter
    $fileName = 'Pembagian_Dosen_Visitasi';
    if ($request->filter_tahun) {
        $fileName .= '_Tahun_' . $request->filter_tahun;
    }
    if ($request->filter_kelas) {
        $fileName .= '_Kelas_' . $request->filter_kelas;
    }
    $fileName .= '_' . date('d-m-Y') . '.xlsx';

    // ✅ DIUBAH: Kirim juga $hasInterviewA dan $hasInterviewB
    return \Maatwebsite\Excel\Facades\Excel::download(
        new \App\Exports\DosenVisitasiExport($data, $hasInterviewA, $hasInterviewB), 
        $fileName
    );
}
    public function detailInterviewKaprodi($id_perusahaan)
    {
        // 1. Ambil Data Perusahaan
        $perusahaan = \App\Models\Perusahaan::find($id_perusahaan);
        if (!$perusahaan) {
            abort(404, 'Perusahaan tidak ditemukan.');
        }

        // 2. Ambil Data Login Kaprodi (untuk filter prodi)
        $idUser = auth()->user()->id_user;
        $dosenLogin = \App\Models\Dosen::where('id_user', $idUser)->first();
        $idProdi = $dosenLogin->id_prodi;

        // 3. Cari Data Visitasi yang sesuai dengan Perusahaan ini dan Prodi Kaprodi
        $visitasi = \App\Models\DosenVisitasi::with(['dosen', 'mahasiswa'])
            ->where('id_perusahaan', $id_perusahaan)
            ->whereHas('dosen', function($q) use ($idProdi) {
                $q->where('id_prodi', $idProdi);
            })
            ->first();

        // 4. Ambil Data Interview (Form A & B) jika visitasi ada
        $interviewA = null;
        $interviewB = null;

        if ($visitasi) {
            $interviewA = \App\Models\InterviewVisitasiMagangA::where('id_dosen_visitasi', $visitasi->id_dosen_visitasi)->first();
            $interviewB = \App\Models\InterviewVisitasiMagangB::where('id_dosen_visitasi', $visitasi->id_dosen_visitasi)->first();
        }

        // 5. Data Dosen untuk TTD (Gunakan dosen visitasi jika ada, atau dosen login)
        $dosenPengisi = $visitasi ? $visitasi->dosen : $dosenLogin;
        
        // URL TTD Digital
        $ttdUrl = null;
        if ($dosenPengisi && $dosenPengisi->ttd_digital) {
            $ttdUrl = asset('storage/ttd_digital/' . $dosenPengisi->ttd_digital);
        }

        // URL Back
        $backUrl = route('kaprodi.pembagian-dosen-visitasi.index');

        return view('pages.dosen_visitasi.perusahaan_visitasi.detail_interview', compact(
            'perusahaan', 
            'visitasi', 
            'interviewA', 
            'interviewB', 
            'dosenPengisi', 
            'ttdUrl',
            'backUrl'
        ));
    }

    // ==================== INTERVIEW VISITASI (DOSEN) ====================

    public function interviewStoreA(Request $request, $id_perusahaan)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'penerima'              => 'required|string',
            'jabatan_penerima'      => 'required|string',
            'sikap_perilaku'        => 'required|string',
            'kedisiplinan_motivasi' => 'required|string',
            'kekurangan_peserta'    => 'required|string',
            'peran_peserta'         => 'required|string',
            'adaptasi_komunikasi'   => 'required|string',
            'kompetensi_kesesuaian' => 'required|string',
            'penempatan_dan_tugas'  => 'required|string',
            'kepuasan_perusahaan'   => 'required|string',
            'saran_masukan'         => 'required|string',
        ]);

        // 2. Ambil id_dosen_visitasi dari Session (Penting!)
        // Ini dibutuhkan agar tahu data visitasi mana yang sedang diisi
        $idDosenVisitasi = session()->get('active_visitasi_id');

        // 3. ✅ LOGIKA BARU: Ambil id_perusahaan dari tabel dosen_visitasi
        // Kita cari data dosen_visitasi berdasarkan ID visitasi yang aktif
        $visitasiData = DosenVisitasi::find($idDosenVisitasi);

        $dataToSave = $validated;
        $dataToSave['id_dosen_visitasi'] = $idDosenVisitasi;

        if ($visitasiData) {
            // Jika data dosen_visitasi ditemukan, ambil id_perusahaan darisana
            // Ini menjamin data konsisten sesuai tabel dosen_visitasi
            $dataToSave['id_perusahaan'] = $visitasiData->id_perusahaan;
        } else {
            // Fallback jika data visitasi tidak ditemukan (ambil dari URL)
            $dataToSave['id_perusahaan'] = $id_perusahaan;
        }

        // 4. Simpan ke Database
        InterviewVisitasiMagangA::create($dataToSave);

        // 5. Redirect ke Step 2
        return redirect()
            ->route('dosen-visitasi.interview-form-b', $id_perusahaan)
            ->with('success', 'Data Form 1 berhasil disimpan.');
    }

    public function interviewStoreB(Request $request, $id_perusahaan)
    {
        $validated = $request->validate([
            'skala_perusahaan'       => 'required|string',
            'bidang_usaha'           => 'required|string',
            'lingkungan_kerja'       => 'required|string',
            'sistem_pembimbingan'    => 'required|string',
            'kesesuaian_kurikulum'   => 'required|string',
            'penerapan_k3'           => 'required|string',
            'fasilitas_magang'       => 'required|string',
            'tanggapan_perusahaan'   => 'required|string',
            'kelebihan_perusahaan'   => 'required|string',
            'kekurangan_perusahaan'  => 'required|string',
            'rekomendasi'            => 'required|string',
            'catatan'                => 'nullable|string',
        ]);

        $idDosenVisitasi = session()->get('active_visitasi_id');
        $visitasiData = DosenVisitasi::find($idDosenVisitasi);

        $dataToSave = $validated;
        $dataToSave['id_dosen_visitasi'] = $idDosenVisitasi;

        if ($visitasiData) {
            $dataToSave['id_perusahaan'] = $visitasiData->id_perusahaan;
        } else {
            $dataToSave['id_perusahaan'] = $id_perusahaan;
        }

        InterviewVisitasiMagangB::create($dataToSave);

        return redirect()
            ->route('dosen-visitasi.interview-form-photo', $id_perusahaan)
            ->with('success', 'Data Form 2 berhasil disimpan.');
    }

public function interviewStorePhoto(Request $request, $idPerusahaan)
{
    $request->validate([
        'foto_visitasi'   => 'required|image|max:5120',
        'tgl_visitasi'    => 'required|date',
        'keterangan_foto' => 'nullable|string',
        'persetujuan'     => 'required',
    ]);

    $idUser = auth()->user()->id_user;
    $dosenLogin = Dosen::where('id_user', $idUser)->first();

    // ✅ AMBIL SEMUA visitasi di perusahaan ini milik dosen yang login
    $allVisitasi = DosenVisitasi::where('id_perusahaan', $idPerusahaan)
        ->where('id_dosen', $dosenLogin->id_dosen)
        ->get();

    // Upload foto
    $file = $request->file('foto_visitasi');
    $namaFoto = time() . '_foto_visitasi_1.' . $file->getClientOriginalExtension();
    $file->storeAs('foto_visitasi', $namaFoto, 'public');

    // ✅ Update SEMUA visitasi di perusahaan ini
    foreach ($allVisitasi as $visitasi) {
        // Hapus foto lama jika ada
        if ($visitasi->foto && file_exists(storage_path('app/public/foto_visitasi/' . $visitasi->foto))) {
            unlink(storage_path('app/public/foto_visitasi/' . $visitasi->foto));
        }

        $visitasi->update([
            'foto'         => $namaFoto,
            'tgl_visitasi' => $request->tgl_visitasi,
            'catatan'      => $request->keterangan_foto,
        ]);
    }

    // ✅ GANTI ROUTE INI SESUAI NAMA ROUTE YANG BENAR
   return redirect()->route('dosen-visitasi.personal-interview')
    ->with('success', 'Interview visitasi berhasil disimpan! Data visitasi untuk perusahaan telah lengkap.');
}
    // ==================== DOSEN VISITASI - PERSONAL INTERVIEW ====================
    // ==================== DOSEN VISITASI - PERSONAL INTERVIEW ====================

    public function personalInterview()
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $visitasiList = DosenVisitasi::with(['perusahaan', 'mahasiswa'])
            ->where('id_dosen', $dosenLogin->id_dosen)
            ->get();

        $groupedByPerusahaan = $visitasiList->groupBy('id_perusahaan');

        $interviewComplete = [];
        foreach ($groupedByPerusahaan as $idPerusahaan => $items) {
            $firstItem = $items->first();
            $hasA = InterviewVisitasiMagangA::where('id_dosen_visitasi', $firstItem->id_dosen_visitasi)->exists();
            $hasB = InterviewVisitasiMagangB::where('id_dosen_visitasi', $firstItem->id_dosen_visitasi)->exists();
            $interviewComplete[$idPerusahaan] = ($hasA && $hasB);
        }

        return view('pages.dosen_visitasi.perusahaan_visitasi.index', compact(
            'dosenLogin', 'groupedByPerusahaan', 'interviewComplete'
        ));
    }

    public function formInterviewA($id_perusahaan)
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $perusahaan = \App\Models\Perusahaan::findOrFail($id_perusahaan);

        $visitasi = DosenVisitasi::where('id_dosen', $dosenLogin->id_dosen)
            ->where('id_perusahaan', $id_perusahaan)
            ->first();

        if (!$visitasi) {
            return redirect()->route('dosen-visitasi.personal-interview')
                ->withErrors('Perusahaan ini bukan tugasan visitasi Anda.');
        }

        $existingA = InterviewVisitasiMagangA::where('id_dosen_visitasi', $visitasi->id_dosen_visitasi)->first();

        return view('pages.dosen_visitasi.perusahaan_visitasi.form_interview', compact(
            'dosenLogin', 'perusahaan', 'existingA'
        ));
    }

    public function storeInterviewA(Request $request, $id_perusahaan)
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $request->validate([
            'penerima'              => 'required|string|max:255',
            'jabatan_penerima'      => 'required|string|max:255',
            'sikap_perilaku'        => 'required|string',
            'kedisiplinan_motivasi' => 'required|string',
            'kekurangan_peserta'    => 'required|string',
            'peran_peserta'         => 'required|string',
            'adaptasi_komunikasi'   => 'required|string',
            'kompetensi_kesesuaian' => 'required|string',
            'penempatan_dan_tugas'  => 'required|string',
            'kepuasan_perusahaan'   => 'required|string',
            'saran_masukan'         => 'required|string',
        ]);

        $visitasi = DosenVisitasi::where('id_dosen', $dosenLogin->id_dosen)
            ->where('id_perusahaan', $id_perusahaan)
            ->first();

        if (!$visitasi) {
            return redirect()->route('dosen-visitasi.personal-interview')
                ->withErrors('Perusahaan ini bukan tugasan visitasi Anda.');
        }

        InterviewVisitasiMagangA::updateOrCreate(
            ['id_dosen_visitasi' => $visitasi->id_dosen_visitasi],
            [
                'penerima'              => $request->penerima,
                'jabatan_penerima'      => $request->jabatan_penerima,
                'sikap_perilaku'        => $request->sikap_perilaku,
                'kedisiplinan_motivasi' => $request->kedisiplinan_motivasi,
                'kekurangan_peserta'    => $request->kekurangan_peserta,
                'peran_peserta'         => $request->peran_peserta,
                'adaptasi_komunikasi'   => $request->adaptasi_komunikasi,
                'kompetensi_kesesuaian' => $request->kompetensi_kesesuaian,
                'penempatan_dan_tugas'  => $request->penempatan_dan_tugas,
                'kepuasan_perusahaan'   => $request->kepuasan_perusahaan,
                'saran_masukan'         => $request->saran_masukan,
            ]
        );

        return redirect()->route('dosen-visitasi.interview-form-b', $id_perusahaan)
            ->with('success', 'Form 1 berhasil disimpan. Silakan lanjutkan ke Form 2.');
    }

    public function formInterviewB($id_perusahaan)
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $perusahaan = \App\Models\Perusahaan::findOrFail($id_perusahaan);

        $visitasi = DosenVisitasi::where('id_dosen', $dosenLogin->id_dosen)
            ->where('id_perusahaan', $id_perusahaan)
            ->first();

        if (!$visitasi) {
            return redirect()->route('dosen-visitasi.personal-interview')
                ->withErrors('Perusahaan ini bukan tugasan visitasi Anda.');
        }

        $existingA = InterviewVisitasiMagangA::where('id_dosen_visitasi', $visitasi->id_dosen_visitasi)->first();
        if (!$existingA) {
            return redirect()->route('dosen-visitasi.interview-form-a', $id_perusahaan)
                ->withErrors('Silakan isi Form 1 terlebih dahulu.');
        }

        $existingB = InterviewVisitasiMagangB::where('id_dosen_visitasi', $visitasi->id_dosen_visitasi)->first();

        return view('pages.dosen_visitasi.perusahaan_visitasi.form_interview', compact(
            'dosenLogin', 'perusahaan', 'existingA', 'existingB'
        ));
    }

    public function storeInterviewB(Request $request, $id_perusahaan)
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $request->validate([
            'skala_perusahaan'      => 'required|string',
            'bidang_usaha'          => 'required|string',
            'lingkungan_kerja'      => 'required|string',
            'sistem_pembimbingan'   => 'required|string',
            'kesesuaian_kurikulum'  => 'required|string',
            'penerapan_k3'          => 'required|string',
            'fasilitas_magang'      => 'required|string',
            'tanggapan_perusahaan'  => 'required|string',
            'kelebihan_perusahaan'  => 'required|string',
            'kekurangan_perusahaan' => 'required|string',
            'rekomendasi'           => 'required|string',
            'catatan'               => 'nullable|string',
        ]);

        $visitasi = DosenVisitasi::where('id_dosen', $dosenLogin->id_dosen)
            ->where('id_perusahaan', $id_perusahaan)
            ->first();

        if (!$visitasi) {
            return redirect()->route('dosen-visitasi.personal-interview')
                ->withErrors('Perusahaan ini bukan tugasan visitasi Anda.');
        }

        $existingA = InterviewVisitasiMagangA::where('id_dosen_visitasi', $visitasi->id_dosen_visitasi)->first();
        if (!$existingA) {
            return redirect()->route('dosen-visitasi.interview-form-a', $id_perusahaan)
                ->withErrors('Silakan isi Form 1 terlebih dahulu.');
        }

        InterviewVisitasiMagangB::updateOrCreate(
            ['id_dosen_visitasi' => $visitasi->id_dosen_visitasi],
            [
                'skala_perusahaan'      => $request->skala_perusahaan,
                'bidang_usaha'          => $request->bidang_usaha,
                'lingkungan_kerja'      => $request->lingkungan_kerja,
                'sistem_pembimbingan'   => $request->sistem_pembimbingan,
                'kesesuaian_kurikulum'  => $request->kesesuaian_kurikulum,
                'penerapan_k3'          => $request->penerapan_k3,
                'fasilitas_magang'      => $request->fasilitas_magang,
                'tanggapan_perusahaan'  => $request->tanggapan_perusahaan,
                'kelebihan_perusahaan'  => $request->kelebihan_perusahaan,
                'kekurangan_perusahaan' => $request->kekurangan_perusahaan,
                'rekomendasi'           => $request->rekomendasi,
                'catatan'               => $request->catatan,
            ]
        );

        return redirect()->route('dosen-visitasi.personal-interview')
            ->with('success', 'Personal Interview untuk ' . $visitasi->perusahaan->nama_perusahaan . ' berhasil disimpan!');
    }

    public function detailInterview($id_perusahaan)
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $perusahaan = \App\Models\Perusahaan::findOrFail($id_perusahaan);

        $visitasi = DosenVisitasi::where('id_dosen', $dosenLogin->id_dosen)
            ->where('id_perusahaan', $id_perusahaan)
            ->first();

        if (!$visitasi) {
            abort(404, 'Data visitasi tidak ditemukan.');
        }

        $interviewA = InterviewVisitasiMagangA::where('id_dosen_visitasi', $visitasi->id_dosen_visitasi)->first();
        $interviewB = InterviewVisitasiMagangB::where('id_dosen_visitasi', $visitasi->id_dosen_visitasi)->first();

        $dosenPengisi = $dosenLogin;
        $ttdUrl = null;
        if ($dosenPengisi->ttd_digital) {
            $ttdUrl = asset('storage/ttd_digital/' . $dosenPengisi->ttd_digital);
        }

        return view('pages.dosen_visitasi.perusahaan_visitasi.detail_interview', compact(
            'perusahaan', 'visitasi', 'interviewA', 'interviewB', 'dosenPengisi', 'ttdUrl'
        ));
    }
}