<?php

namespace App\Imports;

use App\Models\Prodi;
use App\Models\Jurusan;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class untuk handle import data prodi dari file Excel
 * 
 * Prodi ini punya relasi dengan Jurusan, jadi perlu cek apakah jurusannya ada di database
 * Beda sama JurusanImport yang nggak punya relasi ke tabel lain
 */
class ProdiImport implements ToCollection, WithHeadingRow
{
    // Counter buat hitung berapa yang berhasil disimpan
    protected $successCount = 0;
    
    // Counter buat hitung berapa yang gagal
    protected $failCount = 0;
    
    // Tempat nyimpen error yang terjadi selama proses import
    protected $errors = [];

    /**
     * Array buat tracking duplikat dalam SATU file Excel
     */
    protected $seenKodeProdi = [];   // Format: ['ti' => 3] artinya kode prodi "TI" muncul pertama kali di baris 3
    protected $seenNamaProdi = [];  // Format: ['teknik informatika' => 5] artinya nama ini muncul pertama kali di baris 5

    /**
     * Method utama yang dipanggil pas import Excel
     */
    public function collection(Collection $rows)
    {
        // Reset counter dan tracker setiap kali import
        $this->successCount = 0;
        $this->failCount = 0;
        $this->errors = [];
        $this->seenKodeProdi = [];
        $this->seenNamaProdi = [];

        // Loop setiap baris di Excel
        foreach ($rows as $index => $row) {
            // +2 karena header baris 1, data mulai baris 2
            $rowNumber = $index + 2;

            // =============================================
            // AMBIL DAN BERSIHKAN DATA
            // =============================================
            // Prodi nggak perlu handle DateTime object karena nggak punya kolom tanggal
            // Jadi langsung ambil dan bersihkan data dari $row
            $jurusanInput     = trim($row['jurusan'] ?? '');       // Nama jurusan (bukan ID)
            $kodeProdi        = trim($row['kode_prodi'] ?? '');
            $namaProdi        = trim($row['nama_prodi'] ?? '');
            $jenjang          = trim(strtoupper($row['jenjang'] ?? ''));  // Langsung di-uppercase
            $semesterMagang   = trim($row['semester_magang'] ?? '');
            $semesterPengajuan = trim($row['semester_pengajuan_magang'] ?? '');

            // =============================================
            // SKIP BARIS KOSONG
            // =============================================
            // Baris yang tidak ada datanya sama sekali
            // Ini untuk menghindari baris kosong di bawah data Excel
            // Note: Untuk semester, cek pakai === '' atau === null karena 0 itu value yang valid
            $isRowEmpty = empty($jurusanInput) 
                       && empty($kodeProdi) 
                       && empty($namaProdi) 
                       && empty($jenjang) 
                       && ($semesterMagang === '' || $semesterMagang === null)
                       && ($semesterPengajuan === '' || $semesterPengajuan === null);

            if ($isRowEmpty) {
                // Langsung skip, tidak dihitung gagal
                continue;
            }

            // Array buat nampung error di baris ini
            $rowErrors = [];

            // =============================================
            // VALIDASI JURUSAN
            // =============================================
            // Prodi harus punya jurusan, jadi kita cek apakah jurusannya ada di database
            if (empty($jurusanInput)) {
                $rowErrors[] = 'Jurusan wajib diisi.';
            } else {
                // Cari jurusan berdasarkan nama
                $jurusan = Jurusan::where('nama_jurusan', $jurusanInput)->first();
                if (!$jurusan) {
                    // Kalau jurusan nggak ditemukan di database
                    $rowErrors[] = 'Jurusan "' . $jurusanInput . '" tidak ditemukan di database.';
                }
            }

            // =============================================
            // VALIDASI KODE PRODI
            // =============================================
            if (empty($kodeProdi)) {
                // Cek 1: Wajib diisi
                $rowErrors[] = 'Kode prodi wajib diisi.';
            } else {
                // Cek 2: Maksimal 30 karakter
                if (strlen($kodeProdi) > 30) {
                    $rowErrors[] = 'Kode prodi maksimal 30 karakter.';
                }

                // Cek 3: Duplikat dalam file Excel yang sama
                $kodeLower = Str::lower($kodeProdi);
                if (isset($this->seenKodeProdi[$kodeLower])) {
                    $rowErrors[] = 'Kode prodi "' . $kodeProdi . '" duplikat dalam file ini (baris ke-' . $this->seenKodeProdi[$kodeLower] . ').';
                } else {
                    // Cek 4: Duplikat di database
                    $existsInDb = Prodi::where('kode_prodi', $kodeProdi)->exists();
                    if ($existsInDb) {
                        $rowErrors[] = 'Kode prodi "' . $kodeProdi . '" sudah terdaftar di database.';
                    } else {
                        // Kalau lolos semua cek, simpen sebagai "sudah dilihat"
                        $this->seenKodeProdi[$kodeLower] = $rowNumber;
                    }
                }
            }

            // =============================================
            // VALIDASI NAMA PRODI
            // =============================================
            // Logikanya sama persis dengan validasi kode prodi di atas
            if (empty($namaProdi)) {
                // Cek 1: Wajib diisi
                $rowErrors[] = 'Nama prodi wajib diisi.';
            } else {
                // Cek 2: Maksimal 50 karakter
                if (strlen($namaProdi) > 50) {
                    $rowErrors[] = 'Nama prodi maksimal 50 karakter.';
                }

                // Cek 3: Duplikat dalam file Excel yang sama
                $namaLower = Str::lower($namaProdi);
                if (isset($this->seenNamaProdi[$namaLower])) {
                    $rowErrors[] = 'Nama prodi "' . $namaProdi . '" duplikat dalam file ini (baris ke-' . $this->seenNamaProdi[$namaLower] . ').';
                } else {
                    // Cek 4: Duplikat di database
                    $existsInDb = Prodi::where('nama_prodi', $namaProdi)->exists();
                    if ($existsInDb) {
                        $rowErrors[] = 'Nama prodi "' . $namaProdi . '" sudah terdaftar di database.';
                    } else {
                        // Kalau lolos semua cek, simpen sebagai "sudah dilihat"
                        $this->seenNamaProdi[$namaLower] = $rowNumber;
                    }
                }
            }

            // =============================================
            // VALIDASI JENJANG
            // =============================================
            // Di sistem ini, jenjang hanya boleh D3 atau D4
            if (empty($jenjang)) {
                $rowErrors[] = 'Jenjang wajib diisi.';
            } elseif (!in_array($jenjang, ['D3', 'D4'])) {
                $rowErrors[] = 'Jenjang hanya boleh diisi D3 atau D4.';
            }

            // =============================================
            // VALIDASI SEMESTER MAGANG
            // =============================================
            // Semester magang itu semester ke berapa mahasiswa harus magang
            // Biasanya di range 1-8
            if ($semesterMagang === '' || $semesterMagang === null) {
                // Cek pakai === '' karena empty(0) akan return true, padahal 0 itu invalid tapi bukan "kosong"
                $rowErrors[] = 'Semester magang wajib diisi.';
            } elseif (!is_numeric($semesterMagang)) {
                $rowErrors[] = 'Semester magang harus berupa angka.';
            } elseif ((int)$semesterMagang < 1 || (int)$semesterMagang > 8) {
                $rowErrors[] = 'Semester magang harus di antara 1 sampai 8.';
            }

            // =============================================
            // VALIDASI SEMESTER PENGAJUAN MAGANG
            // =============================================
            // Semester pengajuan itu semester ke berapa mahasiswa boleh mengajukan magang
            // Biasanya lebih awal dari semester magang (misal magang semester 6, pengajuan semester 5)
            if ($semesterPengajuan === '' || $semesterPengajuan === null) {
                $rowErrors[] = 'Semester pengajuan magang wajib diisi.';
            } elseif (!is_numeric($semesterPengajuan)) {
                $rowErrors[] = 'Semester pengajuan magang harus berupa angka.';
            } elseif ((int)$semesterPengajuan < 1 || (int)$semesterPengajuan > 8) {
                $rowErrors[] = 'Semester pengajuan magang harus di antara 1 sampai 8.';
            }

            // =============================================
            // SIMPAN ATAU TOLAK
            // =============================================
            if (empty($rowErrors)) {
                // Kalau nggak ada error sama sekali, simpan ke database
                try {
                    Prodi::create([
                        'id_jurusan'                => $jurusan->id_jurusan,   // Ambil ID jurusan dari objek yang udah dicari tadi
                        'kode_prodi'                => $kodeProdi,
                        'nama_prodi'                => $namaProdi,
                        'jenjang'                   => $jenjang,               // Sudah di-uppercase sebelumnya
                        'semester_magang'           => (int)$semesterMagang,   // Cast ke integer
                        'semester_pengajuan_magang' => (int)$semesterPengajuan, // Cast ke integer
                    ]);
                    $this->successCount++;
                } catch (\Exception $e) {
                    // Kalau ada error saat menyimpan
                    $this->failCount++;
                    $this->errors[] = [
                        'row'        => $rowNumber,
                        'kode_prodi' => $kodeProdi,
                        'nama_prodi' => $namaProdi,
                        'errors'     => ['Gagal menyimpan ke database: ' . $e->getMessage()],
                    ];
                }
            } else {
                // Kalau ada error, simpan info error buat ditampilkan ke user
                $this->failCount++;
                $this->errors[] = [
                    'row'        => $rowNumber,
                    'kode_prodi' => $kodeProdi ?: '-',    // Tampilkan '-' kalau kosong
                    'nama_prodi' => $namaProdi ?: '-',
                    'errors'     => $rowErrors,
                ];
            }
        }
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

    /**
     * Getter buat ambil semua error yang terjadi
     * Biasanya dipanggil di controller buat ditampilkan ke user
     */
    public function getErrors()
    {
        return $this->errors;
    }
}