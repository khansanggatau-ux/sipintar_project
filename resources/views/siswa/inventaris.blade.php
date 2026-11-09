@extends('layouts.app')
@section('title', 'Beranda - SIPINTAR Siswa')

@section('content')
  @php
    $u = auth()->user();
    $namaDepan = explode(' ', trim($u->name))[0];
    $siap = $barang->where('stok', '>', 0)->count();
  @endphp

  <section class="beranda" id="beranda">
    <svg class="beranda-trace" viewBox="0 0 1200 420" preserveAspectRatio="xMaxYMid slice" aria-hidden="true">
      <g class="jalur">
        <path d="M1200 60H960L910 110H700"/><circle cx="688" cy="110" r="7"/>
        <path d="M1200 190H1040L990 240V330"/><circle cx="990" cy="342" r="7"/>
        <path d="M1200 340H880L830 290H640"/><circle cx="628" cy="290" r="7"/>
        <path d="M520 420V350L570 300H760"/><circle cx="772" cy="300" r="7"/>
        <path d="M300 0V60L350 110H520"/><circle cx="532" cy="110" r="7"/>
      </g>
      <g class="denyut">
        <path pathLength="600" d="M1200 60H960L910 110H700"/>
        <path pathLength="600" d="M1200 190H1040L990 240V330"/>
        <path pathLength="600" d="M1200 340H880L830 290H640"/>
        <path pathLength="600" d="M520 420V350L570 300H760"/>
        <path pathLength="600" d="M300 0V60L350 110H520"/>
      </g>
    </svg>
    <div class="beranda-glow"></div>
    <div class="beranda-body">
      <p class="beranda-hai" id="salam">halo</p>
      <h1>{{ $namaDepan }}, mau pinjam apa hari ini?</h1>
      <p class="beranda-sub">{{ $u->tipe ?? 'Siswa' }}@if($u->kelas) &middot; {{ $u->kelas }}@endif &middot; pilih alat, ajukan, lalu ambil di admin.</p>
      <div class="beranda-stats">
        <div><b data-count="{{ $barang->count() }}">{{ $barang->count() }}</b><span>Jenis barang</span></div>
        <div><b data-count="{{ $barang->sum('stok') }}">{{ $barang->sum('stok') }}</b><span>Total unit</span></div>
        <div><b data-count="{{ $siap }}">{{ $siap }}</b><span>Siap dipinjam</span></div>
      </div>
    </div>
  </section>

  @php
    $grup = $barang->groupBy('kategori')->filter(function ($g, $k) { return $k !== '' && $k !== null; });
    $menipis = $barang->where('stok', '>', 0)->where('stok', '<=', 2)->sortBy('stok');
    $langkah = [
      ['Pilih alat', 'Cari di daftar atau pakai filter kategori. Stok dan kondisi alat langsung terlihat di setiap baris.'],
      ['Ajukan', 'Isi jumlah dan catatan, lalu kirim. Nomor struk muncul seketika dan bisa dibuka lagi di menu Pengajuan Saya.'],
      ['Ambil', 'Datang ke admin dengan nomor struk. Barang tercatat dipinjam setelah admin menyetujui pengambilan.'],
      ['Kembalikan', 'Serahkan barang ke admin. Status berubah jadi Selesai setelah admin menyetujui pengembalian.'],
    ];
  @endphp

  @if($grup->count() > 1)
    <section class="blok">
      <div class="blok-head"><h2 class="sec-title">Cari lewat kategori</h2><p>Pilih satu, daftar di bawah langsung menyesuaikan.</p></div>
      <div class="kat-grid">
        @foreach($grup as $nama => $items)
          <button type="button" class="kat-tile" data-kat="{{ $nama }}" onclick="pilihKategori(this)">
            <span class="kat-icons">@foreach($items->take(3) as $it)<span class="item-icon">{!! $it->icon !!}</span>@endforeach</span>
            <b>{{ $nama }}</b>
            <small>{{ $items->count() }} jenis &middot; {{ $items->sum('stok') }} unit</small>
          </button>
        @endforeach
      </div>
    </section>
  @endif

  @if($menipis->count())
    <div class="menipis">
      <div class="menipis-head"><span class="denyut-dot"></span><b>Hampir habis</b><span>buruan ajukan sebelum kehabisan</span></div>
      <div class="menipis-list">
        @foreach($menipis as $it)
          <button type="button" class="menipis-item" onclick="bukaBarang({{ $it->id }})"><span>{{ $it->nama }}</span><em>sisa {{ $it->stok }}</em></button>
        @endforeach
      </div>
    </div>
  @endif

  <div class="page-head" id="daftar">
    <div><h2 class="sec-title">Daftar alat</h2><p>Klik barang untuk lihat detail dan ajukan peminjaman.</p></div>
  </div>

  @include('partials.barang-ringkasan', ['sembunyiRingkasan' => true])

  @php $kelompok = $barang->groupBy(function ($b) { return $b->kategori ?: 'Lainnya'; })->sortKeys(); @endphp
  <div class="daftar">
    @forelse($kelompok as $namaKelompok => $items)
      <section class="grup">
        <div class="grup-head"><b>{{ $namaKelompok }}</b><span>{{ $items->count() }} jenis</span></div>
        @foreach($items as $item)
          @include('partials.barang-kartu', ['item' => $item, 'aksi' => 'openAjukan(this)'])
        @endforeach
      </section>
    @empty
      <div class="empty">Belum ada barang di inventaris.</div>
    @endforelse
    <div class="empty" id="tagKosong" style="display:none;">Tidak ada barang yang cocok.</div>
  </div>

  <section class="blok" id="cara" style="margin-top:36px;">
    <div class="blok-head"><h2 class="sec-title">Cara meminjam</h2><p>Empat langkah dari pilih alat sampai dikembalikan.</p></div>
    <div class="stepper">
      <div class="stepper-nav">
        <div class="stepper-line"><div class="stepper-fill" id="stepFill"></div></div>
        @foreach($langkah as $i => $l)
          <button type="button" class="stp {{ $i === 0 ? 'on' : '' }}" data-i="{{ $i }}"><i>{{ $i + 1 }}</i><span>{{ $l[0] }}</span></button>
        @endforeach
      </div>
      <div class="stepper-card" id="stepCard"><h3 id="stepJudul">{{ $langkah[0][0] }}</h3><p id="stepIsi">{{ $langkah[0][1] }}</p></div>
    </div>
  </section>
