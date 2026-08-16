<aside class="w-64 bg-white border-r border-gray-200 flex-shrink-0 hidden md:flex flex-col">
    
    {{-- Logo Sidebar --}}
    <div class="h-16 flex items-center px-6 border-b border-gray-100 bg-[#fffbeb]">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 mr-3">
        <h1 class="font-bold text-lg tracking-wide text-gray-800">SIMMAGI <span class="text-xs font-normal text-[#ece75f]">Admin</span></h1>
    </div>

    {{-- Menu Navigasi --}}
    <nav class="flex-1 overflow-y-auto py-4">
        <ul class="space-y-1">

            {{-- Dashboard --}}
            <li>
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center px-6 py-3 transition group
                   {{ Request::is('admin/dashboard') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-orange-500' }}">
                    Dashboard
                </a>
            </li>

            {{-- Manajemen Pengguna --}}
            <li>
                @php
                    $isManajemenPengguna = Request::is('admin/user*') || 
                                           Request::is('admin/mahasiswa*') || 
                                           Request::is('admin/pegawai*') || 
                                           Request::is('admin/dosen') || 
                                           Request::is('admin/dosen/*');
                @endphp
                @if($isManajemenPengguna)
                    <details open class="group">
                @else
                    <details class="group">
                @endif
                    <summary class="flex items-center justify-between px-6 py-3 cursor-pointer text-gray-600 hover:bg-gray-50 hover:text-orange-500 transition list-none [&::-webkit-details-marker]:hidden">
                        <div class="flex items-center">
                            <span class="text-sm font-medium">Manajemen Pengguna</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </summary>
                    <ul class="mt-1 space-y-1 pb-1">
                        <li>
                            <a href="{{ route('admin.user.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/user*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">User</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.mahasiswa.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/mahasiswa*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Data Mahasiswa</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.dosen.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/dosen') || Request::is('admin/dosen/*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Data Dosen</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.pegawai.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/pegawai*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Data Pegawai</a>
                        </li>
                    </ul>
                </details>
            </li>

            {{-- Akademik --}}
            <li>
                @if(Request::is('admin/jurusan*') || Request::is('admin/prodi*') || Request::is('admin/jabatan') || Request::is('admin/jabatan/*') || Request::is('admin/jabatan-dosen*'))
                    <details open class="group">
                @else
                    <details class="group">
                @endif
                    <summary class="flex items-center justify-between px-6 py-3 cursor-pointer text-gray-600 hover:bg-gray-50 hover:text-orange-500 transition list-none [&::-webkit-details-marker]:hidden">
                        <div class="flex items-center">
                            <span class="text-sm font-medium">Akademik</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </summary>
                    <ul class="mt-1 space-y-1 pb-1">
                        <li>
                            <a href="{{ route('admin.jurusan.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/jurusan*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Data Jurusan</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.prodi.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/prodi*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Data Prodi</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.jabatan.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/jabatan') || Request::is('admin/jabatan/*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Data Jabatan</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.jabatan-dosen.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/jabatan-dosen*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Jabatan Tendik</a>
                        </li>
                    </ul>
                </details>
            </li>

            {{-- Data Magang --}}
            <li>
                @if(Request::is('admin/perusahaan*') || Request::is('admin/pengajuan-magang*') || Request::is('admin/monitoring-penempatan*') || Request::is('admin/berkas-magang*'))
                    <details open class="group">
                @else
                    <details class="group">
                @endif
                    <summary class="flex items-center justify-between px-6 py-3 cursor-pointer text-gray-600 hover:bg-gray-50 hover:text-orange-500 transition list-none [&::-webkit-details-marker]:hidden">
                        <div class="flex items-center">
                            <span class="text-sm font-medium">Data Magang</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </summary>
                    <ul class="mt-1 space-y-1 pb-1">
                        <li>
                            <a href="{{ route('admin.perusahaan.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/perusahaan*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Data Perusahaan</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.pengajuan-magang.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/pengajuan-magang*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Pengajuan Magang</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.monitoring-penempatan') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/monitoring-penempatan') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Monitoring Penempatan</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.berkas-magang.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/berkas-magang*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Berkas Magang</a>
                        </li>
                    </ul>
                </details>
            </li>

            {{-- Pembimbing & Visitasi --}}
            <li>
                @if(Request::is('admin/dosen-pembimbing*') || Request::is('admin/dosen-visitasi*') || Request::is('admin/personal-interview*'))
                    <details open class="group">
                @else
                    <details class="group">
                @endif
                    <summary class="flex items-center justify-between px-6 py-3 cursor-pointer text-gray-600 hover:bg-gray-50 hover:text-orange-500 transition list-none [&::-webkit-details-marker]:hidden">
                        <div class="flex items-center">
                            <span class="text-sm font-medium">Pembimbing & Visitasi</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </summary>
                    <ul class="mt-1 space-y-1 pb-1">
                        <li>
                            <a href="{{ route('admin.dosen-pembimbing.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/dosen-pembimbing*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Data Dosen Pembimbing</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.dosen-visitasi.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/dosen-visitasi*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Dosen Visitasi</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.personal-interview.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/personal-interview*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Personal Interview</a>
                        </li>
                    </ul>
                </details>
            </li>

            {{-- Monitoring Magang --}}
            <li>
                @if(Request::is('admin/logbook*') || Request::is('admin/jadwal-seminar*'))
                    <details open class="group">
                @else
                    <details class="group">
                @endif
                    <summary class="flex items-center justify-between px-6 py-3 cursor-pointer text-gray-600 hover:bg-gray-50 hover:text-orange-500 transition list-none [&::-webkit-details-marker]:hidden">
                        <div class="flex items-center">
                            <span class="text-sm font-medium">Monitoring Magang</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </summary>
                    <ul class="mt-1 space-y-1 pb-1">
                        <li>
                            <a href="{{ route('admin.logbook.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/logbook*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Logbook</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.jadwal-seminar.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/jadwal-seminar*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Jadwal Seminar</a>
                        </li>
                    </ul>
                </details>
            </li>
                        {{-- Penilaian --}}
            <li>
                @if(Request::is('admin/penilaian-perusahaan*') || Request::is('admin/penilaian-dospem*') || Request::is('admin/nilai-akhir*'))
                    <details open class="group">
                @else
                    <details class="group">
                @endif
                    <summary class="flex items-center justify-between px-6 py-3 cursor-pointer text-gray-600 hover:bg-gray-50 hover:text-orange-500 transition list-none [&::-webkit-details-marker]:hidden">
                        <div class="flex items-center">
                            <span class="text-sm font-medium">Penilaian</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </summary>
                    <ul class="mt-1 space-y-1 pb-1">
                        <li>
                            <a href="{{ route('admin.penilaian-perusahaan.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/penilaian-perusahaan*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Nilai Perusahaan dan Angket</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.penilaian-dospem.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/penilaian-dospem*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Nilai Dosen Pembimbing</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.nilai-akhir.index') }}" class="flex items-center pl-8 pr-6 py-2.5 text-sm transition {{ Request::is('admin/nilai-akhir*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-orange-500' }}">Nilai Akhir</a>
                        </li>
                    </ul>
                </details>
            </li>
                      {{-- Backup Sistem --}}
            <li>
                <a href="{{ route('admin.backup.index') }}" 
                   class="flex items-center px-6 py-3 transition group
                   {{ Request::is('admin/backup*') ? 'bg-[#fffbeb] text-[#b45309] border-r-4 border-[#ece75f] font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-orange-500' }}">
                    
                    Backup Sistem
                </a>
            </li>

        </ul>
    </nav>
      

    {{-- Sidebar Footer - Logout --}}
    <div class="p-4 border-t border-gray-100">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Logout
            </button>
        </form>
    </div>
</aside>