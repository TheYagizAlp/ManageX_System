<?php
session_start();

// Tüm kullanıcılar giriş yapmış olmalı
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION["user"];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Şirket Konumu - ManageX</title>
<style>
body{
  font-family:'Segoe UI',sans-serif;
  background:#e5e7eb; /* 🔥 saf beyaz yerine açık gri */
  margin:0;
  padding:30px;
}

.container{
  max-width:1000px;
  margin:auto;
  background:#f9fafb; /* 🔥 mermer beyaz yerine soft beyaz */
  border-radius:18px;
  box-shadow:0 10px 30px rgba(0,0,0,.08);
  padding:26px;
}

.top{
  display:flex;
  justify-content:space-between;
  align-items:center;
  flex-wrap:wrap;
  gap:12px;
  margin-bottom:16px;
}

h2{
  margin:0;
  color:#0f172a;
}

.sub{
  margin:6px 0 0 0;
  color:#475569;
  font-size:14px;
}

.btns{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
}

.btn{
  border:none;
  border-radius:12px;
  padding:10px 16px;
  font-weight:700;
  cursor:pointer;
  transition:.2s;
}

.back{
  background:#0ea5e9;
  color:#fff;
}
.back:hover{ filter:brightness(.95); }

.route{
  background:#22c55e;
  color:#fff;
}
.route:hover{ filter:brightness(.95); }

.logout{
  background:#ef4444;
  color:#fff;
}
.logout:hover{ filter:brightness(.95); }

.mapwrap{
  margin-top:16px;
  border-radius:18px;
  overflow:hidden;
  border:1px solid #d1d5db;
  background:#e5e7eb; /* iframe yüklenene kadar soft zemin */
}

iframe{
  width:100%;
  height:520px;
  border:0;
  display:block;
}

.note{
  margin-top:14px;
  color:#64748b;
  font-size:13px;
  background:#f1f5f9; /* küçük info kart hissi */
  padding:10px 14px;
  border-radius:12px;
}
</style>

</head>
<body>

<div class="container">
  <div class="top">
    <div>
      <h2>📍 Şirket Konumu</h2>
      <p class="sub">Hoş geldin <b><?= htmlspecialchars($user["name"]) ?></b>! Haritadan ofisin konumunu görebilir ve yol tarifi alabilirsin.</p>
    </div>

    <div class="btns">
      <button class="btn back" onclick="window.location='dashboard.php'">🡐 Panele Dön</button>

      <!-- Yol Tarifi: Google Maps dışarıda açılır -->
      <button class="btn route" onclick="window.open('https://www.google.com/maps/dir/?api=1&destination=Avrasya+%C3%9Cniversitesi+Trabzon','_blank')">
        🧭 Yol Tarifi Al
      </button>

      <button class="btn logout" onclick="window.location='logout.php'">🚪 Çıkış</button>
    </div>
  </div>

  <div class="mapwrap">
    <!-- Avrasya Üniversitesi pinli embed -->
    <iframe
        src="https://www.google.com/maps?q=Avrasya+Üniversitesi+Trabzon&output=embed&z=16"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
    </iframe>
  </div>

  <div class="note">
    Not: “Yol Tarifi Al” butonu Google Maps’i yeni sekmede açar (kullanıcının kendi konumundan rota verir).
  </div>
</div>

</body>
</html>