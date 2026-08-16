<?php

namespace App\Http\Controllers; // Namespace untuk mengelompokkan controller

use Illuminate\Http\Request; // Import class Request untuk menangani HTTP request
use Illuminate\Support\Facades\DB; // Import facade DB untuk menjalankan query database
use Illuminate\Support\Facades\File; // Import facade File untuk operasi file dan direktori
use ZipArchive; // Import class ZipArchive untuk membuat file ZIP

class BackupController extends Controller // Deklarasi class BackupController
{
    /**
     * Tampilkan halaman backup beserta riwayat file
     * Menampilkan statistik database dan daftar file backup yang pernah dibuat
     */
    public function index()
    {
        // Tentukan path folder penyimpanan backup
        $backupDir = storage_path('backups');

        // Buat folder backups jika belum ada (dengan permission 0755 dan recursive)
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        // Ambil semua file backup, lalu filter dan urutkan
        $files = collect(File::files($backupDir)) // Kumpulkan semua file menjadi collection
            ->filter(function ($file) { // Filter hanya file .sql dan .zip
                return in_array($file->getExtension(), ['sql', 'zip']);
            })
            ->sortByDesc(function ($file) { // Urutkan dari terbaru berdasarkan waktu modifikasi
                return $file->getMTime();
            })
            ->map(function ($file, $index) { // Transformasi setiap file menjadi array informasi
                $ext = $file->getExtension(); // Ambil ekstensi file
                return [
                    'no'            => $index + 1, // Nomor urut
                    'filename'      => $file->getFilename(), // Nama file
                    'size'          => $this->formatFileSize($file->getSize()), // Ukuran file yang sudah diformat
                    'type'          => $ext === 'sql' ? 'Database' : 'Source Code', // Tipe berdasarkan ekstensi
                    'type_class'    => $ext === 'sql' // Class CSS untuk badge warna
                        ? 'bg-blue-50 text-blue-700 ring-blue-600/20'   // Biru untuk database
                        : 'bg-purple-50 text-purple-700 ring-purple-600/20', // Ungu untuk source code
                    'icon_color'    => $ext === 'sql' ? 'text-blue-500' : 'text-purple-500', // Warna icon
                    'created_at'    => date('d-m-Y H:i:s', $file->getMTime()), // Tanggal dibuat diformat
                ];
            })
            ->values(); // Reset index setelah filter

        // Ambil informasi statistik database menggunakan raw query
        $tables     = DB::select('SHOW TABLE STATUS'); // Status semua tabel (termasuk ukuran)
        $tableCount = count($tables); // Hitung jumlah tabel
        $dbSize     = 0; // Inisialisasi variabel ukuran database
        foreach ($tables as $table) {
            // Jumlahkan ukuran data + ukuran index untuk setiap tabel
            $dbSize += ($table->Data_length ?? 0) + ($table->Index_length ?? 0);
        }

        // Cari backup database terakhir dari koleksi file
        $lastDbBackup   = $files->where('type', 'Database')->first();
        // Cari backup source code terakhir dari koleksi file
        $lastCodeBackup = $files->where('type', 'Source Code')->first();

        // Tampilkan view dengan membawa semua data yang sudah disiapkan
        return view('pages.admin.backup.index', compact(
            'files', 'tableCount', 'dbSize', 'lastDbBackup', 'lastCodeBackup'
        ));
    }

    /**
     * Backup database — generate file .sql menggunakan raw query
     * File akan langsung di-download oleh browser
     */
    public function backupDatabase()
    {
        // Tentukan path folder backup
        $backupDir = storage_path('backups');
        
        // Buat folder jika belum ada
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        // Buat nama file dengan format: backup_db_simmagi_TANGGAL_JAM.sql
        $filename = 'backup_db_simmagi_' . date('Y-m-d_His') . '.sql';
        $filepath = $backupDir . '/' . $filename;

        // Generate isi file SQL
        $sql = $this->generateSqlDump();
        
        // Tulis isi SQL ke file
        File::put($filepath, $sql);

        // Kirim file sebagai download ke browser (deleteFileAfterSend = false agar file tersimpan di server)
        return response()->download($filepath, $filename)->deleteFileAfterSend(false);
    }

