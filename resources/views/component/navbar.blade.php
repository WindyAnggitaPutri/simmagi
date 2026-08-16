<style>
  /* ===================== CSS UNTUK NAVIGASI RESPONSIVE ===================== */
  
  /* Background gelap di belakang menu saat menu di HP dibuka */
  .nav-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 45;
    transition: opacity 0.3s ease;
  }

  .nav-overlay.is-active {
    display: block;
  }

  /* Kotak menu yang keluar dari samping kanan layar di tampilan HP */
  .nav-collapse {
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    right: 0;
    height: 100vh;
    width: 280px;
    background: #ffffff;
    box-shadow: -5px 0 15px rgba(0,0,0,0.1);
    z-index: 50;
    transform: translateX(100%);
    transition: transform 0.3s ease-in-out;
    overflow-y: auto;
    padding: 1.5rem;
  }

  .nav-collapse.is-open {
    transform: translateX(0);
  }

  .nav-collapse-inner {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    width: 100%;
    margin-top: 2rem;
  }

  /* Tombol untuk membuka menu hamburger di HP */
  .nav-toggle-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    border-radius: 0.375rem;
    cursor: pointer;
    background: transparent;
    border: none;
  }

  .nav-toggle-btn:hover {
    background-color: #f3f4f6;
  }

  /* Tombol silang untuk menutup menu di HP */
  .nav-close-btn {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 0.375rem;
  }

  .nav-close-btn:hover {
    background-color: #f3f4f6;
  }

  /* Pengaturan untuk menu yang memiliki submenu (dropdown) */
  .dropdown-item {
    position: relative;
  }

  .dropdown-trigger {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 0.5rem;
    width: 100%;
    padding: 0.5rem 0;
    cursor: pointer;
    background: transparent;
    border: none;
    font-weight: 400;
  }

  .dropdown-trigger:focus, 
  .dropdown-trigger:active,
  .nav-collapse a:focus,
  .nav-collapse a:active {
    outline: none !important;
    box-shadow: none !important;
    background-color: transparent !important;
  }

  /* Animasi panah dropdown saat menu dibuka */
  .dropdown-arrow {
    transition: transform 0.3s ease;
    width: 16px;
    height: 16px;
  }

  .dropdown-item.dropdown-open .dropdown-arrow {
    transform: rotate(90deg);
  }

  /* Menyembunyikan submenu secara default */
  .dropdown-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
    opacity: 0;
  }

  /* Menampilkan submenu saat diklik */
  .dropdown-item.dropdown-open .dropdown-content {
    max-height: 500px;
    opacity: 1;
  }

  .dropdown-content a {
    display: block;
    padding: 0.5rem 0 0.5rem 1.5rem;
    font-size: 0.875rem;
  }

  /* Tanda untuk halaman yang sedang aktif dikunjungi */
  .nav-active {
    color: #ea580c !important;
    font-weight: 600;
  }
  
  .dropdown-item.is-trigger-active > .dropdown-trigger {
    color: #ea580c !important;
  }

  /* Pengaturan tampilan navbar untuk layar komputer atau tablet */
  @media (min-width: 768px) {
    .nav-overlay {
      display: none !important;
    }

    .nav-collapse {
      position: relative;
      top: auto;
      right: auto;
      height: auto;
      width: auto;
      background: transparent;
      box-shadow: none;
      z-index: 40;
      transform: none;
      padding: 0;
      overflow-y: visible;
      flex-direction: row;
      align-items: center;
    }

    .nav-collapse-inner {
      flex-direction: row;
      align-items: center;
      gap: 1.5rem;
      width: auto;
      margin-top: 0;
    }

    .nav-toggle-btn,
    .nav-close-btn {
      display: none !important;
    }

    .dropdown-item {
      position: relative;
    }

    .dropdown-trigger {
      width: auto;
      padding: 0;
    }

    .dropdown-arrow {
      transform: none;
    }

    .dropdown-item:hover .dropdown-arrow,
    .dropdown-item:focus-within .dropdown-arrow {
      transform: rotate(90deg);
    }

    .dropdown-item.dropdown-open .dropdown-arrow {
      transform: rotate(90deg) !important;
    }

    /* Submenu jadi muncul ke bawah saat dihover di tampilan komputer */
    .dropdown-content {
      position: absolute !important;
      top: 100% !important;
      left: 0 !important;
      width: auto !important;
      min-width: 12rem !important;
      max-height: none !important;
      opacity: 1 !important;
      overflow: visible !important;
      padding-top: 0.5rem !important;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06) !important;
      border-radius: 0.375rem !important;
      background: #ffffff !important;
      border: 1px solid #e5e7eb !important;
      display: none !important; 
    }

    .dropdown-item:hover .dropdown-content,
    .dropdown-item:focus-within .dropdown-content {
      display: block !important;
    }

    .dropdown-content a {
      padding: 0.5rem 1rem;
      white-space: nowrap;
    }
    
    .dropdown-content a:hover {
      background-color: #f9fafb !important;
    }

    .nav-active {
      border-bottom: 2px solid #ea580c;
      padding-bottom: 2px;
      margin-bottom: -2px;
    }
  }
