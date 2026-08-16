<?php

namespace App\Http\Controllers;

use App\Models\TokenPerusahaan;
use App\Models\AngketPerusahaan;
use App\Models\PenilaianMahasiswa;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use DB;

/**
 * Controller ini ngurus form yang diisi oleh PERUSAHAAN (bukan mahasiswa/dosen)
 * 
 * Jadi ceritanya gini:
 * 1. Kaprodi generate token (di controller lain) lalu kirim ke perusahaan
 * 2. Perusahaan klik link yang berisi token tersebut
 * 3. Muncul form sesuai jenis token:
 *    - Token Angket: form feedback tentang program magang (10 aspek, skala 1-4)
 *    - Token Penilaian: form penilaian mahasiswa magang (18 aspek, skala 1-100) + TTD digital
 * 4. Setelah diisi, token ditandai "sudah dipakai" dan gak bisa diisi lagi
 * 5. Kaprodi dapet notifikasi bahwa token sudah diisi
 * 
 * Penting: form ini BISA diakses tanpa login (karena perusahaan gak punya akun)
 * Jadi keamanannya bergantung pada token yang unik dan kadaluarsa
 */
class PerusahaanFormController extends Controller
{
    /**
     * Menampilkan form isian berdasarkan token
     * 
     * Ada 3 pengecekan sebelum form ditampilkan:
     * 1. Token harus ada di database (kalau gak -> 404)
     * 2. Token belum pernah dipakai (kalau sudah -> tampil halaman "sudah dipakai")
     * 3. Token belum kadaluarsa (kalau sudah -> tampil halaman "sudah kadaluarsa")
     */
    public function showForm($token)
    {
        // Cari token berdasarkan string token-nya
        $tokenData = TokenPerusahaan::where('token', $token)->first();

        // Kalau token gak ketemu -> 404
        if (!$tokenData) {
            abort(404, 'Link tidak valid.');
        }

        // Kalau token sudah pernah dipakai -> tampilkan halaman info
        if ($tokenData->is_used) {
            return view('pages.perusahaan.tidak_valid', ['pesan' => 'Link ini sudah pernah digunakan untuk mengisi form.']);
        }

        // Kalau token sudah kadaluarsa -> tampilkan halaman info
        if (now()->isAfter($tokenData->expired_at)) {
            return view('pages.perusahaan.tidak_valid', ['pesan' => 'Link ini sudah kadaluarsa. Silakan hubungi Kaprodi untuk mendapatkan link baru.']);
        }

        // Kalau semua pengecekan lolos, load relasi yang dibutuhkan form
        $tokenData->load(['perusahaan', 'mahasiswa.prodi']);

        return view('pages.perusahaan.form', compact('tokenData'));
    }

