@php
  $daftarKategori = $barang->pluck('kategori')->filter()->unique()->values();
@endphp

@if(empty($sembunyiRingkasan))
<div class="readout">
  <div><b>{{ $barang->count() }}</b><span>Jenis barang</span></div>
  <div><b>{{ $barang->sum('stok') }}</b><span>Total unit</span></div>
  <div><b>{{ $barang->where('stok', '>', 2)->count() }}</b><span>Stok aman</span></div>
  <div><b>{{ $barang->where('stok', '<=', 2)->count() }}</b><span>Sedikit atau habis</span></div>
</div>
@endif

<div class="toolbar">
  <form method="GET" class="search-wrap" onsubmit="">
    <input class="search" id="cariBarang" type="text" name="q" value="{{ $q }}" placeholder="Cari barang..." autocomplete="off">
    <kbd class="kbd">/</kbd>
  </form>
  @if($daftarKategori->count() > 1)
    <div class="chips">
      <button type="button" class="chip active" data-kat="" onclick="filterKategori(this)">Semua</button>
      @foreach($daftarKategori as $k)
        <button type="button" class="chip" data-kat="{{ $k }}" onclick="filterKategori(this)">{{ $k }}</button>
      @endforeach
    </div>
  @endif
</div>

<script>
  var katAktif = '', kata = '';

  function terapkanFilter() {
    var tampil = 0;
    document.querySelectorAll('.brg, .ledger-table .lrow').forEach(function (t) {
      var cocokKat = !katAktif || t.dataset.kategori === katAktif;
      var cocokNama = !kata || (t.dataset.nama || '').toLowerCase().indexOf(kata) !== -1;
      var cocok = cocokKat && cocokNama;
      t.style.display = cocok ? '' : 'none';
      if (cocok) tampil++;
    });
    // judul kelompok ikut hilang kalau semua barang di dalamnya tersaring
    document.querySelectorAll('.grup').forEach(function (g) {
      var ada = 0;
      g.querySelectorAll('.brg').forEach(function (b) { if (b.style.display !== 'none') ada++; });
      g.style.display = ada ? '' : 'none';
    });
    var kosong = document.getElementById('tagKosong');
    if (kosong) kosong.style.display = tampil ? 'none' : 'block';
  }

  function filterKategori(btn) {
    document.querySelectorAll('.chips .chip').forEach(function (c) { c.classList.remove('active'); });
    btn.classList.add('active');
    katAktif = btn.dataset.kat;
    terapkanFilter();
  }

  (function () {
    var cari = document.getElementById('cariBarang');
    // hasil langsung berubah saat mengetik
    cari.addEventListener('input', function () { kata = this.value.trim().toLowerCase(); terapkanFilter(); });
    // tekan "/" untuk langsung mengetik di kotak cari
    document.addEventListener('keydown', function (e) {
      var tag = document.activeElement ? document.activeElement.tagName : '';
      if (e.key === '/' && !/INPUT|TEXTAREA|SELECT/.test(tag)) { e.preventDefault(); cari.focus(); }
    });
  })();
</script>