<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Imports\MahasiswaImport;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Controller ini khusus buat Admin untuk ngelola data Mahasiswa (CRUD + Import Excel)
 * 
 * Fiturnya:
 * - Lihat daftar mahasiswa (dengan filter search, angkatan, prodi)
 * - Tambah mahasiswa baru (otomatis buat akun user)
 * - Edit mahasiswa (password di-reset ke tanggal lahir, akun ditandai wajib ganti password)
 * - Hapus mahasiswa (akun user-nya ikut terhapus)
 * - Import dari file Excel
 * - Download template Excel
 */
class MahasiswaController extends Controller
{
    /**
     * Halaman daftar mahasiswa
     * 
     * Bisa difilter berdasarkan:
     * - Kata kunci (nama, npm, atau nama prodi)
     * - Angkatan/tahun masuk
     * - Program studi
     * 
     * Mengambil SEMUA data (get, bukan paginate) karena filter dilakukan di client-side
     */
    public function index(Request $request)
    {
        $query = Mahasiswa::with('prodi');

        // Filter pencarian: cari di nama, npm, atau nama prodi
        if ($request->has('search') && $request->search != '') {
            $query->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('npm', 'like', '%' . $request->search . '%')
                  ->orWhereHas('prodi', function($q) use ($request) {
                      $q->where('nama_prodi', 'like', '%' . $request->search . '%');
                  });
        }

        // Filter berdasarkan angkatan
        if ($request->has('filter_tahun') && $request->filter_tahun != '') {
            $query->where('angkatan', $request->filter_tahun);
        }

        // Filter berdasarkan prodi
        if ($request->has('filter_prodi') && $request->filter_prodi != '') {
            $query->whereHas('prodi', function($q) use ($request) {
                $q->where('id_prodi', $request->filter_prodi);
            });
        }

        $mahasiswa = $query->orderBy('id_mahasiswa', 'asc')->get();

        // Ambil list angkatan unik untuk dropdown filter (diurutkan dari terbaru)
        $listAngkatan = Mahasiswa::select('angkatan')
                            ->distinct()
                            ->orderBy('angkatan', 'desc')
                            ->pluck('angkatan');

        // Ambil list prodi untuk dropdown filter
        $listProdi = Prodi::orderBy('nama_prodi', 'asc')->get();

        return view('pages.admin.mahasiswa.index', compact('mahasiswa', 'listAngkatan', 'listProdi'));
    }

    /**
     * Halaman form tambah mahasiswa baru
     */
    public function create()
    {
        // Ambil semua prodi untuk dropdown pilihan
        $prodi = Prodi::orderBy('nama_prodi', 'asc')->get();
        return view('pages.admin.mahasiswa.create', compact('prodi'));
    }

