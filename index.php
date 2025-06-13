<?php
echo "Versi lokal";
echo "dan tambahan dari GitHub";
// --- LOGIKA PHP INTI (TIDAK BERUBAH) ---
$page_title = "BeritaViral - Portal Berita Terkini";
require_once 'koneksi.php';
require_once 'config.php';

// Ambil filter kategori dan pencarian
$filter_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$filter_search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Pagination dengan validasi
$per_page = 6; // Jumlah berita per halaman
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

// Query builder dengan prepared statement
$where = [];
$params = [];
$types = '';

if ($filter_kategori && in_array($filter_kategori, CATEGORIES)) {
    $where[] = 'kategori = ?';
    $params[] = $filter_kategori;
    $types .= 's';
}

if ($filter_search) {
    $where[] = '(judul LIKE ? OR isi_berita LIKE ? OR tags LIKE ?)';
    $search_term = "%$filter_search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
    $types .= 'sss';
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Hitung total berita untuk pagination
$stmt_count = $koneksi->prepare("SELECT COUNT(*) FROM berita $where_sql");
if ($params) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$stmt_count->bind_result($total_berita);
$stmt_count->fetch();
$stmt_count->close();

// Tampilkan berita
$stmt = $koneksi->prepare("SELECT * FROM berita $where_sql ORDER BY tanggal_publish DESC LIMIT ? OFFSET ?");
if ($params) {
    $all_params = array_merge($params, [$per_page, $offset]);
    $stmt->bind_param($types . 'ii', ...$all_params);
} else {
    $stmt->bind_param('ii', $per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

// --- PANGGIL HEADER SETELAH SEMUA LOGIKA SELESAI ---
require_once 'templates/header.php';
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
                    <a href="?kategori=<?= $cat ?>" class="<?= ($filter_kategori === $cat) ? 'active' : '' ?>">
                        <?= ucfirst($cat) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <main class="content-right">

        <div class="search-container">
            <form class="search-form" method="get">
                <input type="text" name="q" placeholder="Cari berita..." value="<?= htmlspecialchars($filter_search) ?>">
                <button type="submit">Cari</button>
            </form>
        </div>

        <h2 class="page-title">
             Berita <?= $filter_kategori ? htmlspecialchars(ucfirst($filter_kategori)) : 'Terkini' ?>
        </h2>
        <?php if($filter_search): ?>
            <p class="search-info" style="margin-bottom: 18px;">
                Hasil pencarian untuk: <strong>"<?= htmlspecialchars($filter_search) ?>"</strong>
            </p>
        <?php endif; ?>

        <div class="article-list">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($artikel = $result->fetch_assoc()): ?>
                    <?php
                        // Siapkan variabel untuk kebersihan kode
                        $judul = htmlspecialchars($artikel['judul']);
                        $sumber = htmlspecialchars($artikel['sumber']);
                        $link_detail = 'detail.php?slug=' . urlencode($artikel['slug']);
                        $gambar = !empty($artikel['gambar']) ? htmlspecialchars($artikel['gambar']) : 'https://via.placeholder.com/110x74.png?text=No+Image';
                        $tanggal = date("d F Y", strtotime($artikel['tanggal_publish']));
                        $excerpt = isset($artikel['excerpt']) && $artikel['excerpt'] ? htmlspecialchars($artikel['excerpt']) : mb_substr(strip_tags($artikel['isi_berita'] ?? ''), 0, 120) . '...';
                    ?>
                    <article class="article-card">
                        <div class="article-image">
                            <a href="<?= $link_detail ?>"><img src="<?= $gambar ?>" alt="<?= $judul ?>"></a>
                        </div>
                        <div class="article-content">
                            <h2><a href="<?= $link_detail ?>"><?= $judul ?></a></h2>
                            <div class="article-meta">Oleh <strong><?= $sumber ?></strong> - <?= $tanggal ?></div>
                            <div class="article-excerpt"><?= $excerpt ?></div>
                            <a href="<?= $link_detail ?>" class="sumber-link">Baca Selengkapnya &rarr;</a>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="error-box" style="background: #fff; padding: 40px; text-align: center; border-radius: var(--radius);">Belum ada berita untuk ditampilkan.</div>
            <?php endif; ?>
        </div>

        <?php 
        $total_pages = isset($total_berita) ? ceil($total_berita / $per_page) : 1;
        if ($total_pages > 1): ?>
            <div class="pagination-wrapper">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php 
                            // Bangun URL dengan parameter yang sudah ada
                            $query_params = $_GET; 
                            $query_params['page'] = $i; 
                            $url = '?' . http_build_query($query_params); 
                        ?>
                        <li>
                            <a href="<?= $url ?>" class="<?= ($i == $page) ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
        <?php endif; ?>

    </main>

</div> <?php 
// --- PANGGIL FOOTER DAN TUTUP KONEKSI ---
$stmt->close();
$koneksi->close();
require_once 'templates/footer.php';
?>
include 'koneksi.php';

$limit = 6;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

$total_result = mysqli_query($conn, "SELECT COUNT(*) FROM berita");
$total_rows = mysqli_fetch_row($total_result)[0];
$total_pages = ceil($total_rows / $limit);

// Ambil berita utama
$berita = mysqli_query($conn, "SELECT * FROM berita ORDER BY created_at DESC LIMIT $start, $limit");

// Ambil berita populer (berdasarkan views)
$populer = mysqli_query($conn, "SELECT * FROM berita ORDER BY views DESC LIMIT 5");

// Ambil berita terbaru untuk featured section
$featured = mysqli_query($conn, "SELECT * FROM berita ORDER BY created_at DESC LIMIT 1");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NewsHub - Berita Terkini & Terpercaya</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root {
      --primary: #0066cc;
      --primary-dark: #004d99;
      --secondary: #ff6600;
      --dark: #1a1a1a;
      --gray-dark: #333;
      --gray: #666;
      --gray-light: #e6e6e6;
      --light: #f8f9fa;
      --white: #ffffff;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    }
    
    body {
      background-color: var(--light);
      color: var(--dark);
      line-height: 1.6;
    }
    
    .container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    /* Header */
    header {
      background-color: var(--white);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }
    
    .header-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 0;
    }
    
    .logo {
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary);
      text-decoration: none;
    }
    
    .logo span {
      color: var(--secondary);
    }
    
    nav ul {
      display: flex;
      list-style: none;
    }
    
    nav ul li {
      margin-left: 30px;
    }
    
    nav ul li a {
      color: var(--dark);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s;
    }
    
    nav ul li a:hover {
      color: var(--primary);
    }
    
    .mobile-menu {
      display: none;
      font-size: 1.5rem;
      cursor: pointer;
    }
    
    /* Featured News */
    .featured-news {
      margin: 30px 0;
      position: relative;
      height: 500px;
      overflow: hidden;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .featured-content {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      padding: 40px;
      background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
      color: var(--white);
      z-index: 2;
    }
    
    .featured-news img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s;
    }
    
    .featured-news:hover img {
      transform: scale(1.05);
    }
    
    .featured-tag {
      display: inline-block;
      background-color: var(--secondary);
      color: var(--white);
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      margin-bottom: 15px;
    }
    
    .featured-title {
      font-size: 2.5rem;
      margin-bottom: 15px;
      line-height: 1.2;
    }
    
    .featured-title a {
      color: var(--white);
      text-decoration: none;
    }
    
    .featured-meta {
      display: flex;
      align-items: center;
      font-size: 0.9rem;
    }
    
    .featured-meta span {
      margin-right: 20px;
      display: flex;
      align-items: center;
    }
    
    .featured-meta i {
      margin-right: 5px;
    }
    
    /* Main Content */
    .main-content {
      display: flex;
      margin: 40px 0;
    }
    
    .news-section {
      flex: 2;
      margin-right: 30px;
    }
    
    .section-title {
      font-size: 1.8rem;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid var(--primary);
      display: inline-block;
    }
    
    .news-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 25px;
    }
    
    .news-card {
      background-color: var(--white);
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .news-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }
    
    .news-img {
      height: 200px;
      overflow: hidden;
    }
    
    .news-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s;
    }
    
    .news-card:hover .news-img img {
      transform: scale(1.1);
    }
    
    .news-content {
      padding: 20px;
    }
    
    .news-category {
      display: inline-block;
      color: var(--primary);
      font-size: 0.8rem;
      font-weight: 600;
      margin-bottom: 10px;
      text-transform: uppercase;
    }
    
    .news-title {
      font-size: 1.2rem;
      margin-bottom: 10px;
      line-height: 1.4;
    }
    
    .news-title a {
      color: var(--dark);
      text-decoration: none;
      transition: color 0.3s;
    }
    
    .news-title a:hover {
      color: var(--primary);
    }
    
    .news-excerpt {
      color: var(--gray);
      margin-bottom: 15px;
      font-size: 0.95rem;
    }
    
    .news-meta {
      display: flex;
      justify-content: space-between;
      color: var(--gray);
      font-size: 0.85rem;
    }
    
    .news-meta span {
      display: flex;
      align-items: center;
    }
    
    .news-meta i {
      margin-right: 5px;
    }
    
    /* Sidebar */
    .sidebar {
      flex: 1;
    }
    
    .sidebar-widget {
      background-color: var(--white);
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 30px;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }
    
    .widget-title {
      font-size: 1.3rem;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid var(--primary);
    }
    
    .popular-list {
      list-style: none;
    }
    
    .popular-list li {
      padding: 12px 0;
      border-bottom: 1px solid var(--gray-light);
    }
    
    .popular-list li:last-child {
      border-bottom: none;
    }
    
    .popular-list a {
      display: flex;
      align-items: center;
      color: var(--dark);
      text-decoration: none;
      transition: color 0.3s;
    }
    
    .popular-list a:hover {
      color: var(--primary);
    }
    
    .popular-number {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 25px;
      height: 25px;
      background-color: var(--primary);
      color: var(--white);
      border-radius: 50%;
      font-size: 0.7rem;
      font-weight: bold;
      margin-right: 10px;
      flex-shrink: 0;
    }
    
    .popular-list li:nth-child(1) .popular-number {
      background-color: #e74c3c;
    }
    
    .popular-list li:nth-child(2) .popular-number {
      background-color: #f39c12;
    }
    
    .popular-list li:nth-child(3) .popular-number {
      background-color: #3498db;
    }
    
    .popular-title {
      font-size: 0.95rem;
      line-height: 1.4;
    }
    
    /* Newsletter */
    .newsletter {
      text-align: center;
    }
    
    .newsletter p {
      margin-bottom: 15px;
      color: var(--gray);
    }
    
    .newsletter-form {
      display: flex;
    }
    
    .newsletter-form input {
      flex: 1;
      padding: 12px 15px;
      border: 1px solid var(--gray-light);
      border-radius: 4px 0 0 4px;
      font-size: 0.9rem;
    }
    
    .newsletter-form button {
      background-color: var(--primary);
      color: var(--white);
      border: none;
      padding: 0 20px;
      border-radius: 0 4px 4px 0;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    
    .newsletter-form button:hover {
      background-color: var(--primary-dark);
    }
    
    /* Categories */
    .category-list {
      list-style: none;
    }
    
    .category-list li {
      margin-bottom: 10px;
    }
    
    .category-list a {
      display: block;
      padding: 10px 15px;
      background-color: var(--light);
      color: var(--dark);
      text-decoration: none;
      border-radius: 4px;
      transition: all 0.3s;
    }
    
    .category-list a:hover {
      background-color: var(--primary);
      color: var(--white);
      transform: translateX(5px);
    }
    
    .category-list i {
      margin-right: 10px;
    }
    
    /* Pagination */
    .pagination {
      display: flex;
      justify-content: center;
      margin-top: 40px;
    }
    
    .pagination a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      margin: 0 5px;
      border-radius: 50%;
      color: var(--dark);
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s;
    }
    
    .pagination a:hover, .pagination a.active {
      background-color: var(--primary);
      color: var(--white);
    }
    
    /* Footer */
    footer {
      background-color: var(--dark);
      color: var(--white);
      padding: 50px 0 20px;
      margin-top: 50px;
    }
    
    .footer-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
      margin-bottom: 30px;
    }
    
    .footer-logo {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--white);
      margin-bottom: 20px;
      display: block;
    }
    
    .footer-logo span {
      color: var(--secondary);
    }
    
    .footer-about p {
      color: var(--gray-light);
      margin-bottom: 20px;
    }
    
    .social-links {
      display: flex;
    }
    
    .social-links a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background-color: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      margin-right: 10px;
      color: var(--white);
      text-decoration: none;
      transition: background-color 0.3s;
    }
    
    .social-links a:hover {
      background-color: var(--primary);
    }
    
    .footer-links h3 {
      font-size: 1.2rem;
      margin-bottom: 20px;
      position: relative;
      padding-bottom: 10px;
    }
    
    .footer-links h3::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      width: 40px;
      height: 2px;
      background-color: var(--primary);
    }
    
    .footer-links ul {
      list-style: none;
    }
    
    .footer-links li {
      margin-bottom: 10px;
    }
    
    .footer-links a {
      color: var(--gray-light);
      text-decoration: none;
      transition: color 0.3s;
    }
    
    .footer-links a:hover {
      color: var(--white);
    }
    
    .footer-contact p {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
      color: var(--gray-light);
    }
    
    .footer-contact i {
      margin-right: 10px;
      color: var(--primary);
    }
    
    .copyright {
      text-align: center;
      padding-top: 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      color: var(--gray-light);
      font-size: 0.9rem;
    }
    
    /* Responsive */
    @media (max-width: 992px) {
      .main-content {
        flex-direction: column;
      }
      
      .news-section {
        margin-right: 0;
        margin-bottom: 40px;
      }
      
      .featured-title {
        font-size: 2rem;
      }
    }
    
    @media (max-width: 768px) {
      nav ul {
        display: none;
      }
      
      .mobile-menu {
        display: block;
      }
      
      .featured-news {
        height: 400px;
      }
      
      .featured-content {
        padding: 20px;
      }
      
      .featured-title {
        font-size: 1.5rem;
      }
    }
    
    @media (max-width: 576px) {
      .featured-news {
        height: 300px;
      }
      
      .news-grid {
        grid-template-columns: 1fr;
      }
      
      .pagination a {
        width: 35px;
        height: 35px;
        font-size: 0.9rem;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="container header-container">
      <a href="index.php" class="logo">News<span>Hub</span></a>
      
      <nav>
        <ul>
          <li><a href="index.php">Beranda</a></li>
          <li><a href="#">Politik</a></li>
          <li><a href="#">Ekonomi</a></li>
          <li><a href="#">Teknologi</a></li>
          <li><a href="#">Olahraga</a></li>
          <li><a href="#">Kesehatan</a></li>
        </ul>
      </nav>
      
      <div class="mobile-menu">
        <i class="fas fa-bars"></i>
      </div>
    </div>
  </header>
  
  <main class="container">
    <!-- Featured News -->
    <?php if ($featured_news = mysqli_fetch_assoc($featured)): ?>
    <div class="featured-news">
      <img src="<?= $featured_news['gambar'] ?>" alt="<?= $featured_news['judul'] ?>">
      <div class="featured-content">
        <span class="featured-tag">Headline</span>
        <h2 class="featured-title"><a href="berita.php?slug=<?= $featured_news['slug'] ?>"><?= $featured_news['judul'] ?></a></h2>
        <div class="featured-meta">
          <span><i class="far fa-calendar-alt"></i> <?= date('d F Y', strtotime($featured_news['created_at'])) ?></span>
          <span><i class="far fa-eye"></i> <?= $featured_news['views'] ?> views</span>
          <span><i class="far fa-user"></i> <?= $featured_news['penulis'] ?? 'Admin' ?></span>
        </div>
      </div>
    </div>
    <?php endif; ?>
    
    <div class="main-content">
      <section class="news-section">
        <h2 class="section-title">Berita Terbaru</h2>
        
        <div class="news-grid">
          <?php while ($row = mysqli_fetch_assoc($berita)): ?>
          <article class="news-card">
            <div class="news-img">
              <?php if (!empty($row['gambar'])): ?>
                <img src="<?= $row['gambar'] ?>" alt="<?= $row['judul'] ?>">
              <?php else: ?>
                <img src="https://via.placeholder.com/400x300?text=NewsHub" alt="Placeholder">
              <?php endif; ?>
            </div>
            <div class="news-content">
              <span class="news-category">Berita</span>
              <h3 class="news-title"><a href="berita.php?slug=<?= $row['slug'] ?>"><?= $row['judul'] ?></a></h3>
              <p class="news-excerpt"><?= substr(strip_tags($row['isi']), 0, 150) ?>...</p>
              <div class="news-meta">
                <span><i class="far fa-clock"></i> <?= date('d M Y', strtotime($row['created_at'])) ?></span>
                <span><i class="far fa-eye"></i> <?= $row['views'] ?></span>
              </div>
            </div>
          </article>
          <?php endwhile; ?>
        </div>
        
        <div class="pagination">
          <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a>
          <?php endif; ?>
          
          <?php 
          // Show limited pagination links
          $start_page = max(1, $page - 2);
          $end_page = min($total_pages, $page + 2);
          
          if ($start_page > 1) {
            echo '<a href="?page=1">1</a>';
            if ($start_page > 2) echo '<span>...</span>';
          }
          
          for ($i = $start_page; $i <= $end_page; $i++): ?>
            <a href="?page=<?= $i ?>" <?= ($i == $page) ? 'class="active"' : '' ?>><?= $i ?></a>
          <?php endfor;
          
          if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) echo '<span>...</span>';
            echo '<a href="?page='.$total_pages.'">'.$total_pages.'</a>';
          }
          ?>
          
          <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a>
          <?php endif; ?>
        </div>
      </section>
      
      <aside class="sidebar">
        <div class="sidebar-widget">
          <h3 class="widget-title">Berita Populer</h3>
          <ul class="popular-list">
            <?php 
            $counter = 1;
            mysqli_data_seek($populer, 0); // Reset pointer
            while ($pop = mysqli_fetch_assoc($populer)): ?>
              <li>
                <a href="berita.php?slug=<?= $pop['slug'] ?>">
                  <span class="popular-number"><?= $counter ?></span>
                  <span class="popular-title"><?= $pop['judul'] ?></span>
                </a>
              </li>
            <?php 
              $counter++;
            endwhile; ?>
          </ul>
        </div>
        
        <div class="sidebar-widget newsletter">
          <h3 class="widget-title">Newsletter</h3>
          <p>Dapatkan berita terbaru langsung ke email Anda</p>
          <form class="newsletter-form">
            <input type="email" placeholder="Alamat email Anda" required>
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
          </form>
        </div>
        
        <div class="sidebar-widget">
          <h3 class="widget-title">Kategori</h3>
          <ul class="category-list">
            <li><a href="#"><i class="fas fa-landmark"></i> Politik</a></li>
            <li><a href="#"><i class="fas fa-chart-line"></i> Ekonomi</a></li>
            <li><a href="#"><i class="fas fa-microchip"></i> Teknologi</a></li>
            <li><a href="#"><i class="fas fa-running"></i> Olahraga</a></li>
            <li><a href="#"><i class="fas fa-heartbeat"></i> Kesehatan</a></li>
            <li><a href="#"><i class="fas fa-graduation-cap"></i> Pendidikan</a></li>
          </ul>
        </div>
      </aside>
    </div>
  </main>
  
  <footer>
    <div class="container">
      <div class="footer-container">
        <div class="footer-about">
          <a href="index.php" class="footer-logo">News<span>Hub</span></a>
          <p>NewsHub menyajikan berita terkini dan terpercaya dari seluruh Indonesia dan dunia. Selalu update dengan perkembangan terbaru di berbagai bidang.</p>
          <div class="social-links">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
          </div>
        </div>
        
        <div class="footer-links">
          <h3>Tautan Cepat</h3>
          <ul>
            <li><a href="#">Tentang Kami</a></li>
            <li><a href="#">Kontak</a></li>
            <li><a href="#">Kebijakan Privasi</a></li>
            <li><a href="#">Syarat & Ketentuan</a></li>
            <li><a href="#">Karir</a></li>
          </ul>
        </div>
        
        <div class="footer-links">
          <h3>Kategori</h3>
          <ul>
            <li><a href="#">Politik</a></li>
            <li><a href="#">Bisnis</a></li>
            <li><a href="#">Teknologi</a></li>
            <li><a href="#">Olahraga</a></li>
            <li><a href="#">Hiburan</a></li>
          </ul>
        </div>
        
        <div class="footer-contact">
          <h3>Kontak Kami</h3>
          <p><i class="fas fa-map-marker-alt"></i> Jl. Contoh No. 123, Jakarta</p>
          <p><i class="fas fa-phone"></i> (021) 1234-5678</p>
          <p><i class="fas fa-envelope"></i> info@newshub.com</p>
        </div>
      </div>
      
      <div class="copyright">
        <p>&copy; <?= date('Y') ?> NewsHub. All Rights Reserved.</p>
      </div>
    </div>
  </footer>
</body>
</html>
