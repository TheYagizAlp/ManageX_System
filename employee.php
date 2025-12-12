<?php
session_start();
include_once "classes/Database.php";
include_once "classes/Employee.php";

$db  = new Database();
$conn = $db->conn;
$emp = new Employee($conn);

// Çalışan silme
if (isset($_GET["delete"])) {
    $id = $_GET["delete"];
    $conn->query("DELETE FROM employees WHERE id=$id");
    echo "<script>alert('Çalışan silindi.'); window.location='employee.php';</script>";
}

// Çalışan ekleme
if (isset($_POST["create"])) {
    $name = $_POST["name"];
    $department = $_POST["department"];
    $position = $_POST["position"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];

    $photo = $_FILES["photo"]["name"] ?? null;
    if ($photo) {
        $target = "uploads/employees/" . basename($photo);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target);
    }

    $stmt = $conn->prepare("INSERT INTO employees (name, department, position, email, phone, photo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $department, $position, $email, $phone, $photo);
    if ($stmt->execute()) {
        echo "<script>alert('Yeni çalışan eklendi.'); window.location='employee.php';</script>";
    }
}

// Çalışan düzenleme (UPDATE)
if (isset($_POST["update"])) {
    $id = $_POST["id"];
    $name = $_POST["name"];
    $department = $_POST["department"];
    $position = $_POST["position"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];

    $photo = $_FILES["photo"]["name"] ?? null;
    if ($photo) {
        $target = "uploads/employees/" . basename($photo);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target);
        $conn->query("UPDATE employees SET photo='$photo' WHERE id=$id");
    }

    $stmt = $conn->prepare("UPDATE employees SET name=?, department=?, position=?, email=?, phone=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $department, $position, $email, $phone, $id);
    if ($stmt->execute()) {
        echo "<script>alert('Çalışan bilgileri güncellendi.'); window.location='employee.php';</script>";
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

  .muted { color: #6b7280; font-size: 14px; }

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

  /* --- Dialog (create & edit) ortak stil --- */
dialog {
  max-width: 420px;
  width: 90%;
  border: none;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 12px 30px rgba(0,0,0,.25);
}

dialog h3 {
  margin: 0 0 12px 0;
  text-align: center;
  color: #0f172a;
  font-size: 18px;
  font-weight: 700;
}

/* Form yerleşimi */
dialog form {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

/* Inputlar */
dialog input[type="text"],
dialog input[type="email"],
dialog input[type="password"],
dialog input[type="file"] {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  background: #f9fafb;
  font-size: 14px;
  outline: none;
  transition: border .2s, box-shadow .2s, background .2s;
}

dialog input:focus {
  border-color: #0ea5e9;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(14,165,233,.15);
}

/* Alt butonlar */
dialog .actions,
dialog form > div:last-child {
  display: flex;
  gap: 10px;
  margin-top: 6px;
}

dialog button[type="submit"],
dialog button[type="button"] {
  flex: 1;
  padding: 10px 12px;
  border: none;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  transition: transform .05s ease, filter .2s ease;
}

dialog button[type="submit"] {
  background: #0ea5e9;
  color: #fff;
}
dialog button[type="button"] {
  background: #ef4444;
  color: #fff;
}

dialog button:hover { filter: brightness(.95); }
dialog button:active { transform: translateY(1px); }

/* Kaydırma çubuğunu engelle */
dialog::-webkit-scrollbar {
  display: none;
}

/* Inputların taşmasını engelle */
dialog input,
dialog select,
dialog button {
  max-width: 100%;
  box-sizing: border-box;
}

/* Dialog genel overflow düzeltmesi */
dialog {
  overflow: hidden;
}

</style>
</head>
<body>
    <div class="main">
        <div class="topbar">
            <h1>Çalışanlar</h1>
            <button class="btn" onclick="document.getElementById('createDlg').showModal()">+ Yeni Çalışan</button>
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
                            <button class="link" onclick="editEmployee(<?= $r['id'] ?>, '<?= htmlspecialchars($r['name']) ?>', '<?= htmlspecialchars($r['department']) ?>', '<?= htmlspecialchars($r['position']) ?>', '<?= htmlspecialchars($r['email']) ?>', '<?= htmlspecialchars($r['phone']) ?>')">Düzenle</button>
                            <button class="link danger" onclick="window.location='employee.php?delete=<?= $r['id'] ?>'">Sil</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="map">
        <h3 style="text-align:center; margin-bottom:10px;">Şirket Konumu</h3>
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3058.928501740542!2d39.7200!3d41.0039!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4065490f1bce4579%3A0x8e9b6f2d50443d91!2sAvrasya%20%C3%9Cniversitesi!5e0!3m2!1str!2str!4v1684932439880" loading="lazy"></iframe>
    </div>

<!-- Yeni çalışan ekleme -->
<dialog id="createDlg" style="max-width:420px;width:90%;border:none;border-radius:16px;padding:25px;box-shadow:0 8px 25px rgba(0,0,0,0.25);">
  <form method="POST" enctype="multipart/form-data">
    <h3 style="text-align:center;color:#0f172a;margin-bottom:15px;">Yeni Çalışan Ekle</h3>

    <input type="text" name="name" placeholder="Ad Soyad" required>
    <input type="text" name="department" placeholder="Departman">
    <input type="text" name="position" placeholder="Pozisyon">
    <input type="email" name="email" placeholder="E-posta">
    <input type="text" name="phone" placeholder="Telefon">
    <input type="file" name="photo">

    <div style="display:flex;justify-content:space-between;margin-top:15px;gap:10px;">
      <button type="submit" name="create" style="flex:1;padding:10px;background:#0ea5e9;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer;">Kaydet</button>
      <button type="button" onclick="document.getElementById('createDlg').close()" style="flex:1;padding:10px;background:#ef4444;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer;">Kapat</button>
    </div>
  </form>
</dialog>

<!-- Çalışan düzenleme -->
<dialog id="editDlg" style="max-width:420px;width:90%;border:none;border-radius:16px;padding:25px;box-shadow:0 8px 25px rgba(0,0,0,0.25);">
  <form method="POST" enctype="multipart/form-data">
    <h3 style="text-align:center;color:#0f172a;margin-bottom:15px;">Çalışanı Düzenle</h3>

    <input type="hidden" name="id" id="edit_id">
    <input type="text" name="name" id="edit_name" placeholder="Ad Soyad" required>
    <input type="text" name="department" id="edit_department" placeholder="Departman">
    <input type="text" name="position" id="edit_position" placeholder="Pozisyon">
    <input type="email" name="email" id="edit_email" placeholder="E-posta">
    <input type="text" name="phone" id="edit_phone" placeholder="Telefon">
    <input type="file" name="photo">

    <div style="display:flex;justify-content:space-between;margin-top:15px;gap:10px;">
      <button type="submit" name="update" style="flex:1;padding:10px;background:#0ea5e9;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer;">Kaydet</button>
      <button type="button" onclick="document.getElementById('editDlg').close()" style="flex:1;padding:10px;background:#ef4444;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer;">Kapat</button>
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
</body>
</html>