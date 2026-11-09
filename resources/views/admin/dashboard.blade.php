@extends('layouts.app')
@section('title', 'Dashboard & Laporan - SIPINTAR Admin')

@section('content')
  @php
    $semua = \App\Models\Peminjaman::with(['barang', 'peminjam'])->get();

    // pengajuan per minggu (8 minggu terakhir)
    $tren = [];
    for ($i = 7; $i >= 0; $i--) {
        $awal = \Carbon\Carbon::now()->startOfWeek()->subWeeks($i);
        $akhir = $awal->copy()->endOfWeek();
        $tren[] = [
            'label' => $awal->format('j M'),
            'n' => $semua->filter(function ($p) use ($awal, $akhir) {
                return $p->tanggal_pengajuan && \Carbon\Carbon::parse($p->tanggal_pengajuan)->between($awal, $akhir);
            })->count(),
        ];
    }
    $trenMaks = max(1, collect($tren)->max('n'));

    // barang paling sering dipinjam
    $topBarang = $laporan->groupBy(function ($p) { return optional($p->barang)->nama ?: 'Tanpa nama'; })
        ->map(function ($g) { return $g->sum('jumlah'); })->sortDesc()->take(5);
    $topMaks = max(1, (int) $topBarang->max());

    // kategori terfavorit (donat)
    $kategori = $laporan->groupBy(function ($p) { return optional($p->barang)->kategori ?: 'Lainnya'; })
        ->map(function ($g) { return $g->count(); })->sortDesc();
    $katTotal = max(1, $kategori->sum());
    $warna = ['#16473a', '#e0873f', '#6fae94', '#d6ac4a', '#5b7a8c'];
    $acc = 0; $stops = [];
    foreach ($kategori as $k => $n) {
        $mulai = $acc; $acc += $n / $katTotal * 100;
        $stops[] = $warna[count($stops) % 5] . ' ' . round($mulai, 2) . '% ' . round($acc, 2) . '%';
    }
    $donat = $stops ? 'conic-gradient(' . implode(',', $stops) . ')' : 'var(--line)';

    // yang perlu ditagih
    $terlambat = $semua->filter(function ($p) { return \App\Support\Pinjam::telat($p) > 0; })
        ->sortByDesc(function ($p) { return \App\Support\Pinjam::telat($p); });
  @endphp

  <div class="page-head"><div><h1>Dashboard &amp; Laporan</h1><p>Ringkasan stok, transaksi, dan hal yang perlu ditindaklanjuti.</p></div></div>

  <div class="stats-grid">
    <div class="stat-box"><div class="num">{{ $totalUnit }}</div><div class="lbl">Total unit barang</div></div>
    <div class="stat-box"><div class="num">{{ $tersedia }}</div><div class="lbl">Jenis barang tersedia</div></div>
    <div class="stat-box"><div class="num">{{ $dipinjam }}</div><div class="lbl">Sedang dipinjam</div></div>
    <div class="stat-box"><div class="num">{{ $menunggu }}</div><div class="lbl">Menunggu approve</div></div>
    <div class="stat-box"><div class="num">{{ $sedikit }}</div><div class="lbl">Stok sedikit</div></div>
    <div class="stat-box"><div class="num">{{ $habis }}</div><div class="lbl">Stok habis</div></div>
    <div class="stat-box {{ $terlambat->count() ? 'stat-merah' : '' }}"><div class="num">{{ $terlambat->count() }}</div><div class="lbl">Terlambat kembali</div></div>
  </div>

  @php
    $qs = array_filter([
      'dari' => $dari ? $dari->format('Y-m-d') : null,
      'sampai' => $sampai ? $sampai->format('Y-m-d') : null,
    ]);
  @endphp
  <div class="panel">
    <h3>Laporan &amp; Export</h3>
    <form method="GET" action="{{ route('admin.dashboard') }}" class="lap-form">
      <div class="field"><label>Dari tanggal</label><input type="date" name="dari" value="{{ $dari ? $dari->format('Y-m-d') : '' }}"></div>
      <div class="field"><label>Sampai tanggal</label><input type="date" name="sampai" value="{{ $sampai ? $sampai->format('Y-m-d') : '' }}"></div>
      <div class="btn-row">
        <button class="btn btn-orange" type="submit">Terapkan</button>
        @if($dari || $sampai)<a class="btn btn-ghost" href="{{ route('admin.dashboard') }}">Reset filter</a>@endif
      </div>
    </form>
    <p style="font-size:12.5px;color:var(--muted);margin:14px 0;">
      Periode: <b>{{ $dari ? $dari->translatedFormat('j M Y') : 'awal' }}</b> s/d <b>{{ $sampai ? $sampai->translatedFormat('j M Y') : 'sekarang' }}</b>
      &middot; {{ $laporan->count() }} pengajuan
      ({{ $laporan->where('status', 'selesai')->count() }} selesai,
      {{ $laporan->where('status', 'dipinjam')->count() }} dipinjam,
      {{ $laporan->where('status', 'menunggu')->count() }} menunggu,
      {{ $laporan->where('status', 'ditolak')->count() }} ditolak)
      <br>Grafik &ldquo;Barang paling sering dipinjam&rdquo; dan &ldquo;Kategori terfavorit&rdquo; di bawah ikut periode ini.
    </p>
    <div class="lap-aksi">
      <a class="btn btn-ghost" target="_blank" href="{{ route('admin.laporan.cetak', $qs) }}">Cetak / Simpan PDF</a>
      <a class="btn btn-ghost" href="{{ route('admin.laporan.csv', $qs) }}">Export ke Excel (CSV)</a>
    </div>
  </div>

  <div class="dash-grid">
    <div class="panel">
      <h3>Pengajuan 8 minggu terakhir</h3>
      <div class="vbars">
        @foreach($tren as $t)
          <div class="vbar"><b>{{ $t['n'] }}</b><i data-h="{{ round($t['n'] / $trenMaks * 110) }}"></i><span>{{ $t['label'] }}</span></div>
        @endforeach
      </div>
    </div>

    <div class="panel">
      <h3>Barang paling sering dipinjam</h3>
      @forelse($topBarang as $nama => $jml)
        <div class="hbar">
          <div class="hbar-top"><span>{{ $nama }}</span><b>{{ $jml }} unit</b></div>
          <div class="hbar-track"><i data-w="{{ round($jml / $topMaks * 100) }}"></i></div>
        </div>
      @empty
        <div class="empty">Belum ada data peminjaman.</div>
      @endforelse
    </div>

    <div class="panel">
      <h3>Kategori terfavorit</h3>
      @if($kategori->count())
        <div class="donat-wrap">
          <div class="donat" style="background:{{ $donat }};"><span><b>{{ $kategori->sum() }}</b>pengajuan</span></div>
          <div class="kleg" style="grid-template-columns:1fr;">
            @foreach($kategori as $k => $n)
              <div><i style="background:{{ $warna[$loop->index % 5] }}"></i><span>{{ $k }}</span><b>{{ $n }}</b></div>
            @endforeach
          </div>
        </div>
      @else
        <div class="empty">Belum ada data peminjaman.</div>
      @endif
    </div>

    <div class="panel">
      <h3>Perlu ditagih</h3>
      @forelse($terlambat as $p)
        @php
          $telat = \App\Support\Pinjam::telat($p);
          $pesan = "Halo {$p->peminjam->name}, {$p->barang->nama} yang kamu pinjam sudah lewat batas kembali selama {$telat} hari. Mohon segera dikembalikan ke admin ya. Terima kasih.";
        @endphp
        <div class="tagih">
          <div><b>{{ $p->peminjam->name }}</b><span>{{ $p->barang->nama }} &middot; terlambat {{ $telat }} hari</span></div>
          <form id="formNotif{{ $p->id }}" method="POST" action="{{ route('admin.pengguna.notif', $p->peminjam) }}" data-nama="{{ $p->peminjam->name }}" hidden>@csrf<input type="hidden" name="pesan" value="{{ $pesan }}"></form>
          <div class="tagih-aksi">
            <a class="btn btn-wa-telat btn-sm" target="_blank" rel="noopener" href="{{ \App\Support\Pinjam::wa($p->peminjam->no_wa, $pesan) }}">Ingatkan</a>
            <button type="button" class="btn btn-ghost btn-sm" onclick="kirimNotifWeb(this, 'formNotif{{ $p->id }}')">Notif web</button>
          </div>
        </div>
      @empty
        <div class="empty">Tidak ada yang terlambat. Aman.</div>
      @endforelse
    </div>
  </div>

  <div class="panel">
    <h3>Pengajuan Terbaru</h3>
    <div class="list">
      @forelse($recent as $p)
        <div class="row-card" style="cursor:default;">
          <div class="info"><h3>{{ $p->barang->nama }}</h3><p>{{ $p->peminjam->name }} &middot; {{ optional($p->tanggal_pengajuan)->translatedFormat('j M Y') }}</p></div>
          @if($p->status === 'menunggu')<span class="badge badge-amber">Menunggu approve</span>@endif
          @if($p->status === 'dipinjam')<span class="badge badge-red">Belum Dikembalikan</span>@endif
          @if($p->status === 'selesai')<span class="badge badge-green">Selesai &#10003;</span>@endif
        </div>
      @empty
        <div class="empty">Belum ada pengajuan.</div>
      @endforelse
    </div>
  </div>
@endsection

@section('scripts')
@include('partials.notif-website')
<script>
  requestAnimationFrame(function () {
    requestAnimationFrame(function () {
      document.querySelectorAll('.vbar i').forEach(function (i) { i.style.height = i.dataset.h + 'px'; });
      document.querySelectorAll('.hbar-track i').forEach(function (i) { i.style.width = i.dataset.w + '%'; });
    });
  });
</script>
@endsection