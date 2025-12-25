<?php
session_start();
include_once "classes/Database.php";
include_once "classes/Employee.php";

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION["user"];
$role = $user["role"];

// Misafir giremez
if ($role === "user") {
    header("Location: dashboard.php");
    exit;
}

// Rol etiketi
function roleLabel($role) {
    if ($role === "admin") return "Yönetici";
    if ($role === "manager") return "Çalışan";
    return "Misafir";
}

$db  = new Database();
$conn = $db->conn;
$emp = new Employee($conn);

// ===========================
// Yönetici işlemleri (CRUD)
// ===========================

// Çalışan silme (sadece admin)
if (isset($_GET["delete"]) && $role === "admin") {
    $id = (int)$_GET["delete"];
    $stmt = $conn->prepare("DELETE FROM employees WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "<script>alert('Çalışan silindi.'); window.location='employee.php';</script>";
    exit;
}

// Çalışan ekleme (sadece admin)
if (isset($_POST["create"]) && $role === "admin") {
    $name = $_POST["name"];
    $department = $_POST["department"];
    $position = $_POST["position"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];

    $photo = $_FILES["photo"]["name"] ?? null;
    if ($photo) {
        $targetDir = "uploads/employees/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $target = $targetDir . basename($photo);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target);
    }

    $stmt = $conn->prepare("INSERT INTO employees (name, department, position, email, phone, photo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $department, $position, $email, $phone, $photo);
    if ($stmt->execute()) {
        echo "<script>alert('Yeni çalışan eklendi.'); window.location='employee.php';</script>";
        exit;
    }
}

// Çalışan düzenleme (sadece admin)
if (isset($_POST["update"]) && $role === "admin") {
    $id = (int)$_POST["id"];
    $name = $_POST["name"];
    $department = $_POST["department"];
    $position = $_POST["position"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];

    $photo = $_FILES["photo"]["name"] ?? null;
    if ($photo) {
        $targetDir = "uploads/employees/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $target = $targetDir . basename($photo);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target);

        $stmtPhoto = $conn->prepare("UPDATE employees SET photo=? WHERE id=?");
        $stmtPhoto->bind_param("si", $photo, $id);
        $stmtPhoto->execute();
    }

    $stmt = $conn->prepare("UPDATE employees SET name=?, department=?, position=?, email=?, phone=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $department, $position, $email, $phone, $id);
    if ($stmt->execute()) {
        echo "<script>alert('Çalışan bilgileri güncellendi.'); window.location='employee.php';</script>";
        exit;
    }
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
    gap: 10px;
  }

  .topbar h1 {
    font-size: 24px;
    color: #0f172a;
    margin: 0;
  }

  .topbar .right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
  }

  .badge {
    background: #111827;
    color: #fff;
    padding: 8px 12px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 700;
    opacity: 0.9;
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

  .btn.gray {
    background: #334155;
  }
  .btn.gray:hover {
    background: #1f2937;
  }

  .btn.red {
    background: #ef4444;
  }
  .btn.red:hover {
    background: #b91c1c;
  }

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

  .muted { color: #6b7280; font-size: 14px; }

  .actions {
    margin-top: 10px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
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

  /* --- Dialog (create & edit) ortak stil --- */
  dialog {
    max-width: 420px;
    width: 90%;
    border: none;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 12px 30px rgba(0,0,0,.25);
    overflow: hidden;
  }

  dialog form {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  dialog input[type="text"],
  dialog input[type="email"],
  dialog input[type="file"] {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #f9fafb;
    font-size: 14px;
    outline: none;
    box-sizing: border-box;
  }

  dialog input:focus {
    border-color: #0ea5e9;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(14,165,233,.15);
  }
</style>
</head>
<body>
    <div class="main">
        <div class="topbar">
            <h1>Şirket Ortakları</h1>

            <div class="right">
                <span class="badge">
                    <?= htmlspecialchars($user["name"]) ?> • <?= roleLabel($role) ?>
                </span>

                <button class="btn gray" onclick="window.location='dashboard.php'">🡐 Panele Dön</button>

                <?php if ($role === "admin"): ?>
                    <button class="btn" onclick="document.getElementById('createDlg').showModal()">+ Yeni Ortak</button>
                <?php endif; ?>

                <button class="btn red" onclick="window.location='logout.php'">🚪 Çıkış</button>
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

                            <?php if ($role === "admin"): ?>
                                <button class="link" onclick="editEmployee(<?= $r['id'] ?>, '<?= htmlspecialchars($r['name']) ?>', '<?= htmlspecialchars($r['department']) ?>', '<?= htmlspecialchars($r['position']) ?>', '<?= htmlspecialchars($r['email']) ?>', '<?= htmlspecialchars($r['phone']) ?>')">Düzenle</button>
                                <button class="link danger" onclick="if(confirm('Silmek istediğine emin misin?')) window.location='employee.php?delete=<?= $r['id'] ?>'">Sil</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="map">
        <h3 style="text-align:center; margin-bottom:10px;">Şirket Konumu</h3>
        <iframe
            src="https://www.google.com/maps?q=Avrasya+Üniversitesi+Trabzon&output=embed&z=16"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>

<?php if ($role === "admin"): ?>
<!-- Yeni çalışan ekleme -->
<dialog id="createDlg">
  <form method="POST" enctype="multipart/form-data">
    <h3 style="text-align:center;color:#0f172a;margin:0 0 10px;">Yeni Çalışan Ekle</h3>

    <input type="text" name="name" placeholder="Ad Soyad" required>
    <input type="text" name="department" placeholder="Departman">
    <input type="text" name="position" placeholder="Pozisyon">
    <input type="email" name="email" placeholder="E-posta">
    <input type="text" name="phone" placeholder="Telefon">
    <input type="file" name="photo">

    <div style="display:flex;gap:10px;margin-top:10px;">
      <button type="submit" name="create" class="btn" style="flex:1;">Kaydet</button>
      <button type="button" class="btn red" style="flex:1;" onclick="document.getElementById('createDlg').close()">Kapat</button>
    </div>
  </form>
</dialog>

<!-- Çalışan düzenleme -->
<dialog id="editDlg">
  <form method="POST" enctype="multipart/form-data">
    <h3 style="text-align:center;color:#0f172a;margin:0 0 10px;">Çalışanı Düzenle</h3>

    <input type="hidden" name="id" id="edit_id">
    <input type="text" name="name" id="edit_name" placeholder="Ad Soyad" required>
    <input type="text" name="department" id="edit_department" placeholder="Departman">
    <input type="text" name="position" id="edit_position" placeholder="Pozisyon">
    <input type="email" name="email" id="edit_email" placeholder="E-posta">
    <input type="text" name="phone" id="edit_phone" placeholder="Telefon">
    <input type="file" name="photo">

    <div style="display:flex;gap:10px;margin-top:10px;">
      <button type="submit" name="update" class="btn" style="flex:1;">Kaydet</button>
      <button type="button" class="btn red" style="flex:1;" onclick="document.getElementById('editDlg').close()">Kapat</button>
    </div>
  </form>
</dialog>

<script>
  function editEmployee(id, name, department, position, email, phone) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_department').value = department;
    document.getElementById('edit_position').value = position;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('editDlg').showModal();
  }
</script>
<?php endif; ?>

</body>
</html>