</style>


@php
    // Memanggil model yang dibutuhkan untuk mendapatkan data pengguna yang login
    use App\Models\BerkasMagang;
    use App\Models\Mahasiswa;
    use App\Models\Dosen;
    use App\Models\Pegawai;
    
    // Memberikan efek warna orange saat kursor diarahkan ke menu
    $hoverClass = 'hover:text-orange-500';
    // Khusus dosen pembimbing atau visitasi, warna hovernya diubah jadi kuning
    if(auth()->check() && auth()->user()->role == 'dosen') {
        $activeRole = session('dosen_active_role');
        if(in_array($activeRole, ['dospem', 'dosen_visitasi'])) {
            $hoverClass = 'hover:text-yellow-500';
        }
    }

    // Mengambil daftar jenis berkas untuk pengunjung yang belum login
    $jenisBerkasNavbar = [];
    if(!auth()->check()) {
        $jenisBerkasNavbar = BerkasMagang::select('jenis_berkas')->distinct()->pluck('jenis_berkas');
    }

    // Variabel untuk menyimpan info pengguna yang akan ditampilkan di pojok kanan navbar
    $nama = '';
    $identifier = '';
    $roleText = '';

    // Cek jika pengguna sudah login, lalu ambil datanya sesuai perannya (role)
    if(auth()->check()) {
        $user = auth()->user();
        
        // Ambil data mahasiswa
        if($user->role == 'mahasiswa') {
            $mhs = Mahasiswa::where('id_user', $user->id_user)->first();
            if($mhs) {
                $nama = $mhs->nama;
                $identifier = 'NPM: ' . $mhs->npm;
            }
            $roleText = 'Mahasiswa';
        } 
        // Ambil data dosen beserta perannya (kajur, kaprodi, dll)
        elseif($user->role == 'dosen') {
            $dsn = Dosen::where('id_user', $user->id_user)->first();
            if($dsn) {
                $nama = $dsn->nama;
                $identifier = 'NIDN: ' . $dsn->nidn;
            }
            $activeDosenRole = session('dosen_active_role');
            if($activeDosenRole == 'kajur') $roleText = 'Ketua Jurusan';
            elseif($activeDosenRole == 'kaprodi') $roleText = 'Kaprodi';
            elseif($activeDosenRole == 'dospem') $roleText = 'Dosen Pembimbing';
            elseif($activeDosenRole == 'dosen_visitasi') $roleText = 'Dosen Visitasi';
            else $roleText = 'Dosen';
        } 
        // Ambil data pegawai beserta perannya (baak, admin kajur)
        elseif($user->role == 'pegawai') {
            $pgw = Pegawai::where('id_user', $user->id_user)->first();
            if($pgw) {
                $nama = $pgw->nama;
                $identifier = 'NIP: ' . $pgw->nip;
            }
            $activePegawaiRole = session('role_pegawai');
            if($activePegawaiRole == 'baak') $roleText = 'BAAK';
            elseif($activePegawaiRole == 'admin_kajur') $roleText = 'Admin Kajur';
            else $roleText = 'Pegawai';
        } 
        // Khusus admin
        elseif($user->role == 'admin') {
            $nama = 'Administrator';
            $identifier = 'Admin';
            $roleText = 'Admin';
        }
    }
