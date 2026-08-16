<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalLogbook extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'approval_logbook';
    
    // Primary key-nya
    protected $primaryKey = 'id_approval_logbook';

    // ✅ TAMBAHKAN BARIS INI AGAR LARAVEL TIDAK MENCARI KOLOM created_at & updated_at
    // Sama kayak sebelumnya, dimatiin biar laravel nggak otomatis nyari kolom timestamps bawaan
    public $timestamps = false;

    // Kolom yang boleh diisi massal
    protected $fillable = [
        'id_mahasiswa',
        'id_dosen_pembimbing',
        'minggu_ke',
        'status',
        'catatan',
        'approved_at',
    ];
}