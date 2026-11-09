@extends('layouts.app')
@section('title', 'Peminjaman - SIPINTAR Admin')
@section('main-style')style="max-width:none;"@endsection

@section('content')
  <style>
    .pj-split{display:flex;gap:22px;align-items:flex-start;}
    .pj-main{flex:1;min-width:0;}
    .pj-back{display:none;margin-bottom:14px;font-size:13px;font-weight:700;color:var(--accent-dark);text-decoration:none;}
    .detail-panel .detail-row span:last-child{min-width:0;overflow-wrap:anywhere;}
    .row-card .info{min-width:0;}
    @media(max-width:960px){
      .pj-split{flex-direction:column;align-items:stretch;}
      .pj-split .detail-panel{position:static;width:100%;}
      .pj-split.has-sel .detail-panel{order:-1;}
      .pj-split:not(.has-sel) .detail-panel{display:none;}
      .pj-back{display:inline-block;}
    }
    @media(max-width:520px){
      .row-card{flex-wrap:wrap;padding:14px;}
      .row-card .info{flex:1 1 100%;}
      .tabs{width:100%;}
      .tab-btn{padding:9px 12px;font-size:13px;}
      .detail-panel{padding:18px;}
    }
  </style>
  <div class="pj-split {{ $selected ? 'has-sel' : '' }}">
  <div class="pj-main">
    <div class="page-head"><div><h1>Peminjaman</h1><p>Dua proses approval yang berbeda: approve saat barang <b>diambil</b>, dan approve saat barang <b>dikembalikan</b>.</p></div></div>

    <div class="tabs">
      <a href="{{ route('admin.peminjaman.index', ['tab' => 'menunggu']) }}" class="tab-btn {{ $tab === 'menunggu' ? 'active' : '' }}" style="text-decoration:none;display:inline-flex;">
        Approve Pengambilan @if($countMenunggu) ({{ $countMenunggu }}) @endif
      </a>
      <a href="{{ route('admin.peminjaman.index', ['tab' => 'dipinjam']) }}" class="tab-btn {{ $tab === 'dipinjam' ? 'active' : '' }}" style="text-decoration:none;display:inline-flex;">
        Approve Pengembalian @if($countDipinjam) ({{ $countDipinjam }}) @endif
      </a>
      <a href="{{ route('admin.peminjaman.index', ['tab' => 'riwayat']) }}" class="tab-btn {{ $tab === 'riwayat' ? 'active' : '' }}" style="text-decoration:none;display:inline-flex;">
        Riwayat
      </a>
    </div>

    <div class="list">
      @forelse($list as $p)
        @php $telatP = \App\Support\Pinjam::telat($p); @endphp
        <a href="{{ route('admin.peminjaman.index', ['tab' => $tab, 'selected' => $p->id, 'page' => $list->currentPage()]) }}" style="text-decoration:none;color:inherit;">
          <div class="row-card {{ $selected && $selected->id === $p->id ? 'selected' : '' }}">
            <div class="info">
              <h3>{{ $p->barang->nama }}</h3>
              <p>Diajukan oleh {{ $p->peminjam->name }} &middot; {{ optional($p->tanggal_pengajuan)->translatedFormat('j M Y') }}@if($p->durasi_hari) &middot; {{ $p->durasi_hari }} hari @endif</p>
            </div>
            @if($p->status === 'dipinjam' && $p->perpanjang_status === 'menunggu')<span class="badge badge-amber">Minta perpanjang</span>@endif
            @if($telatP)
              <span class="badge badge-red">Terlambat {{ $telatP }} hari</span>
            @elseif($p->status === 'menunggu')
              <span class="badge badge-amber">Menunggu approve</span>
            @elseif($p->status === 'dipinjam')
              <span class="badge badge-red">Belum Dikembalikan</span>
            @elseif($p->status === 'selesai')
              <span class="badge badge-green">Selesai &#10003;</span>
            @else
              <span class="badge badge-red">Ditolak</span>
            @endif
          </div>
        </a>
      @empty
        <div class="empty">Tidak ada data di tab ini.</div>
      @endforelse
    </div>

    @if($list->total() > 0)
      <p class="hasil-info" style="margin-top:14px;">Menampilkan {{ $list->firstItem() }}&ndash;{{ $list->lastItem() }} dari {{ $list->total() }} data</p>
    @endif
    @if($list->hasPages())
      <div class="pager">
        @if($list->onFirstPage())
          <span class="btn btn-ghost btn-sm is-off">&larr; Sebelumnya</span>
        @else
          <a class="btn btn-ghost btn-sm" href="{{ $list->previousPageUrl() }}">&larr; Sebelumnya</a>
        @endif
        <span class="pager-info">Halaman {{ $list->currentPage() }} dari {{ $list->lastPage() }}</span>
        @if($list->hasMorePages())
          <a class="btn btn-ghost btn-sm" href="{{ $list->nextPageUrl() }}">Berikutnya &rarr;</a>
        @else
          <span class="btn btn-ghost btn-sm is-off">Berikutnya &rarr;</span>
        @endif
      </div>
    @endif
  </div>

  <div class="detail-panel">
    @if($selected)
      <a class="pj-back" href="{{ route('admin.peminjaman.index', ['tab' => $tab, 'page' => $list->currentPage()]) }}">&larr; Kembali ke daftar</a>
      @php
        $batas = \App\Support\Pinjam::batas($selected);
        $telat = \App\Support\Pinjam::telat($selected);
        $nama = $selected->peminjam->name;
        $barangNama = $selected->barang->nama;
        if ($telat) {
          $pesan = "Halo {$nama}, {$barangNama} yang kamu pinjam sudah lewat batas kembali ({$batas->translatedFormat('j M Y')}) selama {$telat} hari. Mohon segera dikembalikan ke admin ya. Terima kasih.";
        } elseif ($selected->status === 'dipinjam') {
          $pesan = "Halo {$nama}, pengingat dari admin: {$barangNama} yang kamu pinjam harus dikembalikan paling lambat " . ($batas ? $batas->translatedFormat('j M Y') : 'sesuai kesepakatan') . ". Terima kasih.";
        } else {
          $pesan = "Halo {$nama}, pengajuan peminjaman {$barangNama} ({$selected->jumlah} unit) sudah kami terima. Silakan ambil di {$selected->barang->lokasi}. Lama pinjam " . ($selected->durasi_hari ?: 3) . " hari sejak barang diambil. Terima kasih.";
        }
      @endphp
      <div class="avatar">{{ strtoupper(substr($selected->peminjam->name, 0, 1)) }}</div>
      <h3>{{ $selected->peminjam->name }}</h3>
      <p class="role">{{ $selected->peminjam->tipe ?? 'Siswa' }} &middot; {{ $selected->peminjam->kelas }}</p>
      @if($skor)
        <div style="margin:10px 0 12px;padding:10px 12px;border-radius:var(--r-md);background:var(--surface-2);">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
            <span style="font-size:12px;color:var(--muted);">Skor kepercayaan</span>
            <span class="badge {{ $skor['badge'] }}">{{ $skor['baru'] ? 'Peminjam baru' : $skor['skor'] . ' · ' . $skor['label'] }}</span>
          </div>
          @if(!$skor['baru'])
            <p style="font-size:11.5px;color:var(--muted);margin:6px 0 0;">
              {{ $skor['selesai'] }} selesai ({{ $skor['tepat'] }} tepat waktu, {{ $skor['telat'] }} telat)
              &middot; {{ $skor['rusak'] }} rusak &middot; {{ $skor['hilang'] }} hilang
              @if($skor['sedangTelat'])&middot; <b style="color:var(--red);">{{ $skor['sedangTelat'] }} sedang telat</b>@endif
            </p>
          @endif
        </div>
      @endif
      <div class="detail-row"><span>No. WhatsApp</span><span>{{ $selected->peminjam->no_wa }}</span></div>
      <div class="detail-row"><span>Barang</span><span>{{ $selected->barang->nama }}</span></div>
      <div class="detail-row"><span>Jumlah</span><span>{{ $selected->jumlah }} unit</span></div>
      <div class="detail-row"><span>Tanggal</span><span>{{ optional($selected->tanggal_pengajuan)->translatedFormat('j M Y') }}</span></div>
      @if($selected->durasi_hari)<div class="detail-row"><span>Lama pinjam</span><span>{{ $selected->durasi_hari }} hari</span></div>@endif
      @if($batas)<div class="detail-row"><span>Batas kembali</span><span>{{ $batas->translatedFormat('j M Y') }}</span></div>@endif
      @if($telat)<div class="detail-row"><span>Keterlambatan</span><span style="color:var(--red);">{{ $telat }} hari</span></div>@endif
      @if((int) $selected->jumlah_perpanjang > 0)<div class="detail-row"><span>Diperpanjang</span><span>{{ $selected->jumlah_perpanjang }}x</span></div>@endif

      @if($selected->status === 'ditolak')
        <div class="detail-row"><span>Status</span><span style="color:var(--red);">Ditolak</span></div>
        @if($selected->alasan_tolak)<div class="detail-row"><span>Alasan</span><span>{{ $selected->alasan_tolak }}</span></div>@endif
      @elseif($selected->status === 'selesai')
        <div class="detail-row"><span>Status</span><span>Selesai</span></div>
        @if($selected->kondisi_kembali)<div class="detail-row"><span>Kondisi kembali</span><span>{{ ucfirst($selected->kondisi_kembali) }}</span></div>@endif
        @if($selected->catatan_kembali)<div class="detail-row"><span>Catatan</span><span>{{ $selected->catatan_kembali }}</span></div>@endif
      @endif

      @if($selected->status === 'menunggu')
        <p style="font-size:11.5px;color:var(--muted);margin:10px 0 6px;">Barang belum diambil peminjam.</p>
        <form method="POST" action="{{ route('admin.peminjaman.approveAmbil', $selected) }}" onsubmit="this.querySelector('button').disabled = true;">
          @csrf
          <button class="btn btn-orange btn-block" type="submit">&#10003; Approve Pengambilan Barang</button>
        </form>

        <form method="POST" action="{{ route('admin.peminjaman.tolak', $selected) }}" style="margin-top:14px;" onsubmit="return confirm('Tolak pengajuan ini?');">
          @csrf
          <div class="field" style="margin-bottom:8px;">
            <label>Alasan penolakan</label>
            <textarea name="alasan" required maxlength="255" style="min-height:64px;" placeholder="Contoh: barang sedang dipakai praktik"></textarea>
          </div>
          <button class="btn btn-danger-outline btn-block" type="submit">Tolak Pengajuan</button>
        </form>
      @elseif($selected->status === 'dipinjam')
        <p style="font-size:11.5px;color:var(--muted);margin:10px 0 6px;">Barang sudah diambil, menunggu dikembalikan.</p>

        @if($selected->perpanjang_status === 'menunggu')
          <div style="margin:0 0 14px;padding:12px;border-radius:var(--r-md);background:var(--amber-bg, #fdf1d6);">
            <b style="font-size:13px;">Minta tambah {{ $selected->perpanjang_hari }} hari</b>
            @if($selected->perpanjang_alasan)<p style="font-size:12px;margin:4px 0 0;">Alasan: {{ $selected->perpanjang_alasan }}</p>@endif
            <div style="display:flex;gap:8px;margin-top:10px;">
              <form method="POST" action="{{ route('admin.peminjaman.perpanjangSetuju', $selected) }}" style="flex:1;">
                @csrf
                <button class="btn btn-orange btn-sm btn-block" type="submit">Setujui</button>
              </form>
              <form method="POST" action="{{ route('admin.peminjaman.perpanjangTolak', $selected) }}" style="flex:1;" onsubmit="return confirm('Tolak permintaan perpanjangan ini?');">
                @csrf
                <button class="btn btn-danger-outline btn-sm btn-block" type="submit">Tolak</button>
              </form>
            </div>
          </div>
        @endif
        <form method="POST" action="{{ route('admin.peminjaman.approveKembali', $selected) }}" onsubmit="this.querySelector('button[type=submit]').disabled = true;">
          @csrf
          <div class="field" style="margin-bottom:8px;">
            <label>Kondisi barang saat kembali</label>
            <select name="kondisi_kembali" onchange="var w=document.getElementById('rusakWrap');w.style.display=this.value==='rusak'?'':'none';">
              <option value="baik">Baik</option>
              <option value="rusak">Rusak</option>
              <option value="hilang">Hilang</option>
            </select>
          </div>
          <div class="field" id="rusakWrap" style="display:none;margin-bottom:8px;">
            <label>Berapa unit yang rusak?</label>
            <input type="number" name="jumlah_rusak" min="1" max="{{ $selected->jumlah }}" value="{{ $selected->jumlah }}">
          </div>
          <div class="field" style="margin-bottom:6px;">
            <label>Catatan (boleh kosong)</label>
            <input type="text" name="catatan_kembali" maxlength="255" placeholder="Contoh: kabel charger patah">
          </div>
          <p style="font-size:11px;color:var(--muted);margin:0 0 10px;">Rusak: hanya unit yang rusak yang tidak masuk stok, sisanya kembali ke stok. Hilang: stok tidak ditambah.</p>
          <button class="btn btn-orange btn-block" type="submit">&#10003; Approve Pengembalian Barang</button>
        </form>
      @endif

      <form id="formNotifWa" method="POST" action="{{ route('admin.pengguna.notif', $selected->peminjam) }}" data-nama="{{ $selected->peminjam->name }}" hidden>@csrf<input type="hidden" name="pesan" value="{{ $pesan }}"></form>
      <a class="btn {{ $telat ? 'btn-wa-telat' : 'btn-wa' }} btn-block" target="_blank" rel="noopener" href="{{ \App\Support\Pinjam::wa($selected->peminjam->no_wa, $pesan) }}">
        {{ $telat ? 'Kirim pengingat via WhatsApp' : 'Chat peminjam via WhatsApp' }}
      </a>
      <button type="button" class="btn btn-ghost btn-block" style="margin-top:8px;" onclick="kirimNotifWeb(this, 'formNotifWa')">Kirim notif ke website siswa</button>
    @else
      <div class="empty-hint">Pilih salah satu pengajuan di samping untuk melihat detail peminjam.</div>
    @endif
  </div>
  </div>
@endsection

@section('scripts')
  @include('partials.notif-website')
@endsection