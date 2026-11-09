<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:100',
            'password' => 'required|string',
        ]);

        // Batasi percobaan: 5 kali per menit untuk kombinasi username + IP (cegah tebak-tebak password)
        $kunci = Str::lower($data['username']) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            $detik = RateLimiter::availableIn($kunci);
            return back()
                ->withErrors(['username' => "Terlalu banyak percobaan. Coba lagi dalam {$detik} detik."])
                ->withInput($request->only('username'));
        }

        $existing = User::where('username', $data['username'])->first();

        // ---------- MODE BUAT AKUN BARU (kotak dicentang) ----------
        if ($request->boolean('daftar_baru')) {
            if ($existing) {
                return back()
                    ->withErrors(['username' => 'Username "' . $data['username'] . '" sudah dipakai. Pakai username lain, atau hilangkan centang kalau ini akunmu sendiri.'])
                    ->withInput($request->only('username', 'daftar_baru'));
            }

            if (mb_strlen($data['password']) < 6) {
                return back()
                    ->withErrors(['username' => 'Password untuk akun baru minimal 6 karakter.'])
                    ->withInput($request->only('username', 'daftar_baru'));
            }

            $baru = User::create([
                'name' => $data['username'],
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'role' => 'siswa',
                'profile_complete' => false,
            ]);

            Auth::login($baru);
            $request->session()->regenerate();

            return redirect()->route('siswa.lengkapi');
        }

        // ---------- MODE LOGIN BIASA ----------
        if ($existing) {
            if (Auth::attempt($data)) {
                RateLimiter::clear($kunci);
                $request->session()->regenerate();
                return $this->redirectByRole();
            }

            RateLimiter::hit($kunci, 60);

            return back()
                ->withErrors(['username' => 'Username atau password salah.'])
                ->withInput($request->only('username'));
        }

        // Username belum ada: jangan langsung dibuatkan akun (bisa jadi cuma salah ketik).
        RateLimiter::hit($kunci, 60);

        return back()
            ->withErrors(['username' => 'Username "' . $data['username'] . '" belum terdaftar. Kalau salah ketik, klik "Ganti akun". Kalau memang mau buat akun baru, centang kotak "buat akun baru" di bawah kolom kata sandi.'])
            ->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function redirectByRole()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.inventaris.index');
        }

        if (!$user->profile_complete) {
            return redirect()->route('siswa.lengkapi');
        }

        return redirect()->route('siswa.inventaris.index');
    }
}