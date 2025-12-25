<?php
session_start();
include_once "classes/Database.php";

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION["user"];

// Sadece Yönetici (admin) randevu yönetebilir
if ($user["role"] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

// Rol etiketi (DB'yi değiştirmeden)
function roleLabel($role) {
    if ($role === "admin") return "Yönetici";
    if ($role === "manager") return "Çalışan";
    return "Misafir";
}

$db = new Database();
$conn = $db->conn;

// Randevu durumu güncelleme
if (isset($_GET["approve"])) {
    $id = (int)$_GET["approve"];
    $conn->query("UPDATE appointments SET status='approved' WHERE id=$id");
    echo "<script>alert('Randevu onaylandı.'); window.location='appointments_admin.php';</script>";
}
if (isset($_GET["reject"])) {
    $id = (int)$_GET["reject"];
    $conn->query("UPDATE appointments SET status='rejected' WHERE id=$id");
    echo "<script>alert('Randevu reddedildi.'); window.location='appointments_admin.php';</script>";
}

$result = $conn->query("
    SELECT a.*, u.name AS user_name, u.email AS user_email
    FROM appointments a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.datetime DESC
");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Randevu Yönetimi - ManageX</title>
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
    max-width: 900px;
    margin: auto;
    }
    h2 {
    color: #0f172a;
    text-align: center;
    margin-bottom: 20px;
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
    font-weight: bold;
    border-radius: 8px;
    padding: 4px 8px;
    }
    .status.pending { background: #fbbf24; color: #78350f; }
    .status.approved { background: #22c55e; color: white; }
    .status.rejected { background: #ef4444; color: white; }

    button {
    border: none;
    padding: 6px 10px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    }
    .approve { background: #22c55e; color: white; }
    .reject { background: #ef4444; color: white; }
    .logout {
    margin-top: 20px;
    background: #0ea5e9;
    color: white;
    padding: 10px 16px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    width: 100%;
    }
</style>
</head>
<body>

<div class="container">
  <h2>📅 Randevu Yönetimi</h2>
  <p style="text-align:center;color:#374151;">
    Hoş geldin <strong><?= htmlspecialchars($user["name"]) ?></strong>
    (<strong><?= roleLabel($user["role"]) ?></strong>)! Gelen randevuları buradan yönetebilirsin.
  </p>

  <button onclick="window.location='dashboard.php'" style="background:#0ea5e9;color:white;padding:8px 14px;border:none;border-radius:8px;cursor:pointer;float:right;margin-bottom:10px;">
  🡐 Panele Dön
  </button>

  <table>
    <tr>
      <th>ID</th>
      <th>Kullanıcı</th>
      <th>E-posta</th>
      <th>Tarih / Saat</th>
      <th>Durum</th>
      <th>İşlem</th>
    </tr>
    <?php while($r = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $r["id"] ?></td>
        <td><?= htmlspecialchars($r["user_name"]) ?></td>
        <td><?= htmlspecialchars($r["user_email"]) ?></td>
        <td><?= htmlspecialchars($r["datetime"]) ?></td>
        <td>
          <span class="status <?= $r["status"] ?>">
            <?= $r["status"] === "approved" ? "Onaylandı" : ($r["status"] === "rejected" ? "Reddedildi" : "Bekliyor") ?>
          </span>
        </td>
        <td>
          <?php if ($r["status"] === "pending"): ?>
            <button class="approve" onclick="window.location='?approve=<?= $r['id'] ?>'">Onayla</button>
            <button class="reject" onclick="window.location='?reject=<?= $r['id'] ?>'">Reddet</button>
          <?php else: ?>
            -
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>

  <button class="logout" onclick="window.location='logout.php'">🚪 Çıkış Yap</button>
</div>

</body>
</html>