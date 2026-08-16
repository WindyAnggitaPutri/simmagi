<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prodi;
use App\Models\Jurusan;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Imports\ProdiImport;
use Maatwebsite\Excel\Facades\Excel;

class ProdiController extends Controller
{
    // halaman list prodi, ada fitur search nya
    public function index(Request $request)
    {
        $query = Prodi::with('jurusan');

        // klo ada parameter search, filter berdasarkan nama/kode/jenjang/jurusan
        if ($request->has('search') && $request->search != '') {
            $query->where('nama_prodi', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_prodi', 'like', '%' . $request->search . '%')
                  ->orWhere('jenjang', 'like', '%' . $request->search . '%')
                  ->orWhereHas('jurusan', function($q) use ($request) {
                      $q->where('nama_jurusan', 'like', '%' . $request->search . '%');
                  });
        }

        $prodi = $query->orderBy('id_prodi', 'asc')->get();

        return view('pages.admin.prodi.index', compact('prodi'));
    }

    // tampilan form tambah prodi baru
    public function create()
    {
        $jurusan = Jurusan::orderBy('nama_jurusan', 'asc')->get();
        return view('pages.admin.prodi.create', compact('jurusan'));
    }

    // proses simpan data prodi baru
    public function store(Request $request)
    {
        // validasi input, pesan error nya custom biar gampang dibaca user
        $validated = $request->validate([
            'id_jurusan'                => 'required|exists:jurusan,id_jurusan',
            'kode_prodi'                => 'required|string|max:30|unique:prodi,kode_prodi',
            'nama_prodi'                => 'required|string|max:50|unique:prodi,nama_prodi',
            'jenjang'                   => 'required|in:D3,D4',
            'semester_magang'           => 'required|integer|min:1|max:8',
            'semester_pengajuan_magang' => 'required|integer|min:1|max:8',
        ], [
            'id_jurusan.required'                => 'Jurusan wajib dipilih.',
            'id_jurusan.exists'                  => 'Jurusan yang dipilih tidak ditemukan di database.',
            'kode_prodi.required'                => 'Kode prodi wajib diisi.',
            'kode_prodi.string'                  => 'Kode prodi harus berupa teks.',
            'kode_prodi.max'                     => 'Kode prodi maksimal 30 karakter.',
            'kode_prodi.unique'                  => 'Kode prodi sudah terdaftar, silakan gunakan kode yang lain.',
            'nama_prodi.required'                => 'Nama prodi wajib diisi.',
            'nama_prodi.string'                  => 'Nama prodi harus berupa teks.',
            'nama_prodi.max'                     => 'Nama prodi maksimal 50 karakter.',
            'nama_prodi.unique'                  => 'Nama prodi sudah terdaftar, silakan gunakan nama yang lain.',
            'jenjang.required'                   => 'Jenjang wajib dipilih.',
            'jenjang.in'                         => 'Jenjang hanya boleh D3 atau D4.',
            'semester_magang.required'           => 'Semester magang wajib diisi.',
            'semester_magang.integer'            => 'Semester magang harus berupa angka.',
            'semester_magang.min'                => 'Semester magang minimal 1.',
            'semester_magang.max'                => 'Semester magang maksimal 8.',
            'semester_pengajuan_magang.required' => 'Semester pengajuan magang wajib diisi.',
            'semester_pengajuan_magang.integer'  => 'Semester pengajuan magang harus berupa angka.',
            'semester_pengajuan_magang.min'      => 'Semester pengajuan magang minimal 1.',
            'semester_pengajuan_magang.max'      => 'Semester pengajuan magang maksimal 8.',
        ]);

        Prodi::create($validated);

        return redirect()->route('admin.prodi.index')
            ->with('success', 'Program Studi berhasil ditambahkan');
    }

    // tampilan form edit prodi
    public function edit($id_prodi)
    {
        $prodi = Prodi::findOrFail($id_prodi);
        $jurusan = Jurusan::orderBy('nama_jurusan', 'asc')->get();
        return view('pages.admin.prodi.edit', compact('prodi', 'jurusan'));
    }

    // proses update data prodi
    public function update(Request $request, $id_prodi)
    {
        $prodi = Prodi::findOrFail($id_prodi);

        // validasi sama seperti store, tapi unique nya exclude id yang sedang diedit
        $validated = $request->validate([
            'id_jurusan'                => 'required|exists:jurusan,id_jurusan',
            'kode_prodi'                => 'required|string|max:30|unique:prodi,kode_prodi,' . $id_prodi . ',id_prodi',
            'nama_prodi'                => 'required|string|max:50|unique:prodi,nama_prodi,' . $id_prodi . ',id_prodi',
            'jenjang'                   => 'required|in:D3,D4',
            'semester_magang'           => 'required|integer|min:1|max:8',
            'semester_pengajuan_magang' => 'required|integer|min:1|max:8',
        ], [
            'id_jurusan.required'                => 'Jurusan wajib dipilih.',
            'id_jurusan.exists'                  => 'Jurusan yang dipilih tidak ditemukan di database.',
            'kode_prodi.required'                => 'Kode prodi wajib diisi.',
            'kode_prodi.string'                  => 'Kode prodi harus berupa teks.',
            'kode_prodi.max'                     => 'Kode prodi maksimal 30 karakter.',
            'kode_prodi.unique'                  => 'Kode prodi sudah terdaftar, silakan gunakan kode yang lain.',
            'nama_prodi.required'                => 'Nama prodi wajib diisi.',
            'nama_prodi.string'                  => 'Nama prodi harus berupa teks.',
            'nama_prodi.max'                     => 'Nama prodi maksimal 50 karakter.',
            'nama_prodi.unique'                  => 'Nama prodi sudah terdaftar, silakan gunakan nama yang lain.',
            'jenjang.required'                   => 'Jenjang wajib dipilih.',
            'jenjang.in'                         => 'Jenjang hanya boleh D3 atau D4.',
            'semester_magang.required'           => 'Semester magang wajib diisi.',
            'semester_magang.integer'            => 'Semester magang harus berupa angka.',
            'semester_magang.min'                => 'Semester magang minimal 1.',
            'semester_magang.max'                => 'Semester magang maksimal 8.',
            'semester_pengajuan_magang.required' => 'Semester pengajuan magang wajib diisi.',
            'semester_pengajuan_magang.integer'  => 'Semester pengajuan magang harus berupa angka.',
            'semester_pengajuan_magang.min'      => 'Semester pengajuan magang minimal 1.',
            'semester_pengajuan_magang.max'      => 'Semester pengajuan magang maksimal 8.',
        ]);

        $prodi->update($validated);

        return redirect()->route('admin.prodi.index')
            ->with('success', 'Program Studi berhasil diperbarui');
    }

    // hapus data prodi
    public function destroy($id_prodi)
    {
        $prodi = Prodi::findOrFail($id_prodi);
        $prodi->delete();

        return redirect()->route('admin.prodi.index')
            ->with('success', 'Program Studi berhasil dihapus');
    }

    // halaman form import excel
    public function showImportForm()
    {
        $jurusan = Jurusan::orderBy('nama_jurusan', 'asc')->get();
        return view('pages.admin.prodi.import', compact('jurusan'));
    }

    // proses import data dari file excel
    public function importExcel(Request $request)
    {
        // validasi file dulu
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file_excel.required' => 'File Excel wajib dipilih.',
            'file_excel.file'     => 'Yang dipilih harus berupa file.',
            'file_excel.mimes'    => 'File harus berupa format Excel (.xlsx, .xls, .csv).',
            'file_excel.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        $import = new ProdiImport();

        // try catch buat handle error pas baca file
        try {
            Excel::import($import, $request->file('file_excel'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return redirect()->back()->with('error', 'Format file tidak sesuai.')->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        // ambil hasil import nya, ada berapa yang berhasil dan gagal
        $successCount = $import->getSuccessCount();
        $failCount = $import->getFailCount();
        $errors = $import->getErrors();

        // klo ada error, simpan ke session buat ditampilkan
        if (!empty($errors)) {
            session()->flash('import_errors', $errors);
        }

        // susun pesan hasil import
        $message = "Import selesai! Berhasil: {$successCount} data.";
        if ($failCount > 0) {
            $message .= " Gagal: {$failCount} data.";
        }

        return redirect()->route('admin.prodi.index')
            ->with('success', $message)
            ->with('import_done', true);
    }

    // buat dan download template excel buat import
    public function downloadTemplate()
    {
        $jurusan = Jurusan::orderBy('nama_jurusan', 'asc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // header kolom
        $headers = ['jurusan', 'kode_prodi', 'nama_prodi', 'jenjang', 'semester_magang', 'semester_pengajuan_magang'];
        $columnLetters = ['A', 'B', 'C', 'D', 'E', 'F'];

        // styling header, warna orange biar keliatan
        foreach ($headers as $index => $header) {
            $sheet->setCellValue($columnLetters[$index] . '1', $header);
            $sheet->getStyle($columnLetters[$index] . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'F97316']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ]);
        }

        // Set format kolom B dan C sebagai TEXT
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->getStyle('C')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // kasih contoh data biar user tau formatnya
        $sheet->setCellValue('A2', $jurusan->first()->nama_jurusan ?? 'Teknologi Informasi');
        $sheet->setCellValueExplicit('B2', 'TI', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C2', 'Teknik Informatika', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('D2', 'D3');
        $sheet->setCellValue('E2', '5');
        $sheet->setCellValue('F2', '4');

        // lebar kolom disesuaikan
        $widths = [30, 20, 35, 12, 18, 24];
        foreach ($widths as $index => $width) {
            $sheet->getColumnDimension($columnLetters[$index])->setWidth($width);
        }

        // bikin sheet kedua buat petunjuk pengisian
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Petunjuk');
        $instructions = [
            'PETUNJUK IMPORT DATA PROGRAM STUDI',
            '',
            'KOLOM YANG HARUS DIISI:',
            '1. jurusan       - Nama jurusan (harus sesuai dengan data di database)',
            '2. kode_prodi    - Kode prodi (HARUS UNIK, tidak boleh sama dengan data yang sudah ada)',
            '3. nama_prodi    - Nama program studi (HARUS UNIK, tidak boleh sama dengan data yang sudah ada)',
            '4. jenjang       - Isi dengan "D3" atau "D4" saja',
            '5. semester_magang           - Semester pelaksanaan magang (angka 1-8)',
            '6. semester_pengajuan_magang - Semester pengajuan magang (angka 1-8)',
            '',
            'CATATAN PENTING:',
            '- Kode prodi HARUS UNIK. Jika duplikat dengan data yang sudah ada, baris tersebut akan GAGAL.',
            '- Nama prodi HARUS UNIK. Jika duplikat dengan data yang sudah ada, baris tersebut akan GAGAL.',
            '- Jika dalam satu file Excel terdapat kode_prodi atau nama_prodi yang sama, baris duplikatnya akan GAGAL.',
            '- Jurusan harus sesuai dengan data di database (gunakan nama jurusan yang tepat).',
            '- Jenjang hanya boleh diisi D3 atau D4.',
            '- Baris pertama adalah header, jangan diubah atau dihapus.',
            '- Hapus baris contoh sebelum import data asli.',
            '',
            'CONTOH DATA YANG BENAR:',
            'Teknologi Informasi | TI | Teknik Informatika | D4 | 5 | 4',
            'Teknologi Informasi | MI | Manajemen Informatika | D3 | 5 | 4',
        ];

        foreach ($instructions as $index => $instruction) {
            $sheet2->setCellValue('A' . ($index + 1), $instruction);
            if ($index === 0) {
                $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            }
        }
        $sheet2->getColumnDimension('A')->setWidth(100);

        // stream file ke browser buat download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="Template_Import_Prodi.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}