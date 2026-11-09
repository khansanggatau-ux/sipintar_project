<script>
  // Kirim pesan ke layar siswa di website, lalu tampilkan popup berhasil / gagal
  function popupNotif(ok, teks) {
    var t = document.createElement('div');
    t.className = 'pop-notif';
    t.setAttribute('role', 'status');
    t.style.cssText = 'position:fixed;right:24px;bottom:' + (24 + document.querySelectorAll('.pop-notif').length * 84) + 'px;z-index:200;'
      + 'max-width:min(400px,calc(100vw - 32px));padding:14px 18px;border-radius:12px;background:#0c2e25;color:#fff;'
      + 'font-size:13.5px;font-weight:500;line-height:1.5;box-shadow:0 10px 30px rgba(0,0,0,.28);'
      + 'border-left:5px solid ' + (ok ? '#5fd39a' : '#ff7a66') + ';';
    t.innerHTML = '<b style="display:block;margin-bottom:2px;font-size:14px;"></b><span></span>';
    t.querySelector('b').textContent = ok ? 'Berhasil terkirim' : 'Gagal terkirim';
    t.querySelector('span').textContent = teks;
    document.body.appendChild(t);
    setTimeout(function () {
      t.style.transition = 'opacity .3s';
      t.style.opacity = '0';
      setTimeout(function () { t.remove(); }, 300);
    }, ok ? 4500 : 10000);
  }

  function kirimNotifWeb(tombol, formId) {
    var f = document.getElementById(formId);
    if (!f) { popupNotif(false, 'Form notifikasi tidak ditemukan. Refresh halaman (Ctrl+F5) lalu coba lagi.'); return; }

    var nama = f.dataset.nama || 'siswa';
    var kecil = tombol.classList.contains('btn-sm');
    var asal = tombol.textContent;
    tombol.disabled = true;
    tombol.textContent = 'Mengirim...';

    function selesai(ok, teks) {
      tombol.disabled = false;
      tombol.textContent = kecil ? (ok ? 'Terkirim \u2713' : 'Ulangi') : asal;
      popupNotif(ok, teks);
    }

    fetch(f.action, {
      method: 'POST',
      body: new FormData(f),
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (r) {
      if (r.ok) {
        r.json().then(function (d) {
          selesai(!!(d && d.ok), d && d.ok
            ? 'Notifikasi terkirim ke website ' + nama + '. Muncul di layarnya begitu dia membuka atau memuat ulang SIPINTAR.'
            : 'Server menjawab tapi pesan tidak tersimpan. Coba lagi.');
        }, function () { selesai(false, 'Jawaban server tidak dikenali. Refresh halaman lalu coba lagi.'); });
        return;
      }
      var pesan = {
        404: 'Alamat kirimnya belum ada (404). Pastikan routes/web.php sudah diganti, lalu jalankan: php artisan route:clear',
        419: 'Sesi halaman sudah kedaluwarsa (419). Refresh halaman lalu coba lagi.',
        401: 'Kamu sudah logout. Login lagi sebagai admin.',
        403: 'Akun ini tidak punya izin mengirim (403).',
        422: 'Isi pesan kosong, jadi tidak dikirim.',
        500: 'Ada error di server (500). Cek file storage/logs/laravel.log.'
      }[r.status] || ('Gagal dengan kode ' + r.status + '.');
      selesai(false, pesan);
    }, function () {
      selesai(false, 'Tidak bisa terhubung ke server. Cek koneksi lalu coba lagi.');
    });
  }
</script>