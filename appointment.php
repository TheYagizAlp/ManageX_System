<?php
session_start();
include_once "classes/Database.php";

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION["user"];
if ($user["role"] !== "user") {
    header("Location: index.php");
    exit;
}

$db = new Database();
$conn = $db->conn;

// Randevu ekleme
if (isset($_POST["create"])) {
    $datetime = $_POST["datetime"];
    $user_id = $user["id"];

    // Aynı tarih-saat dolu mu kontrol et
    $check = $conn->prepare("SELECT * FROM appointments WHERE datetime = ?");
    $check->bind_param("s", $datetime);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Bu saat dolu, başka bir zaman seç!');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO appointments (user_id, datetime, status, created_at) VALUES (?, ?, 'pending', NOW())");
        $stmt->bind_param("is", $user_id, $datetime);
        $stmt->execute();
        echo "<script>alert('Randevun başarıyla oluşturuldu!'); window.location='appointment.php';</script>";
    }
}

$appointments = $conn->query("SELECT * FROM appointments WHERE user_id={$user['id']} ORDER BY datetime DESC");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Randevularım - ManageX</title>
<style>
body {
  font-family: 'Segoe UI', sans-serif;
  background: #f3f4f6;
  margin: 0;
  padding: 40px;
}
.container {
  background: white;
  border-radius: 14px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  padding: 30px;
  max-width: 800px;
  margin: auto;
}
h2 {
  text-align: center;
  color: #0f172a;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
th, td {
  border: 1px solid #ddd;
  padding: 10px;
  text-align: center;
  font-size: 14px;
}
th {
  background: #0ea5e9;
  color: white;
}
.status {
  font-weight: 600;
  border-radius: 8px;
  padding: 4px 10px;
}
.status.pending { background: #fbbf24; color: #78350f; }
.status.approved { background: #22c55e; color: white; }
.status.rejected { background: #ef4444; color: white; }
button {
  border: none;
  padding: 8px 14px;
  border-radius: 8px;
  background: #0ea5e9;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: 0.3s;
}
button:hover { background: #0369a1; }
.logout {
  margin-top: 20px;
  background: #ef4444;
  padding: 10px 16px;
  border: none;
  border-radius: 8px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
}
</style>
</head>
<body>

<div class="container">
  <h2>📅 Randevularım</h2>
  <p style="text-align:center;color:#374151;">Hoş geldin <strong><?= htmlspecialchars($user["name"]) ?></strong>! Buradan yeni randevu oluşturabilir veya mevcutları görüntüleyebilirsin.</p>

  <button onclick="window.location='dashboard.php'" style="background:#0ea5e9;color:white;padding:8px 14px;border:none;border-radius:8px;cursor:pointer;float:right;margin-bottom:10px;">
  🡐 Panele Dön
  </button>

  <form method="POST" style="margin-top:20px; text-align:center;">
    <input type="datetime-local" name="datetime" required style="padding:10px;border-radius:8px;border:1px solid #ccc;">
    <button type="submit" name="create">Randevu Oluştur</button>
  </form>

  <table>
    <tr>
      <th>ID</th>
      <th>Tarih / Saat</th>
      <th>Durum</th>
      <th>Oluşturulma</th>
    </tr>
    <?php while($r = $appointments->fetch_assoc()): ?>
    <tr>
      <td><?= $r["id"] ?></td>
      <td><?= htmlspecialchars($r["datetime"]) ?></td>
      <td>
        <span class="status <?= $r["status"] ?>">
          <?= $r["status"] === "approved" ? "✅ Onaylandı" :
             ($r["status"] === "rejected" ? "❌ Reddedildi" : "⏳ Bekliyor") ?>
        </span>
      </td>
      <td><?= htmlspecialchars($r["created_at"]) ?></td>
    </tr>
    <?php endwhile; ?>
  </table>

  <button class="logout" onclick="window.location='logout.php'">🚪 Çıkış Yap</button>
</div>

</body>
</html>