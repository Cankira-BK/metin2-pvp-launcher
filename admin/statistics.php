<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();

// Tarih aralığı (son 30 gün varsayılan)
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Genel istatistikler
$stats = [
    'total_vehicles' => $db->fetchOne("SELECT COUNT(*) as count FROM vehicles WHERE status='active'")['count'] ?? 0,
    'total_offers' => $db->fetchOne("SELECT COUNT(*) as count FROM offers")['count'] ?? 0,
    'total_views' => $db->fetchOne("SELECT SUM(views) as total FROM vehicles")['total'] ?? 0,
    'pending_vehicles' => $db->fetchOne("SELECT COUNT(*) as count FROM vehicles WHERE status='pending'")['count'] ?? 0,
    'new_offers_today' => $db->fetchOne("SELECT COUNT(*) as count FROM offers WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
    'avg_vehicle_price' => $db->fetchOne("SELECT AVG(CAST(REPLACE(REPLACE(price, ' TL', ''), '.', '') AS UNSIGNED)) as avg FROM vehicles WHERE status='active'")['avg'] ?? 0,
];

// En çok görüntülenen araçlar
$topVehicles = $db->fetchAll(
    "SELECT title, views, price FROM vehicles WHERE status='active' ORDER BY views DESC LIMIT 10"
);

// Günlük istatistikler (grafik için)
$dailyStats = $db->fetchAll(
    "SELECT stat_date, total_views, total_offers, total_vehicles_added 
     FROM daily_stats 
     WHERE stat_date BETWEEN ? AND ? 
     ORDER BY stat_date ASC",
    [$startDate, $endDate]
);

// Yakıt tipi dağılımı
$fuelStats = $db->fetchAll(
    "SELECT fuel, COUNT(*) as count FROM vehicles WHERE status='active' GROUP BY fuel ORDER BY count DESC"
);

// Vites dağılımı
$transmissionStats = $db->fetchAll(
    "SELECT transmission, COUNT(*) as count FROM vehicles WHERE status='active' GROUP BY transmission"
);

// Teklif türü dağılımı
$offerTypeStats = $db->fetchAll(
    "SELECT offer_type, COUNT(*) as count FROM offers GROUP BY offer_type"
);

// Aylık satış trendi
$monthlyTrend = $db->fetchAll(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
     FROM vehicles 
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
     GROUP BY month 
     ORDER BY month ASC"
);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İstatistikler - Admin Panel</title>
    <link rel="stylesheet" href="admin-style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .stats-big-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-big-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .stat-big-card h3 { font-size: 0.9rem; opacity: 0.9; margin-bottom: 0.5rem; }
        .stat-big-card .number { font-size: 2.5rem; font-weight: bold; }
        .stat-big-card.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .stat-big-card.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-big-card.blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-big-card.purple { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .chart-container { background: white; padding: 2rem; border-radius: 10px; margin-bottom: 2rem; }
        .chart-wrapper { position: relative; height: 300px; }
        .filter-form { background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; display: flex; gap: 1rem; align-items: end; flex-wrap: wrap; }
        .filter-form .form-group { margin: 0; }
        .top-list { list-style: none; padding: 0; }
        .top-list li { padding: 1rem; background: #f8f9fa; margin-bottom: 0.5rem; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        .top-list li:nth-child(1) { background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); font-weight: bold; }
        .top-list li:nth-child(2) { background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%); }
        .top-list li:nth-child(3) { background: linear-gradient(135deg, #cd7f32 0%, #e8a87c 100%); }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 İstatistikler ve Raporlar</h1>
        <div class="header-right">
            <a href="index.php" class="btn btn-small">← Admin Panel</a>
        </div>
    </div>

    <div class="container">
        <!-- Tarih Filtresi -->
        <form method="GET" class="filter-form">
            <div class="form-group">
                <label>Başlangıç Tarihi</label>
                <input type="date" name="start_date" value="<?php echo $startDate; ?>">
            </div>
            <div class="form-group">
                <label>Bitiş Tarihi</label>
                <input type="date" name="end_date" value="<?php echo $endDate; ?>">
            </div>
            <button type="submit" class="btn">🔍 Filtrele</button>
            <a href="statistics.php" class="btn" style="background: #6c757d; color: white;">🔄 Sıfırla</a>
        </form>

        <!-- Büyük Stat Kartları -->
        <div class="stats-big-grid">
            <div class="stat-big-card green">
                <h3>Toplam Araç</h3>
                <div class="number"><?php echo number_format($stats['total_vehicles']); ?></div>
            </div>
            <div class="stat-big-card orange">
                <h3>Toplam Teklif</h3>
                <div class="number"><?php echo number_format($stats['total_offers']); ?></div>
            </div>
            <div class="stat-big-card blue">
                <h3>Toplam Görüntülenme</h3>
                <div class="number"><?php echo number_format($stats['total_views']); ?></div>
            </div>
            <div class="stat-big-card purple">
                <h3>Bugün Gelen Teklif</h3>
                <div class="number"><?php echo $stats['new_offers_today']; ?></div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 2rem;">
            <!-- Günlük Görüntülenme Grafiği -->
            <div class="chart-container">
                <h2>📈 Günlük Görüntülenme Trendi</h2>
                <div class="chart-wrapper">
                    <canvas id="viewsChart"></canvas>
                </div>
            </div>

            <!-- Günlük Teklif Grafiği -->
            <div class="chart-container">
                <h2>📊 Günlük Teklif Sayısı</h2>
                <div class="chart-wrapper">
                    <canvas id="offersChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Yakıt ve Vites Dağılımı -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">
            <div class="chart-container">
                <h2>⛽ Yakıt Tipi Dağılımı</h2>
                <div class="chart-wrapper">
                    <canvas id="fuelChart"></canvas>
                </div>
            </div>

            <div class="chart-container">
                <h2>⚙️ Vites Tipi Dağılımı</h2>
                <div class="chart-wrapper">
                    <canvas id="transmissionChart"></canvas>
                </div>
            </div>

            <div class="chart-container">
                <h2>💬 Teklif Türü Dağılımı</h2>
                <div class="chart-wrapper">
                    <canvas id="offerTypeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- En Çok Görüntülenen Araçlar -->
        <div class="card">
            <h2>🏆 En Çok Görüntülenen Araçlar</h2>
            <ol class="top-list">
                <?php foreach ($topVehicles as $index => $vehicle): ?>
                    <li>
                        <span>
                            <strong><?php echo $index + 1; ?>.</strong>
                            <?php echo htmlspecialchars($vehicle['title']); ?>
                        </span>
                        <span>
                            <strong><?php echo number_format($vehicle['views']); ?></strong> görüntülenme
                        </span>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>

        <!-- Aylık Trend -->
        <div class="chart-container">
            <h2>📅 12 Aylık Araç Ekleme Trendi</h2>
            <div class="chart-wrapper">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- Özet Bilgiler -->
        <div class="card">
            <h2>📋 Özet Bilgiler</h2>
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 1rem; font-weight: bold;">Ortalama Araç Fiyatı:</td>
                    <td style="padding: 1rem; text-align: right; color: #ffd700; font-weight: bold; font-size: 1.2rem;">
                        <?php echo number_format($stats['avg_vehicle_price']); ?> TL
                    </td>
                </tr>
                <tr style="background: #f8f9fa;">
                    <td style="padding: 1rem; font-weight: bold;">Onay Bekleyen Araç:</td>
                    <td style="padding: 1rem; text-align: right; color: #dc3545; font-weight: bold;">
                        <?php echo $stats['pending_vehicles']; ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 1rem; font-weight: bold;">Toplam Kayıt:</td>
                    <td style="padding: 1rem; text-align: right;">
                        <?php echo number_format($stats['total_vehicles'] + $stats['total_offers']); ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <script>
        // Günlük görüntülenme grafiği
        const viewsCtx = document.getElementById('viewsChart').getContext('2d');
        new Chart(viewsCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($dailyStats, 'stat_date')); ?>,
                datasets: [{
                    label: 'Görüntülenme',
                    data: <?php echo json_encode(array_column($dailyStats, 'total_views')); ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

        // Günlük teklif grafiği
        const offersCtx = document.getElementById('offersChart').getContext('2d');
        new Chart(offersCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($dailyStats, 'stat_date')); ?>,
                datasets: [{
                    label: 'Teklifler',
                    data: <?php echo json_encode(array_column($dailyStats, 'total_offers')); ?>,
                    backgroundColor: '#f5576c'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

        // Yakıt tipi dağılımı
        const fuelCtx = document.getElementById('fuelChart').getContext('2d');
        new Chart(fuelCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($fuelStats, 'fuel')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($fuelStats, 'count')); ?>,
                    backgroundColor: ['#667eea', '#f5576c', '#38ef7d', '#4facfe', '#ffd700']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Vites dağılımı
        const transmissionCtx = document.getElementById('transmissionChart').getContext('2d');
        new Chart(transmissionCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($transmissionStats, 'transmission')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($transmissionStats, 'count')); ?>,
                    backgroundColor: ['#11998e', '#fa709a', '#4facfe']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Teklif türü dağılımı
        const offerTypeCtx = document.getElementById('offerTypeChart').getContext('2d');
        new Chart(offerTypeCtx, {
            type: 'polarArea',
            data: {
                labels: ['Alım', 'Satım', 'Takas'],
                datasets: [{
                    data: [
                        <?php 
                        $offerTypes = array_column($offerTypeStats, 'count', 'offer_type');
                        echo ($offerTypes['buy'] ?? 0) . ',';
                        echo ($offerTypes['sell'] ?? 0) . ',';
                        echo ($offerTypes['exchange'] ?? 0);
                        ?>
                    ],
                    backgroundColor: ['#667eea', '#f5576c', '#38ef7d']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Aylık trend
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthlyTrend, 'month')); ?>,
                datasets: [{
                    label: 'Eklenen Araç',
                    data: <?php echo json_encode(array_column($monthlyTrend, 'count')); ?>,
                    borderColor: '#ffd700',
                    backgroundColor: 'rgba(255, 215, 0, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>
</html>