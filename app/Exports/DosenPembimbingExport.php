<?php

namespace App\Exports;

use App\Models\DosenPembimbing;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Class ini digunakan untuk mengatur data Dosen Pembimbing
// agar bisa diekspor menjadi file Excel
class DosenPembimbingExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    // Menyimpan data dosen pembimbing yang akan ditampilkan di Excel
    protected $data;

    // Constructor digunakan untuk menerima data dari controller
    // lalu menyimpannya ke dalam variabel $data
    public function __construct($data)
    {
        $this->data = $data;
    }

    // Method ini mengatur isi data yang akan dimasukkan ke Excel
    public function collection()
    {
        // Melakukan perulangan pada setiap data dosen pembimbing
        // kemudian mengubah format data sesuai kebutuhan tabel Excel
        return $this->data->map(function ($item, $index) {

            // Mengembalikan data dalam bentuk array
            // setiap array akan menjadi satu baris di Excel
            return [
                // Nomor urut data
                'No'                    => $index + 1,

                // Mengambil NIDN dari data dosen pembimbing
                // Jika data tidak ditemukan maka tampil "-"
                'NIDN'                  => $item->dosen->nidn ?? '-',

                // Mengambil nama dosen pembimbing
                'Nama Dosen'            => $item->dosen->nama ?? '-',

                // Mengambil NPM mahasiswa yang dibimbing
                'NPM'                   => $item->mahasiswa->npm ?? '-',

                // Mengambil nama mahasiswa
                'Nama Mahasiswa'        => $item->mahasiswa->nama ?? '-',

                // Mengambil tahun angkatan mahasiswa
                'Tahun Angkatan'        => $item->mahasiswa->angkatan ?? '-',

                // Mengambil kelas mahasiswa
                'Kelas'                 => $item->mahasiswa->kelas ?? '-',

                // Mengambil nama perusahaan tempat mahasiswa magang
                'Nama Perusahaan'       => $item->pengajuanMagang->perusahaan->nama_perusahaan ?? '-',
            ];
        });
    }

    // Mengatur nama judul setiap kolom pada Excel
    public function headings(): array
    {
        return [
            'No',
            'NIDN',
            'Nama Dosen',
            'NPM',
            'Nama Mahasiswa',
            'Tahun Angkatan',
            'Kelas', 
            'Nama Perusahaan',
        ];
    }

    // Mengatur nama sheet pada file Excel
    public function title(): string
    {
        return 'Pembagian Dosen Pembimbing';
    }

    // Mengatur tampilan atau style tabel Excel
    public function styles($sheet)
    {
        // Memberikan style pada bagian header tabel (baris pertama)
        // Meliputi warna background, ukuran tulisan, posisi teks, dan garis tabel
        // H1 digunakan agar kolom Nama Perusahaan ikut mendapatkan style
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                // Membuat tulisan header menjadi tebal
                'bold'  => true,

                // Mengatur ukuran font header
                'size'  => 12,

                // Mengatur warna tulisan menjadi putih
                'color' => ['argb' => 'FFFFFF'],
            ],

            // Memberikan warna background pada header
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFD700'],
            ],

            // Mengatur posisi teks header agar berada di tengah
            'alignment' => [
                'horizontal' => 'center',
                'vertical'   => 'center',
            ],

            // Memberikan garis pada setiap sisi tabel
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Mengatur ukuran kolom otomatis sesuai panjang isi data
        // Supaya tulisan tidak terpotong ketika dibuka di Excel
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}