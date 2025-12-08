<?php
    class Database {
        public $conn;

        public function __construct() {
            $this->conn = new mysqli("localhost", "root", "", "managex");

            if ($this->conn->connect_error) {
                die("Bağlantı hatası: " . $this->conn->connect_error);
            }
        }
    }
?>