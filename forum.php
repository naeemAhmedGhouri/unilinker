
<?php
class Forum {
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }
    public function getPosts($limit = 10, $offset = 0) {
        $stmt = $this->conn->prepare("SELECT * FROM posts ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}