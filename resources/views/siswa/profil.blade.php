@extends('layouts.app')
@section('title', 'Profil - SIPINTAR Siswa')

@section('content')
  <div class="page-head"><div><h1>Profil</h1><p>Data akun yang tersimpan untuk pengajuan peminjaman.</p></div></div>

  <div class="profile-hero">
    <div class="av-lg">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</div>
    <div class="ph-info">
      <h2>{{ $user->name }}</h2>
      <p>{{ $user->tipe }}@if($user->kelas) &middot; {{ $user->kelas }}@endif @if(($user->tipe ?? '') !== 'Guru' && $user->jurusan && $user->jurusan !== '-')&middot; {{ $user->jurusan }}@endif</p>
    </div>
    <div class="ph-stats">
      <div><b>{{ $stats['total'] }}</b><span>Total pengajuan</span></div>
      <div><b>{{ $stats['dipinjam'] }}</b><span>Sedang dipinjam</span></div>
      <div><b>{{ $stats['selesai'] }}</b><span>Selesai</span></div>
    </div>
  </div>

  <div class="panel" style="margin-bottom:18px;">
    <h3>Skor kepercayaan</h3>
    @if($skor['baru'])
      <p style="font-size:13px;color:var(--muted);margin:0;">Kamu belum punya riwayat pinjam. Pinjam, lalu kembalikan tepat waktu dan dalam kondisi baik supaya skormu naik.</p>
    @else
      <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
        <b style="font-size:34px;line-height:1;">{{ $skor['skor'] }}</b>
        <span class="badge {{ $skor['badge'] }}">{{ $skor['label'] }}</span>
      </div>
      <p style="font-size:12.5px;color:var(--muted);margin:10px 0 0;">
        {{ $skor['selesai'] }} peminjaman selesai ({{ $skor['tepat'] }} tepat waktu, {{ $skor['telat'] }} telat)
        &middot; {{ $skor['rusak'] }} kembali rusak &middot; {{ $skor['hilang'] }} hilang
      </p>
      @if($skor['sedangTelat'])
        <p style="font-size:12.5px;color:var(--red);margin:8px 0 0;"><b>{{ $skor['sedangTelat'] }} barang terlambat dikembalikan.</b> Selama belum dikembalikan, kamu belum bisa mengajukan peminjaman baru.</p>
      @endif
    @endif
  </div>

  <div class="profile-grid">
    <div class="panel">
      <h3>Ubah data</h3>
      <form method="POST" action="{{ route('siswa.profil.update') }}">
        @csrf
        @if(($user->tipe ?? '') === 'Guru')
          <input type="hidden" name="jurusan" value="{{ $user->jurusan ?: '-' }}">
        @else
          <div class="field"><label>Jurusan</label><input name="jurusan" value="{{ $user->jurusan }}"></div>
        @endif
        <div class="field"><label>{{ ($user->tipe ?? '') === 'Guru' ? 'Kelas yang diampu' : 'Kelas' }}</label><input name="kelas" value="{{ $user->kelas }}"></div>
        <div class="field">
          <label>No. WhatsApp</label>
          <input type="tel" inputmode="tel" name="no_wa" value="{{ old('no_wa', $user->no_wa) }}" maxlength="20" required>
          @error('no_wa')<p class="field-err">{{ $message }}</p>@enderror
          <p class="field-hint">Harus nomor yang aktif WhatsApp, contoh 081234567890.</p>
        </div>
        <button class="btn btn-orange btn-block" type="submit">Simpan perubahan</button>
      </form>
    </div>

    <div class="panel">
      <h3>Riwayat terakhir</h3>
      <div class="list">
        @forelse($milikSaya->take(5) as $p)
          <div class="row-card" style="cursor:default;">
            <div class="lead">
              <div class="item-icon">{!! $p->barang->icon !!}</div>
              <div class="info"><h3>{{ $p->barang->nama }}</h3><p>No. struk {{ $p->kode_struk }} &middot; {{ optional($p->tanggal_pengajuan)->translatedFormat('j M Y') }}</p></div>
            </div>
            @if($p->status === 'menunggu')<span class="badge badge-amber">Menunggu approve</span>@endif
            @if($p->status === 'dipinjam')<span class="badge badge-red">Belum Dikembalikan</span>@endif
            @if($p->status === 'selesai')<span class="badge badge-green">Selesai &#10003;</span>@endif
            @if($p->status === 'ditolak')<span class="badge badge-red">Ditolak</span>@endif
          </div>
        @empty
          <div class="empty">Belum ada riwayat peminjaman.</div>
        @endforelse
      </div>
    </div>
  </div>
@endsection