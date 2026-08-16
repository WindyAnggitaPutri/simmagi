<?php

// File model ini untuk menyimpan data surat pengajuan magang, baik itu surat permohonan maupun surat pengantar

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratPengajuan extends Model
{
    use HasFactory;

    // Menentukan nama tabel
    protected $table = 'surat_pengajuan';
    // Menentukan primary key
    protected $primaryKey = 'id_surat';
    // Primary key bertambah otomatis
    public $incrementing = true;
    // Tipe data primary key integer
    protected $keyType = 'int';

    // Mematikan fitur waktu otomatis
    public $timestamps = false;

    // Daftar kolom yang bisa diisi
    protected $fillable = [
        'id_pengajuan',
        'jenis_surat', // 'permohonan' atau 'pengantar'
        'file_surat',
        'uploaded_by',
        'uploaded_at',
    ];

    // Mengatur kolom uploaded_at agar berupa format tanggal dan waktu
    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    // Fungsi relasi ke tabel PengajuanMagang
    // Menunjukkan bahwa surat ini bagian dari satu pengajuan magang
    public function pengajuan()
    {
        return $this->belongsTo(PengajuanMagang::class, 'id_pengajuan', 'id_pengajuan');
    }

    // Fungsi relasi ke tabel Pegawai
    // Menunjukkan siapa pegawai yang mengupload surat tersebut
    public function uploader()
    {
        return $this->belongsTo(Pegawai::class, 'uploaded_by', 'id_pegawai');
    }
}