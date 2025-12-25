<?php
class Task {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function create($title, $description, $priority, $due_date, $assigned_to, $created_by) {
        $assigned_to = (int)$assigned_to;
        $created_by  = (int)$created_by;

        $due_date = trim((string)$due_date);
        if ($due_date === "") { $due_date = null; }

        $stmt = $this->conn->prepare("
            INSERT INTO tasks (title, description, priority, status, due_date, assigned_to, created_by, created_at)
            VALUES (?, ?, ?, 'pending', ?, ?, ?, NOW())
        ");
        if (!$stmt) return false;

        $stmt->bind_param("ssssii", $title, $description, $priority, $due_date, $assigned_to, $created_by);
        return $stmt->execute();
    }

    public function list($role, $user_id, $status = "", $q = "") {
        $user_id = (int)$user_id;

        $where = " WHERE 1=1 ";
        $params = [];
        $types = "";

        // Çalışan ise sadece kendi görevleri
        if ($role === "user") {
            $where .= " AND t.assigned_to=? ";
            $types .= "i";
            $params[] = $user_id;
        }

        if ($status !== "") {
            $where .= " AND t.status=? ";
            $types .= "s";
            $params[] = $status;
        }

        if ($q !== "") {
            $where .= " AND (t.title LIKE ? OR t.description LIKE ?) ";
            $types .= "ss";
            $like = "%" . $q . "%";
            $params[] = $like;
            $params[] = $like;
        }

        $sql = "
            SELECT t.*, u.name AS assigned_name
            FROM tasks t
            LEFT JOIN users u ON t.assigned_to = u.id
            $where
            ORDER BY
              FIELD(t.priority,'high','medium','low') ASC,
              COALESCE(t.due_date,'9999-12-31') ASC,
              t.id DESC
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        if ($types !== "") {
            // güvenli bind (PHP 8 uyumlu)
            $bind_names[] = $types;
            for ($i = 0; $i < count($params); $i++) {
                $bind_name = 'bind' . $i;
                $$bind_name = $params[$i];
                $bind_names[] = &$$bind_name;
            }
            call_user_func_array([$stmt, 'bind_param'], $bind_names);
        }

        $stmt->execute();
        return $stmt->get_result();
    }

    public function update($id, $title, $description, $priority, $due_date, $assigned_to) {
        $id = (int)$id;
        $assigned_to = (int)$assigned_to;

        $due_date = trim((string)$due_date);
        if ($due_date === "") { $due_date = null; }

        $stmt = $this->conn->prepare("
            UPDATE tasks
            SET title=?, description=?, priority=?, due_date=?, assigned_to=?
            WHERE id=?
        ");
        if (!$stmt) return false;

        $stmt->bind_param("ssssii", $title, $description, $priority, $due_date, $assigned_to, $id);
        return $stmt->execute();
    }

    public function markDone($id) {
        $id = (int)$id;
        $stmt = $this->conn->prepare("UPDATE tasks SET status='done', completed_at=NOW() WHERE id=?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function markPending($id) {
        $id = (int)$id;
        $stmt = $this->conn->prepare("UPDATE tasks SET status='pending', completed_at=NULL WHERE id=?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $id = (int)$id;
        $stmt = $this->conn->prepare("DELETE FROM tasks WHERE id=?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}