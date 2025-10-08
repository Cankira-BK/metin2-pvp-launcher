<?php
session_start();
require_once '../config/database.php';

// Giriş kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();
$message = '';
$error = '';

// Araç ekleme
if (isset($_POST['add_vehicle'])) {
    try {
        $sql = "INSERT INTO vehicles (title, price, year, km, fuel, transmission, color, body_type, description, image, sahibinden_link, is_featured, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        
        $db->insert($sql, [
            sanitize($_POST['title']),
            sanitize($_POST['price']),
            sanitize($_POST['year']),
            sanitize($_POST['km']),
            $_POST['fuel'],
            $_POST['transmission'] ?? 'Manuel',
            sanitize($_POST['color'] ?? ''),
            sanitize($_POST['body_type'] ?? ''),
            sanitize($_POST['description'] ?? ''),
            sanitize($_POST['image']),
            sanitize($_POST['sahibinden_link'] ?? ''),
            isset($_POST['is_featured']) ? 1 : 0
        ]);
        
        logSecurity('data_change', $_SESSION['admin_username'], 'Vehicle added');
        $message = 'Araç başarıyla eklendi!';
    } catch (Exception $e) {
        $error = 'Hata: ' . $e->getMessage();
    }
}

// Araç silme
if (isset($_GET['delete'])) {
    try {
        $db->execute("DELETE FROM vehicles WHERE id = ?", [(int)$_GET['delete']]);
        logSecurity('data_change', $_SESSION['admin_username'], 'Vehicle deleted: ' . $_GET['delete']);
        $message = 'Araç silindi!';
        header('Location: index.php?msg=deleted');
        exit;
    } catch (Exception $e) {
        $error = 'Silme hatası: ' . $e->getMessage();
    }
}

// Araç düzenleme
if (isset($_POST['edit_vehicle'])) {
    try {
        $sql = "UPDATE vehicles SET title=?, price=?, year=?, km=?, fuel=?, transmission=?, color=?, body_type=?, description=?, image=?, sahibinden_link=?, is_featured=? WHERE id=?";
        
        $db->execute($sql, [
            sanitize($_POST['title']),
            sanitize($_POST['price']),
            sanitize($_POST['year']),
            sanitize($_POST['km']),
            $_POST['fuel'],
            $_POST['transmission'] ?? 'Manuel',
            sanitize($_POST['color'] ?? ''),
            sanitize($_POST['body_type'] ?? ''),
            sanitize($_POST['description'] ?? ''),
            sanitize($_POST['image']),
            sanitize($_POST['sahibinden_link'] ?? ''),
            isset($_POST['is_featured']) ? 1 : 0,
            (int)$_POST['id']
        ]);
        
        logSecurity('data_change', $_SESSION['admin_username'], 'Vehicle updated: ' . $_POST['id']);
        $message = 'Araç güncellendi!';
        header('Location: index.php?msg=updated');
        exit;
    } catch (Exception $e) {
        $error = 'Güncelleme hatası: ' . $e->getMessage();
    }
}

// Mesaj
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') $message = 'Araç silindi!';
    if ($_GET['msg'] == 'updated') $message = 'Araç güncellendi!';
}

// İstatistikler
$stats = [
    'total_vehicles' => $db->fetchOne("SELECT COUNT(*) as count FROM vehicles WHERE status='active'")['count'] ?? 0,
    'featured_vehicles' => $db->fetchOne("SELECT COUNT(*) as count FROM vehicles WHERE is_featured=1 AND status='active'")['count'] ?? 0,
    'total_offers' => $db->fetchOne("SELECT COUNT(*) as count FROM offers")['count'] ?? 0,
    'new_offers' => $db->fetchOne("SELECT COUNT(*) as count FROM offers WHERE status='new'")['count'] ?? 0,
    'total_views' => $db->fetchOne("SELECT SUM(views) as total FROM vehicles")['total'] ?? 0
];

// Araçları çek
$vehicles = $db->fetchAll("SELECT * FROM vehicles ORDER BY created_at DESC");

// Düzenlenecek aracı çek
$editVehicle = null;
if (isset($_GET['edit'])) {
    $editVehicle = $db->fetchOne("SELECT * FROM vehicles WHERE id = ?", [(int)$_GET['edit']]);
}

