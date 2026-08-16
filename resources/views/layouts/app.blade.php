<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIM Magang</title>
    {{-- Memanggil file CSS dan JS bawaan Laravel menggunakan Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

       @stack('styles')
</head>

<body class="flex flex-col min-h-screen bg-white">

    {{-- Memanggil file navbar dari folder component --}}
    @include('component.navbar')

    {{-- Tempat untuk menampilkan isi halaman utama --}}
    <main class="flex-grow p-6">
        @yield('content')
    </main>

    {{-- Memanggil file footer dari folder component --}}
    @include('component.footer')

       @stack('scripts')

</body>
</html>