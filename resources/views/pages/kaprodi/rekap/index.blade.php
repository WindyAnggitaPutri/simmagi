@extends('layouts.app')

@section('title', 'Rekap Data Magang - SIMMAGI')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Rekap Data Magang</h1>
        <p class="text-gray-500 mt-1">Kelola dan unduh rekap data magang program studi</p>
    </div>

    <!-- Card Rekap Data -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        
        <!-- Header Card -->
        <div class="bg-gradient-to-r from-yellow-300 to-yellow-400 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-white font-bold text-lg">Pilih Data Rekap</h2>
                    <p class="text-yellow-100 text-sm">Klik tombol untuk mengunduh data rekap dalam format Word</p>
                </div>
            </div>
        </div>

        <!-- Body Card -->
        <div class="p-6">
            
            <!-- Button: Rekap Data Magang -->
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-100 hover:border-yellow-200 hover:bg-yellow-50/50 transition-all duration-200">
                <div class="flex items-center gap-4">
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Rekap Data Magang</h3>
                        <p class="text-sm text-gray-500">Data seluruh rekap magang mahasiswa program studi</p>
                    </div>
                </div>
                <a href="{{ route('kaprodi.rekap.download') }}" class="bg-yellow-400 hover:bg-yellow-500 text-white px-5 py-2.5 rounded-lg font-medium text-sm flex items-center gap-2 transition-colors duration-200 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download
                </a>
            </div>

        </div>
    </div>

</div>
@endsection