// Son teklifleri çek
$recentOffers = $db->fetchAll("SELECT o.*, v.title as vehicle_title FROM offers o LEFT JOIN vehicles v ON o.vehicle_id = v.id ORDER BY o.created_at DESC LIMIT 5");

// Çıkış
if (isset($_GET['logout'])) {
    logSecurity('logout', $_SESSION['admin_username'], 'Admin logout');
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Güçlü Otomotiv</title>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <div class="header">
        <h1>🚗 Güçlü Otomotiv - Admin Panel</h1>
        <div class="header-right">
            <span>Hoş geldin, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="../index.php" class="btn btn-small" target="_blank">Siteyi Görüntüle</a>
            <a href="?logout=1" class="btn btn-small btn-danger">Çıkış</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- İstatistikler -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Toplam Araç</h3>
                <div class="number"><?php echo $stats['total_vehicles']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Öne Çıkan</h3>
                <div class="number"><?php echo $stats['featured_vehicles']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Toplam Teklif</h3>
                <div class="number"><?php echo $stats['total_offers']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Yeni Teklif</h3>
                <div class="number" style="color: #dc3545;"><?php echo $stats['new_offers']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Görüntülenme</h3>
                <div class="number"><?php echo number_format($stats['total_views']); ?></div>
            </div>
        </div>

        <!-- Tab Menü -->
        <div class="tab-menu">
            <button class="tab-btn active" onclick="openTab(event, 'vehicles')">🚗 Araçlar</button>
            <button class="tab-btn" onclick="openTab(event, 'offers')">📝 Teklifler (<?php echo $stats['new_offers']; ?>)</button>
            <button class="tab-btn" onclick="openTab(event, 'settings')">⚙️ Ayarlar</button>
        </div>

        <!-- Araçlar Tab -->
        <div id="vehicles" class="tab-content active">
            <div class="card">
                <h2><?php echo $editVehicle ? 'Araç Düzenle' : 'Yeni Araç Ekle'; ?></h2>
                <form method="POST">
                    <?php if ($editVehicle): ?>
                        <input type="hidden" name="id" value="<?php echo $editVehicle['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Araç Başlığı *</label>
                            <input type="text" name="title" required value="<?php echo $editVehicle ? htmlspecialchars($editVehicle['title']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Fiyat *</label>
                            <input type="text" name="price" required placeholder="Örn: 1.142.000 TL" value="<?php echo $editVehicle ? htmlspecialchars($editVehicle['price']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Yıl *</label>
                            <input type="text" name="year" required value="<?php echo $editVehicle ? htmlspecialchars($editVehicle['year']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Kilometre *</label>
                            <input type="text" name="km" required value="<?php echo $editVehicle ? htmlspecialchars($editVehicle['km']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Yakıt *</label>
                            <select name="fuel" required>
                                <option value="">Seçiniz</option>
                                <option value="Dizel" <?php echo ($editVehicle && $editVehicle['fuel'] === 'Dizel') ? 'selected' : ''; ?>>Dizel</option>
                                <option value="Benzin" <?php echo ($editVehicle && $editVehicle['fuel'] === 'Benzin') ? 'selected' : ''; ?>>Benzin</option>
                                <option value="Benzin/LPG" <?php echo ($editVehicle && $editVehicle['fuel'] === 'Benzin/LPG') ? 'selected' : ''; ?>>Benzin/LPG</option>
                                <option value="Hybrid" <?php echo ($editVehicle && $editVehicle['fuel'] === 'Hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                                <option value="Elektrik" <?php echo ($editVehicle && $editVehicle['fuel'] === 'Elektrik') ? 'selected' : ''; ?>>Elektrik</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Vites *</label>
                            <select name="transmission">
                                <option value="Manuel" <?php echo ($editVehicle && $editVehicle['transmission'] === 'Manuel') ? 'selected' : ''; ?>>Manuel</option>
                                <option value="Otomatik" <?php echo ($editVehicle && $editVehicle['transmission'] === 'Otomatik') ? 'selected' : ''; ?>>Otomatik</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Renk</label>
                            <input type="text" name="color" value="<?php echo $editVehicle ? htmlspecialchars($editVehicle['color']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Kasa Tipi</label>
                            <input type="text" name="body_type" value="<?php echo $editVehicle ? htmlspecialchars($editVehicle['body_type']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Resim URL *</label>
                            <input type="url" name="image" required value="<?php echo $editVehicle ? htmlspecialchars($editVehicle['image']) : ''; ?>">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Sahibinden Link</label>
                            <input type="url" name="sahibinden_link" value="<?php echo $editVehicle ? htmlspecialchars($editVehicle['sahibinden_link']) : ''; ?>">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Açıklama</label>
                            <textarea name="description" rows="3"><?php echo $editVehicle ? htmlspecialchars($editVehicle['description']) : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label><input type="checkbox" name="is_featured" value="1" <?php echo ($editVehicle && $editVehicle['is_featured']) ? 'checked' : ''; ?>> ⭐ Öne Çıkan (Slider)</label>
                        </div>
                    </div>
                    
                    <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                        <button type="submit" name="<?php echo $editVehicle ? 'edit_vehicle' : 'add_vehicle'; ?>" class="btn">
                            <?php echo $editVehicle ? '✓ Güncelle' : '+ Ekle'; ?>
                        </button>
                        <?php if ($editVehicle): ?>
                            <a href="index.php" class="btn btn-secondary">İptal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2>Araç Listesi (<?php echo count($vehicles); ?>)</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Resim</th>
                                <th>Başlık</th>
                                <th>Fiyat</th>
                                <th>Yıl/KM</th>
                                <th>Görüntülenme</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehicles as $v): ?>
                                <tr>
                                    <td><img src="<?php echo htmlspecialchars($v['image']); ?>" style="width: 60px; height: 45px; object-fit: cover; border-radius: 5px;"></td>
                                    <td>
                                        <?php echo htmlspecialchars($v['title']); ?>
                                        <?php if ($v['is_featured']): ?><span class="badge">⭐</span><?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($v['price']); ?></td>
                                    <td><?php echo htmlspecialchars($v['year']); ?> / <?php echo htmlspecialchars($v['km']); ?></td>
                                    <td><?php echo number_format($v['views']); ?></td>
                                    <td>
                                        <a href="?edit=<?php echo $v['id']; ?>" class="btn btn-small">Düzenle</a>
                                        <a href="?delete=<?php echo $v['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Silmek istediğinizden emin misiniz?')">Sil</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Teklifler Tab -->
        <div id="offers" class="tab-content">
            <div class="card">
                <h2>Gelen Teklifler</h2>
                <?php if (empty($recentOffers)): ?>
                    <p style="text-align: center; padding: 2rem; color: #666;">Henüz teklif yok.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tarih</th>
                                    <th>Tür</th>
                                    <th>Müşteri</th>
                                    <th>Telefon</th>
                                    <th>Araç</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOffers as $o): ?>
                                    <tr>
                                        <td><?php echo date('d.m.Y H:i', strtotime($o['created_at'])); ?></td>
                                        <td><?php $types = ['buy' => '🛒 Alım', 'sell' => '💰 Satım', 'exchange' => '🔄 Takas']; echo $types[$o['offer_type']] ?? ''; ?></td>
                                        <td><?php echo htmlspecialchars($o['customer_name']); ?></td>
                                        <td><a href="tel:<?php echo htmlspecialchars($o['customer_phone']); ?>"><?php echo htmlspecialchars($o['customer_phone']); ?></a></td>
                                        <td><?php echo htmlspecialchars($o['vehicle_title'] ?? 'Genel'); ?></td>
                                        <td><a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $o['customer_phone']); ?>" target="_blank" class="btn btn-small btn-success">💬 WhatsApp</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ayarlar Tab -->
        <div id="settings" class="tab-content">
            <div class="card">
                <h2>⚙️ Site Ayarları</h2>
                <p style="margin-bottom: 1rem;">Site ayarlarını yönetmek için:</p>
                <a href="settings.php" class="btn">Site Ayarlarına Git</a>
            </div>
        </div>
    </div>

    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
    </script>
</body>
</html>