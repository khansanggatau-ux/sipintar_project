@extends('layouts.app')
@section('title', 'Pengajuan Saya - SIPINTAR Siswa')

@section('content')
  <div class="page-head"><div><h1>Pengajuan Saya</h1><p>Pantau status pengajuanmu. Klik salah satu untuk membuka struknya.</p></div></div>

  @if($list->count())
    <div class="tabs">
      <button type="button" class="tab-btn active" onclick="filterPengajuan(this,'all')">Semua ({{ $list->count() }})</button>
      @foreach(['menunggu' => 'Menunggu', 'dipinjam' => 'Dipinjam', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'] as $key => $label)
        @if($list->where('status', $key)->count())
          <button type="button" class="tab-btn" onclick="filterPengajuan(this,'{{ $key }}')">{{ $label }} ({{ $list->where('status', $key)->count() }})</button>
        @endif
      @endforeach
    </div>
  @endif

  <div class="list" id="pengajuanList">
    @forelse($list as $p)
      @php
        $telat = \App\Support\Pinjam::telat($p);
        $batas = \App\Support\Pinjam::batas($p);
      @endphp
      <div class="row-card" data-status="{{ $p->status }}" onclick="bukaStruk({{ $p->id }})">
        <div class="lead">
          <div class="item-icon">{!! $p->barang->icon !!}</div>
          <div class="info">
            <h3>{{ $p->barang->nama }}</h3>
            <p>No. struk {{ $p->kode_struk }} &middot; {{ $p->jumlah }} unit @if($p->durasi_hari)&middot; {{ $p->durasi_hari }} hari @endif&middot; {{ optional($p->tanggal_pengajuan)->translatedFormat('j M Y') }}@if($batas && $p->status === 'dipinjam') &middot; kembali {{ $batas->translatedFormat('j M') }}@endif</p>
          </div>
        </div>
        <div class="side">
          @if($telat)<span class="badge badge-red">Terlambat {{ $telat }} hari</span>@endif
          @if($p->status === 'menunggu')<span class="badge badge-amber">Menunggu approve</span>@endif
          @if($p->status === 'dipinjam' && !$telat)<span class="badge badge-red">Belum Dikembalikan</span>@endif
          @if($p->status === 'selesai')<span class="badge badge-green">Selesai &#10003;</span>@endif
          @if($p->status === 'ditolak')<span class="badge badge-red">Ditolak</span>@endif
          <span class="chev">&rsaquo;</span>
        </div>
      </div>
    @empty
      <div class="empty-state">
        <b>Belum ada pengajuan</b>
        Pengajuan peminjamanmu akan muncul di sini.
        <div><a class="btn btn-orange btn-sm" href="{{ route('siswa.inventaris.index') }}">Lihat Inventaris</a></div>
      </div>
    @endforelse
    <div class="empty" id="filterEmpty" style="display:none;">Tidak ada pengajuan dengan status ini.</div>
  </div>
@endsection

@section('modals')
  @foreach($list as $p)
    @php
      $telat = \App\Support\Pinjam::telat($p);
      $batas = \App\Support\Pinjam::batas($p);
    @endphp
    <div class="overlay" id="struk{{ $p->id }}">
      <div class="modal">
        <div class="modal-head"><div><h3>Struk Pengajuan</h3></div>
          <button class="modal-close" onclick="closeModal('struk{{ $p->id }}')">&times;</button>
        </div>
        <div class="receipt">
          <p style="color:var(--muted);font-size:12.5px;">Nomor struk</p>
          <div class="code">{{ $p->kode_struk }}</div>
          @php $tahap = ['menunggu' => 1, 'dipinjam' => 2, 'selesai' => 3][$p->status] ?? 0; @endphp
          @if($p->status === 'ditolak')
            <div class="progres-tolak">Pengajuan ini ditolak admin</div>
          @else
            <div class="progres">
              @foreach(['Diajukan', 'Dipinjam', 'Selesai'] as $i => $lab)
                <div class="langkah {{ $tahap >= $i + 1 ? 'on' : '' }} {{ ($tahap == $i + 1 && $p->status !== 'selesai') ? 'now' : '' }}"><i>{{ $i + 1 }}</i><span>{{ $lab }}</span></div>
              @endforeach
            </div>
          @endif
          <div class="receipt-table">
            <div><span>Barang</span><span>{{ $p->barang->nama }}</span></div>
            <div><span>Lokasi</span><span>{{ $p->barang->lokasi }}</span></div>
            <div><span>Jumlah</span><span>{{ $p->jumlah }} unit</span></div>
            <div><span>Tanggal</span><span>{{ optional($p->tanggal_pengajuan)->translatedFormat('j M Y') }}</span></div>
            @if($p->durasi_hari)<div><span>Lama pinjam</span><span>{{ $p->durasi_hari }} hari</span></div>@endif
            @if($batas)<div><span>Batas kembali</span><span>{{ $batas->translatedFormat('j M Y') }}</span></div>@endif
            @if($telat)<div><span>Keterlambatan</span><span style="color:var(--red);">{{ $telat }} hari</span></div>@endif
            @if($p->status === 'ditolak' && $p->alasan_tolak)<div><span>Alasan ditolak</span><span>{{ $p->alasan_tolak }}</span></div>@endif
            @if($p->status === 'selesai' && $p->kondisi_kembali)<div><span>Kondisi kembali</span><span>{{ ucfirst($p->kondisi_kembali) }}</span></div>@endif
            @if($p->status === 'selesai' && $p->catatan_kembali)<div><span>Catatan admin</span><span>{{ $p->catatan_kembali }}</span></div>@endif
            <div><span>Status</span><span>
              @if($p->status === 'menunggu') Menunggu approve
              @elseif($p->status === 'dipinjam') Belum Dikembalikan
              @elseif($p->status === 'selesai') Selesai
              @else Ditolak
              @endif
            </span></div>
          </div>
          @if(in_array($p->status, ['menunggu', 'dipinjam']))
            <div class="struk-qr">
              <div class="qr" id="qr{{ $p->id }}" data-url="{{ route('admin.peminjaman.index', ['tab' => $p->status === 'menunggu' ? 'menunggu' : 'dipinjam', 'selected' => $p->id]) }}"></div>
              <p>Tunjukkan QR ini ke admin. Admin cukup scan untuk membuka pengajuanmu.</p>
            </div>
          @endif
          @if($p->status === 'dipinjam')
            @if($p->perpanjang_status === 'menunggu')
              <p style="margin:14px 0 0;padding:10px 12px;border-radius:var(--r-md);background:var(--amber-bg, #fdf1d6);font-size:12.5px;">
                Permintaan tambah {{ $p->perpanjang_hari }} hari sedang menunggu admin.
              </p>
            @elseif(!$telat && (int) $p->jumlah_perpanjang < 2)
              @if($p->perpanjang_status === 'ditolak')
                <p style="margin:14px 0 0;font-size:12.5px;color:var(--red);">Permintaan perpanjangan sebelumnya ditolak admin.</p>
              @endif
              <form method="POST" action="{{ route('siswa.pengajuan.perpanjang', $p) }}" style="margin-top:14px;">
                @csrf
                <div class="field" style="margin-bottom:8px;">
                  <label>Butuh waktu lebih? Minta tambah hari</label>
                  <select name="hari">
                    @foreach([1, 2, 3, 5, 7] as $h)<option value="{{ $h }}">{{ $h }} hari</option>@endforeach
                  </select>
                </div>
                <div class="field" style="margin-bottom:10px;"><input type="text" name="alasan" maxlength="255" placeholder="Alasan (boleh kosong)"></div>
                <button class="btn btn-ghost btn-block" type="submit">Minta Perpanjang</button>
              </form>
            @endif
            @if((int) $p->jumlah_perpanjang > 0)
              <p style="margin:10px 0 0;font-size:12px;color:var(--muted);">Sudah diperpanjang {{ $p->jumlah_perpanjang }}x (maksimal 2x).</p>
            @endif
          @endif
          @if($p->status === 'menunggu')
            <form method="POST" action="{{ route('siswa.pengajuan.batal', $p) }}" style="margin-top:14px;" onsubmit="return confirm('Batalkan pengajuan ini?');">
              @csrf
              @method('DELETE')
              <button class="btn btn-danger-outline btn-block" type="submit">Batalkan Pengajuan</button>
            </form>
          @endif
        </div>
        <button class="btn btn-orange btn-block" style="margin-top:16px;" onclick="closeModal('struk{{ $p->id }}')">Tutup</button>
      </div>
    </div>
  @endforeach
@endsection

@section('scripts')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script>
    function bukaStruk(id) {
      openModal('struk' + id);
      var el = document.getElementById('qr' + id);
      if (el && !el.dataset.siap) { el.dataset.siap = 1; buatQR(el, el.dataset.url, 124); }
    }

    function filterPengajuan(btn, status) {
      document.querySelectorAll('.tabs .tab-btn').forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      var shown = 0;
      document.querySelectorAll('#pengajuanList .row-card').forEach(function (row) {
        var cocok = status === 'all' || row.dataset.status === status;
        row.style.display = cocok ? '' : 'none';
        if (cocok) shown++;
      });
      document.getElementById('filterEmpty').style.display = shown ? 'none' : 'block';
    }
  </script>
  @if(session('struk'))
    <script>window.addEventListener('DOMContentLoaded', function(){ bukaStruk({{ (int) session('struk') }}); });</script>
  @endif
@endsection