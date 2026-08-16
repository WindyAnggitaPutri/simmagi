<?php

namespace App\Imports;

use App\Models\Jurusan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Class untuk handle import data jurusan dari file Excel
 * 
 * Bedanya sama import lain, ini pakai validasi manual (bukan Validator bawaan Laravel)
 * karena datanya cuma 2 kolom jadi lebih simpel pakai if-else
 */
class JurusanImport implements ToCollection, WithHeadingRow
{
    // Counter buat hitung berapa yang berhasil disimpan
    protected $successCount = 0;
    
    // Counter buat hitung berapa yang gagal
    protected $failCount = 0;
    
    // Tempat nyimpen error yang terjadi selama proses import
    protected $errors = [];

    /**
     * Array buat tracking duplikat dalam SATU file Excel
     * Tujuannya biar nggak ada kode/nama jurusan yang sama di dalam file yang sama
     */
    protected $seenKodeJurusan = [];   // Format: ['ti' => 3] artinya kode "TI" muncul pertama kali di baris 3
    protected $seenNamaJurusan = [];  // Format: ['teknik informatika' => 5] artinya nama ini muncul pertama kali di baris 5

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
        $this->seenKodeJurusan = [];
        $this->seenNamaJurusan = [];

        // Loop setiap baris di Excel
        foreach ($rows as $index => $row) {
            // Baris 1 di Excel itu header, jadi data pertama itu baris ke-2
            $rowNumber = $index + 2;

            // =============================================
            // HANDLE FORMAT TANGGAL DARI EXCEL
            // =============================================
            // Meskipun jurusan nggak punya kolom tanggal, tetap kita handle
            // karena kadang Excel ngasih format aneh ke semua kolom
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
            // AMBIL DAN BERSIHKAN DATA
            // =============================================
            // trim() buat hilangkan spasi berlebih di awal dan akhir
            // ?? '' buat jaga-jaga kalau kolomnya nggak ada
            $kodeJurusan = trim($rowData['kode_jurusan'] ?? '');
            $namaJurusan = trim($rowData['nama_jurusan'] ?? '');

            // =============================================
            // SKIP BARIS KOSONG
            // =============================================
            // Kadang di Excel ada baris kosong di bawah data, ini buat skip baris tersebut
            // Baris dianggap kosong kalau kode DAN nama jurusannya kosong
            $isRowEmpty = empty($kodeJurusan) && empty($namaJurusan);

            if ($isRowEmpty) {
                // Langsung lanjut ke baris berikutnya
                continue;
            }

            // Array buat nampung error di baris ini
            // Beda sama import lain yang pakai Validator, ini pakai array biasa
            $rowErrors = [];

            // =============================================
            // VALIDASI KODE JURUSAN
            // =============================================
            if (empty($kodeJurusan)) {
                // Cek 1: Wajib diisi
                $rowErrors[] = 'Kode jurusan wajib diisi.';
            } else {
                // Cek 2: Maksimal 30 karakter
                if (strlen($kodeJurusan) > 30) {
                    $rowErrors[] = 'Kode jurusan maksimal 30 karakter.';
                }

                // Cek 3: Duplikat dalam file Excel yang sama
                // Pakai lowercase biar "TI" dan "ti" dianggap sama
                $kodeLower = Str::lower($kodeJurusan);
                if (isset($this->seenKodeJurusan[$kodeLower])) {
                    // Kalau kode ini udah pernah muncul sebelumnya di file
                    $rowErrors[] = 'Kode jurusan "' . $kodeJurusan . '" duplikat dalam file ini (baris ke-' . $this->seenKodeJurusan[$kodeLower] . ').';
                } else {
                    // Cek 4: Duplikat di database
                    // Ini baru cek ke database kalau di file nggak duplikat
                    $existsInDb = Jurusan::where('kode_jurusan', $kodeJurusan)->exists();
                    if ($existsInDb) {
                        $rowErrors[] = 'Kode jurusan "' . $kodeJurusan . '" sudah terdaftar di database.';
                    } else {
                        // Kalau lolos semua cek, simpen sebagai "sudah dilihat"
                        $this->seenKodeJurusan[$kodeLower] = $rowNumber;
                    }
                }
            }

            // =============================================
            // VALIDASI NAMA JURUSAN
            // =============================================
            // Logikanya sama persis dengan validasi kode jurusan di atas
            if (empty($namaJurusan)) {
                // Cek 1: Wajib diisi
                $rowErrors[] = 'Nama jurusan wajib diisi.';
            } else {
                // Cek 2: Maksimal 50 karakter
                if (strlen($namaJurusan) > 50) {
                    $rowErrors[] = 'Nama jurusan maksimal 50 karakter.';
                }

                // Cek 3: Duplikat dalam file Excel yang sama
                $namaLower = Str::lower($namaJurusan);
                if (isset($this->seenNamaJurusan[$namaLower])) {
                    $rowErrors[] = 'Nama jurusan "' . $namaJurusan . '" duplikat dalam file ini (baris ke-' . $this->seenNamaJurusan[$namaLower] . ').';
                } else {
                    // Cek 4: Duplikat di database
                    $existsInDb = Jurusan::where('nama_jurusan', $namaJurusan)->exists();
                    if ($existsInDb) {
                        $rowErrors[] = 'Nama jurusan "' . $namaJurusan . '" sudah terdaftar di database.';
                    } else {
                        // Kalau lolos semua cek, simpen sebagai "sudah dilihat"
                        $this->seenNamaJurusan[$namaLower] = $rowNumber;
                    }
                }
            }

            // =============================================
            // SIMPAN ATAU TOLAK
            // =============================================
            if (empty($rowErrors)) {
                // Kalau nggak ada error sama sekali, simpan ke database
                try {
                    Jurusan::create([
                        'kode_jurusan' => $kodeJurusan,
                        'nama_jurusan' => $namaJurusan,
                    ]);
                    $this->successCount++;
                } catch (\Exception $e) {
                    // Kalau ada error saat menyimpan (misal: error database)
                    $this->failCount++;
                    $this->errors[] = [
                        'row'          => $rowNumber,
                        'kode_jurusan' => $kodeJurusan,
                        'nama_jurusan' => $namaJurusan,
                        'errors'       => ['Gagal menyimpan ke database: ' . $e->getMessage()],
                    ];
                }
            } else {
                // Kalau ada error, simpan info error buat ditampilkan ke user
                $this->failCount++;
                $this->errors[] = [
                    'row'          => $rowNumber,
                    'kode_jurusan' => $kodeJurusan ?: '-',    // Tampilkan '-' kalau kosong
                    'nama_jurusan' => $namaJurusan ?: '-',
                    'errors'       => $rowErrors,
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