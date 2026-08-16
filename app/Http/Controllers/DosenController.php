<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dosen;
use App\Models\Prodi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\JabatanTendik;
use App\Models\DosenPembimbing;
use App\Models\DosenVisitasi;
use Symfony\Component\HttpFoundation\StreamedResponse; // TAMBAHKAN INI
use App\Imports\DosenImport; // TAMBAHKAN INI
use Maatwebsite\Excel\Facades\Excel; // TAMBAHKAN INI

/**
 * Controller untuk mengelola data Dosen
 * 
 * Fitur yang tersedia:
 * - CRUD data dosen (Create, Read, Update, Delete)
 * - Import data dosen dari file Excel
 * - Download template import Excel
 * - Pemilihan role untuk dosen yang login
 */
class DosenController extends Controller
{
    /**
     * Menampilkan halaman daftar semua dosen
     * 
     * Fitur:
     * - Bisa mencari dosen berdasarkan nama, NIDN, atau nama prodi
     * - Menampilkan data dosen beserta prodi terkait
     * - Data diurutkan berdasarkan ID dosen
     */
    public function index(Request $request)
    {
        // Mulai query dengan relasi prodi (supaya tidak query terpisah)
        $query = Dosen::with('prodi');

        // Kalau user ketik sesuatu di kolom pencarian...
        if ($request->has('search') && $request->search != '') {
            // Cari di kolom nama dosen
            $query->where('nama', 'like', '%' . $request->search . '%')
                  // Atau cari di kolom NIDN
                  ->orWhere('nidn', 'like', '%' . $request->search . '%')
                  // Atau cari di nama prodi (lewat relasi)
                  ->orWhereHas('prodi', function($q) use ($request) {
                      $q->where('nama_prodi', 'like', '%' . $request->search . '%');
                  });
        }

        // Ambil semua data yang cocok, urutkan dari ID terkecil
        $dosen = $query->orderBy('id_dosen', 'asc')->get();
        // Ambil daftar semua prodi untuk dropdown filter (kalau ada)
        $listProdi = Prodi::orderBy('nama_prodi', 'asc')->get();

        // Tampilkan ke halaman view
        return view('pages.admin.dosen.index', compact('dosen', 'listProdi'));
    }

    /**
     * Menampilkan form untuk menambah dosen baru
     * 
     * Yang ditampilkan:
     * - Form input data dosen
     * - Dropdown pilihan prodi
     */
    public function create()
    {
        // Ambil daftar prodi, urutkan abjad
        $prodi = Prodi::orderBy('nama_prodi', 'asc')->get();
        return view('pages.admin.dosen.create', compact('prodi'));
    }

