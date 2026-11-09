<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'siswa') {
            if (! $user->profile_complete) {
                return redirect()->route('siswa.lengkapi');
            }

            // ada peringatan dari admin yang belum dibaca -> tampilkan dulu sebelum halaman lain
            if (! empty($user->peringatan) && $user->peringatan_dibaca_at === null) {
                return redirect()->route('siswa.peringatan');
            }
        }

        return $next($request);
    }
}