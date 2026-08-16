<?php

namespace App\Imports;

use App\Models\Pegawai;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Class untuk handle import data pegawai dari file Excel
 * 
 * Mirip sama DosenImport, tapi lebih simpel karena:
 * - Pakai NIP (bukan NIDN)
 * - Nggak punya kolom prodi
 * - Role-nya 'pegawai' (bukan 'dosen')
 */
class PegawaiImport implements ToCollection, WithHeadingRow
{
    // Tempat nyimpen error yang terjadi selama proses import
    protected $errors = [];
    
    // Counter buat hitung berapa yang berhasil disimpan
    protected $successCount = 0;
    
    // Counter buat hitung berapa yang gagal
    protected $failCount = 0;

    /**
     * Array buat tracking duplikat dalam SATU file Excel
     * Tujuannya biar nggak ada NIP atau email yang sama di file yang sama
     */
    protected $seenNip = [];     // Format: ['123456789' => 3] artinya NIP ini muncul pertama kali di baris 3
    protected $seenEmail = [];  // Format: ['budi@email.com' => 5] artinya email ini muncul pertama kali di baris 5

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
        $this->seenNip = [];
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
            // Pegawai cuma punya 6 kolom, lebih sedikit dari dosen/mahasiswa
            $nip           = trim($rowData['nip'] ?? '');
            $nama          = trim($rowData['nama'] ?? '');
            $tglLahirRaw   = trim($rowData['tgl_lahir'] ?? '');
            $jenisKelamin  = trim($rowData['jenis_kelamin'] ?? '');
            $noHp          = trim($rowData['no_hp'] ?? '');
            $email         = trim($rowData['email'] ?? '');

            // =============================================
            // SKIP BARIS KOSONG
            // =============================================
            // Kadang di Excel ada baris kosong di bawah, ini buat skip baris tersebut
            // Baris dianggap kosong kalau SEMUA kolom penting kosong
            $isRowEmpty = empty($nip)
                       && empty($nama)
                       && empty($tglLahirRaw)
                       && empty($jenisKelamin)
                       && empty($noHp)
                       && empty($email);

            if ($isRowEmpty) {
                // Langsung lanjut ke baris berikutnya
                continue;
            }

            // =============================================
            // VALIDASI DATA MENGGUNAKAN LARAVEL VALIDATOR
            // =============================================
            $validator = Validator::make($rowData, [
                'nama'          => 'required|max:100',                                  // Nama wajib, maks 100 karakter (lebih longgar dari dosen)
                'nip'           => 'required|max:50|unique:pegawai,nip',                 // NIP wajib, maks 50, unik di tabel pegawai
                'tgl_lahir'     => 'required',                                           // Tanggal lahir wajib
                'jenis_kelamin' => 'required|in:L,P,l,p',                               // Harus L atau P
                'no_hp'         => 'required|max:20',                                    // No HP wajib, maks 20 karakter
                'email'         => 'required|email|max:100|unique:pegawai,email',        // Email wajib, format valid, unik
            ], [
                // Pesan error custom biar lebih ramah
                'nama.required'          => 'Nama wajib diisi.',
                'nama.max'               => 'Nama maksimal 100 karakter.',
                'nip.required'           => 'NIP wajib diisi.',
                'nip.max'                => 'NIP maksimal 50 karakter.',
                'nip.unique'             => 'NIP "' . $nip . '" sudah terdaftar di database.',
                'tgl_lahir.required'     => 'Tanggal lahir wajib diisi.',
                'jenis_kelamin.required' => 'Jenis kelamin wajib diisi.',
                'jenis_kelamin.in'       => 'Jenis kelamin harus L atau P.',
                'no_hp.required'         => 'No HP wajib diisi.',
                'no_hp.max'              => 'No HP maksimal 20 karakter.',
                'email.required'         => 'Email wajib diisi.',
                'email.email'            => 'Format email tidak valid.',
                'email.max'              => 'Email maksimal 100 karakter.',
                'email.unique'           => 'Email "' . $email . '" sudah terdaftar di database.',
            ]);

            // =============================================
            // CEK DUPLIKAT NIP DALAM FILE YANG SAMA
            // =============================================
            // Validator di atas cuma cek duplikat dengan database
            // Ini buat cek duplikat ANTAR BARIS dalam file Excel yang sama
            if (!empty($nip)) {
                $nipLower = Str::lower($nip);
                
                if (isset($this->seenNip[$nipLower])) {
                    $validator->errors()->add('nip', 'NIP "' . $nip . '" duplikat dalam file ini (baris ke-' . $this->seenNip[$nipLower] . ').');
                } else {
                    $this->seenNip[$nipLower] = $rowNumber;
                }
            }

