<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Mahasiswa;
use App\Models\PengajuanMagang;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /**
     * Halaman daftar absensi mahasiswa
     * HANYA untuk menampilkan halaman, TIDAK untuk menyimpan data
     */
    public function index()
    {
        $user = Auth::user();
        $mahasiswa = Mahasiswa::where('id_user', $user->id_user)->first();

        if (!$mahasiswa) {
            return redirect()->route('beranda-mahasiswa')
                ->with('error', 'Data mahasiswa tidak ditemukan.');
        }

        $riwayatAbsensi = Absensi::byMahasiswa($mahasiswa->id_mahasiswa)
            ->terbaru()
            ->paginate(10);

        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        $totalHadirBulanIni = Absensi::byMahasiswa($mahasiswa->id_mahasiswa)
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->hadir()
            ->count();

        $totalAbsensiBulanIni = Absensi::byMahasiswa($mahasiswa->id_mahasiswa)
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->count();

        // Cek status absensi hari ini (HANYA CEK, TIDAK SIMPAN)
        $absenHariIni = Absensi::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->whereDate('tanggal', Carbon::today())
            ->first();

        $statusHariIni = 'belum_masuk';
        $tipeAbsenSekarang = 'masuk';
        $nonHadirHariIni = null;

        if ($absenHariIni) {
            if ($absenHariIni->status !== 'hadir') {
                $statusHariIni = 'non_hadir';
                $tipeAbsenSekarang = null;
                $nonHadirHariIni = $absenHariIni;
            } else {
                if ($absenHariIni->jam_pulang) {
                    $statusHariIni = 'lengkap';
                    $tipeAbsenSekarang = null;
                } else {
                    $statusHariIni = 'belum_pulang';
                    $tipeAbsenSekarang = 'pulang';
                }
            }
        }

        // Cek pengajuan magang
        $pengajuanMagang = PengajuanMagang::where('id_user', $user->id_user)->first();

        $namaPerusahaan = '-';
        $idPerusahaan = null;

        if ($pengajuanMagang && $pengajuanMagang->id_perusahaan) {
            $perusahaan = Perusahaan::find($pengajuanMagang->id_perusahaan);
            if ($perusahaan) {
                $namaPerusahaan = $perusahaan->nama_perusahaan;
                $idPerusahaan = $perusahaan->id_perusahaan;
            }
        }

        $perusahaanCoords = null;
        if ($idPerusahaan) {
            $perusahaanData = Perusahaan::find($idPerusahaan);
            if ($perusahaanData && $perusahaanData->latitude && $perusahaanData->longitude) {
                $perusahaanCoords = [
                    'lat' => (float) $perusahaanData->latitude,
                    'lng' => (float) $perusahaanData->longitude,
                ];
            }
        }

        return view('pages.mahasiswa.absensi.index', compact(
            'mahasiswa',
            'riwayatAbsensi',
            'totalAbsensiBulanIni',
            'totalHadirBulanIni',
            'statusHariIni',
            'tipeAbsenSekarang',
            'nonHadirHariIni',
            'pengajuanMagang',
            'namaPerusahaan',
            'idPerusahaan',
            'perusahaanCoords'
        ));
    }

    /**
     * Simpan absensi masuk atau pulang
     * INI tempat yang benar untuk logic penyimpanan
     */
    public function storeFromIndex(Request $request)
    {
        $rules = [
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'alamat' => 'nullable|string|max:5000',
            'catatan' => 'nullable|string|max:1000',
            'face_verified' => 'required|in:true,false',
        ];

        $messages = [
            'foto.required' => 'Foto wajib diambil.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format gambar harus JPEG, PNG, atau JPG.',
            'foto.max' => 'Ukuran gambar maksimal 5MB.',
            'latitude.required' => 'Lokasi (latitude) wajib diambil.',
            'longitude.required' => 'Lokasi (longitude) wajib diambil.',
            'face_verified.required' => 'Verifikasi wajah wajib dilakukan.',
            'face_verified.in' => 'Verifikasi wajah gagal.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()->all()
            ], 422);
        }

        if ($request->face_verified !== 'true') {
            return response()->json([
                'success' => false,
                'message' => 'Verifikasi wajah gagal. Silakan coba lagi.'
            ], 400);
        }

        $user = Auth::user();
        $mahasiswa = Mahasiswa::where('id_user', $user->id_user)->first();

        if (!$mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data mahasiswa tidak ditemukan.'
            ], 404);
        }

        $pengajuanMagang = PengajuanMagang::where('id_user', $user->id_user)->first();

        if (!$pengajuanMagang) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki pengajuan magang.'
            ], 400);
        }

        try {
            $fotoPath = null;

            if ($request->hasFile('foto')) {
                $foto = $request->file('foto');
                $namaFoto = 'absensi_' . $mahasiswa->npm . '_' . now()->format('Ymd_His') . '.' . $foto->getClientOriginalExtension();
                $fotoPath = $foto->storeAs('absensi', $namaFoto, 'public');
                Log::info('FOTO TERSIMPAN: ' . $fotoPath);
            }

            $idPerusahaan = $pengajuanMagang->id_perusahaan;
            $perusahaan = $idPerusahaan ? Perusahaan::find($idPerusahaan) : null;

            $jarakMeter = null;
            if ($perusahaan && $perusahaan->latitude && $perusahaan->longitude) {
                $jarakMeter = $this->hitungJarakMeter(
                    (float) $request->latitude,
                    (float) $request->longitude,
                    (float) $perusahaan->latitude,
                    (float) $perusahaan->longitude
                );
            }

            // Cek apakah sudah ada record hadir hari ini
            $absenHariIni = Absensi::where('id_mahasiswa', $mahasiswa->id_mahasiswa)
                ->whereDate('tanggal', Carbon::today())
                ->where('status', 'hadir')
                ->first();

            if (!$absenHariIni) {
                // ==================== ABSEN MASUK ====================
                $absensi = Absensi::create([
                    'id_mahasiswa' => $mahasiswa->id_mahasiswa,
                    'id_perusahaan' => $idPerusahaan,
                    'tanggal' => now()->toDateString(),
                    'jam_masuk' => now()->format('H:i:s'),
                    'foto_masuk' => $fotoPath,
                    'status' => 'hadir',
                    'latitude_masuk' => $request->latitude,
                    'longitude_masuk' => $request->longitude,
                    'alamat_masuk' => $request->alamat,
                    'jarak_masuk' => $jarakMeter,
                    'status_validasi' => 'pending',
                    'catatan' => $request->catatan,
                ]);

                Log::info('ABSEN MASUK BERHASIL: ID=' . $absensi->id_absensi);

                return response()->json([
                    'success' => true,
                    'message' => 'Absen masuk berhasil disimpan.',
                    'redirect' => route('mahasiswa.absensi.index')
                ], 200);

            } elseif (!$absenHariIni->jam_pulang) {
                // ==================== ABSEN PULANG ====================
                $absenHariIni->update([
                    'jam_pulang' => now()->format('H:i:s'),
                    'foto_pulang' => $fotoPath,
                    'latitude_pulang' => $request->latitude,
                    'longitude_pulang' => $request->longitude,
                    'alamat_pulang' => $request->alamat,
                    'jarak_pulang' => $jarakMeter,
                ]);

                Log::info('ABSEN PULANG BERHASIL: ID=' . $absenHariIni->id_absensi);

                         // Simpan data ke session untuk pre-fill form logbook
                session()->flash('from_absen_pulang', true);
                session()->flash('absen_tanggal', $absenHariIni->tanggal->format('Y-m-d'));
                session()->flash('absen_jam_mulai', $absenHariIni->jam_masuk);
                session()->flash('absen_jam_selesai', $absenHariIni->jam_pulang);
                // LANGSUNG REDIRECT KE LOGBOOK
                return response()->json([
                    'success' => true,
                    'message' => 'Absen pulang berhasil. Mengalihkan ke logbook...',
                    'redirect' => route('mahasiswa.logbook.create')
                ], 200);

            } else {
                // ==================== SUDAH LENGKAP ====================
                if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                    Storage::disk('public')->delete($fotoPath);
                }

                // Set session data untuk pre-fill logbook
                     // Set session data untuk pre-fill logbook
                session()->flash('from_absen_pulang', true);
                session()->flash('absen_tanggal', $absenHariIni->tanggal->format('Y-m-d'));
                session()->flash('absen_jam_mulai', $absenHariIni->jam_masuk);
                session()->flash('absen_jam_selesai', $absenHariIni->jam_pulang);
                return response()->json([
                    'success' => true,
                    'message' => 'Absensi hari ini sudah lengkap. Mengalihkan ke logbook...',
                    'redirect' => route('mahasiswa.logbook.create')
                ], 200);
            }

        } catch (\Exception $e) {
            Log::error('GAGAL SIMPAN ABSENSI: ' . $e->getMessage());

            if (isset($fotoPath) && $fotoPath && Storage::disk('public')->exists($fotoPath)) {
                Storage::disk('public')->delete($fotoPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan absensi.'
            ], 500);
        }
    }

    /**
     * Cek status Python service
     */
    public function checkFaceService()
    {
        try {
            $ch = curl_init('http://127.0.0.1:5000/health');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                return response()->json([
                    'online' => true,
                    'data' => json_decode($response, true)
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Face service offline: ' . $e->getMessage());
        }

        return response()->json([
            'online' => false,
            'message' => 'Face detection service sedang offline'
        ]);
    }

    /**
     * Halaman daftar absensi untuk dosen pembimbing
     */
    public function indexDospem()
    {
        $user = Auth::user();
        $dosen = \App\Models\Dosen::where('id_user', $user->id_user)->first();

        if (!$dosen) {
            return redirect()->route('beranda-dospem')
                ->with('error', 'Data dosen tidak ditemukan.');
        }

        $mahasiswaBimbingan = \App\Models\DosenPembimbing::where('id_dosen', $dosen->id_dosen)
            ->with('mahasiswa')
            ->get()
            ->filter(function ($dp) {
                return $dp->mahasiswa !== null;
            });

        $idUserPunyaMagang = PengajuanMagang::whereNotNull('id_perusahaan')
            ->where('id_perusahaan', '>', 0)
            ->pluck('id_user')
            ->toArray();

        $mahasiswaBimbingan = $mahasiswaBimbingan->filter(function ($dp) use ($idUserPunyaMagang) {
            return in_array($dp->mahasiswa->id_user, $idUserPunyaMagang);
        });

        $dataMahasiswa = [];
        foreach ($mahasiswaBimbingan as $dp) {
            $mhs = $dp->mahasiswa;
            if (!$mhs) continue;

            $idMhs = $mhs->id_mahasiswa;

            $pengajuan = PengajuanMagang::where('id_user', $mhs->id_user)->first();

            $perusahaan = '-';
            if ($pengajuan && $pengajuan->id_perusahaan) {
                $perusahaanData = Perusahaan::find($pengajuan->id_perusahaan);
                if ($perusahaanData) {
                    $perusahaan = $perusahaanData->nama_perusahaan;
                }
            }

            $totalAbsensi = Absensi::where('id_mahasiswa', $idMhs)->count();
            $totalHadir = Absensi::where('id_mahasiswa', $idMhs)->where('status', 'hadir')->count();
            $totalSakit = Absensi::where('id_mahasiswa', $idMhs)->where('status', 'sakit')->count();
            $totalIzin = Absensi::where('id_mahasiswa', $idMhs)->where('status', 'izin')->count();
            $pending = Absensi::where('id_mahasiswa', $idMhs)->where('status_validasi', 'pending')->count();

            $dataMahasiswa[] = (object) [
                'id_mahasiswa'   => $idMhs,
                'nama'           => $mhs->nama,
                'npm'            => $mhs->npm,
                'perusahaan'     => $perusahaan,
                'total_absensi'  => $totalAbsensi,
                'total_hadir'    => $totalHadir,
                'total_sakit'    => $totalSakit,
                'total_izin'     => $totalIzin,
                'pending'        => $pending,
            ];
        }

        $totalMahasiswa = count($dataMahasiswa);
        $totalPending = array_sum(array_column($dataMahasiswa, 'pending'));

        return view('pages.dospem.absensi.index', compact(
            'dataMahasiswa',
            'totalMahasiswa',
            'totalPending'
        ));
    }

    /**
     * Detail absensi satu mahasiswa bimbingan
     */
    public function detailDospem($id_mahasiswa)
    {
        $user = Auth::user();
        $dosen = \App\Models\Dosen::where('id_user', $user->id_user)->first();

        if (!$dosen) {
            return redirect()->route('dospem.absensi.index')
                ->with('error', 'Data dosen tidak ditemukan.');
        }

        $isBimbingan = \App\Models\DosenPembimbing::where('id_dosen', $dosen->id_dosen)
            ->where('id_mahasiswa', $id_mahasiswa)
            ->exists();

        if (!$isBimbingan) {
            return redirect()->route('dospem.absensi.index')
                ->with('error', 'Mahasiswa ini bukan bimbingan Anda.');
        }

        $mahasiswa = Mahasiswa::find($id_mahasiswa);

        if (!$mahasiswa) {
            return redirect()->route('dospem.absensi.index')
                ->with('error', 'Data mahasiswa tidak ditemukan.');
        }

        $pengajuan = PengajuanMagang::where('id_user', $mahasiswa->id_user)->first();
        $perusahaan = '-';
        if ($pengajuan && $pengajuan->id_perusahaan) {
            $perusahaanData = Perusahaan::find($pengajuan->id_perusahaan);
            if ($perusahaanData) {
                $perusahaan = $perusahaanData->nama_perusahaan;
            }
        }

        $daftarAbsensi = Absensi::where('id_mahasiswa', $id_mahasiswa)
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_masuk', 'desc')
            ->paginate(10);

        $totalAbsensi = Absensi::where('id_mahasiswa', $id_mahasiswa)->count();
        $totalHadir = Absensi::where('id_mahasiswa', $id_mahasiswa)->where('status', 'hadir')->count();
        $totalSakit = Absensi::where('id_mahasiswa', $id_mahasiswa)->where('status', 'sakit')->count();
        $totalIzin = Absensi::where('id_mahasiswa', $id_mahasiswa)->where('status', 'izin')->count();
        $totalPending = Absensi::where('id_mahasiswa', $id_mahasiswa)->where('status_validasi', 'pending')->count();
        $totalDiterima = Absensi::where('id_mahasiswa', $id_mahasiswa)->where('status_validasi', 'diterima')->count();
        $totalDitolak = Absensi::where('id_mahasiswa', $id_mahasiswa)->where('status_validasi', 'ditolak')->count();

        return view('pages.dospem.absensi.detail', compact(
            'mahasiswa',
            'perusahaan',
            'daftarAbsensi',
            'totalAbsensi',
            'totalHadir',
            'totalSakit',
            'totalIzin',
            'totalPending',
            'totalDiterima',
            'totalDitolak'
        ));
    }

    /**
     * Halaman form validasi satu absensi
     */
    public function validasiDospem($id_absensi)
    {
        $user = Auth::user();
        $dosen = \App\Models\Dosen::where('id_user', $user->id_user)->first();

        if (!$dosen) {
            return redirect()->route('dospem.absensi.index')
                ->with('error', 'Data dosen tidak ditemukan.');
        }

        $absensi = Absensi::with('mahasiswa')->find($id_absensi);

        if (!$absensi) {
            return redirect()->route('dospem.absensi.index')
                ->with('error', 'Data absensi tidak ditemukan.');
        }

        $isBimbingan = \App\Models\DosenPembimbing::where('id_dosen', $dosen->id_dosen)
            ->where('id_mahasiswa', $absensi->id_mahasiswa)
            ->exists();

        if (!$isBimbingan) {
            return redirect()->route('dospem.absensi.index')
                ->with('error', 'Anda tidak memiliki akses untuk memvalidasi absensi ini.');
        }

        $perusahaan = null;
        if ($absensi->id_perusahaan) {
            $perusahaan = Perusahaan::find($absensi->id_perusahaan);
        }

        return view('pages.dospem.absensi.validasi', compact(
            'absensi',
            'perusahaan'
        ));
    }

    /**
     * Update perusahaan (tetap di sini sesuai aslinya)
     */
    public function update(Request $request, $id_perusahaan)
    {
        $perusahaan = Perusahaan::findOrFail($id_perusahaan);

        $validated = $request->validate([
            'nama_perusahaan' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('perusahaan', 'nama_perusahaan')->ignore($id_perusahaan, 'id_perusahaan'),
            ],
            'alamat' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ], [
            'latitude.required' => 'Titik lokasi belum dipilih di peta.',
            'longitude.required' => 'Titik lokasi belum dipilih di peta.',
        ]);

        Log::info('UPDATE PERUSAHAAN ID=' . $id_perusahaan);
        Log::info('  latitude lama: ' . $perusahaan->latitude);
        Log::info('  longitude lama: ' . $perusahaan->longitude);
        Log::info('  latitude baru: ' . $validated['latitude']);
        Log::info('  longitude baru: ' . $validated['longitude']);

        $perusahaan->update($validated);

        $perusahaan->refresh();
        Log::info('  latitude setelah save: ' . $perusahaan->latitude);
        Log::info('  longitude setelah save: ' . $perusahaan->longitude);

        return redirect()->route('admin.perusahaan.edit', $id_perusahaan)
            ->with('success', 'Data perusahaan berhasil diperbarui');
    }

    /**
     * Proses validasi absensi (setujui atau tolak)
     */
    public function prosesValidasiDospem(Request $request, $id_absensi)
    {
        $user = Auth::user();
        $dosen = \App\Models\Dosen::where('id_user', $user->id_user)->first();

        if (!$dosen) {
            return redirect()->route('dospem.absensi.index')
                ->with('error', 'Data dosen tidak ditemukan.');
        }

        $absensi = Absensi::find($id_absensi);

        if (!$absensi) {
            return redirect()->route('dospem.absensi.index')
                ->with('error', 'Data absensi tidak ditemukan.');
        }

        $isBimbingan = \App\Models\DosenPembimbing::where('id_dosen', $dosen->id_dosen)
            ->where('id_mahasiswa', $absensi->id_mahasiswa)
            ->exists();

        if (!$isBimbingan) {
            return redirect()->route('dospem.absensi.index')
                ->with('error', 'Anda tidak memiliki akses untuk memvalidasi absensi ini.');
        }

        $request->validate([
            'status_validasi' => 'required|in:diterima,ditolak',
            'catatan_validasi' => 'nullable|string|max:1000',
        ], [
            'status_validasi.required' => 'Status validasi wajib dipilih.',
            'status_validasi.in' => 'Status validasi tidak valid.',
        ]);

        try {
            $absensi->update([
                'status_validasi' => $request->status_validasi,
                'validated_by'    => $dosen->id_dosen,
                'catatan'         => $request->catatan_validasi,
            ]);

            $statusText = $request->status_validasi == 'diterima' ? 'disetujui' : 'ditolak';

            return redirect()->route('dospem.absensi.detail', ['id_mahasiswa' => $absensi->id_mahasiswa])
                ->with('success', 'Absensi tanggal ' . Carbon::parse($absensi->tanggal)->format('d F Y') . ' berhasil ' . $statusText . '.');

        } catch (\Exception $e) {
            Log::error('GAGAL VALIDASI ABSENSI: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memproses validasi.')
                ->withInput();
        }
    }

    /**
     * Proxy deteksi wajah ke Python service
     */
    public function proxyDetect(Request $request)
    {
        try {
            $ch = curl_init('http://127.0.0.1:5000/detect');
            $payload = json_encode([
                'image'      => $request->input('image'),
                'session_id' => $request->input('session_id'),
            ]);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT        => 5,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error    = curl_error($ch);
            curl_close($ch);

            if ($error) {
                Log::warning('Proxy detect error: ' . $error);
                return response()->json(['success' => false, 'message' => 'Service tidak merespon'], 503);
            }

            return response($response, $httpCode)
                ->header('Content-Type', 'application/json');

        } catch (\Exception $e) {
            Log::error('Proxy detect exception: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
        }
    }

    /**
     * Proxy reset session di Python service
     */
    public function proxyReset(Request $request)
    {
        try {
            $ch = curl_init('http://127.0.0.1:5000/reset');
            $payload = json_encode([
                'session_id' => $request->input('session_id'),
            ]);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT        => 3,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return response($response, $httpCode)
                ->header('Content-Type', 'application/json');

        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    private function hitungJarakMeter($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * asin(sqrt($a));
        return round($earthRadius * $c, 2);
    }
}