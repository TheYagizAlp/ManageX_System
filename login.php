<?php
    session_start();
    include_once "classes/Database.php";
    include_once "classes/User.php";

    $db = new Database();
    $conn = $db->conn;
    $user = new User($conn);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST["email"];
        $password = $_POST["password"];
        $role = $_POST["role"];

        $loginUser = $user->login($email, $password);

        if ($loginUser && $loginUser["role"] == $role) {
            $_SESSION["user"] = $loginUser;
            header("Location: dashboard.php");
        } else {
            echo "<script>alert('Bilgiler hatalı veya rol yanlış!');</script>";
        }
    }
?>