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
    } catch (Exception $e) {
        $error = 'Güncelleme hatası: ' . $e->getMessage();
    }
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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            line-height: 1.6;
        }
        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header h1 { font-size: 1.5rem; }
        .header-right { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; }
        .btn {
            padding: 0.6rem 1.2rem;
            background: #ffd700;
            color: #1a1a2e;
            text-decoration: none;
            border-bottom: 3px solid #ffd700;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .stat-card .number {
            color: #16213e;
            font-size: 2rem;
            font-weight: bold;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            color: #333;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ffd700;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: #16213e;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .vehicle-img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .tab-menu {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e0e0e0;
        }
        .tab-btn {
            padding: 1rem 2rem;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        .tab-btn.active {
            color: #16213e;
            border-bottom-color: #ffd700;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
            }
            .header-right {
                justify-content: center;
            }
            table {
                font-size: 0.9rem;
            }
            th, td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚗 Güçlü Otomotiv - Admin Panel</h1>
        <div class="header-right">
            <span>Hoş geldin, <?php echo cleanOutput($_SESSION['admin_username']); ?></span>
            <a href="../index.php" class="btn btn-small" target="_blank">Siteyi Görüntüle</a>
            <a href="?logout=1" class="btn btn-small btn-danger">Çıkış</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?php echo cleanOutput($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo cleanOutput($error); ?></div>
        <?php endif; ?>

        <!-- İstatistikler -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Toplam Araç</h3>
                <div class="number"><?php echo $stats['total_vehicles']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Öne Çıkan Araçlar</h3>
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
                <h3>Toplam Görüntülenme</h3>
                <div class="number"><?php echo number_format($stats['total_views']); ?></div>
            </div>
        </div>

        <!-- Tab Menü -->
        <div class="tab-menu">
            <button class="tab-btn active" onclick="openTab('vehicles')">🚗 Araçlar</button>
            <button class="tab-btn" onclick="openTab('offers')">📝 Teklifler (<?php echo $stats['new_offers']; ?>)</button>
            <button class="tab-btn" onclick="openTab('settings')">⚙️ Ayarlar</button>
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
                            <input type="text" name="title" required 
                                   value="<?php echo $editVehicle ? cleanOutput($editVehicle['title']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Fiyat *</label>
                            <input type="text" name="price" required placeholder="Örn: 1.142.000 TL"
                                   value="<?php echo $editVehicle ? cleanOutput($editVehicle['price']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Yıl *</label>
                            <input type="text" name="year" required placeholder="Örn: 2024"
                                   value="<?php echo $editVehicle ? cleanOutput($editVehicle['year']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Kilometre *</label>
                            <input type="text" name="km" required placeholder="Örn: 18.500 km"
                                   value="<?php echo $editVehicle ? cleanOutput($editVehicle['km']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Yakıt Tipi *</label>
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
                            <select name="transmission" required>
                                <option value="Manuel" <?php echo ($editVehicle && $editVehicle['transmission'] === 'Manuel') ? 'selected' : ''; ?>>Manuel</option>
                                <option value="Otomatik" <?php echo ($editVehicle && $editVehicle['transmission'] === 'Otomatik') ? 'selected' : ''; ?>>Otomatik</option>
                                <option value="Yarı Otomatik" <?php echo ($editVehicle && $editVehicle['transmission'] === 'Yarı Otomatik') ? 'selected' : ''; ?>>Yarı Otomatik</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Renk</label>
                            <input type="text" name="color" placeholder="Örn: Beyaz"
                                   value="<?php echo $editVehicle ? cleanOutput($editVehicle['color']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Kasa Tipi</label>
                            <input type="text" name="body_type" placeholder="Örn: Sedan, SUV, Hatchback"
                                   value="<?php echo $editVehicle ? cleanOutput($editVehicle['body_type']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Resim URL *</label>
                            <input type="url" name="image" required placeholder="https://..."
                                   value="<?php echo $editVehicle ? cleanOutput($editVehicle['image']) : ''; ?>">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Sahibinden Link</label>
                            <input type="url" name="sahibinden_link" placeholder="https://www.sahibinden.com/..."
                                   value="<?php echo $editVehicle ? cleanOutput($editVehicle['sahibinden_link']) : ''; ?>">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Açıklama</label>
                            <textarea name="description" rows="3" placeholder="Araç hakkında detaylı bilgi..."><?php echo $editVehicle ? cleanOutput($editVehicle['description']) : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" name="is_featured" id="is_featured" value="1" 
                                       <?php echo ($editVehicle && $editVehicle['is_featured']) ? 'checked' : ''; ?>>
                                <label for="is_featured" style="margin: 0;">⭐ Öne Çıkan Araç (Slider'da göster)</label>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                        <button type="submit" name="<?php echo $editVehicle ? 'edit_vehicle' : 'add_vehicle'; ?>" class="btn">
                            <?php echo $editVehicle ? '✓ Güncelle' : '+ Ekle'; ?>
                        </button>
                        <?php if ($editVehicle): ?>
                            <a href="index.php" class="btn" style="background: #6c757d; color: white;">İptal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2>Araç Listesi</h2>
                <?php if (empty($vehicles)): ?>
                    <p style="text-align: center; color: #666; padding: 2rem;">Henüz araç eklenmemiş.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Resim</th>
                                    <th>Başlık</th>
                                    <th>Fiyat</th>
                                    <th>Yıl/KM</th>
                                    <th>Yakıt/Vites</th>
                                    <th>Görüntülenme</th>
                                    <th>Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <tr>
                                        <td><img src="<?php echo cleanOutput($vehicle['image']); ?>" alt="" class="vehicle-img"></td>
                                        <td>
                                            <?php echo cleanOutput($vehicle['title']); ?>
                                            <?php if ($vehicle['is_featured']): ?>
                                                <span class="badge badge-warning">⭐ Öne Çıkan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo cleanOutput($vehicle['price']); ?></td>
                                        <td><?php echo cleanOutput($vehicle['year']); ?><br><?php echo cleanOutput($vehicle['km']); ?></td>
                                        <td><?php echo cleanOutput($vehicle['fuel']); ?><br><?php echo cleanOutput($vehicle['transmission']); ?></td>
                                        <td><?php echo number_format($vehicle['views']); ?></td>
                                        <td>
                                            <?php if ($vehicle['status'] === 'active'): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Satıldı</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <a href="?edit=<?php echo $vehicle['id']; ?>" class="btn btn-small">Düzenle</a>
                                                <a href="?delete=<?php echo $vehicle['id']; ?>" 
                                                   class="btn btn-small btn-danger"
                                                   onclick="return confirm('Bu aracı silmek istediğinizden emin misiniz?')">Sil</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Teklifler Tab -->
        <div id="offers" class="tab-content">
            <div class="card">
                <h2>Gelen Teklifler</h2>
                <?php if (empty($recentOffers)): ?>
                    <p style="text-align: center; color: #666; padding: 2rem;">Henüz teklif gelmemiş.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tarih</th>
                                    <th>Tür</th>
                                    <th>Müşteri</th>
                                    <th>Telefon</th>
                                    <th>Araç</th>
                                    <th>Mesaj</th>
                                    <th>Durum</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOffers as $offer): ?>
                                    <tr>
                                        <td><?php echo date('d.m.Y H:i', strtotime($offer['created_at'])); ?></td>
                                        <td>
                                            <?php 
                                            $types = ['buy' => '🛒 Alım', 'sell' => '💰 Satım', 'exchange' => '🔄 Takas'];
                                            echo $types[$offer['offer_type']] ?? $offer['offer_type'];
                                            ?>
                                        </td>
                                        <td><?php echo cleanOutput($offer['customer_name']); ?></td>
                                        <td><a href="tel:<?php echo cleanOutput($offer['customer_phone']); ?>"><?php echo cleanOutput($offer['customer_phone']); ?></a></td>
                                        <td><?php echo cleanOutput($offer['vehicle_title'] ?? 'Genel Teklif'); ?></td>
                                        <td><?php echo cleanOutput(substr($offer['message'], 0, 50)) . (strlen($offer['message']) > 50 ? '...' : ''); ?></td>
                                        <td>
                                            <?php
                                            $statusBadge = [
                                                'new' => '<span class="badge badge-danger">Yeni</span>',
                                                'contacted' => '<span class="badge badge-warning">İletişimde</span>',
                                                'completed' => '<span class="badge badge-success">Tamamlandı</span>',
                                                'cancelled' => '<span class="badge badge-info">İptal</span>'
                                            ];
                                            echo $statusBadge[$offer['status']] ?? $offer['status'];
                                            ?>
                                        </td>
                                        <td>
                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $offer['customer_phone']); ?>" target="_blank" class="btn btn-small btn-success">💬 WhatsApp</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 1rem; text-align: center;">
                        <a href="offers.php" class="btn">Tüm Teklifleri Görüntüle</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ayarlar Tab -->
        <div id="settings" class="tab-content">
            <div class="card">
                <h2>Site Ayarları</h2>
                <p style="color: #666;">Site ayarları için <code>config/database.php</code> dosyasını düzenleyin.</p>
                <div style="margin-top: 2rem;">
                    <a href="settings.php" class="btn">Detaylı Ayarlar</a>
                    <a href="security_logs.php" class="btn" style="background: #6c757d; color: white;">Güvenlik Logları</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openTab(tabName) {
            // Tüm tab içeriklerini gizle
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));
            
            // Tüm tab butonlarından active sınıfını kaldır
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            
            // Seçilen tab'ı göster
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>radius: 5px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: transform 0.3s;
            display: inline-block;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-small { padding: 0.4rem 0.8rem; font-size: 0.9rem; }
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border: 1px solid #f5c6cb;
        }
        .card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .card h2 {
            color: #16213e;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-