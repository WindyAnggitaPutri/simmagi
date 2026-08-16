<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifikasiEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $judul;
    public $pesan;
    public $tipe;
    public $namaPenerima;
    public $link;
    public $kategori;
    public $badgeColor;

    public function __construct($judul, $pesan, $tipe, $namaPenerima = null, $link = null)
    {
        $this->judul = $judul;
        $this->pesan = $pesan;
        $this->tipe = $tipe;
        $this->namaPenerima = $namaPenerima;
        $this->link = $link ?? url('/notifikasi');

        $this->kategori = $this->getKategori();
        $this->badgeColor = $this->getBadgeColor();
    }

    public function build()
    {
        return $this->subject('[SIMMAGI] ' . $this->judul)
                    ->view('pages.notifikasi_email');
    }

    private function getKategori()
    {
        $map = [
            'approval_kaprodi' => 'PENGAJUAN MAGANG',
            'approval_kajur' => 'PENGAJUAN MAGANG',
            'approval_baak' => 'PENGAJUAN MAGANG',
            'approval_pengajuan' => 'PENGAJUAN MAGANG',
            'ditolak' => 'PENGAJUAN MAGANG',
            'pengajuan_magang' => 'PENGAJUAN MAGANG',
            'kaprodi_pengajuan_baru' => 'PENGAJUAN MAGANG',
'kajur_pengajuan_magang' => 'PENGAJUAN MAGANG',
'admin_kajur_pengajuan_magang' => 'PENGAJUAN MAGANG',
'baak_pengajuan_magang' => 'PENGAJUAN MAGANG',
            'surat_permohonan' => 'SURAT PERMOHONAN',
            'kaprodi_surat_permohonan' => 'SURAT PERMOHONAN',
            'surat_pengantar' => 'SURAT PENGANTAR',
            'kaprodi_surat_pengantar' => 'SURAT PENGANTAR',
            'verifikasi_balasan' => 'VERIFIKASI SURAT',
            'surat_balasan' => 'VERIFIKASI SURAT',
            'kaprodi_surat_balasan' => 'VERIFIKASI SURAT',
            'baak_surat_balasan_verifikasi' => 'VERIFIKASI SURAT',
            'dosen_pembimbing' => 'DOSEN PEMBIMBING',
            'kaprodi_dospem_disetujui_kajur' => 'DOSEN PEMBIMBING',
            'kaprodi_dospem_ditolak_kajur' => 'DOSEN PEMBIMBING',
            'kaprodi_dospem_upload_sk' => 'DOSEN PEMBIMBING',
            'kajur_dospem_baru' => 'DOSEN PEMBIMBING',
            'baak_dospem_disetujui_kajur' => 'DOSEN PEMBIMBING',
            'dosen_visitasi' => 'DOSEN VISITASI',
            'kaprodi_visitasi_disetujui_kajur' => 'DOSEN VISITASI',
            'kaprodi_visitasi_ditolak_kajur' => 'DOSEN VISITASI',
            'kaprodi_visitasi_upload_sk' => 'DOSEN VISITASI',
            'kajur_visitasi_baru' => 'DOSEN VISITASI',
            'baak_visitasi_upload_sk' => 'DOSEN VISITASI',
            'baup_visitasi_disetujui_kajur' => 'DOSEN VISITASI',
            'logbook_minggu' => 'LOGBOOK MINGGUAN',
            'jadwal_semgang' => 'JADWAL SEMINAR',
            'jadwal_seminar' => 'JADWAL SEMINAR',
            'kaprodi_seminar_selesai' => 'JADWAL SEMINAR',
            'kaprodi_token_digunakan' => 'TOKEN PERUSAHAAN',
            'baak_visitasi_disetujui_kajur' => 'DOSEN VISITASI',
        ];

        return $map[$this->tipe] ?? 'NOTIFIKASI';
    }

    private function getBadgeColor()
    {
        $colors = [
            'PENGAJUAN MAGANG' => '#059669',
            'SURAT PERMOHONAN' => '#059669',
            'SURAT PENGANTAR' => '#059669',
            'VERIFIKASI SURAT' => '#059669',
            'DOSEN PEMBIMBING' => '#059669',
            'DOSEN VISITASI' => '#059669',
            'LOGBOOK MINGGUAN' => '#059669',
            'JADWAL SEMINAR' => '#059669',
            'TOKEN PERUSAHAAN' => '#059669',
        ];

        return $colors[$this->kategori] ?? '#6B7280';
    }
}