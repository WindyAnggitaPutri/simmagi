<?php

// Daftar controller yang dipanggil di file route ini
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RekapDataMagangController;
use App\Http\Controllers\NilaiAkhirController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\KaprodiReferensiPerusahaanController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\JabatanTendikController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\PengajuanMagangController;
use App\Http\Controllers\DosenPembimbingController;
use App\Http\Controllers\BerkasMagangController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\JadwalSemgangController;
use App\Http\Controllers\BerandaPengunjungController;
use App\Http\Controllers\KaprodiTokenController;
use App\Http\Controllers\PerusahaanFormController;
use App\Http\Controllers\DosenVisitasiController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\BackupController;

// ==========================================
// 1. ROUTE PUBLIK (Bisa diakses tanpa login)
// ==========================================

// halaman utama (landing page)
Route::get('/', [BerandaPengunjungController::class, 'index'])->name('beranda_pengunjung');
Route::get('/jadwal-seminar-magang', [BerandaPengunjungController::class, 'jadwalSeminar'])->name('pengunjung.jadwal-seminar');
Route::get('/referensi-perusahaan', [BerandaPengunjungController::class, 'referensiPerusahaan'])->name('pengunjung.referensi-perusahaan');
Route::get('/referensi-perusahaan/{id_perusahaan}', [BerandaPengunjungController::class, 'detailPerusahaan'])->name('pengunjung.detail-perusahaan');

// halaman login & logout
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.post');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

// Lupa Password (Public)
// GANTI INI:
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('forgot.password');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('forgot.password.send');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('forgot.password.form');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('forgot.password.reset');

// MENJADI:
Route::get('/forgot-password', [ResetPasswordController::class, 'showForgotForm'])->name('forgot.password');
Route::post('/forgot-password', [ResetPasswordController::class, 'sendResetLink'])->name('forgot.password.send');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('forgot.password.form');
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])->name('forgot.password.reset');

// Halaman form penilaian buat pihak perusahaan, dikasih link langsung pakai token
Route::get('/perusahaan/form/{token}', [PerusahaanFormController::class, 'showForm'])->name('perusahaan.form.show');
Route::post('/perusahaan/form/{token}', [PerusahaanFormController::class, 'submitForm'])->name('perusahaan.form.submit');
Route::get('/perusahaan/form-berhasil', [PerusahaanFormController::class, 'berhasil'])->name('perusahaan.form.berhasil');
Route::get('token-perusahaan/{id_token}/cetak-dokumen', [KaprodiTokenController::class, 'cetakDokumen'])->name('kaprodi.token-perusahaan.cetak-dokumen');

// Halaman buat ngeliat berkas magang berdasarkan jenisnya
Route::get('/berkas/{jenis}', [BerkasMagangController::class, 'showByJenis'])->name('berkas.jenis');



