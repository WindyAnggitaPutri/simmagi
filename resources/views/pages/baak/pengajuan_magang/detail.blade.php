@extends('layouts.app')

@section('content')

{{-- TOMBOL AKSI --}}
<div class="no-print max-w-4xl mx-auto py-6 px-6 relative z-40">
    <div class="flex justify-between items-center">
        <button type="button" 
                onclick="window.location.href='{{ route('baak.pengajuan_magang.index') }}'" 
                class="text-yellow-600 font-semibold text-sm hover:underline cursor-pointer bg-transparent border-none p-0">
            ← Kembali ke Daftar Pengajuan
        </button>
        <div class="flex gap-3">
            @if ($pengajuan->surat_pengajuan)
                <!-- <a href="{{ asset('storage/surat_pengajuan/' . $pengajuan->surat_pengajuan) }}" target="_blank" 
                   class="border border-gray-300 text-sm px-4 py-2 hover:bg-gray-100 transition">
                    📎 File Upload Asli
                </a> -->
            @endif
            <!-- <button onclick="window.print()" class="bg-blue-600 text-white text-sm px-4 py-2 hover:bg-blue-700 transition">
                🖨️ Cetak / Simpan PDF
            </button> -->
            
            @if (!$approvalBaak)
                <button onclick="openModal()" class="bg-green-600 text-white text-sm px-4 py-2 hover:bg-green-700 transition">
                     Setujui / Tolak
                </button>
            @else
                <span class="text-sm px-4 py-2 rounded {{ $approvalBaak->status == 'disetujui' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300' }}">
                    {{ $approvalBaak->status == 'disetujui' ? ' DISETUJUI' : ' DITOLAK' }}
                </span>
            @endif
        </div>
    </div>
</div>

{{-- NOTIFIKASI SUKSES --}}
@if (session('success'))
    <div class="no-print max-w-4xl mx-auto px-6 mb-4">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    </div>
@endif

{{-- ERROR --}}
@if ($errors->any())
    <div class="no-print max-w-4xl mx-auto px-6 mb-4">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            @foreach ($errors->all() as $error)
                <p class="text-sm">{{ $error }}</p>
            @endforeach
        </div>
    </div>
@endif

