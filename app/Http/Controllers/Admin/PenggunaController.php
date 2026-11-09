<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\User;
use App\Support\Skor;
use Illuminate\Http\Request;

class PenggunaController extends Controller
{
    // Skor dianggap jelek kalau di bawah 60, atau masih ada barang yang telat dikembalikan.
    private static function perluPeringatan(array $skor): bool
    {
        return ! $skor['baru'] && ($skor['skor'] < 60 || $skor['sedangTelat'] > 0);
    }

    // Daftar semua siswa & guru
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $urut = (string) $request->query('urut', 'prioritas');

        $users = User::where('role', 'siswa')->orderBy('name')->get();

        // satu kali ambil riwayat semua orang, lalu dikelompokkan per user
        $riwayat = Peminjaman::whereIn('status', ['dipinjam', 'selesai'])->get()->groupBy('user_id');

        $baris = $users->map(function ($u) use ($riwayat) {
            $grup = $riwayat->get($u->id, collect());

            return [
                'user' => $u,
                'skor' => Skor::hitung($grup),
                'pernah' => $grup->count(),
            ];
        });

        if ($q !== '') {
            $cari = mb_strtolower($q);
            $baris = $baris->filter(function ($b) use ($cari) {
                $u = $b['user'];

                return str_contains(mb_strtolower((string) $u->name), $cari)
                    || str_contains(mb_strtolower((string) $u->username), $cari)
                    || str_contains(mb_strtolower((string) $u->kelas), $cari);
            });
        }

        if ($urut === 'skor') {
            $baris = $baris->sortBy(function ($b) {
                return $b['skor']['baru'] ? 101 : $b['skor']['skor'];
            });
        } elseif ($urut === 'prioritas') {
            // yang skornya buruk / sedang telat otomatis di paling atas, sisanya urut nama
            $baris = $baris->sort(function ($a, $b) {
                $pa = self::perluPeringatan($a['skor']);
                $pb = self::perluPeringatan($b['skor']);

                if ($pa !== $pb) {
                    return $pa ? -1 : 1;
                }
                if ($pa) {
                    if ($a['skor']['sedangTelat'] !== $b['skor']['sedangTelat']) {
                        return $b['skor']['sedangTelat'] <=> $a['skor']['sedangTelat'];
                    }
                    if ($a['skor']['skor'] !== $b['skor']['skor']) {
                        return $a['skor']['skor'] <=> $b['skor']['skor'];
                    }
                }

                return strcasecmp((string) $a['user']->name, (string) $b['user']->name);
            });
        }

        $baris = $baris->values();

        return view('admin.pengguna', compact('baris', 'q', 'urut'));
    }

    // Detail satu pengguna
    public function show(User $user)
    {
        abort_if($user->role !== 'siswa', 404);

        $riwayat = Peminjaman::with('barang')
            ->where('user_id', $user->id)
            ->orderByDesc('tanggal_pengajuan')
            ->get();

        $skor = Skor::untuk($user->id);
        $perlu = self::perluPeringatan($skor);

        // isi peringatan yang sudah disiapkan sesuai masalahnya (admin boleh mengubah)
        $bagian = [];
        if ($skor['sedangTelat']) {
            $bagian[] = 'Kamu masih punya ' . $skor['sedangTelat'] . ' barang yang terlambat dikembalikan. Mohon segera kembalikan ke admin.';
        }
        if ($skor['telat']) {
            $bagian[] = 'Kamu pernah ' . $skor['telat'] . 'x mengembalikan barang terlambat.';
        }
        if ($skor['rusak']) {
            $bagian[] = $skor['rusak'] . ' barang yang kamu pinjam kembali dalam keadaan rusak.';
        }
        if ($skor['hilang']) {
            $bagian[] = $skor['hilang'] . ' barang yang kamu pinjam hilang.';
        }
        $bagian[] = 'Mohon kembalikan barang tepat waktu dan jaga kondisinya ya. Terima kasih.';
        $pesanDefault = implode(' ', $bagian);

        return view('admin.pengguna-detail', compact('user', 'riwayat', 'skor', 'perlu', 'pesanDefault'));
    }

    // Kirim peringatan ke pengguna (hanya kalau skornya jelek)
    public function peringatan(Request $request, User $user)
    {
        abort_if($user->role !== 'siswa', 404);

        $kembali = redirect()->route('admin.pengguna.show', $user);

        if (! self::perluPeringatan(Skor::untuk($user->id))) {
            return $kembali->with('status', 'Skor pengguna ini belum jelek, jadi belum perlu diperingatkan.');
        }

        $pesan = trim((string) $request->input('pesan', ''));
        if ($pesan === '') {
            return $kembali->with('status', 'Isi pesan peringatannya dulu ya.');
        }

        $user->update([
            'peringatan' => mb_substr($pesan, 0, 500),
            'peringatan_at' => now(),
            'peringatan_dibaca_at' => null,
        ]);

        return $kembali->with('status', 'Peringatan terkirim. Langsung muncul di layar ' . $user->name . ' begitu dia membuka SIPINTAR.');
    }

    // Dipanggil diam-diam saat admin menekan tombol WhatsApp: pesan yang sama juga muncul di website siswa
    public function kirimNotif(Request $request, User $user)
    {
        abort_if($user->role !== 'siswa', 404);

        $pesan = trim((string) $request->input('pesan', ''));
        if ($pesan === '') {
            return response()->json(['ok' => false], 422);
        }

        $user->update([
            'peringatan' => mb_substr($pesan, 0, 500),
            'peringatan_at' => now(),
            'peringatan_dibaca_at' => null,
        ]);

        return response()->json(['ok' => true]);
    }
}