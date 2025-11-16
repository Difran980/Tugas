<?php
$host = "localhost";
$username = 'admin';  // Ganti dengan username MySQL Anda
$password = 'masenida24';      // Ganti dengan password MySQL Anda 
$dbname = 'book_catalog';

$conn  = new mysqli ($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>