{{-- AREA SURAT RESMI --}}
<div class="surat-container max-w-4xl mx-auto px-16 py-12 mb-10" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.6; color: #000; text-align: justify;">

    {{-- KOP SURAT --}}
    <div style="text-align: center; margin-bottom: 30px; position: relative; padding: 0 20px;">
        <img src="{{ asset('images/logo-PNC.png') }}" 
             alt="Logo Kiri" 
             style="position: absolute; top: 35%; left: 0; transform: translateY(-50%); width: 180px; height: 160px; object-fit: contain;">
        
        <div style="position: absolute; top: 50%; right: 0; transform: translateY(-50%); width: 90px; height: 90px; display: flex; align-items: center; justify-content: center;">
        </div>

        <div style="text-align: center; padding: 0 100px;">
            <p style="font-size: 12pt; font-weight: bold; margin: 0; text-transform: uppercase; letter-spacing: 1px;">
                Kementerian Pendidikan Tinggi, Sains, dan Teknologi
            </p>
            <h2 style="font-size: 16pt; font-weight: bold; margin: 5px 0 0 0; text-transform: uppercase; letter-spacing: 1.5px;">
                Jurusan Komputer dan Bisnis
            </h2>
        </div>

        <div style="border-bottom: 3px solid #000; padding-bottom: 10px; margin-top: 15px; text-align: center;">
            <p style="font-size: 10pt; margin: 2px 0; font-style: italic;">
                Jalan Dr. Soetomo No. 1, Sidakaya - Cilacap 53212 Jawa Tengah
            </p>
            <p style="font-size: 10pt; margin: 2px 0;">
                Telepon: (0282) 533329, Fax: (0282) 537992
            </p>
            <p style="font-size: 10pt; margin: 2px 0; font-weight: bold;">
                <a href="https://www.pnc.ac.id" target="_blank" style="color: blue;">www.pnc.ac.id</a>, Email: 
                <a href="mailto:sekretariat@pnc.ac.id" style="color: blue;">sekretariat@pnc.ac.id</a>
            </p>
        </div>
    </div>

    <div style="text-align: right; margin-bottom: 20px; font-size: 10pt;">
        FM.PM-PMI-C.01-R.0
    </div>
    <p style="margin-bottom: 20px;">
        <strong>Perihal</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Permohonan Ijin Magang Industri
    </p>

    <p style="margin-bottom: 25px;">
        Yth. Kordinator Magang Industri<br>
        Politeknik Negeri Cilacap<br>
        di – Cilacap
    </p>

    <p style="margin-bottom: 15px;">
        Dengan ini mohon dibuatkan surat ijin magang Industri atas nama mahasiswa dibawah ini :
    </p>

    <table style="border-collapse: collapse; width: 100%; margin-bottom: 30px; font-size: 11pt;">
        <thead>
            <tr style="background-color: #f9f9f9;">
                <th style="border: 1px solid #000; padding: 8px; width: 5%;">No</th>
                <th style="border: 1px solid #000; padding: 8px;">Nama Mahasiswa</th>
                <th style="border: 1px solid #000; padding: 8px; width: 15%;">NIM</th>
                <th style="border: 1px solid #000; padding: 8px; width: 25%;">Program Studi</th>
                <th style="border: 1px solid #000; padding: 8px; width: 10%;">IPK</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pengajuan->mahasiswa as $index => $pm)
            <tr>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #000; padding: 8px;">{{ $pm->mahasiswa->nama ?? '-' }}</td>
                <td style="border: 1px solid #000; padding: 8px;">{{ $pm->mahasiswa->npm ?? '-' }}</td>
                <td style="border: 1px solid #000; padding: 8px;">{{ $pm->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">{{ $pm->ipk ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- <div style="margin-bottom: 30px;">
        <p>1. Nama Perusahaan Tujuan &nbsp;&nbsp;&nbsp;&nbsp;: <strong>{{ $pengajuan->perusahaan->nama_perusahaan ?? '-' }}</strong></p>
        <p>2. Ditujukan kepada &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->ditujukan_kepada ?: '-' }}</p>
        <p>3. Alamat lengkap &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->perusahaan->alamat ?? '-' }}</p>
        <p>4. No. Telepon/e-mail &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->email_perusahaan ?: '-' }}</p>
        <p>5. Contact Person di Perusahaan &nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->contact_person ?? '-' }}</p>
        <p>6. No. HP CP di Perusahaan &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp: {{ $pengajuan->no_hp_cp ?? '-' }}</p>
        <p>7. Waktu Pelaksanaan Magang &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ 
            $pengajuan->tgl_mulai ? \Carbon\Carbon::parse($pengajuan->tgl_mulai)->locale('id')->translatedFormat('F Y') . ' - ' . \Carbon\Carbon::parse($pengajuan->tgl_selesai)->locale('id')->translatedFormat('F Y') : 'Belum ditentukan'
        }}</p>
        <p>8. Deskripsi Perusahaan &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->deskripsi_perusahaan ?? '-' }}</p>
        <p>9. Nama Perwakilan Kelompok &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->mahasiswa->first()->mahasiswa->nama ?? '-' }}</p>
        <p>10. No. HP Perwakilan Kelompok &nbsp;&nbsp;&nbsp;&nbsp;: {{ $pengajuan->mahasiswa->first()->mahasiswa->no_hp ?? '-' }}</p>
    </div> -->
        {{-- DETAIL INFO PERUSAHAAN --}}
    <table style="border-collapse: collapse; width: 100%; margin-bottom: 30px; font-size: 11pt;">
        <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">1.</td>
            <td style="width:25%; padding:3px 0; vertical-align:top;">Nama Perusahaan Tujuan</td>
            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>
            <td style="padding:3px 0; vertical-align:top;"><strong>{{ $pengajuan->perusahaan->nama_perusahaan ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">2.</td>
            <td style="width:25%; padding:3px 0; vertical-align:top;">Ditujukan Kepada</td>
            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>
            <td style="padding:3px 0; vertical-align:top;">{{ $pengajuan->ditujukan_kepada ?: '-' }}</td>
        </tr>
        <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">3.</td>
            <td style="width:25%; padding:3px 0; vertical-align:top;">Alamat Lengkap</td>
            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>
            <td style="padding:3px 0; vertical-align:top;">{{ $pengajuan->perusahaan->alamat ?? '-' }}</td>
        </tr>
        <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">4.</td>
            <td style="width:25%; padding:3px 0; vertical-align:top;">No. Telepon/e-mail</td>
            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>
            <td style="padding:3px 0; vertical-align:top;">{{ $pengajuan->email_perusahaan ?: '-' }}</td>
        </tr>
        <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">5.</td>
            <td style="width:25%; padding:3px 0; vertical-align:top;">Contact Person di Perusahaan</td>
            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>
            <td style="padding:3px 0; vertical-align:top;">{{ $pengajuan->contact_person ?? '-' }}</td>
        </tr>
        <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">6.</td>
            <td style="width:25%; padding:3px 0; vertical-align:top;">No. HP CP di Perusahaan</td>
            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>
            <td style="padding:3px 0; vertical-align:top;">{{ $pengajuan->no_hp_cp ?? '-' }}</td>
        </tr>
        <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">7.</td>
            <td style="width:25%; padding:3px 0; vertical-align:top;">Waktu Pelaksanaan Magang</td>
            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>
            <td style="padding:3px 0; vertical-align:top;">{{ 
                $pengajuan->tgl_mulai ? \Carbon\Carbon::parse($pengajuan->tgl_mulai)->locale('id')->translatedFormat('F Y') . ' - ' . \Carbon\Carbon::parse($pengajuan->tgl_selesai)->locale('id')->translatedFormat('F Y') : 'Belum ditentukan'
            }}</td>
        </tr>
        <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">8.</td>
            <td style="width:25%; padding:3px 0; vertical-align:top;">Deskripsi Perusahaan</td>
            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>
            <td style="padding:3px 0; vertical-align:top;">{{ $pengajuan->deskripsi_perusahaan ?? '-' }}</td>
        </tr>
        <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">9.</td>
            <td style="width:25%; padding:3px 0; vertical-align:top;">Nama Perwakilan Kelompok</td>
            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>
            <td style="padding:3px 0; vertical-align:top;">{{ $pengajuan->mahasiswa->first()->mahasiswa->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td style="width:4%; padding:3px 5px 3px 0; vertical-align:top; text-align:right;">10.</td>
            <td style="width:25%; padding:3px 0; vertical-align:top;">No. HP Perwakilan Kelompok</td>
            <td style="width:2%; padding:3px 2px; vertical-align:top; text-align:center;">:</td>
            <td style="padding:3px 0; vertical-align:top;">{{ $pengajuan->mahasiswa->first()->mahasiswa->no_hp ?? '-' }}</td>
        </tr>
    </table>

    {{-- CATATAN DITOLAK KAPRODI --}}
    @if ($approvalKaprodi && $approvalKaprodi->status == 'ditolak')
    <div style="margin-bottom: 20px; background-color: #F8D7DA; padding: 10px 15px; border-left: 4px solid #721C24;">
        <strong style="color: #721C24;">Ditolak oleh Kaprodi:</strong><br>
        <span style="color: #721C24;">{{ $approvalKaprodi->catatan ?: 'Tidak diberikan keterangan.' }}</span>
    </div>
    @endif

    {{-- CATATAN DIKEMBALIKAN ADMIN KAJUR --}}
    @if ($approvalAdminKajur && $approvalAdminKajur->status == 'ditolak')
    <div style="margin-bottom: 20px; background-color: #FFF3CD; padding: 10px 15px; border-left: 4px solid #856404;">
        <strong style="color: #856404;">Dikembalikan oleh Admin Kajur:</strong><br>
        <span style="color: #856404;">{{ $approvalAdminKajur->catatan ?: 'Tidak diberikan keterangan.' }}</span>
    </div>
    @endif

    {{-- CATATAN DITOLAK BAAK --}}
    @if ($approvalBaak && $approvalBaak->status == 'ditolak')
    <div style="margin-bottom: 20px; background-color: #F8D7DA; padding: 10px 15px; border-left: 4px solid #721C24;">
        <strong style="color: #721C24;">Ditolak oleh BAAK:</strong><br>
        <span style="color: #721C24;">{{ $approvalBaak->catatan ?: 'Tidak diberikan keterangan.' }}</span>
    </div>
    @endif

    <p style="margin-bottom: 10px;">
        Demikian permohonan ini, atas perhatian dan kerjasamanya, kami ucapkan terima kasih.
    </p>

    {{-- 2 KOLOM TTD --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 0 20px; margin-top: 20px; margin-bottom: 40px;">
        
        {{-- KOLOM KIRI: TTD KAPRODI --}}
        <div style="text-align: center; width: 40%;">
            <p style="margin: 0;">Menyetujui,<br>Ketua Program Studi</p>
            <div style="height: 20px;"></div>
            
            @if($approvalKaprodi && $approvalKaprodi->status == 'disetujui' && $approvalKaprodi->dosen && $approvalKaprodi->dosen->ttd_digital)
                <div style="display: flex; justify-content: center; margin-bottom: 5px;">
                    <img src="{{ asset('storage/ttd_digital/' . $approvalKaprodi->dosen->ttd_digital) }}" 
                         alt="TTD Kaprodi" class="ttd-bersih"
                         style="width: 200px; height: auto; max-height: 100px; object-fit: contain;">
                </div>
                <p style="margin-top: 5px; font-size: 10pt;">
                    <strong>{{ $approvalKaprodi->dosen->nama }}</strong><br>
                    NIDN. {{ $approvalKaprodi->dosen->nidn }}
                </p>
            @else
                <div style="height: 80px;"></div>
                <div style="border-bottom: 1px solid #000; width: 200px; margin: 0 auto;"></div>
                <p style="margin-top: 5px; font-size: 10pt;">NIDN. ................................</p>
            @endif
        </div>

        {{-- KOLOM KANAN: TTD PERWAKILAN KELOMPOK --}}
        <div style="width: 40%;">
            <div style="text-align: center;">
                Cilacap, {{ \Carbon\Carbon::parse($pengajuan->tgl_pengajuan)->locale('id')->translatedFormat('d F Y') }}
            </div>
            <div style="text-align: center;">
                <p style="margin: 0;">Perwakilan Kelompok</p>
                <div style="height: 20px;"></div>
                @if($pengajuan->mahasiswa->first()->mahasiswa->ttd_digital)
                    <div style="display: flex; justify-content: center; margin-bottom: 5px;">
                        <img src="{{ asset('storage/ttd_digital/' . $pengajuan->mahasiswa->first()->mahasiswa->ttd_digital) }}" 
                             alt="TTD Digital" class="ttd-bersih"
                             style="width: 200px; height: auto; max-height: 100px; object-fit: contain;">
                    </div>
                @else
                    <div style="height: 80px;"></div>
                    <div style="border-bottom: 1px solid #000; width: 200px; margin: 0 auto;"></div>
                @endif
                <p style="margin-top: 5px; font-size: 10pt;">
                    {{ $pengajuan->mahasiswa->first()->mahasiswa->nama ?? '-' }}<br>
                    NIM. {{ $pengajuan->mahasiswa->first()->mahasiswa->npm ?? '-' }}
                </p>
            </div>
        </div>
    </div>

</div>

{{-- LAMPIRAN SURAT IZIN ORANG TUA --}}
<div class="no-print">
    @foreach ($pengajuan->mahasiswa as $pm)
        @if ($pm->surat_ortu)
            @php
                $extension = pathinfo($pm->surat_ortu, PATHINFO_EXTENSION);
                $filePath = asset('storage/surat_ortu/' . $pm->surat_ortu);
            @endphp
            <div class="surat-container max-w-4xl mx-auto px-16 py-12 mb-10" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; color: #000;">
                <h3 style="text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 5px;">
                    LAMPIRAN SURAT IZIN ORANG TUA
                </h3>
                <p style="text-align: center; font-size: 11pt; margin-bottom: 20px;">
                    {{ $pm->mahasiswa->nama ?? '-' }} ({{ $pm->mahasiswa->npm ?? '-' }})
                </p>
                <div style="border-top: 1px solid #000; margin-bottom: 20px;"></div>
                @if (strtolower($extension) == 'pdf')
                    <embed src="{{ $filePath }}#toolbar=0&navpanes=0&scrollbar=0" 
                           type="application/pdf" style="width: 100%; height: 900px; border: none;">
                @else
                    <img src="{{ $filePath }}" alt="Surat Izin Orang Tua" style="width: 100%; height: auto; display: block;">
                @endif
            </div>
        @endif
    @endforeach
</div>

{{-- MODAL KONFIRMASI BAAK --}}
<div id="modalKonfirmasi" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <!-- <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg> -->
                Persetujuan Pengajuan Magang
            </h3>
            <p class="text-sm text-gray-500 mt-1">Sebagai BAAK, Anda menyetujui atau menolak berkas pengajuan ini.</p>
        </div>

        <form id="formApproval" action="{{ route('baak.pengajuan_magang.proses_approval', $pengajuan->id_pengajuan) }}" method="POST">
            @csrf
            <div class="p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Pilih Aksi:</label>
                    <div class="grid grid-cols-1 gap-3">
                        
                        {{-- SETUJUI --}}
                        <label class="cursor-pointer">
                            <input type="radio" name="status_approval" value="disetujui" class="peer hidden" required>
                            <div class="border-2 border-gray-200 rounded-lg p-4 flex items-center gap-4 peer-checked:border-green-500 peer-checked:bg-green-50 transition">
                                <!-- <div class="flex-shrink-0">
                                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div> -->
                                <div>
                                    <span class="font-bold text-green-700 text-base">Setujui Pengajuan</span>
                                    <p class="text-xs text-gray-500 mt-0.5">Berkas dinyatakan layak, lanjut ke tahap upload Surat Permohonan</p>
                                </div>
                            </div>
                        </label>

                        {{-- TOLAK --}}
                        <label class="cursor-pointer">
                            <input type="radio" name="status_approval" value="ditolak" class="peer hidden" required>
                            <div class="border-2 border-gray-200 rounded-lg p-4 flex items-center gap-4 peer-checked:border-red-500 peer-checked:bg-red-50 transition">
                                <!-- <div class="flex-shrink-0">
                                    <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div> -->
                                <div>
                                    <span class="font-bold text-red-700 text-base">Tolak Pengajuan</span>
                                    <p class="text-xs text-gray-500 mt-0.5">Berkas tidak layak / tidak memenuhi persyaratan</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="catatan_approval" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan <span class="text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <textarea id="catatan_approval" name="catatan_approval" rows="3" 
                              class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                              placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                </div>
            </div>

            <div class="p-6 border-t border-gray-200 flex gap-3">
                <button type="button" onclick="closeModal()" class="flex-1 border border-gray-300 text-gray-700 font-semibold py-2.5 px-4 rounded-lg hover:bg-gray-50 transition">
                    ← Batal
                </button>
                <button type="submit" id="btnSubmit" class="flex-1 bg-green-600 text-white font-semibold py-2.5 px-4 rounded-lg hover:bg-green-700 transition">
                    Kirim
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

<style>
    .surat-container { border: 1px solid #000 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .ttd-bersih { filter: grayscale(100%) contrast(500%) brightness(150%); }

    @media print {
        nav, footer, .no-print, .sidebar, .navbar, header { display: none !important; }
        main { padding: 0 !important; margin: 0 !important; }
        .surat-container { border: 1px solid #000 !important; box-shadow: none !important; padding: 20mm 25mm !important; max-width: 100% !important; margin: 0 !important; }
        .ttd-bersih { filter: grayscale(100%) contrast(500%) brightness(150%) !important; }
        @page { size: A4; margin: 0; }
    }
</style>

<script>
    function openModal() {
        document.getElementById('modalKonfirmasi').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        document.getElementById('modalKonfirmasi').classList.add('hidden');
        document.body.style.overflow = '';
        document.getElementById('formApproval').reset();
    }
    document.getElementById('modalKonfirmasi').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });

    // Ubah warna tombol submit sesuai pilihan
    document.querySelectorAll('input[name="status_approval"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var btn = document.getElementById('btnSubmit');
            if (this.value === 'disetujui') {
                btn.className = 'flex-1 bg-green-600 text-white font-semibold py-2.5 px-4 rounded-lg hover:bg-green-700 transition';
                btn.textContent = '✅ Setujui';
            } else {
                btn.className = 'flex-1 bg-red-600 text-white font-semibold py-2.5 px-4 rounded-lg hover:bg-red-700 transition';
                btn.textContent = '❌ Tolak';
            }
        });
    });
</script>