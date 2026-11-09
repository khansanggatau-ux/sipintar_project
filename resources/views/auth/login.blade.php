@php
  // ===== GANTI DI SINI: kontak admin =====
  $kontak = ['nama' => 'Admin Lab Jurusan', 'wa' => '0858-0277-2388'];
  $wa = preg_replace('/\D+/', '', $kontak['wa']);
  if (str_starts_with($wa, '0')) { $wa = '62' . substr($wa, 1); }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>document.documentElement.className+=' js';</script>
<title>Login - SIPINTAR</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<style>
  html{scroll-behavior:smooth;scroll-padding-top:76px;}
  .lp-wrap{max-width:1120px;margin:0 auto;padding:0 28px;}

  /* nav */
  .lp-nav{position:sticky;top:0;z-index:20;background:rgba(238,240,232,.9);backdrop-filter:blur(10px);border-bottom:1px solid var(--line);}
  .lp-nav .lp-wrap{display:flex;align-items:center;gap:24px;height:66px;}
  .lp-brand{display:flex;align-items:center;gap:10px;font-weight:800;letter-spacing:-.01em;color:var(--navy);}
  .lp-brand i{width:22px;height:22px;border-radius:7px;background:var(--accent);}
  .lp-links{display:flex;gap:4px;margin-left:auto;}
  .lp-links a{padding:8px 12px;border-radius:var(--r-sm);font-size:13.5px;font-weight:600;color:var(--text-2);}
  .lp-links a:hover{background:#e3e8da;color:var(--text);}
  .lp-nav .btn{padding:9px 18px;font-size:13px;}

  /* hero */
  .lp-hero{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(0,420px);gap:64px;align-items:center;padding:72px 0 88px;}
  .lp-hero h1{margin-bottom:20px;font-size:clamp(36px,5.2vw,62px);font-weight:800;line-height:1.05;letter-spacing:-.035em;color:var(--navy);}
  .lp-lead{max-width:520px;margin:0 0 32px;font-size:17px;line-height:1.65;color:var(--text-2);}
  .lp-sheet{max-width:460px;padding:8px 22px;background:var(--surface);border:1.5px solid var(--line);border-radius:var(--r-lg);}
  .lp-sheet p{margin:12px 0 2px;font-size:13px;font-weight:600;color:var(--muted);}
  .lp-sheet div{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:13px 0;border-bottom:1px dashed var(--line-strong);font-size:14px;font-weight:600;}
  .lp-sheet div:last-child{border-bottom:0;}
  .lp-sheet div span:last-child{padding:3px 11px;border-radius:99px;background:var(--accent);color:#2a1800;font-size:12px;font-weight:700;}

  .lp-card{padding:36px 34px;background:var(--surface);border:1px solid var(--line);border-radius:var(--r-xl);box-shadow:var(--shadow-lg);}
  .lp-card h2{margin-bottom:6px;font-size:24px;}
  .lp-card .sub{margin:0 0 24px;font-size:14px;color:var(--muted);}
  .lp-err{margin:0 0 16px;padding:10px 14px;border-radius:var(--r-md);background:var(--red-bg);color:var(--red);font-size:13px;font-weight:600;}

  /* sections */
  .lp-sec{padding:84px 0;}
  .lp-h{max-width:640px;margin-bottom:12px;font-size:clamp(28px,3.6vw,40px);font-weight:800;line-height:1.12;letter-spacing:-.03em;}
  .lp-sub{max-width:560px;margin:0 0 48px;font-size:16px;line-height:1.65;color:var(--text-2);}

  .lp-steps{display:grid;grid-template-columns:repeat(4,1fr);counter-reset:s;}
  .lp-step{position:relative;padding-right:24px;}
  .lp-step::before{
    counter-increment:s;content:counter(s);position:relative;z-index:1;
    display:flex;align-items:center;justify-content:center;width:42px;height:42px;margin-bottom:18px;
    border-radius:50%;background:var(--navy);color:var(--accent);font-weight:800;
  }
  .lp-step::after{content:"";position:absolute;top:20px;left:42px;right:0;height:2px;background:var(--line-strong);}
  .lp-step:last-child::after{display:none;}
  .lp-step h3{margin-bottom:8px;font-size:17px;}
  .lp-step p{margin:0;font-size:14px;line-height:1.6;color:var(--text-2);}

  .lp-dark{background:var(--navy);color:#fff;}
  .lp-dark .lp-sub{color:#a4b8ad;margin-bottom:0;}
  .lp-cats{display:grid;grid-template-columns:minmax(0,.8fr) minmax(0,1.2fr);gap:64px;align-items:start;}
  .lp-cat{display:flex;justify-content:space-between;align-items:baseline;gap:24px;padding:22px 0;border-bottom:1px solid rgba(255,255,255,.12);}
  .lp-cat:first-child{border-top:1px solid rgba(255,255,255,.12);}
  .lp-cat b{font-size:22px;font-weight:700;letter-spacing:-.02em;}
  .lp-cat span{max-width:300px;text-align:right;font-size:14px;color:#a4b8ad;}

  .lp-roles{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
  .lp-role{padding:32px;background:var(--surface);border:1.5px solid var(--line);border-radius:var(--r-lg);}
  .lp-role.alt{background:var(--amber-bg);border-color:#f1dcae;}
  .lp-role h3{margin-bottom:6px;font-size:22px;}
  .lp-role > p{margin:0 0 20px;font-size:14.5px;color:var(--text-2);}
  .lp-role ul{display:grid;gap:12px;margin:0;padding:0;list-style:none;}
  .lp-role li{position:relative;padding-left:28px;font-size:14.5px;line-height:1.5;}
  .lp-role li::before{content:"\2713";position:absolute;left:0;top:0;font-weight:800;color:var(--green);}

  .lp-faq{display:grid;grid-template-columns:minmax(0,.8fr) minmax(0,1.2fr);gap:64px;align-items:start;}
  .lp-faq .lp-sub{margin-bottom:0;}
  .lp-faq details{border-bottom:1px solid var(--line-strong);}
  .lp-faq details:first-child{border-top:1px solid var(--line-strong);}
  .lp-faq summary{display:flex;justify-content:space-between;gap:16px;padding:20px 0;list-style:none;cursor:pointer;font-size:16px;font-weight:700;}
  .lp-faq summary::-webkit-details-marker{display:none;}
  .lp-faq summary::after{content:"+";font-size:22px;line-height:1;color:var(--accent-dark);transition:transform .2s;}
  .lp-faq details[open] summary::after{transform:rotate(45deg);}
  .lp-faq details p{max-width:520px;margin:0 0 20px;font-size:14.5px;line-height:1.65;color:var(--text-2);}

  .lp-cta{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:24px;margin-bottom:84px;padding:48px;border-radius:var(--r-xl);color:#fff;background:radial-gradient(420px 240px at 100% 0%,rgba(224,135,63,.25),transparent 70%),var(--navy);}
  .lp-cta h2{max-width:480px;font-size:clamp(24px,3vw,32px);font-weight:800;letter-spacing:-.025em;}
  .lp-foot{display:flex;flex-wrap:wrap;justify-content:space-between;gap:12px;padding:28px 0;border-top:1px solid var(--line);font-size:13px;color:var(--muted);}

  .lp-contact{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:28px;padding:36px 40px;background:var(--surface);border:1.5px solid var(--line);border-radius:var(--r-xl);}
  .lp-contact .lp-h{margin-bottom:8px;}
  .lp-contact .lp-sub{margin:0;}
  .lp-contact-box{display:flex;flex-direction:column;align-items:flex-start;gap:4px;}
  .lp-contact-name{font-size:13px;font-weight:600;color:var(--muted);}
  .lp-contact-num{margin-bottom:10px;font-size:26px;letter-spacing:-.015em;}
  .lp-inline{color:var(--accent-dark);font-weight:700;text-decoration:underline;text-underline-offset:3px;}

  /* ===== tema papan sirkuit ===== */
  .lp-nav{background:rgba(12,46,37,.94);border-bottom:1px solid rgba(255,255,255,.08);}
  .lp-brand{color:#fff;}
  .lp-links a{color:#b4c9be;}
  .lp-links a:hover{background:rgba(255,255,255,.08);color:#fff;}
  .lp-herobg{background:var(--navy) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 640' fill='none' stroke='%23e0873f' stroke-opacity='.2' stroke-width='2.5'%3E%3Cpath d='M1200 80H980L930 130H760'/%3E%3Ccircle cx='748' cy='130' r='7'/%3E%3Cpath d='M1200 220H1060L1010 270V380'/%3E%3Ccircle cx='1010' cy='392' r='7'/%3E%3Cpath d='M1200 520H900L850 470H700'/%3E%3Ccircle cx='688' cy='470' r='7'/%3E%3Cpath d='M600 640V560L650 510H820'/%3E%3Ccircle cx='832' cy='510' r='7'/%3E%3Cpath d='M300 0V70L350 120H520'/%3E%3Ccircle cx='532' cy='120' r='7'/%3E%3Cpath d='M0 560H180L230 510H400'/%3E%3Ccircle cx='412' cy='510' r='7'/%3E%3Crect x='1040' y='430' width='80' height='46' rx='4'/%3E%3C/svg%3E") no-repeat right center / cover;}
  .lp-hero h1{color:#fff;}
  .lp-lead{color:#b4c9be;}
  .lp-sheet{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.14);}
  .lp-sheet p{color:#8fa99b;}
  .lp-sheet div{color:#eaf3ee;border-bottom-color:rgba(255,255,255,.16);}
  .lp-sheet div span:last-child{font-family:'IBM Plex Mono',ui-monospace,Consolas,monospace;font-size:12px;border-radius:6px;}
  .lp-card{position:relative;padding:68px 34px 34px;border:0;border-radius:22px;box-shadow:0 36px 70px -24px rgba(0,0,0,.55);}
  .lp-card::before{content:"";position:absolute;top:18px;left:50%;width:20px;height:20px;margin-left:-10px;border-radius:50%;background:var(--navy);box-shadow:inset 0 2px 5px rgba(0,0,0,.5);}
  .lp-card::after{content:"";position:absolute;top:50px;left:26px;right:26px;border-top:2px dashed var(--line-strong);}

  /* ===== form dua langkah ala kartu akses ===== */
  .lp-line{display:block;position:relative;margin-bottom:24px;}
  .lp-line span{display:block;margin-bottom:2px;font-family:var(--mono);font-size:12px;color:var(--muted);}
  .lp-line input{width:100%;padding:10px 0;border:0;border-bottom:2px solid var(--line-strong);border-radius:0;background:transparent;font-family:var(--mono);font-size:18px;color:var(--navy);transition:border-color .2s;}
  .lp-line input:focus{outline:none;border-bottom-color:var(--accent);}
  .lp-line input::placeholder{color:#b3c0b8;}
  .lp-line input[type=password]{padding-right:70px;}
  .lp-eye{position:absolute;right:0;bottom:10px;border:0;background:none;font-family:var(--mono);font-size:12px;font-weight:600;color:var(--accent-dark);cursor:pointer;}
  .lp-pane{display:none;}
  .lp-pane.is-active{display:block;animation:lpPane .35s var(--ease);}
  .lp-hello{margin:0 0 20px;font-size:15px;color:var(--text-2);}
  .lp-back{display:block;margin:14px auto 0;border:0;background:none;font-size:13px;color:var(--muted);text-decoration:underline;text-underline-offset:3px;cursor:pointer;}
  .lp-shake{animation:lpShake .35s;}
  @keyframes lpPane{from{opacity:0;transform:translateX(18px);}to{opacity:1;transform:none;}}
  @keyframes lpShake{25%{transform:translateX(-6px);}75%{transform:translateX(6px);}}
  @keyframes lpSwing{0%{transform:rotate(-4deg);}30%{transform:rotate(2.5deg);}60%{transform:rotate(-1.2deg);}100%{transform:rotate(0);}}
  @keyframes lpRise{from{opacity:0;transform:translateY(24px);}to{opacity:1;transform:none;}}
  .lp-card{transform-origin:50% 0;animation:lpSwing 1.5s var(--ease) .25s both;}

  /* ===== animasi saat halaman dibuka dan di-scroll ===== */
  .js .lp-hero > div:first-child > *{animation:lpRise .8s var(--ease) both;}
  .js .lp-hero > div:first-child > :nth-child(2){animation-delay:.12s;}
  .js .lp-hero > div:first-child > :nth-child(3){animation-delay:.24s;}
  .js [data-reveal]{opacity:0;transform:translateY(28px);transition:opacity .7s var(--ease),transform .7s var(--ease);transition-delay:var(--d,0s);}
  .js [data-reveal].in{opacity:1;transform:none;}
  .js .lp-step::after{transform:scaleX(0);transform-origin:left;transition:transform .9s var(--ease) .35s;}
  .js .lp-step.in::after{transform:scaleX(1);}
  .lp-progress{position:fixed;top:0;left:0;right:0;z-index:50;height:3px;background:var(--accent);transform:scaleX(0);transform-origin:left;}
  .lp-nav.scrolled{box-shadow:0 10px 28px -14px rgba(0,0,0,.5);}
  .lp-herobg{background-position:right calc(50% + var(--py,0px));}
  @media(max-width:900px){
    .js .lp-step::after{transform:scaleY(0);transform-origin:top;}
    .js .lp-step.in::after{transform:scaleY(1);}
  }
  @media(prefers-reduced-motion:reduce){
    .lp-card,.js .lp-hero > div:first-child > *{animation:none;}
    .js [data-reveal]{opacity:1;transform:none;transition:none;}
    .js .lp-step::after{transform:none;transition:none;}
  }

  @media(max-width:900px){
    .lp-links{display:none;}
    .lp-nav .btn{margin-left:auto;}
    .lp-hero{grid-template-columns:1fr;gap:40px;padding:44px 0 56px;}
    .lp-sec{padding:56px 0;}
    .lp-steps{grid-template-columns:1fr;gap:28px;}
    .lp-step{padding:0 0 0 58px;}
    .lp-step::before{position:absolute;left:0;top:0;margin:0;}
    .lp-step::after{top:42px;bottom:-28px;left:20px;right:auto;width:2px;height:auto;}
    .lp-cats,.lp-faq,.lp-roles{grid-template-columns:1fr;gap:32px;}
    .lp-roles{gap:16px;}
    .lp-cta{padding:32px 24px;}
  }
  @media(max-width:520px){
    .lp-wrap{padding:0 18px;}
    .lp-card{padding:64px 22px 28px;}
    .lp-cat{flex-direction:column;gap:6px;}
    .lp-cat span{text-align:left;}
  }
</style>
</head>
<body>
<div class="lp-progress" id="lpProgress"></div>

<nav class="lp-nav" id="lpNav">
  <div class="lp-wrap">
    <a href="#masuk" class="lp-brand"><i></i><span>SIPINTAR</span></a>
    <div class="lp-links">
      <a href="#alur">Alur</a>
      <a href="#alat">Alat</a>
      <a href="#peran">Untuk siapa</a>
      <a href="#bantuan">Bantuan</a>
      <a href="#kontak">Kontak</a>
    </div>
    <a href="#masuk" class="btn btn-orange">Masuk</a>
  </div>
</nav>

<div class="lp-herobg">
<header class="lp-wrap lp-hero" id="masuk">
  <div>
    <h1>Satu pintu untuk seluruh inventaris jurusan.</h1>
    <p class="lp-lead">Pantau stok, ajukan, dan kelola peminjaman alat lab dari satu sistem yang sama, untuk siswa, guru, maupun admin.</p>
    <div class="lp-sheet">
      <p>Sebagian isi inventaris</p>
      <div><span>Arduino Uno</span><span>30 unit</span></div>
      <div><span>Laptop HP</span><span>3 unit</span></div>
      <div><span>Proyektor</span><span>3 unit</span></div>
    </div>
  </div>

  <div class="lp-card">
    <h2>Selamat datang kembali</h2>
    <p class="sub">Masuk dengan akun sekolahmu.</p>

    @if ($errors->any())
      <p class="lp-err">{{ $errors->first() }}</p>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}" id="loginForm">
      @csrf
      <div class="lp-pane is-active" data-pane="1">
        <label class="lp-line"><span>nama pengguna</span>
          <input type="text" name="username" id="inUser" value="{{ old('username') }}" placeholder="ketik username" autocomplete="username" autofocus required>
        </label>
        <button type="button" class="btn btn-orange btn-block" id="btnNext">Lanjut</button>
      </div>
      <div class="lp-pane" data-pane="2">
        <p class="lp-hello">Halo, <b id="helloName"></b></p>
        <label class="lp-line"><span>kata sandi</span>
          <input type="password" name="password" id="inPass" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" autocomplete="current-password" required>
          <button type="button" class="lp-eye" id="btnEye">tampil</button>
        </label>
        <label style="display:flex;gap:8px;align-items:flex-start;font-size:13px;margin:4px 0 14px;cursor:pointer;">
          <input type="checkbox" name="daftar_baru" value="1" style="margin-top:3px;" @checked(old('daftar_baru'))>
          <span>Belum punya akun? Centang untuk buat akun baru dengan username ini (password minimal 6 karakter)</span>
        </label>
        <button class="btn btn-orange btn-block" type="submit">Masuk</button>
        <button type="button" class="lp-back" id="btnBack">Bukan kamu? Ganti akun</button>
      </div>
    </form>
    <p class="helper-link">Login pertama kali? Ketik username yang kamu mau, klik Lanjut, isi password (minimal 6 karakter), lalu centang &quot;buat akun baru&quot; sebelum klik Masuk. Setelah itu kamu diminta lengkapi profil sekali saja. Login berikutnya, cukup username &amp; password yang sama.<br><br>Coba: <b>rahmat / siswa123</b> (siswa contoh).<br><br>Ada kendala masuk? <a href="#kontak" class="lp-inline">Hubungi admin</a>.</p>
  </div>
</header>
</div>

<section class="lp-sec" id="alur">
  <div class="lp-wrap">
    <h2 class="lp-h">Dari butuh sampai kembali, empat langkah.</h2>
    <p class="lp-sub">Tidak perlu antre mencatat di buku. Semua tercatat di sistem, dan kamu bisa cek statusnya kapan saja.</p>
    <div class="lp-steps">
      <div class="lp-step"><h3>Cari barang</h3><p>Buka Data Inventaris, lihat stok dan lokasi barang yang kamu butuhkan.</p></div>
      <div class="lp-step"><h3>Ajukan</h3><p>Isi jumlah dan catatan, lalu kirim. Kamu langsung mendapat nomor struk.</p></div>
      <div class="lp-step"><h3>Ambil barang</h3><p>Datang ke admin. Barang dicatat sebagai dipinjam setelah admin menyetujui.</p></div>
      <div class="lp-step"><h3>Kembalikan</h3><p>Serahkan barang ke admin. Status berubah jadi Selesai setelah disetujui.</p></div>
    </div>
  </div>
</section>

<section class="lp-sec lp-dark" id="alat">
  <div class="lp-wrap lp-cats">
    <div>
      <h2 class="lp-h">Apa saja yang bisa dipinjam?</h2>
      <p class="lp-sub">Inventaris dikelompokkan supaya mudah dicari. Stok selalu diperbarui admin.</p>
    </div>
    <div>
      <div class="lp-cat"><b>Perangkat Komputer</b><span>Laptop HP, flashdisk, mouse, keyboard, monitor</span></div>
      <div class="lp-cat"><b>Elektronik</b><span>Proyektor, Arduino Uno, speaker, mic + sound</span></div>
      <div class="lp-cat"><b>Alat Praktikum</b><span>Obeng mini, solder, bor tembok, bor mini listrik</span></div>
    </div>
  </div>
</section>

<section class="lp-sec" id="peran">
  <div class="lp-wrap">
    <h2 class="lp-h">Satu sistem, dua peran.</h2>
    <p class="lp-sub">Tampilan dan akses menyesuaikan siapa yang masuk.</p>
    <div class="lp-roles">
      <div class="lp-role">
        <h3>Siswa dan guru</h3>
        <p>Untuk yang butuh meminjam alat.</p>
        <ul>
          <li>Melihat stok dan lokasi barang secara langsung</li>
          <li>Mengajukan peminjaman dan mendapat nomor struk</li>
          <li>Memantau status: menunggu, dipinjam, selesai, atau ditolak</li>
          <li>Membuka kembali struk kapan saja</li>
        </ul>
      </div>
      <div class="lp-role alt">
        <h3>Admin</h3>
        <p>Untuk yang mengelola alat jurusan.</p>
        <ul>
          <li>Menambah barang dan stok, mengubah detail, atau menghapus</li>
          <li>Menyetujui saat barang diambil dan saat dikembalikan</li>
          <li>Melihat data peminjam, termasuk kelas dan nomor WhatsApp</li>
          <li>Memantau ringkasan stok dan transaksi di dashboard</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<section class="lp-sec" id="bantuan" style="padding-top:0;">
  <div class="lp-wrap lp-faq">
    <div>
      <h2 class="lp-h">Pertanyaan yang sering muncul</h2>
      <p class="lp-sub">Belum terjawab? Tanyakan langsung ke admin jurusan.</p>
    </div>
    <div>
      <details>
        <summary>Kenapa saya diminta lengkapi profil?</summary>
        <p>Nama, kelas, dan nomor WhatsApp ditampilkan ke admin saat kamu mengajukan, supaya admin tahu siapa peminjamnya dan bisa menghubungi kalau perlu. Kamu cukup mengisinya sekali.</p>
      </details>
      <details>
        <summary>Tombol ajukan tidak bisa dipakai, kenapa?</summary>
        <p>Stok barang itu sedang habis. Cek lagi nanti, stok bertambah saat barang dikembalikan atau admin menambah stok.</p>
      </details>
      <details>
        <summary>Kapan barang boleh diambil?</summary>
        <p>Setelah mengajukan, datanglah ke admin untuk mengambilnya. Barang tercatat sebagai dipinjam ketika admin menyetujui pengambilan.</p>
      </details>
      <details>
        <summary>Pengajuan saya ditolak, bagaimana?</summary>
        <p>Statusnya tampil sebagai Ditolak di halaman Pengajuan Saya. <a href="#kontak" class="lp-inline">Hubungi admin</a> untuk menanyakan alasannya, lalu ajukan ulang jika perlu.</p>
      </details>
      <details>
        <summary>Saya lupa password.</summary>
        <p><a href="#kontak" class="lp-inline">Hubungi admin</a> untuk membantu mengatur ulang akunmu.</p>
      </details>
    </div>
  </div>
</section>

<section class="lp-sec" id="kontak" style="padding-top:0;">
  <div class="lp-wrap">
    <div class="lp-contact">
      <div>
        <h2 class="lp-h">Hubungi admin</h2>
        <p class="lp-sub">Untuk lupa password, pengajuan yang ditolak, atau pertanyaan seputar alat.</p>
      </div>
      <div class="lp-contact-box">
        <span class="lp-contact-name">{{ $kontak['nama'] }}</span>
        <b class="lp-contact-num">{{ $kontak['wa'] }}</b>
        <a class="btn btn-orange" href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener">Chat via WhatsApp</a>
      </div>
    </div>
  </div>
</section>

<div class="lp-wrap">
  <div class="lp-cta">
    <h2>Butuh alat untuk praktik hari ini?</h2>
    <a href="#masuk" class="btn btn-orange">Masuk sekarang</a>
  </div>
  <footer class="lp-foot">
    <span>SIPINTAR &middot; SMK Negeri 5 Surakarta</span>
    <span>Sistem peminjaman inventaris jurusan</span>
  </footer>
</div>

<script>
(function () {
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---- form dua langkah ---- */
  var panes = document.querySelectorAll('.lp-pane');
  var inUser = document.getElementById('inUser'), inPass = document.getElementById('inPass');
  function show(n) {
    panes.forEach(function (p) { p.classList.toggle('is-active', p.dataset.pane == n); });
    (n == 1 ? inUser : inPass).focus();
  }
  function next() {
    var v = inUser.value.trim();
    if (!v) {
      inUser.classList.remove('lp-shake'); void inUser.offsetWidth; inUser.classList.add('lp-shake'); inUser.focus();
      return;
    }
    document.getElementById('helloName').textContent = v;
    show(2);
  }
  document.getElementById('btnNext').addEventListener('click', next);
  inUser.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); next(); } });
  document.getElementById('btnBack').addEventListener('click', function () { show(1); });
  document.getElementById('btnEye').addEventListener('click', function () {
    var sembunyi = inPass.type === 'password';
    inPass.type = sembunyi ? 'text' : 'password';
    this.textContent = sembunyi ? 'sembunyi' : 'tampil';
  });
  @if($errors->any() && old('username'))
  document.getElementById('helloName').textContent = @json(old('username'));
  show(2);
  @endif

  /* ---- animasi saat scroll ---- */
  document.querySelectorAll('.lp-h,.lp-sub,.lp-cta,.lp-contact-box,.lp-foot').forEach(function (el) { el.setAttribute('data-reveal', ''); });
  document.querySelectorAll('.lp-steps,.lp-cats > div:last-child,.lp-roles,.lp-faq > div:last-child').forEach(function (box) {
    Array.prototype.forEach.call(box.children, function (el, i) {
      el.setAttribute('data-reveal', '');
      el.style.setProperty('--d', (i * 0.12) + 's');
    });
  });
  var items = document.querySelectorAll('[data-reveal]');
  if (reduced || !('IntersectionObserver' in window)) {
    items.forEach(function (el) { el.classList.add('in'); });
  } else {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { threshold: 0.15, rootMargin: '0px 0px -6% 0px' });
    items.forEach(function (el) { io.observe(el); });
  }

  /* ---- garis progres, bayangan nav, parallax ---- */
  var bar = document.getElementById('lpProgress'), nav = document.getElementById('lpNav'), hero = document.querySelector('.lp-herobg'), tick = false;
  function onScroll() {
    var y = window.scrollY, max = document.documentElement.scrollHeight - window.innerHeight;
    bar.style.transform = 'scaleX(' + (max > 0 ? y / max : 0) + ')';
    nav.classList.toggle('scrolled', y > 8);
    if (!reduced && y < 900) hero.style.setProperty('--py', (y * 0.18) + 'px');
    tick = false;
  }
  window.addEventListener('scroll', function () { if (!tick) { tick = true; requestAnimationFrame(onScroll); } }, { passive: true });
  onScroll();
})();
</script>
</body>
</html>