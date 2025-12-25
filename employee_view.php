<?php
session_start();
include_once "classes/Database.php";
include_once "classes/Employee.php";

// Giriş kontrolü
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION["user"];

// Sadece Yönetici (admin) ve Çalışan (manager) görebilir
if (!in_array($user["role"], ["admin", "manager"])) {
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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$e  = $emp->getById($id);
if (!$e) { die("Kayıt bulunamadı"); }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($e['name']) ?> - Çalışan Detayı</title>
    <style>
        body{
            font-family: Arial, sans-serif;
            background:#f6f7fb;
            margin:0;
        }
        .wrap{
            max-width:900px;
            margin:40px auto;
            background:#fff;
            padding:24px;
            border-radius:14px;
            box-shadow:0 10px 24px rgba(0,0,0,.06);
        }
        .row{
            display:flex;
            gap:24px;
        }
        .avatar{
            width:160px;
            height:160px;
            border-radius:16px;
            object-fit:cover;
            background:#eee;
        }
        .title{
            margin:0;
            font-size:24px;
        }
        .muted{
            color:#6b7280;
            margin:6px 0;
        }
        .box{
            margin-top:16px;
            padding:12px;
            border:1px solid #e5e7eb;
            border-radius:12px;
        }
        .back{
            text-decoration:none;
            display:inline-block;
            margin-bottom:12px;
            color:#2563eb;
            font-weight:600;
        }
        .role{
            font-size:13px;
            color:#475569;
            margin-bottom:10px;
        }
    </style>
</head>
<body>

<div class="wrap">
    <div class="role">
        Giriş yapan: <strong><?= htmlspecialchars($user["name"]) ?></strong>
        (<?= roleLabel($user["role"]) ?>)
    </div>

    <a class="back" href="employee.php">← Çalışanlara dön</a>

    <div class="row">
        <img class="avatar"
             src="<?= 'uploads/employees/' . htmlspecialchars($e['photo'] ?? '') ?>"
             onerror="this.src='assets/images/large-user.png'">

        <div>
            <h1 class="title"><?= htmlspecialchars($e['name']) ?></h1>
            <div class="muted">
                <?= htmlspecialchars($e['department'] ?: 'Departman?') ?>
                •
                <?= htmlspecialchars($e['position'] ?: 'Pozisyon?') ?>
            </div>

            <div class="box">
                📧 <?= htmlspecialchars($e['email'] ?: '-') ?><br>
                📞 <?= htmlspecialchars($e['phone'] ?: '-') ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>