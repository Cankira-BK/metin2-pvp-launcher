<?php
// YENİ ŞİFRENİZİ BURAYA YAZIN
$yeni_sifre = "güvenli_şifreniz_123";

echo password_hash($yeni_sifre, PASSWORD_DEFAULT);
?>