
<?php
// includes/db.php
$host = getenv('MYSQLHOST')     ?: 'localhost';
$user = getenv('MYSQLUSER')     ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$name = getenv('MYSQLDATABASE') ?: 'task_tracker_db';
$port = getenv('MYSQLPORT')     ?: 3306;
 
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
 
// Compatibility wrapper so all existing $conn->prepare() calls still work
$conn = new class($pdo) {
    public PDO $pdo;
 
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }
 
    public function prepare(string $sql): PDOStatementWrapper {
        return new PDOStatementWrapper($this->pdo->prepare($sql));
    }
 
    public function query(string $sql): PDOStatementWrapper {
        $stmt = $this->pdo->query($sql);
        return new PDOStatementWrapper($stmt);
    }
 
    public function real_escape_string(string $s): string {
        return addslashes($s);
    }
};
 
class PDOStatementWrapper {
    private PDOStatement $stmt;
    private array $results = [];
    private int $ptr = 0;
    public int $num_rows = 0;
    public int $affected_rows = 0;
 
    public function __construct(PDOStatement $stmt) { $this->stmt = $stmt; }
 
    public function bind_param(string $types, &...$vars): void {
        foreach ($vars as $i => &$var) {
            $this->stmt->bindParam($i + 1, $var);
        }
    }
 
    public function execute(): bool {
        $result = $this->stmt->execute();
        $this->results = $this->stmt->fetchAll() ?: [];
        $this->num_rows = count($this->results);
        $this->affected_rows = $this->stmt->rowCount();
        $this->ptr = 0;
        return $result;
    }
 
    public function get_result(): static {
        return $this;
    }
 
    public function fetch_assoc(): ?array {
        if ($this->ptr < count($this->results)) {
            return $this->results[$this->ptr++];
        }
        return null;
    }
 
    public function store_result(): void {}
 
    public function num_rows(): int { return $this->num_rows; }
}
?>