    /**
     * Proses menyimpan mahasiswa baru
     * 
     * Yang terjadi di belakang layar:
     * 1. Validasi semua input
     * 2. Buat akun user di tabel users (username = NPM, password = tanggal lahir tanpa strip)
     * 3. Upload TTD digital kalau ada
     * 4. Simpan data mahasiswa
     * 
     * Akun user dibuat dengan must_change_password = 1, artinya saat pertama kali login
     * mahasiswa akan dipaksa ganti password dulu
     */
    public function store(Request $request)
    {
        // Pesan error kustom dalam Bahasa Indonesia (biar user-friendly)
        $messages = [
            'required' => ':attribute wajib diisi.',
            'string'   => ':attribute harus berupa teks.',
            'max'      => ':attribute tidak boleh lebih dari :max karakter.',
            'date'     => ':attribute harus berupa tanggal yang valid.',
            'in'       => 'Pilihan :attribute tidak valid.',
            'exists'   => ':attribute tidak ditemukan.',
            'email'    => 'Format :attribute tidak valid.',
            'unique'   => ':attribute sudah terdaftar.',
            'integer'  => ':attribute harus berupa angka.',
            'min'      => ':attribute minimal :min.',
            'file'     => ':attribute harus berupa file.',
            'regex'    => ':attribute hanya boleh berisi angka.',
            'ttd_digital.max' => 'Ukuran file TTD Digital maksimal 2MB.',
        ];

        // Label kustom untuk setiap field (biar pesan error lebih rapi)
        $customAttributes = [
            'nama' => 'Nama Lengkap',
            'npm' => 'NPM',
            'tgl_lahir' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
            'id_prodi' => 'Program Studi',
            'angkatan' => 'Angkatan',
            'semester' => 'Semester',
            'kelas' => 'Kelas',
            'no_hp' => 'No. HP',
            'email' => 'Email',
            'ttd_digital' => 'TTD Digital',
        ];

        // Validasi input dari form
        $validated = $request->validate([
            'nama'          => 'required|string|max:100',
            'npm'           => 'required|string|max:50|unique:mahasiswa,npm', // NPM harus unik
            'tgl_lahir'     => 'required|date',
            'jenis_kelamin' => 'required|in:L,P', // Hanya L atau P
            'id_prodi'      => 'required|exists:prodi,id_prodi', // Prodi harus ada di tabel
            'angkatan'      => 'required|integer|min:2020|max:2100',
            'semester'      => 'required|integer|min:1|max:14',
            'kelas'         => 'required|in:A,B,C,D',
            'no_hp'         => 'required|string|max:15|regex:/^[0-9]+$/', // Hanya angka
            'email'         => 'required|email|max:100|unique:mahasiswa,email', // Email harus unik
            'ttd_digital'   => 'nullable|file|max:2048', // Opsional, maks 2MB
        ], $messages, $customAttributes);

        // Buat password dari tanggal lahir (hapus tanda strip, misal: 2005-01-15 -> 20050115)
        $passwordRaw = str_replace('-', '', $request->tgl_lahir);

        // Buat akun user di tabel users
        // insertGetId dipakai biar langsung dapet ID user yang baru dibuat
        $idUser = DB::table('users')->insertGetId([
            'username' => $request->npm, // Username = NPM
            'password' => Hash::make($passwordRaw), // Password = tanggal lahir (di-hash)
            'role'     => 'mahasiswa',
            'must_change_password' => 1, // Wajib ganti password saat pertama login
        ], 'id_user');

        // Upload TTD digital kalau ada
        $namaTTD = null;
        if ($request->hasFile('ttd_digital')) {
            $file = $request->file('ttd_digital');
            $namaTTD = $file->getClientOriginalName();// Nama file: timestamp_namaasli
            $file->storeAs('ttd_digital', $namaTTD, 'public');
        }

        // Simpan data mahasiswa, gabungkan dengan data yang sudah divalidasi
        Mahasiswa::create(array_merge($validated, [
            'id_user' => $idUser, // Hubungkan dengan akun user yang baru dibuat
            'ttd_digital' => $namaTTD,
        ]));

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', 'Mahasiswa berhasil ditambahkan');
    }

    /**
     * Halaman form edit mahasiswa
     */
    public function edit($id_mahasiswa)
    {
        $mahasiswa = Mahasiswa::findOrFail($id_mahasiswa);
        $prodi = Prodi::orderBy('nama_prodi', 'asc')->get();
        return view('pages.admin.mahasiswa.edit', compact('mahasiswa', 'prodi'));
    }

