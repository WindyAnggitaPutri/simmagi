<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

/**
 * Controller ini ngurus sistem notifikasi real-time
 * 
 * Notifikasi muncul di:
 * 1. Dropdown navbar (icon lonceng) -> max 5 terbaru, hanya yang belum dibaca
 * 2. Halaman lengkap notifikasi -> semua yang belum dibaca, bisa search & paginate
 * 3. Badge angka di icon lonceng -> jumlah notifikasi yang belum dibaca
 * 
 * Setiap notifikasi punya "tipe" yang menentukan:
 * - Ke mana user diarahkan saat notifikasi diklik
 * - Icon apa yang tampil
 * 
 * Tipe notifikasi yang ada:
 * - Mahasiswa: logbook_disetujui, logbook_ditolak, logbook_minggu, approval_*, surat_*, verifikasi_*, jadwal_semgang
 * - Kaprodi: kaprodi_pengajuan_baru, kaprodi_surat_*, kaprodi_dospem_*, kaprodi_visitasi_*, kaprodi_seminar_*, kaprodi_token_*
 * - Kajur: kajur_pengajuan_magang, kajur_dospem_baru, kajur_visitasi_baru
 * - Admin Kajur: admin_kajur_pengajuan_magang, admin_kajur_surat_*
 * - BAAK: baak_pengajuan_magang, baak_surat_*, baak_dospem_*, baak_visitasi_*
 * - BAUP: baup_visitasi_*
 */
class NotifikasiController extends Controller
{
    /**
     * Halaman lengkap notifikasi
     * 
     * Hanya menampilkan notifikasi yang BELUM dibaca (read = 0)
     * Bisa di-search dan dipaginate
     */
    public function index()
    {
        $idUser = auth()->user()->id_user;

        $search  = request()->input('search');
        $perPage = in_array(request('per_page'), [5, 10, 15, 25]) ? request('per_page') : 5;

        // Hanya tampil yang belum dibaca
        $notifikasis = Notifikasi::where('id_user', $idUser)
            ->where('read', 0)
            ->when($search, function ($q) use ($search) {
                // Search di judul atau pesan notifikasi
                $q->where('judul', 'like', '%' . $search . '%')
                  ->orWhere('pesan', 'like', '%' . $search . '%');
            })
            ->orderBy('created_at', 'desc') // Terbaru di atas
            ->paginate($perPage)
            ->withQueryString();

        return view('pages.notifikasi', compact('notifikasis', 'search', 'perPage'));
    }

    /**
     * AJAX: Ambil notifikasi untuk dropdown di navbar
     * 
     * Dipanggil lewat JavaScript (biasanya polling setiap beberapa detik)
     * Mengembalikan max 5 notifikasi terbaru yang belum dibaca
     * Format: JSON
     */
    public function getNotifications()
    {
        $idUser = auth()->user()->id_user;

        // Hanya yang belum dibaca, max 5, terbaru di atas
        $notifs = Notifikasi::where('id_user', $idUser)
            ->where('read', 0)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($n) {
                return [
                    'id_notifikasi' => $n->id_notifikasi,
                    'judul'         => $n->judul,
                    'pesan'         => \Illuminate\Support\Str::limit($n->pesan, 80), // Potong pesan max 80 karakter
                    'tipe'          => $n->tipe,
                    'icon'          => $n->icon,
                    'read'          => $n->read,
                    'waktu'         => $n->waktuLalu, // Properti accessor: "5 menit yang lalu", dll
                    'link'          => $n->link,
                ];
            });

