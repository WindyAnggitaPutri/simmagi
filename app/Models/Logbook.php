<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logbook extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'logbook';
    
    // Primary key-nya
    protected $primaryKey = 'id_logbook';
    
    // Id-nya auto-increment dan tipenya integer
    public $incrementing = true;
    protected $keyType = 'int';
    
    // Timestamps dimatiin
    public $timestamps = false;

    // Kolom yang boleh diisi massal, isinya data kegiatan harian atau mingguan mahasiswa saat magang
    protected $fillable = [
        'id_mahasiswa',
        'id_dosen_pembimbing',
        'tanggal',
        'jam_mulai',      // ← TAMBAHKAN
    'jam_selesai',
        'minggu_ke',
        'kegiatan',
         'foto',
        'status',
        'approved_by',
        'approved_at',
        'catatan',
    ];

    // Ngubah format data pas diambil dari database
    protected $casts = [
        'tanggal' => 'date', // tanggal diubah jadi format date
        'approved_at' => 'datetime', // approved_at diubah jadi datetime
    ];

    // Relasi ke mahasiswa pemilik logbook
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id_mahasiswa');
    }

    // Relasi ke dosen pembimbing yang ngecek logbooknya
    public function dosenPembimbing()
    {
        return $this->belongsTo(DosenPembimbing::class, 'id_dosen_pembimbing', 'id_dosen_pembimbing');
    }

    // Relasi ke dosen yang nge-approve logbooknya
    public function approver()
    {
        return $this->belongsTo(Dosen::class, 'approved_by', 'id_dosen');
    }
}