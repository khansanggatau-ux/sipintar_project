<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'SIPINTAR')</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/nav-atas.css') }}">
</head>
<body>
@php
  $u = auth()->user();
  $isAdmin = $u->role === 'admin';
  $roleLabel = $isAdmin ? 'Admin' : ($u->tipe ?? 'Siswa');
  $initial = strtoupper(mb_substr($u->name, 0, 1));
  $rute = $isAdmin ? 'admin.notif' : 'siswa.notif';
  $notifUrl = \Illuminate\Support\Facades\Route::has($rute) ? route($rute) : null;
  $icon = [
    'bell'  => '<svg viewBox="0 0 24 24"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/></svg>',
    'chev'  => '<svg class="um-chev" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>',
    'out'   => '<svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg>',
  ];
@endphp
<div class="shell">
  <header class="topnav">
    <div class="topnav-in">
      <span class="brand"><span class="dot"></span><span>SIPINTAR</span></span>

      <nav class="tn-links" aria-label="Menu utama">
        @if($isAdmin)
          <a href="{{ route('admin.inventaris.index') }}" class="{{ request()->routeIs('admin.inventaris.*') ? 'active' : '' }}">Data Inventaris</a>
          <a href="{{ route('admin.peminjaman.index') }}" class="{{ request()->routeIs('admin.peminjaman.*') ? 'active' : '' }}">Peminjaman</a>
          <a href="{{ route('admin.pengguna.index') }}" class="{{ request()->routeIs('admin.pengguna.*') ? 'active' : '' }}">Pengguna</a>
          <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard &amp; Laporan</a>
        @else
          <a href="{{ route('siswa.inventaris.index') }}" class="{{ request()->routeIs('siswa.inventaris.*') ? 'active' : '' }}">Inventaris</a>
          <a href="{{ route('siswa.pengajuan.index') }}" class="{{ request()->routeIs('siswa.pengajuan.*') ? 'active' : '' }}">Pengajuan Saya</a>
          <a href="{{ route('siswa.profil') }}" class="{{ request()->routeIs('siswa.profil') ? 'active' : '' }}">Profil</a>
        @endif
      </nav>

      <div class="tn-right">
        @if($notifUrl)
        <span class="bell-wrap" id="bellWrap">
          <button type="button" class="bell" id="bell" aria-label="Notifikasi" aria-expanded="false">{!! $icon['bell'] !!}<span class="bell-badge" id="bellBadge" hidden>0</span></button>
          <div class="bell-panel" id="bellPanel" hidden>
            <div class="bell-head"><b>Notifikasi</b><button type="button" id="bellClear" hidden>Tandai dibaca</button></div>
            <div class="bell-list" id="bellList"><p class="bell-empty">Belum ada notifikasi.</p></div>
          </div>
        </span>
        @endif

        <div class="um" id="um">
          <button type="button" class="um-btn" id="umBtn" aria-haspopup="true" aria-expanded="false" aria-controls="umPanel">
            <span class="tb-av">{{ $initial }}</span>
            <span class="tb-name">{{ $u->name }}</span>
            {!! $icon['chev'] !!}
          </button>
          <div class="um-panel" id="umPanel" hidden>
            <div class="um-head"><b>{{ $u->name }}</b><small>{{ $roleLabel }}</small></div>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="um-out">{!! $icon['out'] !!}<span>Logout</span></button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </header>

  @if(session('status'))
    <div class="toast" id="toast" role="status"><span class="toast-dot"></span><span>{{ session('status') }}</span><button type="button" class="toast-x" aria-label="Tutup" onclick="tutupToast()">&times;</button></div>
  @endif

  <main class="main" {!! $__env->yieldContent('main-style') !!}>
    @yield('content')
  </main>
</div>

@yield('modals')