        return response()->json($notifs);
    }

    /**
     * AJAX: Hitung jumlah notifikasi yang belum dibaca
     * 
     * Dipakai untuk badge angka di icon lonceng navbar
     * Dipanggil lewat JavaScript, mengembalikan angka saja
     */
    public function getUnreadCount()
    {
        $idUser = auth()->user()->id_user;
        $count = Notifikasi::where('id_user', $idUser)->where('read', 0)->count();
        return response()->json(['count' => $count]);
    }

    /**
     * Proses membaca notifikasi: tandai sebagai dibaca LALU arahkan ke halaman terkait
     * 
     * Ini yang dipanggil saat user KLIK notifikasi (di dropdown atau halaman lengkap)
     * Alurnya:
     * 1. Tandai notifikasi sebagai dibaca (read = 1)
     * 2. Berdasarkan tipe notifikasi, tentukan ke mana user diarahkan
     * 3. Redirect ke halaman yang sesuai
     * 
     * reference_id dipakai sebagai parameter di route (misalnya ID pengajuan, ID minggu, dll)
     */
    public function baca($id_notifikasi)
    {
        // Ambil notifikasi, pastikan ini milik user yang login
        $notif = Notifikasi::where('id_notifikasi', $id_notifikasi)
            ->where('id_user', auth()->user()->id_user)
            ->firstOrFail();

        // Tandai sebagai dibaca
        $notif->update(['read' => 1]);

        $refId = $notif->reference_id;

        // ==================== ROUTING BERDASARKAN TIPE ====================
        // Setiap tipe notifikasi diarahkan ke halaman yang berbeda
        switch ($notif->tipe) {

            // --- Notifikasi Logbook (untuk Mahasiswa) ---
            case 'logbook_disetujui':
            case 'logbook_ditolak':
                // Arahkan ke halaman minggu logbook yang bersangkutan
                if ($refId) {
                    return redirect()->route('mahasiswa.logbook.minggu', $refId);
                }
                return redirect()->route('mahasiswa.logbook.index');

            case 'logbook_minggu':
                // Sama seperti di atas, tapi khusus approve mingguan
                if ($refId) {
                    return redirect()->route('mahasiswa.logbook.minggu', $refId);
                }
                return redirect()->route('mahasiswa.logbook.index');

            // --- Notifikasi Dosen Pembimbing & Visitasi (untuk Mahasiswa) ---
            case 'dosen_pembimbing':
                return redirect()->route('mahasiswa.dosen-pembimbing');

            case 'dosen_visitasi':
                return redirect()->route('mahasiswa.dosen-visitasi');

            // --- Notifikasi Pengajuan Magang (untuk Mahasiswa) ---
            case 'approval_kaprodi':
            case 'approval_kajur':
            case 'approval_baak':
            case 'approval_pengajuan':
            case 'ditolak':
            case 'pengajuan_magang':
                // Arahkan ke detail pengajuan magang
                if (!$refId) return redirect()->route('notifikasi.index');
                return redirect()->route('mahasiswa.pengajuan_magang.detail', ['id_pengajuan' => $refId]);

            case 'surat_permohonan':
                // Arahkan ke halaman surat permohonan
                if (!$refId) return redirect()->route('notifikasi.index');
                return redirect()->route('mahasiswa.pengajuan_magang.surat_permohonan', ['id_pengajuan' => $refId]);

            case 'verifikasi_balasan':
            case 'surat_balasan':
                // Arahkan ke halaman upload surat balasan
                if (!$refId) return redirect()->route('notifikasi.index');
                return redirect()->route('mahasiswa.pengajuan_magang.upload_surat_balasan', ['id' => $refId]);

            case 'surat_pengantar':
                // Arahkan ke halaman surat pengantar
                if (!$refId) return redirect()->route('notifikasi.index');
                return redirect()->route('mahasiswa.pengajuan_magang.surat_pengantar', ['id_pengajuan' => $refId]);

            // --- Notifikasi untuk Kaprodi ---
            case 'kaprodi_pengajuan_baru':
                // Ada pengajuan baru yang perlu dilihat Kaprodi
                if ($refId) {
                    return redirect()->route('kaprodi.pengajuan_magang.detail', ['id_pengajuan' => $refId]);
                }
                return redirect()->route('kaprodi.pengajuan_magang.index');

            case 'kaprodi_surat_permohonan':
                if ($refId) {
                    return redirect()->route('kaprodi.pengajuan_magang.surat_permohonan', ['id_pengajuan' => $refId]);
                }
                return redirect()->route('kaprodi.pengajuan_magang.index');

            case 'kaprodi_surat_balasan':
                if ($refId) {
                    return redirect()->route('kaprodi.pengajuan_magang.verifikasi_surat_balasan', ['id' => $refId]);
                }
                return redirect()->route('kaprodi.pengajuan_magang.index');

            case 'kaprodi_surat_pengantar':
                if ($refId) {
                    return redirect()->route('kaprodi.pengajuan_magang.surat_pengantar', ['id_pengajuan' => $refId]);
                }
                return redirect()->route('kaprodi.pengajuan_magang.index');

            // Notifikasi terkait dosen pembimbing (disetujui/ditolak/upload SK oleh Kajur)
            case 'kaprodi_dospem_disetujui_kajur':
            case 'kaprodi_dospem_ditolak_kajur':
            case 'kaprodi_dospem_upload_sk':
                return redirect()->route('kaprodi.dosen-pembimbing.index');

            // Notifikasi terkait visitasi (disetujui/ditolak/upload SK oleh Kajur)
            case 'kaprodi_visitasi_disetujui_kajur':
            case 'kaprodi_visitasi_ditolak_kajur':
            case 'kaprodi_visitasi_upload_sk':
                return redirect()->route('kaprodi.pembagian-dosen-visitasi.index');

            // Notifikasi ketika seminar selesai dan sudah ada nilai
            case 'kaprodi_seminar_selesai':
                if ($refId) {
                    return redirect()->route('kaprodi.jadwal-semgang.detail-nilai', ['id' => $refId]);
                }
                return redirect()->route('kaprodi.jadwal-semgang.index');

            // Notifikasi ketika token perusahaan digunakan (angket/penilaian diisi)
            case 'kaprodi_token_digunakan':
                return redirect()->route('kaprodi.token-perusahaan.index');

            // --- Notifikasi untuk Kajur ---
            case 'kajur_pengajuan_magang':
                if ($refId) {
                    return redirect()->route('kajur.pengajuan_magang.detail', ['id_pengajuan' => $refId]);
                }
                return redirect()->route('kajur.pengajuan_magang.index');

            case 'kajur_dospem_baru':
                return redirect()->route('kajur.dosen-pembimbing.index');

            case 'kajur_visitasi_baru':
                return redirect()->route('kajur.pembagian-dosen-visitasi.index');

            // --- Notifikasi untuk Admin Kajur ---
            case 'admin_kajur_pengajuan_magang':
                if ($refId) {
                    return redirect()->route('admin_kajur.pengajuan_magang.detail', ['id_pengajuan' => $refId]);
                }
                return redirect()->route('admin_kajur.pengajuan_magang.index');

            case 'admin_kajur_surat_permohonan':
                if ($refId) {
                    return redirect()->route('admin_kajur.pengajuan_magang.surat_permohonan', ['id_pengajuan' => $refId]);
                }
                return redirect()->route('admin_kajur.pengajuan_magang.index');

            case 'admin_kajur_surat_balasan':
                if ($refId) {
                    return redirect()->route('admin_kajur.lihat_surat_balasan', ['id' => $refId]);
                }
                return redirect()->route('admin_kajur.pengajuan_magang.index');

            case 'admin_kajur_surat_pengantar':
                if ($refId) {
                    return redirect()->route('admin_kajur.pengajuan_magang.surat_pengantar', ['id_pengajuan' => $refId]);
                }
                return redirect()->route('admin_kajur.pengajuan_magang.index');

            // --- Notifikasi untuk BAAK ---
            case 'baak_pengajuan_magang':
                if ($refId) {
                    return redirect()->route('baak.pengajuan_magang.detail', ['id_pengajuan' => $refId]);
                }
                return redirect()->route('baak.pengajuan_magang.index');

            case 'baak_surat_balasan_verifikasi':
                if ($refId) {
                    return redirect()->route('baak.pengajuan_magang.detail', ['id_pengajuan' => $refId]);
                }
                return redirect()->route('baak.pengajuan_magang.index');

            case 'baak_dospem_disetujui_kajur':
                return redirect()->route('baak.dosen-pembimbing.index');

            case 'baak_visitasi_upload_sk':
                return redirect()->route('baak.dosen-visitasi.index');

            // --- Notifikasi untuk BAUP ---
            case 'baup_visitasi_disetujui_kajur':
                return redirect()->route('baup.dosen-visitasi.index');

            // --- Notifikasi Jadwal Seminar (untuk Mahasiswa) ---
            case 'jadwal_semgang':
                if ($refId) {
                    return redirect()->route('mahasiswa.pelaksanaan-seminar.detail', $refId);
                }
                return redirect()->route('mahasiswa.pelaksanaan-seminar.index');

            case 'baak_visitasi_disetujui_kajur':
                return redirect()->route('baak.dosen-visitasi.index');

            // --- Default: kalau tipe-nya gak dikenali ---
            default:
                // Coba pakai link dari kolom 'link' kalau ada
                if ($notif->link && $notif->link !== '#') {
                    return redirect($notif->link);
                }
                // Kalau gak ada, arahkan ke halaman notifikasi
                return redirect()->route('notifikasi.index');
        }
    }

    /**
     * AJAX: Tandai SATU notifikasi sebagai dibaca
     * 
     * Dipanggil lewat JavaScript, biasanya saat user klik icon "sudah dibaca"
     * Mengembalikan JSON success
     */
    public function markAsRead($id_notifikasi)
    {
        $notif = Notifikasi::where('id_notifikasi', $id_notifikasi)
            ->where('id_user', auth()->user()->id_user)
            ->firstOrFail();

        $notif->update(['read' => 1]);

        return response()->json(['success' => true]);
    }

    /**
     * AJAX: Tandai SEMUA notifikasi sebagai dibaca sekaligus
     * 
     * Dipanggil saat user klik tombol "Tandai semua sudah dibaca"
     * Hanya mengubah yang belum dibaca (read = 0)
     */
    public function markAllAsRead()
    {
        Notifikasi::where('id_user', auth()->user()->id_user)
            ->where('read', 0)
            ->update(['read' => 1]);

        return response()->json(['success' => true]);
    }
}