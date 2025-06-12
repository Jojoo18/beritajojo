<?php
require_once 'koneksi.php';
require_once 'config.php'; // Diperlukan untuk sidebar kategori

// Cek apakah parameter slug ada
if (!isset($_GET['slug'])) {
    header("Location: index.php");
    exit();
}

$slug = $_GET['slug'];

// Ambil data berita dari database menggunakan prepared statement
$stmt = $koneksi->prepare("SELECT * FROM berita WHERE slug = ? LIMIT 1");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

// Jika berita tidak ditemukan, tampilkan pesan error dengan layout yang benar
if ($result->num_rows === 0) {
    $page_title = "Berita Tidak Ditemukan";
    require_once 'templates/header.php'; // Panggil header
    ?>
    <div class="main-wrapper">
        <aside class="sidebar-left">
            <h3 class="sidebar-title">Kategori Berita</h3>
            <ul class="category-list">
                <li><a href="index.php">Semua Kategori</a></li>
                <?php foreach (CATEGORIES as $cat): ?>
                    <li><a href="?kategori=<?= $cat ?>"><?= ucfirst($cat) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </aside>
        <main class="content-right">
            <div class="notice-box">
                Maaf, berita yang Anda cari tidak ditemukan.
                <br><br>
                <a href="index.php" class="sumber-link">&larr; Kembali ke Beranda</a>
            </div>
        </main>
    </div>
    <?php
    require_once 'templates/footer.php'; // Panggil footer
    exit();
}

$berita = $result->fetch_assoc();
$page_title = htmlspecialchars($berita['judul']); // Judul halaman dinamis

// Panggil header setelah semua data siap
require_once 'templates/header.php';

// Siapkan variabel untuk ditampilkan
$judul = htmlspecialchars($berita['judul']);
$sumber = htmlspecialchars($berita['sumber']);
$penulis = !empty($berita['penulis']) ? htmlspecialchars($berita['penulis']) : 'Redaksi';
$link_sumber_asli = htmlspecialchars($berita['url_sumber']);
$gambar = !empty($berita['gambar']) ? htmlspecialchars($berita['gambar']) : 'https://via.placeholder.com/800x450.png?text=BeritaViral';
$tanggal = date("l, d F Y H:i", strtotime($berita['tanggal_publish']));
// Gunakan nl2br untuk mengubah newline menjadi <br>, tapi letakkan di dalam <p>
$isi_berita_paragraphs = explode("\n", htmlspecialchars($berita['isi_berita']));

// Dapatkan kategori aktif untuk header
$filter_kategori = $berita['kategori'];
?>

<div class="main-wrapper">

    <aside class="sidebar-left">
        <h3 class="sidebar-title">Kategori Berita</h3>
        <ul class="category-list">
            <li>
                <a href="index.php" class="<?= empty($filter_kategori) ? 'active' : '' ?>">
                    Semua Kategori
                </a>
            </li>
            <?php foreach (CATEGORIES as $cat): ?>
                <li>
                    <a href="index.php?kategori=<?= $cat ?>" class="<?= ($filter_kategori === $cat) ? 'active' : '' ?>">
                        <?= ucfirst($cat) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <main class="content-right">
        <div class="article-detail-container">
            <header>
                <h1 class="article-title"><?= $judul ?></h1>
                <div class="article-meta-detail">
                    <span>Oleh: <strong><?= $penulis ?></strong></span>
                    <span>Sumber: <strong><?= $sumber ?></strong></span>
                    <span>Dipublikasikan: <strong><?= $tanggal ?> WIB</strong></span>
                </div>
            </header>
            
            <div class="article-featured-image">
                <img src="<?= $gambar ?>" alt="Gambar <?= $judul ?>">
            </div>

            <article class="article-body">
                <?php foreach ($isi_berita_paragraphs as $paragraph): ?>
                    <p><?= trim($paragraph) ?></p>
                <?php endforeach; ?>
                
                <?php if (!empty($link_sumber_asli)): ?>
                <p>
                    <a href="<?= $link_sumber_asli ?>" class="sumber-link" target="_blank" rel="noopener noreferrer">
                        Baca lebih lanjut di sumber asli &rarr;
                    </a>
                </p>
                <?php endif; ?>
            </article>
        </div>
    </main>
</div>

<?php
$stmt->close();
$koneksi->close();
require_once 'templates/footer.php';
?>