    /**
     * Menyimpan data dosen baru ke database
     * 
     * Yang dilakukan:
     * 1. Validasi semua input dari form
     * 2. Buat akun user untuk dosen (username = NIDN, password = tanggal lahir)
     * 3. Upload file TTD digital jika ada
     * 4. Simpan data dosen
     */
    public function store(Request $request)
    {
        // Pesan error custom dalam Bahasa Indonesia
        $messages = [
            'required' => ':attribute wajib diisi.',
            'string'   => ':attribute harus berupa teks.',
            'max'      => ':attribute tidak boleh lebih dari :max karakter.',
            'date'     => ':attribute harus berupa tanggal yang valid.',
            'in'       => 'Pilihan :attribute tidak valid.',
            'exists'   => ':attribute tidak ditemukan.',
            'email'    => 'Format :attribute tidak valid.',
            'unique'   => ':attribute sudah terdaftar.',
            'file'     => ':attribute harus berupa file.',
            'ttd_digital.max' => 'Ukuran file TTD Digital maksimal 2MB.',
        ];

        // Nama atribut yang ditampilkan di pesan error (biar ramah user)
        $customAttributes = [
            'nidn' => 'NIDN',
            'nama' => 'Nama Lengkap',
            'tgl_lahir' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
            'id_prodi' => 'Program Studi',
            'no_hp' => 'No. HP',
            'email' => 'Email',
            'ttd_digital' => 'TTD Digital',
        ];

        // Validasi input dari form
        $validated = $request->validate([
            'nidn'          => 'required|string|max:50|unique:dosen,nidn',      // NIDN wajib, unik
            'nama'          => 'required|string|max:100',                       // Nama wajib, maks 100 huruf
            'tgl_lahir'     => 'required|date',                                 // Tanggal lahir wajib, format tanggal
            'jenis_kelamin' => 'required|in:L,P',                              // Hanya boleh L atau P
            'id_prodi'      => 'required|exists:prodi,id_prodi',                // Prodi harus ada di tabel prodi
            'no_hp'         => 'required|string|max:15',                        // No HP wajib, maks 15 digit
            'email'         => 'required|email|max:100|unique:dosen,email',     // Email wajib, valid, unik
            'ttd_digital'   => 'nullable|file|max:2048',                       // TTD opsional, maks 2MB
        ], $messages, $customAttributes);

        // Buat password dari tanggal lahir (contoh: 1990-05-20 -> 19900520)
        $passwordRaw = str_replace('-', '', $request->tgl_lahir);

        // Buat akun user baru di tabel users untuk login dosen ini
        $idUser = DB::table('users')->insertGetId([
            'username' => $request->nidn,                          // Username = NIDN
            'password' => Hash::make($passwordRaw),                // Password = tanggal lahir (di-hash)
            'role'     => 'dosen',                                 // Role = dosen
            'must_change_password' => 1,                           // Wajib ganti password saat pertama login
        ], 'id_user');

        // Proses upload TTD digital kalau ada filenya
        $namaTTD = null;
        if ($request->hasFile('ttd_digital')) {
            $file = $request->file('ttd_digital');
            // Beri nama file unik dengan timestamp
            $namaTTD = $file->getClientOriginalName();
            // Simpan ke folder storage/app/public/ttd_digital
            $file->storeAs('ttd_digital', $namaTTD, 'public');
        }

        // Simpan data dosen ke database
        Dosen::create(array_merge($validated, [
            'id_user' => $idUser,          // Hubungkan dengan akun user yang tadi dibuat
            'ttd_digital' => $namaTTD,     // Simpan nama file TTD
        ]));

        // Kembali ke halaman daftar dosen dengan pesan sukses
        return redirect()->route('admin.dosen.index')
            ->with('success', 'Dosen berhasil ditambahkan');
    }

    /**
     * Menampilkan form edit data dosen
     * 
     * @param int $id_dosen - ID dosen yang akan diedit
     */
    public function edit($id_dosen)
    {
        // Cari data dosen berdasarkan ID, kalau tidak ada langsung error 404
        $dosen = Dosen::findOrFail($id_dosen);
        // Ambil daftar prodi untuk dropdown
        $prodi = Prodi::orderBy('nama_prodi', 'asc')->get();
        return view('pages.admin.dosen.edit', compact('dosen', 'prodi'));
    }

