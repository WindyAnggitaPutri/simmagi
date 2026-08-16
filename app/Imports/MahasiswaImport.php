<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Class untuk handle import data mahasiswa dari file Excel
 * 
 * Mirip sama DosenImport, tapi ada beberapa kolom tambahan:
 * - npm (pengganti nidn)
 * - angkatan
 * - semester
 * - kelas
 */
class MahasiswaImport implements ToCollection, WithHeadingRow
{
    // Tempat nyimpen error yang terjadi selama proses import
    protected $errors = [];
    
    // Counter buat hitung berapa yang berhasil disimpan
    protected $successCount = 0;
    
    // Counter buat hitung berapa yang gagal
    protected $failCount = 0;

    /**
     * Array buat tracking duplikat dalam SATU file Excel
     * Tujuannya biar nggak ada NPM atau email yang sama di file yang sama
     */
    protected $seenNpm = [];     // Format: ['2021001' => 3] artinya NPM ini muncul pertama kali di baris 3
    protected $seenEmail = [];  // Format: ['adi@email.com' => 5] artinya email ini muncul pertama kali di baris 5

    /**
     * Method utama yang dipanggil pas import Excel
     * $rows berisi semua baris dari Excel (sudah diubah jadi Collection)
     */
    public function collection(Collection $rows)
    {
        // Reset semua counter dan tracker, biar bersih tiap kali import
        $this->successCount = 0;
        $this->failCount = 0;
        $this->errors = [];
        $this->seenNpm = [];
        $this->seenEmail = [];

        // Loop setiap baris di Excel
        foreach ($rows as $index => $row) {
            // Baris 1 di Excel itu header, jadi data pertama itu baris ke-2
            $rowNumber = $index + 2;

            // =============================================
            // HANDLE FORMAT TANGGAL DARI EXCEL
            // =============================================
            // Kadang Excel ngasih tanggal dalam bentuk objek DateTime, bukan string
            // Jadi kita perlu convert dulu ke string sebelum diproses
            $rowData = [];
            foreach ($row->toArray() as $key => $value) {
                if ($value instanceof \DateTimeInterface) {
                    // Kalau berupa objek tanggal, ubah ke format Y-m-d
                    $rowData[$key] = $value->format('Y-m-d');
                } elseif ($value !== null) {
                    // Kalau bukan null, cast ke string
                    $rowData[$key] = (string) $value;
                } else {
                    // Kalau null, biarkan null
                    $rowData[$key] = null;
                }
            }

            // =============================================
            // AMBIL DAN BERSIHKAN DATA DARI SETIAP KOLOM
            // =============================================
            // trim() buat hilangkan spasi berlebih di awal dan akhir
            // ?? '' buat jaga-jaga kalau kolomnya nggak ada
            $npm           = trim($rowData['npm'] ?? '');
            $nama          = trim($rowData['nama'] ?? '');
            $tglLahirRaw   = trim($rowData['tgl_lahir'] ?? '');
            $jenisKelamin  = trim($rowData['jenis_kelamin'] ?? '');
            $prodiInput    = trim($rowData['prodi'] ?? '');
            $angkatan      = trim($rowData['angkatan'] ?? '');
            $semester      = trim($rowData['semester'] ?? '');
            $kelas         = trim($rowData['kelas'] ?? '');
            $noHp          = trim($rowData['no_hp'] ?? '');
            $email         = trim($rowData['email'] ?? '');

            // =============================================
            // SKIP BARIS KOSONG
            // =============================================
            // Kadang di Excel ada baris kosong di bawah, ini buat skip baris tersebut
            // Baris dianggap kosong kalau SEMUA kolom penting kosong
            $isRowEmpty = empty($npm)
                       && empty($nama)
                       && empty($tglLahirRaw)
                       && empty($jenisKelamin)
                       && empty($prodiInput)
                       && empty($angkatan)
                       && empty($semester)
                       && empty($kelas)
                       && empty($noHp)
                       && empty($email);

            if ($isRowEmpty) {
                // Langsung lanjut ke baris berikutnya
                continue;
            }

            // =============================================
            // VALIDASI DATA MENGGUNAKAN LARAVEL VALIDATOR
            // =============================================
            // Ini validasi standar Laravel, kayak di form biasa
            $validator = Validator::make($rowData, [
                'nama'          => 'required|max:50',                                                    // Nama wajib, maks 50 karakter
                'npm'           => 'required|max:30|unique:mahasiswa,npm',                               // NPM wajib, maks 30, unik di tabel mahasiswa
                'tgl_lahir'     => 'required',                                                           // Tanggal lahir wajib
                'jenis_kelamin' => 'required|in:L,P,l,p',                                               // Harus L atau P (case insensitive)
                'prodi'         => 'required',                                                           // Prodi wajib
                'angkatan'      => 'required',                                                           // Angkatan wajib
                'semester'      => 'required',                                                           // Semester wajib
                'kelas'         => 'required|in:A,B,C,D,a,b,c,d',                                       // Kelas wajib, harus A/B/C/D (case insensitive)
                'no_hp'         => 'required|max:15|regex:/^[0-9]+$/',                                  // No HP wajib, maks 15, hanya angka
                'email'         => 'required|email|max:100|unique:mahasiswa,email',                      // Email wajib, format valid, unik
            ], [
                // Pesan error custom biar lebih ramah dan mudah dipahami user
                'nama.required'          => 'Nama wajib diisi.',
                'nama.max'               => 'Nama maksimal 50 karakter.',
                'npm.required'           => 'NPM wajib diisi.',
                'npm.max'                => 'NPM maksimal 30 karakter.',
                'npm.unique'             => 'NPM "' . $npm . '" sudah terdaftar di database.',
                'tgl_lahir.required'     => 'Tanggal lahir wajib diisi.',
                'jenis_kelamin.required' => 'Jenis kelamin wajib diisi.',
                'jenis_kelamin.in'       => 'Jenis kelamin harus L atau P.',
                'prodi.required'         => 'Prodi wajib diisi.',
                'angkatan.required'      => 'Angkatan wajib diisi.',
                'semester.required'      => 'Semester wajib diisi.',
                'kelas.required'         => 'Kelas wajib diisi.',
                'kelas.in'               => 'Kelas harus A, B, C, atau D.',
                'no_hp.required'         => 'No HP wajib diisi.',
                'no_hp.max'              => 'No HP maksimal 15 karakter.',
                'no_hp.regex'            => 'No HP hanya boleh berisi angka.',
                'email.required'         => 'Email wajib diisi.',
                'email.email'            => 'Format email tidak valid.',
                'email.max'              => 'Email maksimal 100 karakter.',
                'email.unique'           => 'Email "' . $email . '" sudah terdaftar di database.',
            ]);

            // =============================================
            // CEK DUPLIKAT NPM DALAM FILE YANG SAMA
            // =============================================
            // Validator di atas cuma cek duplikat dengan database
            // Nah, ini buat cek duplikat ANTAR BARIS dalam file Excel yang sama
            if (!empty($npm)) {
                // Ubah ke lowercase biar "NPM123" dan "npm123" dianggap sama
                $npmLower = Str::lower($npm);
                
                if (isset($this->seenNpm[$npmLower])) {
                    // Kalau NPM ini udah pernah muncul sebelumnya, tambah error
                    $validator->errors()->add('npm', 'NPM "' . $npm . '" duplikat dalam file ini (baris ke-' . $this->seenNpm[$npmLower] . ').');
                } else {
                    // Kalau belum pernah muncul, simpen sebagai "sudah dilihat"
                    $this->seenNpm[$npmLower] = $rowNumber;
                }
            }

            // =============================================
            // CEK DUPLIKAT EMAIL DALAM FILE YANG SAMA
            // =============================================
            // Logikanya sama persis dengan cek duplikat NPM di atas
            if (!empty($email)) {
                $emailLower = Str::lower($email);
                
                if (isset($this->seenEmail[$emailLower])) {
                    $validator->errors()->add('email', 'Email "' . $email . '" duplikat dalam file ini (baris ke-' . $this->seenEmail[$emailLower] . ').');
                } else {
                    $this->seenEmail[$emailLower] = $rowNumber;
                }
            }

            // =============================================
            // CARI DATA PRODI DI DATABASE
            // =============================================
            // User bisa input prodi dalam bentuk ID (angka) atau nama prodi
            $prodi = null;
            
            // Coba cari berdasarkan ID dulu (kalau inputnya angka)
            if (is_numeric($prodiInput)) {
                $prodi = Prodi::find((int) $prodiInput);
            }
            
            // Kalau nggak ketemu berdasarkan ID, coba cari berdasarkan nama
            // Pakai LIKE biar fleksibel, misal "Teknik Informatika" akan ketemu walau cuma input "Informatika"
            if (!$prodi) {
                $prodi = Prodi::where('nama_prodi', 'LIKE', '%' . $prodiInput . '%')->first();
            }
            
            // Kalau sampai sini masih nggak ketemu, berarti prodi-nya memang nggak ada di database
            if (!$prodi && !empty($prodiInput)) {
                $validator->errors()->add('prodi', 'Prodi "' . $prodiInput . '" tidak ditemukan di database.');
            }

            // =============================================
            // VALIDASI ANGKATAN
            // =============================================
            // Angkatan harus berupa tahun yang masuk akal (2020-2100)
            if (!empty($angkatan)) {
                if (!is_numeric($angkatan) || (int) $angkatan < 2020 || (int) $angkatan > 2100) {
                    $validator->errors()->add('angkatan', 'Angkatan harus berupa angka tahun (2020-2100).');
                }
            }

            // =============================================
            // VALIDASI SEMESTER
            // =============================================
            // Semester mahasiswa biasanya 1-14 (kalau sampai 14 berarti sudah lama banget kuliahnya 😄)
            if (!empty($semester)) {
                if (!is_numeric($semester) || (int) $semester < 1 || (int) $semester > 14) {
                    $validator->errors()->add('semester', 'Semester harus berupa angka (1-14).');
                }
            }

            // =============================================
            // PARSE TANGGAL LAHIR
            // =============================================
            // Excel itu punya banyak format tanggal, jadi kita handle berbagai kemungkinan
            $tglLahir = null;
            
            if (!empty($tglLahirRaw)) {
                // Kasus 1: Format sudah standar Y-m-d (contoh: 1990-01-15)
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tglLahirRaw)) {
                    $tglLahir = $tglLahirRaw;
                
                // Kasus 2: Format Y-m-d tapi ada jam-nya (contoh: 1990-01-15 00:00:00)
                } elseif (preg_match('/^(\d{4}-\d{2}-\d{2})\s+\d{2}:\d{2}/', $tglLahirRaw, $m)) {
                    // Ambil bagian tanggalnya aja, buang jam-nya
                    $tglLahir = $m[1];
                
                // Kasus 3: Format d/m/Y (contoh: 15/01/1990) -> format umum di Indonesia
                } elseif (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $tglLahirRaw, $m)) {
                    // Ubah dari d/m/Y jadi Y-m-d
                    // str_pad buat tambahin leading zero kalau hanya 1 digit
                    $tglLahir = $m[3] . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
                
                // Kasus 4: Format serial number Excel (contoh: 41275)
                // Excel nyimpen tanggal sebagai angka sejak 1 Januari 1900
                } elseif (is_numeric($tglLahirRaw) && (int) $tglLahirRaw > 10000) {
                    try {
                        // Library PhpSpreadsheet punya fungsi khusus buat convert ini
                        $tglLahir = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $tglLahirRaw)->format('Y-m-d');
                    } catch (\Exception $e) {
                        // Kalau gagal convert, biarkan null (nanti akan error)
                        $tglLahir = null;
                    }
                
