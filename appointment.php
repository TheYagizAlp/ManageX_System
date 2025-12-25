<?php
session_start();
include_once "classes/Database.php";

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION["user"];

// Misafir (user) + Çalışan (manager) randevu alabilir
if ($user["role"] !== "user" && $user["role"] !== "manager") {
    header("Location: dashboard.php");
    exit;
}


$db = new Database();
$conn = $db->conn;

/* --- Ayarlar --- */
$DURATION_MINUTES = 45;     // randevu süresi
$DAYS_AHEAD = 7;            // kaç gün ileri
$START_HOUR = 8;            // çalışma saati başlangıç
$END_HOUR = 18;             // çalışma saati bitiş
$STEP_SECONDS = 900;        // 15 dk adım

/* --- Yardımcı: minLocal'ı step'e göre yukarı yuvarla --- */
function ceil_to_step($ts, $step) {
    return (int)(ceil($ts / $step) * $step);
}

/* --- Dolu randevuları çek (önümüzdeki X gün) --- */
$now = date("Y-m-d H:i:00");
$endDate = date("Y-m-d 23:59:59", strtotime("+$DAYS_AHEAD days"));

$stmt = $conn->prepare("SELECT datetime FROM appointments WHERE datetime BETWEEN ? AND ? ORDER BY datetime ASC");
$stmt->bind_param("ss", $now, $endDate);
$stmt->execute();
$resBusy = $stmt->get_result();

$busyTimes = [];
while ($b = $resBusy->fetch_assoc()) {
    $busyTimes[] = $b["datetime"];
}

/* --- Randevu ekleme --- */
if (isset($_POST["create"])) {
    $raw = trim($_POST["datetime"] ?? "");
    $user_id = (int)$user["id"];

    // "YYYY-MM-DDTHH:MM" -> "YYYY-MM-DD HH:MM:SS"
    $datetime = str_replace("T", " ", $raw) . ":00";
    $startTs = strtotime($datetime);

    if (!$startTs) {
        echo "<script>alert('Tarih formatı hatalı!');</script>";
    } else {
        // 1) Geçmişe randevu yok
        if ($startTs < time()) {
            echo "<script>alert('Geçmiş bir saate randevu alınamaz!');</script>";
        } else {
            // 2) sadece ileri X gün
            $maxTs = strtotime("+$DAYS_AHEAD days 23:59:59");
            if ($startTs > $maxTs) {
                echo "<script>alert('Sadece önümüzdeki $DAYS_AHEAD gün için randevu alınabilir!');</script>";
            } else {
                // 3) çalışma saatleri aralığı
                $startOfDay = strtotime(date("Y-m-d 00:00:00", $startTs));
                $workStart = $startOfDay + ($START_HOUR * 3600);     // 09:00
                $workEnd   = $startOfDay + ($END_HOUR * 3600);       // 17:00
                $endTs = $startTs + ($DURATION_MINUTES * 60);

                // başlangıç çalışma saatinden önce olamaz
                if ($startTs < $workStart) {
                    echo "<script>alert('Çalışma saatleri $START_HOUR:00 - $END_HOUR:00 arasıdır!');</script>";
                }
                // bitiş çalışma saatinden sonra olamaz (16:15 + 60 dk gibi)
                else if ($endTs > $workEnd) {
                    echo "<script>alert('Randevu süresi $DURATION_MINUTES dk. Bu saatte başlarsa mesai dışına taşar!');</script>";
                } else {
                    // 4) Overlap kontrolü (aynı gün için)
                    $dayStart = date("Y-m-d 00:00:00", $startTs);
                    $dayEnd   = date("Y-m-d 23:59:59", $startTs);

                    $q = $conn->prepare("SELECT datetime FROM appointments WHERE datetime BETWEEN ? AND ?");
                    $q->bind_param("ss", $dayStart, $dayEnd);
                    $q->execute();
                    $res = $q->get_result();

                    $conflict = false;
                    while ($row = $res->fetch_assoc()) {
                        $dbStartTs = strtotime($row["datetime"]);
                        $dbEndTs = $dbStartTs + ($DURATION_MINUTES * 60);

                        // overlap: start < otherEnd AND end > otherStart
                        if ($startTs < $dbEndTs && $endTs > $dbStartTs) {
                            $conflict = true;
                            break;
                        }
                    }

                    if ($conflict) {
                        echo "<script>alert('Bu saat dolu (1 saatlik aralık çakışıyor). Başka bir saat seç!');</script>";
                    } else {
                        $ins = $conn->prepare("INSERT INTO appointments (user_id, datetime, status, created_at) VALUES (?, ?, 'pending', NOW())");
                        $ins->bind_param("is", $user_id, $datetime);
                        $ins->execute();
                        echo "<script>alert('Randevun başarıyla oluşturuldu!'); window.location='appointment.php';</script>";
                        exit;
                    }
                }
            }
        }
    }
}

