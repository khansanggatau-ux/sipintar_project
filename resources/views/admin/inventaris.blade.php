@extends('layouts.app')
@section('title', 'Data Inventaris - SIPINTAR Admin')

@section('content')
  @php
    $perlu = $barang->where('stok', '<=', 2)->sortBy('stok');
  @endphp

  <div class="page-head">
    <div>
      <h1>Kelola Inventaris</h1>
      <p>{{ $barang->count() }} jenis barang &middot; {{ $barang->sum('stok') }} total unit. Klik baris untuk ubah detail atau tambah stok.</p>
    </div>
    <div class="head-actions">
      <button type="button" class="btn btn-orange" onclick="openModal('modalTambah')">+ Tambah Barang</button>
    </div>
  </div>

  @php
    $komposisi = $barang->groupBy('kategori')->map(function ($g) { return $g->sum('stok'); })->sortDesc();
    $totalKomp = max(1, $komposisi->sum());
    $warna = ['#16473a', '#e0873f', '#6fae94', '#d6ac4a', '#5b7a8c'];
  @endphp

  <div class="admin-top {{ $perlu->count() ? '' : 'satu' }}">
    <div class="komposisi">
      <h3>Komposisi stok</h3>
      <p>Total {{ $komposisi->sum() }} unit, dibagi per kategori.</p>
      <div class="kbar">
        @foreach($komposisi as $kat => $jml)
          <i data-w="{{ round($jml / $totalKomp * 100, 1) }}" style="background:{{ $warna[$loop->index % count($warna)] }}" title="{{ $kat ?: 'Tanpa kategori' }}: {{ $jml }} unit"></i>
        @endforeach
      </div>
      <div class="kleg">
        @foreach($komposisi as $kat => $jml)
          <div><i style="background:{{ $warna[$loop->index % count($warna)] }}"></i><span>{{ $kat ?: 'Tanpa kategori' }}</span><b>{{ $jml }}</b></div>
        @endforeach
      </div>
      <div class="kstat">
        <div><b>{{ $barang->count() }}</b><span>Jenis barang</span></div>
        <div><b>{{ $barang->where('stok', '>', 2)->count() }}</b><span>Stok aman</span></div>
        <div><b>{{ $perlu->count() }}</b><span>Perlu perhatian</span></div>
      </div>
    </div>

    @if($perlu->count())
      <div class="restock">
        <div class="restock-head"><b>Perlu restock</b><span>{{ $perlu->count() }} barang stoknya sedikit atau habis</span></div>
        <div class="restock-list">
          @foreach($perlu as $item)
            <button type="button" class="restock-item {{ $item->stok <= 0 ? 'restock-habis' : '' }}" onclick="openModal('modalEdit{{ $item->id }}')">
              <span>{{ $item->nama }}</span><b>{{ $item->stok }}</b>
            </button>
          @endforeach
        </div>
      </div>
    @endif
  </div>

  @include('partials.barang-ringkasan', ['sembunyiRingkasan' => true])

  <div class="ledger">
    <table class="ledger-table tabel-inv">
      <thead>
        <tr>
          <th>Barang</th><th>Ketersediaan</th><th>Aktivitas</th><th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($barang as $item)
          @php
            $tersedia  = (int) $item->stok;
            $dipinjam  = (int) $item->dipinjam_unit;
            $rusak     = (int) $item->jumlah_rusak;
            $total     = max(1, $tersedia + $dipinjam + $rusak);
            $menunggu  = (int) $item->menunggu_count;
            $terlambat = (int) $item->terlambat_count;
            $antri     = $item->antrian->count();
            $kon       = $item->kondisi ?: 'baik';
            $konLabel  = ['rusak_ringan' => 'Kondisi rusak ringan', 'rusak_berat' => 'Kondisi rusak berat'][$kon] ?? null;

            // warna garis kiri: merah = habis/terlambat, kuning = perlu perhatian, hijau = aman
            if ($terlambat > 0 || $tersedia <= 0) {
                $nada = 'habis';
            } elseif ($tersedia <= 2 || $menunggu > 0 || $rusak > 0 || $konLabel) {
                $nada = 'sedikit';
            } else {
                $nada = 'tersedia';
            }
            $kode = 'BRG-' . str_pad($item->id, 3, '0', STR_PAD_LEFT);
          @endphp
          <tr class="lrow lrow-{{ $nada }}" tabindex="0"
              data-kategori="{{ $item->kategori }}"
              data-nama="{{ $item->nama }}"
              onclick="openModal('modalEdit{{ $item->id }}')"
              onkeydown="if(event.key==='Enter'){openModal('modalEdit{{ $item->id }}');}">
            <td class="td-judul">
              <div class="cell-item">
                <span class="item-icon">{!! $item->icon !!}</span>
                <div>
                  <b>{{ $item->nama }}</b>
                  <span class="sub" @if($item->ket_kondisi) title="{{ $item->ket_kondisi }}" @endif>{{ $kode }}@if($item->kategori) &middot; {{ $item->kategori }}@endif</span>
                </div>
              </div>
            </td>
            <td data-label="Ketersediaan" class="ketersediaan">
              <div class="bar" role="img" aria-label="{{ $tersedia }} tersedia, {{ $dipinjam }} dipinjam, {{ $rusak }} rusak">
                @if($tersedia > 0)<i class="b-ok" style="width:{{ round($tersedia / $total * 100, 1) }}%"></i>@endif
                @if($dipinjam > 0)<i class="b-pinjam" style="width:{{ round($dipinjam / $total * 100, 1) }}%"></i>@endif
                @if($rusak > 0)<i class="b-rusak" style="width:{{ round($rusak / $total * 100, 1) }}%"></i>@endif
              </div>
              <div class="ket-legend">
                <span style="--c:var(--green)"><b>{{ $tersedia }}</b> tersedia</span>
                @if($dipinjam > 0)<span style="--c:var(--accent)"><b>{{ $dipinjam }}</b> dipinjam</span>@endif
                @if($rusak > 0)<span style="--c:var(--red)"><b>{{ $rusak }}</b> rusak</span>@endif
              </div>
            </td>
            <td data-label="Aktivitas">
              @if($terlambat > 0)<span class="tg tg-merah">{{ $terlambat }} terlambat</span>@endif
              @if($tersedia <= 0)<span class="tg tg-merah">Stok habis</span>@endif
              @if($menunggu > 0)<span class="tg tg-kuning">{{ $menunggu }} menunggu persetujuan</span>@endif
              @if($antri > 0)<span class="tg tg-kuning" title="Antri: {{ $item->antrian->map(function ($a) { return optional($a->user)->name ?: '-'; })->implode(', ') }}">{{ $antri }} antri</span>@endif
              @if($konLabel)<span class="tg tg-kuning" @if($item->ket_kondisi) title="{{ $item->ket_kondisi }}" @endif>{{ $konLabel }}</span>@endif
              @if($tersedia > 0 && $tersedia <= 2)<span class="tg tg-kuning">Stok sedikit</span>@endif
              @if($terlambat == 0 && $tersedia > 2 && $menunggu == 0 && $antri == 0 && ! $konLabel)<span class="tg tg-hijau">Aman</span>@endif
            </td>
            <td class="act"><button type="button" class="btn btn-ghost btn-sm" onclick="event.stopPropagation();openModal('modalEdit{{ $item->id }}')">Kelola</button></td>
          </tr>
        @empty
          <tr><td colspan="4"><div class="empty">Belum ada barang. Klik "+ Tambah Barang" untuk menambahkan.</div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="empty" id="tagKosong" style="display:none;">Tidak ada barang di kategori ini.</div>
