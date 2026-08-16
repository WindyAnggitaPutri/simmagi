<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\PengajuanMagang;

class SuratMagangMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuan;
    public $filePath;
    public $jenisSurat;

    public function __construct(PengajuanMagang $pengajuan, $filePath, $jenisSurat = 'permohonan')
    {
        $this->pengajuan = $pengajuan;
        $this->filePath = $filePath;
        $this->jenisSurat = $jenisSurat; // 'permohonan' atau 'pengantar'
    }

    public function build()
    {
        $namaPerusahaan = $this->pengajuan->perusahaan->nama_perusahaan ?? 'Perusahaan';
        $namaMahasiswa = $this->pengajuan->mahasiswa->map(function($pm) {
            return $pm->mahasiswa->nama ?? '-';
        })->implode(', ');

        $periode = '-';
        if ($this->pengajuan->tgl_mulai && $this->pengajuan->tgl_selesai) {
            $periode = \Carbon\Carbon::parse($this->pengajuan->tgl_mulai)->format('d M Y') 
                     . ' s/d ' 
                     . \Carbon\Carbon::parse($this->pengajuan->tgl_selesai)->format('d M Y');
        }

        $subject = $this->jenisSurat === 'permohonan'
            ? 'Surat Permohonan Magang - ' . $namaPerusahaan
            : 'Surat Pengantar Magang - ' . $namaPerusahaan;

        $email = $this->subject($subject)
                      ->view('emails.surat_magang')
                      ->with([
                          'namaPerusahaan' => $namaPerusahaan,
                          'namaMahasiswa' => $namaMahasiswa,
                          'periode' => $periode,
                          'ditujukanKepada' => $this->pengajuan->ditujukan_kepada ?? '-',
                          'jenisSurat' => $this->jenisSurat,
                      ]);

        // Lampirkan file surat
        if (file_exists($this->filePath)) {
            $email->attach($this->filePath, [
                'as' => $this->jenisSurat === 'permohonan' 
                    ? 'Surat_Permohonan_Magang.pdf' 
                    : 'Surat_Pengantar_Magang.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $email;
    }
}