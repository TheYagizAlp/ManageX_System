<?php
    include_once "Database.php";

    class Employee {
        private $conn;

        public function __construct($conn){
            $this->conn = $conn;
        }

        public function getAll(){
            $sql = "SELECT * FROM employees ORDER BY id DESC";
            return $this->conn->query($sql);
        }

        public function getById($id){
            $id = (int)$id;
            $sql = "SELECT * FROM employees WHERE id=$id";
            return $this->conn->query($sql)->fetch_assoc();
        }

        public function add($name, $position, $department, $email, $phone, $photoName){
            $stmt = $this->conn->prepare("INSERT INTO employees (name, position, department, email, phone, photo) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("ssssss", $name, $position, $department, $email, $phone, $photoName);
            return $stmt->execute();
        }

        public function update($id, $name, $position, $department, $email, $phone, $photoName = null){
            $id = (int)$id;
            if($photoName){
                $stmt = $this->conn->prepare("UPDATE employees SET name=?, position=?, department=?, email=?, phone=?, photo=? WHERE id=?");
                $stmt->bind_param("ssssssi", $name, $position, $department, $email, $phone, $photoName, $id);
            } else {
                $stmt = $this->conn->prepare("UPDATE employees SET name=?, position=?, department=?, email=?, phone=? WHERE id=?");
                $stmt->bind_param("sssssi", $name, $position, $department, $email, $phone, $id);
            }
            return $stmt->execute();
        }

        public function delete($id){
            $id = (int)$id;
            $stmt = $this->conn->prepare("DELETE FROM employees WHERE id=?");
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        }

        public function create($data, $files) {
            $name = $this->conn->real_escape_string($data["name"]);
            $department = $this->conn->real_escape_string($data["department"]);
            $position = $this->conn->real_escape_string($data["position"]);
            $email = $this->conn->real_escape_string($data["email"]);
            $phone = $this->conn->real_escape_string($data["phone"]);

            $photo = "";
            if (!empty($files["photo"]["name"])) {
                $targetDir = "uploads/employees/";
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $photo = basename($files["photo"]["name"]);
                $targetFile = $targetDir . $photo;
                move_uploaded_file($files["photo"]["tmp_name"], $targetFile);
            }

            $sql = "INSERT INTO employees (name, department, position, email, phone, photo)
                    VALUES ('$name', '$department', '$position', '$email', '$phone', '$photo')";
            return $this->conn->query($sql);
        }
    }
?>