// ==========================================
// 2. ROUTE AUTH ONLY (Khusus Ganti Password & Admin)
// ==========================================
// Route ini cuma bisa diakses kalau sudah login
Route::middleware('auth')->group(function () {
    
    // Route buat ganti password wajib (biar gak loop redirect terus)
    Route::get('/ganti-password', [PasswordController::class, 'edit'])->name('password.change.form');
    Route::post('/ganti-password', [PasswordController::class, 'update'])->name('password.change.update');

    // DASHBOARD ADMIN (Admin ga dipaksa ganti password)
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');

    // CRUD Mahasiswa (Buat Admin)
    Route::get('/admin/mahasiswa', [MahasiswaController::class, 'index'])->name('admin.mahasiswa.index');
    Route::get('/admin/mahasiswa/create', [MahasiswaController::class, 'create'])->name('admin.mahasiswa.create');
    Route::post('/admin/mahasiswa', [MahasiswaController::class, 'store'])->name('admin.mahasiswa.store');
    Route::get('/admin/mahasiswa/{id_mahasiswa}/edit', [MahasiswaController::class, 'edit'])->name('admin.mahasiswa.edit');
    Route::put('/admin/mahasiswa/{id_mahasiswa}', [MahasiswaController::class, 'update'])->name('admin.mahasiswa.update');
    Route::delete('/admin/mahasiswa/{id_mahasiswa}', [MahasiswaController::class, 'destroy'])->name('admin.mahasiswa.destroy');
    // Fitur import excel dan download template buat mahasiswa
Route::get('/admin/mahasiswa/import', [MahasiswaController::class, 'showImportForm'])->name('admin.mahasiswa.import.form');
Route::post('/admin/mahasiswa/import', [MahasiswaController::class, 'importExcel'])->name('admin.mahasiswa.import');
Route::get('/admin/mahasiswa/template', [MahasiswaController::class, 'downloadTemplate'])->name('admin.mahasiswa.template');

  // CRUD Jurusan (Buat Admin)
Route::get('/admin/jurusan', [JurusanController::class, 'index'])->name('admin.jurusan.index');
Route::get('/admin/jurusan/create', [JurusanController::class, 'create'])->name('admin.jurusan.create');
Route::post('/admin/jurusan', [JurusanController::class, 'store'])->name('admin.jurusan.store');
Route::get('/admin/jurusan/{id_jurusan}/edit', [JurusanController::class, 'edit'])->name('admin.jurusan.edit');
Route::put('/admin/jurusan/{id_jurusan}', [JurusanController::class, 'update'])->name('admin.jurusan.update');
Route::delete('/admin/jurusan/{id_jurusan}', [JurusanController::class, 'destroy'])->name('admin.jurusan.destroy');
Route::get('/admin/jurusan/import', [JurusanController::class, 'showImportForm'])->name('admin.jurusan.import.form');
Route::post('/admin/jurusan/import', [JurusanController::class, 'importExcel'])->name('admin.jurusan.import');
Route::get('/admin/jurusan/template', [JurusanController::class, 'downloadTemplate'])->name('admin.jurusan.template');
    // CRUD Prodi (Buat Admin)
    Route::get('/admin/prodi', [ProdiController::class, 'index'])->name('admin.prodi.index');
    Route::get('/admin/prodi/create', [ProdiController::class, 'create'])->name('admin.prodi.create');
    Route::post('/admin/prodi', [ProdiController::class, 'store'])->name('admin.prodi.store');
    Route::get('/admin/prodi/{id_prodi}/edit', [ProdiController::class, 'edit'])->name('admin.prodi.edit');
    Route::put('/admin/prodi/{id_prodi}', [ProdiController::class, 'update'])->name('admin.prodi.update');
    Route::delete('/admin/prodi/{id_prodi}', [ProdiController::class, 'destroy'])->name('admin.prodi.destroy');
        Route::get('/admin/prodi/import', [ProdiController::class, 'showImportForm'])->name('admin.prodi.import.form');
    Route::post('/admin/prodi/import', [ProdiController::class, 'importExcel'])->name('admin.prodi.import');
    Route::get('/admin/prodi/template', [ProdiController::class, 'downloadTemplate'])->name('admin.prodi.template');
    
    // CRUD Dosen (Buat Admin)
    Route::get('/admin/dosen', [DosenController::class, 'index'])->name('admin.dosen.index');
    Route::get('/admin/dosen/create', [DosenController::class, 'create'])->name('admin.dosen.create');
    Route::post('/admin/dosen', [DosenController::class, 'store'])->name('admin.dosen.store');
    Route::get('/admin/dosen/{id_dosen}/edit', [DosenController::class, 'edit'])->name('admin.dosen.edit');
    Route::put('/admin/dosen/{id_dosen}', [DosenController::class, 'update'])->name('admin.dosen.update');
    Route::delete('/admin/dosen/{id_dosen}', [DosenController::class, 'destroy'])->name('admin.dosen.destroy');
        // Fitur import excel dan download template buat dosen
    Route::get('/admin/dosen/import', [DosenController::class, 'showImportForm'])->name('admin.dosen.import.form');
    Route::post('/admin/dosen/import', [DosenController::class, 'importExcel'])->name('admin.dosen.import');
    Route::get('/admin/dosen/template', [DosenController::class, 'downloadTemplate'])->name('admin.dosen.template');

    // CRUD Pegawai (Buat Admin)
    Route::get('/admin/pegawai', [PegawaiController::class, 'index'])->name('admin.pegawai.index');
    Route::get('/admin/pegawai/create', [PegawaiController::class, 'create'])->name('admin.pegawai.create');
    Route::post('/admin/pegawai', [PegawaiController::class, 'store'])->name('admin.pegawai.store');
    Route::get('/admin/pegawai/{id_pegawai}/edit', [PegawaiController::class, 'edit'])->name('admin.pegawai.edit');
    Route::put('/admin/pegawai/{id_pegawai}', [PegawaiController::class, 'update'])->name('admin.pegawai.update');
    Route::delete('/admin/pegawai/{id_pegawai}', [PegawaiController::class, 'destroy'])->name('admin.pegawai.destroy');
        Route::get('/admin/pegawai/import', [PegawaiController::class, 'showImportForm'])->name('admin.pegawai.import.form');
    Route::post('/admin/pegawai/import', [PegawaiController::class, 'importExcel'])->name('admin.pegawai.import');
    Route::get('/admin/pegawai/template', [PegawaiController::class, 'downloadTemplate'])->name('admin.pegawai.template');

    // CRUD Jabatan (Buat Admin)
    Route::get('/admin/jabatan', [JabatanController::class, 'index'])->name('admin.jabatan.index');
    Route::get('/admin/jabatan/create', [JabatanController::class, 'create'])->name('admin.jabatan.create');
    Route::post('/admin/jabatan', [JabatanController::class, 'store'])->name('admin.jabatan.store');
    Route::get('/admin/jabatan/{id_jabatan}/edit', [JabatanController::class, 'edit'])->name('admin.jabatan.edit');
    Route::put('/admin/jabatan/{id_jabatan}', [JabatanController::class, 'update'])->name('admin.jabatan.update');
    Route::delete('/admin/jabatan/{id_jabatan}', [JabatanController::class, 'destroy'])->name('admin.jabatan.destroy');

    // CRUD Jabatan Dosen / Tendik (Buat Admin)
    Route::get('/admin/jabatan-dosen', [JabatanTendikController::class, 'index'])->name('admin.jabatan-dosen.index');
    Route::get('/admin/jabatan-dosen/create', [JabatanTendikController::class, 'create'])->name('admin.jabatan-dosen.create');
    Route::post('/admin/jabatan-dosen', [JabatanTendikController::class, 'store'])->name('admin.jabatan-dosen.store');
    Route::get('/admin/jabatan-dosen/{id_jabatan_tendik}/edit', [JabatanTendikController::class, 'edit'])->name('admin.jabatan-dosen.edit');
    Route::put('/admin/jabatan-dosen/{id_jabatan_tendik}', [JabatanTendikController::class, 'update'])->name('admin.jabatan-dosen.update');
    Route::delete('/admin/jabatan-dosen/{id_jabatan_tendik}', [JabatanTendikController::class, 'destroy'])->name('admin.jabatan-dosen.destroy');

    // CRUD Perusahaan (Buat Admin)
    Route::get('/admin/perusahaan', [PerusahaanController::class, 'index'])->name('admin.perusahaan.index');
    Route::get('/admin/perusahaan/create', [PerusahaanController::class, 'create'])->name('admin.perusahaan.create');
    Route::post('/admin/perusahaan', [PerusahaanController::class, 'store'])->name('admin.perusahaan.store');
    Route::get('/admin/perusahaan/{id_perusahaan}/edit', [PerusahaanController::class, 'edit'])->name('admin.perusahaan.edit');
    Route::put('/admin/perusahaan/{id_perusahaan}', [PerusahaanController::class, 'update'])->name('admin.perusahaan.update');
    Route::delete('/admin/perusahaan/{id_perusahaan}', [PerusahaanController::class, 'destroy'])->name('admin.perusahaan.destroy');

     // Dosen Pembimbing - Admin (Cuma bisa lihat)
    Route::get('/admin/dosen-pembimbing', [DosenPembimbingController::class, 'indexAdmin'])->name('admin.dosen-pembimbing.index');

    // ADMIN PENGAJUAN MAGANG & MONITORING (Buat Admin)
    Route::get('/admin/pengajuan-magang', [PengajuanMagangController::class, 'indexAdmin'])->name('admin.pengajuan-magang.index');
    Route::get('/admin/pengajuan-magang/{id_pengajuan}', [PengajuanMagangController::class, 'detailAdmin'])->name('admin.pengajuan-magang.detail');
    Route::get('/admin/monitoring-penempatan', [PengajuanMagangController::class, 'monitoringPenempatanAdmin'])->name('admin.monitoring-penempatan');

    // Admin - Berkas Magang (CRUD)
    Route::get('/admin/berkas-magang', [BerkasMagangController::class, 'indexAdmin'])->name('admin.berkas-magang.index');
    Route::get('/admin/berkas-magang/create', [BerkasMagangController::class, 'createAdmin'])->name('admin.berkas-magang.create');
    Route::post('/admin/berkas-magang', [BerkasMagangController::class, 'storeAdmin'])->name('admin.berkas-magang.store');
    Route::get('/admin/berkas-magang/{id_berkas_magang}/edit', [BerkasMagangController::class, 'editAdmin'])->name('admin.berkas-magang.edit');
    Route::put('/admin/berkas-magang/{id_berkas_magang}', [BerkasMagangController::class, 'updateAdmin'])->name('admin.berkas-magang.update');
    Route::delete('/admin/berkas-magang/{id_berkas_magang}', [BerkasMagangController::class, 'destroyAdmin'])->name('admin.berkas-magang.destroy');

    // CRUD User (Buat Admin)
    Route::get('/admin/user', [UserController::class, 'index'])->name('admin.user.index');
    Route::get('/admin/user/create', [UserController::class, 'create'])->name('admin.user.create');
    Route::post('/admin/user', [UserController::class, 'store'])->name('admin.user.store');
    Route::get('/admin/user/{id_user}/edit', [UserController::class, 'edit'])->name('admin.user.edit');
    Route::put('/admin/user/{id_user}', [UserController::class, 'update'])->name('admin.user.update');
    Route::delete('/admin/user/{id_user}', [UserController::class, 'destroy'])->name('admin.user.destroy');

 
        // Dosen Visitasi - Admin (Cuma bisa lihat)
    Route::get('/admin/dosen-visitasi', [DosenVisitasiController::class, 'indexAdmin'])->name('admin.dosen-visitasi.index');
    
    // ADMIN - MONITORING LOGBOOK (Buat Admin)
    Route::get('/admin/logbook', [LogbookController::class, 'indexAdmin'])->name('admin.logbook.index');
    Route::get('/admin/logbook/detail_logbook/{id_dosen_pembimbing}', [LogbookController::class, 'detailAdmin'])->name('admin.logbook.detail_logbook');
    Route::get('/admin/logbook/export', [LogbookController::class, 'exportAdmin'])->name('admin.logbook.export');

    // ADMIN - PERSONAL INTERVIEW MAGANG (Buat Admin)
    Route::get('/admin/personal-interview', [DosenVisitasiController::class, 'indexPersonalInterviewAdmin'])->name('admin.personal-interview.index');
    Route::get('/admin/personal-interview/{id_perusahaan}/detail', [DosenVisitasiController::class, 'detailPersonalInterviewAdmin'])->name('admin.personal-interview.detail');

    // ADMIN - JADWAL SEMINAR MAGANG (Cuma bisa lihat)
    Route::get('/admin/jadwal-seminar', [JadwalSemgangController::class, 'indexAdmin'])->name('admin.jadwal-seminar.index');
    Route::get('/jadwal-seminar/{id}', [JadwalSemgangController::class, 'detailAdmin'])->name('admin.jadwal-seminar.detail');
});