                // Kasus 5: Format lain yang masih bisa diparse oleh strtotime PHP
                } elseif (($timestamp = @strtotime($tglLahirRaw)) !== false) {
                    $tglLahir = date('Y-m-d', $timestamp);
                }

                // =============================================
                // VALIDASI TANGGAL YANG SUDAH DI-PARSE
                // =============================================
                // Setelah dapat format Y-m-d, kita perlu pastikan tanggalnya valid
                // Misalnya 1990-02-30 itu formatnya bener, tapi tanggalnya nggak ada
                if ($tglLahir) {
                    $dateParts = explode('-', $tglLahir);
                    // checkdate() akan return false kalau tanggal nggak valid
                    if (!checkdate((int)$dateParts[1], (int)$dateParts[2], (int)$dateParts[0])) {
                        $tglLahir = null;
                    }
                }

                // Kalau setelah semua usaha parse tetap null, berarti formatnya nggak dikenali
                if (!$tglLahir) {
                    $validator->errors()->add('tgl_lahir', 'Format tanggal tidak valid (gunakan Y-m-d atau d/m/Y).');
                }
            }

            // =============================================
            // CEK APAKAH ADA ERROR
            // =============================================
            // Kalau ada error (dari validator, duplikat file, prodi, tanggal, dll), skip baris ini
            if ($validator->fails()) {
                $this->failCount++;
                
                // Simpan info error buat ditampilkan ke user nanti
                $this->errors[] = [
                    'row'    => $rowNumber,           // Nomor baris di Excel
                    'npm'    => $npm ?: '-',         // NPM (atau '-' kalau kosong)
                    'nama'   => $nama ?: '-',         // Nama (atau '-' kalau kosong)
                    'errors' => $validator->errors()->all()  // Array berisi semua pesan error
                ];
                
                // Lanjut ke baris berikutnya, jangan simpan data ini
                continue;
            }

            // =============================================
            // SIMPAN DATA KE DATABASE
            // =============================================
            // Kalau sampai sini, berarti semua validasi lolos!
            try {
                // Mulai transaction biar kalau ada yang gagal, semua bisa di-rollback
                DB::beginTransaction();

                // Password default: tanggal lahir tanpa tanda strip
                // Contoh: 1990-01-15 jadi 19900115
                $passwordRaw = str_replace('-', '', $tglLahir);

                // Buat akun user dulu di tabel users
                // insertGetId buat langsung dapat ID yang baru di-insert
                $idUser = DB::table('users')->insertGetId([
                    'username'             => $npm,             // Username sama dengan NPM
                    'password'             => Hash::make($passwordRaw),  // Hash password-nya
                    'role'                 => 'mahasiswa',      // Role-nya mahasiswa
                    'must_change_password' => 1,                // Flag buat wajib ganti password saat pertama login
                ], 'id_user');  // Nama kolom primary key-nya 'id_user'

                // Setelah user dibuat, baru buat data mahasiswanya
                Mahasiswa::create([
                    'id_user'       => $idUser,               // Hubungkan dengan user yang baru dibuat
                    'nama'          => $nama,
                    'npm'           => $npm,
                    'tgl_lahir'     => $tglLahir,
                    'jenis_kelamin' => strtoupper($jenisKelamin),  // Pastikan huruf kapital (L atau P)
                    'id_prodi'      => $prodi->id_prodi,      // Ambil ID prodi dari objek yang udah dicari tadi
                    'angkatan'      => (int) $angkatan,       // Cast ke integer
                    'semester'      => (int) $semester,       // Cast ke integer
                    'kelas'         => strtoupper($kelas),    // Pastikan huruf kapital (A, B, C, atau D)
                    'no_hp'         => $noHp,
                    'email'         => $email,
                    'ttd_digital'   => null,                  // Belum ada TTD digital
                ]);

                // Kalau semua berhasil, commit transaction-nya
                DB::commit();
                
                // Tambah counter sukses
                $this->successCount++;

            } catch (\Exception $e) {
                // Kalau ada error saat menyimpan, batalkan semua yang udah dilakukan
                DB::rollBack();
                
                $this->failCount++;
                
                // Simpan error message buat ditampilkan ke user
                $this->errors[] = [
                    'row'    => $rowNumber,
                    'npm'    => $npm,
                    'nama'   => $nama,
                    'errors' => ['Gagal menyimpan ke database: ' . $e->getMessage()]
                ];
            }
        }
    }

    /**
     * Getter buat ambil semua error yang terjadi
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Getter buat ambil jumlah data yang berhasil disimpan
     */
    public function getSuccessCount()
    {
        return $this->successCount;
    }

    /**
     * Getter buat ambil jumlah data yang gagal
     */
    public function getFailCount()
    {
        return $this->failCount;
    }
}