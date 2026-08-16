<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\NotifikasiEmail;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Pegawai;
use App\Models\Jabatan;
use Carbon\Carbon;

class Notifikasi extends Model
{
    // Nama tabel di database
    protected $table = 'notifikasi';
    
    // Primary key-nya
    protected $primaryKey = 'id_notifikasi';

    // Timestamps dimatiin, tapi di bawah diatur manual pas data baru dibuat
    public $timestamps = false;

    // Ngisi kolom created_at otomatis sama tanggal sekarang pas notifikasi baru dibuat
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? now();
        });
    }

    // Kolom yang boleh diisi massal
    protected $fillable = [
        'id_user',
        'judul',
        'pesan',
        'tipe',
        'reference_id',
        'read',
        'created_at',
    ];

    // Ngubah format data pas diambil dari database
    protected $casts = [
        'read' => 'boolean', // status udah dibaca apa belum jadi true/false
        'created_at' => 'datetime', // formatnya jadi datetime
    ];

    // Relasi ke tabel user, notifikasi ini ditujukan untuk user tertentu
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Accessor buat nampilkan waktu yang udah lalu (contoh: "1 jam yang lalu")
    public function getWaktuLaluAttribute()
    {
        if (!$this->created_at) return '';
        return Carbon::parse($this->created_at)->locale('id')->diffForHumans();
    }

    // Accessor buat nentuin icon apa yang muncul di tampilan berdasarkan tipe notifikasinya
    public function getIconAttribute()
    {
        $icons = [
            'approval_pengajuan'        => '',
            'approval_kaprodi'          => '',
            'approval_kajur'            => '',
            'approval_baak'             => '',
            'ditolak'                   => '',
            'pengajuan_magang'          => '',
            'surat_permohonan'          => '',
            'surat_pengantar'           => '',
            'verifikasi_balasan'        => '',
            'surat_balasan'             => '',
            'dosen_pembimbing'          => '',
            'dosen_visitasi'            => '',
            'logbook'                   => '',
            'logbook_disetujui'         => '',
            'logbook_ditolak'           => '',
            'logbook_minggu'            => '',
            'jadwal_semgang'            => '',
            'jadwal_seminar'            => '',
            'kaprodi_pengajuan_baru'          => '',
            'kaprodi_surat_permohonan'        => '',
            'kaprodi_surat_balasan'           => '',
            'kaprodi_surat_pengantar'         => '',
            'kaprodi_dospem_disetujui_kajur'  => '',
            'kaprodi_dospem_ditolak_kajur'    => '',
            'kaprodi_dospem_upload_sk'        => '',
            'kaprodi_visitasi_disetujui_kajur'=> '',
            'kaprodi_visitasi_ditolak_kajur'  => '',
            'kaprodi_visitasi_upload_sk'      => '',
            'kaprodi_seminar_selesai'         => '',
            'kaprodi_token_digunakan'         => '',
            'kajur_pengajuan_magang'    => '',
'kajur_dospem_baru'         => '',
'kajur_visitasi_baru'       => '',
'admin_kajur_pengajuan_magang'    => '',
'admin_kajur_surat_permohonan'    => '',
'admin_kajur_surat_balasan'       => '',
'admin_kajur_surat_pengantar'     => '',
            'baak_pengajuan_magang'          => '',
            'baak_surat_balasan_verifikasi'  => '',
            'baak_dospem_disetujui_kajur'    => '',
            'baak_visitasi_upload_sk'        => '',
            'baup_visitasi_disetujui_kajur'  => '',
            'baak_visitasi_disetujui_kajur' => '',
        ];
        return $icons[$this->tipe] ?? '';
    }

    // Accessor buat nentuin link tujuan pas notifikasi diklik, disesuaikan sama peran (role) user
    public function getLinkAttribute()
    {
        try {
            $user = $this->user;
            if (!$user) return '#';

            $role = $user->role;
            $refId = $this->reference_id;

            switch ($this->tipe) {

                // ==================== MAHASISWA ====================
                case 'logbook_disetujui':
                case 'logbook_ditolak':
                case 'logbook_minggu':
                    if ($role === 'mahasiswa' && $refId)
                        return route('mahasiswa.logbook.minggu', $refId);
                    return '#';

                case 'dosen_pembimbing':
                    if ($role === 'mahasiswa') return route('mahasiswa.dosen-pembimbing');
                    return '#';

                case 'dosen_visitasi':
                    if ($role === 'mahasiswa') return route('mahasiswa.dosen-visitasi');
                    return '#';

                case 'approval_kaprodi':
                case 'approval_kajur':
                case 'approval_baak':
                case 'approval_pengajuan':
                case 'ditolak':
                case 'pengajuan_magang':
                    if ($role === 'mahasiswa' && $refId)
                        return route('mahasiswa.pengajuan_magang.detail', ['id_pengajuan' => $refId]);
                    return '#';

                case 'surat_permohonan':
                    if ($role === 'mahasiswa' && $refId)
                        return route('mahasiswa.pengajuan_magang.surat_permohonan', ['id_pengajuan' => $refId]);
                    return '#';

                case 'verifikasi_balasan':
                case 'surat_balasan':
                    if ($role === 'mahasiswa' && $refId)
                        return route('mahasiswa.pengajuan_magang.upload_surat_balasan', ['id' => $refId]);
                    return '#';

                case 'surat_pengantar':
                    if ($role === 'mahasiswa' && $refId)
                        return route('mahasiswa.pengajuan_magang.surat_pengantar', ['id_pengajuan' => $refId]);
                    return '#';

                case 'jadwal_semgang':
                case 'jadwal_seminar':
                    if ($role === 'mahasiswa') return route('mahasiswa.pelaksanaan-seminar.index');
                    return '#';

                case 'logbook':
                    if ($role === 'mahasiswa') return route('mahasiswa.logbook.index');
                    return '#';

                // ==================== KAPRODI ====================
                case 'kaprodi_pengajuan_baru':
                    if ($role === 'dosen' && $refId)
                        return route('kaprodi.pengajuan_magang.detail', ['id_pengajuan' => $refId]);
                    return '#';

                case 'kaprodi_surat_permohonan':
                    if ($role === 'dosen' && $refId)
                        return route('kaprodi.pengajuan_magang.surat_permohonan', ['id_pengajuan' => $refId]);
                    return '#';

                case 'kaprodi_surat_balasan':
                    if ($role === 'dosen' && $refId)
                        return route('kaprodi.pengajuan_magang.verifikasi_surat_balasan', ['id' => $refId]);
                    return '#';

                case 'kaprodi_surat_pengantar':
                    if ($role === 'dosen' && $refId)
                        return route('kaprodi.pengajuan_magang.surat_pengantar', ['id_pengajuan' => $refId]);
                    return '#';

                case 'kaprodi_dospem_disetujui_kajur':
                case 'kaprodi_dospem_ditolak_kajur':
                case 'kaprodi_dospem_upload_sk':
                    if ($role === 'dosen') return route('kaprodi.dosen-pembimbing.index');
                    return '#';

                case 'kaprodi_visitasi_disetujui_kajur':
                case 'kaprodi_visitasi_ditolak_kajur':
                case 'kaprodi_visitasi_upload_sk':
                    if ($role === 'dosen') return route('kaprodi.pembagian-dosen-visitasi.index');
                    return '#';

                case 'kaprodi_seminar_selesai':
                    if ($role === 'dosen' && $refId)
                        return route('kaprodi.jadwal-semgang.detail-nilai', ['id' => $refId]);
                    return '#';

                case 'kaprodi_token_digunakan':
                    if ($role === 'dosen') return route('kaprodi.token-perusahaan.index');
                    return '#';

                // ==================== KAJUR ====================
                case 'kajur_pengajuan_magang':
                    if ($role === 'dosen' && $refId)
                        return route('kajur.pengajuan_magang.detail', ['id_pengajuan' => $refId]);
                    return '#';

                case 'kajur_dospem_baru':
                    if ($role === 'dosen') return route('kajur.dosen-pembimbing.index');
                    return '#';

                case 'kajur_visitasi_baru':
                    if ($role === 'dosen') return route('kajur.pembagian-dosen-visitasi.index');
                    return '#';
// ==================== ADMIN KAJUR ====================
case 'admin_kajur_pengajuan_magang':
    if ($role === 'pegawai' && $refId)
        return route('admin_kajur.pengajuan_magang.detail', ['id_pengajuan' => $refId]);
    return route('admin_kajur.pengajuan_magang.index');

case 'admin_kajur_surat_permohonan':
    if ($role === 'pegawai' && $refId)
        return route('admin_kajur.pengajuan_magang.surat_permohonan', ['id_pengajuan' => $refId]);
    return route('admin_kajur.pengajuan_magang.index');

case 'admin_kajur_surat_balasan':
    if ($role === 'pegawai' && $refId)
        return route('admin_kajur.lihat_surat_balasan', ['id' => $refId]);
    return route('admin_kajur.pengajuan_magang.index');

case 'admin_kajur_surat_pengantar':
    if ($role === 'pegawai' && $refId)
        return route('admin_kajur.pengajuan_magang.surat_pengantar', ['id_pengajuan' => $refId]);
    return route('admin_kajur.pengajuan_magang.index');


// ==================== BAAK ====================
case 'baak_pengajuan_magang':
                    if ($role === 'pegawai' && $refId)
                        return route('baak.pengajuan_magang.detail', ['id_pengajuan' => $refId]);
                    return route('baak.pengajuan_magang.index');

                case 'baak_surat_balasan_verifikasi':
                    if ($role === 'pegawai' && $refId)
                        return route('baak.pengajuan_magang.detail', ['id_pengajuan' => $refId]);
                    return route('baak.pengajuan_magang.index');

                case 'baak_dospem_disetujui_kajur':
                    if ($role === 'pegawai')
                        return route('baak.dosen-pembimbing.index');
                    return '#';

                case 'baak_visitasi_upload_sk':
                    if ($role === 'pegawai')
                        return route('baak.dosen-visitasi.index');
                    return '#';

                // ==================== BAUP ====================
                case 'baup_visitasi_disetujui_kajur':
                    if ($role === 'pegawai')
                        return route('baup.dosen-visitasi.index');
                    return '#';

                default:
                    return '#';
            }
        } catch (\Exception $e) {
            return '#';
        }
    }

    /**
     *  KIRIM NOTIFIKASI + EMAIL
     */