@endsection

@section('modals')
  <div class="overlay" id="modalAjukan">
    <div class="modal">
      <div class="modal-head">
        <div style="display:flex;gap:14px;align-items:center;">
          <div class="item-icon" id="ajukanIcon"></div>
          <div><h3 id="ajukanNama"></h3><p id="ajukanLokasi"></p></div>
        </div>
        <button type="button" class="modal-close" onclick="closeModal('modalAjukan')">&times;</button>
      </div>

      <div class="detail-row"><span>Kategori</span><span id="ajukanKategori"></span></div>
      <div class="detail-row"><span>Jumlah stok</span><span id="ajukanStok"></span></div>
      <div class="detail-row"><span>Kondisi</span><span id="ajukanKondisi"></span></div>

      @error('jumlah')
        <p style="color:var(--red);font-size:12.5px;margin:12px 0 0;">{{ $message }}</p>
      @enderror

      <form id="ajukanForm" method="POST" action="" style="margin-top:16px;" onsubmit="return mintaKonfirmasi(event)">
        @csrf
        <input type="hidden" name="barang_id" id="ajukanBarangId">
        <div class="field"><label>Jumlah</label><input type="number" name="jumlah" id="ajukanJumlah" value="1" min="1" max="1" readonly><p class="field-hint">Maksimal 1 unit per barang untuk setiap akun.</p></div>
        <div class="field">
          <label>Lama pinjam</label>
          <div class="durasi">
            @foreach([1, 2, 3, 5, 7] as $d)
              <label class="dur"><input type="radio" name="durasi_hari" value="{{ $d }}" {{ $d === 3 ? 'checked' : '' }}><span>{{ $d }} hari</span></label>
            @endforeach
          </div>
          <p class="durasi-info" id="durasiInfo"></p>
        </div>
        <div class="field"><label>Catatan (opsional)</label><textarea rows="3" name="catatan" placeholder="cth. dipakai untuk latihan ujian praktik">{{ old('catatan') }}</textarea></div>
        <button class="btn btn-orange btn-block" type="submit">Kirim Pengajuan</button>
      </form>

      <p id="ajukanHabis" style="display:none;margin:16px 0 0;padding:12px 14px;border-radius:var(--r-md);background:var(--red-bg);color:var(--red);font-size:13px;font-weight:600;">
        Stok barang ini sedang habis, belum bisa diajukan.
      </p>

      <p id="ajukanAktif" style="display:none;margin:16px 0 0;padding:12px 14px;border-radius:var(--r-md);background:var(--amber-bg);color:#8a5a08;font-size:13px;font-weight:600;"></p>

      <form id="kabariForm" method="POST" action="" style="display:none;margin-top:12px;">
        @csrf
        <button class="btn btn-ghost btn-block" type="submit" id="kabariTombol">Kabari aku kalau tersedia</button>
        <p style="font-size:12px;color:var(--muted);margin:8px 0 0;">Kamu dikabari lewat lonceng notifikasi begitu barang ini ada lagi.</p>
      </form>
    </div>
  </div>

  @php $pengguna = auth()->user(); @endphp
  <style>
    .kf-judul{margin:16px 0 2px;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:var(--muted);}
    .kf-note{margin:16px 0 0;padding:12px 14px;border-radius:var(--r-md);background:var(--amber-bg);font-size:12.5px;line-height:1.55;}
    .kf-aksi{display:flex;gap:10px;margin-top:18px;}
    .kf-aksi .btn{flex:1;}
    @media(max-width:420px){.kf-aksi{flex-direction:column-reverse;}}
  </style>
  <div class="overlay" id="modalKonfirmasi">
    <div class="modal">
      <div class="modal-head">
        <div><h3>Yakin mau pinjam barang ini?</h3><p>Cek lagi datanya sebelum dikirim ke admin.</p></div>
        <button type="button" class="modal-close" onclick="closeModal('modalKonfirmasi')">&times;</button>
      </div>

      <p class="kf-judul">Peminjam</p>
      <div class="detail-row"><span>Nama</span><span>{{ $pengguna->name }}</span></div>
      <div class="detail-row"><span>Status</span><span>{{ $pengguna->tipe ?? 'Siswa' }}</span></div>
      @if($pengguna->kelas)<div class="detail-row"><span>Kelas</span><span>{{ $pengguna->kelas }}</span></div>@endif
      <div class="detail-row"><span>No. WhatsApp</span><span>{{ $pengguna->no_wa ?: '-' }}</span></div>

      <p class="kf-judul">Barang</p>
      <div class="detail-row"><span>Barang</span><span id="kfBarang"></span></div>
      <div class="detail-row"><span>Lokasi ambil</span><span id="kfLokasi"></span></div>
      <div class="detail-row"><span>Jumlah</span><span id="kfJumlah"></span></div>
      <div class="detail-row"><span>Lama pinjam</span><span id="kfDurasi"></span></div>
      <div class="detail-row" id="kfCatatanRow"><span>Catatan</span><span id="kfCatatan"></span></div>

      <p class="kf-note">Dengan mengirim, kamu bertanggung jawab atas barang ini sampai dikembalikan ke admin dalam keadaan baik dan tepat waktu.</p>

      <div class="kf-aksi">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalKonfirmasi')">Cek lagi</button>
        <button type="button" class="btn btn-orange" id="kfKirim" onclick="kirimPengajuan()">Ya, saya yakin</button>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
  var AJUKAN_URL = @json(route('siswa.inventaris.ajukan', '__ID__'));
  var KABARI_URL = @json(route('siswa.inventaris.kabari', '__ID__'));
  var ANTRIAN = @json($antrianIds);

  function openAjukan(card) {
    var d = card.dataset, stok = parseInt(d.stok, 10) || 0;
    document.getElementById('ajukanIcon').innerHTML = card.querySelector('.item-icon').innerHTML;
    document.getElementById('ajukanNama').textContent = d.nama;
    document.getElementById('ajukanLokasi').textContent = d.lokasi;
    document.getElementById('ajukanKategori').textContent = d.kategori || '-';
    document.getElementById('ajukanStok').textContent = stok + ' unit';
    document.getElementById('ajukanBarangId').value = d.id;
    var kon = d.kondisi || 'baik';
    document.getElementById('ajukanKondisi').textContent = (KONDISI[kon] || 'Baik') + (d.ket ? ' - ' + d.ket : '');

    var jumlah = document.getElementById('ajukanJumlah');
    jumlah.max = 1;
    jumlah.value = 1;
    document.querySelector('input[name=durasi_hari][value="3"]').checked = true;
    infoDurasi();

    document.getElementById('ajukanForm').action = AJUKAN_URL.replace('__ID__', d.id);
    var aktif = d.aktif || '';
    var pa = document.getElementById('ajukanAktif');
    pa.style.display = aktif ? 'block' : 'none';
    pa.textContent = aktif === 'dipinjam'
      ? 'Kamu sedang meminjam barang ini. Kembalikan dulu ke admin sebelum bisa meminjam lagi (1 akun hanya boleh 1 peminjaman per barang).'
      : 'Pengajuanmu untuk barang ini masih menunggu persetujuan admin. 1 akun hanya boleh 1 peminjaman per barang.';
    document.getElementById('ajukanForm').style.display = (stok > 0 && !aktif) ? '' : 'none';
    document.getElementById('ajukanHabis').style.display = (stok <= 0 && !aktif) ? 'block' : 'none';

    var kf = document.getElementById('kabariForm');
    kf.style.display = (stok > 0 || aktif) ? 'none' : 'block';
    kf.action = KABARI_URL.replace('__ID__', d.id);
    document.getElementById('kabariTombol').textContent =
      ANTRIAN.indexOf(parseInt(d.id, 10)) !== -1 ? 'Batal kabari aku' : 'Kabari aku kalau tersedia';
    openModal('modalAjukan');
  }

  @if($errors->any() && old('barang_id'))
    window.addEventListener('DOMContentLoaded', function () {
      var card = document.querySelector('.brg[data-id="{{ old('barang_id') }}"]');
      if (card) {
        openAjukan(card);
        document.getElementById('ajukanJumlah').value = 1;
        var dr = document.querySelector('input[name=durasi_hari][value="' + @json((string) old('durasi_hari', 3)) + '"]');
        if (dr) { dr.checked = true; infoDurasi(); }
      }
    });
  @endif

  /* ===== popup konfirmasi sebelum kirim pengajuan ===== */
  var KONDISI = { baik: 'Baik', rusak_ringan: 'Rusak Ringan', rusak_berat: 'Rusak Berat' };

  function mintaKonfirmasi(e) {
    e.preventDefault();
    var f = document.getElementById('ajukanForm');
    if (!f.reportValidity()) return false;

    var dur = document.querySelector('input[name=durasi_hari]:checked');
    var catatan = f.querySelector('textarea[name=catatan]').value.trim();
    document.getElementById('kfBarang').textContent = document.getElementById('ajukanNama').textContent;
    document.getElementById('kfLokasi').textContent = document.getElementById('ajukanLokasi').textContent;
    document.getElementById('kfJumlah').textContent = document.getElementById('ajukanJumlah').value + ' unit';
    document.getElementById('kfDurasi').textContent = (dur ? dur.value : 3) + ' hari (sejak diambil)';
    document.getElementById('kfCatatan').textContent = catatan;
    document.getElementById('kfCatatanRow').style.display = catatan ? '' : 'none';

    var b = document.getElementById('kfKirim');
    b.disabled = false; b.textContent = 'Ya, saya yakin';
    openModal('modalKonfirmasi');
    return false;
  }

  function kirimPengajuan() {
    var b = document.getElementById('kfKirim');
    b.disabled = true; b.textContent = 'Mengirim...';
    document.getElementById('ajukanForm').submit();
  }

  /* ===== kategori, hampir habis, cara meminjam ===== */
  function pilihKategori(tile) {
    var aktif = !tile.classList.contains('on');
    document.querySelectorAll('.kat-tile').forEach(function (t) { t.classList.remove('on'); });
    if (aktif) tile.classList.add('on');
    var tujuan = aktif ? tile.dataset.kat : '';
    document.querySelectorAll('.chips .chip').forEach(function (c) { if (c.dataset.kat === tujuan) c.click(); });
    document.getElementById('daftar').scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
  document.querySelectorAll('.chips .chip').forEach(function (c) {
    c.addEventListener('click', function () {
      document.querySelectorAll('.kat-tile').forEach(function (t) { t.classList.toggle('on', !!katAktif && t.dataset.kat === katAktif); });
    });
  });

  function infoDurasi() {
    var pilih = document.querySelector('input[name=durasi_hari]:checked');
    var hari = pilih ? parseInt(pilih.value, 10) : 3, d = new Date();
    d.setDate(d.getDate() + hari);
    document.getElementById('durasiInfo').textContent =
      'Dihitung sejak barang diambil. Kalau diambil hari ini, batas kembali ' +
      d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }) + '.';
  }
  document.querySelectorAll('input[name=durasi_hari]').forEach(function (r) { r.addEventListener('change', infoDurasi); });

  function bukaBarang(id) {
    var kartu = document.querySelector('.brg[data-id="' + id + '"]');
    if (kartu) openAjukan(kartu);
  }

  // dibuka dari notifikasi "sudah tersedia": /siswa/inventaris?buka=ID
  (function () {
    var id = new URLSearchParams(location.search).get('buka');
    if (id) bukaBarang(id);
  })();

  (function () {
    var langkah = @json($langkah);
    var tombol = document.querySelectorAll('.stp'), isi = document.getElementById('stepCard'), now = 0, timer = null;
    function atur(i) {
      now = i;
      tombol.forEach(function (t, k) { t.classList.toggle('on', k === i); t.classList.toggle('done', k < i); });
      document.getElementById('stepFill').style.width = (i / (langkah.length - 1) * 100) + '%';
      document.getElementById('stepJudul').textContent = langkah[i][0];
      document.getElementById('stepIsi').textContent = langkah[i][1];
      isi.classList.remove('swap'); void isi.offsetWidth; isi.classList.add('swap');
    }
    tombol.forEach(function (t, k) { t.addEventListener('click', function () { clearInterval(timer); atur(k); }); });
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      timer = setInterval(function () { atur((now + 1) % langkah.length); }, 4500);
    }
  })();

  /* ===== beranda: sapaan, angka berhitung, kilau mengikuti kursor & baris muncul bertahap ===== */
  (function () {
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var hero = document.getElementById('beranda');

    // sapaan sesuai jam di perangkat
    var jam = new Date().getHours();
    document.getElementById('salam').textContent =
      jam < 11 ? 'selamat pagi' : jam < 15 ? 'selamat siang' : jam < 18 ? 'selamat sore' : 'selamat malam';

    if (reduced) return;

    // angka berhitung naik
    hero.querySelectorAll('[data-count]').forEach(function (el) {
      var target = parseInt(el.dataset.count, 10) || 0, mulai = performance.now(), durasi = 1000;
      function langkah(t) {
        var p = Math.min(1, (t - mulai) / durasi);
        el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
        if (p < 1) requestAnimationFrame(langkah);
      }
      el.textContent = '0';
      requestAnimationFrame(langkah);
    });

    // kilau tembaga mengikuti kursor
    hero.addEventListener('mousemove', function (e) {
      var r = hero.getBoundingClientRect();
      hero.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      hero.style.setProperty('--my', (e.clientY - r.top) + 'px');
    });

    // jalur sirkuit bergeser pelan saat scroll
    window.addEventListener('scroll', function () {
      if (window.scrollY < 700) hero.style.setProperty('--py', (window.scrollY * 0.12) + 'px');
    }, { passive: true });

    // baris barang muncul bertahap saat terlihat
    var kartu = document.querySelectorAll('.brg');
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (es) {
        es.forEach(function (e) {
          if (!e.isIntersecting) return;
          var el = e.target;
          el.classList.add('in');
          io.unobserve(el);
          setTimeout(function () { el.classList.remove('rv', 'in'); }, 1100);
        });
      }, { threshold: 0.12 });
      kartu.forEach(function (el, i) {
        el.classList.add('rv');
        el.style.setProperty('--d', ((i % 6) * 0.04) + 's');
        io.observe(el);
      });
    }
  })();
</script>
@endsection