@endsection

@section('modals')
  <div class="overlay" id="modalTambah">
    <div class="modal">
      <div class="modal-head">
        <div><h3>Tambah Barang</h3><p>Isi detail barang baru untuk inventaris.</p></div>
        <button class="modal-close" onclick="closeModal('modalTambah')">&times;</button>
      </div>
      <form method="POST" action="{{ route('admin.inventaris.store') }}">
        @csrf
        <div class="field"><label>Nama Barang</label><input name="nama" required placeholder="cth. Kamera DSLR Canon EOS 1500D"></div>
        <div class="field"><label>Kategori</label>
          <select name="kategori"><option>Elektronik</option><option>Perangkat Komputer</option><option>Jaringan</option><option>Alat Praktikum</option></select>
        </div>
        <div class="field"><label>Lokasi</label><input name="lokasi" placeholder="GUDANG RPL"></div>
        <div class="field"><label>Stok Awal</label><input type="number" name="stok" value="0"></div>
        <div class="field"><label>Kondisi Barang</label>
          <select name="kondisi">
            <option value="baik" selected>Baik</option>
            <option value="rusak_ringan">Rusak Ringan</option>
            <option value="rusak_berat">Rusak Berat</option>
          </select>
        </div>
        <div class="field"><label>Keterangan Kondisi (boleh kosong)</label><input name="ket_kondisi" maxlength="255" placeholder="cth. semua unit normal"></div>
        <button class="btn btn-orange btn-block" type="submit">Tambah Barang</button>
      </form>
    </div>
  </div>

  @foreach($barang as $item)
    <div class="overlay" id="modalEdit{{ $item->id }}">
      <div class="modal">
        <div class="modal-head">
          <div><h3>Edit Barang</h3><p>Perbarui detail, atau tambah stok di bawah.</p></div>
          <button class="modal-close" onclick="closeModal('modalEdit{{ $item->id }}')">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.inventaris.update', $item) }}">
          @csrf
          @method('PUT')
          <div class="field"><label>Nama Barang</label><input name="nama" value="{{ $item->nama }}" required></div>
          <div class="field"><label>Kategori</label>
            <select name="kategori">
              @foreach(['Elektronik','Perangkat Komputer','Jaringan','Alat Praktikum'] as $opt)
                <option {{ $item->kategori === $opt ? 'selected' : '' }}>{{ $opt }}</option>
              @endforeach
            </select>
          </div>
          <div class="field"><label>Lokasi</label><input name="lokasi" value="{{ $item->lokasi }}"></div>
          <div class="field"><label>Stok Tersedia</label><input type="number" name="stok" value="{{ $item->stok }}"></div>
          <div class="field"><label>Kondisi Barang</label>
            <select name="kondisi">
              @foreach(['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat'] as $val => $lab)
                <option value="{{ $val }}" {{ ($item->kondisi ?: 'baik') === $val ? 'selected' : '' }}>{{ $lab }}</option>
              @endforeach
            </select>
          </div>
          <div class="field"><label>Jumlah Unit Rusak</label><input type="number" name="jumlah_rusak" min="0" value="{{ (int) $item->jumlah_rusak }}">
            <p style="margin:6px 0 0;font-size:12px;color:var(--muted);">Terisi otomatis saat ada pengembalian rusak. Isi 0 kalau sudah diperbaiki, lalu tambah stoknya dan ubah kondisi ke Baik.</p>
          </div>
          <div class="field"><label>Keterangan Kondisi (boleh kosong)</label><input name="ket_kondisi" maxlength="255" value="{{ $item->ket_kondisi }}" placeholder="cth. 2 unit kabel USB patah"></div>
          <button class="btn btn-orange btn-block" type="submit">Simpan Perubahan</button>
        </form>

        <hr style="border:none;border-top:1px solid var(--line);margin:16px 0;">

        <form method="POST" action="{{ route('admin.inventaris.stok', $item) }}" style="margin-bottom:10px;">
          @csrf
          <div class="field" style="margin-bottom:8px;"><label>Tambah Stok Cepat</label><input type="number" name="jumlah" placeholder="cth. 5"></div>
          <button class="btn btn-ghost btn-block" type="submit">+ Tambah Stok</button>
        </form>

        @if($item->antrian->count())
          <div class="antri-box">
            <div class="antri-head"><b>{{ $item->antrian->count() }} siswa menunggu barang ini</b></div>
            <p>
              @if($item->stok > 0)
                Stok sudah ada, jadi mereka sudah dapat notifikasi di lonceng.
              @else
                Mereka otomatis dapat notifikasi di lonceng begitu stok tersedia.
              @endif
            </p>
            <ul class="antri-list">
              @foreach($item->antrian as $a)
                @php $org = $a->user; @endphp
                <li>
                  <span>
                    <b>{{ optional($org)->name ?: 'Pengguna dihapus' }}</b>
                    <small>{{ optional($org)->tipe ?: 'Siswa' }}@if(optional($org)->kelas) &middot; {{ $org->kelas }}@endif &middot; antri {{ $a->created_at ? $a->created_at->locale('id')->diffForHumans() : '' }}</small>
                  </span>
                  @if(optional($org)->no_wa)
                    <a class="antri-wa" target="_blank" rel="noopener" href="{{ \App\Support\Pinjam::wa($org->no_wa, 'Halo ' . $org->name . ', ' . $item->nama . ' di Gudang RPL sudah tersedia. Silakan ajukan peminjaman lewat SIPINTAR ya.') }}">WhatsApp</a>
                  @endif
                </li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('admin.inventaris.destroy', $item) }}" onsubmit="return confirm('Hapus barang ini?')">
          @csrf
          @method('DELETE')
          <button class="btn btn-danger-outline btn-block" type="submit">Hapus Barang</button>
        </form>
      </div>
    </div>
  @endforeach
@endsection

@section('scripts')
<script>
  // dibuka lewat tautan: /admin/inventaris?buka=ID
  (function () {
    var id = new URLSearchParams(location.search).get('buka');
    if (id && document.getElementById('modalEdit' + id)) openModal('modalEdit' + id);
  })();

  requestAnimationFrame(function () {
    requestAnimationFrame(function () {
      document.querySelectorAll('.kbar i').forEach(function (i) { i.style.width = i.dataset.w + '%'; });
    });
  });
</script>
@endsection