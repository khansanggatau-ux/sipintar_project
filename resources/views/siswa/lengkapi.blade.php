@extends('layouts.guest')

@section('title', 'Lengkapi Profil - SIPINTAR')
@section('hero-title', 'Satu langkah lagi sebelum bisa mengajukan peminjaman.')
@section('hero-sub', 'Data ini akan otomatis ditampilkan ke admin setiap kali kamu mengajukan peminjaman, jadi isi dengan benar.')

@section('content')
  <h2>Lengkapi Profil</h2>
  <p class="sub">Diisi sekali saja, akan tersimpan untuk peminjaman berikutnya.</p>

  <form method="POST" action="{{ route('siswa.lengkapi.simpan') }}">
    @csrf
    <div class="field"><label>Nama Lengkap</label><input type="text" name="name" value="{{ old('name') }}" placeholder="cth. Khansa Khairunnisa" required></div>
    <div class="field"><label>Role</label>
      <select name="tipe" id="tipe"><option>Siswa</option><option>Guru</option></select>
    </div>
    <div class="field" id="fieldJurusan"><label>Jurusan</label>
      <select name="jurusan" id="selectJurusan">
        <option>RPL &ndash; Rekayasa Perangkat Lunak</option>
        <option>DPIB &ndash; Desain Pemodelan dan Informasi Bangunan</option>
        <option>TE &ndash; Teknik Elektro</option>
        <option>TO &ndash; Teknik Otomotif</option>
        <option>TBSM &ndash; Teknik Bisnis Sepeda Motor</option>
        <option>TKP &ndash; Teknik Konstruksi dan Perumahan</option>
        <option>TM &ndash; Teknik Mesin</option>
        <option>TITL &ndash; Teknik Instalasi Tenaga Listrik</option>
      </select>
    </div>
    <input type="hidden" name="jurusan" id="jurusanGuru" value="-" disabled>
    <div class="field"><label id="labelKelas">Kelas</label><input type="text" name="kelas" id="inputKelas" value="{{ old('kelas') }}" placeholder="cth. XII RPL B" required></div>
    <div class="field">
      <label>No. WhatsApp</label>
      <input type="tel" inputmode="tel" name="no_wa" value="{{ old('no_wa') }}" placeholder="0812-3456-7890" maxlength="20" required autocomplete="tel">
      @error('no_wa')<p class="field-err">{{ $message }}</p>@enderror
      <p class="field-hint">Wajib nomor yang aktif WhatsApp. Admin akan menghubungimu lewat nomor ini kalau ada urusan peminjaman.</p>
    </div>
    <button class="btn btn-orange btn-block" type="submit">Lanjutkan</button>
  </form>

  <script>
    (function () {
      var tipe = document.getElementById('tipe');
      var fieldJurusan = document.getElementById('fieldJurusan');
      var selectJurusan = document.getElementById('selectJurusan');
      var jurusanGuru = document.getElementById('jurusanGuru');
      var labelKelas = document.getElementById('labelKelas');
      var inputKelas = document.getElementById('inputKelas');

      function atur() {
        var guru = tipe.value === 'Guru';
        fieldJurusan.style.display = guru ? 'none' : '';
        selectJurusan.disabled = guru;      // yang disabled tidak ikut terkirim
        jurusanGuru.disabled = !guru;       // guru mengirim "-" sebagai pengganti jurusan
        labelKelas.textContent = guru ? 'Kelas yang diampu' : 'Kelas';
        inputKelas.placeholder = guru ? 'cth. XII RPL A, XII RPL B' : 'cth. XII RPL B';
      }
      tipe.addEventListener('change', atur);
      atur();
    })();
  </script>
@endsection