public static function kirim($id_user, $judul, $pesan, $tipe, $reference_id = null)
{
    // ==================== 1. SIMPAN KE DB ====================
    // Simpan data notifikasi ke database
    $notif = self::create([
        'id_user'      => $id_user,
        'judul'        => $judul,
        'pesan'        => $pesan,
        'tipe'         => $tipe,
        'reference_id' => $reference_id,
        'read'         => 0,
        'created_at'   => now(),
    ]);

    // ==================== 2. KIRIM EMAIL ====================
    //  WHITELIST: Hanya tipe ini yang dikirim via email
    $emailTypes = [
        // ==================== MAHASISWA ====================
        'approval_kaprodi',
        'approval_kajur',
        'approval_baak',
        'approval_pengajuan',
        'pengajuan_magang',
        'ditolak',
        'surat_permohonan',
        'surat_pengantar',
        'verifikasi_balasan',
        'surat_balasan',
        'dosen_pembimbing',
        'dosen_visitasi',
        'logbook_minggu',
        'jadwal_semgang',

        // ==================== KAPRODI ====================
        'kaprodi_pengajuan_baru',         // 1. pengajuan magang baru
        'kaprodi_surat_balasan',          // 1. mahasiswa upload surat balasan
        'kaprodi_dospem_ditolak_kajur',   // 2. dospem ditolak kajur
        'kaprodi_dospem_upload_sk',       // 2. BAAK upload SK dospem
        'kaprodi_visitasi_ditolak_kajur', // 3. visitasi ditolak kajur
        'kaprodi_visitasi_upload_sk',     // 3. BAUP upload SK visitasi
        'kaprodi_seminar_selesai',        // 4. nilai akhir selesai

        // ==================== KAJUR ====================
        'kajur_dospem_baru',              // 1. pengajuan SK dospem
        'kajur_visitasi_baru',            // 2. pengajuan dosen visitasi
        'kajur_pengajuan_magang',         // 3. pengajuan magang di-acc Kaprodi

// ==================== ADMIN KAJUR ====================
'admin_kajur_pengajuan_magang',       // 1. pengajuan di-acc Kaprodi

// ==================== BAAK ====================
'baak_pengajuan_magang',              // 1. pengajuan di-acc Kajur
        'baak_surat_balasan_verifikasi',      // 1. surat balasan diverifikasi Kaprodi
        'baak_dospem_disetujui_kajur',        // 2. SK dospem di-acc Kajur
        'baak_visitasi_disetujui_kajur',      // 3. visitasi di-acc Kajur

        // ==================== BAUP ====================
        'baup_visitasi_disetujui_kajur',      // 1. visitasi di-acc Kajur
    ];

    // Kalau tipe notifikasinya nggak ada di daftar email, berhenti dan cuma simpen ke DB aja
    if (!in_array($tipe, $emailTypes)) {
        return $notif;
    }

    try {
        $user = User::find($id_user);
        if (!$user) {
            Log::warning('Skip email: user tidak ditemukan', ['id_user' => $id_user]);
            return $notif;
        }

        $email = null;
        $namaPenerima = null;
        $role = $user->role;

        // Cari email penerima berdasarkan perannya (mahasiswa, dosen, atau pegawai)
        if ($role === 'mahasiswa') {
            $mhs = Mahasiswa::where('id_user', $id_user)->first();
            if ($mhs) {
                $email = $mhs->email;
                $namaPenerima = $mhs->nama;
            }
        } elseif ($role === 'dosen') {
            $dosen = Dosen::where('id_user', $id_user)->first();
            if ($dosen) {
                $email = $dosen->email;
                $namaPenerima = $dosen->nama;
            }
        } elseif ($role === 'pegawai') {
            $pegawai = Pegawai::where('id_user', $id_user)->first();
            if ($pegawai) {
                $email = $pegawai->email;
                $namaPenerima = $pegawai->nama;
            }
        }

        // Kalau user nggak punya email, skip kirim email
        if (!$email) {
            Log::warning('Skip email: tidak punya email', [
                'id_user' => $id_user,
                'tipe' => $tipe,
                'role' => $role,
            ]);
            return $notif;
        }

        // Buat link untuk tombol di isi email
        $link = self::buatLink($tipe, $reference_id, $role);

        Log::info('Mengirim email notifikasi', [
            'tipe' => $tipe,
            'email' => $email,
            'judul' => $judul,
            'role' => $role,
        ]);

        // Proses kirim emailnya
        Mail::to($email)->send(new NotifikasiEmail(
            $judul,
            $pesan,
            $tipe,
            $namaPenerima,
            $link
        ));

        Log::info('Email notifikasi BERHASIL terkirim', [
            'tipe' => $tipe,
            'email' => $email,
        ]);

    } catch (\Exception $e) {
        Log::error('Gagal kirim email notifikasi: ' . $e->getMessage(), [
            'tipe' => $tipe,
            'id_user' => $id_user,
            'trace' => $e->getTraceAsString(),
        ]);
    }

    return $notif;
}

    /**
     *  BUAT LINK UNTUK EMAIL — sekarang pakai route() + role-aware
     */
    // Fungsi buat ngambil link halaman tujuan biar bisa diklik di email
    private static function buatLink($tipe, $reference_id, $role = null)
    {
        try {
            switch ($tipe) {

                // ==================== MAHASISWA ====================
                case 'approval_kaprodi':
                case 'approval_kajur':
                case 'approval_baak':
                case 'approval_pengajuan':
                case 'ditolak':
                case 'pengajuan_magang':
                    if ($role === 'mahasiswa' && $reference_id)
                        return route('mahasiswa.pengajuan_magang.detail', ['id_pengajuan' => $reference_id]);
                    break;

                case 'surat_permohonan':
                    if ($role === 'mahasiswa' && $reference_id)
                        return route('mahasiswa.pengajuan_magang.surat_permohonan', ['id_pengajuan' => $reference_id]);
                    break;

                case 'verifikasi_balasan':
                case 'surat_balasan':
                    if ($role === 'mahasiswa' && $reference_id)
                        return route('mahasiswa.pengajuan_magang.upload_surat_balasan', ['id' => $reference_id]);
                    break;

                case 'surat_pengantar':
                    if ($role === 'mahasiswa' && $reference_id)
                        return route('mahasiswa.pengajuan_magang.surat_pengantar', ['id_pengajuan' => $reference_id]);
                    break;

                case 'dosen_pembimbing':
                    if ($role === 'mahasiswa')
                        return route('mahasiswa.dosen-pembimbing');
                    break;

                case 'dosen_visitasi':
                    if ($role === 'mahasiswa')
                        return route('mahasiswa.dosen-visitasi');
                    break;

                case 'jadwal_semgang':
                case 'jadwal_seminar':
                    if ($role === 'mahasiswa')
                        return route('mahasiswa.pelaksanaan-seminar.index');
                    break;

                case 'logbook':
                    if ($role === 'mahasiswa')
                        return route('mahasiswa.logbook.index');
                    break;

                case 'logbook_minggu':
                    if ($role === 'mahasiswa' && $reference_id)
                        return route('mahasiswa.logbook.minggu', $reference_id);
                    break;

                // ==================== KAPRODI ====================
                case 'kaprodi_pengajuan_baru':
                    if ($role === 'dosen' && $reference_id)
                        return route('kaprodi.pengajuan_magang.detail', ['id_pengajuan' => $reference_id]);
                    break;

                case 'kaprodi_surat_permohonan':
                    if ($role === 'dosen' && $reference_id)
                        return route('kaprodi.pengajuan_magang.surat_permohonan', ['id_pengajuan' => $reference_id]);
                    break;

                case 'kaprodi_surat_balasan':
                    if ($role === 'dosen' && $reference_id)
                        return route('kaprodi.pengajuan_magang.verifikasi_surat_balasan', ['id' => $reference_id]);
                    break;

                case 'kaprodi_surat_pengantar':
                    if ($role === 'dosen' && $reference_id)
                        return route('kaprodi.pengajuan_magang.surat_pengantar', ['id_pengajuan' => $reference_id]);
                    break;

                case 'kaprodi_dospem_disetujui_kajur':
                case 'kaprodi_dospem_ditolak_kajur':
                case 'kaprodi_dospem_upload_sk':
                    if ($role === 'dosen')
                        return route('kaprodi.dosen-pembimbing.index');
                    break;

                case 'kaprodi_visitasi_disetujui_kajur':
                case 'kaprodi_visitasi_ditolak_kajur':
                case 'kaprodi_visitasi_upload_sk':
                    if ($role === 'dosen')
                        return route('kaprodi.pembagian-dosen-visitasi.index');
                    break;

                case 'kaprodi_seminar_selesai':
                    if ($role === 'dosen' && $reference_id)
                        return route('kaprodi.jadwal-semgang.detail-nilai', ['id' => $reference_id]);
                    break;

                case 'kaprodi_token_digunakan':
                    if ($role === 'dosen')
                        return route('kaprodi.token-perusahaan.index');
                    break;

                // ==================== KAJUR ====================
                case 'kajur_pengajuan_magang':
                    if ($role === 'dosen' && $reference_id)
                        return route('kajur.pengajuan_magang.detail', ['id_pengajuan' => $reference_id]);
                    break;

                case 'kajur_dospem_baru':
                    if ($role === 'dosen')
                        return route('kajur.dosen-pembimbing.index');
                    break;

                case 'kajur_visitasi_baru':
                    if ($role === 'dosen')
                        return route('kajur.pembagian-dosen-visitasi.index');
                    break;

                // ==================== BAAK ====================
                case 'baak_pengajuan_magang':
                    if ($role === 'pegawai' && $reference_id)
                        return route('baak.pengajuan_magang.detail', ['id_pengajuan' => $reference_id]);
                    if ($role === 'pegawai')
                        return route('baak.pengajuan_magang.index');
                    break;

                case 'baak_surat_balasan_verifikasi':
                    if ($role === 'pegawai' && $reference_id)
                        return route('baak.pengajuan_magang.detail', ['id_pengajuan' => $reference_id]);
                    if ($role === 'pegawai')
                        return route('baak.pengajuan_magang.index');
                    break;

                case 'baak_dospem_disetujui_kajur':
                    if ($role === 'pegawai')
                        return route('baak.dosen-pembimbing.index');
                    break;

                case 'baak_visitasi_upload_sk':
                    if ($role === 'pegawai')
                        return route('baak.dosen-visitasi.index');
                    break;
                    case 'baak_visitasi_disetujui_kajur':
    if ($role === 'pegawai')
        return route('baak.dosen-visitasi.index');
    break;

                // ==================== BAUP ====================
                case 'baup_visitasi_disetujui_kajur':
                    if ($role === 'pegawai')
                        return route('baup.dosen-visitasi.index');
                    break;

            }
        } catch (\Exception $e) {
            Log::warning('buatLink route() gagal, fallback ke manual', [
                'tipe' => $tipe,
                'role' => $role,
                'error' => $e->getMessage(),
            ]);
        }

        // Fallback: ke halaman notifikasi
        return config('app.url') . '/notifikasi';
    }

    /**
     * Helper: Kirim notifikasi ke semua mahasiswa dalam satu pengajuan
     */
    public static function kirimKeMahasiswaPengajuan($pengajuan, $judul, $pesan, $tipe)
    {
        $idPengajuan = $pengajuan->id_pengajuan;

        if (!$pengajuan->relationLoaded('mahasiswa')) {
            $pengajuan->load('mahasiswa.mahasiswa');
        }

        // Looping tiap mahasiswa di pengajuan itu dan kirim notifnya
        foreach ($pengajuan->mahasiswa as $pm) {
            if ($pm->mahasiswa && $pm->mahasiswa->id_user) {
                self::kirim(
                    $pm->mahasiswa->id_user,
                    $judul,
                    $pesan,
                    $tipe,
                    $idPengajuan
                );
            }
        }
    }

    // Helper buat kirim notif ke Kaprodi berdasarkan id prodi
    public static function kirimKeKaprodiProdi($id_prodi, $judul, $pesan, $tipe, $reference_id = null)
    {
        $jabatanKaprodi = Jabatan::where('nama_jabatan', 'LIKE', '%ketua program studi%')->first();

        if (!$jabatanKaprodi) return;

        $kaprodiList = Dosen::whereHas('jabatanTendik', function ($q) use ($jabatanKaprodi) {
                $q->where('id_jabatan', $jabatanKaprodi->id_jabatan);
            })
            ->where('id_prodi', $id_prodi)
            ->get();

        foreach ($kaprodiList as $kaprodi) {
            if ($kaprodi->id_user) {
                self::kirim($kaprodi->id_user, $judul, $pesan, $tipe, $reference_id);
            }
        }
    }

    // Helper buat kirim notif ke seluruh pegawai BAAK
    public static function kirimKeBaak($judul, $pesan, $tipe, $reference_id = null)
    {
        $jabatanBaak = Jabatan::where('kode_jabatan', 'BAAK')->first();

        if (!$jabatanBaak) {
            Log::warning('kirimKeBaak: Jabatan BAAK tidak ditemukan!');
            return;
        }

        $pegawaiBaak = Pegawai::whereHas('jabatanTendik', function ($q) use ($jabatanBaak) {
            $q->where('id_jabatan', $jabatanBaak->id_jabatan);
        })->get();

        foreach ($pegawaiBaak as $pegawai) {
            if ($pegawai->id_user) {
                self::kirim($pegawai->id_user, $judul, $pesan, $tipe, $reference_id);
            }
        }
    }

    // Helper buat kirim notif ke seluruh pegawai BAUP
    public static function kirimKeBaup($judul, $pesan, $tipe, $reference_id = null)
    {
        $jabatanBaup = Jabatan::where('kode_jabatan', 'BAUP')->first();

        if (!$jabatanBaup) {
            Log::warning('kirimKeBaup: Jabatan BAUP tidak ditemukan!');
            return;
        }

        $pegawaiBaup = Pegawai::whereHas('jabatanTendik', function ($q) use ($jabatanBaup) {
            $q->where('id_jabatan', $jabatanBaup->id_jabatan);
        })->get();

        foreach ($pegawaiBaup as $pegawai) {
            if ($pegawai->id_user) {
                self::kirim($pegawai->id_user, $judul, $pesan, $tipe, $reference_id);
            }
        }
    }
    
    // Helper buat kirim notif ke Admin Kajur
      public static function kirimKeAdminKajur($judul, $pesan, $tipe, $reference_id = null)
    {
        $jabatanAdminKajur = Jabatan::where('kode_jabatan', 'Admin_Kajur')->first();

        if (!$jabatanAdminKajur) {
            Log::warning('kirimKeAdminKajur: Jabatan Admin_Kajur tidak ditemukan!');
            return;
        }

        $pegawaiList = Pegawai::whereHas('jabatanTendik', function ($q) use ($jabatanAdminKajur) {
            $q->where('id_jabatan', $jabatanAdminKajur->id_jabatan);
        })->get();

        foreach ($pegawaiList as $pegawai) {
            if ($pegawai->id_user) {
                self::kirim($pegawai->id_user, $judul, $pesan, $tipe, $reference_id);
            }
        }
    }
    
    // Helper buat kirim notif ke Ketua Jurusan (Kajur) yang ngampusin prodi terkait
    public static function kirimKeKajurJurusan($id_prodi, $judul, $pesan, $tipe, $reference_id = null)
{
    $prodi = \App\Models\Prodi::find($id_prodi);
    if (!$prodi || !$prodi->id_jurusan) return;

    $jabatanKajur = Jabatan::where('nama_jabatan', 'LIKE', '%ketua jurusan%')->first();
    if (!$jabatanKajur) return;

    $kajurList = Dosen::whereHas('jabatanTendik', function ($q) use ($jabatanKajur) {
            $q->where('id_jabatan', $jabatanKajur->id_jabatan);
        })
        ->whereHas('prodi', function ($q) use ($prodi) {
            $q->where('id_jurusan', $prodi->id_jurusan);
        })
        ->get();

    foreach ($kajurList as $kajur) {
        if ($kajur->id_user) {
            self::kirim($kajur->id_user, $judul, $pesan, $tipe, $reference_id);
        }
    }
}
}