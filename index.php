<?php
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