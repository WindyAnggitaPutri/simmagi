@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6">

    {{-- Judul --}}
    <h1 class="text-center text-2xl font-bold mb-2">
        PERMOHONAN PENGAJUAN MAGANG INDUSTRI <br>
        MAHASISWA JURUSAN KOMPUTER DAN BISNIS
    </h1>

    {{-- TAMBAHKAN INI: NOTIFIKASI ERROR JIKA NEKAT AKSES URL CREATE --}}
    @if (session('error'))
        <div class="max-w-3xl mx-auto mt-6 mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                <p class="font-semibold text-sm">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- Notifikasi Syarat Semester --}}
    @if(!$statusSemester['boleh_ajukan'])
        @if($statusSemester['belum_diatur'] ?? false)
            <div class="mt-6 bg-orange-50 border border-orange-300 text-orange-700 p-4 rounded-lg text-sm">
             Aturan semester pengajuan magang untuk prodi <strong>{{ $statusSemester['nama_prodi'] }}</strong> belum diatur oleh admin. 
                Silakan hubungi admin atau Kaprodi untuk mengatur semester pengajuan magang di halaman Kelola Prodi.
            </div>
        @else
            <div class="mt-6 bg-red-50 border border-red-300 text-red-700 p-4 rounded-lg text-sm">
                 Pengajuan magang untuk prodi <strong>{{ $statusSemester['nama_prodi'] }}</strong> dibuka mulai <strong>Semester {{ $statusSemester['min_semester'] }}</strong>. Anda saat ini berada di Semester {{ $statusSemester['semester_sekarang'] }}.
            </div>
        @endif
    @endif

    {{-- Button AJUKAN MAGANG --}}
    <div class="flex justify-center mt-8 mb-6">
        @if($bisaAjukanBaru)
            <a href="{{ route('mahasiswa.pengajuan_magang.create') }}" 
               class="border-2 border-yellow-500 text-yellow-600 font-bold text-lg px-12 py-3 hover:bg-yellow-500 hover:text-white transition-all duration-200">
                 AJUKAN MAGANG
            </a>
        @elseif($adaPengajuanBerjalan)
            <button disabled
                class="border-2 border-gray-300 text-gray-400 font-bold text-lg px-12 py-3 cursor-not-allowed bg-gray-50">
                AJUKAN MAGANG (SEDANG BERJALAN)
            </button>
        @else
            <button disabled
                class="border-2 border-gray-300 text-gray-400 font-bold text-lg px-12 py-3 cursor-not-allowed bg-gray-50"
                title="@if($statusSemester['belum_diatur'] ?? false) 
                    Aturan semester pengajuan untuk prodi Anda belum diatur admin. 
                @else 
                    Anda baru bisa mengajukan saat memasuki Semester {{ $statusSemester['min_semester'] }}. Saat ini Semester {{ $statusSemester['semester_sekarang'] }}. 
                @endif">
                + AJUKAN MAGANG 
                @if($statusSemester['belum_diatur'] ?? false)
                    (ATURAN BELUM DIISET)
                @else
                    (BELUM MEMENUHI SYARAT SEMESTER)
                @endif
            </button>
        @endif
    </div>

    {{-- ========================================== --}}
    {{-- TABEL 1: PENGAJUAN SAAT INI              --}}
    {{-- ========================================== --}}
    <div class="mt-8 mb-10">
        <h2 class="text-lg font-bold mb-4 border-l-4 border-yellow-500 pl-3 pb-2">Pengajuan Saat Ini</h2>

        @if ($pengajuanSaatIni->isEmpty())
            <div class="text-center text-gray-400 py-8 border border-gray-200 bg-gray-50 rounded-lg">
                Tidak ada pengajuan yang sedang berjalan.
            </div>
        @else
            <div class="overflow-x-auto border border-yellow-400 rounded-lg shadow-sm">
                <table class="min-w-[1200px] w-full text-sm text-left">
                    <thead class="bg-yellow-50 text-xs uppercase text-gray-700 border-b-2 border-yellow-400">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 whitespace-nowrap">Nama Perusahaan</th>
                            <th class="px-4 py-3 whitespace-nowrap">Nama Mahasiswa</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap w-20">Tahun Angkatan</th>
                            <th class="px-4 py-3 whitespace-nowrap">Tgl Diajukan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Status Pengajuan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Permohonan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Balasan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Pengantar</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($pengajuanSaatIni as $index => $p)
                            @php
                                $warna = $p->status_alur['color'];
                                $textStatus = $p->status_alur['text'];
                            @endphp
                            <tr class="hover:bg-yellow-50/40 transition">
                                <td class="px-4 py-3 text-center font-medium text-gray-600">{{ $loop->iteration }}</td>
                                
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-800">{{ $p->perusahaan->nama_perusahaan ?? '-' }}</span>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $p->perusahaan->alamat ?? '' }}</div>
                                </td>
                                
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($p->mahasiswa as $pm)
                                            @if ($pm->mahasiswa)
                                                <span class="inline-block bg-yellow-50 text-gray-700 text-xs px-2 py-1 border border-yellow-200 rounded">
                                                    {{ $pm->mahasiswa->nama }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>

                                {{-- ✅ KOLOM TAHUN ANGKATAN --}}
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $angkatanList = [];
                                        foreach ($p->mahasiswa as $pm) {
                                            if ($pm->mahasiswa && $pm->mahasiswa->angkatan && !in_array($pm->mahasiswa->angkatan, $angkatanList)) {
                                                $angkatanList[] = $pm->mahasiswa->angkatan;
                                            }
                                        }
                                    @endphp
                                    <div class="flex flex-wrap gap-1 justify-center">
                                        @foreach ($angkatanList as $angkatan)
                                            <span class="inline-block bg-blue-50 text-blue-700 text-xs font-bold px-2 py-1 border border-blue-200 rounded">
                                                {{ $angkatan }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                    {{ \Carbon\Carbon::parse($p->tgl_pengajuan)->locale('id')->translatedFormat('d F Y') }}
                                </td>
                                
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block border text-xs font-bold px-3 py-1 whitespace-nowrap {{ $warna }}">
                                        {{ strtoupper($textStatus) }}
                                    </span>
                                </td>
                                
                                {{-- KOLOM SURAT PERMOHONAN --}}
                                <td class="px-4 py-3 text-center">
                                    @if($p->suratPermohonan)
                                        <a href="{{ route('mahasiswa.pengajuan_magang.surat_permohonan', $p->id_pengajuan) }}" 
                                           class="bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-indigo-700 transition rounded">
                                             Lihat
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Menunggu BAAK</span>
                                    @endif
                                </td>

                                {{-- KOLOM SURAT BALASAN --}}
                                <td class="px-4 py-3 text-center">
                                    @if($p->ada_surat_balasan_kelompok)
                                        <a href="{{ route('mahasiswa.pengajuan_magang.upload_surat_balasan', $p->id_pengajuan) }}" 
                                           class="bg-purple-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-purple-700 transition rounded">
                                             Lihat
                                        </a>
                                    @elseif($p->sudah_ada_surat_permohonan)
                                        <a href="{{ route('mahasiswa.pengajuan_magang.upload_surat_balasan', $p->id_pengajuan) }}" 
                                           class="bg-yellow-500 text-white text-xs font-bold px-3 py-1.5 hover:bg-yellow-600 transition rounded">
                                             Upload
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic" title="Upload setelah Surat Permohonan dari BAAK tersedia">
                                          Menunggu Baak
                                        </span>
                                    @endif
                                </td>

                                {{-- KOLOM SURAT PENGANTAR --}}
                                <td class="px-4 py-3 text-center">
                                    @if($p->suratPengantar)
                                        <a href="{{ route('mahasiswa.lihat_surat_pengantar', $p->id_pengajuan) }}" 
                                           class="bg-purple-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-purple-700 transition rounded">
                                             Lihat
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Menunggu BAAK</span>
                                    @endif
                                </td>

                                {{-- KOLOM AKSI --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('mahasiswa.pengajuan_magang.detail', $p->id_pengajuan) }}" 
                                           class="text-blue-600 hover:text-blue-800 font-semibold text-sm hover:underline whitespace-nowrap">
                                            Detail
                                        </a>
                                        
                                        @if($p->boleh_batal)
                                            <form id="form-batal-{{ $p->id_pengajuan }}" action="{{ route('mahasiswa.pengajuan_magang.batal', $p->id_pengajuan) }}" method="POST">
                                                @csrf
                                                <button type="button" onclick="tampilkanModal('form-batal-{{ $p->id_pengajuan }}')" 
                                                    class="text-red-500 hover:text-red-700 font-semibold text-xs hover:underline whitespace-nowrap">
                                                    Batalkan
                                                </button>
                                            </form>
                                        @elseif($p->alasan_tidak_bisa_batal)
                                            <span class="text-orange-500 text-xs italic cursor-help whitespace-nowrap" 
                                                  title="{{ $p->alasan_tidak_bisa_batal }}"
                                                  style="border-bottom: 1px dashed #f97316;">
                                                 Batal
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ========================================== --}}
    {{-- TABEL 2: RIWAYAT PENGAJUAN MAHASISWA     --}}
    {{-- ========================================== --}}
    <div class="mt-10">
        <h2 class="text-lg font-bold mb-4 border-l-4 border-gray-500 pl-3 pb-2">Riwayat Pengajuan</h2>

        @if ($riwayatPengajuan->isEmpty())
            <div class="text-center text-gray-400 py-8 border border-gray-200 bg-gray-50 rounded-lg">
                Belum ada riwayat pengajuan.
            </div>
        @else
            <div class="overflow-x-auto border border-yellow-400 rounded-lg shadow-sm">
                <table class="min-w-[1200px] w-full text-sm text-left">
                    <thead class="bg-yellow-50 text-xs uppercase text-gray-700 border-b-2 border-yellow-400">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 whitespace-nowrap">Nama Perusahaan</th>
                            <th class="px-4 py-3 whitespace-nowrap">Nama Mahasiswa</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap w-20">Tahun Angkatan</th>
                            <th class="px-4 py-3 whitespace-nowrap">Tgl Diajukan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Status Pengajuan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Permohonan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Balasan</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Surat Pengantar</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($riwayatPengajuan as $index => $p)
                            @php
                                $warna = $p->status_alur['color'];
                                $textStatus = $p->status_alur['text'];
                                
                                $sayaDitolakDiBalasan = $p->my_surat_balasan_status === 'ditolak';
                                $sayaDitolakStatus = $p->my_status === 'ditolak';
                                if ($sayaDitolakDiBalasan || $sayaDitolakStatus) {
                                    $textStatus = 'Dibatalkan';
                                    $warna = 'bg-red-100 text-red-700 border-red-300';
                                }
                                
                                $ditolak = str_contains($textStatus, 'Ditolak');
                            @endphp
                            <tr class="hover:bg-yellow-50/40 transition">
                                <td class="px-4 py-3 text-center font-medium text-gray-600">{{ $loop->iteration }}</td>
                                
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-700">{{ $p->perusahaan->nama_perusahaan ?? '-' }}</span>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $p->perusahaan->alamat ?? '' }}</div>
                                </td>
                                
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($p->mahasiswa as $pm)
                                            @if ($pm->mahasiswa)
                                                <span class="inline-block bg-yellow-50 text-gray-700 text-xs px-2 py-1 border border-yellow-200 rounded">
                                                    {{ $pm->mahasiswa->nama }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>

                                {{-- ✅ KOLOM TAHUN ANGKATAN --}}
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $angkatanList = [];
                                        foreach ($p->mahasiswa as $pm) {
                                            if ($pm->mahasiswa && $pm->mahasiswa->angkatan && !in_array($pm->mahasiswa->angkatan, $angkatanList)) {
                                                $angkatanList[] = $pm->mahasiswa->angkatan;
                                            }
                                        }
                                    @endphp
                                    <div class="flex flex-wrap gap-1 justify-center">
                                        @foreach ($angkatanList as $angkatan)
                                            <span class="inline-block bg-blue-50 text-blue-700 text-xs font-bold px-2 py-1 border border-blue-200 rounded">
                                                {{ $angkatan }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                    {{ \Carbon\Carbon::parse($p->tgl_pengajuan)->locale('id')->translatedFormat('d F Y') }}
                                </td>
                                
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block border text-xs font-bold px-3 py-1 whitespace-nowrap {{ $warna }}">
                                        {{ strtoupper($textStatus) }}
                                    </span>
                                </td>
                                
                                {{-- KOLOM SURAT PERMOHONAN --}}
                                <td class="px-4 py-3 text-center">
                                    @if($p->suratPermohonan)
                                        <a href="{{ route('mahasiswa.pengajuan_magang.surat_permohonan', $p->id_pengajuan) }}" 
                                           class="bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-indigo-700 transition rounded">
                                             Lihat
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Menunggu BAAK</span>
                                    @endif
                                </td>
        
                                {{-- KOLOM SURAT BALASAN --}}
                                <td class="px-4 py-3 text-center">
                                    @if($p->ada_surat_balasan_kelompok)
                                        <a href="{{ route('mahasiswa.pengajuan_magang.upload_surat_balasan', $p->id_pengajuan) }}" 
                                           class="bg-purple-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-purple-700 transition rounded">
                                             Lihat
                                        </a>
                                    @elseif($p->sudah_ada_surat_permohonan)
                                        <a href="{{ route('mahasiswa.pengajuan_magang.upload_surat_balasan', $p->id_pengajuan) }}" 
                                           class="bg-yellow-500 text-white text-xs font-bold px-3 py-1.5 hover:bg-yellow-600 transition rounded">
                                             Upload
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic" title="Upload setelah Surat Permohonan dari BAAK tersedia">
                                            Menunggu Baak
                                        </span>
                                    @endif
                                </td>

                                {{-- KOLOM SURAT PENGANTAR --}}
                                <td class="px-4 py-3 text-center">
                                    @if($p->suratPengantar)
                                        <a href="{{ route('mahasiswa.lihat_surat_pengantar', $p->id_pengajuan) }}" 
                                           class="bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 hover:bg-indigo-700 transition rounded">
                                             Lihat
                                        </a>
                                    @elseif($ditolak)
                                        <span class="text-red-400 text-xs font-medium">Ditolak</span>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Menunggu BAAK</span>
                                    @endif
                                </td>

                                {{-- KOLOM AKSI --}}
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('mahasiswa.pengajuan_magang.detail', $p->id_pengajuan) }}" 
                                       class="text-blue-600 hover:text-blue-800 font-semibold text-sm hover:underline">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- ========================================== --}}
{{-- MODAL KONFIRMASI MODERN                    --}}
{{-- ========================================== --}}
<div id="modalBatalkan" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 transform scale-95 opacity-0 transition-all duration-300" id="modalBatalkanContent">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-500 text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Batalkan Pengajuan?</h3>
            <p class="text-sm text-gray-500 mb-6">Tindakan ini tidak dapat dikembalikan. Data pengajuan magang Anda akan ditandai sebagai dibatalkan.</p>
        </div>
        <div class="flex gap-3 justify-center">
            <button type="button" onclick="tutupModal()" class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition">
                Kembali
            </button>
            <button type="button" onclick="submitFormBatal()" class="px-5 py-2.5 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition shadow-md">
                Ya, Batalkan
            </button>
        </div>
    </div>
</div>

<script>
    let targetFormId = null;

    function tampilkanModal(formId) {
        targetFormId = formId;
        const modal = document.getElementById('modalBatalkan');
        const content = document.getElementById('modalBatalkanContent');
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Trigger animation
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function tutupModal() {
        const modal = document.getElementById('modalBatalkan');
        const content = document.getElementById('modalBatalkanContent');
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    function submitFormBatal() {
        if (targetFormId) {
            document.getElementById(targetFormId).submit();
        }
    }

    // Tutup modal jika klik backdrop luar
    document.getElementById('modalBatalkan').addEventListener('click', function(e) {
        if (e.target === this) {
            tutupModal();
        }
    });
</script>
@endsection