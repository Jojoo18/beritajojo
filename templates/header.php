<?php
// Ambil variabel global agar bisa digunakan di template
global $page_title, $filter_kategori; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="main-header">
    <div class="container">
        <a href="index.php" class="logo">BeritaViral</a>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php" class="<?= empty($filter_kategori) ? 'active' : '' ?>">Home</a></li>
                <li><a href="index.php?kategori=teknologi" class="<?= ($filter_kategori === 'teknologi') ? 'active' : '' ?>">Teknologi</a></li>
                <li><a href="index.php?kategori=olahraga" class="<?= ($filter_kategori === 'olahraga') ? 'active' : '' ?>">Olahraga</a></li>
                <li><a href="index.php?kategori=hiburan" class="<?= ($filter_kategori === 'hiburan') ? 'active' : '' ?>">Hiburan</a></li>
            </ul>
        </nav>
    </div>
</header>