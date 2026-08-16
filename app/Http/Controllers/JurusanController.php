<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jurusan;
use App\Imports\JurusanImport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller untuk mengelola data master Jurusan
 * 
 * Fitur:
 * - CRUD jurusan (Create, Read, Update, Delete)
 * - Import jurusan dari file Excel
 * - Download template Excel untuk import
 */
class JurusanController extends Controller
{
    /**
     * Menampilkan halaman daftar semua jurusan
     * 
     * Bisa cari berdasarkan nama jurusan atau kode jurusan
     */
    public function index(Request $request)
    {
        $query = Jurusan::query();

        // Kalau ada kata kunci pencarian...
        if ($request->has('search') && $request->search != '') {
            // Cari di nama jurusan
            $query->where('nama_jurusan', 'like', '%' . $request->search . '%')
                  // Atau cari di kode jurusan
                  ->orWhere('kode_jurusan', 'like', '%' . $request->search . '%');
        }

        // Ambil semua data, urutkan dari ID terkecil
        $jurusan = $query->orderBy('id_jurusan', 'asc')->get();

        return view('pages.admin.jurusan.index', compact('jurusan'));
    }

    /**
     * Menampilkan form untuk menambah jurusan baru
     */
    public function create()
    {
        return view('pages.admin.jurusan.create');
    }

    /**
     * Menyimpan jurusan baru ke database
     * 
     * Validasi:
     * - Kode jurusan: wajib, unik
     * - Nama jurusan: wajib, unik
     */
    public function store(Request $request)
    {
        // Validasi dengan pesan error custom Bahasa Indonesia
        $validated = $request->validate([
            'kode_jurusan' => 'required|string|max:30|unique:jurusan,kode_jurusan',
            'nama_jurusan' => 'required|string|max:50|unique:jurusan,nama_jurusan',
        ], [
            'kode_jurusan.required' => 'Kode jurusan wajib diisi.',
            'kode_jurusan.string'   => 'Kode jurusan harus berupa teks.',
            'kode_jurusan.max'      => 'Kode jurusan maksimal 30 karakter.',
            'kode_jurusan.unique'   => 'Kode jurusan sudah terdaftar, silakan gunakan kode yang lain.',
            'nama_jurusan.required' => 'Nama jurusan wajib diisi.',
            'nama_jurusan.string'   => 'Nama jurusan harus berupa teks.',
            'nama_jurusan.max'      => 'Nama jurusan maksimal 50 karakter.',
            'nama_jurusan.unique'   => 'Nama jurusan sudah terdaftar, silakan gunakan nama yang lain.',
        ]);

        // Simpan ke database
        Jurusan::create($validated);

        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil ditambahkan');
    }

    /**
     * Menampilkan form edit jurusan
     * 
     * @param int $id_jurusan - ID jurusan yang akan diedit
     */
    public function edit($id_jurusan)
    {
        $jurusan = Jurusan::findOrFail($id_jurusan);
        return view('pages.admin.jurusan.edit', compact('jurusan'));
    }

    /**
     * Mengupdate data jurusan
     * 
     * Unique rule mengecualikan jurusan yang sedang diedit
     * 
     * @param int $id_jurusan - ID jurusan yang akan diupdate
     */
    public function update(Request $request, $id_jurusan)
    {
        $jurusan = Jurusan::findOrFail($id_jurusan);

        // Validasi (unique mengecualikan ID ini)
        $validated = $request->validate([
            'kode_jurusan' => 'required|string|max:30|unique:jurusan,kode_jurusan,' . $id_jurusan . ',id_jurusan',
            'nama_jurusan' => 'required|string|max:50|unique:jurusan,nama_jurusan,' . $id_jurusan . ',id_jurusan',
        ], [
            'kode_jurusan.required' => 'Kode jurusan wajib diisi.',
            'kode_jurusan.string'   => 'Kode jurusan harus berupa teks.',
            'kode_jurusan.max'      => 'Kode jurusan maksimal 30 karakter.',
            'kode_jurusan.unique'   => 'Kode jurusan sudah terdaftar, silakan gunakan kode yang lain.',
            'nama_jurusan.required' => 'Nama jurusan wajib diisi.',
            'nama_jurusan.string'   => 'Nama jurusan harus berupa teks.',
            'nama_jurusan.max'      => 'Nama jurusan maksimal 50 karakter.',
            'nama_jurusan.unique'   => 'Nama jurusan sudah terdaftar, silakan gunakan nama yang lain.',
        ]);

        $jurusan->update($validated);

        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil diperbarui');
    }

