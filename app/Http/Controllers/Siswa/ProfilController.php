<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Support\Skor;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
    public function lengkapiForm()
    {
        if (auth()->user()->profile_complete) {
            return redirect()->route('siswa.inventaris.index');
        }

        return view('siswa.lengkapi');
    }

    public function lengkapiSimpan(Request $request)
    {
        $request->merge(['no_wa' => $this->rapikanWa($request->input('no_wa'))]);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'tipe' => 'required|in:Siswa,Guru',
            'jurusan' => 'required|string|max:100',
            'kelas' => 'required|string|max:50',
            'no_wa' => $this->aturanWa(),
        ], $this->pesanWa());

        $data['profile_complete'] = true;
        auth()->user()->update($data);

        return redirect()->route('siswa.inventaris.index');
    }

    public function show()
    {
        $user = auth()->user();

        $milikSaya = Peminjaman::with('barang')
            ->where('user_id', $user->id)
            ->latest('tanggal_pengajuan')
            ->get();

        $stats = [
            'total' => $milikSaya->count(),
            'dipinjam' => $milikSaya->where('status', 'dipinjam')->count(),
            'selesai' => $milikSaya->where('status', 'selesai')->count(),
        ];

        $skor = Skor::untuk($user->id);

        return view('siswa.profil', compact('user', 'milikSaya', 'stats', 'skor'));
    }

    public function update(Request $request)
    {
        $request->merge(['no_wa' => $this->rapikanWa($request->input('no_wa'))]);

        $data = $request->validate([
            'jurusan' => 'required|string|max:100',
            'kelas' => 'required|string|max:50',
            'no_wa' => $this->aturanWa(),
        ], $this->pesanWa());

        auth()->user()->update($data);

        return back()->with('status', 'Profil disimpan.');
    }

    // +62 812-3456-7890 / 62812... / 812... -> 081234567890
    private function rapikanWa($no): string
    {
        $n = preg_replace('/[^0-9+]/', '', (string) $no);

        if (strpos($n, '+62') === 0) {
            $n = '0' . substr($n, 3);
        } elseif (strpos($n, '62') === 0) {
            $n = '0' . substr($n, 2);
        } elseif (strpos($n, '8') === 0) {
            $n = '0' . $n;
        }

        return $n;
    }

    // nomor HP Indonesia: 08xx, total 10-13 digit, bukan angka berulang
    private function aturanWa(): array
    {
        return ['required', 'regex:/^08[1-9][0-9]{7,10}$/', 'not_regex:/(\d)\1{6,}/'];
    }

    private function pesanWa(): array
    {
        return [
            'no_wa.required' => 'Nomor WhatsApp wajib diisi.',
            'no_wa.regex' => 'Nomor WhatsApp belum valid. Pakai nomor HP Indonesia yang aktif WhatsApp, contoh 081234567890.',
            'no_wa.not_regex' => 'Nomor WhatsApp kelihatannya bukan nomor asli (angkanya berulang). Isi dengan nomor aktifmu.',
        ];
    }
}