<?php
session_start();
include_once "classes/Database.php";
include_once "classes/Task.php";

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION["user"];
$role = $user["role"];

$db = new Database();
$conn = $db->conn;

$task = new Task($conn);

// Admin/manager için kullanıcı listesi
function fetchUsers($conn) {
    return $conn->query("SELECT id, name FROM users ORDER BY name ASC");
}

/* =========================
   DONE / PENDING / DELETE
========================= */
if (isset($_GET["done"])) {
    $task->markDone((int)$_GET["done"]);
    header("Location: tasks.php");
    exit;
}
if (isset($_GET["pending"])) {
    $task->markPending((int)$_GET["pending"]);
    header("Location: tasks.php");
    exit;
}
if (isset($_GET["delete"])) {
    $task->delete((int)$_GET["delete"]);
    header("Location: tasks.php");
    exit;
}

/* =========================
   CREATE
========================= */
if (isset($_POST["do_create"]) && $_POST["do_create"] === "1") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $priority = $_POST["priority"] ?? "medium";
    $due_date = $_POST["due_date"] ?? null;

    $assigned_to = ($role === "admin" || $role === "manager")
        ? (int)($_POST["assigned_to"] ?? $user["id"])
        : (int)$user["id"];

    if ($title === "") {
        echo "<script>alert('Başlık boş olamaz!');</script>";
    } else {
        // create(title, desc, priority, due_date, assigned_to, created_by)
        $ok = $task->create($title, $description, $priority, $due_date, $assigned_to, (int)$user["id"]);

        if ($ok) {
            echo "<script>alert('Görev eklendi!'); window.location='tasks.php';</script>";
            exit;
        } else {
            echo "<script>alert('Görev eklenemedi! DB: ".addslashes($conn->error)."');</script>";
        }
    }
}

/* =========================
   UPDATE (EDIT)
========================= */
if (isset($_POST["do_update"]) && $_POST["do_update"] === "1") {
    $id = (int)($_POST["id"] ?? 0);
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $priority = $_POST["priority"] ?? "medium";
    $due_date = $_POST["due_date"] ?? null;

    if ($role === "admin" || $role === "manager") {
        $assigned_to = (int)($_POST["assigned_to"] ?? 0);
        if ($assigned_to <= 0) $assigned_to = (int)$user["id"];
    } else {
        $assigned_to = (int)$user["id"];
    }

    if ($id <= 0) {
        echo "<script>alert('Hata: Görev ID gelmedi!');</script>";
    } elseif ($title === "") {
        echo "<script>alert('Başlık boş olamaz!');</script>";
    } else {
        $ok = $task->update($id, $title, $description, $priority, $due_date, $assigned_to);

        if ($ok) {
            echo "<script>alert('Görev güncellendi!'); window.location='tasks.php';</script>";
            exit;
        } else {
            echo "<script>alert('Güncelleme başarısız! DB: ".addslashes($conn->error)."');</script>";
        }
    }
}

/* =========================
   FILTER + LIST
========================= */
$status = $_GET["status"] ?? "";
$q = trim($_GET["q"] ?? "");

