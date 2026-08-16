<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIMMAGI - Admin')</title>
    {{-- Memanggil file CSS Tailwind dari internet lewat CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-800 antialiased h-screen flex overflow-hidden">

    {{-- Memanggil file sidebar khusus admin dari folder component --}}
    @include('component.sidebar_admin')

    {{-- Bagian utama halaman tempat konten akan ditampilkan --}}
    <main class="flex-1 overflow-y-auto focus:outline-none relative bg-gray-50">
        
    {{-- Bagian header atas yang berisi judul halaman dan info admin --}}
    <header class="bg-white border-b border-gray-100 h-16 flex items-center justify-between px-8 sticky top-0 z-10 shadow-sm">
        {{-- Menampilkan judul halaman yang dikirim dari file lain --}}
        <h2 class="text-xl font-bold text-gray-800">@yield('page-title', 'Dashboard Overview')</h2>
        
        <div class="flex items-center gap-3">
            {{-- Menampilkan nama pengguna yang sedang login --}}
            <p class="text-sm font-bold text-gray-800 hidden sm:block">Super Admin</p>
            
            {{-- Cek apakah admin punya foto profil, jika ada maka ditampilkan --}}
            @if(auth()->user()->foto && file_exists(public_path('storage/' . auth()->user()->foto)))
                <img src="{{ asset('storage/' . auth()->user()->foto) }}" 
                     class="w-10 h-10 rounded-full border-2 border-[#ece75f] object-cover" 
                     alt="Super Admin">
            @else
                {{-- Jika tidak punya foto, maka tampilkan gambar bawaan berupa ikon orang --}}
                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
            @endif
        </div>
    </header>

        {{-- Tempat untuk menampilkan isi halaman utama --}}
        <div class="p-8">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
<script>
(function () {
    var sidebar = document.querySelector('.sidebar');
    var content = document.querySelector('.main-content');
    if (!sidebar || !content) return;

    // Fungsi untuk menyesuaikan tinggi sidebar agar sama dengan tinggi konten
    function syncHeight() {
        sidebar.style.minHeight = Math.max(
            content.scrollHeight,
            document.body.scrollHeight,
            window.innerHeight
        ) + 'px';
    }

    syncHeight();

    // Memantau jika ukuran konten berubah maka tinggi sidebar ikut menyesuaikan
    if (window.ResizeObserver) {
        new ResizeObserver(syncHeight).observe(content);
    }

    window.addEventListener('resize', syncHeight);
})();
</script>
</html>