// ==========================================
// 3. ROUTE NON-ADMIN (Wajib Auth & Ganti Password)
// ==========================================
// Route buat user selain admin, wajib login dan udah ganti password
Route::middleware(['auth', 'password.change'])->group(function () {

    // BERANDA (Tampilan awal masing-masing role)
    Route::get('/beranda-mahasiswa', function () {
        return view('pages.mahasiswa.beranda_mahasiswa');
    });
    Route::get('/beranda-dosen', function () {
        return view('pages.dosen.beranda_dosen');
    });
    Route::get('/beranda-pegawai', function () {
        return view('pages.pegawai.beranda_pegawai');
    });
    Route::get('/beranda-kajur', function () {
        return view('pages.kajur.beranda_kajur');
    });
    Route::get('/beranda-kaprodi', function () {
        return view('pages.kaprodi.beranda_kaprodi');
    });
    Route::get('/beranda-baak', function () {
        return view('pages.baak.beranda_baak');
    })->name('pegawai.beranda-baak');
    Route::get('/beranda-baup', function () {
        return view('pages.baup.beranda_baup');
    })->name('pegawai.beranda-baup');
    Route::get('/beranda-dospem', function () {
        return view('pages.dospem.beranda_dospem');
    })->name('beranda-dospem');
    Route::get('/beranda-dosen-visitasi', function () {
        return view('pages.dosen_visitasi.beranda_dosen_visitasi');
    })->name('beranda-dosen-visitasi');

    // mahasiswa (Buat ngisi data awal pas baru login)
    Route::get('/mahasiswa/create', [MahasiswaController::class, 'create']);
    Route::post('/mahasiswa/store', [MahasiswaController::class, 'store']);

    // dosen (Buat ngisi data awal pas baru login)
    Route::get('/dosen/create', [DosenController::class, 'create']);
    Route::post('/dosen/store', [DosenController::class, 'store']);

    // pegawai (Buat ngisi data awal pas baru login)
    Route::get('/pegawai/create', [PegawaiController::class, 'create']);
    Route::post('/pegawai/store', [PegawaiController::class, 'store']);

    // DOSEN PEMBIMBING (Buat nuker peran kalau dosen punya banyak role)
    Route::get('/dosen/pilih-role', [DosenController::class, 'pilihRole'])->name('dosen.pilih-role');
    Route::post('/dosen/set-role', [DosenController::class, 'setRole'])->name('dosen.set-role');

    // PEGAWAI (Buat nuker peran kalau pegawai punya banyak role)
    Route::get('/pegawai/pilih-role', [PegawaiController::class, 'pilihRole'])->name('pegawai.pilih-role');
    Route::post('/pegawai/set-role', [PegawaiController::class, 'setRole'])->name('pegawai.set-role');

    // ==========================================
    // PENGAJUAN MAGANG MAHASISWA
    // (Buat mahasiswa ngajuin magang, liat detail, upload surat, dll)
    // ==========================================
    Route::get('/mahasiswa/pengajuan_magang', [PengajuanMagangController::class, 'index'])->name('mahasiswa.pengajuan_magang.index');
    Route::get('/mahasiswa/pengajuan_magang/create', [PengajuanMagangController::class, 'create'])->name('mahasiswa.pengajuan_magang.create');
    Route::post('/mahasiswa/pengajuan_magang', [PengajuanMagangController::class, 'store'])->name('mahasiswa.pengajuan_magang.store');
    Route::get('/mahasiswa/pengajuan_magang/{id_pengajuan}', [PengajuanMagangController::class, 'show'])->name('mahasiswa.pengajuan_magang.detail');
    Route::get('/mahasiswa/pengajuan-magang/{id_pengajuan}/surat-permohonan', [PengajuanMagangController::class, 'lihatSuratPermohonanMahasiswa'])->name('mahasiswa.pengajuan_magang.surat_permohonan');
    Route::get('/mahasiswa/pengajuan-magang/{id}/upload-surat-balasan', [PengajuanMagangController::class, 'formUploadSuratBalasan'])->name('mahasiswa.pengajuan_magang.upload_surat_balasan');
    Route::post('/mahasiswa/pengajuan-magang/{id}/upload-surat-balasan', [PengajuanMagangController::class, 'uploadSuratBalasan'])->name('mahasiswa.pengajuan_magang.upload_surat_balasan.store');
    Route::get('/mahasiswa/pengajuan-magang/{id_pengajuan}/surat-pengantar', [PengajuanMagangController::class, 'lihatSuratPengantarMahasiswa'])->name('mahasiswa.pengajuan_magang.surat_pengantar');
    Route::get('/mahasiswa/pengajuan-magang/{id_pengajuan}/lihat-surat-pengantar', [PengajuanMagangController::class, 'lihatSuratPengantarMahasiswa'])->name('mahasiswa.lihat_surat_pengantar');
    Route::get('/mahasiswa/pengajuan-magang/{id_pengajuan}/surat-pengantar', [PengajuanMagangController::class, 'lihatSuratPengantarMahasiswa'])->name('mahasiswa.pengajuan_magang.surat_pengantar');
    Route::post('/pengajuan_magang/{id_pengajuan}/batal', [PengajuanMagangController::class, 'batalkanPengajuan'])->name('mahasiswa.pengajuan_magang.batal');

        // ==========================================
    // MAHASISWA - ABSENSI
    // (Mahasiswa melakukan absensi harian saat magang)
    // ==========================================
// YANG BENAR - cukup pakai middleware yang sudah ada
Route::prefix('mahasiswa/absensi')->name('mahasiswa.absensi.')->group(function() {
    Route::get('/', [AbsensiController::class, 'index'])->name('index');
    Route::post('/store', [AbsensiController::class, 'storeFromIndex'])->name('store-index');
    Route::get('/check-service', [AbsensiController::class, 'checkFaceService'])->name('check-service');
     Route::post('/detect', [AbsensiController::class, 'proxyDetect'])->name('detect');
    
});



    // PROFIL (Buat ngubah data diri dan password sendiri)
    Route::get('/profil/edit', [ProfilController::class, 'edit'])->name('profil.edit');
Route::put('/profil/update', [ProfilController::class, 'update'])->name('profil.update');
Route::put('/profil/update-password', [ProfilController::class, 'updatePassword'])->name('profil.updatePassword');

// ==========================================
// PENGAJUAN MAGANG ADMIN KAJUR (PENGGANTI KAJUR APPROVAL)
// (Admin kajur yang nge-approve pengajuan magang mahasiswa)
// ==========================================
Route::get('/admin-kajur/pengajuan-magang', [PengajuanMagangController::class, 'indexAdminKajur'])->name('admin_kajur.pengajuan_magang.index');
Route::get('/admin-kajur/pengajuan-magang/{id_pengajuan}', [PengajuanMagangController::class, 'detailAdminKajur'])->name('admin_kajur.pengajuan_magang.detail');
Route::post('/admin-kajur/pengajuan-magang/{id_pengajuan}/approval', [PengajuanMagangController::class, 'prosesApprovalAdminKajur'])->name('admin_kajur.pengajuan_magang.proses_approval');
Route::get('/admin-kajur/pengajuan-magang/{id_pengajuan}/surat-permohonan', [PengajuanMagangController::class, 'lihatSuratPermohonanAdminKajur'])->name('admin_kajur.pengajuan_magang.surat_permohonan');
Route::get('/admin-kajur/pengajuan-magang/{id}/lihat-surat-balasan', [PengajuanMagangController::class, 'lihatSuratBalasanAdminKajur'])->name('admin_kajur.lihat_surat_balasan');
Route::get('/admin-kajur/pengajuan-magang/{id_pengajuan}/surat-pengantar', [PengajuanMagangController::class, 'lihatSuratPengantarAdminKajur'])->name('admin_kajur.pengajuan_magang.surat_pengantar');

// ==========================================
// PENGAJUAN MAGANG KAJUR (MONITORING ONLY)
// (Kajur cuma bisa ngawasin pengajuan magang aja)
// ==========================================
Route::get('/kajur/pengajuan-magang', [PengajuanMagangController::class, 'indexKajur'])->name('kajur.pengajuan_magang.index');
Route::get('/kajur/pengajuan-magang/{id_pengajuan}', [PengajuanMagangController::class, 'detailKajur'])->name('kajur.pengajuan_magang.detail');
Route::get('/kajur/pengajuan-magang/{id_pengajuan}/surat-permohonan', [PengajuanMagangController::class, 'lihatSuratPermohonanKajur'])->name('kajur.pengajuan_magang.surat_permohonan');
Route::get('/kajur/pengajuan-magang/{id}/lihat-surat-balasan', [PengajuanMagangController::class, 'lihatSuratBalasanKajur'])->name('kajur.lihat_surat_balasan');
Route::get('/kajur/pengajuan-magang/{id_pengajuan}/surat-pengantar', [PengajuanMagangController::class, 'lihatSuratPengantarKajur'])->name('kajur.pengajuan_magang.surat_pengantar');
Route::get('/kajur/pengajuan_magang/{id_pengajuan}/lihat-surat-pengantar', [PengajuanMagangController::class, 'lihatSuratPengantarKajur'])->name('kajur.lihat_surat_pengantar');


// ==========================================
// BAAK - MONITORING DOSEN PEMBIMBING
// (BAAK ngawasin SK dosen pembimbing)
// ==========================================
Route::get('/baak/monitoring-pembimbing', [DosenPembimbingController::class, 'monitoringPembimbingBaak'])->name('baak.monitoring.pembimbing');
Route::get('/baak/monitoring/dosen-pembimbing/{id_dosen_pembimbing}/lihat-sk', [DosenPembimbingController::class, 'lihatSkBaakMonitoring'])->name('baak.monitoring.lihat_sk');

    // ==========================================
    // PENGAJUAN MAGANG KAPRODI
    // (Kaprodi yang nge-approve, bikin surat, sama verifikasi surat balasan)
    // ==========================================
    Route::get('/kaprodi/pengajuan-magang', [PengajuanMagangController::class, 'indexKaprodi'])->name('kaprodi.pengajuan_magang.index');
    Route::get('/kaprodi/pengajuan-magang/{id_pengajuan}', [PengajuanMagangController::class, 'detailKaprodi'])->name('kaprodi.pengajuan_magang.detail');
    Route::post('/kaprodi/pengajuan-magang/{id_pengajuan}/approval', [PengajuanMagangController::class, 'prosesApprovalKaprodi'])->name('kaprodi.pengajuan_magang.proses_approval');
    Route::get('/kaprodi/pengajuan-magang/{id_pengajuan}/surat-permohonan', [PengajuanMagangController::class, 'lihatSuratPermohonanKaprodi'])->name('kaprodi.pengajuan_magang.surat_permohonan');
    Route::post('/kaprodi/pengajuan-magang/proses-surat-balasan', [PengajuanMagangController::class, 'prosesSuratBalasan'])->name('kaprodi.pengajuan_magang.proses_surat_balasan');
    Route::get('/kaprodi/pengajuan-magang/{id}/verifikasi-surat-balasan', [PengajuanMagangController::class, 'verifikasiSuratBalasan'])->name('kaprodi.pengajuan_magang.verifikasi_surat_balasan');
    Route::post('/kaprodi/pengajuan-magang/{id}/verifikasi-surat-balasan', [PengajuanMagangController::class, 'prosesVerifikasiSuratBalasan'])->name('kaprodi.pengajuan_magang.proses_verifikasi_sb');
    Route::get('/kaprodi/pengajuan-magang/{id_pengajuan}/surat-pengantar', [PengajuanMagangController::class, 'lihatSuratPengantarKaprodi'])->name('kaprodi.pengajuan_magang.surat_pengantar');
    Route::get('/kaprodi/pengajuan_magang/{id_pengajuan}/lihat-surat-pengantar', [PengajuanMagangController::class, 'lihatSuratPengantarKaprodi'])->name('kaprodi.lihat_surat_pengantar');
    Route::post('/pengajuan_magang/update-tanggal-prodi', [PengajuanMagangController::class, 'updateTanggalMagangProdi'])->name('kaprodi.pengajuan_magang.update_tanggal_prodi');

// ==========================================
// KAPRODI - REKAP DATA MAGANG
// ==========================================
Route::get('/kaprodi/rekap', [RekapDataMagangController::class, 'index'])->name('kaprodi.rekap.index');
Route::get('/kaprodi/rekap/download', [RekapDataMagangController::class, 'download'])->name('kaprodi.rekap.download');

    // ==========================================
    // PENGAJUAN MAGANG BAAK
    // (BAAK urus upload surat permohonan, pengantar, sama kirim email ke perusahaan)
    // ==========================================
    Route::get('/baak/pengajuan-magang', [PengajuanMagangController::class, 'indexBaak'])->name('baak.pengajuan_magang.index');
    Route::get('/baak/pengajuan-magang/{id_pengajuan}', [PengajuanMagangController::class, 'detailBaak'])->name('baak.pengajuan_magang.detail');
    Route::post('/baak/pengajuan-magang/{id_pengajuan}/approval', [PengajuanMagangController::class, 'prosesApprovalBaak'])->name('baak.pengajuan_magang.proses_approval');
    Route::get('/baak/pengajuan-magang/{id}/upload-surat-permohonan', [PengajuanMagangController::class, 'formUploadSuratPermohonan'])->name('baak.upload_surat_permohonan');
    Route::post('/baak/pengajuan-magang/{id}/upload-surat-permohonan', [PengajuanMagangController::class, 'uploadSuratPermohonan'])->name('baak.upload_surat_permohonan.store');
    Route::get('/baak/pengajuan-magang/{id}/upload-surat-pengantar', [PengajuanMagangController::class, 'formUploadSuratPengantar'])->name('baak.upload_surat_pengantar');
    Route::post('/baak/pengajuan-magang/{id}/upload-surat-pengantar', [PengajuanMagangController::class, 'uploadSuratPengantar'])->name('baak.upload_surat_pengantar.store');
    Route::get('/baak/pengajuan-magang/{id}/lihat-surat-balasan', [PengajuanMagangController::class, 'lihatSuratBalasanBaak'])->name('baak.lihat_surat_balasan');
    Route::post('/baak/pengajuan-magang/{id}/kirim-email-surat-permohonan', [PengajuanMagangController::class, 'kirimEmailSuratPermohonan'])->name('baak.kirim_email_surat_permohonan');
    Route::post('/baak/pengajuan-magang/{id}/kirim-email-surat-pengantar', [PengajuanMagangController::class, 'kirimEmailSuratPengantar'])->name('baak.kirim_email_surat_pengantar');

    // ==========================================
    // KAPRODI - BERKAS MAGANG
    // (CRUD berkas magang buat Kaprodi)
    // ==========================================
    Route::get('/kaprodi/berkas-magang', [BerkasMagangController::class, 'index'])->name('kaprodi.berkas-magang');
    Route::post('/kaprodi/berkas-magang', [BerkasMagangController::class, 'store'])->name('kaprodi.berkas-magang.store');
    Route::put('/kaprodi/berkas-magang/{id_berkas_magang}', [BerkasMagangController::class, 'update'])->name('kaprodi.berkas-magang.update');
    Route::delete('/kaprodi/berkas-magang/{id_berkas_magang}', [BerkasMagangController::class, 'destroy'])->name('kaprodi.berkas-magang.destroy');

        // ==========================================
    // ADMIN KAJUR - BERKAS MAGANG
    // (CRUD berkas magang buat Admin Kajur)
    // ==========================================
    Route::get('/admin-kajur/berkas-magang', [BerkasMagangController::class, 'indexAdminKajur'])->name('admin_kajur.berkas-magang.index');
    Route::get('/admin-kajur/berkas-magang/create', [BerkasMagangController::class, 'createAdminKajur'])->name('admin_kajur.berkas-magang.create');
    Route::post('/admin-kajur/berkas-magang', [BerkasMagangController::class, 'storeAdminKajur'])->name('admin_kajur.berkas-magang.store');
    Route::get('/admin-kajur/berkas-magang/{id_berkas_magang}/edit', [BerkasMagangController::class, 'editAdminKajur'])->name('admin_kajur.berkas-magang.edit');
    Route::put('/admin-kajur/berkas-magang/{id_berkas_magang}', [BerkasMagangController::class, 'updateAdminKajur'])->name('admin_kajur.berkas-magang.update');
    Route::delete('/admin-kajur/berkas-magang/{id_berkas_magang}', [BerkasMagangController::class, 'destroyAdminKajur'])->name('admin_kajur.berkas-magang.destroy');


    // ==========================================
    // KAPRODI - PEMBAGIAN DOSEN PEMBIMBING
    // (Kaprodi bagi dosen pembimbing ke mahasiswa, terus diajukan ke Kajur)
    // ==========================================
    Route::get('/kaprodi/pembagian-dosen-pembimbing', [DosenPembimbingController::class, 'indexKaprodi'])->name('kaprodi.dosen-pembimbing.index');
    Route::get('/kaprodi/pembagian-dosen-pembimbing/create', [DosenPembimbingController::class, 'createKaprodi'])->name('kaprodi.dosen-pembimbing.create');
    Route::post('/kaprodi/pembagian-dosen-pembimbing', [DosenPembimbingController::class, 'storeKaprodi'])->name('kaprodi.dosen-pembimbing.store');
    Route::get('/kaprodi/pembagian-dosen-pembimbing/{id}/edit', [DosenPembimbingController::class, 'editKaprodi'])->name('kaprodi.dosen-pembimbing.edit');
    Route::put('/kaprodi/pembagian-dosen-pembimbing/{id}', [DosenPembimbingController::class, 'updateKaprodi'])->name('kaprodi.dosen-pembimbing.update');
    Route::delete('/kaprodi/pembagian-dosen-pembimbing/{id}', [DosenPembimbingController::class, 'destroyKaprodi'])->name('kaprodi.dosen-pembimbing.destroy');
    Route::get('/kaprodi/pembagian-dosen-pembimbing/{id}/detail', [DosenPembimbingController::class, 'detailKaprodi'])->name('kaprodi.dosen-pembimbing.detail');
    Route::get('/kaprodi/dosen-pembimbing/{id}/lihat-sk', [DosenPembimbingController::class, 'lihatSkKaprodi'])->name('kaprodi.dosen-pembimbing.lihat_sk');
    Route::get('/kaprodi/dosen-pembimbing/preview-surat', [DosenPembimbingController::class, 'previewSuratFinalKaprodi'])->name('kaprodi.dosen-pembimbing.preview-surat');
    Route::post('/kaprodi/pembagian-dosen-pembimbing/ajukan', [DosenPembimbingController::class, 'ajukanKaprodi'])->name('kaprodi.dosen-pembimbing.ajukan');
    Route::get('/dosen-pembimbing/export', [DosenPembimbingController::class, 'exportKaprodi'])->name('kaprodi.dosen-pembimbing.export');

    // ==========================================
    // KAJUR - DOSEN PEMBIMBING
    // (Kajur yang nge-approve SK dosen pembimbing yang diajukan Kaprodi)
    // ==========================================
    Route::get('/kajur/dosen-pembimbing', [DosenPembimbingController::class, 'indexKajur'])->name('kajur.dosen-pembimbing.index');
    Route::get('/kajur/dosen-pembimbing/{id}/detail', [DosenPembimbingController::class, 'detailKajur'])->name('kajur.dosen-pembimbing.detail');
    Route::post('/kajur/dosen-pembimbing/{id}/approval', [DosenPembimbingController::class, 'prosesApprovalKajur'])->name('kajur.dosen-pembimbing.proses_approval');
    Route::get('/kajur/dosen-pembimbing/prodi/{id_prodi}/lihat-sk', [DosenPembimbingController::class, 'lihatSkKajur'])->name('kajur.dosen-pembimbing.lihat_sk');
    Route::get('/kajur/dosen-pembimbing/prodi/{id_prodi}', [DosenPembimbingController::class, 'previewSuratFinalKajurPerProdi'])->name('kajur.dosen-pembimbing.preview-per-prodi');
    Route::post('/kajur/dosen-pembimbing/prodi/{id_prodi}/approval', [DosenPembimbingController::class, 'prosesApprovalKajurPerProdi'])->name('kajur.dosen-pembimbing.proses_approval_per_prodi');
    Route::get('/kajur/dosen-pembimbing/preview-surat', [DosenPembimbingController::class, 'previewSuratFinalKajur'])->name('kajur.dosen-pembimbing.preview-surat');

   
    // ==========================================
    // DOSEN PEMBIMBING - MAHASISWA BIMBINGAN
    // (Dospem ngeliat daftar mahasiswa yang dia bimbing)
    // ==========================================
    Route::get('/dospem/mahasiswa-bimbingan', [DosenPembimbingController::class, 'indexDospem'])->name('dospem.mahasiswa-bimbingan');
    Route::get('/dospem/mahasiswa-bimbingan/{id}/detail', [DosenPembimbingController::class, 'detailDospem'])->name('dospem.mahasiswa-detail');
    Route::get('/dospem/mahasiswa-bimbingan/{id}/lihat-sk', [DosenPembimbingController::class, 'lihatSkDospem'])->name('dospem.lihat-sk');

    // ==========================================
    // KAPRODI - MONITORING
    // (Kaprodi ngawasin pembimbing sama penempatan magangnya)
    // ==========================================
    Route::get('/kaprodi/monitoring-pembimbing', [DosenPembimbingController::class, 'monitoringPembimbing'])->name('kaprodi.monitoring.pembimbing');
    Route::get('/kaprodi/monitoring/penempatan', [PengajuanMagangController::class, 'monitoringPenempatan'])->name('kaprodi.monitoring.penempatan');

    // ==========================================
    // MAHASISWA - LOGBOOK
    // (Mahasiswa ngisi laporan mingguan logbook magang)
    // ==========================================
    Route::get('/mahasiswa/logbook', [LogbookController::class, 'indexMahasiswa'])->name('mahasiswa.logbook.index');
    Route::get('/mahasiswa/logbook/minggu-{minggu}/rekap', [LogbookController::class, 'rekapMingguMahasiswa'])
        ->where('minggu', '[0-9]+')
        ->name('mahasiswa.logbook.rekap');
    Route::get('/mahasiswa/logbook/minggu-{minggu}', [LogbookController::class, 'showMingguMahasiswa'])
        ->where('minggu', '[0-9]+')
        ->name('mahasiswa.logbook.minggu');
    Route::get('/mahasiswa/logbook/create', [LogbookController::class, 'createMahasiswa'])->name('mahasiswa.logbook.create');
    Route::post('/mahasiswa/logbook', [LogbookController::class, 'storeMahasiswa'])->name('mahasiswa.logbook.store');
    Route::get('/mahasiswa/logbook/{id_logbook}/edit', [LogbookController::class, 'editMahasiswa'])->name('mahasiswa.logbook.edit');
    Route::put('/mahasiswa/logbook/{id_logbook}', [LogbookController::class, 'updateMahasiswa'])->name('mahasiswa.logbook.update');
    Route::delete('/mahasiswa/logbook/{id_logbook}', [LogbookController::class, 'destroyMahasiswa'])->name('mahasiswa.logbook.destroy');
    Route::get('/mahasiswa/logbook/{id_logbook}/detail', [LogbookController::class, 'detailMahasiswa'])->name('mahasiswa.logbook.detail');

    // ==========================================
    // DOSEN PEMBIMBING - LOGBOOK
    // (Dospem ngawas dan nge-approve logbook mahasiswa per minggu)
    // ==========================================
    Route::get('/dospem/logbook', [LogbookController::class, 'indexDospem'])->name('dospem.logbook.index');
    Route::get('/dospem/logbook/{id}/per-mahasiswa', [LogbookController::class, 'detailDospem'])->name('dospem.logbook.per_mahasiswa');
    Route::get('/dospem/logbook/detail/{id_logbook}', [LogbookController::class, 'detailLogbookDospem'])->name('dospem.logbook.detail_logbook');
    Route::put('/dospem/logbook/konfirmasi/{id_logbook}', [LogbookController::class, 'konfirmasiLogbookDospem'])->name('dospem.logbook.konfirmasi');
    Route::post('/dospem/logbook/approve-minggu/{id_dosen_pembimbing}/{minggu}', [LogbookController::class, 'approveMingguDospem'])->name('dospem.logbook.approve-minggu');
    Route::get('/dospem/logbook/rekap/{id_dosen_pembimbing}/{minggu}', [LogbookController::class, 'rekapMingguDospem'])->name('dospem.logbook.rekap');

    // ==========================================
// DOSEN PEMBIMBING - ABSENSI
// (Dospem ngeliat dan validasi absensi mahasiswa bimbingan)
// ==========================================
Route::get('/dospem/absensi', [AbsensiController::class, 'indexDospem'])->name('dospem.absensi.index');
Route::get('/dospem/absensi/{id_mahasiswa}/detail', [AbsensiController::class, 'detailDospem'])->name('dospem.absensi.detail');
Route::get('/dospem/absensi/{id_absensi}/validasi', [AbsensiController::class, 'validasiDospem'])->name('dospem.absensi.validasi');
Route::put('/dospem/absensi/{id_absensi}/proses-validasi', [AbsensiController::class, 'prosesValidasiDospem'])->name('dospem.absensi.proses-validasi');

    // ==========================================
    // KAPRODI - JADWAL SEMINAR MAGANG
    // (Kaprodi atur jadwal seminar, mahasiswa ngeliat jadwalnya)
    // ==========================================
    Route::get('/kaprodi/jadwal-seminar-magang/get-detail-mahasiswa/{id_mahasiswa}', [JadwalSemgangController::class, 'getDetailMahasiswa'])->name('kaprodi.jadwal-semgang.get-detail-mahasiswa');
    Route::get('/kaprodi/jadwal-semgang', [JadwalSemgangController::class, 'index'])->name('kaprodi.jadwal-semgang.index');
    Route::get('/kaprodi/jadwal-semgang/create', [JadwalSemgangController::class, 'create'])->name('kaprodi.jadwal-semgang.create');
    Route::post('/kaprodi/jadwal-semgang', [JadwalSemgangController::class, 'store'])->name('kaprodi.jadwal-semgang.store');
    Route::get('/kaprodi/jadwal-semgang/{id}/edit', [JadwalSemgangController::class, 'edit'])->name('kaprodi.jadwal-semgang.edit');
    Route::put('/kaprodi/jadwal-semgang/{id}', [JadwalSemgangController::class, 'update'])->name('kaprodi.jadwal-semgang.update');
    Route::delete('/kaprodi/jadwal-semgang/{id}', [JadwalSemgangController::class, 'destroy'])->name('kaprodi.jadwal-semgang.destroy');
    Route::get('/kaprodi/jadwal-seminar/{id}/detail-nilai', [JadwalSemgangController::class, 'detailNilaiKaprodi'])->name('kaprodi.jadwal-semgang.detail-nilai');
    Route::get('/kaprodi/interview/detail/{id_perusahaan}', [DosenVisitasiController::class, 'detailInterviewKaprodi'])->name('kaprodi.interview-detail');

    // ==========================================
    // DOSEN PEMBIMBING - JADWAL SEMINAR MAGANG
    // (Dospem ngasih nilai pas seminar magang)
    // ==========================================
    Route::get('/dospem/jadwal-seminar-magang', [JadwalSemgangController::class, 'indexDospem'])->name('dospem.jadwal-semgang.index');
    Route::get('/dospem/jadwal-seminar-magang/{id}/form-nilai', [JadwalSemgangController::class, 'formNilaiDospem'])->name('dospem.jadwal-semgang.form-nilai');
    Route::put('/dospem/jadwal-seminar-magang/{id}/store-nilai', [JadwalSemgangController::class, 'storeNilaiDospem'])->name('dospem.jadwal-semgang.store-nilai');
    Route::get('/dospem/jadwal-seminar-magang/{id}/detail-nilai', [JadwalSemgangController::class, 'detailNilaiDospem'])->name('dospem.jadwal-semgang.detail-nilai');

    // ==========================================
    // MAHASISWA - PELAKSANAAN SEMINAR MAGANG
    // (Mahasiswa ngeliat jadwal seminar sampe kelulusannya)
    // ==========================================
    Route::get('/mahasiswa/pelaksanaan-seminar-magang', [JadwalSemgangController::class, 'indexMahasiswa'])->name('mahasiswa.pelaksanaan-seminar.index');
    Route::get('/mahasiswa/pelaksanaan-seminar-magang/{id}/detail', [JadwalSemgangController::class, 'detailMahasiswa'])->name('mahasiswa.pelaksanaan-seminar.detail');
    Route::put('/mahasiswa/pelaksanaan-seminar-magang/{id}', [JadwalSemgangController::class, 'updateMahasiswa'])->name('mahasiswa.pelaksanaan-seminar.update');

    // ==========================================
    // KAPRODI - TOKEN PERUSAHAAN
    // (Kaprodi bikin token buat perusahaan biar bisa ngisi penilaian)
    // ==========================================
    Route::get('/kaprodi/token-perusahaan', [KaprodiTokenController::class, 'index'])->name('kaprodi.token-perusahaan.index');
    Route::get('/kaprodi/token-perusahaan/create', [KaprodiTokenController::class, 'create'])->name('kaprodi.token-perusahaan.create');
    Route::post('/kaprodi/token-perusahaan', [KaprodiTokenController::class, 'store'])->name('kaprodi.token-perusahaan.store');
    Route::post('/kaprodi/token-perusahaan/{id_token}/regenerate', [KaprodiTokenController::class, 'regenerate'])->name('kaprodi.token-perusahaan.regenerate');
    Route::get('/kaprodi/token-perusahaan/{id_token}/detail-nilai', [KaprodiTokenController::class, 'detailNilai'])->name('kaprodi.token-perusahaan.detail-nilai');
    Route::post('/token-perusahaan/force-regenerate', [KaprodiTokenController::class, 'forceRegenerateAll'])
    ->name('kaprodi.token-perusahaan.force-regenerate');

    // ==========================================
    // KAPRODI - PEMBAGIAN DOSEN VISITASI
    // (Kaprodi bagi dosen visitasi, terus diajukan ke Kajur)
    // ==========================================
    Route::get('/kaprodi/pembagian-dosen-visitasi', [DosenVisitasiController::class, 'indexKaprodi'])->name('kaprodi.pembagian-dosen-visitasi.index');
    Route::get('/kaprodi/pembagian-dosen-visitasi/create', [DosenVisitasiController::class, 'createKaprodi'])->name('kaprodi.pembagian-dosen-visitasi.create');
    Route::post('/kaprodi/pembagian-dosen-visitasi', [DosenVisitasiController::class, 'storeKaprodi'])->name('kaprodi.pembagian-dosen-visitasi.store');
    Route::get('/kaprodi/pembagian-dosen-visitasi/{id}/edit', [DosenVisitasiController::class, 'editKaprodi'])->name('kaprodi.pembagian-dosen-visitasi.edit');
    Route::put('/kaprodi/pembagian-dosen-visitasi/{id}', [DosenVisitasiController::class, 'updateKaprodi'])->name('kaprodi.pembagian-dosen-visitasi.update');
    Route::delete('/kaprodi/pembagian-dosen-visitasi/{id}', [DosenVisitasiController::class, 'destroyKaprodi'])->name('kaprodi.pembagian-dosen-visitasi.destroy');
    Route::get('/kaprodi/pembagian-dosen-visitasi/{id}/detail', [DosenVisitasiController::class, 'detailKaprodi'])->name('kaprodi.pembagian-dosen-visitasi.detail');
    Route::get('/kaprodi/pembagian-dosen-visitasi/preview-surat', [DosenVisitasiController::class, 'previewSuratFinalKaprodi'])->name('kaprodi.pembagian-dosen-visitasi.preview-surat');
    Route::get('/kaprodi/pembagian-dosen-visitasi/{id}/lihat-sk', [DosenVisitasiController::class, 'lihatSkKaprodi'])->name('kaprodi.pembagian-dosen-visitasi.lihat_sk');
    Route::post('/kaprodi/pembagian-dosen-visitasi/ajukan', [DosenVisitasiController::class, 'ajukanKaprodi'])->name('kaprodi.pembagian-dosen-visitasi.ajukan');
    Route::get('/kaprodi/pembagian-dosen-visitasi/export', [DosenVisitasiController::class, 'exportKaprodi'])->name('kaprodi.pembagian-dosen-visitasi.export');
    
    

    // ==========================================
    // KAJUR - DOSEN VISITASI
    // (Kajur nge-approve SK dosen visitasi yang diajukan Kaprodi)
    // ==========================================
    Route::get('/kajur/pembagian-dosen-visitasi', [DosenVisitasiController::class, 'indexKajur'])->name('kajur.pembagian-dosen-visitasi.index');
    Route::get('/kajur/pembagian-dosen-visitasi/preview/{id_prodi}', [DosenVisitasiController::class, 'previewSuratFinalKajur'])->name('kajur.pembagian-dosen-visitasi.preview');
    Route::post('/kajur/pembagian-dosen-visitasi/proses-approval/{id_prodi}', [DosenVisitasiController::class, 'prosesApprovalKajurPerProdi'])->name('kajur.dosen-visitasi.proses_approval_per_prodi');
    Route::get('/kajur/pembagian-dosen-visitasi/prodi/{id_prodi}/lihat-sk', [DosenVisitasiController::class, 'lihatSkKajur'])->name('kajur.dosen-visitasi.lihat_sk');
    

    // ==========================================
    // BAAK - DOSEN VISITASI (VIEW ONLY)
    // (BAAK cuma bisa lihat SK dosen visitasi)
    // ==========================================
    Route::get('/baak/dosen-visitasi', [DosenVisitasiController::class, 'indexBaak'])->name('baak.dosen-visitasi.index');
    Route::get('/baak/dosen-visitasi/prodi/{id_prodi}', [DosenVisitasiController::class, 'previewSuratFinalBaak'])->name('baak.dosen-visitasi.preview-per-prodi');
    Route::get('/baak/dosen-visitasi/prodi/{id_prodi}/lihat-sk', [DosenVisitasiController::class, 'lihatSkBaak'])->name('baak.dosen-visitasi.lihat_sk');

    // ==========================================
    // BAUP - DOSEN VISITASI (BISA UPLOAD SK)
    // (BAUP bisa upload SK dosen visitasi)
    // ==========================================
    Route::get('/baup/dosen-visitasi', [DosenVisitasiController::class, 'indexBaup'])->name('baup.dosen-visitasi.index');
    Route::get('/baup/dosen-visitasi/prodi/{id_prodi}', [DosenVisitasiController::class, 'previewSuratFinalBaup'])->name('baup.dosen-visitasi.preview-per-prodi');
    Route::get('/baup/dosen-visitasi/prodi/{id_prodi}/upload-sk', [DosenVisitasiController::class, 'formUploadSkBaup'])->name('baup.dosen-visitasi.upload_sk');
    Route::post('/baup/dosen-visitasi/prodi/{id_prodi}/upload-sk', [DosenVisitasiController::class, 'uploadSkBaup'])->name('baup.dosen-visitasi.upload_sk.store');
    Route::get('/baup/dosen-visitasi/prodi/{id_prodi}/lihat-sk', [DosenVisitasiController::class, 'lihatSkBaup'])->name('baup.dosen-visitasi.lihat_sk');

    // ==========================================
    // DOSEN VISITASI - ROLE
    // (Dosen visitasi ngeliat jadwal visitasi, ngisi form interview, sama upload foto)
    // ==========================================
    Route::get('/visitasi/mahasiswa-visitasi', [DosenVisitasiController::class, 'indexVisitasi'])->name('visitasi.mahasiswa-visitasi');
    Route::get('/visitasi/mahasiswa-visitasi/{id}/detail', [DosenVisitasiController::class, 'detailVisitasi'])->name('visitasi.mahasiswa-detail');
    Route::put('/visitasi/mahasiswa-visitasi/{id}/update-visitasi', [DosenVisitasiController::class, 'updateVisitasi'])->name('visitasi.update-visitasi');
    Route::get('/visitasi/lihat-sk', [DosenVisitasiController::class, 'lihatSkVisitasi'])->name('visitasi.lihat-sk');
    Route::post('/dosen-visitasi/interview/store-photo/{id_perusahaan}', [DosenVisitasiController::class, 'storePhotoInterview'])->name('dosen-visitasi.interview-store-photo');

    Route::get('/dosen-visitasi/mahasiswa-visitasi', [DosenVisitasiController::class, 'indexDosenVisitasi'])->name('dosen-visitasi.mahasiswa-visitasi');
    Route::get('/dosen-visitasi/personal-interview', [DosenVisitasiController::class, 'personalInterview'])->name('dosen-visitasi.personal-interview');
    Route::get('/dosen-visitasi/lihat-sk/{id_dosen}', [DosenVisitasiController::class, 'lihatSkDosenVisitasi'])->name('dosen-visitasi.lihat-sk');
    Route::get('/dosen-visitasi/perusahaan-visitasi/{id}/detail', [DosenVisitasiController::class, 'detailPerusahaanVisitasi'])->name('dosen-visitasi.perusahaan-detail');
    Route::get('/dosen-visitasi/interview/form-a/{id_perusahaan}', [DosenVisitasiController::class, 'formInterviewA'])->name('dosen-visitasi.interview-form-a');
    Route::post('/dosen-visitasi/interview/store-a/{id_perusahaan}', [DosenVisitasiController::class, 'storeInterviewA'])->name('dosen-visitasi.interview-store-a');
    Route::get('/dosen-visitasi/interview/form-b/{id_perusahaan}', [DosenVisitasiController::class, 'formInterviewB'])->name('dosen-visitasi.interview-form-b');
    Route::post('/dosen-visitasi/interview/store-b/{id_perusahaan}', [DosenVisitasiController::class, 'storeInterviewB'])->name('dosen-visitasi.interview-store-b');
    Route::get('/dosen-visitasi/interview/detail/{id_perusahaan}', [DosenVisitasiController::class, 'detailInterview'])->name('dosen-visitasi.interview-detail');

    // ==========================================
    // KAPRODI - NILAI AKHIR MAHASISWA
    // (Kaprodi ngitung sama ngeliat nilai akhir mahasiswa magang)
    // ==========================================
    Route::get('/kaprodi/nilai-akhir-mahasiswa', [NilaiAkhirController::class, 'index'])->name('kaprodi.nilai-akhir.index');
    Route::get('/kaprodi/nilai-akhir-mahasiswa/{id_jadwal_semgang}/detail', [NilaiAkhirController::class, 'detail'])->name('kaprodi.nilai-akhir.detail');
    Route::post('/kaprodi/nilai-akhir-mahasiswa/{id_jadwal_semgang}/proses', [NilaiAkhirController::class, 'prosesNilaiAkhir'])->name('kaprodi.nilai-akhir.proses');
    Route::post('/kaprodi/nilai-akhir-mahasiswa/proses-semua', [NilaiAkhirController::class, 'prosesSemua'])->name('kaprodi.nilai-akhir.proses-semua');

    // ==========================================
    // KAPRODI - REFERENSI PERUSAHAAN
    // (Kaprodi milih perusahaan mana aja yang mau ditampilin ke halaman publik)
    // ==========================================
    Route::get('/kaprodi/referensi-perusahaan', [KaprodiReferensiPerusahaanController::class, 'index'])->name('kaprodi.referensi-perusahaan.index');
    Route::post('/kaprodi/referensi-perusahaan/toggle/{id_perusahaan}', [KaprodiReferensiPerusahaanController::class, 'toggleTampil'])->name('kaprodi.referensi-perusahaan.toggle');
    Route::post('/kaprodi/referensi-perusahaan/set-massal', [KaprodiReferensiPerusahaanController::class, 'setMassal'])->name('kaprodi.referensi-perusahaan.set-massal');

    // ==========================================
    // KAPRODI - PENILAIAN DOSEN PEMBIMBING
    // (Kaprodi ngeliat penilaian dari dospem)
    // ==========================================
    Route::get('/kaprodi/penilaian-dospem', [JadwalSemgangController::class, 'indexPenilaianDospemKaprodi'])->name('kaprodi.penilaian-dospem.index');
});

    // BERANDA GABUNGAN (Buat ngarahin halaman beranda kalau diakses lewat URL)
    Route::get('/beranda-mahasiswa', [BerandaPengunjungController::class, 'index']);
    Route::get('/beranda-dosen', [BerandaPengunjungController::class, 'index']);
    Route::get('/beranda-pegawai', [BerandaPengunjungController::class, 'index']);
    Route::get('/beranda-kajur', [BerandaPengunjungController::class, 'index']);
    Route::get('/beranda-kaprodi', [BerandaPengunjungController::class, 'index']);
    Route::get('/beranda-baak', [BerandaPengunjungController::class, 'index'])->name('pegawai.beranda-baak');
    Route::get('/beranda-baup', [BerandaPengunjungController::class, 'index'])->name('pegawai.beranda-baup');
    Route::get('/beranda-dospem', [BerandaPengunjungController::class, 'index'])->name('beranda-dospem');
    Route::get('/beranda-dosen-visitasi', [BerandaPengunjungController::class, 'index'])->name('beranda-dosen-visitasi');
    Route::get('/pegawai/beranda-admin-kajur', [PegawaiController::class, 'berandaAdminKajur'])->name('pegawai.beranda-admin-kajur');

