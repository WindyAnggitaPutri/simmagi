<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SIMMAGI</title>
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>

            <h2 class="text-lg font-bold text-gray-800 mb-2 text-center">
                Buat Password Baru
            </h2>

            <p class="text-sm text-gray-500 mb-6 text-center">
                Masukkan password baru untuk akun Anda.
            </p>

            <!-- Form -->
            <form action="{{ route('forgot.password.reset') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Password Baru -->
                <div>
                    <label class="text-sm font-medium text-gray-600">Password Baru</label>
                    <div class="relative mt-1">
                        <input 
                            id="password-input"
                            type="password" 
                            name="password"
                            class="w-full px-4 py-2 pr-10 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"
                            placeholder="Minimal 6 karakter"
                            required
                            autofocus
                        >
                        <!-- Tombol Mata -->
                        <button 
                            type="button"
                            id="toggle-password"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition"
                        >
                            <svg id="icon-eye" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="icon-eye-off" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label class="text-sm font-medium text-gray-600">Konfirmasi Password</label>
                    <div class="relative mt-1">
                        <input 
                            id="password-confirm-input"
                            type="password" 
                            name="password_confirmation"
                            class="w-full px-4 py-2 pr-10 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"
                            placeholder="Ulangi password baru"
                            required
                        >
                        <!-- Tombol Mata -->
                        <button 
                            type="button"
                            id="toggle-confirm-password"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition"
                        >
                            <svg id="icon-eye-confirm" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="icon-eye-off-confirm" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Button Simpan -->
                <button 
                    type="submit"
                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 rounded transition shadow-sm flex items-center justify-center gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Password Baru
                </button>
            </form>

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

    <!-- Toggle Password Script -->
    <script>
        // Toggle Password Baru
        document.getElementById('toggle-password').addEventListener('click', function() {
            const input = document.getElementById('password-input');
            const eye = document.getElementById('icon-eye');
            const eyeOff = document.getElementById('icon-eye-off');
            
            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.add('hidden');
                eyeOff.classList.remove('hidden');
            } else {
                input.type = 'password';
                eye.classList.remove('hidden');
                eyeOff.classList.add('hidden');
            }
        });

        // Toggle Konfirmasi Password
        document.getElementById('toggle-confirm-password').addEventListener('click', function() {
            const input = document.getElementById('password-confirm-input');
            const eye = document.getElementById('icon-eye-confirm');
            const eyeOff = document.getElementById('icon-eye-off-confirm');
            
            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.add('hidden');
                eyeOff.classList.remove('hidden');
            } else {
                input.type = 'password';
                eye.classList.remove('hidden');
                eyeOff.classList.add('hidden');
            }
        });
    </script>

</body>
</html>