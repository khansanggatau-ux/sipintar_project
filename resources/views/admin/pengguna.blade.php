@extends('layouts.app')
@section('title', 'Pengguna - SIPINTAR Admin')

@section('content')
  <div class="page-head">
    <div>
      <h1>Pengguna</h1>
      <p>{{ $baris->count() }} siswa dan guru. Klik nama untuk lihat detail dan riwayat pinjamnya.</p>
    </div>
  </div>

  <form method="GET" action="{{ route('admin.pengguna.index') }}" class="pg-filter">
    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama, username, atau kelas..." autocomplete="off">
    <select name="urut">
      <option value="prioritas" {{ $urut === 'prioritas' ? 'selected' : '' }}>Perlu perhatian dulu</option>
      <option value="nama" {{ $urut === 'nama' ? 'selected' : '' }}>Urut nama</option>
      <option value="skor" {{ $urut === 'skor' ? 'selected' : '' }}>Skor terendah dulu</option>
    </select>
    <div class="btn-row">
      <button class="btn btn-orange" type="submit">Terapkan</button>
      @if($q !== '' || $urut !== 'prioritas')
        <a class="btn btn-ghost" href="{{ route('admin.pengguna.index') }}">Reset</a>
      @endif
    </div>
  </form>

  <div class="ledger">
    <table class="ledger-table">
      <thead>
        <tr>
          <th>Nama</th><th>Peran</th><th>WhatsApp</th><th class="num">Pernah pinjam</th><th>Skor</th><th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
        @forelse($baris as $b)
          @php
            $u = $b['user'];
            $skor = $b['skor'];
            $adaPeringatan = ! empty($u->peringatan) && $u->peringatan_dibaca_at === null;
            $buruk = ! $skor['baru'] && ($skor['skor'] < 60 || $skor['sedangTelat'] > 0);
            $sedang = ! $skor['baru'] && ! $buruk && $skor['skor'] < 85;
          @endphp
          <tr class="lrow {{ $buruk ? 'lrow-habis' : ($sedang ? 'lrow-sedikit' : '') }}" tabindex="0" style="cursor:pointer;"
              onclick="location.href='{{ route('admin.pengguna.show', $u) }}'"
              onkeydown="if(event.key==='Enter'){location.href='{{ route('admin.pengguna.show', $u) }}';}">
            <td class="td-judul">
              <div class="cell-item">
                <span class="avatar">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</span>
                <div><b>{{ $u->name }}</b><div style="font-size:12px;color:var(--muted);">{{ $u->username }}</div></div>
              </div>
            </td>
            <td data-label="Peran">{{ $u->tipe ?: '-' }}@if($u->kelas)<div style="font-size:12px;color:var(--muted);">{{ $u->kelas }}</div>@endif</td>
            <td data-label="WhatsApp">{{ $u->no_wa ?: '-' }}</td>
            <td class="num" data-label="Pernah pinjam">{{ $b['pernah'] }}</td>
            <td data-label="Skor">
              @if($skor['baru'])
                <span class="badge">Baru</span>
              @else
                <span class="badge {{ $skor['badge'] }}">{{ $skor['skor'] }} &middot; {{ $skor['label'] }}</span>
              @endif
            </td>
            <td data-label="Keterangan">
              @if($skor['sedangTelat'])
                <span class="badge badge-red">Telat {{ $skor['sedangTelat'] }} barang</span>
              @elseif($adaPeringatan)
                <span class="badge badge-amber">Peringatan belum dibaca</span>
              @elseif(! $u->profile_complete)
                <span class="badge badge-amber">Profil belum lengkap</span>
              @else
                <span style="color:var(--muted);">-</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6"><div class="empty">Tidak ada pengguna yang cocok.</div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection