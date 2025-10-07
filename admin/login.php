<?php
require 'config.php';
if (isset($_SESSION['user_id'])) {
  header('Location: dashboard.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->execute([$username]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    // giriş logunu ekle
    $ip = $_SERVER['REMOTE_ADDR'];
    $ua = $_SERVER['HTTP_USER_AGENT'];
    $logStmt = $pdo->prepare("INSERT INTO giris_kayit (user_id, username, action, ip_address, user_agent) VALUES (?, ?, 'login', ?, ?)");
    $logStmt->execute([$user['id'], $user['username'], $ip, $ua]);

    header('Location: dashboard.php');
    exit;
  } else {
    $error = "Geçersiz kullanıcı adı veya şifre";
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Giriş Yap</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
<div class="card p-4 shadow" style="min-width: 350px;">
  <h3 class="mb-3 text-center">🔐 Admin Giriş</h3>
  <form method="POST">
    <input type="text" name="username" class="form-control mb-3" placeholder="Kullanıcı adı" required>
    <input type="password" name="password" class="form-control mb-3" placeholder="Şifre" required>
    <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
    <?php if (isset($error)) echo "<div class='mt-3 alert alert-danger'>$error</div>"; ?>
  </form>
</div>
</body>
</html>
