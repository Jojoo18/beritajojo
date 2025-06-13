<?php
include 'koneksi.php';

$judul = mysqli_real_escape_string($conn, $_POST['judul']);
$slug  = mysqli_real_escape_string($conn, $_POST['slug']);
$isi   = mysqli_real_escape_string($conn, $_POST['isi']);

$sql = "INSERT INTO berita (judul, slug, isi) VALUES ('$judul', '$slug', '$isi')";

if (mysqli_query($conn, $sql)) {
    header("Location: index.php");
    exit;
} else {
    echo "Gagal menyimpan: " . mysqli_error($conn);
}
?>
<?php
include 'koneksi.php';

$judul = $_POST['judul'];
$slug = $_POST['slug'];
$isi = $_POST['isi'];

$gambar = '';
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $gambar_name = basename($_FILES['gambar']['name']);
    $target_file = $target_dir . $gambar_name;
    move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file);
    $gambar = $gambar_name;
}

$sql = "INSERT INTO berita (judul, slug, isi, gambar) VALUES ('$judul', '$slug', '$isi', '$gambar')";
mysqli_query($conn, $sql);

header("Location: index.php");
?>