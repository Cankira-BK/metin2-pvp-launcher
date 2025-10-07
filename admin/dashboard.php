<?php require 'config.php'; if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; } ?>

<?php 
// Kaç kayıt gösterileceğini belirle (varsayılan 10)
$limit = isset($_GET['limit']) && in_array($_GET['limit'], [10,20,30,40,50]) 
         ? (int)$_GET['limit'] 
         : 10;
?>
<!DOCTYPE html>
<html>
<head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <title>Admin Panel</title>  
</head>
<body>

<?php include __DIR__ . "/sidebar.php"; ?>

<div class="content">
  <h1>Hoşgeldiniz!</h1>
  <p>Sol menüyü kullanarak işlemler yapabilirsiniz.</p>

  <div class="card mt-4 shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <span>Kullanıcı Giriş / Çıkış Kayıtları</span>
      <form method="get" class="mb-0">
        <select name="limit" class="form-select form-select-sm" onchange="this.form.submit()">
          <?php 
          foreach ([10,20,30,40,50] as $opt) {
            $sel = ($opt == $limit) ? "selected" : "";
            echo "<option value='$opt' $sel>$opt Kayıt göster</option>";
          }
          ?>
        </select>
      </form>
    </div>
    <div class="card-body p-0">
      <table class="table table-striped table-bordered mb-0">
        <thead class="table-dark">
          <tr>
            <th>Kullanıcı</th>
            <th>İşlem</th>
            <th>IP</th>
            <th>Tarayıcı</th>
            <th>Tarih</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $stmt = $pdo->prepare("SELECT * FROM giris_kayit ORDER BY id DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>
                    <td>{$row['username']}</td>
                    <td>" . ($row['action'] == 'login' ? '🔓 Giriş' : '🔒 Çıkış') . "</td>
                    <td>{$row['ip_address']}</td>
                    <td>{$row['user_agent']}</td>
                    <td>{$row['created_at']}</td>
                  </tr>";
        }
        ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>
