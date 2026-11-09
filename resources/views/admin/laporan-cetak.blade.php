<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Laporan Peminjaman - SIPINTAR</title>
<style>
  *{box-sizing:border-box;}
  body{font-family:Arial,Helvetica,sans-serif;color:#1d2433;margin:0;padding:28px;font-size:12px;}
  h1{font-size:20px;margin:0 0 4px;}
  .meta{color:#5b6475;margin:0 0 16px;line-height:1.6;}
  .bar{display:flex;gap:10px;margin-bottom:18px;}
  .bar button,.bar a{padding:9px 16px;border-radius:8px;border:0;background:#16473a;color:#fff;font-size:13px;cursor:pointer;text-decoration:none;}
  .bar a{background:#e8ebf0;color:#1d2433;}
  table{width:100%;border-collapse:collapse;}
  th,td{border:1px solid #cfd5df;padding:6px 8px;text-align:left;vertical-align:top;}
  th{background:#eef1f5;font-size:11px;text-transform:uppercase;letter-spacing:.3px;}
  tr{page-break-inside:avoid;}
  .kosong{padding:24px;text-align:center;color:#5b6475;border:1px dashed #cfd5df;}
  @media print{
    body{padding:0;}
    .bar{display:none;}
    @page{size:A4 landscape;margin:12mm;}
  }
</style>
</head>
<body>
  <div class="bar">
    <button type="button" onclick="window.print()">Cetak / Simpan sebagai PDF</button>
    <a href="{{ route('admin.dashboard') }}">Kembali</a>
  </div>

  <h1>Laporan Peminjaman Alat - SIPINTAR</h1>
  <p class="meta">
    Periode: {{ $dari ? $dari->translatedFormat('j F Y') : 'awal' }} s/d {{ $sampai ? $sampai->translatedFormat('j F Y') : 'sekarang' }}<br>
    Total {{ $data->count() }} pengajuan:
    {{ $data->where('status', 'selesai')->count() }} selesai,
    {{ $data->where('status', 'dipinjam')->count() }} dipinjam,
    {{ $data->where('status', 'menunggu')->count() }} menunggu,
    {{ $data->where('status', 'ditolak')->count() }} ditolak<br>
    Dicetak: {{ now()->translatedFormat('j F Y, H:i') }}
  </p>

  @if($data->count())
    <table>
      <thead>
        <tr>
          <th>No</th><th>No. Struk</th><th>Peminjam</th><th>Barang</th><th>Jml</th>
          <th>Diajukan</th><th>Batas Kembali</th><th>Kembali</th><th>Status</th><th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $i => $p)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $p->kode_struk }}</td>
            <td>{{ optional($p->peminjam)->name }}@if(optional($p->peminjam)->kelas)<br>{{ $p->peminjam->kelas }}@endif</td>
            <td>{{ optional($p->barang)->nama }}</td>
            <td>{{ $p->jumlah }}</td>
            <td>{{ optional($p->tanggal_pengajuan)->format('d/m/Y') }}</td>
            <td>{{ $p->batas_kembali ? \Carbon\Carbon::parse($p->batas_kembali)->format('d/m/Y') : '-' }}</td>
            <td>{{ optional($p->tanggal_kembali)->format('d/m/Y') ?: '-' }}</td>
            <td>{{ \App\Support\Laporan::labelStatus($p->status) }}</td>
            <td>
              @if($p->status === 'ditolak' && $p->alasan_tolak)Ditolak: {{ $p->alasan_tolak }}@endif
              @if($p->status === 'selesai' && $p->kondisi_kembali)Kondisi: {{ ucfirst($p->kondisi_kembali) }}@if($p->catatan_kembali) - {{ $p->catatan_kembali }}@endif @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @else
    <div class="kosong">Tidak ada data pada periode ini.</div>
  @endif
</body>
</html>