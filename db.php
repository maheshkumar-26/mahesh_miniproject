<?php
/**
 * Database Configuration & Connection
 * Employee Compensation Insights
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'employee_compensation_insights');
define('DB_CHARSET', 'utf8mb4');

define('BASE_URL', 'http://localhost/employee-compensation-insights/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

class Database {
    private static ?Database $instance = null;
    private mysqli $conn;

    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            error_log('DB Connection failed: ' . $this->conn->connect_error);
            die(json_encode(['error' => 'Database connection failed. Please check config/db.php']));
        }
        $this->conn->set_charset(DB_CHARSET);
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): mysqli {
        return $this->conn;
    }

    /**
     * Execute a prepared statement and return the result
     */
    public function query(string $sql, string $types = '', array $params = []): mysqli_result|bool {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log('Prepare failed: ' . $this->conn->error . ' | SQL: ' . $sql);
            return false;
        }
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result !== false ? $result : true;
    }

    /**
     * Fetch all rows
     */
    public function fetchAll(string $sql, string $types = '', array $params = []): array {
        $result = $this->query($sql, $types, $params);
        if ($result instanceof mysqli_result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    /**
     * Fetch single row
     */
    public function fetchOne(string $sql, string $types = '', array $params = []): ?array {
        $result = $this->query($sql, $types, $params);
        if ($result instanceof mysqli_result) {
            $row = $result->fetch_assoc();
            return $row ?: null;
        }
        return null;
    }

    /**
     * Execute INSERT/UPDATE/DELETE and return affected rows
     */
    public function execute(string $sql, string $types = '', array $params = []): int {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log('Prepare failed: ' . $this->conn->error);
            return 0;
        }
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }

    /**
     * Get last inserted ID
     */
    public function lastInsertId(): int {
        return (int)$this->conn->insert_id;
    }

    /**
     * Escape string (use prepared statements instead when possible)
     */
    public function escape(string $value): string {
        return $this->conn->real_escape_string($value);
    }
}

// Global helper
function db(): Database {
    return Database::getInstance();
}