    /**
     * Backup source code — generate file .zip
     * Mengecualikan folder vendor, node_modules, .git, dll
     */
    public function backupSourceCode()
    {
        // Cek apakah ekstensi ZipArchive tersedia di server
        if (!class_exists('ZipArchive')) {
            return back()->with('error', 'Ekstensi ZipArchive tidak tersedia di server. Hubungi admin server.');
        }

        // Tentukan path folder backup
        $backupDir = storage_path('backups');
        
        // Buat folder jika belum ada
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        // Buat nama file dengan format: backup_source_simmagi_TANGGAL_JAM.zip
        $filename = 'backup_source_simmagi_' . date('Y-m-d_His') . '.zip';
        $filepath = $backupDir . '/' . $filename;

        // Inisialisasi ZipArchive
        $zip = new ZipArchive();
        
        // Buka/create file ZIP
        if ($zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Gagal membuat file backup source code.');
        }

        // Path root aplikasi
        $baseDir    = base_path();
        
        // Daftar folder yang TIDAK diikutkan dalam backup
        $excludeDirs = ['vendor', 'node_modules', '.git', 'storage/backups', 'storage/logs', 'storage/framework'];

        // Tambahkan file secara rekursif ke ZIP
        $this->addFilesToZip($zip, $baseDir, '', $excludeDirs);
        
        // Tutup file ZIP (proses penulisan sebenarnya terjadi di sini)
        $zip->close();

        // Validasi: pastikan file ZIP berhasil dibuat dan tidak kosong
        if (!File::exists($filepath) || File::size($filepath) === 0) {
            return back()->with('error', 'File backup kosong atau gagal dibuat.');
        }

        // Kirim file sebagai download (file tetap tersimpan di server)
        return response()->download($filepath, $filename)->deleteFileAfterSend(false);
    }

    /**
     * Download file backup yang sudah ada
     * Digunakan untuk mengunduh ulang file backup dari riwayat
     * 
     * @param string $filename - Nama file yang akan di-download
     */
    public function downloadBackup($filename)
    {
        // Validasi nama file: hanya boleh mengandung karakter aman
        // Mencegah directory traversal attack
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
            abort(400, 'Nama file tidak valid.');
        }

        // Bangun path lengkap file
        $filepath = storage_path('backups/' . $filename);
        
        // Cek apakah file benar-benar ada
        if (!File::exists($filepath)) {
            abort(404, 'File backup tidak ditemukan.');
        }

