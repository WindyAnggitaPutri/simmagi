@extends('layouts.admin')

@section('title', 'Detail Angket Perusahaan')
@section('page-title', 'Detail Angket Perusahaan')

@section('content')
@php
    $skalaAngket = [
        1 => 'Kurang', 2 => 'Cukup', 3 => 'Baik', 4 => 'Sangat Baik',
    ];
    $warnaAngket = [
        1 => 'bg-red-100 text-red-700 border-red-200',
        2 => 'bg-yellow-100 text-yellow-700 border-yellow-200',
        3 => 'bg-blue-100 text-blue-700 border-blue-200',
        4 => 'bg-green-100 text-green-700 border-green-200',
    ];
    $abjad = ['a', 'b', 'c', 'd', 'e'];

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
@endphp

{{-- TOMBOL AKSI --}}
<div class="no-print max-w-5xl mx-auto py-6 px-4">
    <a href="{{ route('admin.penilaian-perusahaan.index') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-yellow-700 font-medium text-sm transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Kembali ke Daftar Nilai Perusahaan
    </a>
</div>

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
        <span class="text-sm font-semibold text-yellow-800"> Catatan:</span>
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
@endsection