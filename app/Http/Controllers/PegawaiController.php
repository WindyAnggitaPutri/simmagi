<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Models\Pegawai;
use App\Models\JadwalSemgang;
use App\Models\Jabatan;
use Illuminate\Support\Facades\DB;
use App\Models\JabatanTendik;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Imports\PegawaiImport;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Controller ini ngurus data Pegawai/Tendik dari sisi Admin
 * 
 * Fiturnya hampir sama seperti MahasiswaController:
 * - CRUD pegawai (create, read, update, delete)
 * - Import dari Excel
 * - Download template Excel
 * 
 * Yang unik di sini:
 * - Ada sistem "pilih role" untuk pegawai yang punya jabatan khusus (BAAK, BAUP, Admin Kajur)
 * - Saat pertama kali login, pegawai harus memilih role dulu sebelum masuk dashboard
 */
class PegawaiController extends Controller
{
    /**
     * Halaman daftar semua pegawai
     * 
     * Bisa dicari berdasarkan nama atau NIP
     * Mengambil SEMUA data (get, bukan paginate) karena filter dilakukan di client-side
     */
    public function index(Request $request)
    {
        $query = Pegawai::query();

        // Filter pencarian: cari di nama ATAU NIP
        if ($request->has('search') && $request->search != '') {
            $query->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('nip', 'like', '%' . $request->search . '%');
        }

        $pegawai = $query->orderBy('id_pegawai', 'asc')->get();

        return view('pages.admin.pegawai.index', compact('pegawai'));
    }

    /**
     * Halaman form tambah pegawai baru
     */
    public function create()
    {
        return view('pages.admin.pegawai.create');
    }

    /**
     * Proses menyimpan pegawai baru
     * 
     * Yang terjadi di belakang layar:
     * 1. Validasi semua input
     * 2. Buat akun user di tabel users (username = NIP, password = tanggal lahir tanpa strip)
     * 3. Upload TTD digital kalau ada
     * 4. Simpan data pegawai
     * 
     * Akun user dibuat dengan must_change_password = 1, artinya saat pertama kali login
     * pegawai akan dipaksa ganti password dulu
     */
    public function store(Request $request)
    {
        // Pesan error kustom dalam Bahasa Indonesia
        $messages = [
            'required' => ':attribute wajib diisi.',
            'string'   => ':attribute harus berupa teks.',
            'max'      => ':attribute tidak boleh lebih dari :max karakter.',
            'date'     => ':attribute harus berupa tanggal yang valid.',
            'in'       => 'Pilihan :attribute tidak valid.',
            'email'    => 'Format :attribute tidak valid.',
            'unique'   => ':attribute sudah terdaftar.',
            'file'     => ':attribute harus berupa file.',
            'ttd_digital.max' => 'Ukuran file TTD Digital maksimal 2MB.',
        ];

        // Label kustom untuk setiap field (biar pesan error lebih rapi)
        $customAttributes = [
            'nip' => 'NIP',
            'nama' => 'Nama Lengkap',
            'tgl_lahir' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
            'no_hp' => 'No. HP',
            'email' => 'Email',
            'ttd_digital' => 'TTD Digital',
        ];

        // Validasi input dari form
        // NIP dan email harus unik (gak boleh sama dengan pegawai lain)
        $validated = $request->validate([
            'nip'           => 'required|string|max:50|unique:pegawai,nip',
            'nama'          => 'required|string|max:100',
            'tgl_lahir'     => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp'         => 'required|string|max:20',
            'email'         => 'required|email|max:100|unique:pegawai,email',
            'ttd_digital'   => 'nullable|file|max:2048', // Opsional, maks 2MB
        ], $messages, $customAttributes);

        // Buat password dari tanggal lahir (hapus tanda strip, misal: 1985-01-15 -> 19850115)
        $passwordRaw = str_replace('-', '', $request->tgl_lahir);

        // Buat akun user di tabel users
        // insertGetId dipakai biar langsung dapet ID user yang baru dibuat
        $idUser = DB::table('users')->insertGetId([
            'username' => $request->nip, // Username = NIP
            'password' => Hash::make($passwordRaw), // Password = tanggal lahir (di-hash)
            'role'     => 'pegawai',
            'must_change_password' => 1, // Wajib ganti password saat pertama login
        ], 'id_user');

        // Upload TTD digital kalau ada
        $namaTTD = null;
        if ($request->hasFile('ttd_digital')) {
            $file = $request->file('ttd_digital');
            $namaTTD = time() . '_' . $file->getClientOriginalName(); // Nama file: timestamp_namaasli
            $file->storeAs('ttd_digital', $namaTTD, 'public');
        }

        // Simpan data pegawai, hubungkan dengan akun user yang baru dibuat
        Pegawai::create([
            'id_user' => $idUser,
            'nip' => $request->nip,
            'nama' => $request->nama,
            'tgl_lahir' => $request->tgl_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'ttd_digital' => $namaTTD,
        ]);

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Pegawai berhasil ditambahkan');
    }