@endphp

<!-- Bagian utama navbar -->
<nav class="bg-white shadow-md px-4 md:px-6 py-4  relative z-50 sticky top-0">
    
    <div class="flex items-center justify-between">
        <!-- Logo aplikasi dan nama -->
        <div class="flex items-center gap-2 shrink-0">
            <img src="{{ asset('images/logo.png') }}" class="h-10">
            <div class="font-bold text-xl text-[#ece75f]">SIMMAGI</div>
        </div>

        <!-- Bagian menu yang akan muncul beda tergantung ukuran layar -->
        <div id="nav-menu" class="nav-collapse">
            
            <!-- Tombol silang untuk menutup menu di HP -->
            <button id="nav-close" class="nav-close-btn">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <div class="nav-collapse-inner">
                
                {{-- MENU UNTUK PENGGUNA YANG BELUM LOGIN (TAMU) --}}
                @guest
                    <a href="/" class="{{ $hoverClass }} py-2 {{ request()->is('/') ? 'nav-active' : '' }}">Beranda</a>
                    <a href="{{ route('pengunjung.jadwal-seminar') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('pengunjung.jadwal-seminar') ? 'nav-active' : '' }}">Jadwal Seminar</a>
                    <a href="{{ route('pengunjung.referensi-perusahaan') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('pengunjung.referensi-perusahaan') ? 'nav-active' : '' }}">Referensi Magang</a>
                    
                    <!-- Submenu dropdown untuk melihat daftar berkas -->
                    <div class="dropdown-item {{ request()->routeIs('berkas.*') ? 'is-trigger-active' : '' }}">
                        <button class="dropdown-trigger {{ $hoverClass }}">
                            Berkas
                            <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                        <div class="dropdown-content">
                            @forelse($jenisBerkasNavbar as $jenis)
                                <a href="{{ route('berkas.jenis', ['jenis' => $jenis]) }}" class="{{ $hoverClass }} {{ request()->routeIs('berkas.jenis') && request('jenis') == $jenis ? 'nav-active' : '' }}">{{ $jenis }}</a>
                            @empty
                                <a href="#" class="text-gray-400">Belum ada berkas</a>
                            @endforelse
                        </div>
                    </div>
                @endguest

                {{-- MENU UNTUK PENGGUNA YANG SUDAH LOGIN --}}
                @auth
                    {{-- Menu khusus Mahasiswa --}}
                    @if(auth()->user()->role == 'mahasiswa')
                        <a href="/" class="{{ $hoverClass }} py-2 {{ request()->is('/') ? 'nav-active' : '' }}">Beranda</a>
                        <!-- Submenu bimbingan magang -->
                        <div class="dropdown-item {{ (request()->routeIs('mahasiswa.dosen-pembimbing') || request()->routeIs('mahasiswa.dosen-visitasi')) ? 'is-trigger-active' : '' }}">
                            <button class="dropdown-trigger {{ $hoverClass }}">
                                Bimbingan Magang
                                <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                            <div class="dropdown-content">
                                <a href="{{ route('mahasiswa.dosen-pembimbing') }}" class="{{ $hoverClass }} {{ request()->routeIs('mahasiswa.dosen-pembimbing') ? 'nav-active' : '' }}">Dosen Pembimbing</a>
                                <a href="{{ route('mahasiswa.dosen-visitasi') }}" class="{{ $hoverClass }} {{ request()->routeIs('mahasiswa.dosen-visitasi') ? 'nav-active' : '' }}">Dosen Visitasi</a>
                            </div>
                        </div>
   <a href="{{ route('mahasiswa.pengajuan_magang.index') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('mahasiswa.pengajuan_magang.*') ? 'nav-active' : '' }}">Pengajuan Magang</a>
