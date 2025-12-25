<?php
session_start();
include_once "classes/Database.php";
include_once "classes/User.php";

$db = new Database();
$conn = $db->conn;
$user = new User($conn);

// Zaten giriş yaptıysa direkt dashboard
if (isset($_SESSION["user"])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $loginUser = $user->login($email, $password);

    if ($loginUser) {
        $_SESSION["user"] = $loginUser;
        header("Location: dashboard.php");
        exit;
    } else {
        echo "<script>alert('E-posta veya şifre hatalı!');</script>";
    }
}
?>
