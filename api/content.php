<?php
header("Content-Type: application/json; charset=utf-8");

// Yedek: içerik dosya yolu
$content_file = __DIR__ . '/../data/content.txt'; // Dilersen yolu deðiþtir!
$admin_password = "SNooP.,456";

// POST ile içerik güncelle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['password']) || $data['password'] !== $admin_password) {
        echo json_encode(["success" => false, "msg" => "Yetkisiz eriþim!"]);
        exit;
    }
    $content = isset($data['content']) ? trim($data['content']) : "";
    if ($content === "") {
        echo json_encode(["success" => false, "msg" => "Ýçerik boþ olamaz!"]);
        exit;
    }
    file_put_contents($content_file, $content);
    echo json_encode(["success" => true]);
    exit;
}

// GET ile içerik oku
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $content = "";
    if (file_exists($content_file)) {
        $content = file_get_contents($content_file);
    }
    echo json_encode([
        "success" => true,
        "content" => $content
    ]);
    exit;
}

echo json_encode(["success" => false, "msg" => "Geçersiz istek!"]);
exit;
?>
