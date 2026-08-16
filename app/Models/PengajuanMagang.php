<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanMagang extends Model
{
    // Nama tabel di database
    protected $table = 'pengajuan_magang';

    // Primary key-nya
    protected $primaryKey = 'id_pengajuan';

    // Timestamps dimatiin
    public $timestamps = false;

    // Kolom yang boleh diisi massal, isinya data soal pengajuan magang
    protected $fillable = [
        'id_user',
        'id_perusahaan',
        'tgl_pengajuan',
        'catatan',
        'ditujukan_kepada',
        'email_perusahaan',
        'contact_person',
        'no_hp_cp',
        'tgl_mulai',
        'tgl_selesai',
        'deskripsi_perusahaan',
    ];

    // Relasi ke tabel pengajuan mahasiswa, satu pengajuan bisa berisi banyak mahasiswa (kalau kelompok)
    public function mahasiswa()
    {
        return $this->hasMany(PengajuanMahasiswa::class, 'id_pengajuan', 'id_pengajuan');
    }

    // Relasi ke tabel perusahaan tempat mahasiswa ngajuin magang
    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan', 'id_perusahaan');
    }

    // Relasi ke tabel approval pengajuan, satu pengajuan bisa punya beberapa data approval (dari kaprodi, kajur, dll)
    public function approvals()
    {
        return $this->hasMany(ApprovalPengajuan::class, 'id_pengajuan', 'id_pengajuan');
    }

    // ==================== RELASI SURAT BARU ====================
    
    // Surat Permohonan (diupload BAAK setelah approve permohonan)
    // Ambil data surat permohonan yang jenisnya 'permohonan'
    public function suratPermohonan()
    {
        return $this->hasOne(SuratPengajuan::class, 'id_pengajuan', 'id_pengajuan')
                    ->where('jenis_surat', 'permohonan');
    }

    // Surat Pengantar (diupload BAAK setelah Kaprodi verifikasi balasan)
    // Ambil data surat pengantar yang jenisnya 'pengantar'
    public function suratPengantar()
    {
        return $this->hasOne(SuratPengajuan::class, 'id_pengajuan', 'id_pengajuan')
                    ->where('jenis_surat', 'pengantar');
    }

    // Semua surat terkait pengajuan ini
    public function suratPengajuan()
    {
        return $this->hasMany(SuratPengajuan::class, 'id_pengajuan', 'id_pengajuan');
    }
}