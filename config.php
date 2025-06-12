<?php
// Tampilkan semua error saat development, sembunyikan saat production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// PENTING: Ganti dengan API Key Anda yang valid dari NewsAPI.org
define('NEWS_API_KEY', '472cde823299429fa11bb2108e06cb26');

// Kategori yang akan diambil oleh fetcher.php
define('CATEGORIES', ['general', 'business', 'technology', 'sports', 'health', 'entertainment']);