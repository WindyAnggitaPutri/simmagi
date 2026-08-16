<?php

// File model ini berfungsi untuk mengatur data penilaian mahasiswa dari pihak perusahaan tempat magang

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianMahasiswa extends Model
{
    // Menentukan nama tabel yang dipakai di database
    protected $table = 'penilaian_mahasiswa';
    // Menentukan kolom primary key dari tabel
    protected $primaryKey = 'id_penilaian';
    // Mematikan fitur bawaan Laravel untuk kolom waktu created_at dan updated_at otomatis
    public $timestamps = false;

    // Daftar kolom yang boleh diisi dengan data secara massal
    protected $fillable = [
        'id_token',
        'id_mahasiswa',
        'id_perusahaan',
        'nama_pengisi',
        'jabatan_pengisi',
        'email_pengisi',
        'pernyataan_keaslian',
        'kedisiplinan',
        'adaptasi',
        'tanggung_jawab',
        'kemandirian',
        'inisiatif_kreativitas',
        'kesesuaian_instruksi',
        'kualitas_pekerjaan',
        'ketepatan_waktu',
        'problem_solving',
        'teamwork',
        'hubungan_atasan',
        'hubungan_rekan',
        'hubungan_relasi',
        'sikap_kerja',
        'disiplin_kerja',
        'loyalitas',
        'motivasi',
        'penampilan',
        'total_nilai',   // ← TAMBAHKAN INI
        'rata_rata',     // ← TAMBAHKAN INI
        'catatan',
          'ttd_digital',   
        'created_at',
    ];
}