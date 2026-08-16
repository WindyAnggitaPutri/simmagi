<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - SIMMAGI</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md">

        <!-- Card -->
        <div class="bg-white border border-yellow-100 rounded-lg shadow-sm p-8">

            <!-- Logo -->
            <div class="text-center mb-6 flex flex-col items-center">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10">
                <h1 class="text-2xl font-bold mt-3 text-gray-800">SIMMAGI</h1>
                <p class="text-sm text-gray-500">Sistem Monitoring Magang</p>
            </div>

            <!-- Ilustrasi / Ikon -->
            <div class="flex justify-center mb-4">
                <div class="w-16 h-16 bg-yellow-50 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>

            <h2 class="text-lg font-bold text-gray-800 mb-2 text-center">
                Lupa Password?
            </h2>

            <p class="text-sm text-gray-500 mb-6 text-center">
                Masukkan email yang terdaftar. Kami akan mengirimkan link untuk mereset password Anda.
            </p>

            <!-- Success Message -->
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm p-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

         

            <!-- Form -->
            <form action="{{ route('forgot.password.send') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Email -->
                <div>
                    <label class="text-sm font-medium text-gray-600">Email</label>
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input 
                            type="email" 
                            name="email"
                            value="{{ old('email') }}"
                            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"
                            placeholder="contoh@email.com"
                            required
                            autofocus
                        >
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Button Kirim -->
                <button 
                    type="submit"
                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 rounded transition shadow-sm flex items-center justify-center gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Kirim Link Reset Password
                </button>
            </form>

            <!-- Error Token -->
            @if ($errors->has('token'))
                <div class="mt-4 bg-red-50 border border-red-200 text-red-600 text-sm p-3 rounded">
                    {{ $errors->first('token') }}
                </div>
            @endif

            <!-- Kembali ke Login -->
            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-yellow-600 hover:text-yellow-700 font-medium inline-flex items-center gap-1 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Login
                </a>
            </div>

        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-gray-400 mt-6">
            © {{ date('Y') }} SIMMAGI
        </p>

    </div>

</body>
</html>