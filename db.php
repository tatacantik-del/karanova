<?php
// config/db.php
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "database_kasanova";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
  $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
  $conn->set_charset("utf8mb4");
} catch (Exception $e) {
  die("Koneksi DB gagal: " . $e->getMessage());
}

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>