<script>
  (function () {
    var btn = document.getElementById('umBtn'), panel = document.getElementById('umPanel'), wrap = document.getElementById('um');
    function set(buka) {
      panel.hidden = !buka;
      btn.setAttribute('aria-expanded', buka ? 'true' : 'false');
    }
    btn.addEventListener('click', function (e) { e.stopPropagation(); set(panel.hidden); });
    document.addEventListener('click', function (e) { if (!wrap.contains(e.target)) set(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') set(false); });
  })();
</script>
<script>
  function tutupToast() {
    var t = document.getElementById('toast');
    if (!t) return;
    t.classList.add('out');
    setTimeout(function () { t.remove(); }, 350);
  }
  if (document.getElementById('toast')) setTimeout(tutupToast, 4500);
</script>
@if($notifUrl)
<script>
(function () {
  var URL_NOTIF = @json($notifUrl), ROLE = @json($isAdmin ? 'admin' : 'siswa'), UID = @json($u->id);
  var bell = document.getElementById('bell'), panel = document.getElementById('bellPanel'), badge = document.getElementById('bellBadge');
  var list = document.getElementById('bellList'), clear = document.getElementById('bellClear'), wrap = document.getElementById('bellWrap');
  var kini = { items: [], log: [] };

  function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function baca(k, def) { try { var v = localStorage.getItem(k); return v === null ? def : JSON.parse(v); } catch (e) { return def; } }
  function simpan(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} }
  function lalu(ts) {
    var d = Math.round((Date.now() - ts) / 1000);
    if (d < 60) return 'baru saja';
    if (d < 3600) return Math.floor(d / 60) + ' menit lalu';
    if (d < 86400) return Math.floor(d / 3600) + ' jam lalu';
    return Math.floor(d / 86400) + ' hari lalu';
  }
  function toast(teks) {
    var t = document.createElement('div');
    t.className = 'toast'; t.setAttribute('role', 'status');
    t.style.bottom = (24 + document.querySelectorAll('.toast').length * 64) + 'px';
    t.innerHTML = '<span class="toast-dot"></span><span>' + esc(teks) + '</span><button type="button" class="toast-x" aria-label="Tutup">&times;</button>';
    document.body.appendChild(t);
    function tutup() { t.classList.add('out'); setTimeout(function () { t.remove(); }, 350); }
    t.querySelector('.toast-x').onclick = tutup;
    setTimeout(tutup, 6000);
  }
  function bunyi() { bell.classList.remove('ring'); void bell.offsetWidth; bell.classList.add('ring'); }

  function gambar() {
    var baris = kini.items, jumlah = kini.badge || 0;
    badge.hidden = jumlah <= 0; badge.textContent = jumlah > 9 ? '9+' : jumlah;
    clear.hidden = !(ROLE === 'siswa' && kini.log.some(function (l) { return !l.baca; }));
    if (!baris.length) { list.innerHTML = '<p class="bell-empty">Belum ada notifikasi.</p>'; return; }
    list.innerHTML = baris.map(function (b) {
      return '<a class="bell-item' + (b.warn ? ' warn' : '') + (b.baru ? ' baru' : '') + '" href="' + esc(b.href || '#') + '"><i></i><span><b>' + esc(b.t) + '</b>' + (b.s ? '<small>' + esc(b.s) + '</small>' : '') + '</span></a>';
    }).join('');
  }

  function olahAdmin(d) {
    var items = [];
    if (d.menunggu > 0) items.push({ t: d.menunggu + ' pengajuan menunggu persetujuan', s: 'Buka daftar pengajuan', href: d.links.menunggu, baru: true });
    if (d.terlambat > 0) items.push({ t: d.terlambat + ' barang terlambat dikembalikan', s: 'Tagih lewat WhatsApp', href: d.links.dipinjam, warn: true });
    if (d.perpanjang > 0) items.push({ t: d.perpanjang + ' permintaan perpanjang pinjaman', s: 'Buka tab Approve Pengembalian', href: d.links.dipinjam, baru: true });
    d.baru.forEach(function (b) { items.push({ t: b.nama + ' mengajukan ' + b.barang, s: b.waktu, href: b.href }); });
    var prev = baca('sip_adm_menunggu', null);
    if (prev !== null && d.menunggu > prev) { toast('Ada pengajuan baru' + (d.baru[0] ? ': ' + d.baru[0].barang : '')); bunyi(); }
    simpan('sip_adm_menunggu', d.menunggu);
    kini = { items: items, log: [], badge: d.menunggu + d.terlambat + (d.perpanjang || 0) };
  }

  function olahSiswa(d) {
    var kPeta = 'sip_status_' + UID, kLog = 'sip_log_' + UID;
    var peta = baca(kPeta, null), log = baca(kLog, []), baru = {};
    d.items.forEach(function (p) {
      baru[p.id] = p.status;
      var kx = 'x' + p.id, px = p.perpanjang || '';
      baru[kx] = px;
      if (peta && peta[kx] !== undefined && peta[kx] !== px && (px === 'disetujui' || px === 'ditolak')) {
        var mx = px === 'disetujui' ? 'Perpanjangan "' + p.barang + '" disetujui. Batas kembali bertambah.' : 'Perpanjangan "' + p.barang + '" ditolak admin.';
        log.unshift({ t: mx, ts: Date.now(), baca: false, href: d.link }); toast(mx); bunyi();
      }
      if (peta && peta[p.id] !== undefined && peta[p.id] !== p.status) {
        var msg = p.status === 'dipinjam' ? '"' + p.barang + '" dicatat dipinjam. Ingat kembalikan tepat waktu.'
                : p.status === 'selesai' ? '"' + p.barang + '" sudah dikembalikan. Terima kasih!'
                : p.status === 'ditolak' ? 'Pengajuan "' + p.barang + '" ditolak admin.' : null;
        if (msg) { log.unshift({ t: msg, ts: Date.now(), baca: false, href: d.link }); toast(msg); bunyi(); }
      }
    });
    log = log.slice(0, 10);
    simpan(kPeta, baru); simpan(kLog, log);

    var pengingat = [];
    if (d.pesan) {
      var kPsn = 'sip_psn_' + UID;
      if (baca(kPsn, 0) !== d.pesan.at) { toast('Ada pesan baru dari admin. Klik lonceng untuk membacanya.'); bunyi(); simpan(kPsn, d.pesan.at); }
      pengingat.push({ t: 'Pesan dari admin', s: d.pesan.cuplikan, href: d.pesan.href, baru: true });
    }
    var kTsd = 'sip_tsd_' + UID, sudahTahu = baca(kTsd, []), idTsd = [];
    (d.tersedia || []).forEach(function (x) {
      idTsd.push(x.id);
      if (sudahTahu.indexOf(x.id) === -1) { toast('"' + x.barang + '" yang kamu tunggu sudah tersedia. Ajukan sekarang!'); bunyi(); }
    });
    simpan(kTsd, idTsd);
    (d.tersedia || []).forEach(function (x) { pengingat.push({ t: '"' + x.barang + '" sudah tersedia', s: 'Ajukan sekarang sebelum kehabisan', href: x.href, baru: true }); });
    d.items.forEach(function (p) {
      if (p.status !== 'dipinjam') return;
      if (p.telat > 0) pengingat.push({ t: '"' + p.barang + '" terlambat ' + p.telat + ' hari', s: 'Segera kembalikan ke admin', href: d.link, warn: true });
      else if (p.sisa === 0) pengingat.push({ t: '"' + p.barang + '" harus dikembalikan hari ini', s: 'Batas kembali hari ini', href: d.link, warn: true });
      else if (p.sisa === 1) pengingat.push({ t: '"' + p.barang + '" dikembalikan besok', s: 'Batas kembali besok', href: d.link });
    });
    var items = pengingat.concat(log.map(function (l) { return { t: l.t, s: lalu(l.ts), href: l.href, baru: !l.baca }; }));
    kini = { items: items, log: log, badge: log.filter(function (l) { return !l.baca; }).length + pengingat.length, kLog: kLog };
  }

  function ambil() {
    if (document.hidden) return;
    fetch(URL_NOTIF, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) { if (!d) return; (ROLE === 'admin' ? olahAdmin : olahSiswa)(d); gambar(); })
      .catch(function () {});
  }

  bell.addEventListener('click', function (e) {
    e.stopPropagation();
    var buka = panel.hidden;
    panel.hidden = !buka; bell.setAttribute('aria-expanded', buka ? 'true' : 'false');
  });
  document.addEventListener('click', function (e) { if (!wrap.contains(e.target)) { panel.hidden = true; bell.setAttribute('aria-expanded', 'false'); } });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') panel.hidden = true; });
  clear.addEventListener('click', function () {
    kini.log.forEach(function (l) { l.baca = true; });
    simpan(kini.kLog, kini.log);
    kini.badge = Math.max(0, kini.items.filter(function (b) { return b.warn; }).length);
    kini.items.forEach(function (b) { b.baru = false; });
    gambar();
  });

  ambil();
  setInterval(ambil, 20000);
  document.addEventListener('visibilitychange', function () { if (!document.hidden) ambil(); });
})();
</script>
@endif
<script src="{{ asset('js/app.js') }}"></script>
@yield('scripts')
</body>
</html>