    /**
     * Proses menyimpan jawaban form dari perusahaan
     * 
     * Ini bagian yang paling kompleks karena:
     * - Validasi beda-beda tergantung jenis token (angket vs penilaian)
     * - Penilaian ada 18 aspek yang harus dihitung rata-ratanya
     * - Penilaian butuh upload TTD digital
     * - Semua proses simpan dibungkus transaction (biar konsisten)
     * - Setelah selesai, kirim notifikasi ke Kaprodi
     */
    public function submitForm(Request $request, $token)
    {
        // Cek ulang token (sama seperti di showForm, tapi tanpa halaman info)
        $tokenData = TokenPerusahaan::where('token', $token)->first();

        if (!$tokenData || $tokenData->is_used || now()->isAfter($tokenData->expired_at)) {
            return view('pages.perusahaan.tidak_valid', ['pesan' => 'Token tidak valid atau sudah tidak bisa digunakan.']);
        }

        // ============================================
        // VALIDASI INPUT
        // Ada field dasar yang sama untuk angket dan penilaian:
        // - nama_pengisi: siapa yang mengisi form
        // - jabatan_pengisi: jabatan-nya di perusahaan
        // - email_pengisi: email untuk kontak
        // - pernyataan_keaslian: checkbox bahwa data yang diisi adalah benar
        // ============================================
        $baseRules = [
            'nama_pengisi' => 'required|string|max:100',
            'jabatan_pengisi' => 'required|string|max:100',
            'email_pengisi' => 'required|email|max:100',
            'pernyataan_keaslian' => 'required|accepted', // accepted = harus dicentang
        ];

        if ($tokenData->type === 'angket') {
            // ============================================
            // VALIDASI ANGKET: 10 aspek, skala 1-4
            // Angket ini tentang feedback program magang secara umum
            // ============================================
            $angketRules = [
                'integritas_motivasi' => 'required|integer|min:1|max:4',
                'etika_sopan_santun' => 'required|integer|min:1|max:4',
                'kompetensi_keahlian' => 'required|integer|min:1|max:4',
                'kedisiplinan' => 'required|integer|min:1|max:4',
                'respon_kecepatan_kerja' => 'required|integer|min:1|max:4',
                'adaptasi_komunikasi' => 'required|integer|min:1|max:4',
                'kerjasama_tim' => 'required|integer|min:1|max:4',
                'pengembangan_diri' => 'required|integer|min:1|max:4',
                'kemampuan_bahasa_inggris' => 'required|integer|min:1|max:4',
                'kemampuan_teknologi_informasi' => 'required|integer|min:1|max:4',
            ];
            $request->validate(array_merge($baseRules, $angketRules));
        } else {
            // ============================================
            // VALIDASI PENILAIAN: 18 aspek, skala 1-100 + TTD digital
            // Penilaian ini tentang performa mahasiswa yang magang
            // ============================================
            $penilaianRules = [
                'kedisiplinan_mhs' => 'required|integer|min:1|max:100',
                'adaptasi_mhs' => 'required|integer|min:1|max:100',
                'tanggung_jawab' => 'required|integer|min:1|max:100',
                'kemandirian' => 'required|integer|min:1|max:100',
                'inisiatif_kreativitas' => 'required|integer|min:1|max:100',
                'kesesuaian_instruksi' => 'required|integer|min:1|max:100',
                'kualitas_pekerjaan' => 'required|integer|min:1|max:100',
                'ketepatan_waktu' => 'required|integer|min:1|max:100',
                'problem_solving' => 'required|integer|min:1|max:100',
                'teamwork_mhs' => 'required|integer|min:1|max:100',
                'hubungan_atasan' => 'required|integer|min:1|max:100',
                'hubungan_rekan' => 'required|integer|min:1|max:100',
                'hubungan_relasi' => 'required|integer|min:1|max:100',
                'sikap_kerja' => 'required|integer|min:1|max:100',
                'disiplin_kerja' => 'required|integer|min:1|max:100',
                'loyalitas' => 'required|integer|min:1|max:100',
                'motivasi' => 'required|integer|min:1|max:100',
                'penampilan' => 'required|integer|min:1|max:100',
                'ttd_digital' => 'required|image|mimes:jpeg,png,jpg|max:2048', // TTD WAJIB diisi
            ];
            $request->validate(array_merge($baseRules, $penilaianRules));
        }

        // ============================================
        // PROSES SIMPAN: Dibungkus dalam transaction
        // Artinya: kalau ada bagian yang gagal, semua dibatalkan
        // Jadi gak akan ada data setengah jadi
        // ============================================
        DB::transaction(function () use ($request, $tokenData) {
            if ($tokenData->type === 'angket') {
                // ============================================
                // SIMPAN ANGKET
                // Langsung simpan semua jawaban ke tabel angket_perusahaan
                // Catatan bersifat opsional
                // ============================================
                AngketPerusahaan::create([
                    'id_token' => $tokenData->id_token,
                    'id_perusahaan' => $tokenData->id_perusahaan,
                    'nama_pengisi' => $request->nama_pengisi,
                    'jabatan_pengisi' => $request->jabatan_pengisi,
                    'email_pengisi' => $request->email_pengisi,
                    'pernyataan_keaslian' => $request->pernyataan_keaslian ? true : false,
                    'integritas_motivasi' => $request->integritas_motivasi,
                    'etika_sopan_santun' => $request->etika_sopan_santun,
                    'kompetensi_keahlian' => $request->kompetensi_keahlian,
                    'kedisiplinan' => $request->kedisiplinan,
                    'respon_kecepatan_kerja' => $request->respon_kecepatan_kerja,
                    'adaptasi_komunikasi' => $request->adaptasi_komunikasi,
                    'kerjasama_tim' => $request->kerjasama_tim,
                    'pengembangan_diri' => $request->pengembangan_diri,
                    'kemampuan_bahasa_inggris' => $request->kemampuan_bahasa_inggris,
                    'kemampuan_teknologi_informasi' => $request->kemampuan_teknologi_informasi,
                    'catatan' => $request->catatan_angket,
                    'created_at' => now(),
                ]);
            } else {
                // ============================================
                // SIMPAN PENILAIAN MAHASISWA
                // Lebih kompleks karena:
                // 1. Upload TTD digital dulu
                // 2. Hitung total dan rata-rata dari 18 aspek
                // 3. Simpan ke tabel penilaian_mahasiswa
                // ============================================

                // Upload TTD digital ke storage
                $ttdPath = null;
                if ($request->hasFile('ttd_digital')) {
                    $file = $request->file('ttd_digital');
                    if ($file->isValid()) {
                        // Nama file dibuat unik: ttd_pl_[id token]_[timestamp].[ekstensi]
                        // Biar gak tabrakan kalau ada beberapa upload bersamaan
                        $filename = 'ttd_pl_' . $tokenData->id_token . '_' . time() . '.' . $file->getClientOriginalExtension();
                        $ttdPath = $file->storeAs('ttd', $filename, 'public');
                    }
                }

                // Hitung total nilai dari 18 aspek
                $total = $request->kedisiplinan_mhs + $request->adaptasi_mhs + $request->tanggung_jawab + 
                         $request->kemandirian + $request->inisiatif_kreativitas + $request->kesesuaian_instruksi + 
                         $request->kualitas_pekerjaan + $request->ketepatan_waktu + $request->problem_solving + 
                         $request->teamwork_mhs + $request->hubungan_atasan + $request->hubungan_rekan + 
                         $request->hubungan_relasi + $request->sikap_kerja + $request->disiplin_kerja + 
                         $request->loyalitas + $request->motivasi + $request->penampilan;
                         
                // Hitung rata-rata (dibagi 18 karena ada 18 aspek)
                $rata_rata = $total / 18;

                // Simpan ke tabel penilaian_mahasiswa
                // Catatan: nama field di form (_mhs) beda dengan nama kolom di database
                // Misal: kedisiplinan_mhs di form -> kedisiplinan di database
                PenilaianMahasiswa::create([
                    'id_token' => $tokenData->id_token,
                    'id_mahasiswa' => $tokenData->id_mahasiswa,
                    'id_perusahaan' => $tokenData->id_perusahaan,
                    'nama_pengisi' => $request->nama_pengisi,
                    'jabatan_pengisi' => $request->jabatan_pengisi,
                    'email_pengisi' => $request->email_pengisi,
                    'pernyataan_keaslian' => $request->pernyataan_keaslian ? true : false,
                    'kedisiplinan' => $request->kedisiplinan_mhs,
                    'adaptasi' => $request->adaptasi_mhs,
                    'tanggung_jawab' => $request->tanggung_jawab,
                    'kemandirian' => $request->kemandirian,
                    'inisiatif_kreativitas' => $request->inisiatif_kreativitas,
                    'kesesuaian_instruksi' => $request->kesesuaian_instruksi,
                    'kualitas_pekerjaan' => $request->kualitas_pekerjaan,
                    'ketepatan_waktu' => $request->ketepatan_waktu,
                    'problem_solving' => $request->problem_solving,
                    'teamwork' => $request->teamwork_mhs,
                    'hubungan_atasan' => $request->hubungan_atasan,
                    'hubungan_rekan' => $request->hubungan_rekan,
                    'hubungan_relasi' => $request->hubungan_relasi,
                    'sikap_kerja' => $request->sikap_kerja,
                    'disiplin_kerja' => $request->disiplin_kerja,
                    'loyalitas' => $request->loyalitas,
                    'motivasi' => $request->motivasi,
                    'penampilan' => $request->penampilan,
                    'total_nilai' => $total,
                    'rata_rata' => $rata_rata,
                    'catatan' => $request->catatan_penilaian,
                    'ttd_digital' => $ttdPath,
                    'created_at' => now(),
                ]);
            }

            // ============================================
            // TANDAI TOKEN SUDAH DIPAKAI
            // Setelah data tersimpan, token ditandai "sudah dipakai"
            // Jadi kalau ada yang coba akses link ini lagi, gak bisa
            // ============================================
            $tokenData->update([
                'is_used' => true,
                'accessed_at' => now(), // Catat kapan token ini dipakai
            ]);
        });

        // ============================================
        // KIRIM NOTIFIKASI KE KAPRODI
        // Setelah token berhasil diisi, Kaprodi yang bersangkutan dapet notifikasi
        // Jadi Kaprodi tau kalau perusahaan sudah mengisi form-nya
        // ============================================
        $tokenData->refresh(); // Refresh data token (karena barusan di-update di dalam transaction)
        if ($tokenData && $tokenData->id_prodi) {
            // Tentukan jenis notifikasi berdasarkan tipe token
            $jenis = $tokenData->type === 'angket' ? 'Angket Perusahaan' : 'Penilaian Mahasiswa';
            $namaPerusahaan = $tokenData->perusahaan->nama_perusahaan ?? '-';
            // Kalau penilaian, cantumkan juga nama mahasiswa yang dinilai
            $namaMhs = $tokenData->type === 'penilaian' ? ($tokenData->mahasiswa->nama ?? '-') : '';
            
            // kirimKeKaprodiProdi: helper yang mencari Kaprodi berdasarkan ID prodi, lalu kirim notifikasi
            Notifikasi::kirimKeKaprodiProdi(
                $tokenData->id_prodi,
                'Token ' . $jenis . ' Telah Diisi',
                'Token ' . $jenis . ($namaMhs ? ' (' . $namaMhs . ')' : '') . ' untuk ' . $namaPerusahaan . ' telah diisi oleh perusahaan.',
                'kaprodi_token_digunakan',
                $tokenData->id_token
            );
        }

        // Redirect ke halaman sukses
        return redirect()->route('perusahaan.form.berhasil');
    }

    /**
     * Halaman sukses setelah form berhasil diisi
     * 
     * Halaman ini menampilkan pesan bahwa form sudah berhasil dikirim
     */
    public function berhasil()
    {
        return view('pages.perusahaan.berhasil');
    }
}