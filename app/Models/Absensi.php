<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $primaryKey = 'id_absensi';

    protected $fillable = [
        'id_mahasiswa', 'id_perusahaan', 'tanggal',
        'jam_masuk', 'jam_pulang',
        'foto_masuk', 'foto_pulang',
        'status',
        'latitude_masuk', 'longitude_masuk',
        'latitude_pulang', 'longitude_pulang',
        'alamat_masuk', 'alamat_pulang',
        'jarak_masuk', 'jarak_pulang',
        'status_validasi', 'validated_by', 'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    // --- Scopes ---

    public function scopeByMahasiswa($query, $id)
    {
        return $query->where('id_mahasiswa', $id);
    }

    public function scopeTerbaru($query)
    {
        return $query->orderBy('tanggal', 'desc')->orderBy('jam_masuk', 'desc');
    }

    public function scopeHadir($query)
    {
        return $query->where('status', 'hadir');
    }

    // --- Accessors (teks biasa, tanpa HTML, tanpa warna) ---

    public function getTanggalFormattedAttribute()
    {
        return Carbon::parse($this->tanggal)->format('d F Y');
    }

    public function getJamMasukFormattedAttribute()
    {
        return $this->jam_masuk ?? '-';
    }

    public function getJamPulangFormattedAttribute()
    {
        return $this->jam_pulang ?? '-';
    }

    public function getStatusTextAttribute()
    {
        return ucfirst($this->status);
    }

    public function getValidasiTextAttribute()
    {
        return ucfirst($this->status_validasi ?? 'pending');
    }

    public function getJarakMasukTampilAttribute()
    {
        if ($this->jarak_masuk !== null) {
            return round($this->jarak_masuk) . ' m';
        }
        return '-';
    }

    public function getJarakPulangTampilAttribute()
    {
        if ($this->jarak_pulang !== null) {
            return round($this->jarak_pulang) . ' m';
        }
        return '-';
    }

    // --- Relations ---

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id_mahasiswa');
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan', 'id_perusahaan');
    }
}