<?php
$host = 'localhost';
$user = 'root'; // Sesuaikan dengan username database Anda
$pass = '';     // Sesuaikan dengan password database Anda
$db   = 'db_berita_portal'; // Sesuaikan dengan nama database Anda

$koneksi = new mysqli($host, $user, $pass, $db);

if ($koneksi->connect_error) {
    die("Koneksi ke database gagal: " . $koneksi->connect_error);
}
?>