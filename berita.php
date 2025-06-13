<?php
include 'koneksi.php';

$slug = $_GET['slug'];
// Gunakan prepared statement untuk mencegah SQL injection
$stmt = $conn->prepare("UPDATE berita SET views = views + 1 WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();

$stmt = $conn->prepare("SELECT * FROM berita WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    header("HTTP/1.0 404 Not Found");
    echo "<!DOCTYPE html>
    <html>
    <head>
      <title>Berita Tidak Ditemukan</title>
      <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
      <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
      <style>
        .error-container {
          background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
          min-height: 100vh;
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
        }
        .error-card {
          background: rgba(255,255,255,0.1);
          backdrop-filter: blur(10px);
          border-radius: 20px;
          padding: 3rem;
          text-align: center;
          box-shadow: 0 15px 30px rgba(0,0,0,0.2);
          border: 1px solid rgba(255,255,255,0.2);
        }
        .error-icon {
          font-size: 5rem;
          margin-bottom: 1.5rem;
          color: #ffca28;
        }
      </style>
    </head>
    <body>
      <div class='error-container'>
        <div class='error-card'>
          <i class='fas fa-exclamation-triangle error-icon'></i>
          <h1 class='display-4 fw-bold mb-3'>404 - Berita Tidak Ditemukan</h1>
          <p class='lead mb-4'>Maaf, berita yang Anda cari tidak tersedia atau mungkin telah dihapus.</p>
          <a href='index.php' class='btn btn-light btn-lg px-4 py-2 rounded-pill'>
            <i class='fas fa-arrow-left me-2'></i>Kembali ke Beranda
          </a>
        </div>
      </div>
    </body>
    </html>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($data['judul']) ?> | BeritaViral</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #2563eb;
      --primary-dark: #1d4ed8;
      --secondary: #f59e0b;
      --dark: #1e293b;
      --light: #f8fafc;
      --gray: #64748b;
      --light-gray: #e2e8f0;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--light);
      color: var(--dark);
      line-height: 1.6;
    }
    
    h1, h2, h3, h4, h5, h6 {
      font-family: 'Montserrat', sans-serif;
      font-weight: 600;
    }
    
    .navbar-brand {
      font-family: 'Montserrat', sans-serif;
      font-weight: 700;
      font-size: 1.8rem;
      color: var(--primary);
    }
    
    .article-hero {
      position: relative;
      margin-bottom: 2rem;
    }
    
    .article-hero-img {
      width: 100%;
      max-height: 600px;
      object-fit: cover;
      border-radius: 12px;
      box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
      display: block;
      margin: 0 auto;
    }
    
    .article-header {
      max-width: 900px;
      margin: 0 auto;
      padding: 2rem 0;
    }
    
    .article-title {
      font-size: 2.5rem;
      line-height: 1.2;
      margin-bottom: 1.5rem;
      color: var(--dark);
    }
    
    .article-meta {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      margin-bottom: 1.5rem;
      color: var(--gray);
      flex-wrap: wrap;
    }
    
    .article-meta-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.9rem;
    }
    
    .article-content {
      max-width: 800px;
      margin: 0 auto;
      padding: 2rem 0;
      font-size: 1.1rem;
      line-height: 1.8;
    }
    
    .article-content p {
      margin-bottom: 1.5rem;
    }
    
    .article-content img {
      max-width: 100%;
      height: auto;
      border-radius: 8px;
      margin: 2rem auto;
      display: block;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .share-section {
      background: var(--light-gray);
      padding: 2rem;
      border-radius: 12px;
      margin: 3rem 0;
      text-align: center;
    }
    
    .share-btn {
      border-radius: 8px;
      padding: 0.5rem 1rem;
      font-weight: 500;
      margin: 0.5rem;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
    }
    
    .share-btn i {
      margin-right: 0.5rem;
    }
    
    .btn-facebook {
      background: #1877f2;
      color: white;
    }
    
    .btn-twitter {
      background: #1da1f2;
      color: white;
    }
    
    .btn-whatsapp {
      background: #25d366;
      color: white;
    }
    
    .btn-linkedin {
      background: #0077b5;
      color: white;
    }
    
    .tag {
      display: inline-block;
      background: var(--light-gray);
      color: var(--dark);
      padding: 0.25rem 0.75rem;
      border-radius: 50px;
      font-size: 0.8rem;
      font-weight: 500;
      margin-right: 0.5rem;
      margin-bottom: 0.5rem;
    }
    
    .tag-primary {
      background: var(--primary);
      color: white;
    }
    
    .tag-secondary {
      background: var(--secondary);
      color: white;
    }
    
    .author-card {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      background: white;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      margin: 3rem 0;
    }
    
    .author-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      object-fit: cover;
    }
    
    .back-to-top {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: var(--primary);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      z-index: 100;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s;
    }
    
    .back-to-top.visible {
      opacity: 1;
      visibility: visible;
    }
    
    .back-to-top:hover {
      background: var(--primary-dark);
      transform: translateY(-3px);
    }
    
    footer {
      background: var(--dark);
      color: white;
      padding: 4rem 0 2rem;
      margin-top: 4rem;
    }
    
    .footer-logo {
      font-family: 'Montserrat', sans-serif;
      font-weight: 700;
      font-size: 1.8rem;
      margin-bottom: 1.5rem;
      color: white;
    }
    
    .footer-links h5 {
      font-size: 1.1rem;
      margin-bottom: 1.5rem;
      position: relative;
      padding-bottom: 0.5rem;
      font-weight: 600;
    }
    
    .footer-links h5::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      width: 40px;
      height: 2px;
      background: var(--secondary);
    }
    
    .footer-links ul {
      list-style: none;
      padding: 0;
    }
    
    .footer-links li {
      margin-bottom: 0.5rem;
    }
    
    .footer-links a {
      color: #94a3b8;
      text-decoration: none;
      transition: all 0.2s;
      font-size: 0.9rem;
    }
    
    .footer-links a:hover {
      color: white;
      padding-left: 5px;
    }
    
    .social-links a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      background: rgba(255,255,255,0.1);
      border-radius: 50%;
      color: white;
      margin-right: 0.5rem;
      transition: all 0.2s;
    }
    
    .social-links a:hover {
      background: var(--primary);
      transform: translateY(-3px);
    }
    
    .copyright {
      border-top: 1px solid rgba(255,255,255,0.1);
      padding-top: 2rem;
      margin-top: 3rem;
      text-align: center;
      color: #94a3b8;
      font-size: 0.9rem;
    }
    
    @media (max-width: 768px) {
      .article-title {
        font-size: 2rem;
      }
      
      .article-hero-img {
        max-height: 350px;
      }
      
      .article-meta {
        gap: 1rem;
      }
      
      .author-card {
        flex-direction: column;
        text-align: center;
      }
    }
  </style>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="index.php">BeritaViral</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="index.php">Beranda</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Kategori</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Tentang</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Kontak</a>
          </li>
        </ul>
        <form class="d-flex ms-3">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Cari berita..." aria-label="Search">
            <button class="btn btn-outline-primary" type="submit">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </form>
      </div>
    </div>
  </nav>

  <!-- Article Hero Image -->
  <div class="container my-4">
    <div class="article-hero">
      <?php
      // Ambil gambar dari database
      $gambar = !empty($data['gambar']) ? $data['gambar'] : 'https://via.placeholder.com/1200x600?text=NewsHub';
      ?>
      <img src="<?= htmlspecialchars($gambar) ?>" alt="<?= htmlspecialchars($data['judul']) ?>" class="article-hero-img">
    </div>
  </div>

  <!-- Article Header -->
  <div class="container">
    <div class="article-header">
      <div class="d-flex flex-wrap gap-2 mb-3">
        <span class="tag tag-primary">Berita Terkini</span>
        <span class="tag tag-secondary">Headline</span>
      </div>
      <h1 class="article-title"><?= htmlspecialchars($data['judul']) ?></h1>
      <div class="article-meta">
        <div class="article-meta-item">
          <i class="far fa-user"></i>
          <span>Admin NewsHub</span>
        </div>
        <div class="article-meta-item">
          <i class="far fa-calendar-alt"></i>
          <span><?= date('d F Y', strtotime($data['created_at'])) ?></span>
        </div>
        <div class="article-meta-item">
          <i class="far fa-eye"></i>
          <span><?= number_format($data['views'], 0, ',', '.') ?>x dilihat</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Article Content -->
  <div class="container">
    <div class="article-content">
      <?= nl2br(htmlspecialchars($data['isi'])) ?>
      
      <!-- Share Section -->
      <div class="share-section">
        <h5 class="mb-3"><i class="fas fa-share-alt me-2"></i>Bagikan berita ini:</h5>
        <div>
          <a href="https://facebook.com/sharer/sharer.php?u=<?= urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>" target="_blank" class="btn btn-facebook share-btn">
            <i class="fab fa-facebook-f"></i> Facebook
          </a>
          <a href="https://twitter.com/intent/tweet?url=<?= urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>&text=<?= urlencode($data['judul']) ?>" target="_blank" class="btn btn-twitter share-btn">
            <i class="fab fa-twitter"></i> Twitter
          </a>
          <a href="whatsapp://send?text=<?= urlencode($data['judul'] . " - Baca selengkapnya: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>" class="btn btn-whatsapp share-btn">
            <i class="fab fa-whatsapp"></i> WhatsApp
          </a>
          <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>&title=<?= urlencode($data['judul']) ?>" target="_blank" class="btn btn-linkedin share-btn">
            <i class="fab fa-linkedin-in"></i> LinkedIn
          </a>
        </div>
      </div>
      
      <!-- Author Card -->
      <div class="author-card">
        <img src="pp.jpg" alt="Author" class="author-avatar">
        <div>
          <h5 class="mb-1">Admin BeritaViral</h5>
          <p class="text-muted mb-2">Redaksi Utama</p>
          <p class="mb-0">Tim redaksi BeritaViral berkomitmen menyajikan berita yang akurat, aktual, dan bermanfaat bagi pembaca.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    <div class="container">
      <div class="row">
        <div class="col-lg-4 mb-4">
          <div class="footer-logo">BeritaViral</div>
          <p class="text-muted">Menyajikan berita terkini dan terpercaya seputar Indonesia dan dunia dengan penyampaian yang profesional dan mudah dipahami.</p>
          <div class="social-links mt-3">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
          </div>
        </div>
        <div class="col-lg-2 col-md-6 mb-4">
          <div class="footer-links">
            <h5>Kategori</h5>
            <ul>
              <li><a href="#">Politik</a></li>
              <li><a href="#">Ekonomi</a></li>
              <li><a href="#">Kesehatan</a></li>
              <li><a href="#">Teknologi</a></li>
              <li><a href="#">Olahraga</a></li>
            </ul>
          </div>
        </div>
        <div class="col-lg-2 col-md-6 mb-4">
          <div class="footer-links">
            <h5>Perusahaan</h5>
            <ul>
              <li><a href="#">Tentang Kami</a></li>
              <li><a href="#">Redaksi</a></li>
              <li><a href="#">Karir</a></li>
              <li><a href="#">Kebijakan Privasi</a></li>
              <li><a href="#">Pedoman Media</a></li>
            </ul>
          </div>
        </div>
        <div class="col-lg-4 mb-4">
          <div class="footer-links">
            <h5>Kontak Kami</h5>
            <ul class="text-muted">
              <li><i class="fas fa-map-marker-alt me-2"></i> Jl. Sudirman No. 123, Jakarta</li>
              <li><i class="fas fa-phone me-2"></i> +62 21 1234 5678</li>
              <li><i class="fas fa-envelope me-2"></i> redaksi@newshub.id</li>
            </ul>
          </div>
        </div>
      </div>
      <div class="copyright">
        <p class="mb-0">&copy; <?= date('Y') ?> NewsHub. All Rights Reserved.</p>
      </div>
    </div>
  </footer>

  <!-- Back to Top Button -->
  <a href="#" class="back-to-top" id="backToTop">
    <i class="fas fa-arrow-up"></i>
  </a>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Back to Top Button
    const backToTop = document.getElementById('backToTop');
    
    window.addEventListener('scroll', function() {
      if (window.pageYOffset > 300) {
        backToTop.classList.add('visible');
      } else {
        backToTop.classList.remove('visible');
      }
    });
    
    backToTop.addEventListener('click', function(e) {
      e.preventDefault();
      window.scrollTo({top: 0, behavior: 'smooth'});
    });
    
    // Add active class to current nav item
    document.addEventListener('DOMContentLoaded', function() {
      const currentPage = location.pathname.split('/').pop();
      const navLinks = document.querySelectorAll('.nav-link');
      
      navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
          link.classList.add('active');
        }
      });
    });
  </script>
</body>
</html>