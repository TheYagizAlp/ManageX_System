<?php
session_start();
require_once __DIR__ . "/classes/Database.php";
require_once __DIR__ . "/classes/User.php";

$db = new Database();
$conn = $db->conn;
$userObj = new User($conn);

// Zaten giriş yaptıysa dashboard'a
if (isset($_SESSION["user"])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $loginUser = $userObj->login($email, $password);

    if ($loginUser) {
        $_SESSION["user"] = $loginUser;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "E-posta veya şifre hatalı!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>ManageX - Giriş</title>

<style>
* {
  box-sizing: border-box; /* 🔴 input taşma fix */
}

body {
  font-family: 'Segoe UI', sans-serif;
  background: linear-gradient(135deg, #0ea5e9, #00704a);
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
}

.login-box {
  background: #fff;
  padding: 40px 35px;
  border-radius: 18px;
  box-shadow: 0 12px 35px rgba(0,0,0,0.15);
  width: 380px;
  text-align: center;
}

.logo {
  font-size: 24px;
  font-weight: 700;
  color: #00704a;
  margin-bottom: 10px clarifying;
}

h2 {
  margin-bottom: 20px;
  color: #0f172a;
}

input {
  width: 100%;
  padding: 12px 14px;
  margin: 10px 0;
  border: 1px solid #ddd;
  border-radius: 10px;
  font-size: 15px;
}

input:focus {
  border-color: #0ea5e9;
  outline: none;
  box-shadow: 0 0 0 3px rgba(14,165,233,0.2);
}

button {
  width: 100%;
  background: #00704a;
  color: #fff;
  font-size: 16px;
  font-weight: 600;
  padding: 12px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  margin-top: 10px;
}

button:hover {
  background: #065f46;
}

a {
  display: block;
  margin-top: 14px;
  color: #0ea5e9;
  text-decoration: none;
  font-size: 14px;
}

a:hover {
  text-decoration: underline;
}

.error {
  background: #fee2e2;
  color: #991b1b;
  padding: 10px;
  border-radius: 8px;
  margin-bottom: 12px;
  font-size: 14px;
}

.footer {
  margin-top: 18px;
  font-size: 13px;
  color: #6b7280;
}
</style>
</head>

<body>

<div class="login-box">
  <div class="logo">ManageX Yönetim Sistemi</div>
  <h2>Giriş Yap</h2>

  <?php if (!empty($error)): ?>
    <div class="error"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST">
    <input type="email" name="email" placeholder="E-posta" required>
    <input type="password" name="password" placeholder="Şifre" required>
    <button type="submit">🔒 Giriş Yap</button>
  </form>

  <a href="register.php">Hesabın yok mu? Kayıt ol</a>
  <div class="footer">© 2025 ManageX System</div>
</div>

</body>
</html>