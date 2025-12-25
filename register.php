<?php
session_start();
require_once __DIR__ . "/classes/Database.php";
require_once __DIR__ . "/classes/User.php";

$db = new Database();
$conn = $db->conn;
$user = new User($conn);

// Zaten giriş yaptıysa dashboard
if (isset($_SESSION["user"])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Kayıt olan herkes Misafir (DB'de user)
    $role = "user";

    $result = $user->register($name, $email, $password, $role);

    // Başarılıysa giriş sayfasına yönlendirelim
    if ($result === "Kayıt başarılı!") {
        echo "<script>alert('Kayıt başarılı! Artık giriş yapabilirsin.'); window.location='index.php';</script>";
        exit;
    } else {
        echo "<script>alert('".$result."');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Kayıt Ol - ManageX</title>
<style>
  body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(120deg, #0ea5e9, #00704a);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
  }
  .container {
    background: #fff;
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    width: 360px;
    text-align: center;
  }
  input {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    box-sizing: border-box;
  }
  button {
    width: 100%;
    background: #0ea5e9;
    color: #fff;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
  }
  button:hover { background: #007acc; }
  a {
    color: #0ea5e9;
    text-decoration: none;
    font-size: 14px;
  }
  a:hover { text-decoration: underline; }
  .note {
    margin-top: 8px;
    font-size: 13px;
    color: #6b7280;
  }
</style>
</head>
<body>
  <div class="container">
    <h2>Yeni Hesap Oluştur</h2>

    <form method="POST" action="">
      <input type="text" name="name" placeholder="Ad Soyad" required>
      <input type="email" name="email" placeholder="E-posta" required>
      <input type="password" name="password" placeholder="Şifre" required>

      <div class="note">Not: Kayıt olan herkes <b>Misafir</b> olarak oluşturulur.</div><br>

      <button type="submit">Kayıt Ol</button><br><br>
    </form>

    <a href="index.php">Zaten hesabın var mı? Giriş yap</a>
  </div>
</body>
</html>