    /**
     * Proses update data mahasiswa
     * 
     * Yang perlu diperhatikan:
     * - Kalau NPM berubah, akun user juga ikut diupdate (username baru = NPM baru)
     * - Password SELALU di-reset ke tanggal lahir (walau gak diubah)
     * - must_change_password di-set ke 1 (paksa ganti password lagi)
     * - Kalau ada foto TTD baru, foto lama dihapus dari storage
     */
    public function update(Request $request, $id_mahasiswa)
    {
        // Pesan error dan label kustom (sama seperti store)
        $messages = [
            'required' => ':attribute wajib diisi.',
            'string'   => ':attribute harus berupa teks.',
            'max'      => ':attribute tidak boleh lebih dari :max karakter.',
            'date'     => ':attribute harus berupa tanggal yang valid.',
            'in'       => 'Pilihan :attribute tidak valid.',
            'exists'   => ':attribute tidak ditemukan.',
            'email'    => 'Format :attribute tidak valid.',
            'unique'   => ':attribute sudah digunakan.',
            'integer'  => ':attribute harus berupa angka.',
            'min'      => ':attribute minimal :min.',
            'file'     => ':attribute harus berupa file.',
            'regex'    => ':attribute hanya boleh berisi angka.',
            'ttd_digital.max' => 'Ukuran file TTD Digital maksimal 2MB.',
        ];

        $customAttributes = [
            'nama' => 'Nama Lengkap',
            'npm' => 'NPM',
            'tgl_lahir' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
            'id_prodi' => 'Program Studi',
            'angkatan' => 'Angkatan',
            'semester' => 'Semester',
            'kelas' => 'Kelas',
            'no_hp' => 'No. HP',
            'email' => 'Email',
            'ttd_digital' => 'TTD Digital',
        ];

        $mahasiswa = Mahasiswa::findOrFail($id_mahasiswa);

        // Validasi: unique NPM dan email, tapi ABaIKAN record yang sedang diedit
        // (kalau gak di-ignore, bakal error "sudah terdaftar" karena NPM-nya sama dengan dirinya sendiri)
        $validated = $request->validate([
            'nama'          => 'required|string|max:100',
            'npm'           => 'required|string|max:50|unique:mahasiswa,npm,' . $id_mahasiswa . ',id_mahasiswa',
            'tgl_lahir'     => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'id_prodi'      => 'required|exists:prodi,id_prodi',
            'angkatan'      => 'required|integer|min:2020|max:2100',
            'semester'      => 'required|integer|min:1|max:14',
            'kelas'         => 'required|in:A,B,C,D',
            'no_hp'         => 'required|string|max:15|regex:/^[0-9]+$/',
            'email'         => 'required|email|max:100|unique:mahasiswa,email,' . $id_mahasiswa . ',id_mahasiswa',
            'ttd_digital'   => 'nullable|file|max:2048',
        ], $messages, $customAttributes);

        // Kalau ada foto TTD baru, hapus foto lama dulu
        if ($request->hasFile('ttd_digital')) {
            if ($mahasiswa->ttd_digital && file_exists(storage_path('app/public/ttd_digital/' . $mahasiswa->ttd_digital))) {
                unlink(storage_path('app/public/ttd_digital/' . $mahasiswa->ttd_digital));
            }
            
            $file = $request->file('ttd_digital');
             $namaTTD = $file->getClientOriginalName();
            $file->storeAs('ttd_digital', $namaTTD, 'public');
            $validated['ttd_digital'] = $namaTTD;
        }

        // Update data mahasiswa
        $mahasiswa->update($validated);

        // Update akun user juga: username, password (reset ke tanggal lahir), dan wajib ganti password
        if ($mahasiswa->id_user) {
            $passwordRaw = str_replace('-', '', $request->tgl_lahir);
            
            DB::table('users')
                ->where('id_user', $mahasiswa->id_user)
                ->update([
                    'username' => $request->npm,
                    'password' => Hash::make($passwordRaw),
                    'must_change_password' => 1,
                ]);
        }

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil diperbarui');
    }

    /**
     * Hapus mahasiswa
     * 
     * Data mahasiswa di tabel mahasiswa DAN akun user-nya di tabel users ikut terhapus
     */
    public function destroy($id_mahasiswa)
    {
        $mahasiswa = Mahasiswa::findOrFail($id_mahasiswa);

        $mahasiswa->delete();

        // Hapus akun user-nya juga kalau ada
        if ($mahasiswa->id_user) {
            DB::table('users')->where('id_user', $mahasiswa->id_user)->delete();
        }

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', 'Mahasiswa berhasil dihapus');
    }

    /**
     * Halaman form import mahasiswa dari Excel
     */
    public function showImportForm()
    {
        $prodi = Prodi::orderBy('nama_prodi', 'asc')->get();
        return view('pages.admin.mahasiswa.import', compact('prodi'));
    }