    /**
     * Mengupdate data dosen yang sudah ada
     * 
     * Yang dilakukan:
     * 1. Validasi input (NIDN dan email dikecualikan untuk dosen ini sendiri)
     * 2. Hapus file TTD lama jika ada file TTD baru
     * 3. Upload file TTD baru jika ada
     * 4. Update data dosen
     * 5. Update username & password di tabel users
     * 
     * @param int $id_dosen - ID dosen yang akan diupdate
     */
    public function update(Request $request, $id_dosen)
    {
        // Pesan error custom (sama seperti di store)
        $messages = [
            'required' => ':attribute wajib diisi.',
            'string'   => ':attribute harus berupa teks.',
            'max'      => ':attribute tidak boleh lebih dari :max karakter.',
            'date'     => ':attribute harus berupa tanggal yang valid.',
            'in'       => 'Pilihan :attribute tidak valid.',
            'exists'   => ':attribute tidak ditemukan.',
            'email'    => 'Format :attribute tidak valid.',
            'unique'   => ':attribute sudah digunakan.',
            'file'     => ':attribute harus berupa file.',
            'ttd_digital.max' => 'Ukuran file TTD Digital maksimal 2MB.',
        ];

        // Nama atribut custom
        $customAttributes = [
            'nidn' => 'NIDN',
            'nama' => 'Nama Lengkap',
            'tgl_lahir' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
            'id_prodi' => 'Program Studi',
            'no_hp' => 'No. HP',
            'email' => 'Email',
            'ttd_digital' => 'TTD Digital',
        ];

        // Ambil data dosen yang akan diedit
        $dosen = Dosen::findOrFail($id_dosen);

        // Validasi input
        // Note: unique rule mengecualikan dosen ini sendiri (pakai $id_dosen)
        $validated = $request->validate([
            'nidn'          => 'required|string|max:50|unique:dosen,nidn,' . $id_dosen . ',id_dosen',
            'nama'          => 'required|string|max:100',
            'tgl_lahir'     => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'id_prodi'      => 'required|exists:prodi,id_prodi',
            'no_hp'         => 'required|string|max:15',
            'email'         => 'required|email|max:100|unique:dosen,email,' . $id_dosen . ',id_dosen',
            'ttd_digital'   => 'nullable|file|max:2048',
        ], $messages, $customAttributes);

        // Kalau user upload TTD baru...
        if ($request->hasFile('ttd_digital')) {
            // Hapus file TTD lama dari storage (kalau ada)
            if ($dosen->ttd_digital && file_exists(storage_path('app/public/ttd_digital/' . $dosen->ttd_digital))) {
                unlink(storage_path('app/public/ttd_digital/' . $dosen->ttd_digital));
            }
            
            // Upload file TTD baru
            $file = $request->file('ttd_digital');
             $namaTTD = $file->getClientOriginalName();
            $file->storeAs('ttd_digital', $namaTTD, 'public');
            // Masukkan nama file baru ke data yang akan diupdate
            $validated['ttd_digital'] = $namaTTD;
        }

        // Update data dosen di database
        $dosen->update($validated);

        // Kalau dosen ini punya akun user, update juga username dan passwordnya
        if ($dosen->id_user) {
            // Password tetap pakai tanggal lahir yang baru
            $passwordRaw = str_replace('-', '', $request->tgl_lahir);

            DB::table('users')
                ->where('id_user', $dosen->id_user)
                ->update([
                    'username' => $request->nidn,          // Update username (NIDN baru)
                    'password' => Hash::make($passwordRaw), // Update password (tgl lahir baru)
                ]);
        }

        // Kembali ke halaman daftar dengan pesan sukses
        return redirect()->route('admin.dosen.index')
            ->with('success', 'Data dosen berhasil diperbarui');
    }

    /**
     * Menghapus data dosen
     * 
     * Yang dilakukan:
     * 1. Hapus file TTD digital dari storage
     * 2. Hapus data dosen dari database
     * 3. Hapus akun user terkait dari tabel users
     * 
     * @param int $id_dosen - ID dosen yang akan dihapus
     */
public function destroy($id_dosen)
{
    DB::beginTransaction();
    try {
        $dosen = Dosen::findOrFail($id_dosen);

        // Hapus file TTD dari storage kalau ada
        if ($dosen->ttd_digital && file_exists(storage_path('app/public/ttd_digital/' . $dosen->ttd_digital))) {
            unlink(storage_path('app/public/ttd_digital/' . $dosen->ttd_digital));
        }

        // ⚠️ HAPUS DATA RELASI DULU sebelum hapus dosen!
        DosenPembimbing::where('id_dosen', $id_dosen)->delete();
        DosenVisitasi::where('id_dosen', $id_dosen)->delete();
        JabatanTendik::where('id_dosen', $id_dosen)->delete();
        
        // Tambahkan tabel relasi lain kalau ada...

        // Baru hapus dosennya
        $dosen->delete();

        // Hapus akun user-nya kalau ada
        if ($dosen->id_user) {
            DB::table('users')->where('id_user', $dosen->id_user)->delete();
        }

        DB::commit();
        
        return redirect()->route('admin.dosen.index')
            ->with('success', 'Dosen berhasil dihapus');
            
    } catch (\Exception $e) {
        DB::rollBack();
        
        // Kalau error, kembalikan dengan pesan error
        return redirect()->route('admin.dosen.index')
            ->with('error', 'Gagal menghapus dosen: ' . $e->getMessage());
    }
}

/**
 * Menampilkan halaman pemilihan role untuk dosen yang login
 * 
 * Role ditentukan berdasarkan KODE JABATAN menggunakan mapping array
 * Dosen Pembimbing & Dosen Visitasi selalu tersedia
 */
public function pilihRole()
{
    $idUser = auth()->user()->id_user;
    $dosen = Dosen::where('id_user', $idUser)->first();

    if (!$dosen) {
        return redirect('/login')->withErrors('Data dosen tidak ditemukan.');
    }

    // Ambil kode jabatan yang dimiliki dosen ini
    $kodeJabatans = JabatanTendik::where('id_dosen', $dosen->id_dosen)
        ->with('jabatan')
        ->get()
        ->pluck('jabatan.kode_jabatan')
        ->toArray();

    // Mapping kode jabatan -> role
    // Tinggal tambah baris baru kalau ada jabatan baru
    $mapping = [
        'J002' => [
            'key'   => 'kajur',
            'label' => 'Ketua Jurusan',
            'desc'  => 'Pimpinan Jurusan',
            'route' => '/beranda-kajur',
            'color' => 'yellow'
        ],
        'J003' => [
            'key'   => 'kaprodi',
            'label' => 'Ketua Program Studi',
            'desc'  => 'Pimpinan Program Studi',
            'route' => '/beranda-kaprodi',
            'color' => 'yellow'
        ],
    ];

    // Cocokkan kode jabatan dosen dengan mapping
    $roles = [];
    foreach ($kodeJabatans as $kodeJabatan) {
        $kodeJabatan = strtoupper(trim($kodeJabatan));

        if (isset($mapping[$kodeJabatan])) {
            $roles[] = $mapping[$kodeJabatan];
        }
    }

    // Dosen Pembimbing selalu tersedia
    $roles[] = [
        'key'   => 'dospem',
        'label' => 'Dosen Pembimbing',
        'desc'  => 'Pembimbing Magang',
        'route' => '/beranda-dospem',
        'color' => 'yellow'
    ];

    // Dosen Visitasi selalu tersedia
    $roles[] = [
        'key'   => 'dosen_visitasi',
        'label' => 'Dosen Visitasi',
        'desc'  => 'Visitasi Magang',
        'route' => '/beranda-dosen-visitasi',
        'color' => 'yellow'
    ];

    $setRoleRoute = 'dosen.set-role';

    return view('pages.pilih_role', compact('roles', 'dosen', 'setRoleRoute'));
}

