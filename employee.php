<?php
session_start();
include_once "classes/Database.php";
include_once "classes/Employee.php";

// --- Giriş kontrolü ---
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

// --- Rol kontrolü ---
if ($_SESSION["user"]["role"] !== "admin" && $_SESSION["user"]["role"] !== "manager") {
    header("Location: appointment.php");
    exit;
}

$user = $_SESSION["user"];
$db  = new Database();
$conn = $db->conn;
$emp = new Employee($conn);

// --- Yeni çalışan ekleme ---
if (isset($_POST["create"])) {
    $emp->create($_POST, $_FILES);
}

// --- Çalışan silme ---
if (isset($_GET["delete"])) {
    $emp->delete($_GET["delete"]);
}

$rows = $emp->getAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Çalışan Yönetimi - ManageX</title>
<style>
body {
  font-family: 'Segoe UI', sans-serif;
  margin: 0;
  display: flex;
  height: 100vh;
  background: #f8fafc;
  color: #111827;
}
.main {
  flex: 3;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
  padding: 25px;
}
.map {
  flex: 1.2;
  background: #fff;
  border-left: 1px solid #e5e7eb;
  padding: 15px;
  display: flex;
  flex-direction: column;
}
.map iframe {
  width: 100%;
  height: 100%;
  border: none;
  border-radius: 12px;
}
.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}
.topbar h1 {
  font-size: 24px;
  color: #0f172a;
  margin: 0;
}
.btn {
  background: #0ea5e9;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 10px 16px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.3s;
}
.btn:hover { background: #0284c7; }
.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 20px;
}
.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  padding: 15px;
  transition: transform 0.2s;
}
.card:hover { transform: translateY(-3px); }
.card img {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  object-fit: cover;
  background: #e5e7eb;
}
.card .info { margin-top: 10px; }
.name {
  font-weight: 700;
  font-size: 18px;
  margin: 5px 0;
}
.muted {
  color: #6b7280;
  font-size: 14px;
}
.actions {
  margin-top: 10px;
  display: flex;
  gap: 8px;
}
.link {
  background: #6ba3dbff;
  border: none;
  border-radius: 6px;
  padding: 6px 10px;
  font-size: 13px;
  cursor: pointer;
  transition: 0.3s;
}
.link:hover { background: #0000006b; }
.danger { background: #ef4444; color: white; }
dialog {
  border: none;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}
dialog input, dialog button {
  width: 100%;
  padding: 10px;
  margin: 8px 0;
  border: 1px solid #ccc;
  border-radius: 6px;
}
</style>
</head>
<body>
  <div class="main">
    <div class="topbar">
      <button onclick="window.location='dashboard.php'" style="background:#0ea5e9;color:white;padding:8px 14px;border:none;border-radius:8px;cursor:pointer;float:right;">
        🡐 Panele Dön
      </button>
      <h1>Çalışanlar</h1>
      <div style="display:flex;align-items:center;gap:15px;">
        <span style="font-size:15px;color:#0f172a;">
          👋 Hoş geldin, <b><?= htmlspecialchars($user['name']) ?></b> (<?= htmlspecialchars($user['role']) ?>)
        </span>
        <button class="btn" onclick="window.location='logout.php'">🚪 Çıkış Yap</button>
        <button class="btn" style="background:#16a34a;" onclick="document.getElementById('createDlg').showModal()">+ Yeni Çalışan</button>
      </div>
    </div>

    <div class="grid">
      <?php while($r = $rows->fetch_assoc()): ?>
        <div class="card">
          <img src="uploads/employees/<?= htmlspecialchars($r['photo'] ?? '') ?>" onerror="this.src='assets/images/user.png'">
          <div class="info">
            <div class="name"><?= htmlspecialchars($r['name']) ?></div>
            <div class="muted"><?= htmlspecialchars($r['department'] ?: 'Departman?') ?> • <?= htmlspecialchars($r['position'] ?: 'Pozisyon?') ?></div>
            <div class="muted" style="margin-top:6px;">
              📧 <?= htmlspecialchars($r['email'] ?: '-') ?><br>
              📞 <?= htmlspecialchars($r['phone'] ?: '-') ?>
            </div>
            <div class="actions">
              <button class="link" onclick="window.location='employee_view.php?id=<?= $r['id'] ?>'">Detay</button>
              <button class="link danger" onclick="window.location='employee.php?delete=<?= $r['id'] ?>'">Sil</button>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <div class="map">
    <h3 style="text-align:center; margin-bottom:10px;">Şirket Konumu</h3>
    <iframe
      src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3011.7164953457277!2d39.8052232!3d40.987688399999996!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40643bdb9c5a2437%3A0xa98b6713d9cb81ac!2sAvrasya%20%C3%9Cniversitesi%20Pelitli%20Yerle%C5%9Fkesi!5e0!3m2!1str!2str!4v1765194447241!5m2!1str!2str"
      width="100%"
      height="100%"
      style="border:none;border-radius:12px;"
      loading="lazy"
      allowfullscreen>
    </iframe>
  </div>

  <!-- Çalışan ekleme formu -->
    <dialog id="createDlg" style="max-width: 420px; width: 90%; border: none; border-radius: 16px; padding: 25px; box-shadow: 0 8px 25px rgba(0,0,0,0.25);">
    <form method="POST" enctype="multipart/form-data">
        <h3 style="text-align:center; color:#0f172a; margin-bottom:15px;">Yeni Çalışan Ekle</h3>

        <div style="display:flex; flex-direction:column; gap:10px;">
        <input type="text" name="name" placeholder="Ad Soyad" required style="padding:10px; border-radius:8px; border:1px solid #ccc;">
        <input type="text" name="department" placeholder="Departman" style="padding:10px; border-radius:8px; border:1px solid #ccc;">
        <input type="text" name="position" placeholder="Pozisyon" style="padding:10px; border-radius:8px; border:1px solid #ccc;">
        <input type="email" name="email" placeholder="E-posta" style="padding:10px; border-radius:8px; border:1px solid #ccc;">
        <input type="text" name="phone" placeholder="Telefon" style="padding:10px; border-radius:8px; border:1px solid #ccc;">
        <input type="file" name="photo" accept="image/*" style="padding:8px; border-radius:8px; border:1px solid #ccc;">
        </div>

        <div style="display:flex; justify-content:space-between; margin-top:15px; gap:10px;">
        <button type="submit" name="create" style="flex:1; padding:10px; background:#0ea5e9; color:white; border:none; border-radius:8px; font-weight:600; cursor:pointer;">Kaydet</button>
        <button type="button" onclick="document.getElementById('createDlg').close()" style="flex:1; padding:10px; background:#ef4444; color:white; border:none; border-radius:8px; font-weight:600; cursor:pointer;">Kapat</button>
        </div>
    </form>
    </dialog>
</body>
</html>