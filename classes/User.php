<?php
    include_once "Database.php";

    class User {

        private $conn;

        public function __construct($conn) {
            $this->conn = $conn;
        }

        // Kullanıcı kaydı
        public function register($name, $email, $password, $role = 'user') {
            // Aynı email var mı kontrol et
            $check = $this->conn->query("SELECT * FROM users WHERE email='$email'");
            if ($check->num_rows > 0) {
                return "Bu e-posta zaten kayıtlı!";
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashed', '$role')";
            
            if ($this->conn->query($query)) {
                return "Kayıt başarılı!";
            } else {
                return "Kayıt sırasında hata: " . $this->conn->error;
            }
        }

        // Giriş işlemi
        public function login($email, $password) {
            $query = $this->conn->query("SELECT * FROM users WHERE email='$email'");

            if ($query->num_rows == 1) {
                $user = $query->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    return $user; // başarılı
                } else {
                    return false; // şifre yanlış
                }
            } else {
                return false; // kullanıcı yok
            }
        }
    }
?>
