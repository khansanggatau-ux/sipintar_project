@extends('layouts.app')
@section('title', $user->name . ' - Pengguna')

@section('content')
  @php
    $jumlahDipinjam = $riwayat->where('status', 'dipinjam')->count();
    $jumlahPernah = $riwayat->whereIn('status', ['dipinjam', 'selesai'])->count();
    $adaPeringatan = ! empty($user->peringatan);
    $sudahDibaca = $adaPeringatan && $user->peringatan_dibaca_at !== null;
  @endphp

  <div class="page-head">
    <div>
      <h1>{{ $user->name }}</h1>
      <p>Detail pengguna dan riwayat pinjamnya.</p>
    </div>
    <div class="head-actions">
      <a class="btn btn-ghost" href="{{ route('admin.pengguna.index') }}">&larr; Semua pengguna</a>
    </div>
  </div>

  <div class="profile-hero">
    <div class="av-lg">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</div>
    <div class="ph-info">
      <h2>{{ $user->name }}</h2>
      <p>
        {{ $user->tipe ?: 'Belum lengkapi profil' }}@if($user->kelas) &middot; {{ $user->kelas }}@endif
        @if(($user->tipe ?? '') !== 'Guru' && $user->jurusan && $user->jurusan !== '-') &middot; {{ $user->jurusan }}@endif
        <br>Username: {{ $user->username }} &middot; WhatsApp: {{ $user->no_wa ?: '-' }}
        @if($user->created_at) <br>Bergabung {{ $user->created_at->translatedFormat('j M Y') }}@endif
      </p>
    </div>
    <div class="ph-stats">
      <div><b>{{ $jumlahPernah }}</b><span>Pernah pinjam</span></div>
      <div><b>{{ $jumlahDipinjam }}</b><span>Sedang dipinjam</span></div>
      <div><b>{{ $skor['baru'] ? '-' : $skor['skor'] }}</b><span>Skor</span></div>
    </div>
  </div>

  <div class="panel" style="margin-bottom:18px;">
    <h3>Skor kepercayaan</h3>
    @if($skor['baru'])
      <p style="font-size:13px;color:var(--muted);margin:0;">Belum punya riwayat pinjam, jadi belum ada skor.</p>
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
        <p style="font-size:12.5px;color:var(--red);margin:8px 0 0;"><b>{{ $skor['sedangTelat'] }} barang sedang terlambat dikembalikan.</b></p>
      @endif
    @endif
  </div>

  <div class="profile-grid">
    <div class="panel">
      <h3>Peringatan</h3>

      @if($adaPeringatan)
        <div style="margin-bottom:14px;padding:12px;border-radius:var(--r-md);background:var(--surface-2);">
          <p style="font-size:12px;color:var(--muted);margin:0 0 6px;">
            Peringatan terakhir, dikirim {{ optional($user->peringatan_at)->translatedFormat('j M Y, H:i') }}
            &middot;
            @if($sudahDibaca)
              <b style="color:var(--green, #16473a);">sudah dibaca {{ $user->peringatan_dibaca_at->translatedFormat('j M Y, H:i') }}</b>
            @else
              <b style="color:var(--red);">belum dibaca</b>
            @endif
          </p>
          <p style="font-size:13px;margin:0;white-space:pre-line;">{{ $user->peringatan }}</p>
        </div>
      @endif

      @if($perlu)
        <form method="POST" action="{{ route('admin.pengguna.peringatan', $user) }}" onsubmit="return confirm('Kirim peringatan ke {{ $user->name }}?');">
          @csrf
          <div class="field" style="margin-bottom:8px;">
            <label>Pesan peringatan</label>
            <textarea name="pesan" required maxlength="500" style="min-height:130px;">{{ old('pesan', $pesanDefault) }}</textarea>
          </div>
          <button class="btn btn-orange btn-block" type="submit">{{ $adaPeringatan ? 'Kirim peringatan lagi' : 'Kirim peringatan' }}</button>
        </form>
      @else
        <p style="font-size:13px;color:var(--muted);margin:0;">Skornya masih aman. Tombol peringatan baru muncul kalau skor di bawah 60 atau ada barang yang terlambat dikembalikan.</p>
      @endif

      @if($user->no_wa)
        <a class="btn btn-wa btn-block" style="margin-top:12px;" target="_blank" rel="noopener"
           href="{{ \App\Support\Pinjam::wa($user->no_wa, 'Halo ' . $user->name . ', ini admin SIPINTAR.') }}">Chat WhatsApp</a>
      @endif
    </div>

    <div class="panel">
      <h3>Riwayat pinjam</h3>
      <div class="list">
        @forelse($riwayat as $p)
          @php
            $tab = $p->status === 'menunggu' ? 'menunggu' : ($p->status === 'dipinjam' ? 'dipinjam' : 'riwayat');
          @endphp
          <a href="{{ route('admin.peminjaman.index', ['tab' => $tab, 'selected' => $p->id]) }}" style="text-decoration:none;color:inherit;">
            <div class="row-card">
              <div class="lead">
                <div class="item-icon">{!! optional($p->barang)->icon !!}</div>
                <div class="info">
                  <h3>{{ optional($p->barang)->nama ?: '-' }}</h3>
                  <p>
                    {{ $p->kode_struk }} &middot; {{ optional($p->tanggal_pengajuan)->translatedFormat('j M Y') }}
                    @if($p->kondisi_kembali && $p->kondisi_kembali !== 'baik') &middot; kembali {{ $p->kondisi_kembali }}@endif
                  </p>
                </div>
              </div>
              @if($p->status === 'menunggu')<span class="badge badge-amber">Menunggu</span>@endif
              @if($p->status === 'dipinjam')<span class="badge badge-red">Dipinjam</span>@endif
              @if($p->status === 'selesai')<span class="badge badge-green">Selesai &#10003;</span>@endif
              @if($p->status === 'ditolak')<span class="badge badge-red">Ditolak</span>@endif
            </div>
          </a>
        @empty
          <div class="empty">Belum ada riwayat pinjam.</div>
        @endforelse
      </div>
    </div>
  </div>
@endsection