        // Kirim file sebagai download
        return response()->download($filepath);
    }

    /**
     * Hapus file backup
     * Menghapus file backup dari riwayat
     * 
     * @param Request $request - HTTP request
     * @param string $filename - Nama file yang akan dihapus
     */
    public function deleteBackup(Request $request, $filename)
    {
        // Validasi nama file untuk keamanan
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
            return back()->with('error', 'Nama file tidak valid.');
        }

        // Bangun path lengkap file
        $filepath = storage_path('backups/' . $filename);
        
        // Cek apakah file ada
        if (!File::exists($filepath)) {
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        // Hapus file
        File::delete($filepath);

        // Redirect kembali dengan pesan sukses
        return back()->with('success', "File backup \"{$filename}\" berhasil dihapus.");
    }

    // =========================================================================
    // PRIVATE HELPERS - Fungsi pembantu internal
    // =========================================================================

    /**
     * Generate full SQL dump dari semua tabel menggunakan raw query
     * Tidak menggunakan mysqldump, murni PHP + SQL query
     * 
     * @return string - Isi file SQL lengkap
     */
    private function generateSqlDump()
    {
        $dbName = env('DB_DATABASE'); // Ambil nama database dari .env
        $lines  = []; // Array untuk menampung setiap baris SQL

        // ---- Tulis header komentar file SQL ----
        $lines[] = "-- ========================================";
        $lines[] = "-- Backup Database : {$dbName}";
        $lines[] = "-- Sistem         : SIMMAGI";
        $lines[] = "-- Tanggal        : " . date('d-m-Y H:i:s');
        $lines[] = "-- Metode         : Raw SQL Query (NO Migration)";
        $lines[] = "-- ========================================";
        $lines[] = "";
        
        // ---- Tulis pengaturan SQL awal ----
        $lines[] = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";"; // Agar nilai 0 tidak di-skip pada AUTO_INCREMENT
        $lines[] = "SET AUTOCOMMIT = 0;"; // Nonaktifkan auto-commit untuk performa
        $lines[] = "START TRANSACTION;"; // Mulai transaksi
        $lines[] = "SET time_zone = \"+00:00\";"; // Set timezone UTC
        $lines[] = "SET FOREIGN_KEY_CHECKS = 0;"; // Nonaktifkan foreign key check saat import
        $lines[] = "";

        // ---- Ambil daftar semua tabel di database ----
        $allTables = DB::select("SHOW TABLES");
        
        // Key-nya dinamis sesuai nama database, contoh: "Tables_in_db_simmagi"
        // Ambil key pertama dari hasil query
        $tableKey = array_keys((array) $allTables[0])[0];

        // Loop setiap tabel untuk dump struktur dan data
        foreach ($allTables as $tableRow) {
            $tableName = $tableRow->$tableKey; // Ambil nama tabel

            // ---- Tulis komentar header tabel ----
            $lines[] = "-- --------------------------------------------------";
            $lines[] = "-- Struktur tabel `$tableName`";
            $lines[] = "-- --------------------------------------------------";
            $lines[] = "";
            $lines[] = "DROP TABLE IF EXISTS `$tableName`;"; // Hapus tabel jika sudah ada (untuk clean import)
            $lines[] = "";

            // ---- Ambil perintah CREATE TABLE ----
            $createResult = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createSql = '';
            foreach ($createResult[0] as $key => $value) {
                // Cari value yang bukan key 'Table' atau 'View' (itu adalah SQL-nya)
                if ($key !== 'Table' && $key !== 'View') {
                    $createSql = $value;
                    break; // Hapus loop setelah ketemu
                }
            }
            $lines[] = $createSql . ";"; // Tulis perintah CREATE TABLE
            $lines[] = "";

            // ---- Tulis komentar header data ----
            $lines[] = "-- --------------------------------------------------";
            $lines[] = "-- Data tabel `$tableName`";
            $lines[] = "-- --------------------------------------------------";
            $lines[] = "";

            // ---- Ambil semua data dari tabel ----
            $rows = DB::select("SELECT * FROM `{$tableName}`");

            if (!empty($rows)) { // Jika tabel tidak kosong
                $columns = array_keys((array) $rows[0]); // Ambil nama-nama kolom

                // Loop setiap baris data untuk dibuat INSERT statement
                foreach ($rows as $row) {
                    $values = [];
                    foreach (array_values((array) $row) as $val) {
                        if ($val === null) {
                            // Jika nilai NULL, tulis langsung NULL tanpa quote
                            $values[] = 'NULL';
                        } elseif (is_int($val) || is_float($val)) {
                            // Jika angka, tulis langsung tanpa quote
                            $values[] = $val;
                        } else {
                            // Jika string, escape karakter berbahaya dan bungkus dengan quote
                            $escaped = str_replace(
                                ['\\', "'", "\0", "\n", "\r", "\x1a"], // Karakter yang di-escape
                                ['\\\\', "\\'", '\\0', '\\n', '\\r', '\\Z'], // Hasil escape
                                $val
                            );
                            $values[] = "'{$escaped}'";
                        }
                    }
                    // Gabungkan menjadi satu baris INSERT statement
                    $lines[] = "INSERT INTO `{$tableName}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");";
                }
                $lines[] = "";
            } else { // Jika tabel kosong
                $lines[] = "-- Tabel `$tableName` kosong.";
                $lines[] = "";
            }
        }

        // ---- Tulis penutup SQL ----
        $lines[] = "SET FOREIGN_KEY_CHECKS = 1;"; // Aktifkan kembali foreign key check
        $lines[] = "COMMIT;"; // Commit transaksi

        // Gabungkan semua baris dengan newline dan kembalikan
        return implode("\n", $lines);
    }

    /**
     * Rekursif tambah file ke ZipArchive
     * Menelusuri direktori secara rekursif dan menambahkan file ke ZIP
     * 
     * @param ZipArchive $zip - Instance ZipArchive
     * @param string $sourcePath - Path folder yang sedang diproses
     * @param string $relativePath - Path relatif di dalam file ZIP
     * @param array $excludeDirs - Daftar folder yang dikecualikan
     */
    private function addFilesToZip(ZipArchive $zip, $sourcePath, $relativePath, array $excludeDirs)
    {
        $items = @scandir($sourcePath); // Baca isi direktori (@ untuk suppress warning)
        if ($items === false) return; // Jika gagal baca, hentikan rekursi

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue; // Lewati direktori . dan ..

            // Bangun path lengkap dan path di dalam ZIP
            $fullPath = $sourcePath . DIRECTORY_SEPARATOR . $item;
            $zipPath  = $relativePath !== '' ? $relativePath . '/' . $item : $item;

            if (is_dir($fullPath)) { // Jika item adalah direktori
                if (in_array($item, $excludeDirs)) continue; // Lewati jika ada di daftar exclude
                $zip->addEmptyDir($zipPath); // Tambahkan folder kosong ke ZIP
                $this->addFilesToZip($zip, $fullPath, $zipPath, $excludeDirs); // Rekursi ke dalam folder
            } else { // Jika item adalah file
                $zip->addFile($fullPath, $zipPath); // Tambahkan file ke ZIP
            }
        }
    }

    /**
     * Format ukuran file ke readable string
     * Mengkonversi bytes ke B, KB, MB, atau GB
     * 
     * @param int $bytes - Ukuran file dalam bytes
     * @return string - Ukuran yang sudah diformat (contoh: "2.45 MB")
     */
    private function formatFileSize($bytes)
    {
        if ($bytes === 0) return '0 B'; // Jika 0 bytes, kembalikan "0 B"
        $units = ['B', 'KB', 'MB', 'GB']; // Satuan ukuran
        $i = floor(log($bytes, 1024)); // Hitung indeks satuan yang sesuai
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i]; // Bulatkan 2 desimal
    }
}