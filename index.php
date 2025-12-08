<?php
session_start();
require_once __DIR__ . "/classes/Database.php";
require_once __DIR__ . "/classes/User.php";

$db = new Database();
$conn = $db->conn;
$user = new User($conn);

// Eğer zaten giriş yaptıysa direkt rolüne göre yönlendir
if (isset($_SESSION["user"])) {
    $role = $_SESSION["user"]["role"];
    if ($role === "admin") {
        header("Location: users_admin.php");
    } elseif ($role === "manager") {
        header("Location: employee.php");
    } else {
        header("Location: appointment.php");
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $loginUser = $user->login($email, $password);

    if ($loginUser) {
    $_SESSION["user"] = $loginUser;
    header("Location: dashboard.php");
    exit;
}

}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>ManageX - Giriş</title>
<style>
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
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  width: 380px;
  text-align: center;
  animation: fadeIn 0.6s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
.logo {
  font-size: 26px;
  font-weight: 700;
  color: #00704a;
  margin-bottom: 20px;
}
input {
  width: 100%;
  padding: 12px;
  margin: 10px 0;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 15px;
  transition: 0.3s;
}
input:focus {
  border-color: #0ea5e9;
  outline: none;
  box-shadow: 0 0 4px rgba(14,165,233,0.3);
}
button {
  width: 100%;
  background: #00704a;
  color: #fff;
  font-size: 16px;
  font-weight: 600;
  padding: 12px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.3s;
  margin-top: 5px;
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
a:hover { text-decoration: underline; }
.footer {
  margin-top: 15px;
  font-size: 13px;
  color: #6b7280;
}
</style>
</head>
<body>

  <div class="login-box">
    <div class="logo">ManageX Yönetim Sistemi</div>
    <h2>Giriş Yap</h2>
    <form method="POST" action="">
      <input type="email" name="email" placeholder="E-posta" required>
      <input type="password" name="password" placeholder="Şifre" required>
      <button type="submit">🔒 Giriş Yap</button>
    </form>
    <a href="register.php">Hesabın yok mu? Kayıt ol</a>
    <div class="footer">© 2025 ManageX System</div>
  </div>

</body>
</html>