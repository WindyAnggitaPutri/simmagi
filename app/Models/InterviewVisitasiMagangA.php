<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewVisitasiMagangA extends Model
{
    use HasFactory;

    // Nama tabel yang dipake di database
    protected $table = 'interview_visitasi_magang_a';
    
    // Primary key dari tabel ini
    protected $primaryKey = 'id_interview_magang_a';
    
    // Dimatiin soalnya di tabel ini nggak ada kolom created_at dan updated_at bawaan Laravel
    public $timestamps = false;

    // Daftar kolom yang boleh diisi langsung lewat kode
    // Isinya rincian data interview hasil visitasi, kayak penilaian sikap, kedisiplinan, sampe saran
    protected $fillable = [
        'id_dosen_visitasi',
        'id_perusahaan',
        'penerima',
        'jabatan_penerima',
        'sikap_perilaku',
        'kedisiplinan_motivasi',
        'kekurangan_peserta',
        'peran_peserta',
        'adaptasi_komunikasi',
        'kompetensi_kesesuaian',
        'penempatan_dan_tugas',
        'kepuasan_perusahaan',
        'saran_masukan',
    ];
}