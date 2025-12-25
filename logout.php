<?php
    session_start();

    // Tüm session verilerini temizle
    $_SESSION = [];

    // Session'ı tamamen yok et
    session_destroy();

    // Giriş sayfasına yönlendir
    header("Location: index.php");
    exit;
?>