    /**
     * Halaman form edit pegawai
     * 
     * Juga menampilkan daftar jabatan (untuk keperluan assign jabatan)
     */
    public function edit($id_pegawai)
    {
        $pegawai = Pegawai::findOrFail($id_pegawai);
        $jabatan = Jabatan::orderBy('nama_jabatan', 'asc')->get();
        return view('pages.admin.pegawai.edit', compact('pegawai', 'jabatan'));
    }

    /**
     * Proses update data pegawai
     * 
     * Yang perlu diperhatikan:
     * - Kalau NIP berubah, akun user juga ikut diupdate (username baru = NIP baru)
     * - Password SELALU di-reset ke tanggal lahir (walau gak diubah)
     * - Kalau ada foto TTD baru, foto lama dihapus dari storage
     * 
     * Beda dengan MahasiswaController: di sini must_change_password TIDAK di-reset
     * Jadi kalau pegawai sudah pernah ganti password, dia gak perlu ganti lagi
     */
    public function update(Request $request, $id_pegawai)
    {
        // Pesan error dan label kustom (sama seperti store)
        $messages = [
            'required' => ':attribute wajib diisi.',
            'string'   => ':attribute harus berupa teks.',
            'max'      => ':attribute tidak boleh lebih dari :max karakter.',
            'date'     => ':attribute harus berupa tanggal yang valid.',
            'in'       => 'Pilihan :attribute tidak valid.',
            'email'    => 'Format :attribute tidak valid.',
            'unique'   => ':attribute sudah digunakan.',
            'file'     => ':attribute harus berupa file.',
            'ttd_digital.max' => 'Ukuran file TTD Digital maksimal 2MB.',
        ];

        $customAttributes = [
            'nip' => 'NIP',
            'nama' => 'Nama Lengkap',
            'tgl_lahir' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
            'no_hp' => 'No. HP',
            'email' => 'Email',
            'ttd_digital' => 'TTD Digital',
        ];

        $pegawai = Pegawai::findOrFail($id_pegawai);

        // Validasi: unique NIP dan email, tapi ABaIKAN record yang sedang diedit
        $validated = $request->validate([
            'nip'           => 'required|string|max:50|unique:pegawai,nip,' . $id_pegawai . ',id_pegawai',
            'nama'          => 'required|string|max:100',
            'tgl_lahir'     => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp'         => 'required|string|max:15',
            'email'         => 'required|email|max:100|unique:pegawai,email,' . $id_pegawai . ',id_pegawai',
            'ttd_digital'   => 'nullable|file|max:2048',
        ], $messages, $customAttributes);

        // Kalau ada foto TTD baru, hapus foto lama dulu dari storage
        if ($request->hasFile('ttd_digital')) {
            if ($pegawai->ttd_digital && file_exists(storage_path('app/public/ttd_digital/' . $pegawai->ttd_digital))) {
                unlink(storage_path('app/public/ttd_digital/' . $pegawai->ttd_digital));
            }
            
            $file = $request->file('ttd_digital');
            $namaTTD = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('ttd_digital', $namaTTD, 'public');
            $validated['ttd_digital'] = $namaTTD;
        }

        // Update data pegawai
        $pegawai->update([
            'nip' => $request->nip,
            'nama' => $request->nama,
            'tgl_lahir' => $request->tgl_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'ttd_digital' => $validated['ttd_digital'] ?? $pegawai->ttd_digital, // Kalau gak upload baru, pakai yang lama
        ]);

        // Update akun user juga: username dan password (reset ke tanggal lahir)
        if ($pegawai->id_user) {
            $passwordRaw = str_replace('-', '', $request->tgl_lahir);

            DB::table('users')
                ->where('id_user', $pegawai->id_user)
                ->update([
                    'username' => $request->nip,
                    'password' => Hash::make($passwordRaw),
                ]);
        }

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Data pegawai berhasil diperbarui');
    }

