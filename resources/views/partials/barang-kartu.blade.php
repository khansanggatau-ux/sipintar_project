@php
  $kode = 'BRG-' . str_pad($item->id, 3, '0', STR_PAD_LEFT);
  $status = $item->stok <= 0 ? 'habis' : ($item->stok <= 2 ? 'sedikit' : 'tersedia');
  $label = ['habis' => 'Habis', 'sedikit' => 'Sisa sedikit', 'tersedia' => 'Tersedia'][$status];
  $rusak = (int) ($item->jumlah_rusak ?? 0);
  $kon = $item->kondisi ?: 'baik';
  $ak = ($aktif ?? [])[$item->id] ?? null;
  $namaKondisi = ['rusak_ringan' => 'Rusak ringan', 'rusak_berat' => 'Rusak berat'][$kon] ?? null;
@endphp
<div class="brg brg-{{ $status }}{{ $ak ? ' brg-aktif' : '' }}" role="button" tabindex="0"
     data-id="{{ $item->id }}"
     data-nama="{{ $item->nama }}"
     data-lokasi="{{ $item->lokasi }}"
     data-kategori="{{ $item->kategori }}"
     data-stok="{{ $item->stok }}"
     data-aktif="{{ $ak }}"
     data-kondisi="{{ $kon }}"
     data-ket="{{ $item->ket_kondisi }}"
     onclick="{{ $aksi }}"
     onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
  <div class="item-icon">{!! $item->icon !!}</div>
  <div class="brg-info">
    <h3>{{ $item->nama }}</h3>
    <p class="brg-sub">
      <span>{{ $kode }}</span>
      @if($namaKondisi)<em>{{ $namaKondisi }}</em>@endif
      @if($rusak > 0)<em>{{ $rusak }} unit rusak</em>@endif
      @if($ak)<em>{{ $ak === 'dipinjam' ? 'Sedang kamu pinjam' : 'Pengajuanmu menunggu' }}</em>@endif
    </p>
  </div>
  <span class="brg-status">{{ $label }}</span>
  <span class="brg-stok"><b>{{ $item->stok }}</b>unit</span>
  <span class="brg-go">{{ $ak ? 'Lihat' : ($item->stok <= 0 ? 'Kabari saya' : 'Ajukan') }}</span>
</div>