<a href="{{ route('mahasiswa.absensi.index') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('mahasiswa.absensi.*') ? 'nav-active' : '' }}">Absensi</a>
<a href="{{ route('mahasiswa.pelaksanaan-seminar.index') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('mahasiswa.pelaksanaan-seminar.*') ? 'nav-active' : '' }}">Pelaksanaan Seminar Magang</a>
                        <a href="{{ route('mahasiswa.logbook.index') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('mahasiswa.logbook.*') ? 'nav-active' : '' }}">Logbook</a>

                    {{-- Menu khusus Dosen, dibedakan lagi berdasarkan peran aktifnya --}}
                    @elseif(auth()->user()->role == 'dosen')
                        @php $activeRole = session('dosen_active_role'); @endphp

                        {{-- Menu untuk Ketua Jurusan --}}
                        @if($activeRole == 'kajur')
                            <a href="/beranda-kajur" class="{{ $hoverClass }} py-2 {{ request()->is('beranda-kajur') ? 'nav-active' : '' }}">Beranda</a>
                            <a href="{{ route('kajur.monitoring.pembimbing') }}" class="{{ $hoverClass }} {{ request()->routeIs('kajur.monitoring.pembimbing') ? 'nav-active' : '' }}">Dosen Pembimbing</a>
                            <a href="{{ route('kajur.monitoring.visitasi') }}" class="{{ $hoverClass }} {{ request()->routeIs('kajur.monitoring.visitasi') ? 'nav-active' : '' }}">Dosen Visitasi</a>
                            <a href="{{ route('kajur.pengajuan_magang.index') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('kajur.pengajuan_magang.*') ? 'nav-active' : '' }}">Pengajuan Magang</a>

                        {{-- Menu untuk Kaprodi --}}
                        @elseif($activeRole == 'kaprodi')
                            <a href="/beranda-kaprodi" class="{{ $hoverClass }} py-2 {{ request()->is('beranda-kaprodi') ? 'nav-active' : '' }}">Beranda</a>
                            <a href="{{ route('kaprodi.referensi-perusahaan.index') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('kaprodi.referensi-perusahaan.*') ? 'nav-active' : '' }}">Referensi Perusahaan</a>
                            
                            <!-- Submenu untuk bimbingan magang -->
                            <div class="dropdown-item {{ (request()->routeIs('kaprodi.dosen-pembimbing.*') || request()->routeIs('kaprodi.pembagian-dosen-visitasi.*') || request()->routeIs('kaprodi.jadwal-semgang.*')) ? 'is-trigger-active' : '' }}">
                                <button class="dropdown-trigger {{ $hoverClass }}">
                                    Bimbingan Magang
                                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                                <div class="dropdown-content">
                                    <a href="{{ route('kaprodi.dosen-pembimbing.index') }}" class="{{ $hoverClass }} {{ request()->routeIs('kaprodi.dosen-pembimbing.*') ? 'nav-active' : '' }}">Pembagian Dosen Pembimbing</a>
                                    <a href="{{ route('kaprodi.pembagian-dosen-visitasi.index') }}" class="{{ $hoverClass }} {{ request()->routeIs('kaprodi.pembagian-dosen-visitasi.*') ? 'nav-active' : '' }}">Pembagian Dosen Visitasi</a>
                                    <a href="{{ route('kaprodi.jadwal-semgang.index') }}" class="{{ $hoverClass }} {{ request()->routeIs('kaprodi.jadwal-semgang.*') ? 'nav-active' : '' }}">Jadwal Seminar Magang</a>
                                </div>
                            </div>

                            <a href="{{ route('kaprodi.pengajuan_magang.index') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('kaprodi.pengajuan_magang.*') ? 'nav-active' : '' }}">Pengajuan Magang</a>
                            
                            <!-- Submenu untuk penilaian -->
                            <div class="dropdown-item {{ (request()->routeIs('kaprodi.token-perusahaan.*') || request()->routeIs('kaprodi.penilaian-dospem.*') || request()->routeIs('kaprodi.nilai-akhir.*')) ? 'is-trigger-active' : '' }}">
                                <button class="dropdown-trigger {{ $hoverClass }}">
                                    Penilaian
                                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                                <div class="dropdown-content">
                                    <a href="{{ route('kaprodi.token-perusahaan.index') }}" class="{{ $hoverClass }} {{ request()->routeIs('kaprodi.token-perusahaan.*') ? 'nav-active' : '' }}">Token Penilaian</a>
                                    <a href="{{ route('kaprodi.penilaian-dospem.index') }}" class="{{ $hoverClass }} {{ request()->routeIs('kaprodi.penilaian-dospem.*') ? 'nav-active' : '' }}">Penilaian Dospem</a>
                                    <a href="{{ route('kaprodi.nilai-akhir.index') }}" class="{{ $hoverClass }} {{ request()->routeIs('kaprodi.nilai-akhir.*') ? 'nav-active' : '' }}">Nilai Akhir Mahasiswa</a>
                                </div>
                            </div>
                            
                            <!-- Submenu untuk monitoring -->
                            <div class="dropdown-item {{ request()->routeIs('kaprodi.monitoring.*') ? 'is-trigger-active' : '' }}">
                                <button class="dropdown-trigger {{ $hoverClass }}">
                                    Monitoring
                                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                                <div class="dropdown-content">
                                    <a href="{{ route('kaprodi.monitoring.pembimbing') }}" class="{{ $hoverClass }} {{ request()->routeIs('kaprodi.monitoring.pembimbing') ? 'nav-active' : '' }}">Pembimbing</a>
                                    <a href="{{ route('kaprodi.monitoring.penempatan') }}" class="{{ $hoverClass }} {{ request()->routeIs('kaprodi.monitoring.penempatan') ? 'nav-active' : '' }}">Penempatan Mahasiswa</a>
                                </div>
                                
                            </div>
                                                        <a href="{{ route('kaprodi.rekap.index') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('kaprodi.rekap.*') ? 'nav-active' : '' }}">Rekap Data Magang</a>
                            
                            

                        {{-- Menu untuk Dosen Pembimbing --}}
                        @elseif($activeRole == 'dospem')
                            <a href="{{ route('beranda-dospem') }}" class="{{ $hoverClass }} py-2 {{ request()->is('beranda-dospem') ? 'nav-active' : '' }}">Beranda</a>
                            <a href="{{ route('dospem.mahasiswa-bimbingan') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('dospem.mahasiswa-bimbingan') ? 'nav-active' : '' }}">Mahasiswa Bimbingan</a>
                            <a href="{{ route('dospem.logbook.index') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('dospem.logbook.*') ? 'nav-active' : '' }}">Logbook Mahasiswa</a>
                            <!-- <a href="{{ route('dospem.absensi.index') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('dospem.absensi.*') ? 'nav-active' : '' }}">Absensi</a> -->
                            <a href="{{ route('dospem.jadwal-semgang.index') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('dospem.jadwal-semgang.*') ? 'nav-active' : '' }}">Pelaksanaan Seminar Magang</a>
                            <a href="{{ route('dosen.pilih-role') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('dosen.pilih-role') ? 'nav-active' : '' }}">Ganti Role</a>

                        {{-- Menu untuk Dosen Visitasi --}}
                        @elseif($activeRole == 'dosen_visitasi')
                            <a href="{{ route('beranda-dosen-visitasi') }}" class="{{ $hoverClass }} py-2 {{ request()->is('beranda-dosen-visitasi') ? 'nav-active' : '' }}">Beranda</a>
                            <a href="{{ route('dosen-visitasi.mahasiswa-visitasi') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('dosen-visitasi.mahasiswa-visitasi') ? 'nav-active' : '' }}">Perusahaan Visitasi</a>
                            <a href="{{ route('dosen.pilih-role') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('dosen.pilih-role') ? 'nav-active' : '' }}">Ganti Role</a>

                        {{-- Menu Dosen jika belum memilih peran --}}
                        @else
                            <a href="/beranda-dosen" class="{{ $hoverClass }} py-2 {{ request()->is('beranda-dosen') ? 'nav-active' : '' }}">Beranda</a>
                            <a href="#" class="{{ $hoverClass }} py-2">Referensi Perusahaan</a>
                            <div class="dropdown-item">
                                <button class="dropdown-trigger {{ $hoverClass }}">
                                    Bimbingan Magang
                                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                                <div class="dropdown-content">
                                    <a href="#" class="{{ $hoverClass }}">Dosen Pembimbing</a>
                                    <a href="#" class="{{ $hoverClass }}">Jadwal Bimbingan</a>
                                </div>
                            </div>
                            <a href="#" class="{{ $hoverClass }} py-2">Pengajuan Magang</a>
                            <a href="#" class="{{ $hoverClass }} py-2">Logbook</a>
                        @endif

                    {{-- Menu khusus Pegawai --}}
                    @elseif(auth()->user()->role == 'pegawai')
                        @php $rolePegawai = session('role_pegawai'); @endphp

                        {{-- Menu untuk Admin Kajur --}}
                        @if($rolePegawai == 'admin_kajur')
                            <a href="{{ route('pegawai.beranda-admin-kajur') }}" class="{{ $hoverClass }} py-2 {{ request()->is('beranda-admin-kajur') ? 'nav-active' : '' }}">Beranda</a>
                            <a href="{{ route('admin_kajur.pengajuan_magang.index') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('admin_kajur.pengajuan_magang.*') ? 'nav-active' : '' }}">Pengajuan Magang</a>
                            <a href="{{ route('admin_kajur.berkas-magang.index') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('admin_kajur.berkas-magang.*') ? 'nav-active' : '' }}">Berkas Magang</a>
                        {{-- Menu untuk BAAK --}}
                        @else
                            <a href="{{ route('pegawai.beranda-baak') }}" class="{{ $hoverClass }} py-2 {{ request()->is('pegawai/beranda-baak') ? 'nav-active' : '' }}">Beranda</a>
                            <a href="{{ route('baak.pengajuan_magang.index') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('baak.pengajuan_magang.*') ? 'nav-active' : '' }}">Pengajuan Magang</a>
                            <a href="{{ route('baak.monitoring.pembimbing') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('baak.monitoring.pembimbing') ? 'nav-active' : '' }}">Dosen Pembimbing</a>
                            <a href="{{ route('baak.dosen-visitasi.index') }}" class="{{ $hoverClass }} py-2 {{ request()->routeIs('baak.dosen-visitasi.*') ? 'nav-active' : '' }}">Dosen Visitasi</a>
                        @endif
                    @endif

                    @if(auth()->user()->role == 'pegawai' && session('role_pegawai') == 'baak')
                        <div class="border-t my-1"></div>
                    @endif
                @endauth
            </div>
        </div>

        {{-- BAGIAN KANAN NAVBAR UNTUK LOGIN, PROFIL, DAN TOMBOL MENU HP --}}
        <div class="flex items-center gap-3 shrink-0">
            @guest
                <a href="{{ route('login') }}" class="bg-orange-500 text-white px-4 py-2 rounded">Login</a>
            @endguest

            @auth
            {{-- Logika untuk menampilkan lonceng notifikasi bagi mahasiswa, dosen tertentu, dan pegawai tertentu --}}
            @php
                $showNotifBell = false;
                if(auth()->user()->role == 'mahasiswa') {
                    $showNotifBell = true;
                } elseif(auth()->user()->role == 'dosen' && in_array(session('dosen_active_role'), ['kaprodi', 'kajur'])) {
                    $showNotifBell = true;
                } elseif(auth()->user()->role == 'pegawai' && in_array(session('role_pegawai'), ['baak', 'admin_kajur'])) {
                    $showNotifBell = true;
                }
            @endphp

            @if($showNotifBell)
            <div class="relative" id="notif-container">
                
                <button id="notif-btn" class="relative p-2 text-gray-500 hover:text-orange-500 transition rounded-full hover:bg-gray-100 focus:outline-none focus:ring-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @php
                        $unreadCount = \App\Models\Notifikasi::where('id_user', auth()->user()->id_user)
                            ->where('read', 0)
                            ->count();
                    @endphp
                    @if($unreadCount > 0)
                        <span id="notif-badge" class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] rounded-full min-w-[18px] h-[18px] flex items-center justify-center font-bold leading-none px-1">
                            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                        </span>
                    @endif
                </button>

                <div id="notif-dropdown" class="absolute right-0 top-full mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-xl z-50 hidden">
                    
                    <div class="p-3 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 text-sm">Notifikasi</h3>
                        <button id="mark-all-read-btn" class="text-xs text-yellow-600 hover:text-yellow-700 font-medium transition">
                            Tandai semua dibaca
                        </button>
                    </div>

                    <div id="notif-list" class="max-h-80 overflow-y-auto">
                        <div class="p-6 text-center text-gray-400 text-sm">Memuat...</div>
                    </div>

                    <div class="p-2 border-t border-gray-100 text-center">
                        <a href="{{ route('notifikasi.index') }}" class="text-xs text-yellow-600 hover:text-yellow-700 font-medium transition">
                            Lihat Semua Notifikasi
                        </a>
                    </div>
                </div>
            </div>
            @endif

                <!-- Dropdown profil pengguna -->
                <div class="relative group">
                    <button class="flex items-center justify-center p-1 {{ $hoverClass }} focus:outline-none focus:ring-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </button>

                    <div class="absolute right-0 top-full w-56 pt-2 hidden group-hover:block z-50">
                        <div class="bg-white shadow-lg rounded-lg border py-2">
                            
                            <!-- Menampilkan nama, NPM/NIP, dan peran pengguna -->
                            <div class="px-4 py-2">
                                <p class="font-bold text-gray-800 truncate">{{ $nama }}</p>
                                <p class="text-sm text-gray-500">{{ $identifier }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $roleText }}</p>
                            </div>

                            <div class="border-t my-1"></div>

                            <a href="{{ route('profil.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50">
                                <span></span>
                                <span>Edit Profil</span>
                            </a>

                            <div class="border-t my-1"></div>

                            <!-- Tombol logout -->
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-semibold">
                                    Logout
                                </button>
                            </form>

                        </div>
                    </div>
                </div>
            @endauth

            <!-- Tombol hamburger untuk tampilan HP -->
            <button id="nav-toggle" class="nav-toggle-btn">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </div>
    </div>