/* --- Kullanıcının randevuları --- */
$appointments = $conn->query("SELECT * FROM appointments WHERE user_id={$user['id']} ORDER BY datetime DESC");

/* --- datetime-local min/max (15 dk step ile uyumlu) --- */
$minTs = ceil_to_step(time(), $STEP_SECONDS); // 15 dk adımına yuvarla
$minLocal = date("Y-m-d\TH:i", $minTs);

// max'ı 7 gün ileri gün sonuna kadar veriyoruz (asıl mesai kontrolünü backend yapıyor)
$maxLocal = date("Y-m-d\TH:i", strtotime("+$DAYS_AHEAD days 23:45"));
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
  max-width: 900px;
  margin: auto;
}
h2 {
  text-align: center;
  color: #0f172a;
  margin-bottom: 6px;
}
.sub {
  text-align:center;
  color:#374151;
  margin-top: 0;
  margin-bottom: 20px;
}
.top-actions{
  display:flex;
  justify-content:space-between;
  gap:10px;
  flex-wrap:wrap;
  margin-bottom: 10px;
}
.btn {
  border:none;
  padding: 10px 14px;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  transition: .2s;
}
.btn:hover{ filter:brightness(.95); }
.back { background:#0ea5e9; color:#fff; }
.create { background:#22c55e; color:#fff; }
.logout { background:#ef4444; color:#fff; }

.form-row{
  display:flex;
  gap:10px;
  justify-content:center;
  flex-wrap:wrap;
  align-items:center;
  margin-top: 14px;
}
input[type="datetime-local"]{
  padding:10px 12px;
  border-radius:10px;
  border:1px solid #cbd5e1;
  min-width: 280px;
  box-sizing:border-box;
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
  font-weight: 700;
  border-radius: 999px;
  padding: 5px 10px;
  font-size: 12px;
  display:inline-block;
}
.status.pending { background: #fbbf24; color: #78350f; }
.status.approved { background: #22c55e; color: white; }
.status.rejected { background: #ef4444; color: white; }

.section-title{
  margin-top: 26px;
  margin-bottom: 10px;
  color:#0f172a;
}

.busy-list{
  background:#f1f5f9;
  border:1px solid #e5e7eb;
  border-radius: 12px;
  padding: 12px 14px;
  color:#334155;
}
.busy-list ul{ margin:0; padding-left: 18px; }
.busy-list li{ margin: 6px 0; font-size: 14px; }

.note{
  margin-top:10px;
  text-align:center;
  color:#64748b;
  font-size: 13px;
}
</style>
</head>
<body>

<div class="container">
  <h2>📅 Randevularım</h2>
  <p class="sub">Hoş geldin <strong><?= htmlspecialchars($user["name"]) ?></strong>! Takvimden saat seçip yönetici ile görüşmek için randevu oluşturabilirsin.</p>

  <div class="top-actions">
    <button class="btn back" onclick="window.location='dashboard.php'">🡐 Panele Dön</button>
    <button class="btn logout" onclick="window.location='logout.php'">🚪 Çıkış Yap</button>
  </div>

  <form method="POST">
    <div class="form-row">
      <input
        type="datetime-local"
        name="datetime"
        required
        min="<?= htmlspecialchars($minLocal) ?>"
        max="<?= htmlspecialchars($maxLocal) ?>"
        step="<?= (int)$STEP_SECONDS ?>"
      >
      <button class="btn create" type="submit" name="create">Randevu Oluştur</button>
    </div>
    <div class="note">
      Not: Randevu süresi <b><?= $DURATION_MINUTES ?></b> dk. Çalışma saatleri <b><?= $START_HOUR ?>:00 - <?= $END_HOUR ?>:00</b>. (15 dk aralıkla seçim)
    </div>
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
      <td><?= (int)$r["id"] ?></td>
      <td><?= date("d.m.Y H:i", strtotime($r["datetime"])) ?></td>
      <td>
        <span class="status <?= htmlspecialchars($r["status"]) ?>">
          <?= $r["status"] === "approved" ? "✅ Onaylandı" :
             ($r["status"] === "rejected" ? "❌ Reddedildi" : "⏳ Bekliyor") ?>
        </span>
      </td>
      <td><?= date("d.m.Y H:i", strtotime($r["created_at"])) ?></td>
    </tr>
    <?php endwhile; ?>
  </table>

  <h3 class="section-title">⛔ Dolu Saatler (Yaklaşan)</h3>
  <div class="busy-list">
    <?php if (count($busyTimes) === 0): ?>
      Şu an dolu saat yok.
    <?php else: ?>
      <ul>
        <?php foreach($busyTimes as $bt): ?>
          <li><?= date("d.m.Y H:i", strtotime($bt)) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

</body>
</html>