$rows = $task->list($role, (int)$user["id"], $status, $q);
if ($rows === false) {
    die("Task->list() hata verdi. SQL/kolon adlarını kontrol et.");
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Görevler - ManageX</title>
<style>
  body{
    font-family:'Segoe UI',sans-serif;
    background:#f3f4f6;
    margin:0;
    padding:40px;
  }
  .container{
    background:#fff;
    border-radius:14px;
    box-shadow:0 4px 20px rgba(0,0,0,.08);
    padding:28px;
    max-width:1050px;
    margin:auto;
  }
  h2{
    text-align:center;
    margin:0 0 6px 0;
    color:#0f172a;
    font-size:24px;
  }
  .welcome{
    text-align:center;
    color:#374151;
    margin-bottom:18px;
    font-size:14px;
  }

  .top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom:12px;
  }

  .btn{
    border:none;
    padding:10px 14px;
    border-radius:10px;
    cursor:pointer;
    font-weight:800;
    font-size:14px;
    transition:.2s;
  }
  .btn:hover{ filter:brightness(.95); }
  .btn.green{ background:#22c55e; color:#fff; }
  .btn.blue{ background:#0ea5e9; color:#fff; }
  .btn.red{ background:#ef4444; color:#fff; }
  .btn.gray{ background:#e5e7eb; color:#111827; }

  .filters{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin:10px 0 16px 0;
    align-items:center;
  }
  .filters input,.filters select{
    padding:10px 12px;
    border:1px solid #ddd;
    border-radius:10px;
    outline:none;
    font-size:14px;
  }

  table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
  }
  th,td{
    border:1px solid #e5e7eb;
    padding:10px;
    text-align:center;
    font-size:14px;
    vertical-align:top;
  }
  th{
    background:#0ea5e9;
    color:#fff;
    font-weight:900;
  }
  td.left{
    text-align:left;
  }

  .badge{
    padding:4px 10px;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
    display:inline-block;
    white-space:nowrap;
  }
  .p-low{ background:#e5e7eb; color:#111827; }
  .p-medium{ background:#fbbf24; color:#78350f; }
  .p-high{ background:#ef4444; color:#fff; }

  .s-pending{ background:#fbbf24; color:#78350f; }
  .s-done{ background:#22c55e; color:#fff; }

  .actions{
    display:flex;
    gap:8px;
    justify-content:center;
    flex-wrap:wrap;
  }
  .mini{
    border:none;
    padding:6px 10px;
    border-radius:10px;
    cursor:pointer;
    font-weight:900;
    font-size:13px;
  }
  .mini.edit{ background:#facc15; }
  .mini.done{ background:#22c55e; color:#fff; }
  .mini.pending{ background:#0ea5e9; color:#fff; }
  .mini.del{ background:#ef4444; color:#fff; }

  dialog{
    max-width:540px;
    width:92%;
    border:none;
    border-radius:16px;
    padding:22px;
    box-shadow:0 12px 30px rgba(0,0,0,.25);
    overflow:hidden;
  }
  dialog::backdrop{ background:rgba(0,0,0,.35); }
  dialog h3{
    margin:0 0 12px 0;
    text-align:center;
    color:#0f172a;
    font-size:18px;
    font-weight:900;
  }
  dialog input, dialog textarea, dialog select{
    width:100%;
    box-sizing:border-box;
    padding:10px 12px;
    border:1px solid #ddd;
    border-radius:10px;
    outline:none;
    margin:6px 0;
    font-size:14px;
  }
  dialog textarea{ resize:vertical; min-height:90px; }
  .dlg-actions{
    display:flex;
    gap:10px;
    margin-top:10px;
  }
  .dlg-actions button{
    flex:1;
    border:none;
    border-radius:10px;
    padding:10px 12px;
    font-weight:900;
    cursor:pointer;
    font-size:14px;
  }
</style>
</head>
<body>

<div class="container">
  <h2>✅ Görev Yönetimi</h2>
  <div class="welcome">Hoş geldin <strong><?= htmlspecialchars($user["name"]) ?></strong>!</div>

  <div class="top">
    <button class="btn green" type="button" onclick="document.getElementById('createTaskDlg').showModal()">+ Yeni Görev</button>

    <div style="display:flex;gap:10px;align-items:center;">
      <button class="btn blue" type="button" onclick="window.location='dashboard.php'">← Panele Dön</button>
      <button class="btn red" type="button" onclick="window.location='logout.php'">🚪 Çıkış</button>
    </div>
  </div>

  <form class="filters" method="GET" action="tasks.php">
    <input type="text" name="q" placeholder="Ara (başlık/açıklama)" value="<?= htmlspecialchars($q) ?>">
    <select name="status">
      <option value="" <?= $status==="" ? "selected" : "" ?>>Tümü</option>
      <option value="pending" <?= $status==="pending" ? "selected" : "" ?>>Bekleyen</option>
      <option value="done" <?= $status==="done" ? "selected" : "" ?>>Yapıldı</option>
    </select>
    <button class="btn gray" type="submit">Filtrele</button>
  </form>

  <table>
    <tr>
      <th style="width:60px;">ID</th>
      <th>Başlık</th>
      <th style="width:90px;">Öncelik</th>
      <th style="width:90px;">Durum</th>
      <th style="width:110px;">Son Tarih</th>
      <th style="width:120px;">Atanan</th>
      <th style="width:220px;">İşlem</th>
    </tr>

    <?php while($t = $rows->fetch_assoc()): ?>
      <?php
        $p = $t["priority"] ?? "medium";
        $pc = $p==="high" ? "p-high" : ($p==="low" ? "p-low" : "p-medium");
        $pt = $p==="high" ? "Yüksek" : ($p==="low" ? "Düşük" : "Orta");

        $st = $t["status"] ?? "pending";
      ?>
      <tr>
        <td><?= (int)$t["id"] ?></td>

        <td class="left">
          <div style="font-weight:900;color:#0f172a;"><?= htmlspecialchars($t["title"] ?? "") ?></div>
          <?php if (!empty($t["description"])): ?>
            <div style="color:#6b7280;font-size:12px;margin-top:4px;line-height:1.3;">
              <?= nl2br(htmlspecialchars($t["description"])) ?>
            </div>
          <?php endif; ?>
        </td>

        <td><span class="badge <?= $pc ?>"><?= $pt ?></span></td>

        <td>
          <?php if($st==="done"): ?>
            <span class="badge s-done">Yapıldı</span>
          <?php else: ?>
            <span class="badge s-pending">Bekliyor</span>
          <?php endif; ?>
        </td>

        <td><?= htmlspecialchars($t["due_date"] ?? "-") ?></td>
        <td><?= htmlspecialchars($t["assigned_name"] ?? "-") ?></td>

        <td>
          <div class="actions">

            <!-- ✅ DÜZENLE: artık inline JS string basmıyoruz, data-* kullanıyoruz -->
            <button class="mini edit" type="button"
              data-id="<?= (int)$t['id'] ?>"
              data-title="<?= htmlspecialchars($t['title'] ?? '', ENT_QUOTES) ?>"
              data-description="<?= htmlspecialchars($t['description'] ?? '', ENT_QUOTES) ?>"
              data-priority="<?= htmlspecialchars($t['priority'] ?? 'medium', ENT_QUOTES) ?>"
              data-duedate="<?= htmlspecialchars($t['due_date'] ?? '', ENT_QUOTES) ?>"
              data-assigned="<?= (int)($t['assigned_to'] ?? 0) ?>"
              onclick="openEditFromBtn(this)"
            >Düzenle</button>

            <?php if($st==="pending"): ?>
              <button class="mini done" type="button" onclick="window.location='?done=<?= (int)$t['id'] ?>'">Yapıldı</button>
            <?php else: ?>
              <button class="mini pending" type="button" onclick="window.location='?pending=<?= (int)$t['id'] ?>'">Geri Al</button>
            <?php endif; ?>

            <button class="mini del" type="button"
              onclick="if(confirm('Görev silinsin mi?')) window.location='?delete=<?= (int)$t['id'] ?>'">Sil</button>

          </div>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>

<!-- ======================
     CREATE DIALOG
====================== -->
<dialog id="createTaskDlg">
  <form method="POST" action="tasks.php">
    <h3>Yeni Görev</h3>

    <input type="hidden" name="do_create" value="1">

    <input type="text" name="title" placeholder="Başlık" required>
    <textarea name="description" placeholder="Açıklama"></textarea>

    <select name="priority" required>
      <option value="low">Düşük</option>
      <option value="medium" selected>Orta</option>
      <option value="high">Yüksek</option>
    </select>

    <input type="date" name="due_date">

    <?php if ($role === "admin" || $role === "manager"): ?>
      <select name="assigned_to" required>
        <?php $u2 = fetchUsers($conn); while($u = $u2->fetch_assoc()): ?>
          <option value="<?= (int)$u["id"] ?>"><?= htmlspecialchars($u["name"]) ?></option>
        <?php endwhile; ?>
      </select>
    <?php endif; ?>

    <div class="dlg-actions">
      <button type="submit" style="background:#22c55e;color:#fff;">Ekle</button>
      <button type="button" onclick="document.getElementById('createTaskDlg').close()" style="background:#ef4444;color:#fff;">Kapat</button>
    </div>
  </form>
</dialog>

<!-- ======================
     EDIT DIALOG
====================== -->
<dialog id="editTaskDlg">
  <form method="POST" action="tasks.php">
    <h3>Görevi Düzenle</h3>

    <input type="hidden" name="do_update" value="1">
    <input type="hidden" name="id" id="edit_id">

    <input type="text" name="title" id="edit_title" placeholder="Başlık" required>
    <textarea name="description" id="edit_description" placeholder="Açıklama"></textarea>

    <select name="priority" id="edit_priority" required>
      <option value="low">Düşük</option>
      <option value="medium">Orta</option>
      <option value="high">Yüksek</option>
    </select>

    <input type="date" name="due_date" id="edit_due_date">

    <?php if ($role === "admin" || $role === "manager"): ?>
      <select name="assigned_to" id="edit_assigned_to" required>
        <?php $u3 = fetchUsers($conn); while($u = $u3->fetch_assoc()): ?>
          <option value="<?= (int)$u["id"] ?>"><?= htmlspecialchars($u["name"]) ?></option>
        <?php endwhile; ?>
      </select>
    <?php endif; ?>

    <div class="dlg-actions">
      <button type="submit" style="background:#0ea5e9;color:#fff;">Kaydet</button>
      <button type="button" onclick="document.getElementById('editTaskDlg').close()" style="background:#ef4444;color:#fff;">Kapat</button>
    </div>
  </form>
</dialog>

<script>
function openEditFromBtn(btn){
  const id = btn.dataset.id || "";
  const title = btn.dataset.title || "";
  const description = btn.dataset.description || "";
  const priority = btn.dataset.priority || "medium";
  const duedate = btn.dataset.duedate || "";
  const assigned = btn.dataset.assigned || "";

  document.getElementById("edit_id").value = id;
  document.getElementById("edit_title").value = title;
  document.getElementById("edit_description").value = description;
  document.getElementById("edit_priority").value = priority;
  document.getElementById("edit_due_date").value = duedate;

  const sel = document.getElementById("edit_assigned_to");
  if (sel && assigned) sel.value = assigned;

  document.getElementById("editTaskDlg").showModal();
}
</script>

</body>
</html>