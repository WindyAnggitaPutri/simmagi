<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalPengajuan extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'approval_pengajuan';
    
    // Primary key-nya
    protected $primaryKey = 'id_approval_pengajuan';
    
    // Nggasih tau laravel kalau id-nya itu angka berurut (auto-increment)
    public $incrementing = true;
    protected $keyType = 'int';

    // Dimatiin soalnya nggak ada kolom created_at & updated_at bawaan laravel
    public $timestamps = false;

    // Kolom yang boleh diisi massal
    protected $fillable = [
        'id_pengajuan',
        'id_dosen',
        'id_pegawai',   // <-- TAMBAHKAN INI
        'id_jabatan',
        'status',
        'catatan',
        'approved_at',
    ];

    // Format approved_at diubah jadi datetime biar gampang diproses
    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // Relasi ke pengajuan magang
    // Artinya: satu data approval ini cuman milik satu data pengajuan magang
    public function pengajuan()
    {
        return $this->belongsTo(PengajuanMagang::class, 'id_pengajuan', 'id_pengajuan');
    }

    // Relasi ke dosen
    // Satu data approval ini cuman milik satu dosen
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen', 'id_dosen');
    }

    // Relasi ke pegawai (TAMBAHKAN INI)
    // Satu data approval ini cuman milik satu pegawai
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai', 'id_pegawai');
    }

    // Relasi ke jabatan
    // Satu data approval ini cuman milik satu jabatan
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan', 'id_jabatan');
    }
}