    /**
     * Hapus pegawai
     * 
     * File TTD digital ikut dihapus dari storage
     * Akun user di tabel users juga ikut terhapus
     */
    public function destroy($id_pegawai)
    {
        $pegawai = Pegawai::findOrFail($id_pegawai);

        // Hapus file TTD dari storage kalau ada
        if ($pegawai->ttd_digital && file_exists(storage_path('app/public/ttd_digital/' . $pegawai->ttd_digital))) {
            unlink(storage_path('app/public/ttd_digital/' . $pegawai->ttd_digital));
        }

        $pegawai->delete();

        // Hapus akun user-nya juga kalau ada
        if ($pegawai->id_user) {
            DB::table('users')->where('id_user', $pegawai->id_user)->delete();
        }

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Pegawai berhasil dihapus');
    }

/**
 * Halaman "Pilih Role" untuk pegawai yang login
 * 
 * Role ditentukan berdasarkan KODE JABATAN menggunakan mapping array
 */
public function pilihRole()
{
    $idUser = auth()->user()->id_user;
    $pegawai = Pegawai::where('id_user', $idUser)->first();

    if (!$pegawai) {
        return redirect('/login')->withErrors('Data pegawai tidak ditemukan.');
    }

    // Ambil kode jabatan yang dimiliki pegawai ini
    $kodeJabatans = JabatanTendik::where('id_pegawai', $pegawai->id_pegawai)
        ->with('jabatan')
        ->get()
        ->pluck('jabatan.kode_jabatan')
        ->toArray();

    // Mapping kode jabatan -> role
    // Tinggal tambah baris baru kalau ada jabatan baru
    $mapping = [
        'J001' => [
            'key'   => 'baak',
            'label' => 'BAAK',
            'desc'  => 'Bagian Akademik dan Kemahasiswaan',
            'route' => route('pegawai.beranda-baak'),
            'color' => 'yellow'
        ],
        'J004' => [
            'key'   => 'admin_kajur',
            'label' => 'Admin Kajur',
            'desc'  => 'Admin Ketua Jurusan',
            'route' => route('pegawai.beranda-admin-kajur'),
            'color' => 'yellow'
        ],
    ];

    // Cocokkan kode jabatan pegawai dengan mapping
    $roles = [];
    foreach ($kodeJabatans as $kodeJabatan) {
        $kodeJabatan = strtoupper(trim($kodeJabatan));

        if (isset($mapping[$kodeJabatan])) {
            $roles[] = $mapping[$kodeJabatan];
        }
    }

    if (empty($roles)) {
        return redirect()->back()->withErrors('Jabatan Anda belum terdaftar di sistem.');
    }

    $setRoleRoute = 'pegawai.set-role';

    return view('pages.pilih_role', compact('roles', 'pegawai', 'setRoleRoute'));
} /**
     * Proses menyimpan pilihan role ke session
     * 
     * Setelah role disimpan di session, semua halaman selanjutnya bisa cek
     * session('role_pegawai') untuk menentukan akses apa yang boleh dilakukan
     */
    public function setRole(Request $request)
    {
        $request->validate([
            'role_key' => 'required|string',
            'route'    => 'required|string'
        ]);

        // Simpan role yang dipilih ke session
        session(['role_pegawai' => $request->role_key]);

        // Redirect ke dashboard sesuai role yang dipilih
        return redirect($request->route);
    }