    /**
     * Menyimpan pilihan role dosen ke session
     * 
     * Setelah memilih role, dosen akan diarahkan ke beranda sesuai role yang dipilih
     */
    public function setRole(Request $request)
    {
        // Validasi input
        $request->validate([
            'role_key' => 'required|string',  // Key role yang dipilih
            'route'    => 'required|string'   // Route tujuan
        ]);

        // Simpan role yang aktif ke session
        session(['dosen_active_role' => $request->role_key]);

        // Redirect ke beranda role yang dipilih
        return redirect($request->route);
    }

    /**
     * Menampilkan form untuk import data dosen dari Excel
     */
    public function showImportForm()
    {
        // Ambil daftar prodi untuk referensi
        $prodi = Prodi::orderBy('nama_prodi', 'asc')->get();
        return view('pages.admin.dosen.import', compact('prodi'));
    }

    /**
     * Proses import data dosen dari file Excel
     * 
     * Yang dilakukan:
     * 1. Validasi file (harus Excel, maks 5MB)
     * 2. Proses import menggunakan library Maatwebsite Excel
     * 3. Tampilkan hasil (berhasil & gagal)
     */
    public function importExcel(Request $request)
    {
        // Validasi file Excel
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:5120',  // Wajib, format Excel, maks 5MB
        ], [
            'file_excel.required' => 'File Excel wajib dipilih',
            'file_excel.mimes'    => 'File harus berupa format Excel (.xlsx, .xls, .csv)',
            'file_excel.max'      => 'Ukuran file maksimal 5MB',
        ]);

        // Inisialisasi class import
        $import = new DosenImport();
        
        try {
            // Jalankan import
            Excel::import($import, $request->file('file_excel'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Kalau ada error validasi di file Excel
            return redirect()->back()->with('error', 'Format file tidak sesuai')->withErrors($e->errors());
        } catch (\Exception $e) {
            // Kalau ada error lain
            return redirect()->back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        // Ambil hasil import
        $successCount = $import->getSuccessCount();  // Jumlah data berhasil
        $failCount = $import->getFailCount();        // Jumlah data gagal
        $errors = $import->getErrors();              // Detail error

        // Simpan error ke session kalau ada
        if (!empty($errors)) {
            session()->flash('import_errors', $errors);
        }
        
        // Buat pesan hasil import
        $message = "Import selesai! Berhasil: {$successCount} data";
        if ($failCount > 0) {
            $message .= ", Gagal: {$failCount} data";
        }

        // Kembali ke halaman daftar dengan pesan hasil
        return redirect()->route('admin.dosen.index')
            ->with('success', $message)
            ->with('import_done', true);
    }

    /**
     * Mengunduh template Excel untuk import data dosen
     * 
     * Template berisi:
     * - Sheet 1: Format kolom yang harus diisi + contoh data
     * - Sheet 2: Petunjuk pengisian
     */
    public function downloadTemplate()
    {
        // Ambil daftar prodi untuk contoh data
        $prodi = Prodi::orderBy('nama_prodi', 'asc')->get();

        // Buat spreadsheet baru
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header kolom yang harus diisi
        $headers = ['nama', 'nidn', 'tgl_lahir', 'jenis_kelamin', 'prodi', 'no_hp', 'email'];
        $columnLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

        // Isi header di baris pertama dengan style (huruf putih, background orange)
        foreach ($headers as $index => $header) {
            $sheet->setCellValue($columnLetters[$index] . '1', $header);
            $sheet->getStyle($columnLetters[$index] . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'F97316']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ]);
        }

        // ✅ Set format kolom B (NIDN) sebagai TEXT
        // (biar tidak otomatis diubah formatnya oleh Excel)
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        // ✅ Set format kolom C (tgl_lahir) sebagai TEXT
        $sheet->getStyle('C')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // Isi contoh data di baris kedua
        $sheet->setCellValue('A2', 'Dr. Ahmad Fauzi, M.Kom');                                    // Nama
        $sheet->setCellValueExplicit('B2', '0701010101', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);  // NIDN (teks)
        $sheet->setCellValueExplicit('C2', '1980-05-20', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);  // Tgl lahir (teks)
        $sheet->setCellValue('D2', 'L');                                                           // Jenis kelamin
        $sheet->setCellValue('E2', $prodi->first()->nama_prodi ?? 'Teknik Informatika');          // Prodi
        $sheet->setCellValue('F2', '081234567890');                                               // No HP
        $sheet->setCellValueExplicit('G2', 'ahmad.fauzi@polinema.ac.id', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);  // Email

        // Atur lebar kolom supaya rapi
        $widths = [30, 20, 15, 15, 30, 20, 35];
        foreach ($widths as $index => $width) {
            $sheet->getColumnDimension($columnLetters[$index])->setWidth($width);
        }

        // ==================== SHEET KEDUA: PETUNJUK ====================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Petunjuk');
        
        // Tulis petunjuk pengisian
        $instructions = [
            'PETUNJUK IMPORT DATA DOSEN',
            '',
            'KOLOM YANG HARUS DIISI:',
            '1. nama - Nama lengkap dosen (maks 50 karakter)',
            '2. nidn - Nomor Induk Dosen Nasional (HARUS UNIK)',
            '3. tgl_lahir - Tanggal lahir dengan format: Y-m-d (contoh: 1980-05-20) atau d/m/Y (contoh: 20/05/1980)',
            '4. jenis_kelamin - Isi dengan "L" untuk Laki-laki atau "P" untuk Perempuan',
            '5. prodi - Nama program studi atau ID prodi',
            '6. no_hp - Nomor HP (maks 20 karakter)',
            '7. email - Email dosen (HARUS UNIK)',
            '',
            'CATATAN PENTING:',
            '- TTD Digital TIDAK perlu diisi saat import',
            '- Password akun akan otomatis dibuat dari tanggal lahir (contoh: 19800520)',
            '- NIDN dan Email harus unik, jika duplikat maka data akan gagal diimport',
            '- Baris pertama adalah header, jangan diubah',
            '- Hapus baris contoh sebelum import data asli',
            '',
            'TIPS:',
            '- Kolom NIDN dan Tanggal sudah diformat sebagai teks, cukup ketik langsung',
            '- Jangan menambahkan tanda apostrof di depan NIDN',
        ];

        // Tulis setiap baris petunjuk ke sheet
        foreach ($instructions as $index => $instruction) {
            $sheet2->setCellValue('A' . ($index + 1), $instruction);
            // Judul dibuat tebal dan besar
            if ($index === 0) {
                $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            }
        }
        // Atur lebar kolom petunjuk
        $sheet2->getColumnDimension('A')->setWidth(100);

        // Siapkan writer untuk menghasilkan file Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // Kirim file sebagai response download (streaming)
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        // Set header HTTP untuk download file
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="Template_Import_Dosen.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}