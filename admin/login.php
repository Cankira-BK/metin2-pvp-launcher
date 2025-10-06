<?php
session_start();
require_once 'config.php';

// Eğer zaten giriş yapılmışsa admin panele yönlendir
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';
$timeout = isset($_GET['timeout']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Rate limiting - basit brute force koruması
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt'] = time();
    }
    
    // 5 başarısız denemeden sonra 5 dakika beklet
    if ($_SESSION['login_attempts'] >= 5) {
        if (time() - $_SESSION['last_attempt'] < 300) {
            $remaining = 300 - (time() - $_SESSION['last_attempt']);
            $error = "Çok fazla hatalı deneme. " . ceil($remaining / 60) . " dakika sonra tekrar deneyin.";
        } else {
            $_SESSION['login_attempts'] = 0;
        }
    }
    
    if (empty($error)) {
        if ($username === ADMIN_USERNAME && verifyPassword($password)) {
            // Başarılı giriş
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            $_SESSION['last_activity'] = time();
            $_SESSION['login_attempts'] = 0;
            
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt'] = time();
            $error = 'Kullanıcı adı veya şifre hatalı!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi - Güçlü Otomotiv</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
        }
        .logo {
            text-align: center;
            font-size: 2rem;
            color: #16213e;
            margin-bottom: 2rem;
            font-weight: bold;
        }
        h2 {
            color: #16213e;
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            color: #333;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #ffd700;
        }
        .btn {
            width: 100%;
            padding: 1rem;
            background: #ffd700;
            color: #1a1a2e;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,215,0,0.4);
        }
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .back-link {
            text-align: center;
            margin-top: 1rem;
        }
        .back-link a {
            color: #16213e;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">🚗 GÜÇLÜ OTOMOTİV</div>
        <h2>Admin Girişi</h2>
        
        <?php if ($timeout): ?>
            <div class="warning">Oturumunuz zaman aşımına uğradı. Lütfen tekrar giriş yapın.</div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo sanitize($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Kullanıcı Adı</label>
                <input type="text" name="username" required autofocus autocomplete="username">
            </div>
            <div class="form-group">
                <label>Şifre</label>
                <input type="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn" <?php echo ($_SESSION['login_attempts'] ?? 0) >= 5 && time() - ($_SESSION['last_attempt'] ?? 0) < 300 ? 'disabled' : ''; ?>>
                Giriş Yap
            </button>
        </form>
        
        <div class="back-link">
            <a href="../index.php">← Ana Sayfaya Dön</a>
        </div>
    </div>
</body>
</html>