<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\Prodi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Class untuk handle import data dosen dari file Excel
 * 
 * Class ini bakal ngebaca Excel baris per baris, validasi datanya,
 * lalu simpan ke database kalau semuanya valid.
 */
class DosenImport implements ToCollection, WithHeadingRow
{
    // Tempat nyimpen error yang terjadi selama proses import
    protected $errors = [];
    
    // Counter buat hitung berapa yang berhasil disimpan
    protected $successCount = 0;
    
    // Counter buat hitung berapa yang gagal
    protected $failCount = 0;

    /**
     * Array buat nyimpen NIDN yang udah ditemui di file Excel
     * Tujuannya buat cek duplikat dalam SATU file yang sama
     * Format: ['nidn_123' => 3] -> artinya NIDN ini muncul pertama kali di baris 3
     */
    protected $seenNidn = [];
    
    // Sama seperti seenNidn, tapi buat email
    protected $seenEmail = [];

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
        $this->seenNidn = [];
        $this->seenEmail = [];

        // Loop setiap baris di Excel
        foreach ($rows as $index => $row) {
            // Baris 1 di Excel itu header, jadi data pertama itu baris ke-2
            // Makanya kita tambahin 2 biar nomor barisnya sesuai dengan yang di Excel
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
            // ?? '' buat jaga-jaga kalau kolomnya nggak ada, isi dengan string kosong
            $nidn          = trim($rowData['nidn'] ?? '');
            $nama          = trim($rowData['nama'] ?? '');
            $tglLahirRaw   = trim($rowData['tgl_lahir'] ?? '');
            $jenisKelamin  = trim($rowData['jenis_kelamin'] ?? '');
            $prodiInput    = trim($rowData['prodi'] ?? '');
            $noHp          = trim($rowData['no_hp'] ?? '');
            $email         = trim($rowData['email'] ?? '');

            // =============================================
            // SKIP BARIS KOSONG
            // =============================================
            // Kadang di Excel ada baris kosong di bawah, ini buat skip baris tersebut
            // Baris dianggap kosong kalau SEMUA kolom penting kosong
            $isRowEmpty = empty($nidn)
                       && empty($nama)
                       && empty($tglLahirRaw)
                       && empty($jenisKelamin)
                       && empty($prodiInput)
                       && empty($noHp)
                       && empty($email);

            if ($isRowEmpty) {
                // Langsung lanjut ke baris berikutnya, skip baris kosong ini
                continue;
            }

            // =============================================
            // VALIDASI DATA MENGGUNAKAN LARAVEL VALIDATOR
            // =============================================
            // Ini validasi standar Laravel, kayak di form biasa
            $validator = Validator::make($rowData, [
                'nama'          => 'required|max:50',                              // Nama wajib isi, maks 50 karakter
                'nidn'          => 'required|max:50|unique:dosen,nidn',            // NIDN wajib, unik di tabel dosen
                'tgl_lahir'     => 'required',                                     // Tanggal lahir wajib
                'jenis_kelamin' => 'required|in:L,P,l,p',                          // Harus L atau P (case insensitive)
                'prodi'         => 'required',                                     // Prodi wajib
                'no_hp'         => 'required|max:20',                              // No HP wajib, maks 20 karakter
                'email'         => 'required|email|max:100|unique:dosen,email',    // Email wajib, format valid, unik
            ], [
                // Pesan error custom biar lebih ramah dan mudah dipahami user
                'nama.required'          => 'Nama wajib diisi.',
                'nama.max'               => 'Nama maksimal 50 karakter.',
                'nidn.required'          => 'NIDN wajib diisi.',
                'nidn.max'               => 'NIDN maksimal 50 karakter.',
                'nidn.unique'            => 'NIDN "' . $nidn . '" sudah terdaftar di database.',
                'tgl_lahir.required'     => 'Tanggal lahir wajib diisi.',
                'jenis_kelamin.required' => 'Jenis kelamin wajib diisi.',
                'jenis_kelamin.in'       => 'Jenis kelamin harus L atau P.',
                'prodi.required'         => 'Prodi wajib diisi.',
                'no_hp.required'         => 'No HP wajib diisi.',
                'no_hp.max'              => 'No HP maksimal 20 karakter.',
                'email.required'         => 'Email wajib diisi.',
                'email.email'            => 'Format email tidak valid.',
                'email.max'              => 'Email maksimal 100 karakter.',
                'email.unique'           => 'Email "' . $email . '" sudah terdaftar di database.',
            ]);

            // =============================================
            // CEK DUPLIKAT NIDN DALAM FILE YANG SAMA
            // =============================================
            // Validator di atas cuma cek duplikat dengan database
            // Nah, ini buat cek duplikat ANTAR BARIS dalam file Excel yang sama
            if (!empty($nidn)) {
                // Ubah ke lowercase biar "NIDN123" dan "nidn123" dianggap sama
                $nidnLower = Str::lower($nidn);
                
                if (isset($this->seenNidn[$nidnLower])) {
                    // Kalau NIDN ini udah pernah muncul sebelumnya, tambah error
                    // Tunjukkan juga di baris mana NIDN ini pertama kali muncul
                    $validator->errors()->add('nidn', 'NIDN "' . $nidn . '" duplikat dalam file ini (baris ke-' . $this->seenNidn[$nidnLower] . ').');
                } else {
                    // Kalau belum pernah muncul, simpen sebagai "sudah dilihat"
                    $this->seenNidn[$nidnLower] = $rowNumber;
                }
            }

            // =============================================
            // CEK DUPLIKAT EMAIL DALAM FILE YANG SAMA
            // =============================================
            // Logikanya sama persis dengan cek duplikat NIDN di atas
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
            // Kalau ada error (dari validator, duplikat file, prodi, atau tanggal), skip baris ini
            if ($validator->fails()) {
                $this->failCount++;
                
                // Simpan info error buat ditampilkan ke user nanti
                $this->errors[] = [
                    'row'    => $rowNumber,           // Nomor baris di Excel
                    'nidn'   => $nidn ?: '-',         // NIDN (atau '-' kalau kosong)
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
                    'username'             => $nidn,           // Username sama dengan NIDN
                    'password'             => Hash::make($passwordRaw),  // Hash password-nya
                    'role'                 => 'dosen',         // Role-nya dosen
                    'must_change_password' => 1,               // Flag buat wajib ganti password saat pertama login
                ], 'id_user');  // Nama kolom primary key-nya 'id_user'

                // Setelah user dibuat, baru buat data dosennya
                Dosen::create([
                    'id_user'       => $idUser,               // Hubungkan dengan user yang baru dibuat
                    'nidn'          => $nidn,
                    'nama'          => $nama,
                    'tgl_lahir'     => $tglLahir,
                    'jenis_kelamin' => strtoupper($jenisKelamin),  // Pastikan huruf kapital (L atau P)
                    'id_prodi'      => $prodi->id_prodi,      // Ambil ID prodi dari objek yang udah dicari tadi
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
                    'nidn'   => $nidn,
                    'nama'   => $nama,
                    'errors' => ['Gagal menyimpan ke database: ' . $e->getMessage()]
                ];
            }
        }
    }

    /**
     * Getter buat ambil semua error yang terjadi
     * Biasanya dipanggil di controller buat ditampilkan ke user
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