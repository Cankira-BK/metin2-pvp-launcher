<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();
$message = '';
$error = '';

// Ayar güncelleme
if (isset($_POST['update_settings'])) {
    try {
        foreach ($_POST as $key => $value) {
            if ($key !== 'update_settings') {
                $db->execute(
                    "UPDATE settings SET setting_value = ? WHERE setting_key = ?",
                    [sanitize($value), $key]
                );
            }
        }
        $message = 'Ayarlar başarıyla güncellendi!';
    } catch (Exception $e) {
        $error = 'Hata: ' . $e->getMessage();
    }
}

// Ayarları çek
$settings = [];
$result = $db->fetchAll("SELECT * FROM settings");
foreach ($result as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Ayarları - Admin Panel</title>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <div class="header">
        <h1>⚙️ Site Ayarları</h1>
        <div class="header-right">
            <a href="index.php" class="btn btn-small">← Admin Panel</a>
            <a href="../index.php" class="btn btn-small" target="_blank">Siteyi Görüntüle</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>📝 Genel Ayarlar</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Site Başlığı</label>
                        <input type="text" name="site_title" value="<?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Sabit Telefon</label>
                        <input type="text" name="site_phone" value="<?php echo htmlspecialchars($settings['site_phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Cep Telefonu</label>
                        <input type="text" name="site_mobile" value="<?php echo htmlspecialchars($settings['site_mobile'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>E-posta Adresi</label>
                        <input type="email" name="site_email" value="<?php echo htmlspecialchars($settings['site_email'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Adres</label>
                        <input type="text" name="site_address" value="<?php echo htmlspecialchars($settings['site_address'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>WhatsApp Numarası (905321234567 formatında)</label>
                        <input type="text" name="whatsapp_number" value="<?php echo htmlspecialchars($settings['whatsapp_number'] ?? ''); ?>" placeholder="905321234567">
                        <small style="color: #666;">Ülke kodu ile birlikte, başında sıfır olmadan yazın</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Sahibinden Profil Linki</label>
                        <input type="url" name="sahibinden_profile" value="<?php echo htmlspecialchars($settings['sahibinden_profile'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Facebook URL</label>
                        <input type="url" name="facebook_url" value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Instagram URL</label>
                        <input type="url" name="instagram_url" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>YouTube URL</label>
                        <input type="url" name="youtube_url" value="<?php echo htmlspecialchars($settings['youtube_url'] ?? ''); ?>">
                    </div>
                </div>
                
                <div style="margin-top: 2rem;">
<button type="submit" name="update_settings" class="btn">💾 Ayarları Kaydet</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>🔐 Güvenlik</h2>
            <p style="margin-bottom: 1rem;">Güvenlik ayarları için:</p>
            <ul style="list-style: none; padding: 0;">
                <li style="padding: 0.5rem 0;">✅ Admin şifrenizi düzenli değiştirin</li>
                <li style="padding: 0.5rem 0;">✅ SSL sertifikası kullanın (HTTPS)</li>
                <li style="padding: 0.5rem 0;">✅ Düzenli veritabanı yedeği alın</li>
            </ul>
            <div style="margin-top: 1rem;">
                <a href="change_password.php" class="btn">🔑 Şifre Değiştir</a>
                <a href="security_logs.php" class="btn btn-secondary">📋 Güvenlik Logları</a>
            </div>
        </div>

        <div class="card">
            <h2>ℹ️ Sistem Bilgileri</h2>
            <table style="margin-top: 1rem;">
                <tr>
                    <td style="font-weight: bold;">PHP Versiyonu:</td>
                    <td><?php echo phpversion(); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Veritabanı:</td>
                    <td><?php echo DB_NAME; ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Sunucu:</td>
                    <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Bilinmiyor'; ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Admin IP:</td>
                    <td><?php echo $_SERVER['REMOTE_ADDR'] ?? 'Bilinmiyor'; ?></td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>