</nav>

<div id="nav-overlay" class="nav-overlay"></div>

{{-- JAVASCRIPT UNTUK MENGATUR MENU HAMBURGER, OVERLAY, DAN DROPDOWN DI HP --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');
    const navClose = document.getElementById('nav-close');
    const navOverlay = document.getElementById('nav-overlay');

    // Fungsi membuka menu di HP
    function openMenu() {
        navMenu.classList.add('is-open');
        navOverlay.classList.add('is-active');
        document.body.style.overflow = 'hidden';
    }

    // Fungsi menutup menu di HP
    function closeMenu() {
        navMenu.classList.remove('is-open');
        navOverlay.classList.remove('is-active');
        document.body.style.overflow = '';
    }

    // Event listener untuk tombol buka, tutup, dan klik overlay
    if (navToggle) navToggle.addEventListener('click', openMenu);
    if (navClose) navClose.addEventListener('click', closeMenu);
    if (navOverlay) navOverlay.addEventListener('click', closeMenu);

    // Mengatur buka tutup submenu (dropdown) di HP
    const dropdownTriggers = document.querySelectorAll('.dropdown-trigger');
    
    dropdownTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const parentItem = this.closest('.dropdown-item');
            
            // Menutup dropdown lain yang sedang terbuka
            document.querySelectorAll('.dropdown-item.dropdown-open').forEach(openItem => {
                if (openItem !== parentItem) {
                    openItem.classList.remove('dropdown-open');
                }
            });

            // Membuka atau menutup dropdown yang diklik
            parentItem.classList.toggle('dropdown-open');
        });
    });

    // Membuka dropdown secara otomatis jika didalamnya ada menu yang sedang aktif dikunjungi
    const activeTrigger = document.querySelector('.dropdown-item.is-trigger-active');
    if (activeTrigger) {
        activeTrigger.classList.add('dropdown-open');
    }
});
</script>

