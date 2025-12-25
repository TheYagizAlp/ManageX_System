<?php
session_start();
include_once "classes/Database.php";

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION["user"];
$role = $user["role"];

$roleNames = [
    "admin"   => "Yönetici",
    "manager" => "Çalışan",
    "user"    => "Misafir"
];

$roleLabel = $roleNames[$role] ?? $role;


$db = new Database();
$conn = $db->conn;

// İstatistikleri sadece admin veya manager görsün
$stats = [];
if ($role === "admin" || $role === "manager") {
    $stats['users'] = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
    $stats['employees'] = $conn->query("SELECT COUNT(*) AS c FROM employees")->fetch_assoc()['c'];
    $stats['pending'] = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE status='pending'")->fetch_assoc()['c'];
    $stats['approved'] = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE status='approved'")->fetch_assoc()['c'];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>ManageX Kontrol Paneli</title>
<style>
    body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #0ea5e9, #00704a);
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    color: #fff;
    padding: 40px 0;
    }
    .panel {
    background: rgba(255,255,255,0.1);
    border-radius: 20px;
    padding: 40px 50px;
    text-align: center;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    width: 90%;
    max-width: 800px;
    }
    h1 { font-size: 28px; margin-bottom: 15px; }
    h2 { color: #e2e8f0; font-weight: 400; margin-bottom: 30px; }

    .stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
    }
    .card {
    background: rgba(255,255,255,0.15);
    border-radius: 12px;
    padding: 20px;
    backdrop-filter: blur(8px);
    }
    .card h3 {
    font-size: 18px;
    margin: 0 0 8px 0;
    color: #e0f2fe;
    }
    .card span {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    }
    button {
    display: block;
    width: 100%;
    background: #fff;
    color: #0f172a;
    font-size: 16px;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    padding: 12px;
    margin: 10px 0;
    cursor: pointer;
    transition: 0.3s;
    }
    button:hover { background: #e2e8f0; }
    .logout {
    background: #ef4444;
    color: white;
    }
    .logout:hover { background: #b91c1c; }
</style>
</head>
<body>

<div class="panel">
  <h1>Hoş geldin, <?= htmlspecialchars($user["name"]) ?> 👋</h1>
  <h2>Rolün: <strong><?= $roleLabel ?></strong></h2>

  <?php if ($role === "admin" || $role === "manager"): ?>
    <div class="stats">
      <div class="card"><h3>👥 Kayıtlı Kullanıcılar</h3><span><?= $stats['users'] ?></span></div>
      <div class="card"><h3>🧑‍💼 Mevcut Ortaklar</h3><span><?= $stats['employees'] ?></span></div>
      <div class="card"><h3>⏳ Bekleyen Randevular</h3><span><?= $stats['pending'] ?></span></div>
      <div class="card"><h3>✅ Onaylı Randevular</h3><span><?= $stats['approved'] ?></span></div>
    </div>
  <?php endif; ?>

  <?php if ($role === "admin"): ?>
    <!-- admin (yönetici) -->
    <button onclick="window.location='users_admin.php'">🙋 Misafir Yönetimi</button>
    <button onclick="window.location='appointments_admin.php'">📅 Randevu Yönetimi</button>
    <button onclick="window.location='employee.php'">👨‍💼 Ortak Yönetimi</button>
    <button onclick="window.location='tasks.php'">🧾 Görev Yönetimi</button>
    <button onclick="window.location='map.php'">🗺️ Şirket Konumu / Yol Tarifi</button>

  <?php elseif ($role === "manager"): ?>
    <!-- manager (çalışan) -->
    <button onclick="window.location='appointment.php'">📅 Randevu Al</button>
    <button onclick="window.location='users_admin.php'">🙋 Misafirleri Gör</button>
    <button onclick="window.location='tasks.php'">🧾 Görevlerim</button>
    <button onclick="window.location='map.php'">🗺️ Şirket Konumu / Yol Tarifi</button>

  <?php elseif ($role === "user"): ?>
    <!-- user (misafir) -->
    <button onclick="window.location='appointment.php'">📅 Randevu Al</button>
    <button onclick="window.location='map.php'">🗺️ Şirket Konumu / Yol Tarifi</button>
    
  <?php endif; ?>

  <button class="logout" onclick="window.location='logout.php'">🚪 Çıkış Yap</button>
</div>

</body>
</html>