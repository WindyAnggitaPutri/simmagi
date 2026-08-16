@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;

    $skalaAngket = [
        1 => 'Kurang', 2 => 'Cukup', 3 => 'Baik', 4 => 'Sangat Baik',
    ];

    $warnaAngket = [
        1 => 'bg-red-100 text-red-700 border-red-200',
        2 => 'bg-yellow-100 text-yellow-700 border-yellow-200',
        3 => 'bg-blue-100 text-blue-700 border-blue-200',
        4 => 'bg-green-100 text-green-700 border-green-200',
    ];

    $angketGroups = [
        ['number' => '1', 'title' => 'Integritas dan Motivasi', 'items' => [
           'integritas_motivasi' => 'Bagaimana penilaian Bapak/Ibu terhadap integritas dan motivasi mahasiswa Politeknik Negeri Semarang selama melaksanakan magang di perusahaan Bapak/Ibu?'
        ]],
        ['number' => '2', 'title' => 'Etika dan Sopan Santun', 'items' => [
            'etika_sopan_santun' => 'Bagaimana penilaian Bapak/Ibu terhadap etika dan sopan santun mahasiswa Politeknik Negeri Semarang selama melaksanakan magang di perusahaan Bapak/Ibu?'
        ]],
        ['number' => '3', 'title' => 'Kedisiplinan', 'items' => [
            'kedisiplinan' => 'Bagaimana penilaian Bapak/Ibu terhadap kedisiplinan mahasiswa Politeknik Negeri Semarang selama melaksanakan magang di perusahaan Bapak/Ibu?'
        ]],
        ['number' => '4', 'title' => 'Kompetensi Keahlian', 'items' => [
            'kompetensi_keahlian' => 'Bagaimana penilaian Bapak/Ibu terhadap kompetensi/keahlian mahasiswa Politeknik Negeri Semarang selama melaksanakan magang di perusahaan Bapak/Ibu?'
        ]],
        ['number' => '5', 'title' => 'Respon dan Kecepatan Kerja', 'items' => [
            'respon_kecepatan_kerja' => 'Bagaimana penilaian Bapak/Ibu terhadap respon dan kecepatan kerja mahasiswa Politeknik Negeri Semarang selama melaksanakan magang di perusahaan Bapak/Ibu?'
        ]],
        ['number' => '6', 'title' => 'Kemampuan Adaptasi dan Kerjasama', 'items' => [
            'adaptasi_komunikasi' => 'Bagaimana penilaian Bapak/Ibu terhadap adaptasi dan komunikasi mahasiswa Politeknik Negeri Semarang selama melaksanakan magang di perusahaan Bapak/Ibu?',
            'kerjasama_tim' => 'Bagaimana penilaian Bapak/Ibu terhadap kerjasama tim mahasiswa Politeknik Negeri Semarang selama melaksanakan magang di perusahaan Bapak/Ibu?'
        ]],
        ['number' => '7', 'title' => 'Pengembangan Diri dan Bahasa', 'items' => [
            'pengembangan_diri' => 'Bagaimana penilaian Bapak/Ibu terhadap pengembangan diri mahasiswa Politeknik Negeri Semarang selama melaksanakan magang di perusahaan Bapak/Ibu?',
            'kemampuan_bahasa_inggris' => 'Bagaimana penilaian Bapak/Ibu terhadap kemampuan bahasa Inggris mahasiswa Politeknik Negeri Semarang selama melaksanakan magang di perusahaan Bapak/Ibu?'
        ]],
        ['number' => '8', 'title' => 'Kemampuan Teknologi Informasi', 'items' => [
            'kemampuan_teknologi_informasi' => 'Bagaimana penilaian Bapak/Ibu terhadap kemampuan teknologi informasi mahasiswa Politeknik Negeri Semarang selama melaksanakan magang di perusahaan Bapak/Ibu?'
        ]]
    ];

    $pertanyaanAngket = [];
    foreach ($angketGroups as $group) {
        foreach ($group['items'] as $key => $label) {
            $pertanyaanAngket[$key] = $label;
        }
    }

    $penilaianGroups = [
        ['number' => '1', 'title' => 'Kedisiplinan', 'items' => ['kedisiplinan' => 'Kedisiplinan']],
        ['number' => '2', 'title' => 'Kemampuan Beradaptasi dengan Lingkungan', 'items' => ['adaptasi' => 'Kemampuan Beradaptasi dengan Lingkungan']],
        ['number' => '3', 'title' => 'Tanggung Jawab terhadap Tugas', 'items' => ['tanggung_jawab' => 'Tanggung Jawab terhadap Tugas']],
        ['number' => '4', 'title' => 'Kemandirian', 'items' => ['kemandirian' => 'Kemandirian']],
        ['number' => '5', 'title' => 'Inisiatif dan Kreativitas', 'items' => ['inisiatif_kreativitas' => 'Inisiatif dan Kreativitas']],
        ['number' => '6', 'title' => 'Keterampilan dalam:', 'items' => [
            'kesesuaian_instruksi' => 'Kesesuaian dalam Instruksi',
            'kualitas_pekerjaan' => 'Kualitas Hasil Pekerjaan',
            'ketepatan_waktu' => 'Ketepatan Waktu',
            'problem_solving' => 'Kemampuan Memecahkan Masalah'
        ]],
        ['number' => '7', 'title' => 'Komunikasi:', 'items' => [
            'teamwork' => 'Kerjasama dengan Teamwork/Kelompok',
            'hubungan_atasan' => 'Hubungan dengan Atasan',
            'hubungan_rekan' => 'Hubungan dengan Rekan Kerja',
            'hubungan_relasi' => 'Hubungan dengan Relasi'
        ]],
        ['number' => '8', 'title' => 'Sikap Potensial:', 'items' => [
            'sikap_kerja' => 'Sikap Menghadapi Pekerjaan',
            'disiplin_kerja' => 'Disiplin Kerja',
            'loyalitas' => 'Loyalitas',
            'motivasi' => 'Semangat dan Motivasi Kerja',
            'penampilan' => 'Penampilan'
        ]]
    ];

    $abjad = ['a', 'b', 'c', 'd', 'e'];

    // ✅ Hanya inisialisasi variabel penilaian jika type-nya BUKAN angket
    if ($token->type != 'angket') {
        $rataRata = (float) $penilaian->rata_rata;
        $keterangans = [
            ['min' => 86, 'max' => 100, 'label' => 'Sangat Baik', 'huruf' => 'A'],
            ['min' => 76, 'max' => 85,  'label' => 'Baik',         'huruf' => 'B'],
            ['min' => 66, 'max' => 75,  'label' => 'Cukup',       'huruf' => 'C'],
            ['min' => 56, 'max' => 65,  'label' => 'Kurang',       'huruf' => 'D'],
            ['min' => 0,  'max' => 55,  'label' => 'Sangat Kurang','huruf' => 'E'],
        ];
        $namaPL = $penilaian->nama_pengisi ?? '-';
        $jabatanPL = $penilaian->jabatan_pengisi ?? '-';
        $ttdPLUrl = $penilaian->ttd_digital ? Storage::url($penilaian->ttd_digital) : null;
    }
