<?php
session_start();
require_once 'config/database.php';

$db = Database::getInstance();

// Filtreler
$conditions = ["status = 'active'"];
$params = [];

// Arama metni
if (!empty($_GET['search'])) {
    $conditions[] = "(title LIKE ? OR description LIKE ?)";
    $searchTerm = '%' . $_GET['search'] . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Fiyat aralığı
if (!empty($_GET['min_price'])) {
    $conditions[] = "CAST(REPLACE(REPLACE(price, ' TL', ''), '.', '') AS UNSIGNED) >= ?";
    $params[] = (int)str_replace(['.', ','], '', $_GET['min_price']);
}
if (!empty($_GET['max_price'])) {
    $conditions[] = "CAST(REPLACE(REPLACE(price, ' TL', ''), '.', '') AS UNSIGNED) <= ?";
    $params[] = (int)str_replace(['.', ','], '', $_GET['max_price']);
}

// Yıl aralığı
if (!empty($_GET['min_year'])) {
    $conditions[] = "year >= ?";
    $params[] = $_GET['min_year'];
}
if (!empty($_GET['max_year'])) {
    $conditions[] = "year <= ?";
    $params[] = $_GET['max_year'];
}

// Yakıt tipi
if (!empty($_GET['fuel']) && $_GET['fuel'] != 'all') {
    $conditions[] = "fuel = ?";
    $params[] = $_GET['fuel'];
}

// Vites tipi
if (!empty($_GET['transmission']) && $_GET['transmission'] != 'all') {
    $conditions[] = "transmission = ?";
    $params[] = $_GET['transmission'];
}

// Renk
if (!empty($_GET['color'])) {
    $conditions[] = "color LIKE ?";
    $params[] = '%' . $_GET['color'] . '%';
}

// Sıralama
$orderBy = "created_at DESC";
if (!empty($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'price_asc':
            $orderBy = "CAST(REPLACE(REPLACE(price, ' TL', ''), '.', '') AS UNSIGNED) ASC";
            break;
        case 'price_desc':
            $orderBy = "CAST(REPLACE(REPLACE(price, ' TL', ''), '.', '') AS UNSIGNED) DESC";
            break;
        case 'year_desc':
            $orderBy = "year DESC";
            break;
        case 'year_asc':
            $orderBy = "year ASC";
            break;
        case 'km_asc':
            $orderBy = "CAST(REPLACE(REPLACE(km, ' km', ''), '.', '') AS UNSIGNED) ASC";
            break;
        case 'views_desc':
            $orderBy = "views DESC";
            break;
    }
}

$sql = "SELECT * FROM vehicles WHERE " . implode(" AND ", $conditions) . " ORDER BY " . $orderBy;
$vehicles = $db->fetchAll($sql, $params);

// Filtre seçenekleri için
$fuelOptions = $db->fetchAll("SELECT DISTINCT fuel FROM vehicles WHERE status='active'");
$yearMin = $db->fetchOne("SELECT MIN(year) as min FROM vehicles WHERE status='active'")['min'] ?? date('Y') - 20;
$yearMax = $db->fetchOne("SELECT MAX(year) as max FROM vehicles WHERE status='active'")['max'] ?? date('Y');

// Site ayarları
$settings = [];
$settingsData = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
foreach ($settingsData as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Araç Ara - <?php echo htmlspecialchars($settings['site_title'] ?? 'Güçlü Otomotiv'); ?></title>
    <link rel="stylesheet" href="assets/styles.css">
    <style>
        .search-container { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin: 2rem auto; max-width: 1400px; }
        .search-filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .filter-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333; }
        .filter-group input, .filter-group select { width: 100%; padding: 0.8rem; border: 2px solid #e0e0e0; border-radius: 8px; }
        .filter-actions { display: flex; gap: 1rem; margin-top: 1rem; }
        .search-results { margin: 2rem 0; }
        .results-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .sort-select { padding: 0.8rem; border: 2px solid #e0e0e0; border-radius: 8px; }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">🚗 <?php echo htmlspecialchars($settings['site_title'] ?? 'GÜÇLÜ OTOMOTİV'); ?></div>
            <ul class="nav-links">
                <li><a href="index.php">Ana Sayfa</a></li>
                <li><a href="search.php">Araç Ara</a></li>
                <li><a href="index.php#iletisim">İletişim</a></li>
            </ul>
        </nav>
    </header>

    <div class="search-container">
        <h1 style="color: #16213e; margin-bottom: 2rem;">🔍 Araç Arama</h1>
        
        <form method="GET">
            <div class="search-filters">
                <div class="filter-group">
                    <label>Arama</label>
                    <input type="text" name="search" placeholder="Araç adı, model..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                
                <div class="filter-group">
                    <label>Min Fiyat</label>
                    <input type="number" name="min_price" placeholder="0" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>">
                </div>
                
                <div class="filter-group">
                    <label>Max Fiyat</label>
                    <input type="number" name="max_price" placeholder="10000000" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>">
                </div>
                
                <div class="filter-group">
                    <label>Min Yıl</label>
                    <input type="number" name="min_year" min="<?php echo $yearMin; ?>" max="<?php echo $yearMax; ?>" value="<?php echo htmlspecialchars($_GET['min_year'] ?? ''); ?>">
                </div>
                
                <div class="filter-group">
                    <label>Max Yıl</label>
                    <input type="number" name="max_year" min="<?php echo $yearMin; ?>" max="<?php echo $yearMax; ?>" value="<?php echo htmlspecialchars($_GET['max_year'] ?? ''); ?>">
                </div>
                
                <div class="filter-group">
                    <label>Yakıt Tipi</label>
                    <select name="fuel">
km_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'km_asc') ? 'selected' : ''; ?>>Kilometre (Az-Çok)</option>
                        <option value="views_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'views_desc') ? 'selected' : ''; ?>>En Çok Görüntülenen</option>
                    </select>
                </form>
            </div>
            
            <?php if (empty($vehicles)): ?>
                <div style="text-align: center; padding: 4rem; background: white; border-radius: 10px;">
                    <p style="font-size: 3rem; margin-bottom: 1rem;">🔍</p>
                    <h3 style="color: #666;">Aramanızla eşleşen araç bulunamadı</h3>
                    <p style="color: #999; margin-top: 1rem;">Lütfen farklı filtreler deneyin</p>
                </div>
            <?php else: ?>
                <div class="vehicles-grid">
                    <?php foreach ($vehicles as $vehicle): ?>
                        <div class="vehicle-card">
                            <div class="vehicle-image">
                                <img src="<?php echo htmlspecialchars($vehicle['image']); ?>" alt="<?php echo htmlspecialchars($vehicle['title']); ?>">
                                <?php if ($vehicle['is_featured']): ?>
                                    <span style="position: absolute; top: 10px; right: 10px; background: #ffd700; color: #000; padding: 5px 10px; border-radius: 5px; font-weight: bold;">⭐</span>
                                <?php endif; ?>
                            </div>
                            <div class="vehicle-info">
                                <h3><?php echo htmlspecialchars($vehicle['title']); ?></h3>
                                <div class="vehicle-price"><?php echo htmlspecialchars($vehicle['price']); ?></div>
                                <div class="vehicle-details">
                                    <span>📅 <?php echo htmlspecialchars($vehicle['year']); ?></span>
                                    <span>🛣️ <?php echo htmlspecialchars($vehicle['km']); ?></span>
                                    <span>⛽ <?php echo htmlspecialchars($vehicle['fuel']); ?></span>
                                </div>
                                <div class="vehicle-details" style="margin-top: 0.5rem;">
                                    <span>⚙️ <?php echo htmlspecialchars($vehicle['transmission']); ?></span>
                                    <?php if (!empty($vehicle['color'])): ?>
                                        <span>🎨 <?php echo htmlspecialchars($vehicle['color']); ?></span>
                                    <?php endif; ?>
                                    <span>👁️ <?php echo number_format($vehicle['views']); ?></span>
                                </div>
                                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                    <?php if (!empty($vehicle['sahibinden_link'])): ?>
                                        <a href="<?php echo htmlspecialchars($vehicle['sahibinden_link']); ?>" target="_blank" class="btn" style="flex: 1; text-align: center; font-size: 0.9rem;">Detaylar</a>
                                    <?php endif; ?>
                                    <a href="https://wa.me/<?php echo htmlspecialchars($settings['whatsapp_number'] ?? '905321234567'); ?>?text=Merhaba, <?php echo urlencode($vehicle['title']); ?> hakkında bilgi almak istiyorum." target="_blank" class="btn" style="flex: 1; text-align: center; background: #25D366; font-size: 0.9rem;">💬 Teklif</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Güçlü Otomotiv. Tüm hakları saklıdır.</p>
        </div>
    </footer>
</body>
</html>all">Tümü</option>
                        <?php foreach ($fuelOptions as $opt): ?>
                            <option value="<?php echo htmlspecialchars($opt['fuel']); ?>" <?php echo (isset($_GET['fuel']) && $_GET['fuel'] == $opt['fuel']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($opt['fuel']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Vites</label>
                    <select name="transmission">
                        <option value="all">Tümü</option>
                        <option value="Manuel" <?php echo (isset($_GET['transmission']) && $_GET['transmission'] == 'Manuel') ? 'selected' : ''; ?>>Manuel</option>
                        <option value="Otomatik" <?php echo (isset($_GET['transmission']) && $_GET['transmission'] == 'Otomatik') ? 'selected' : ''; ?>>Otomatik</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Renk</label>
                    <input type="text" name="color" placeholder="Beyaz, Siyah..." value="<?php echo htmlspecialchars($_GET['color'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn">🔍 Ara</button>
                <a href="search.php" class="btn" style="background: #6c757d; color: white;">🔄 Filtreleri Temizle</a>
            </div>
        </form>
    </div>

    <div class="container">
        <div class="search-results">
            <div class="results-header">
                <h2><?php echo count($vehicles); ?> Araç Bulundu</h2>
                <form method="GET" style="display: flex; align-items: center; gap: 1rem;">
                    <?php foreach ($_GET as $key => $value): ?>
                        <?php if ($key != 'sort'): ?>
                            <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <label>Sırala:</label>
                    <select name="sort" class="sort-select" onchange="this.form.submit()">
                        <option value="">En Yeni</option>
                        <option value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : ''; ?>>Fiyat (Düşük-Yüksek)</option>
                        <option value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : ''; ?>>Fiyat (Yüksek-Düşük)</option>
                        <option value="year_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'year_desc') ? 'selected' : ''; ?>>Yıl (Yeni-Eski)</option>
                        <option value="year_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'year_asc') ? 'selected' : ''; ?>>Yıl (Eski-Yeni)</option>
                        <option value="