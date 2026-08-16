<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// Class ini digunakan untuk mengatur proses export data dosen visitasi
// ke dalam bentuk file Excel
class DosenVisitasiExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    // Menyimpan data pembagian dosen visitasi
    protected $data;

    // Menyimpan data status interview personal magang
    protected $hasInterviewA;
    protected $hasInterviewB;

    // Constructor menerima data dari controller
    // kemudian menyimpan data tersebut ke dalam variabel class
    public function __construct($data, $hasInterviewA = null, $hasInterviewB = null)
    {
        $this->data = $data;
        $this->hasInterviewA = $hasInterviewA;
        $this->hasInterviewB = $hasInterviewB;
    }

    // Mengatur data yang akan dimasukkan ke dalam Excel
    public function collection()
    {
        // Melakukan perulangan setiap data dosen visitasi
        return $this->data->map(function ($item, $index) {

            // Mengecek apakah mahasiswa sudah melakukan
            // interview personal magang A dan B
            $isSelesai = $this->hasInterviewA 
                && $this->hasInterviewB 
                && $this->hasInterviewA->has($item->id_dosen_visitasi) 
                && $this->hasInterviewB->has($item->id_dosen_visitasi);

            // Mengembalikan data dalam bentuk array
            // yang akan menjadi isi tabel Excel
            return [

                // Nomor urut data
                'no'                        => $index + 1,

                // Mengambil nama dosen visitasi
                // Jika tidak ada data maka tampil "-"
                'nama_dosen'                => $item->dosen->nama ?? '-',

                // Mengambil NIDN dosen
                'nidn'                      => $item->dosen->nidn ?? '-',

                // Mengambil nama mahasiswa
                'nama_mahasiswa'            => $item->mahasiswa->nama ?? '-',

                // Mengambil tahun angkatan mahasiswa
                'tahun_angkatan'            => $item->mahasiswa->angkatan ?? '-',

                // Mengambil kelas mahasiswa
                'kelas'                     => $item->mahasiswa->kelas ?? '-',

                // Mengambil NPM mahasiswa
                'npm'                       => $item->mahasiswa->npm ?? '-',

                // Mengambil nama perusahaan tempat magang
                'perusahaan'                => $item->perusahaan->nama_perusahaan ?? '-',

                // Mengambil tahun visitasi dilakukan
                'tahun_visitasi'            => $item->tahun ?? '-', // Kolom tahun visitasi

                // Menampilkan status visitasi berdasarkan hasil pengecekan
                'visitasi'                  => $isSelesai ? 'Selesai' : 'Belum',

                // Jika interview sudah selesai maka tampil "Lihat"
                // Jika belum selesai maka tampil "-"
                'interview_personal_magang' => $isSelesai ? 'Lihat' : '-',
            ];
        });
    }


    // Mengatur judul setiap kolom pada Excel
    public function headings(): array
    {
        return [
            'No',
            'Nama Dosen Visitasi',
            'NIDN',
            'Nama Mahasiswa',
            'Tahun Angkatan',
            'Kelas',
            'NPM',
            'Perusahaan',
            'Tahun Visitasi',
            'Visitasi',
            'Interview Personal Magang',
        ];
    }


    // Mengatur nama sheet Excel
    public function title(): string
    {
        return 'Pembagian Dosen Visitasi';
    }


    // Mengatur tampilan tabel Excel
    public function styles(Worksheet $sheet)
    {
        // Mengambil jumlah baris terakhir pada Excel
        $highestRow = $sheet->getHighestRow();


        // Memberikan style pada bagian header tabel
        // Mulai dari kolom A sampai K
        $sheet->getStyle('A1:K1')->applyFromArray([

            // Membuat tulisan header menjadi tebal dan warna putih
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],

            // Memberikan warna background pada header
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EAB308']],

            // Mengatur posisi tulisan header berada di tengah
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],

            // Memberikan garis pada semua sisi tabel
            'borders'   => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ],
        ]);


        // Membuat ukuran kolom otomatis sesuai isi data
        // agar tulisan tidak terpotong saat dibuka
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }


        // Jika data lebih dari header saja
        // maka berikan style untuk isi tabel
        if ($highestRow > 1) {

            // Memberikan garis tabel dan posisi teks
            // untuk seluruh data dari baris kedua sampai terakhir
            $sheet->getStyle('A2:K' . $highestRow)->applyFromArray([
                'borders'   => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN
                    ]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
            ]);


            // Membuat beberapa kolom berada di tengah
            // agar tampilan Excel lebih rapi
            $sheet->getStyle('A2:A' . $highestRow)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle('E2:F' . $highestRow)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle('I2:K' . $highestRow)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
    }
}