            // =============================================
            // CEK DUPLIKAT EMAIL DALAM FILE YANG SAMA
            // =============================================
            if (!empty($email)) {
                $emailLower = Str::lower($email);
                
                if (isset($this->seenEmail[$emailLower])) {
                    $validator->errors()->add('email', 'Email "' . $email . '" duplikat dalam file ini (baris ke-' . $this->seenEmail[$emailLower] . ').');
                } else {
                    $this->seenEmail[$emailLower] = $rowNumber;
                }
            }

            // =============================================
            // PARSE TANGGAL LAHIR
            // =============================================
            // Excel punya banyak format tanggal, kita handle semuanya
            $tglLahir = null;
            
            if (!empty($tglLahirRaw)) {
                // 1. Format Y-m-d (contoh: 1990-01-15)
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tglLahirRaw)) {
                    $tglLahir = $tglLahirRaw;
                }
                // 2. Format Y-m-d H:i:s (contoh: 1990-01-15 00:00:00)
                elseif (preg_match('/^(\d{4}-\d{2}-\d{2})\s+\d{2}:\d{2}/', $tglLahirRaw, $m)) {
                    $tglLahir = $m[1];
                }
                // 3. Format d/m/Y (contoh: 15/01/1990) -> format umum di Indonesia
                elseif (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $tglLahirRaw, $m)) {
                    // Ubah dari d/m/Y jadi Y-m-d
                    $tglLahir = $m[3] . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
                }
                // 4. Format serial number Excel (contoh: 41275)
                elseif (is_numeric($tglLahirRaw) && (int) $tglLahirRaw > 10000) {
                    try {
                        $tglLahir = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $tglLahirRaw)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $tglLahir = null;
                    }
                }
                // 5. Last resort: coba parse pakai strtotime PHP
                elseif (($timestamp = @strtotime($tglLahirRaw)) !== false) {
                    $tglLahir = date('Y-m-d', $timestamp);
                }

                // Validasi tanggal yang udah di-parse
                // Pastikan tanggalnya benar-benar valid (misalnya 30 Februari itu nggak valid)
                if ($tglLahir) {
                    $dateParts = explode('-', $tglLahir);
                    if (!checkdate((int)$dateParts[1], (int)$dateParts[2], (int)$dateParts[0])) {
                        $tglLahir = null;
                    }
                }

                // Kalau tetap null setelah semua usaha parse, berarti formatnya nggak dikenali
                if (!$tglLahir) {
                    $validator->errors()->add('tgl_lahir', 'Format tanggal tidak valid (gunakan Y-m-d atau d/m/Y).');
                }
            }

            // =============================================
            // CEK APAKAH ADA ERROR
            // =============================================
            if ($validator->fails()) {
                $this->failCount++;
                
                $this->errors[] = [
                    'row'    => $rowNumber,
                    'nip'    => $nip ?: '-',
                    'nama'   => $nama ?: '-',
                    'errors' => $validator->errors()->all()
                ];
                
                // Skip baris ini, lanjut ke baris berikutnya
                continue;
            }

            // =============================================
            // SIMPAN DATA KE DATABASE
            // =============================================
            try {
                // Mulai transaction
                DB::beginTransaction();

                // Password default: tanggal lahir tanpa strip
                // Contoh: 1990-01-15 jadi 19900115
                $passwordRaw = str_replace('-', '', $tglLahir);

                // Buat akun user dulu
                $idUser = DB::table('users')->insertGetId([
                    'username'             => $nip,             // Username = NIP
                    'password'             => Hash::make($passwordRaw),
                    'role'                 => 'pegawai',        // Role-nya pegawai
                    'must_change_password' => 1,                // Wajib ganti password saat pertama login
                ], 'id_user');

                // Setelah user dibuat, baru buat data pegawainya
                Pegawai::create([
                    'id_user'       => $idUser,
                    'nip'           => $nip,
                    'nama'          => $nama,
                    'tgl_lahir'     => $tglLahir,
                    'jenis_kelamin' => strtoupper($jenisKelamin),  // Pastikan kapital (L/P)
                    'no_hp'         => $noHp,
                    'email'         => $email,
                    'ttd_digital'   => null,                      // Belum ada TTD digital
                ]);

                // Kalau berhasil, commit
                DB::commit();
                $this->successCount++;

            } catch (\Exception $e) {
                // Kalau gagal, rollback
                DB::rollBack();
                
                $this->failCount++;
                $this->errors[] = [
                    'row'    => $rowNumber,
                    'nip'    => $nip,
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