// ==========================================
// NOTIFIKASI (WAJIB LOGIN)
// (Sistem notifikasi buat semua user yang login)
// ==========================================
Route::middleware(['auth', 'password.change'])->group(function () {
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/get', [NotifikasiController::class, 'getNotifications'])->name('notifikasi.get');
    Route::get('/notifikasi/{id_notifikasi}/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
    Route::post('/notifikasi/{id_notifikasi}/read', [NotifikasiController::class, 'markAsRead'])->name('notifikasi.read');
    Route::post('/notifikasi/read-all', [NotifikasiController::class, 'markAllAsRead'])->name('notifikasi.readAll');
    Route::get('/notifikasi/unread-count', [NotifikasiController::class, 'getUnreadCount'])->name('notifikasi.unreadCount');
});


// ==========================================
// MAHASISWA - DOSEN PEMBIMBING (reuse view kaprodi monitoring)
// (Mahasiswa ngeliat siapa dospemnya pake tampilan yang sama kayak kaprodi)
// ==========================================
Route::get('/mahasiswa/dosen-pembimbing', [DosenPembimbingController::class, 'indexMahasiswa'])->name('mahasiswa.dosen-pembimbing');

// ==========================================
// KAJUR - MONITORING BIMBINGAN MAGANG
// (Kajur ngawasin jadwal bimbingan dospem)
// ==========================================
Route::get('/kajur/monitoring-pembimbing', [DosenPembimbingController::class, 'monitoringPembimbingKajur'])->name('kajur.monitoring.pembimbing');
Route::get('/kajur/monitoring/dosen-pembimbing/{id_dosen_pembimbing}/lihat-sk', [DosenPembimbingController::class, 'lihatSkKajurMonitoring'])->name('kajur.monitoring.lihat_sk');

// ==========================================
// KAJUR - MONITORING BIMBINGAN MAGANG
// (Ini keknya ketulis dua kali di kodenya, fungsinya sama kayak di atas)
// ==========================================
Route::get('/kajur/monitoring-pembimbing', [DosenPembimbingController::class, 'monitoringPembimbingKajur'])->name('kajur.monitoring.pembimbing');
Route::get('/kajur/monitoring/dosen-pembimbing/{id_dosen_pembimbing}/lihat-sk', [DosenPembimbingController::class, 'lihatSkKajurMonitoring'])->name('kajur.monitoring.lihat_sk');
Route::get('/kajur/monitoring-visitasi', [DosenVisitasiController::class, 'monitoringVisitasiKajur'])->name('kajur.monitoring.visitasi');


// ==========================================
// MAHASISWA - DOSEN VISITASI (reuse view kajur monitoring)
// (Mahasiswa ngeliat siapa dosen visitasinya pake tampilan yang sama)
// ==========================================
Route::get('/mahasiswa/dosen-visitasi', [DosenVisitasiController::class, 'indexMahasiswaVisitasi'])->name('mahasiswa.dosen-visitasi');


// ADMIN - PENILAIAN PERUSAHAAN & ANGKET (Admin ngeliat hasil penilaian perusahaan)
Route::get('/admin/penilaian-perusahaan', [KaprodiTokenController::class, 'indexAdmin'])->name('admin.penilaian-perusahaan.index');
Route::get('/admin/penilaian-perusahaan/{id_token}/detail-angket', [KaprodiTokenController::class, 'detailAngketAdmin'])->name('admin.penilaian-perusahaan.detail-angket');
Route::get('/admin/penilaian-perusahaan/{id_token}/detail-penilaian', [KaprodiTokenController::class, 'detailPenilaianAdmin'])->name('admin.penilaian-perusahaan.detail-penilaian');


// ADMIN - PENILAIAN DOSEN PEMBIMBING (Admin ngeliat nilai dari dospem)
Route::get('/admin/penilaian-dospem', [JadwalSemgangController::class, 'indexPenilaianDospemAdmin'])->name('admin.penilaian-dospem.index');

Route::get('/admin/penilaian-dospem/{id}/detail', [JadwalSemgangController::class, 'detailNilaiDospemAdmin'])->name('admin.penilaian-dospem.detail');

// ADMIN - NILAI AKHIR MAHASISWA (Admin ngeliat rekap nilai akhir mahasiswa)
Route::get('/admin/nilai-akhir', [NilaiAkhirController::class, 'indexAdmin'])->name('admin.nilai-akhir.index');
Route::get('/admin/nilai-akhir/{id_jadwal_semgang}/detail', [NilaiAkhirController::class, 'detailAdmin'])->name('admin.nilai-akhir.detail');



// Di dalam Route::middleware(['auth', 'password.change'])->group(function () { ... }

// PROFIL (Buat ngubah profil sendiri, keterangan dari kodenya sih harus di dalem middleware)
Route::get('/edit-profil', [ProfilController::class, 'edit'])->name('profil.edit');
Route::put('/edit-profil', [ProfilController::class, 'update'])->name('profil.update');


// Di dalam Route::middleware(['auth', 'password.change'])->group(function () { ... }

Route::put('/edit-profil/password', [ProfilController::class, 'updatePassword'])->name('profil.updatePassword');

    // ==========================================
    // BACKUP SISTEM (Buat Admin)
    // ==========================================
    Route::get('/admin/backup', [BackupController::class, 'index'])->name('admin.backup.index');
    Route::get('/admin/backup/database', [BackupController::class, 'backupDatabase'])->name('admin.backup.database');
    Route::get('/admin/backup/source-code', [BackupController::class, 'backupSourceCode'])->name('admin.backup.source-code');
    Route::get('/admin/backup/download/{filename}', [BackupController::class, 'downloadBackup'])->name('admin.backup.download');
    Route::delete('/admin/backup/delete/{filename}', [BackupController::class, 'deleteBackup'])->name('admin.backup.delete');