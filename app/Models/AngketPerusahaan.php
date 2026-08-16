<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AngketPerusahaan extends Model
{
    // Nama tabel yang dipake di database
    protected $table = 'angket_perusahaan';
    
    // Primary key dari tabel ini (bukan 'id' default dari laravel, tapi 'id_form_angket')
    protected $primaryKey = 'id_form_angket';
    
    // Dimatiin soalnya di tabel ini nggak ada kolom created_at & updated_at bawaan laravel
    public $timestamps = false;

    // Kolom-kolom yang boleh diisi langsung lewat kode (mass assignment)
    protected $fillable = [
        'id_token', 'id_perusahaan', 'nama_pengisi', 'jabatan_pengisi', 'email_pengisi',
        'pernyataan_keaslian', 'integritas_motivasi', 'etika_sopan_santun', 'kompetensi_keahlian',
        'kedisiplinan', 'respon_kecepatan_kerja', 'adaptasi_komunikasi', 'kerjasama_tim',
        'pengembangan_diri', 'kemampuan_bahasa_inggris', 'kemampuan_teknologi_informasi', 'catatan', 'created_at'
    ];

    // Otomatis ngubah tipe data pas diambil atau disimpen ke database
    protected $casts = [
        'pernyataan_keaslian' => 'boolean', // diubah jadi true/false
        'created_at' => 'datetime', // diubah jadi format tanggal dan waktu biar gampang diproses
    ];
}