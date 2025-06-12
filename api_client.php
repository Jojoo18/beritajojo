<?php
// Pastikan file config.php sudah di-include sebelum memanggil fungsi ini
if (!defined('NEWS_API_KEY')) {
    die("Konfigurasi API tidak ditemukan.");
}

function get_news(string $category): ?array {
    $url = 'https://newsapi.org/v2/top-headlines?country=us&category=' . urlencode($category) . '&pageSize=20&apiKey=' . NEWS_API_KEY;

    $options = ["http" => ["header" => "User-Agent: PortalBeritaPHP/1.0\r\n", "ignore_errors" => true]];
    $context = stream_context_create($options);
    $response_json = file_get_contents($url, false, $context);

    return $response_json ? json_decode($response_json, true) : null;
}
?>