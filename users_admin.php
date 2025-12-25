<?php
session_start();
include_once "classes/Database.php";

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION["user"];
$role = $user["role"];

// Misafir giremez
if (!in_array($role, ["admin", "manager"])) {
    header("Location: dashboard.php");
    exit;
}

// Rol etiketi (DB değişmeden)
function roleLabel($r) {
    if ($r === "admin") return "Yönetici";
    if ($r === "manager") return "Çalışan";
    return "Misafir";
}

$db = new Database();
$conn = $db->conn;

// =====================
// SADECE admin CRUD
// =====================

// Misafir silme (sadece admin)
if (isset($_GET["delete"]) && $role === "admin") {
    $id = (int)$_GET["delete"];

    // Sadece misafir silinsin (role=user)
    $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='user'");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "<script>alert('Misafir silindi.'); window.location='users_admin.php';</script>";
    exit;
}

// Misafir ekleme (sadece admin) -> rol her zaman user
if (isset($_POST["create"]) && $role === "admin") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Şifre güvenliği önemli değil dedin ama en azından boş kaydetmeyelim
    if ($name === "" || $email === "" || $password === "") {
        echo "<script>alert('Lütfen tüm alanları doldur.');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            echo "<script>alert('Yeni misafir eklendi.'); window.location='users_admin.php';</script>";
            exit;
        } else {
            echo "<script>alert('Ekleme hatası: ".$conn->error."');</script>";
        }
    }
}

// Misafir güncelleme (sadece admin) -> sadece user rolündekiler güncellenir
if (isset($_POST["update"]) && $role === "admin") {
    $id = (int)$_POST["id"];
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);

    $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=? AND role='user'");
    $stmt->bind_param("ssi", $name, $email, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Misafir bilgileri güncellendi.'); window.location='users_admin.php';</script>";
        exit;
    } else {
        echo "<script>alert('Güncelleme hatası: ".$conn->error."');</script>";
    }
}

// Sadece misafirleri listele
$result = $conn->query("SELECT id, name, email, role FROM users WHERE role='user' ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Misafir Yönetimi - ManageX</title>
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

.actions-top {
  display:flex;
  justify-content: space-between;
  align-items:center;
  gap:10px;
  flex-wrap: wrap;
  margin-bottom: 10px;
}

.backbtn {
  background:#0ea5e9;
  color:white;
  padding:8px 14px;
  border:none;
  border-radius:8px;
  cursor:pointer;
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
dialog input {
  width: 100%;
  margin: 8px 0;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 8px;
  box-sizing: border-box;
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
.note {
  text-align:center;
  color:#64748b;
  font-size:13px;
  margin-bottom: 12px;
}
</style>
</head>
<body>

<div class="container">
  <h2>🙋 Misafir Yönetimi</h2>

  <div class="welcome">
    Hoş geldin, <strong><?= htmlspecialchars($user["name"]) ?></strong>
    (<?= roleLabel($role) ?>)!
    <?php if ($role === "admin"): ?>
      Misafirleri buradan yönetebilirsin.
    <?php else: ?>
      Misafirleri buradan görüntüleyebilirsin.
    <?php endif; ?>
  </div>

  <div class="actions-top">
    <?php if ($role === "admin"): ?>
      <button class="add" onclick="document.getElementById('addUser').showModal()">+ Yeni Misafir Ekle</button>
    <?php else: ?>
      <div class="note">Not: Çalışanlar misafirler üzerinde düzenleme yapamazlar.</div>
    <?php endif; ?>

    <button class="backbtn" onclick="window.location='dashboard.php'">🡐 Panele Dön</button>
  </div>

  <table>
    <tr>
      <th>ID</th>
      <th>Ad Soyad</th>
      <th>E-posta</th>
      <th>Rol</th>
      <?php if ($role === "admin"): ?>
        <th>İşlem</th>
      <?php endif; ?>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $row["id"] ?></td>
      <td><?= htmlspecialchars($row["name"]) ?></td>
      <td><?= htmlspecialchars($row["email"]) ?></td>
      <td>Misafir</td>

      <?php if ($role === "admin"): ?>
      <td>
        <button class="edit" onclick="editUser(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>', '<?= htmlspecialchars($row['email']) ?>')">Düzenle</button>
        <button class="delete" onclick="if(confirm('Silmek istediğine emin misin?')) window.location='?delete=<?= $row['id'] ?>'">Sil</button>
      </td>
      <?php endif; ?>
    </tr>
    <?php endwhile; ?>
  </table>

  <button class="logout" onclick="window.location='logout.php'">🚪 Çıkış Yap</button>
</div>

<?php if ($role === "admin"): ?>
<!-- Misafir ekleme -->
<dialog id="addUser">
  <form method="POST">
    <h3 style="text-align:center;color:#0f172a;margin-bottom:15px;">Yeni Misafir Ekle</h3>

    <input type="text" name="name" placeholder="Ad Soyad" required>
    <input type="email" name="email" placeholder="E-posta" required>
    <input type="password" name="password" placeholder="Şifre" required>

    <div class="dialog-actions">
      <button type="submit" name="create" class="save-btn">Ekle</button>
      <button type="button" class="cancel-btn" onclick="document.getElementById('addUser').close()">Kapat</button>
    </div>
  </form>
</dialog>

<!-- Misafir düzenleme -->
<dialog id="editUser">
  <form method="POST">
    <h3 style="text-align:center;color:#0f172a;margin-bottom:15px;">Misafir Bilgilerini Güncelle</h3>
    <input type="hidden" id="edit_id" name="id">
    <input type="text" id="edit_name" name="name" placeholder="Ad Soyad" required>
    <input type="email" id="edit_email" name="email" placeholder="E-posta" required>

    <div class="dialog-actions">
      <button type="submit" name="update" class="save-btn">Kaydet</button>
      <button type="button" class="cancel-btn" onclick="document.getElementById('editUser').close()">Kapat</button>
    </div>
  </form>
</dialog>

<script>
function editUser(id, name, email) {
  document.getElementById('edit_id').value = id;
  document.getElementById('edit_name').value = name;
  document.getElementById('edit_email').value = email;
  document.getElementById('editUser').showModal();
}
</script>
<?php endif; ?>

</body>
</html>