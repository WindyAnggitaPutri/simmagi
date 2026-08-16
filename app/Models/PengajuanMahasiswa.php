<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanMahasiswa extends Model
{
    // Nama tabel di database
    protected $table = 'pengajuan_mahasiswa';

    // Primary key-nya
    protected $primaryKey = 'id_pengajuan_mahasiswa';

    // Timestamps dimatiin
    public $timestamps = false;

    // Kolom yang boleh diisi massal, isinya data status tiap mahasiswa di pengajuan tersebut
    protected $fillable = [
        'id_pengajuan',
        'id_mahasiswa',
        'ipk',
        'status',
        'surat_ortu',

        // TAMBAHAN BARU
        'surat_balasan',
        'status_surat_balasan',
        'verified_at',
        'verified_by',
        'catatan_verifikasi',
    ];

    // Relasi ke tabel pengajuan magang, data ini milik satu pengajuan magang
    public function pengajuan()
    {
        return $this->belongsTo(PengajuanMagang::class, 'id_pengajuan', 'id_pengajuan');
    }

    // Relasi ke tabel mahasiswa, data ini milik satu mahasiswa
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id_mahasiswa');
    }

    // OPTIONAL
    // Relasi ke tabel user yang diverifikasi suratnya
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by', 'id_user');
    }

    // Tambahkan di dalam class PengajuanMahasiswa

    // Relasi ke tabel dosen pembimbing, ambil satu data dospem yang cocok sama mahasiswa dan pengajuannya
public function dosenPembimbing()
{
    return $this->hasOne(DosenPembimbing::class, 'id_mahasiswa', 'id_mahasiswa')
                 ->where('id_pengajuan', $this->id_pengajuan); // Cek berdasarkan pengajuan spesifik
}
}