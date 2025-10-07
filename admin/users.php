<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

// Aktif kullanıcının yetkilerini çek
$stmt = $pdo->prepare("SELECT dosya_yonetim, kullanici_yonetim FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$aktif = $stmt->fetch();
if (!$aktif) {
    die("❌ Kullanıcı bulunamadı.");
}

$message = "";

// Kullanıcı ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ekle'])) {
    if ($aktif['kullanici_yonetim'] != 1) {
        die("❌ Kullanıcı ekleme yetkiniz yok.");
    }

    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $dosya_yonetim    = isset($_POST['dosya_yonetim']) ? 1 : 0;
    $kullanici_yonetim = isset($_POST['kullanici_yonetim']) ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO users 
      (username, password, dosya_yonetim, kullanici_yonetim) 
      VALUES (?,?,?,?)");
    $stmt->execute([$username, $password, $dosya_yonetim, $kullanici_yonetim]);

    $message = "✅ Yeni kullanıcı eklendi.";
}

// Kullanıcı silme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['userid'])) {
    if ($aktif['kullanici_yonetim'] != 1) {
        die("❌ Kullanıcı silme yetkiniz yok.");
    }

    $id = (int) $_POST['userid'];
    if ($id !== $_SESSION['user_id']) { // Kendini silemesin
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $message = "❌ Kullanıcı silindi.";
    }
}

// Kullanıcıları listele
$users = $pdo->query("SELECT * FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <title>Kullanıcı Yönetimi</title>
</head>
<body class="bg-light">
<?php include __DIR__ . "/sidebar.php"; ?>
<div class="content">
<div class="container mt-4">

  <h2>👤 Kullanıcı Yönetimi</h2>
  <?php if ($message): ?><div class="alert alert-info"><?= $message ?></div><?php endif; ?>

  <!-- Kullanıcı ekleme sadece yetkiliye -->
  <?php if ($aktif['kullanici_yonetim'] == 1): ?>
  <form method="POST" class="mb-4 card card-body">
    <input type="hidden" name="ekle" value="1">
    <input type="text" name="username" class="form-control mb-2" placeholder="Yeni kullanıcı adı" required>
    <input type="password" name="password" class="form-control mb-2" placeholder="Şifre" required>

    <label class="fw-bold mt-2">Yetkiler</label>
    <div class="form-check"><input type="checkbox" name="dosya_yonetim" class="form-check-input"> Dosya Yükleme Yönetimi</div>
    <div class="form-check"><input type="checkbox" name="kullanici_yonetim" class="form-check-input"> Kullanıcı Ekle/Sil</div>

    <button class="btn btn-primary mt-3">Kullanıcı Ekle</button>
  </form>
  <?php endif; ?>

  <!-- Kullanıcı listesi -->
  <h4>Mevcut Kullanıcılar</h4>
  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead class="table-dark">
        <tr>
          <th>Kullanıcı Adı</th>
          <th>Dosya Yönetimi</th>
          <th>Kullanıcı Ekle/Sil</th>
          <?php if ($aktif['kullanici_yonetim'] == 1): ?><th>İşlem</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= $u['dosya_yonetim'] ? "✅" : "❌" ?></td>
          <td><?= $u['kullanici_yonetim'] ? "✅" : "❌" ?></td>
          <?php if ($aktif['kullanici_yonetim'] == 1): ?>
          <td>
            <form method="POST" class="d-inline sil-form">
              <input type="hidden" name="userid" value="<?= $u['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger">Sil</button>
            </form>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
  document.querySelectorAll(".sil-form").forEach(form => {
    form.addEventListener("submit", function(event) {
      event.preventDefault();
      Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu kullanıcı kalıcı olarak silinecek!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'Hayır, iptal et'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });
});
</script>

</body>
</html>
