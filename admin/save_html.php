<?php
session_start();
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) { die("Yetkisiz erişim!"); }
if (isset($_POST['content'])) {
    $indexFile = '../index.html';
    $content = $_POST['content'];

    // Basit script engelleme
    if (stripos($content, '<script') !== false) {
        echo "Script etiketi kullanılamaz!";
        exit;
    }
    // Yedekle
    if (file_exists($indexFile)) {
        copy($indexFile, $indexFile . '.bak_' . date('Ymd_His'));
    }
    if (file_put_contents($indexFile, $content) !== false) {
        echo "Başarıyla kaydedildi!";
    } else {
        echo "Kaydedilemedi!";
    }
} else {
    echo "Veri gelmedi!";
}
?>