@endphp

{{-- TOMBOL AKSI --}}
<div class="no-print max-w-[210mm] mx-auto py-6 px-4">
    <div class="flex justify-between items-center">
        <a href="{{ route('kaprodi.token-perusahaan.index') }}" class="inline-flex items-center gap-2 text-yellow-500 hover:text-yellow-700 font-medium text-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke Daftar Token
        </a>
        @if($token->type != 'angket')
        <button onclick="downloadPDF()" class="bg-blue-600 text-white text-sm px-4 py-2 hover:bg-blue-700 transition rounded-lg">
             Download PDF
        </button>
        @endif
    </div>
</div>

@if($token->type == 'angket')
{{-- ANGKET --}}
<div class="no-print bg-white rounded-xl shadow-sm border border-gray-200 p-8 mb-6 max-w-5xl mx-auto">
    <h2 class="text-xl font-bold text-gray-800 mb-1">Detail Penilaian Angket dari Perusahaan</h2>
    <p class="text-gray-500 text-sm mb-6">Data yang diisi oleh pihak perusahaan melalui link token.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 mb-6 text-sm">
        <div><strong>Nama Perusahaan:</strong> {{ $token->perusahaan->nama_perusahaan }}</div>
        <div><strong>Diisi Oleh:</strong> {{ $angket->nama_pengisi }}</div>
        <div><strong>Jabatan:</strong> {{ $angket->jabatan_pengisi }}</div>
        <div><strong>Tanggal Pengisian:</strong> {{ \Carbon\Carbon::parse($angket->created_at)->format('d F Y, H:i') }}</div>
    </div>

    <div class="flex flex-wrap gap-2 mb-6">
        @foreach($skalaAngket as $angka => $label)
        <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-1 rounded-full border {{ $warnaAngket[$angka] }}">
            <span class="font-bold">{{ $angka }}</span> = {{ $label }}
        </span>
        @endforeach
    </div>

    <hr class="mb-6">

    <h3 class="font-bold text-gray-700 mb-4">Skala Penilaian Angket Perusahaan</h3>
    
    <div class="space-y-4">
        @foreach($angketGroups as $group)
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200 font-semibold text-gray-800">
                {{ $group['number'] }}. {{ $group['title'] }}
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($group['items'] as $key => $pertanyaan)
                @php $nilai = $angket->$key; @endphp
                <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50/50 transition">
                    <div class="text-sm text-gray-700 leading-relaxed flex-1 pr-4">
                        @if(count($group['items']) > 1)
                            {{ $abjad[$loop->index] }}. {{ $pertanyaan }}
                        @else
                            {{ $pertanyaan }}
                        @endif
                    </div>
                    <div class="flex-shrink-0">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold border {{ $warnaAngket[$nilai] ?? 'bg-gray-100 text-gray-500 border-gray-200' }}">
                            {{ $skalaAngket[$nilai] ?? '-' }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    @if($angket->catatan)
    <div class="mt-5 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <span class="text-sm font-semibold text-yellow-800">📝 Catatan:</span>
        <p class="text-sm text-yellow-700 mt-1 leading-relaxed">{{ $angket->catatan }}</p>
    </div>
    @endif

    @php
        $nilaiArr = [];
        foreach($pertanyaanAngket as $key => $p) { $nilaiArr[] = $angket->$key; }
        $totalAngket = array_sum($nilaiArr);
        $rataAngket = round($totalAngket / count($nilaiArr), 2);
        $tertinggi = max($nilaiArr);
        $terendah = min($nilaiArr);
    @endphp
    <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 text-center">
            <div class="text-xs text-blue-600 font-medium mb-1">Total Skor</div>
            <div class="text-2xl font-bold text-blue-800">{{ $totalAngket }} <span class="text-xs font-normal text-blue-400">/ 40</span></div>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-lg p-4 text-center">
            <div class="text-xs text-green-600 font-medium mb-1">Rata-rata</div>
            <div class="text-2xl font-bold text-green-800">{{ $rataAngket }}</div>
        </div>
        <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4 text-center">
            <div class="text-xs text-emerald-600 font-medium mb-1">Tertinggi</div>
            <div class="text-lg font-bold text-emerald-800">{{ $skalaAngket[$tertinggi] ?? '-' }}</div>
        </div>
        <div class="bg-orange-50 border border-orange-100 rounded-lg p-4 text-center">
            <div class="text-xs text-orange-600 font-medium mb-1">Terendah</div>
            <div class="text-lg font-bold text-orange-800">{{ $skalaAngket[$terendah] ?? '-' }}</div>
        </div>
    </div>
</div>

@else
{{-- PENILAIAN --}}
<div class="no-print max-w-[210mm] mx-auto mb-4"></div>

<div id="area-cetak" style="font-family:'Times New Roman',Times,serif;font-size:11pt;color:#000;line-height:1.4;background:#fff;max-width:210mm;margin:0 auto 24px;padding:12mm 18mm;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,.08);">

    {{-- KOP SURAT --}}
    <div style="border-bottom:3px double #000;padding-bottom:6px;margin-bottom:10px;">
        <table style="width:100%;border-collapse:collapse;">
            <tr>
                <td style="width:120px;vertical-align:middle;padding-right:10px;">
                    <img src="{{ asset('images/Logo-PNC.png') }}" style="width:270px;height:auto;" alt="Logo PNC">
                </td>
                <td style="vertical-align:middle;text-align:center;">
                    <p style="font-size:14pt;font-weight:bold;letter-spacing:2px;margin:0;">POLITEKNIK NEGERI SEMARANG</p>
                    <p style="font-size:11pt;font-weight:bold;margin:1px 0;">JURUSAN TEKNIK INFORMATIKA</p>
                    <p style="font-size:8pt;margin:0;">Jl. Prof. Sudharto, Tembalang, Semarang 50275</p>
                    <p style="font-size:8pt;margin:0;">Telp: (024) 7473416, Fax: (024) 7460390</p>
                    <p style="font-size:8pt;margin:0;">Website: www.polines.ac.id, Email: info@polines.ac.id</p>
                </td>
            </tr>
        </table>
    </div>

    <div style="text-align:center;margin:8px 0 10px;">
        <h2 style="font-size:12pt;font-weight:bold;text-decoration:underline;margin:0;">DAFTAR PENILAIAN MAGANG INDUSTRI</h2>
    </div>

    <table style="margin-bottom:8px;">
        <tr>
            <td style="padding:1px 0;vertical-align:top;width:130px;font-size:11pt;">Nama</td>
            <td style="padding:1px 0;vertical-align:top;font-size:11pt;">: {{ $penilaian->mahasiswa->nama ?? $token->mahasiswa->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding:1px 0;vertical-align:top;font-size:11pt;">NIM</td>
            <td style="padding:1px 0;vertical-align:top;font-size:11pt;">: {{ $penilaian->mahasiswa->npm ?? $token->mahasiswa->npm ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding:1px 0;vertical-align:top;font-size:11pt;">Program Studi</td>
            <td style="padding:1px 0;vertical-align:top;font-size:11pt;">: {{ $token->prodi->nama_prodi ?? ($token->mahasiswa->prodi->nama_prodi ?? '-') }}</td>
        </tr>
        <tr>
            <td style="padding:1px 0;vertical-align:top;font-size:11pt;">Perusahaan / Instansi</td>
            <td style="padding:1px 0;vertical-align:top;font-size:11pt;">: {{ $token->perusahaan->nama_perusahaan }}</td>
        </tr>
    </table>

    <table style="width:100%;border-collapse:collapse;font-size:10pt;margin-bottom:4px;">
        <thead>
            <tr>
                <th style="border:1px solid #000;padding:3px 6px;background:#f5f5f5;width:25px;text-align:center;">No</th>
                <th style="border:1px solid #000;padding:3px 6px;background:#f5f5f5;text-align:left;">Komponen yang Dinilai</th>
                <th style="border:1px solid #000;padding:3px 6px;background:#f5f5f5;width:55px;text-align:center;">Nilai</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penilaianGroups as $group)
                @if(count($group['items']) == 1)
                    @foreach($group['items'] as $field => $label)
                    <tr>
                        <td style="border:1px solid #000;padding:2px 6px;text-align:center;">{{ $group['number'] }}</td>
                        <td style="border:1px solid #000;padding:2px 6px;">{{ $label }}</td>
                        <td style="border:1px solid #000;padding:2px 6px;text-align:center;">{{ $penilaian->$field }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td style="border:1px solid #000;padding:2px 6px;text-align:center;">{{ $group['number'] }}</td>
                        <td style="border:1px solid #000;padding:2px 6px;">{{ $group['title'] }}</td>
                        <td style="border:1px solid #000;padding:2px 6px;text-align:center;"></td>
                    </tr>
                    @foreach($group['items'] as $field => $label)
                    <tr>
                        <td style="border:1px solid #000;padding:2px 6px;text-align:center;">{{ $abjad[$loop->index] }}</td>
                        <td style="border:1px solid #000;padding:2px 6px;padding-left:25px;">{{ $label }}</td>
                        <td style="border:1px solid #000;padding:2px 6px;text-align:center;">{{ $penilaian->$field }}</td>
                    </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight:bold;">
                <td style="border:1px solid #000;padding:3px 6px;text-align:center;" colspan="2">Total Nilai</td>
                <td style="border:1px solid #000;padding:3px 6px;text-align:center;">{{ $penilaian->total_nilai }}</td>
            </tr>
            <tr style="font-weight:bold;">
                <td style="border:1px solid #000;padding:3px 6px;text-align:center;" colspan="2">Nilai Rata-rata</td>
                <td style="border:1px solid #000;padding:3px 6px;text-align:center;">{{ number_format($penilaian->rata_rata, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($penilaian->catatan)
    <p style="margin-top:5px;font-size:10pt;"><strong>Catatan:</strong> {{ $penilaian->catatan }}</p>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-top:12px;">

        <div style="width:55%;">
            <table style="width:100%;border-collapse:collapse;font-size:9pt;">
                <thead>
                    <tr>
                        <th style="border:1px solid #000;padding:2px 5px;background:#f5f5f5;text-align:center;width:30px;">No</th>
                        <th style="border:1px solid #000;padding:2px 5px;background:#f5f5f5;text-align:center;width:65px;">Rentang Nilai</th>
                        <th style="border:1px solid #000;padding:2px 5px;background:#f5f5f5;text-align:left;">Keterangan</th>
                        <th style="border:1px solid #000;padding:2px 5px;background:#f5f5f5;text-align:center;width:25px;">Huruf</th>
                    </tr>
                </thead>
                <tbody>
                    @php $ketNo = 1; @endphp
                    @foreach($keterangans as $ket)
                    @php $isActive = ($rataRata >= $ket['min'] && $rataRata <= $ket['max']); @endphp
                    <tr style="{{ $isActive ? 'font-weight:bold;background:#f0fdf4;' : '' }}">
                        <td style="border:1px solid #000;padding:2px 5px;text-align:center;">{{ $ketNo++ }}</td>
                        <td style="border:1px solid #000;padding:2px 5px;text-align:center;">{{ $ket['min'] }} - {{ $ket['max'] }}</td>
                        <td style="border:1px solid #000;padding:2px 5px;">{{ $ket['label'] }}</td>
                        <td style="border:1px solid #000;padding:2px 5px;text-align:center;">{{ $ket['huruf'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="width:38%;text-align:center;">
            <div style="font-size:10pt;">Semarang, {{ \Carbon\Carbon::parse($penilaian->created_at)->format('d F Y') }}</div>
            <div style="margin-top:3px;font-size:10pt;">Pembimbing Lapangan,</div>
            <div style="height:10px;"></div>
            @if($ttdPLUrl)
                <div style="display:flex;justify-content:center;margin-bottom:3px;">
                    <img src="{{ $ttdPLUrl }}" alt="TTD PL" class="ttd-bersih" style="width:180px;height:auto;max-height:85px;object-fit:contain;">
                </div>
            @else
                <div style="height:70px;"></div>
            @endif
            <div style="border-bottom:1px solid #000;width:180px;margin:0 auto;"></div>
            <div style="margin-top:3px;font-size:9pt;">
                <strong>{{ $namaPL }}</strong><br>
                {{ $jabatanPL }}
            </div>
        </div>
    </div>
</div>
@endif

@endsection

<style>
    .ttd-bersih { filter: grayscale(100%) contrast(500%) brightness(150%); }
    @page { size: A4; margin: 0; }
    @media print {
        .no-print { display: none !important; }
        html, body { margin: 0; padding: 0; background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        #area-cetak { border: none !important; box-shadow: none !important; max-width: 100% !important; margin: 0 !important; padding: 10mm 15mm !important; }
        .ttd-bersih { filter: grayscale(100%) contrast(500%) brightness(150%) !important; }
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function bersihkanGambarTTD(imgElement) {
    return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = imgElement.naturalWidth;
        canvas.height = imgElement.naturalHeight;
        const img = new Image();
        img.crossOrigin = 'Anonymous';
        img.src = imgElement.src;
        img.onload = function() {
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const data = imageData.data;
            for (let i = 0; i < data.length; i += 4) {
                const r = data[i], g = data[i + 1], b = data[i + 2];
                if (r > 150 && g > 150 && b > 150) { data[i] = 255; data[i+1] = 255; data[i+2] = 255; }
                else { data[i] = 0; data[i+1] = 0; data[i+2] = 0; }
            }
            ctx.putImageData(imageData, 0, 0);
            resolve(canvas.toDataURL('image/png'));
        };
        img.onerror = function() { resolve(imgElement.src); };
    });
}

async function downloadPDF() {
    const element = document.getElementById('area-cetak');
    if (!element) { alert('Area cetak tidak ditemukan.'); return; }
    const orig = { border: element.style.border, boxShadow: element.style.boxShadow, padding: element.style.padding, margin: element.style.margin, maxWidth: element.style.maxWidth };
    const ttdImages = element.querySelectorAll('.ttd-bersih');
    const origSrcs = [], origFilters = [];
    element.style.border = 'none'; element.style.boxShadow = 'none'; element.style.padding = '10mm 15mm'; element.style.margin = '0'; element.style.maxWidth = '100%';
    for (let i = 0; i < ttdImages.length; i++) {
        origSrcs[i] = ttdImages[i].src; origFilters[i] = ttdImages[i].style.filter;
        ttdImages[i].src = await bersihkanGambarTTD(ttdImages[i]); ttdImages[i].style.filter = 'none';
    }
    await new Promise(r => setTimeout(r, 500));
    const fullHeight = element.scrollHeight;
    html2pdf().set({
        margin: [15, 5, 3, 5],
        filename: 'Penilaian_Magang_{{ $penilaian->mahasiswa->npm ?? "mahasiswa" }}.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, scrollY: 0, windowHeight: fullHeight, height: fullHeight },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak: { mode: ['css', 'legacy'] }
    }).from(element).save().then(() => {
        Object.assign(element.style, orig);
        for (let i = 0; i < ttdImages.length; i++) { ttdImages[i].src = origSrcs[i]; ttdImages[i].style.filter = origFilters[i]; }
    });
}
</script>