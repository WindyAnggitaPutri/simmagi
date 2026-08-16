<?php

namespace App\Http\Controllers;

use App\Models\TokenPerusahaan;
use App\Models\Perusahaan;
use App\Models\AngketPerusahaan;
use App\Models\Prodi;   
use App\Models\PenilaianMahasiswa;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DB;
use Illuminate\Support\Facades\Log;

class KaprodiTokenController extends Controller
{
    private function getKaprodiProdi()
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();

        if (!$dosenLogin) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        return $dosenLogin->id_prodi;
    }

    public function index(Request $request)
    {
        $idProdiKaprodi = $this->getKaprodiProdi();

        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');

        if (!in_array($perPage, [5, 10, 15, 'all'])) {
            $perPage = 10;
        }

        $query = TokenPerusahaan::where('id_prodi', $idProdiKaprodi);

        if (!empty($search)) {
            $query->whereHas('perusahaan', function ($q) use ($search) {
                $q->where('nama_perusahaan', 'LIKE', '%' . $search . '%');
            });
        }

        $allPerusahaanIds = (clone $query)
            ->select('id_perusahaan', DB::raw('MAX(created_at) as latest'))
            ->groupBy('id_perusahaan')
            ->orderByDesc('latest')
            ->pluck('id_perusahaan')
            ->values();

        $total = $allPerusahaanIds->count();
        $page = $request->get('page', 1);
        $realPerPage = ($perPage === 'all') ? max($total, 1) : (int) $perPage;

        $currentPageIds = $allPerusahaanIds->forPage($page, $realPerPage)->values();

        $paginatedIds = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageIds,
            $total,
            $realPerPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $tokens = TokenPerusahaan::with(['perusahaan', 'mahasiswa.prodi', 'prodi'])
                    ->where('id_prodi', $idProdiKaprodi)
                    ->whereIn('id_perusahaan', $currentPageIds)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->groupBy('id_perusahaan');

        $sortedTokens = collect();
        foreach ($currentPageIds as $id) {
            if ($tokens->has($id)) {
                $sortedTokens->put($id, $tokens->get($id));
            }
        }
        $tokens = $sortedTokens;

        return view('pages.kaprodi.token_perusahaan.index', compact('tokens', 'paginatedIds', 'perPage', 'search'));
    }

    // ✅ PERBAIKAN METHOD CREATE
    public function create()
    {
        $idUser = auth()->user()->id_user;
        $dosenLogin = Dosen::where('id_user', $idUser)->first();
        $idProdiKaprodi = $dosenLogin->id_prodi;

        $ttdKaprodi = null;
        if ($dosenLogin && $dosenLogin->ttd_digital) {
            $path = storage_path('app/public/ttd_digital/' . $dosenLogin->ttd_digital);
            if (file_exists($path)) {
                $ttdKaprodi = base64_encode(file_get_contents($path));
            }
        }

        // ✅ AMBIL SEMUA PERUSAHAAN YANG PUNYA MAHASISWA MAGANG AKTIF
        // Tanpa filter token - biarkan user yang menentukan
        $perusahaans = Perusahaan::select('perusahaan.*')
            ->join('pengajuan_magang as pmg', 'perusahaan.id_perusahaan', '=', 'pmg.id_perusahaan')
            ->join('pengajuan_mahasiswa as pm', 'pmg.id_pengajuan', '=', 'pm.id_pengajuan')
            ->join('mahasiswa', 'pm.id_mahasiswa', '=', 'mahasiswa.id_mahasiswa')
            ->where('mahasiswa.id_prodi', $idProdiKaprodi)
            ->groupBy('perusahaan.id_perusahaan', 'perusahaan.nama_perusahaan')
            ->orderBy('perusahaan.nama_perusahaan')
            ->get();

        // ✅ OPTIONAL: Tambahkan info jumlah token yang sudah ada
        $perusahaans->each(function ($p) use ($idProdiKaprodi) {
            $p->token_count = TokenPerusahaan::where('id_perusahaan', $p->id_perusahaan)
                ->where('id_prodi', $idProdiKaprodi)
                ->where('is_used', false)
                ->where('expired_at', '>', now())
                ->count();
        });

        // ✅ DEBUG LOG - Hapus setelah fix
        Log::info('Token Create - Prodi: ' . $idProdiKaprodi);
        Log::info('Token Create - Jumlah Perusahaan: ' . $perusahaans->count());
        foreach ($perusahaans as $p) {
            Log::info("  - {$p->nama_perusahaan} (tokens aktif: {$p->token_count})");
        }

        return view('pages.kaprodi.token_perusahaan.create', compact('perusahaans', 'ttdKaprodi'));
    }

    public function getMahasiswaByPerusahaan($id_perusahaan)
    {
        $idProdiKaprodi = $this->getKaprodiProdi();

        $mahasiswas = Mahasiswa::select('mahasiswa.id_mahasiswa', 'mahasiswa.nama', 'mahasiswa.npm')
            ->join('pengajuan_mahasiswa as pm', 'mahasiswa.id_mahasiswa', '=', 'pm.id_mahasiswa')
            ->join('pengajuan_magang as pmg', 'pm.id_pengajuan', '=', 'pmg.id_pengajuan')
            ->where('pmg.id_perusahaan', $id_perusahaan)
            ->where('mahasiswa.id_prodi', $idProdiKaprodi)
            ->groupBy('mahasiswa.id_mahasiswa', 'mahasiswa.nama', 'mahasiswa.npm')
            ->orderBy('mahasiswa.nama')
            ->get();

        // ✅ DEBUG LOG
        Log::info("getMahasiswa - Perusahaan: {$id_perusahaan}, Jumlah: " . $mahasiswas->count());

        return response()->json($mahasiswas);
    }

    // ✅ PERBAIKAN METHOD STORE - Dengan debug yang lebih baik
    public function store(Request $request)
    {
        $request->validate([
            'id_perusahaan' => 'required|exists:perusahaan,id_perusahaan',
            'expired_at' => 'required|date|after:now',
        ]);

        $idProdiKaprodi = $this->getKaprodiProdi();
        $id_perusahaan = $request->id_perusahaan;

        // ✅ DEBUG: Cek akses
        $hasAccess = Mahasiswa::join('pengajuan_mahasiswa as pm', 'mahasiswa.id_mahasiswa', '=', 'pm.id_mahasiswa')
            ->join('pengajuan_magang as pmg', 'pm.id_pengajuan', '=', 'pmg.id_pengajuan')
            ->where('pmg.id_perusahaan', $id_perusahaan)
            ->where('mahasiswa.id_prodi', $idProdiKaprodi)
            ->exists();

        Log::info("Store Token - Perusahaan: {$id_perusahaan}, Prodi: {$idProdiKaprodi}, HasAccess: " . ($hasAccess ? 'YES' : 'NO'));

        if (!$hasAccess) {
            return redirect()->back()
                ->with('error', 'Tidak ada mahasiswa prodi Anda di perusahaan ini.')
                ->withInput();
        }

        // ✅ AMBIL MAHASISWA DAHULU UNTUK DI CEK
        $mahasiswas = Mahasiswa::select('mahasiswa.id_mahasiswa', 'mahasiswa.nama')
            ->join('pengajuan_mahasiswa as pm', 'mahasiswa.id_mahasiswa', '=', 'pm.id_mahasiswa')
            ->join('pengajuan_magang as pmg', 'pm.id_pengajuan', '=', 'pmg.id_pengajuan')
            ->where('pmg.id_perusahaan', $id_perusahaan)
            ->where('mahasiswa.id_prodi', $idProdiKaprodi)
            ->groupBy('mahasiswa.id_mahasiswa', 'mahasiswa.nama')
            ->get();

        Log::info("Store Token - Jumlah Mahasiswa ditemukan: " . $mahasiswas->count());
        foreach ($mahasiswas as $m) {
            Log::info("  - {$m->nama} (ID: {$m->id_mahasiswa})");
        }

        if ($mahasiswas->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Tidak ada mahasiswa prodi Anda yang terdaftar magang di perusahaan ini.')
                ->withInput();
        }

        $tokensCreated = [];
        $tokensSkipped = [];

        DB::transaction(function () use ($id_perusahaan, $request, $idProdiKaprodi, $mahasiswas, &$tokensCreated, &$tokensSkipped) {
            
            // 1. Buat Token Angket
            $existingAngket = TokenPerusahaan::where('id_perusahaan', $id_perusahaan)
                ->where('id_prodi', $idProdiKaprodi)
                ->where('type', 'angket')
                ->where('is_used', false)
                ->where('expired_at', '>', now())
                ->exists();

            if (!$existingAngket) {
                $tokenAngket = TokenPerusahaan::create([
                    'id_perusahaan' => $id_perusahaan,
                    'id_prodi' => $idProdiKaprodi,
                    'type' => 'angket',
                    'id_mahasiswa' => null,
                    'token' => Str::upper(Str::random(10)),
                    'expired_at' => $request->expired_at,
                    'is_used' => false,
                    'created_at' => now(),
                ]);
                $tokensCreated[] = 'Angket';
                Log::info("  Created: Token Angket - {$tokenAngket->token}");
            } else {
                $tokensSkipped[] = 'Angket (sudah ada yang aktif)';
                Log::info("  Skipped: Token Angket sudah ada");
            }

            // 2. Buat Token Penilaian per Mahasiswa
            foreach ($mahasiswas as $mhs) {
                $existingPenilaian = TokenPerusahaan::where('id_perusahaan', $id_perusahaan)
                    ->where('type', 'penilaian')
                    ->where('id_mahasiswa', $mhs->id_mahasiswa)
                    ->where('is_used', false)
                    ->where('expired_at', '>', now())
                    ->exists();

                if (!$existingPenilaian) {
                    $tokenMhs = TokenPerusahaan::create([
                        'id_perusahaan' => $id_perusahaan,
                        'id_prodi' => $idProdiKaprodi,
                        'type' => 'penilaian',
                        'id_mahasiswa' => $mhs->id_mahasiswa,
                        'token' => Str::upper(Str::random(10)),
                        'expired_at' => $request->expired_at,
                        'is_used' => false,
                        'created_at' => now(),
                    ]);
                    $tokensCreated[] = "Penilaian: {$mhs->nama}";
                    Log::info("  Created: Token Penilaian {$mhs->nama} - {$tokenMhs->token}");
                } else {
                    $tokensSkipped[] = "Penilaian: {$mhs->nama} (sudah ada)";
                    Log::info("  Skipped: Token Penilaian {$mhs->nama} sudah ada");
                }
            }
        });

        // ✅ Buat pesan yang informatif
        $message = '';
        if (!empty($tokensCreated)) {
            $message = count($tokensCreated) . ' token berhasil dibuat!';
        }
        if (!empty($tokensSkipped)) {
            $message .= ' ' . count($tokensSkipped) . ' token dilewati (sudah ada yang aktif).';
        }

        if (empty($tokensCreated) && !empty($tokensSkipped)) {
            return redirect()->route('kaprodi.token-perusahaan.index')
                ->with('warning', 'Semua token sudah ada dan masih aktif. Tidak ada token baru yang dibuat.');
        }

        return redirect()->route('kaprodi.token-perusahaan.index')
            ->with('success', $message);
    }

    // ✅ TAMBAHKAN: Method untuk force regenerate semua token
    public function forceRegenerateAll(Request $request)
    {
        $request->validate([
            'id_perusahaan' => 'required|exists:perusahaan,id_perusahaan',
            'expired_at' => 'required|date|after:now',
        ]);

        $idProdiKaprodi = $this->getKaprodiProdi();
        $id_perusahaan = $request->id_perusahaan;

        $mahasiswas = Mahasiswa::select('mahasiswa.id_mahasiswa')
            ->join('pengajuan_mahasiswa as pm', 'mahasiswa.id_mahasiswa', '=', 'pm.id_mahasiswa')
            ->join('pengajuan_magang as pmg', 'pm.id_pengajuan', '=', 'pmg.id_pengajuan')
            ->where('pmg.id_perusahaan', $id_perusahaan)
            ->where('mahasiswa.id_prodi', $idProdiKaprodi)
            ->groupBy('mahasiswa.id_mahasiswa')
            ->get();

        DB::transaction(function () use ($id_perusahaan, $request, $idProdiKaprodi, $mahasiswas) {
            // Hapus semua token lama yang belum digunakan
            TokenPerusahaan::where('id_perusahaan', $id_perusahaan)
                ->where('id_prodi', $idProdiKaprodi)
                ->where('is_used', false)
                ->delete();

            // Buat token angket baru
            TokenPerusahaan::create([
                'id_perusahaan' => $id_perusahaan,
                'id_prodi' => $idProdiKaprodi,
                'type' => 'angket',
                'id_mahasiswa' => null,
                'token' => Str::upper(Str::random(10)),
                'expired_at' => $request->expired_at,
                'is_used' => false,
                'created_at' => now(),
            ]);

            // Buat token penilaian baru
            foreach ($mahasiswas as $mhs) {
                TokenPerusahaan::create([
                    'id_perusahaan' => $id_perusahaan,
                    'id_prodi' => $idProdiKaprodi,
                    'type' => 'penilaian',
                    'id_mahasiswa' => $mhs->id_mahasiswa,
                    'token' => Str::upper(Str::random(10)),
                    'expired_at' => $request->expired_at,
                    'is_used' => false,
                    'created_at' => now(),
                ]);
            }
        });

        return redirect()->route('kaprodi.token-perusahaan.index')
            ->with('success', 'Semua token berhasil di-regenerate!');
    }

    public function regenerate($id_token)
    {
        $token = TokenPerusahaan::findOrFail($id_token);
        $idProdiKaprodi = $this->getKaprodiProdi();

        if ($token->id_prodi != $idProdiKaprodi) {
            abort(403, 'Anda tidak memiliki akses untuk token ini.');
        }

        $token->update([
            'token' => Str::upper(Str::random(10)),
            'expired_at' => now()->addDays(7),
            'is_used' => false,
        ]);

        return redirect()->route('kaprodi.token-perusahaan.index')
            ->with('success', 'Token berhasil diperbaharui!');
    }

    public function detailNilai($id_token)
    {
        $token = TokenPerusahaan::with(['perusahaan', 'mahasiswa.prodi', 'prodi'])->findOrFail($id_token);
        $idProdiKaprodi = $this->getKaprodiProdi();

        if ($token->id_prodi != $idProdiKaprodi) {
            abort(403, 'Anda tidak memiliki akses untuk melihat data ini.');
        }

        if (!$token->is_used) {
            return redirect()->back()->with('error', 'Token belum digunakan/diisi.');
        }

        if ($token->type === 'angket') {
            $angket = AngketPerusahaan::where('id_token', $id_token)->firstOrFail();
            return view('pages.kaprodi.token_perusahaan.detail', compact('token', 'angket'));
        } else {
            $penilaian = PenilaianMahasiswa::where('id_token', $id_token)->firstOrFail();
            return view('pages.kaprodi.token_perusahaan.detail', compact('token', 'penilaian'));
        }
    }

    public function indexAdmin(Request $request)
    {
        $query = TokenPerusahaan::with(['perusahaan', 'mahasiswa.prodi', 'prodi'])
                    ->orderBy('created_at', 'desc');

        if ($request->filled('id_prodi')) {
            $query->where('id_prodi', $request->id_prodi);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->whereHas('perusahaan', function ($q) use ($request) {
                $q->where('nama_perusahaan', 'like', '%' . $request->search . '%');
            });
        }

        $tokens = $query->paginate(10);
        $prodis = Prodi::orderBy('nama_prodi')->get();

        return view('pages.admin.nilai_perusahaan_angket.index', compact('tokens', 'prodis'));
    }

    public function detailNilaiAdmin($id_token)
    {
        $token = TokenPerusahaan::with(['perusahaan', 'mahasiswa.prodi', 'prodi'])->findOrFail($id_token);

        if ($token->type === 'angket') {
            $angket = AngketPerusahaan::where('id_token', $id_token)->firstOrFail();
            return view('pages.admin.nilai_perusahaan_angket.detail_angket', compact('token', 'angket'));
        } else {
            $penilaian = PenilaianMahasiswa::where('id_token', $id_token)->firstOrFail();
            return view('pages.admin.nilai_perusahaan_angket.detail_penilaian', compact('token', 'penilaian'));
        }
    }

    public function detailAngketAdmin($id_token)
    {
        $token = TokenPerusahaan::with(['perusahaan', 'prodi'])->findOrFail($id_token);
        $angket = AngketPerusahaan::where('id_token', $id_token)->firstOrFail();
        return view('pages.admin.nilai_perusahaan_angket.detail_angket', compact('token', 'angket'));
    }

    public function detailPenilaianAdmin($id_token)
    {
        $token = TokenPerusahaan::with(['perusahaan', 'mahasiswa.prodi', 'prodi'])->findOrFail($id_token);
        $penilaian = PenilaianMahasiswa::where('id_token', $id_token)->firstOrFail();
        return view('pages.admin.nilai_perusahaan_angket.detail_penilaian', compact('token', 'penilaian'));
    }

    public function cetakDokumen($id_token)
    {
        $token = TokenPerusahaan::with(['perusahaan', 'mahasiswa.prodi', 'prodi'])->findOrFail($id_token);
        $idProdiKaprodi = $this->getKaprodiProdi();

        if ($token->id_prodi != $idProdiKaprodi) {
            abort(403, 'Anda tidak memiliki akses untuk data ini.');
        }

        if (!$token->is_used) {
            return redirect()->back()->with('error', 'Token belum digunakan/diisi.');
        }

        if ($token->type === 'angket') {
            $angket = AngketPerusahaan::where('id_token', $id_token)->firstOrFail();
            return view('pages.kaprodi.token_perusahaan.cetak-dokumen', compact('token', 'angket'));
        } else {
            $penilaian = PenilaianMahasiswa::where('id_token', $id_token)->firstOrFail();
            return view('pages.kaprodi.token_perusahaan.cetak-dokumen', compact('token', 'penilaian'));
        }
    }
}