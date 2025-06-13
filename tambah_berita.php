<!DOCTYPE html>
<html>
<head>
  <title>Tambah Berita</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="js/slugify.js"></script>
</head>
<body>
  <h2>Tambah Berita</h2>
  <form action="simpan_berita.php" method="POST">
    <label>Judul:</label><br>
    <input type="text" name="judul" id="judul" required><br><br>

    <label>Slug (otomatis):</label><br>
    <input type="text" name="slug" id="slug" readonly><br><br>

    <label>Isi:</label><br>
    <textarea name="isi" rows="8" cols="50" required></textarea><br><br>

    <button type="submit">Simpan</button>
  </form>
<form action="simpan_berita.php" method="POST" enctype="multipart/form-data">
  Judul: <input type="text" name="judul" id="judul"><br>
  Slug: <input type="text" name="slug" id="slug" readonly><br>
  Isi:<br>
  <textarea name="isi" rows="10" cols="50"></textarea><br>
  Gambar: <input type="file" name="gambar"><br><br>
  <input type="submit" value="Simpan">
</form>

  <script>
    $('#judul').on('keyup', function() {
      const slug = generateSlug($(this).val());
      $('#slug').val(slug);
    });
  </script>
</body>
</html>