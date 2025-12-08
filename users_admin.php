<?php
session_start();
include_once "classes/Database.php";

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION["user"];
if ($user["role"] !== "admin") {
    header("Location: index.php");
    exit;
}

$db = new Database();
$conn = $db->conn;

// Kullanıcı silme
if (isset($_GET["delete"])) {
    $id = $_GET["delete"];
    $conn->query("DELETE FROM users WHERE id=$id");
    echo "<script>alert('Kullanıcı silindi.'); window.location='users_admin.php';</script>";
}

// Kullanıcı ekleme
if (isset($_POST["create"])) {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"];
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);
    if ($stmt->execute()) {
        echo "<script>alert('Yeni kullanıcı eklendi.'); window.location='users_admin.php';</script>";
    }
}

// Kullanıcı güncelleme
if (isset($_POST["update"])) {
    $id = $_POST["id"];
    $name = $_POST["name"];
    $email = $_POST["email"];
    $role = $_POST["role"];
    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $role, $id);
    if ($stmt->execute()) {
        echo "<script>alert('Kullanıcı bilgileri güncellendi.'); window.location='users_admin.php';</script>";
    }
}

$result = $conn->query("SELECT * FROM users ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Kullanıcı Yönetimi - ManageX</title>
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
  margin-bottom: 10px;
  text-align: center;
}
.welcome {
  text-align: center;
  color: #374151;
  font-size: 15px;
  margin-bottom: 25px;
}
table {
  width: 100%;
  border-collapse: collapse;
}
th, td {
  border: 1px solid #ddd;
  padding: 10px;
  text-align: center;
  font-size: 14px;
}
th { background: #0ea5e9; color: white; }
button {
  border: none;
  padding: 6px 10px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
}
.edit { background: #facc15; color: black; }
.delete { background: #ef4444; color: white; }
.add {
  background: #22c55e;
  color: white;
  padding: 10px 14px;
  border-radius: 6px;
  margin-bottom: 15px;
  cursor: pointer;
}
.logout {
  margin-top: 20px;
  background: #ef4444;
  color: white;
  padding: 10px 16px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  font-weight: 600;
  width: 100%;
}
dialog {
  max-width: 420px;
  width: 90%;
  border: none;
  border-radius: 16px;
  padding: 25px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.25);
}
dialog input, dialog select {
  width: 100%;
  margin: 8px 0;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 8px;
}
.dialog-actions {
  display: flex;
  justify-content: space-between;
  margin-top: 15px;
  gap: 10px;
}
.save-btn {
  flex: 1;
  background: #0ea5e9;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 10px;
  font-weight: 600;
  cursor: pointer;
}
.cancel-btn {
  flex: 1;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 10px;
  font-weight: 600;
  cursor: pointer;
}
</style>
</head>
<body>

<div class="container">
  <h2>👑 Kullanıcı Yönetimi</h2>
  <div class="welcome">Hoş geldin, <strong><?= htmlspecialchars($user["name"]) ?></strong>! Bugün sistem senin kontrolünde 🔥</div>

  <button class="add" onclick="document.getElementById('addUser').showModal()">+ Yeni Kullanıcı Ekle</button>

  <button onclick="window.location='dashboard.php'" style="background:#0ea5e9;color:white;padding:8px 14px;border:none;border-radius:8px;cursor:pointer;float:right;margin-bottom:10px;">
  🡐 Panele Dön
  </button>

  <table>
    <tr>
      <th>ID</th>
      <th>Ad Soyad</th>
      <th>E-posta</th>
      <th>Rol</th>
      <th>İşlem</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $row["id"] ?></td>
      <td><?= htmlspecialchars($row["name"]) ?></td>
      <td><?= htmlspecialchars($row["email"]) ?></td>
      <td><?= htmlspecialchars($row["role"]) ?></td>
      <td>
        <button class="edit" onclick="editUser(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>', '<?= htmlspecialchars($row['email']) ?>', '<?= $row['role'] ?>')">Düzenle</button>
        <button class="delete" onclick="if(confirm('Silmek istediğine emin misin?')) window.location='?delete=<?= $row['id'] ?>'">Sil</button>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>

  <button class="logout" onclick="window.location='logout.php'">🚪 Çıkış Yap</button>
</div>

<!-- Kullanıcı ekleme -->
<dialog id="addUser">
  <form method="POST">
    <h3 style="text-align:center;color:#0f172a;margin-bottom:15px;">Yeni Kullanıcı Ekle</h3>

    <div style="display:flex;flex-direction:column;gap:10px;">
      <input type="text" name="name" placeholder="Ad Soyad" required>
      <input type="email" name="email" placeholder="E-posta" required>
      <input type="password" name="password" placeholder="Şifre" required>
      <select name="role" required>
        <option value="user">Kullanıcı</option>
        <option value="manager">Yönetici</option>
        <option value="admin">Admin</option>
      </select>
    </div>

    <div class="dialog-actions">
      <button type="submit" name="create" class="save-btn">Ekle</button>
      <button type="button" class="cancel-btn" onclick="document.getElementById('addUser').close()">Kapat</button>
    </div>
  </form>
</dialog>

<!-- Kullanıcı düzenleme -->
<dialog id="editUser">
  <form method="POST">
    <h3 style="text-align:center;color:#0f172a;margin-bottom:15px;">Kullanıcı Bilgilerini Güncelle</h3>
    <input type="hidden" id="edit_id" name="id">
    <input type="text" id="edit_name" name="name" placeholder="Ad Soyad" required>
    <input type="email" id="edit_email" name="email" placeholder="E-posta" required>
    <select id="edit_role" name="role" required>
      <option value="user">Kullanıcı</option>
      <option value="manager">Yönetici</option>
      <option value="admin">Admin</option>
    </select>
    <div class="dialog-actions">
      <button type="submit" name="update" class="save-btn">Kaydet</button>
      <button type="button" class="cancel-btn" onclick="document.getElementById('editUser').close()">Kapat</button>
    </div>
  </form>
</dialog>

<script>
function editUser(id, name, email, role) {
  document.getElementById('edit_id').value = id;
  document.getElementById('edit_name').value = name;
  document.getElementById('edit_email').value = email;
  document.getElementById('edit_role').value = role;
  document.getElementById('editUser').showModal();
}
</script>

</body>
</html>