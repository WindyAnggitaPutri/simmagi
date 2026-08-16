@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-10 px-6">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-yellow-400">Persetujuan Dosen Visitasi</h1>
        <p class="text-gray-500 text-sm mt-1">Daftar penugasan dosen visitasi per program studi di bawah jurusan Anda</p>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('errors') && session('errors')->any())
        <div class="mb-6 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('errors')->first() }}
        </div>
    @endif

    {{-- ==================== TABEL PER PRODI ==================== --}}
    <div class="bg-white shadow-md border border-gray-200 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-yellow-500 text-white">
                    <tr>
                        <th class="p-4 text-center w-12">No</th>
                        <th class="p-4">Nama Prodi</th>
                        <th class="p-4 text-center w-36">Total Mahasiswa</th>
                        <th class="p-4 text-center w-48">Status</th>
                        <th class="p-4 text-center w-28">Aksi</th>
                        <th class="p-4 text-center w-36">SK Visitasi</th>
                        <th class="p-4 text-center w-36">Personal Visitasi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @if(empty($prodiData))
                        <tr>
                            <td colspan="7" class="p-10 text-center text-gray-400">Tidak ada data program studi.</td>
                        </tr>
                    @else
                        @php $no = 1; @endphp
                        @foreach ($prodiData as $data)
                            <tr class="border-b border-gray-100 hover:bg-yellow-50/50 transition">
                                <td class="p-4 text-center text-gray-500 font-medium">{{ $no++ }}</td>
                                
                                <td class="p-4">
                                    <div class="font-semibold text-gray-900">{{ $data['prodi']->nama_prodi }}</div>
                                    @if($data['total'] > 0)
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            {{ $data['total'] }} mahasiswa ditugaskan
                                        </div>
                                    @endif
                                </td>
                                
                                <td class="p-4 text-center">
                                    <span class="text-lg font-bold text-gray-800">{{ $data['total'] }}</span>
                                    <span class="text-xs text-gray-400 block">orang</span>
                                </td>
                                
                                <td class="p-4 text-center">
                                    @php
                                        $statusConfig = [
                                            'belum_ada'      => ['label' => 'Belum Ada Data',      'class' => 'bg-gray-100 text-gray-600'],
                                            'menunggu_kajur' => ['label' => 'Menunggu Persetujuan', 'class' => 'bg-yellow-100 text-yellow-800'],
                                            'diproses'       => ['label' => 'Sedang Diproses',      'class' => 'bg-blue-100 text-blue-800'],
                                            'selesai'        => ['label' => 'Selesai',               'class' => 'bg-green-100 text-green-800'],
                                            'ditolak'        => ['label' => 'Ditolak',               'class' => 'bg-red-100 text-red-800'],
                                        ];
                                        $cfg = $statusConfig[$data['status']] ?? ['label' => ucfirst(str_replace('_', ' ', $data['status'])), 'class' => 'bg-gray-100 text-gray-800'];
                                    @endphp
                                    <span class="inline-block px-3 py-1.5 {{ $cfg['class'] }} rounded-full text-xs font-bold">
                                        {{ $cfg['label'] }}
                                    </span>
                                    
                                    @if($data['total'] > 0 && $data['status'] !== 'selesai' && $data['status'] !== 'ditolak' && $data['status'] !== 'belum_ada')
                                        <div class="flex justify-center gap-2 mt-1.5">
                                            @if($data['menunggu_kajur'] > 0)
                                                <span class="text-[10px] px-1.5 py-0.5 bg-yellow-50 text-yellow-700 rounded">{{ $data['menunggu_kajur'] }} menunggu</span>
                                            @endif
                                            @if($data['diproses'] > 0)
                                                <span class="text-[10px] px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded">{{ $data['diproses'] }} diproses</span>
                                            @endif
                                            @if($data['selesai'] > 0)
                                                <span class="text-[10px] px-1.5 py-0.5 bg-green-50 text-green-700 rounded">{{ $data['selesai'] }} selesai</span>
                                            @endif
                                            @if($data['ditolak'] > 0)
                                                <span class="text-[10px] px-1.5 py-0.5 bg-red-50 text-red-700 rounded">{{ $data['ditolak'] }} ditolak</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                
                                <td class="p-4 text-center">
                                    @if($data['total'] > 0)
                                        <a href="{{ route('kajur.pembagian-dosen-visitasi.preview', $data['prodi']->id_prodi) }}" 
                                           class="inline-flex items-center gap-1 bg-green-50 text-green-700 hover:bg-green-100 text-xs font-semibold px-3 py-1.5 rounded-lg transition border border-green-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Detail
                                        </a>
                                    @else
                                        <span class="text-gray-300 text-xs">-</span>
                                    @endif
                                </td>
                                
                                {{-- ✅ KOLOM SK VISITASI — TOMBOL LIHAT SK --}}
                                <td class="p-4 text-center">
                                    @if($data['status'] === 'selesai' && $data['sk_pertama'])
                                        <a href="{{ route('kajur.dosen-visitasi.lihat_sk', $data['prodi']->id_prodi) }}?from={{ urlencode(url()->current()) }}" 
                                           class="inline-flex items-center gap-1 bg-green-500 text-white hover:bg-green-600 text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                            📄 Lihat SK
                                        </a>
                                    @elseif($data['status'] === 'selesai')
                                        <span class="text-xs text-yellow-600 font-medium">Menunggu upload BAUP</span>
                                    @elseif($data['status'] === 'ditolak')
                                        <span class="text-red-500 text-xs">Ditolak</span>
                                    @elseif(in_array($data['status'], ['diproses']))
                                        <span class="text-xs text-blue-500 font-medium">Diproses BAUP</span>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- KOLOM PERSONAL VISITASI --}}
                                <td class="p-4 text-center">
                                    <span class="text-gray-400 text-xs">-</span>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- LEGENDA STATUS --}}
    <div class="mt-4 flex flex-wrap gap-3 text-xs text-gray-500">
        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-yellow-400 inline-block"></span> Menunggu Persetujuan</span>
        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-400 inline-block"></span> Sedang Diproses</span>
        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-green-400 inline-block"></span> Selesai</span>
        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block"></span> Ditolak</span>
    </div>
</div>
@endsection