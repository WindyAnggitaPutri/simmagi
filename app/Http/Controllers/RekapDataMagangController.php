<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\DosenPembimbing;
use App\Models\PenilaianAkhir;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

class RekapDataMagangController extends Controller
{
    public function index()
    {
        if (auth()->user()->role != 'dosen' || session('dosen_active_role') != 'kaprodi') {
            abort(403, 'Akses ditolak. Hanya untuk Kaprodi.');
        }

        return view('pages.kaprodi.rekap.index');
    }

    public function download()
    {
        if (auth()->user()->role != 'dosen' || session('dosen_active_role') != 'kaprodi') {
            abort(403, 'Akses ditolak. Hanya untuk Kaprodi.');
        }

        // Ambil data dosen (kaprodi) yang login untuk dapatkan id_prodi
        $dosen = Dosen::where('id_user', auth()->user()->id_user)->first();
        if (!$dosen) {
            abort(404, 'Data dosen tidak ditemukan.');
        }

        $idProdi = $dosen->id_prodi;
        $namaProdi = $dosen->prodi->nama_prodi ?? '-';

        // =============================================
        // DATA 1: Mahasiswa dan Kelasnya (1 prodi kaprodi)
        // =============================================
        $dataMahasiswa = Mahasiswa::where('id_prodi', $idProdi)
            ->orderBy('kelas')
            ->orderBy('nama')
            ->get();

        // =============================================
        // DATA 2: Dosen Pembimbing dan Mahasiswa (1 prodi kaprodi)
        // =============================================
        $dataDosenPembimbing = DosenPembimbing::whereHas('mahasiswa', function ($q) use ($idProdi) {
            $q->where('id_prodi', $idProdi);
        })->with(['dosen', 'mahasiswa'])
            ->get();

        // =============================================
        // DATA 3: Mahasiswa (NPM, Nama, Kelas) dan Nilai Akhir (1 prodi kaprodi)
        // =============================================
        $dataNilaiAkhir = PenilaianAkhir::whereHas('mahasiswa', function ($q) use ($idProdi) {
            $q->where('id_prodi', $idProdi);
        })->with(['mahasiswa'])
            ->get();

        // =============================================
        // BUAT DOKUMEN WORD
        // =============================================
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'marginLeft' => 1134,
            'marginRight' => 1134,
            'marginTop' => 1134,
            'marginBottom' => 1134,
        ]);

        // ---- Judul ----
        $section->addText(
            'REKAP DATA MAGANG',
            ['bold' => true, 'size' => 16],
            ['alignment' => Jc::CENTER]
        );
        $section->addText(
            strtoupper($namaProdi),
            ['bold' => true, 'size' => 12],
            ['alignment' => Jc::CENTER]
        );
        $section->addText('');

        // =============================================
        // TABEL 1: Mahasiswa dan Kelas
        // =============================================
        $section->addText('1. Rekap Mahasiswa dan Kelas', ['bold' => true, 'size' => 12]);
        $section->addText('');

        $table1Style = ['borderSize' => 1, 'borderColor' => '000000', 'cellMargin' => 80];
        $headerStyle = ['bold' => true, 'size' => 10];
        $cellStyle = ['size' => 10];
        $centerAlign = ['alignment' => Jc::CENTER];

        $table1 = $section->addTable($table1Style);
        $table1->addRow();
        $table1->addCell(800)->addText('No', $headerStyle, $centerAlign);
        $table1->addCell(2200)->addText('NPM', $headerStyle, $centerAlign);
        $table1->addCell(4500)->addText('Nama Mahasiswa', $headerStyle, $centerAlign);
        $table1->addCell(2000)->addText('Kelas', $headerStyle, $centerAlign);

        if ($dataMahasiswa->count() > 0) {
            $no = 1;
            foreach ($dataMahasiswa as $mhs) {
                $table1->addRow();
                $table1->addCell(800)->addText($no++, $cellStyle, $centerAlign);
                $table1->addCell(2200)->addText($mhs->npm, $cellStyle, $centerAlign);
                $table1->addCell(4500)->addText($mhs->nama, $cellStyle);
                $table1->addCell(2000)->addText($mhs->kelas_lengkap, $cellStyle, $centerAlign);
            }
        } else {
            $table1->addRow();
            $table1->addCell(9500, ['gridSpan' => 4])->addText('Belum ada data mahasiswa.', $cellStyle, $centerAlign);
        }

        $section->addText('');
        $section->addText('');

        // =============================================
        // TABEL 2: Dosen Pembimbing dan Mahasiswa
        // =============================================
        $section->addText('2. Rekap Dosen Pembimbing dan Mahasiswa', ['bold' => true, 'size' => 12]);
        $section->addText('');

        $table2 = $section->addTable($table1Style);
        $table2->addRow();
        $table2->addCell(600)->addText('No', $headerStyle, $centerAlign);
        $table2->addCell(2500)->addText('Nama Dosen', $headerStyle, $centerAlign);
        $table2->addCell(1500)->addText('NIDN', $headerStyle, $centerAlign);
        $table2->addCell(2500)->addText('Nama Mahasiswa', $headerStyle, $centerAlign);
        $table2->addCell(1900)->addText('NPM', $headerStyle, $centerAlign);

        if ($dataDosenPembimbing->count() > 0) {
            $no = 1;
            foreach ($dataDosenPembimbing as $dp) {
                $table2->addRow();
                $table2->addCell(600)->addText($no++, $cellStyle, $centerAlign);
                $table2->addCell(2500)->addText($dp->dosen->nama ?? '-', $cellStyle);
                $table2->addCell(1500)->addText($dp->dosen->nidn ?? '-', $cellStyle, $centerAlign);
                $table2->addCell(2500)->addText($dp->mahasiswa->nama ?? '-', $cellStyle);
                $table2->addCell(1900)->addText($dp->mahasiswa->npm ?? '-', $cellStyle, $centerAlign);
            }
        } else {
            $table2->addRow();
            $table2->addCell(9000, ['gridSpan' => 5])->addText('Belum ada data dosen pembimbing.', $cellStyle, $centerAlign);
        }

        $section->addText('');
        $section->addText('');

        // =============================================
        // TABEL 3: Mahasiswa dan Nilai Akhir
        // =============================================
        $section->addText('3. Rekap Nilai Akhir Mahasiswa', ['bold' => true, 'size' => 12]);
        $section->addText('');

        $table3 = $section->addTable($table1Style);
        $table3->addRow();
        $table3->addCell(600)->addText('No', $headerStyle, $centerAlign);
        $table3->addCell(2000)->addText('NPM', $headerStyle, $centerAlign);
        $table3->addCell(3500)->addText('Nama Mahasiswa', $headerStyle, $centerAlign);
        $table3->addCell(1500)->addText('Kelas', $headerStyle, $centerAlign);
        $table3->addCell(1500)->addText('Nilai Akhir', $headerStyle, $centerAlign);
        $table3->addCell(900)->addText('Grade', $headerStyle, $centerAlign);

        if ($dataNilaiAkhir->count() > 0) {
            $no = 1;
            foreach ($dataNilaiAkhir as $na) {
                $table3->addRow();
                $table3->addCell(600)->addText($no++, $cellStyle, $centerAlign);
                $table3->addCell(2000)->addText($na->mahasiswa->npm ?? '-', $cellStyle, $centerAlign);
                $table3->addCell(3500)->addText($na->mahasiswa->nama ?? '-', $cellStyle);
                $table3->addCell(1500)->addText($na->mahasiswa->kelas_lengkap ?? '-', $cellStyle, $centerAlign);
                $table3->addCell(1500)->addText($na->nilai_akhir ?? '-', $cellStyle, $centerAlign);
                $table3->addCell(900)->addText($na->grade ?? '-', $cellStyle, $centerAlign);
            }
        } else {
            $table3->addRow();
            $table3->addCell(10000, ['gridSpan' => 6])->addText('Belum ada data nilai akhir.', $cellStyle, $centerAlign);
        }

        // =============================================
        // SIMPAN & DOWNLOAD
        // =============================================
        $filename = 'Rekap_Data_Magang_' . str_replace(' ', '_', $namaProdi) . '.docx';
        $tempPath = storage_path('app/temp/' . $filename);

        // Pastikan folder temp ada
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $phpWord->save($tempPath, 'Word2007');

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }
}