    /**
     * Dashboard untuk role "Admin Kajur"
     * 
     * Menampilkan statistik ringkas dan data terkini:
     * - Jumlah mahasiswa, prodi, perusahaan
     * - 5 jadwal seminar mendatang
     * - 8 perusahaan dengan mahasiswa paling banyak yang mengajukan magang
     */
    public function berandaAdminKajur()
    {
        // Set role di session (sebagai fallback kalau langsung akses URL ini)
        session(['role_pegawai' => 'admin_kajur']);

        // Statistik dasar
        $jumlahMahasiswa   = \App\Models\Mahasiswa::count();
        $jumlahProdi       = \App\Models\Prodi::count();
        $jumlahPerusahaan  = \App\Models\Perusahaan::count();

        // 5 jadwal seminar mendatang (tanggal >= hari ini), diurutkan dari terdekat
        $jadwal = \App\Models\JadwalSemgang::with(['mahasiswa.prodi', 'perusahaan'])
            ->whereDate('tgl_semgang', '>=', now())
            ->orderBy('tgl_semgang', 'asc')
            ->limit(5)
            ->get();

        // 8 perusahaan dengan jumlah mahasiswa yang paling banyak mengajukan magang (status: diajukan)
        // selectSub dipakai untuk bikin subquery yang menghitung jumlah mahasiswa per perusahaan
        $perusahaans = \App\Models\Perusahaan::select('perusahaan.*')
            ->selectSub(function ($query) {
                $query->selectRaw('COUNT(*)')
                    ->from('pengajuan_magang')
                    ->join('pengajuan_mahasiswa', 'pengajuan_magang.id_pengajuan', '=', 'pengajuan_mahasiswa.id_pengajuan')
                    ->whereColumn('pengajuan_magang.id_perusahaan', 'perusahaan.id_perusahaan')
                    ->where('pengajuan_mahasiswa.status', 'diajukan');
            }, 'jumlah_mahasiswa')
            ->having('jumlah_mahasiswa', '>', 0) // Hanya yang ada mahasiswa yang mengajukan
            ->orderByDesc('jumlah_mahasiswa')
            ->limit(8)
            ->get();

        return view('pages.beranda', compact('jumlahMahasiswa', 'jumlahProdi', 'jumlahPerusahaan', 'jadwal', 'perusahaans'));
    }

    /**
     * Halaman form import pegawai dari Excel
     */
    public function showImportForm()
    {
        return view('pages.admin.pegawai.import');
    }