    /**
     * Menghapus data jurusan
     * 
     * @param int $id_jurusan - ID jurusan yang akan dihapus
     */
    public function destroy($id_jurusan)
    {
        $jurusan = Jurusan::findOrFail($id_jurusan);
        $jurusan->delete();

        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil dihapus');
    }

    /**
     * Menampilkan form upload file Excel untuk import jurusan
     */
     public function showImportForm()
    {
        return view('pages.admin.jurusan.import');
    }

    /**
     * Proses import data jurusan dari file Excel
     * 
     * Yang dilakukan:
     * 1. Validasi file (harus Excel, maks 5MB)
     * 2. Proses import menggunakan library Maatwebsite Excel
     * 3. Tampilkan hasil (berhasil & gagal)
     */
    public function importExcel(Request $request)
    {
        // Validasi file
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file_excel.required' => 'File Excel wajib dipilih.',
            'file_excel.file'     => 'Yang dipilih harus berupa file.',
            'file_excel.mimes'    => 'File harus berupa format Excel (.xlsx, .xls, .csv).',
            'file_excel.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        $import = new JurusanImport();

        try {
            // Jalankan import
            Excel::import($import, $request->file('file_excel'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Kalau ada error validasi di dalam file
            return redirect()->back()->with('error', 'Format file tidak sesuai.')->withErrors($e->errors());
        } catch (\Exception $e) {
            // Kalau ada error lain
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

        // Buat pesan hasil
        $message = "Import selesai! Berhasil: {$successCount} data.";
        if ($failCount > 0) {
            $message .= " Gagal: {$failCount} data.";
        }

        return redirect()->route('admin.jurusan.index')
            ->with('success', $message)
            ->with('import_done', true);
    }

    /**
     * Mengunduh template Excel untuk import jurusan
     * 
     * Template berisi:
     * - Sheet 1: Header kolom + contoh data (format teks biar tidak keubah)
     * - Sheet 2: Petunjuk pengisian
     */
     public function downloadTemplate()
    {
        // Buat spreadsheet baru
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header kolom
        $headers = ['kode_jurusan', 'nama_jurusan'];
        $columnLetters = ['A', 'B'];

        // Isi header dengan style (huruf putih, background orange)
        foreach ($headers as $index => $header) {
            $sheet->setCellValue($columnLetters[$index] . '1', $header);
            $sheet->getStyle($columnLetters[$index] . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'F97316']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ]);
        }

        // Set format kolom A dan B sebagai TEXT (biar kode tidak keubah jadi angka)
        $sheet->getStyle('A')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // Contoh data di baris kedua
        $sheet->setCellValueExplicit('A2', 'JKB', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B2', 'Jurusan Komputer dan Bisnis', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        // $sheet->setCellValueExplicit('A3', 'TM', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        // $sheet->setCellValueExplicit('B3', 'Teknik Mesin', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        // Atur lebar kolom
        $widths = [25, 40];
        foreach ($widths as $index => $width) {
            $sheet->getColumnDimension($columnLetters[$index])->setWidth($width);
        }
        
        // ==================== Sheet Petunjuk ====================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Petunjuk');
        $instructions = [
            'PETUNJUK IMPORT DATA JURUSAN',
            '',
            'KOLOM YANG HARUS DIISI:',
            '1. kode_jurusan  - Kode jurusan (HARUS UNIK, tidak boleh sama dengan data yang sudah ada)',
            '2. nama_jurusan  - Nama jurusan (HARUS UNIK, tidak boleh sama dengan data yang sudah ada)',
            '',
            'CATATAN PENTING:',
            '- Kode jurusan HARUS UNIK. Jika duplikat, baris tersebut akan GAGAL.',
            '- Nama jurusan HARUS UNIK. Jika duplikat, baris tersebut akan GAGAL.',
            '- Jika dalam satu file Excel terdapat kode atau nama yang sama, baris duplikatnya akan GAGAL.',
            '- Baris pertama adalah header, jangan diubah atau dihapus.',
            '- Hapus baris contoh sebelum import data asli.',
            '',
            'CONTOH DATA YANG BENAR:',
            'JKB | Jurusan Komputer dan Bisnis',
            // 'TM | Teknik Mesin',
            // 'MI | Manajemen Informatika',
        ];

        // Tulis petunjuk ke sheet
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

        // Set header HTTP untuk download
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="Template_Import_Jurusan.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}