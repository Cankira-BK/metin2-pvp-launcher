<?php
$host = 'localhost'; //Genelde Localhost
$db = 'nuyacom_packadmin'; //Veritabanı Adı
$user = 'nuyacom_packadmin'; //Veritabanı Kullanıcı Adı
$pass = 'SNooP.,456'; //Şifre
$BASE_DIR  = __DIR__ . '/../1.0.0.0'; // PACK dosyalarının ekleneceği klasör
$TRASH_DIR = __DIR__ . '/../admin/cop_kutusu'; // Çöp kutusu dizini

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
session_start();

function secure_password($password) {
  return password_hash($password, PASSWORD_DEFAULT);
}
?>