    /**
     * Proses import data pegawai dari file Excel
     * 
     * Sama seperti di MahasiswaController:
     * - Validasi file (wajib, format Excel, maks 5MB)
     * - Proses import menggunakan package Laravel Excel
     * - Tampilkan ringkasan hasil (berhasil/gagal)
     * - Simpan error per-baris ke session kalau ada
     */
    public function importExcel(Request $request)
    {
        // Validasi file
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file_excel.required' => 'File Excel wajib dipilih',
            'file_excel.mimes'    => 'File harus berupa format Excel (.xlsx, .xls, .csv)',
            'file_excel.max'      => 'Ukuran file maksimal 5MB',
        ]);

        $import = new PegawaiImport();
        
        try {
            Excel::import($import, $request->file('file_excel'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Error validasi di file (format kolom salah, dll)
            return redirect()->back()->with('error', 'Format file tidak sesuai')->withErrors($e->errors());
        } catch (\Exception $e) {
            // Error lain (file rusak, dll)
            return redirect()->back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        // Ambil hasil import
        $successCount = $import->getSuccessCount();
        $failCount = $import->getFailCount();
        $errors = $import->getErrors();

        // Simpan error ke session kalau ada
        if (!empty($errors)) {
            session()->flash('import_errors', $errors);
        }

        // Susun pesan hasil
        $message = "Import selesai! Berhasil: {$successCount} data";
        if ($failCount > 0) {
            $message .= ", Gagal: {$failCount} data";
        }

        return redirect()->route('admin.pegawai.index')
            ->with('success', $message)
            ->with('import_done', true);
    }

    /**
     * Download template Excel untuk import pegawai
     * 
     * Template ini berisi:
     * - Sheet 1: Header kolom + 1 baris contoh data
     * - Sheet 2: Petunjuk pengisian
     * 
     * Kolom NIP dan tanggal lahir diformat sebagai TEXT supaya gak keubah oleh Excel
     * (misalnya NIP 198501012010011001 gak jadi 1.98501E+17)
     */
    public function downloadTemplate()
    {
        // Buat spreadsheet baru
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header kolom di baris 1
        $headers = ['nama', 'nip', 'tgl_lahir', 'jenis_kelamin', 'no_hp', 'email'];
        $columnLetters = ['A', 'B', 'C', 'D', 'E', 'F'];

        // Styling header: teks putih, background oranye, rata tengah
        foreach ($headers as $index => $header) {
            $sheet->setCellValue($columnLetters[$index] . '1', $header);
            $sheet->getStyle($columnLetters[$index] . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'F97316']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ]);
        }

        // Set format kolom NIP sebagai TEXT (biar gak keubah jadi angka ilmiah oleh Excel)
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // Set format kolom tanggal lahir juga sebagai TEXT
        // Biar user ketik tanggal langsung sebagai teks, Excel tidak otomatis konversi ke format tanggal
        $sheet->getStyle('C')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // Isi baris contoh data di baris 2
        // setCellValueExplicit dipakai untuk memastikan data disimpan sebagai TEKS
        $sheet->setCellValue('A2', 'Siti Aminah, S.E.');
        $sheet->setCellValueExplicit('B2', '198501012010011001', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C2', '1985-01-15', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('A2', 'Siti Aminah, S.E.');
        $sheet->setCellValueExplicit('B2', '198501012010011001', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('C2', '1985-01-15');
        $sheet->setCellValue('D2', 'P');
        $sheet->setCellValue('E2', '081234567890');
        $sheet->setCellValueExplicit('F2', 'siti.aminah@polinema.ac.id', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        // Set lebar kolom biar rapi
        $widths = [30, 25, 15, 15, 18, 35];
        foreach ($widths as $index => $width) {
            $sheet->getColumnDimension($columnLetters[$index])->setWidth($width);
        }

        // ============================================
        // SHEET 2: Petunjuk pengisian
        // ============================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Petunjuk');
        $instructions = [
            'PETUNJUK IMPORT DATA PEGAWAI',
            '',
            'KOLOM YANG HARUS DIISI:',
            '1. nama - Nama lengkap pegawai (maks 100 karakter)',
            '2. nip - Nomor Induk Pegawai (HARUS UNIK)',
            '3. tgl_lahir - Tanggal lahir dengan format: Y-m-d (contoh: 1985-01-15) atau d/m/Y (contoh: 15/01/1985)',
            '4. jenis_kelamin - Isi dengan "L" untuk Laki-laki atau "P" untuk Perempuan',
            '5. no_hp - Nomor HP (maks 20 karakter)',
            '6. email - Email pegawai (HARUS UNIK)',
            '',
            'CATATAN PENTING:',
            '- TTD Digital TIDAK perlu diisi saat import',
            '- Password akun akan otomatis dibuat dari tanggal lahir (contoh: 19850115)',
            '- NIP dan Email harus unik, jika duplikat maka data akan gagal diimport',
            '- Baris pertama adalah header, jangan diubah',
            '- Hapus baris contoh sebelum import data asli',
            '',
            'TIPS:',
            '- Kolom NIP sudah diformat sebagai teks, cukup ketik angka NIP langsung',
            '- Jangan menambahkan tanda apostrof (\' ) di depan NIP',
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
        $response->headers->set('Content-Disposition', 'attachment;filename="Template_Import_Pegawai.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}