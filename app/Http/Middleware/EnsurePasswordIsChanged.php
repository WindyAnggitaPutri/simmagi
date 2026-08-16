<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ambil data user yang lagi login saat ini
        $user = auth()->user();

        //HAPUS $user->role !== 'admin'
        // Cek: Jika user login dan must_change_password masih true (1)
        if ($user && $user->must_change_password) {
            
            // Kecuali dia sudah berada di route ganti password, biarkan lewat (supaya tidak loop redirect)
            // Ini penting biar pas user lagi di halaman ganti password, dia nggak dilempar terus ke halaman itu lagi
            if ($request->routeIs('password.change.form') || $request->routeIs('password.change.update')) {
                return $next($request);
            }

            // Paksa redirect ke halaman ganti password
            // Kalau user belum ganti password, mau akses halaman apa pun pasti dilempar kesini
            return redirect()->route('password.change.form');
        }

        // Kalau syarat di atas nggak terpenuhi (artinya udah ganti password), biarkan user ngakses halaman yang dituju
        return $next($request);
    }
}