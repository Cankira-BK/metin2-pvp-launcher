<?php
session_start();
require_once 'config.php';

// Giriş kontrolü
checkLogin();

$message = '';
$error = '';

// Araç ekleme
if (isset($_POST['add_vehicle'])) {
    $errors = validateVehicleData($_POST);
    
    if (empty($errors)) {
        $vehicles = getVehicles();
        $newId = empty($vehicles) ? 1 : max(array_column($vehicles, 'id')) + 1;
        
        $newVehicle = [
            'id' => $newId,
            'title' => trim($_POST['title']),
            'price' => trim($_POST['price']),
            'year' => trim($_POST['year']),
            'km' => trim($_POST['km']),
            'fuel' => $_POST['fuel'],
            'image' => trim($_POST['image']),
            'link' => trim($_POST['link']),
            'date' => date('Y-m-d')
        ];
        
        $vehicles[] = $newVehicle;
        saveVehicles($vehicles);
        $message = 'Araç başarıyla eklendi!';
    } else {
        $error = implode('<br>', $errors);
    }
}

// Araç silme
if (isset($_GET['delete'])) {
    $vehicles = getVehicles();
    $deleteId = (int)$_GET['delete'];
    $vehicles = array_filter($vehicles, function($v) use ($deleteId) {
        return $v['id'] !== $deleteId;
    });
    saveVehicles(array_values($vehicles));
    $message = 'Araç silindi!';
    header('Location: index.php');
    exit;
}

// Araç düzenleme
if (isset($_POST['edit_vehicle'])) {
    $errors = validateVehicleData($_POST);
    
    if (empty($errors)) {
        $vehicles = getVehicles();
        $editId = (int)$_POST['id'];
        
        foreach ($vehicles as &$vehicle) {
            if ($vehicle['id'] === $editId) {
                $vehicle['title'] = trim($_POST['title']);
                $vehicle['price'] = trim($_POST['price']);
                $vehicle['year'] = trim($_POST['year']);
                $vehicle['km'] = trim($_POST['km']);
                $vehicle['fuel'] = $_POST['fuel'];
                $vehicle['image'] = trim($_POST['image']);
                $vehicle['link'] = trim($_POST['link']);
                break;
            }
        }
        
        saveVehicles($vehicles);
        $message = 'Araç güncellendi!';
        header('Location: index.php');
        exit;
    } else {
        $error = implode('<br>', $errors);
    }
}

// Çıkış
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$vehicles = getVehicles();
$editVehicle = null;

if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($vehicles as $v) {
        if ($v['id'] === $editId) {
            $editVehicle = $v;
            break;
        }
    }
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
        }
        .header h1 { font-size: 1.5rem; }
        .header-right { display: flex; gap: 1rem; align-items: center; }
        .btn {
            padding: 0.6rem 1.2rem;
            background: #ffd700;
            color: #1a1a2e;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: transform 0.3s;
            display: inline-block;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-small {
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
        }
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
            border-bottom: 3px solid #ffd700;
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
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ffd700;
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
        }
        .stats {
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
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
            }
            .header-right {
                flex-wrap: wrap;
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
            <span>Hoş geldin, <?php echo sanitize($_SESSION['admin_username']); ?></span>
            <a href="../index.php" class="btn btn-small" target="_blank">Siteyi Görüntüle</a>
            <a href="?logout=1" class="btn btn-small btn-danger">Çıkış</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?php echo sanitize($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-card">
                <h3>Toplam Araç</h3>
                <div class="number"><?php echo count($vehicles); ?></div>
            </div>
            <div class="stat-card">
                <h3>Bu Ay Eklenen</h3>
                <div class="number">
                    <?php 
                    $thisMonth = date('Y-m');
                    $monthCount = count(array_filter($vehicles, function($v) use ($thisMonth) {
                        return strpos($v['date'], $thisMonth) === 0;
                    }));
                    echo $monthCount;
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Son Güncelleme</h3>
                <div class="number" style="font-size: 1.2rem;">
                    <?php 
                    if (!empty($vehicles)) {
                        $dates = array_column($vehicles, 'date');
                        rsort($dates);
                        echo date('d.m.Y', strtotime($dates[0]));
                    } else {
                        echo '-';
                    }
                    ?>
                </div>
            </div>
        </div>

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
                               value="<?php echo $editVehicle ? sanitize($editVehicle['title']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Fiyat *</label>
                        <input type="text" name="price" required placeholder="Örn: 1.142.000 TL"
                               value="<?php echo $editVehicle ? sanitize($editVehicle['price']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Yıl *</label>
                        <input type="text" name="year" required placeholder="Örn: 2024"
                               value="<?php echo $editVehicle ? sanitize($editVehicle['year']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Kilometre *</label>
                        <input type="text" name="km" required placeholder="Örn: 18.500 km"
                               value="<?php echo $editVehicle ? sanitize($editVehicle['km']) : ''; ?>">
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
                        <label>Resim URL *</label>
                        <input type="url" name="image" required placeholder="https://..."
                               value="<?php echo $editVehicle ? sanitize($editVehicle['image']) : ''; ?>">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Sahibinden Link *</label>
                        <input type="url" name="link" required placeholder="https://www.sahibinden.com/..."
                               value="<?php echo $editVehicle ? sanitize($editVehicle['link']) : ''; ?>">
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
                                <th>Yıl</th>
                                <th>KM</th>
                                <th>Yakıt</th>
                                <th>Tarih</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // En yeni önce göster
                            usort($vehicles, function($a, $b) {
                                return strcmp($b['date'], $a['date']);
                            });
                            
                            foreach ($vehicles as $vehicle): 
                            ?>
                                <tr>
                                    <td><img src="<?php echo sanitize($vehicle['image']); ?>" alt="" class="vehicle-img"></td>
                                    <td><?php echo sanitize($vehicle['title']); ?></td>
                                    <td><?php echo sanitize($vehicle['price']); ?></td>
                                    <td><?php echo sanitize($vehicle['year']); ?></td>
                                    <td><?php echo sanitize($vehicle['km']); ?></td>
                                    <td><?php echo sanitize($vehicle['fuel']); ?></td>
                                    <td><?php echo date('d.m.Y', strtotime($vehicle['date'])); ?></td>
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
</body>
</html>