{{-- JAVASCRIPT UNTUK MENGATUR FITUR LONCENG NOTIFIKASI --}}
@auth
@if(auth()->user()->role == 'mahasiswa' || (auth()->user()->role == 'dosen' && in_array(session('dosen_active_role'), ['kaprodi', 'kajur'])) || (auth()->user()->role == 'pegawai' && in_array(session('role_pegawai'), ['baak', 'admin_kajur'])))
<script>
document.addEventListener('DOMContentLoaded', function() {

    const notifBtn       = document.getElementById('notif-btn');
    const notifDropdown  = document.getElementById('notif-dropdown');
    const notifList      = document.getElementById('notif-list');
    const markAllBtn     = document.getElementById('mark-all-read-btn');
    const notifContainer = document.getElementById('notif-container');

    if (!notifBtn) return;

    // Mengambil token CSRF untuk keamanan saat mengirim data
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content 
                   || document.querySelector('input[name="_token"]')?.value 
                   || '{{ csrf_token() }}';

    // Membuka atau menutup kotak notifikasi saat lonceng diklik
    notifBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        notifDropdown.classList.toggle('hidden');
        if (!notifDropdown.classList.contains('hidden')) {
            loadNotifications();
        }
    });

    // Menutup kotak notifikasi jika klik di area luar kotak
    document.addEventListener('click', function(e) {
        if (notifContainer && !notifContainer.contains(e.target)) {
            notifDropdown.classList.add('hidden');
        }
    });

    // Fungsi untuk mengambil data notifikasi terbaru dari server
    function loadNotifications() {
        notifList.innerHTML = '<div class="p-6 text-center"><div class="inline-block w-5 h-5 border-2 border-yellow-500 border-t-transparent rounded-full animate-spin"></div><p class="text-gray-400 text-sm mt-2">Memuat...</p></div>';

        fetch('{{ route("notifikasi.get") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Gagal');
            return response.json();
        })
        .then(data => {
            // Jika notifikasi kosong
            if (!data || data.length === 0) {
                notifList.innerHTML = '<div class="p-6 text-center"><span class="text-4xl">🔔</span><p class="text-gray-400 text-sm mt-2">Belum ada notifikasi</p></div>';
                return;
            }

            // Menampilkan daftar notifikasi
            notifList.innerHTML = data.map(n => {
                const baseUrl = '{{ route("notifikasi.baca", ["id_notifikasi" => "__ID__"]) }}';
                const link = baseUrl.replace('__ID__', n.id_notifikasi);
                const bgClass = n.read ? 'bg-white' : 'bg-yellow-50';
                const fontClass = n.read ? 'font-medium' : 'font-bold';
                const dot = n.read ? '' : '<span class="w-2 h-2 bg-yellow-500 rounded-full mt-2 flex-shrink-0"></span>';
                
                return '<a href="' + link + '" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-50 transition ' + bgClass + '">' +
                    '<div class="flex items-start gap-3">' +
                        '<span class="text-xl flex-shrink-0 mt-0.5">' + n.icon + '</span>' +
                        '<div class="flex-1 min-w-0">' +
                            '<p class="font-semibold text-sm text-gray-800 leading-tight ' + fontClass + '">' + n.judul + '</p>' +
                            '<p class="text-xs text-gray-500 mt-0.5">' + n.pesan + '</p>' +
                            '<p class="text-[10px] text-gray-400 mt-1">' + n.waktu + '</p>' +
                        '</div>' +
                        dot +
                    '</div>' +
                '</a>';
            }).join('');
        })
        .catch(err => {
            // Tampilan jika gagal memuat data
            notifList.innerHTML = '<div class="p-4 text-center text-red-400 text-sm">Gagal memuat</div>';
        });
    }

    // Fungsi untuk menandai semua notifikasi sebagai sudah dibaca
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            fetch('{{ route("notifikasi.readAll") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const badge = document.getElementById('notif-badge');
                    if (badge) badge.remove();
                    loadNotifications();
                }
            });
        });
    }

});
</script>
@endif
@endauth