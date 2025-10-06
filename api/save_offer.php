<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Rate limiting
if (!checkRateLimit('offer_submit', 3, 3600)) { // 3 teklif/saat
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Çok fazla istek gönderdiniz. Lütfen daha sonra tekrar deneyin.']);
    exit;
}

// POST kontrolü
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Geçersiz istek']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Veri doğrulama
    $required = ['offer_type', 'customer_name', 'customer_phone'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception(ucfirst($field) . ' alanı gereklidir');
        }
    }
    
    $offerType = sanitize($_POST['offer_type']);
    if (!in_array($offerType, ['buy', 'sell', 'exchange'])) {
        throw new Exception('Geçersiz teklif türü');
    }
    
    $customerName = sanitize($_POST['customer_name']);
    $customerPhone = sanitize($_POST['customer_phone']);
    $customerEmail = sanitize($_POST['customer_email'] ?? '');
    $vehicleInfo = sanitize($_POST['vehicle_info'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $vehicleId = !empty($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : null;
    
    // Telefon numarası validasyonu
    if (!preg_match('/^[0-9]{10,11}$/', preg_replace('/[^0-9]/', '', $customerPhone))) {
        throw new Exception('Geçersiz telefon numarası');
    }
    
    // Veritabanına kaydet
    $sql = "INSERT INTO offers (vehicle_id, offer_type, customer_name, customer_phone, customer_email, vehicle_info, message, ip_address, user_agent, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'new')";
    
    $offerId = $db->insert($sql, [
        $vehicleId,
        $offerType,
        $customerName,
        $customerPhone,
        $customerEmail,
        $vehicleInfo,
        $message,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
    
    // Başarılı log
    logSecurity('offer_created', $customerName, "Offer ID: $offerId, Type: $offerType");
    
    echo json_encode([
        'success' => true,
        'message' => 'Teklifiniz başarıyla kaydedildi',
        'offer_id' => $offerId
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    error_log("Offer save error: " . $e->getMessage());
}