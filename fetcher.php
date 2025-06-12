<?php
// Script ini untuk dijalankan oleh Cron Job (server), bukan diakses dari browser.
// Untuk menjalankannya manual via terminal: php /path/to/your/project/fetcher.php
echo "Memulai proses pengambilan berita...\n\n";

// Mengatur agar script tidak timeout jika prosesnya lama
set_time_limit(300); // 5 menit

// Memuat file-file yang dibutuhkan
require_once 'config.php';
require_once 'api_client.php';
require_once 'koneksi.php';

function createSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    return empty($text) ? 'n-a-' . time() : $text;
}

$totalBeritaBaru = 0;
foreach (CATEGORIES as $kategori) {
    echo "=> Mengambil kategori: " . ucfirst($kategori) . "\n";
    $data = get_news($kategori);

    if ($data && $data['status'] == 'ok' && !empty($data['articles'])) {
        foreach ($data['articles'] as $artikel) {
            // Hanya proses jika judul dan URL sumber ada
            if (empty($artikel['title']) || empty($artikel['url'])) continue;

            $judul = $artikel['title'];
            $slug = createSlug($judul);
            // Gunakan content jika ada, jika tidak pakai description
            $isi_berita = !empty($artikel['content']) ? $artikel['content'] : ($artikel['description'] ?? '');
            $sumber = $artikel['source']['name'];
            $url_sumber = $artikel['url'];
            $gambar = !empty($artikel['urlToImage']) ? $artikel['urlToImage'] : 'https://via.placeholder.com/800x400.png?text=No+Image';
            $tanggal_publish = date("Y-m-d H:i:s", strtotime($artikel['publishedAt']));
            $penulis = $artikel['author'] ?? 'Redaksi';

            $kategori_berita = $kategori;
            $stmt = $koneksi->prepare(
                "INSERT INTO berita (judul, slug, isi_berita, sumber, url_sumber, gambar, tanggal_publish, penulis, kategori) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sssssssss", $judul, $slug, $isi_berita, $sumber, $url_sumber, $gambar, $tanggal_publish, $penulis, $kategori_berita);

            try {
                $stmt->execute();
                echo "   [OK] Disimpan: " . substr($judul, 0, 50) . "...\n";
                $totalBeritaBaru++;
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) {
                    // Duplikat, abaikan saja
                    echo "   [DUPLIKAT] " . substr($judul, 0, 50) . "...\n";
                } else {
                    echo "   [GAGAL] Error: " . $e->getMessage() . "\n";
                }
            }
            $stmt->close();
        }
    } else {
        echo "   [INFO] Tidak ada artikel atau terjadi error pada API untuk kategori ini.\n";
    }
    sleep(1); // Jeda 1 detik untuk menghormati rate limit API
}

$koneksi->close();
echo "\nProses Selesai. " . $totalBeritaBaru . " berita baru ditambahkan ke database.\n";
?>