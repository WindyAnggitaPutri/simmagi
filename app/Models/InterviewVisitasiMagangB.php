<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewVisitasiMagangB extends Model
{
    use HasFactory;

    // Nama tabel yang dipake di database
    protected $table = 'interview_visitasi_magang_b';
    
    // Primary key dari tabel ini
    protected $primaryKey = 'id_interview_magang_b';
    
    // Timestamps dimatiin biar Laravel nggak nyari kolom waktu bawaan
    public $timestamps = false;

    // Kolom yang boleh diisi massal
    // Isinya data soal perusahaan tempat magang, kayak skala perusahaan, K3, fasilitas, sampe rekomendasi
    protected $fillable = [
        'id_dosen_visitasi',
        'id_perusahaan',
        'skala_perusahaan',
        'bidang_usaha',
        'penerapan_k3',
        'fasilitas_magang',
        'tanggapan_perusahaan',
        'kelebihan_perusahaan',
        'kekurangan_perusahaan',
        'rekomendasi',
        'catatan',
        'id_dosen',
    ];
}