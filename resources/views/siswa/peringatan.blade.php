@extends('layouts.app')
@section('title', 'Peringatan dari Admin - SIPINTAR')

@section('content')
  <div style="max-width:620px;margin:24px auto 0;">
    <div class="panel" style="border:2px solid var(--red);">
      <span class="badge badge-red">Peringatan dari admin</span>
      <h1 style="margin:14px 0 6px;font-size:24px;">Ada pesan penting buat kamu, {{ explode(' ', trim($user->name))[0] }}</h1>
      <p style="font-size:12.5px;color:var(--muted);margin:0 0 16px;">Dikirim {{ optional($user->peringatan_at)->translatedFormat('j F Y, H:i') }}</p>

      <div style="margin:0 0 18px;padding:14px 16px;border-left:4px solid var(--red);background:var(--surface-2);border-radius:var(--r-md);font-size:15px;line-height:1.6;white-space:pre-line;">{{ $user->peringatan }}</div>

      @if(! $skor['baru'])
        <p style="font-size:13px;color:var(--muted);margin:0 0 18px;">
          Skor kepercayaanmu sekarang: <b>{{ $skor['skor'] }}</b> ({{ $skor['label'] }}).
          @if($skor['sedangTelat'])<br><b style="color:var(--red);">{{ $skor['sedangTelat'] }} barang terlambat dikembalikan.</b> Selama belum dikembalikan, kamu belum bisa mengajukan peminjaman baru.@endif
        </p>
      @endif

      <form method="POST" action="{{ route('siswa.peringatan.baca') }}">
        @csrf
        <button class="btn btn-orange btn-block" type="submit">Saya mengerti</button>
      </form>
    </div>
  </div>
@endsection