    /**
     * Proses import data mahasiswa dari file Excel
     * 
     * Menggunakan package Laravel Excel (Maatwebsite)
     * Setelah selesai, ditampilkan ringkasan: berapa berhasil, berapa gagal
     * Kalau ada error per-baris, disimpan di session untuk ditampilkan
     */
    public function importExcel(Request $request)
    {
        // Validasi file: wajib, format Excel, maks 5MB
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file_excel.required' => 'File Excel wajib dipilih',
            'file_excel.mimes'    => 'File harus berupa format Excel (.xlsx, .xls, .csv)',
            'file_excel.max'      => 'Ukuran file maksimal 5MB',
        ]);

        // Inisialisasi import class
        $import = new MahasiswaImport();
        
        try {
            // Proses import file Excel
            Excel::import($import, $request->file('file_excel'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Kalau ada error validasi di file (format kolom salah, dll)
            $failures = $e->failures();
            return redirect()->back()->with('error', 'Format file tidak sesuai')->withErrors($e->errors());
        } catch (\Exception $e) {
            // Kalau ada error lain (file rusak, dll)
            return redirect()->back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        // Ambil hasil import: jumlah berhasil, gagal, dan daftar error
        $successCount = $import->getSuccessCount();
        $failCount = $import->getFailCount();
        $errors = $import->getErrors();

        // Simpan error ke session kalau ada (untuk ditampilkan di view)
        if (!empty($errors)) {
            session()->flash('import_errors', $errors);
        }

        // Susun pesan hasil
        $message = "Import selesai! Berhasil: {$successCount} data";
        if ($failCount > 0) {
            $message .= ", Gagal: {$failCount} data";
        }

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', $message)
            ->with('import_done', true);
    }

    /**
     * Download template Excel untuk import mahasiswa
     * 
     * Template ini berisi:
     * - Sheet 1: Header kolom + 1 baris contoh data
     * - Sheet 2: Petunjuk pengisian
     * 
     * Beberapa kolom diformat sebagai TEXT (bukan angka) supaya
     * NPM, tanggal lahir, dan No HP gak keubah formatnya oleh Excel
     * (misalnya NPM 250102072 gak jadi 2.50102E+08)
     */
    public function downloadTemplate()
    {
        // Ambil data prodi untuk contoh di template
        $prodi = Prodi::orderBy('nama_prodi', 'asc')->get();

        // Buat spreadsheet baru
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header kolom di baris 1
        $headers = ['nama', 'npm', 'tgl_lahir', 'jenis_kelamin', 'prodi', 'angkatan', 'semester', 'kelas', 'no_hp', 'email'];
        $columnLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

        // Styling header: teks putih, background oranye, rata tengah
        foreach ($headers as $index => $header) {
            $sheet->setCellValue($columnLetters[$index] . '1', $header);
            $sheet->getStyle($columnLetters[$index] . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'F97316']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ]);
        }

        // Set format kolom tertentu sebagai TEXT (biar gak keubah oleh Excel)
        // Ini penting untuk NPM (bisa diawali 0), tanggal, dan No HP
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->getStyle('C')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->getStyle('I')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // Isi baris contoh data di baris 2
        // setCellValueExplicit dipakai untuk memastikan data disimpan sebagai TEKS, bukan angka/tanggal
        $sheet->setCellValue('A2', 'Windy Anggita Putri');
        $sheet->setCellValueExplicit('B2', '250102072', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C2', '2005-01-15', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('D2', 'P');
        $sheet->setCellValue('E2', $prodi->first()->nama_prodi ?? 'Teknik Informatika');
        $sheet->setCellValue('F2', '2025');
        $sheet->setCellValue('G2', '1');
        $sheet->setCellValue('H2', 'A');
        $sheet->setCellValueExplicit('I2', '081234567890', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('J2', 'windy@email.com', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        // Set lebar kolom biar rapi
        $widths = [30, 20, 15, 15, 30, 12, 10, 8, 18, 30];
        foreach ($widths as $index => $width) {
            $sheet->getColumnDimension($columnLetters[$index])->setWidth($width);
        }

        // ============================================
        // SHEET 2: Petunjuk pengisian
        // ============================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Petunjuk');
        $instructions = [
            'PETUNJUK IMPORT DATA MAHASISWA',
            '',
            'KOLOM YANG HARUS DIISI:',
            '1. nama - Nama lengkap mahasiswa (maks 100 karakter)',
            '2. npm - Nomor Pokok Mahasiswa (HARUS UNIK)',
            '3. tgl_lahir - Tanggal lahir dengan format: Y-m-d (contoh: 2005-01-15) atau d/m/Y (contoh: 15/01/2005)',
            '4. jenis_kelamin - Isi dengan "L" untuk Laki-laki atau "P" untuk Perempuan',
            '5. prodi - Nama program studi (contoh: Teknik Informatika) atau ID prodi',
            '6. angkatan - Tahun angkatan (contoh: 2025)',
            '7. semester - Semester saat ini (1-14)',
            '8. kelas - Huruf kelas: A, B, C, atau D',
            '9. no_hp - Nomor HP (hanya angka, tanpa spasi/tanda hubung)',
            '10. email - Email mahasiswa (HARUS UNIK)',
            '',
            'CATATAN PENTING:',
            '- TTD Digital TIDAK perlu diisi saat import',
            '- Password akun akan otomatis dibuat dari tanggal lahir (contoh: 20050115)',
            '- NPM dan Email harus unik, jika duplikat maka data akan gagal diimport',
            '- Baris pertama adalah header, jangan diubah',
            '- Hapus baris contoh sebelum import data asli',
            '',
            'TIPS:',
            '- Kolom NPM, Tanggal, dan No HP sudah diformat sebagai teks, cukup ketik langsung',
            '- Jangan menambahkan tanda apostrof di depan NPM',
        ];

        // Tulis petunjuk ke sheet 2
        foreach ($instructions as $index => $instruction) {
            $sheet2->setCellValue('A' . ($index + 1), $instruction);
            if ($index === 0) {
                $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            }
        }
        $sheet2->getColumnDimension('A')->setWidth(100);

        // ============================================
        // PROSES DOWNLOAD: Stream file ke browser
        // ============================================
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // StreamedResponse dipakai biar file langsung ke-download tanpa disimpan di server
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        // Set header HTTP untuk download file
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="Template_Import_Mahasiswa.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}