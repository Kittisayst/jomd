<?php

// Database.php
abstract class Database
{
    protected SQLite3 $db;
    private static array $instances = [];
    private array $preparedStatements = [];
    private array $queryLog = [];
    private float $queryStartTime = 0;

    protected function __construct()
    {
        try {
            if (!file_exists(dirname(DB_PATH))) {
                mkdir(dirname(DB_PATH), 0755, true);
            }

            $this->db = new SQLite3(DB_PATH);
            $this->db->enableExceptions(true);
            $this->initializeDatabase();
            $this->initTables();
            $this->checkMigration();
        } catch (Exception $e) {
            $this->handleError($e, "ເກີດຂໍ້ຜິດພາດໃນການເຊື່ອມຕໍ່ຖານຂໍ້ມູນ");
        }
    }

    public static function getInstance()
    {
        $class = static::class;
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class();
        }
        return self::$instances[$class];
    }

    protected function initializeDatabase(): void
    {
        $this->db->exec('PRAGMA foreign_keys = ON');
        $this->db->exec('PRAGMA journal_mode = WAL');
        $this->db->exec('PRAGMA synchronous = NORMAL');
        $this->db->exec('PRAGMA cache_size = -2000'); // 2MB cache
        $this->db->exec('PRAGMA temp_store = MEMORY');
    }

    protected function query(string $sql, array $params = []): array
    {
        $this->startQueryTimer();
        try {
            $stmt = $this->prepare($sql);
            $this->bindParameters($stmt, $params);
            $result = $stmt->execute();  // execute statement ສົ່ງຄືນ SQLite3Result
            if ($result === false) {
                throw new DatabaseException("Query execution failed", $sql, $params);
            }
            return $this->fetchAll($result);
        } catch (Exception $e) {
            throw new DatabaseException(
                "Query error: " . $e->getMessage(),
                $sql,
                $params,
                $e->getCode()
            );
        } finally {
            $this->logQuery($sql, $params);
        }
    }

    protected function execute(string $sql, array $params = []): bool
    {
        $this->startQueryTimer();
        try {
            $stmt = $this->prepare($sql);
            $this->bindParameters($stmt, $params);
            return $stmt->execute() !== false;
        } catch (Exception $e) {
            throw new DatabaseException(
                "Execute error: " . $e->getMessage(),
                $sql,
                $params,
                $e->getCode()
            );
        } finally {
            $this->logQuery($sql, $params);
        }
    }

    protected function executeUpdate(string $sql, array $params = []): bool
    {
        $this->startQueryTimer();
        try {
            $stmt = $this->prepare($sql);
            $this->bindParameters($stmt, $params);
            $result = $stmt->execute();
            return $result !== false;
        } catch (Exception $e) {
            throw new DatabaseException(
                "Execute error: " . $e->getMessage(),
                $sql,
                $params,
                $e->getCode()
            );
        } finally {
            $this->logQuery($sql, $params);
        }
    }

    protected function queryOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params);
        return $result ? $result[0] : null;
    }

    protected function queryValue(string $sql, array $params = []): mixed
    {
        $result = $this->queryOne($sql, $params);
        return $result ? reset($result) : null;
    }

    protected function lastInsertId(): int
    {
        return $this->db->lastInsertRowID();
    }

    protected function prepare(string $sql): SQLite3Stmt
    {
        $key = md5($sql);

        if (!isset($this->preparedStatements[$key])) {
            $stmt = $this->db->prepare($sql);
            if ($stmt === false) {
                throw new DatabaseException(
                    "Prepare error: " . $this->db->lastErrorMsg(),
                    $sql
                );
            }
            $this->preparedStatements[$key] = $stmt;
        }

        return $this->preparedStatements[$key];
    }

    private function bindParameters(SQLite3Stmt $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            $param = is_string($key) ? ":$key" : $key + 1;

            switch (true) {
                case is_int($value):
                    $type = SQLITE3_INTEGER;
                    break;
                case is_float($value):
                    $type = SQLITE3_FLOAT;
                    break;
                case is_null($value):
                    $type = SQLITE3_NULL;
                    break;
                case $value instanceof DateTime:
                    $type = SQLITE3_TEXT;
                    $value = $value->format('Y-m-d H:i:s');
                    break;
                case is_bool($value):
                    $type = SQLITE3_INTEGER;
                    $value = $value ? 1 : 0;
                    break;
                case is_array($value):
                case is_object($value):
                    $type = SQLITE3_TEXT;
                    $value = json_encode($value);
                    break;
                default:
                    $type = SQLITE3_TEXT;
                    $value = $this->sanitize($value);
            }

            $stmt->bindValue($param, $value, $type);
        }
    }

    protected function fetchAll(SQLite3Result $result): array
    {
        $rows = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }

    protected function checkMigration(): void
    {
        // ສ້າງຕາຕະລາງ migrations ຖ້າຍັງບໍ່ມີ
        $this->execute("
        CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            version INTEGER NOT NULL,
            applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ", []);

        // ເອົາເວີຊັນປັດຈຸບັນ
        $currentVersion = $this->queryValue(
            "SELECT version FROM migrations ORDER BY version DESC LIMIT 1",
            []
        ) ?? 0;

        $targetVersion = $this->getVersion();

        // ຖ້າຕ້ອງອັບເດດ
        if ($currentVersion < $targetVersion) {
            $this->beginTransaction();

            try {
                // ເອີ້ນໃຊ້ migrate() ຂອງ child class
                $this->migrate($currentVersion);

                // ບັນທຶກເວີຊັນໃໝ່
                $this->execute(
                    "INSERT INTO migrations (version) VALUES (:version)",
                    ['version' => $targetVersion]
                );

                $this->commit();
            } catch (Exception $e) {
                $this->rollback();
                throw new DatabaseException(
                    "Migration failed: " . $e->getMessage(),
                    "",
                    [],
                    0,
                    $e
                );
            }
        }
    }

    public function beginTransaction(): void
    {
        $this->db->exec('BEGIN TRANSACTION');
    }

    public function commit(): void
    {
        $this->db->exec('COMMIT');
    }

    public function rollback(): void
    {
        $this->db->exec('ROLLBACK');
    }

    public function backup(): string
    {
        if (!file_exists(DB_BACKUP_PATH)) {
            mkdir(DB_BACKUP_PATH, 0755, true);
        }

        $backupFile = DB_BACKUP_PATH . date('Y-m-d_H-i-s') . '_backup.sqlite';
        copy(DB_PATH, $backupFile);

        // Optimize after backup
        $this->db->exec('VACUUM');

        return $backupFile;
    }

    protected function sanitize(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    protected function handleError(Exception $e, string $message): void
    {
        error_log(sprintf(
            "[%s] %s: %s\nStack trace:\n%s",
            date('Y-m-d H:i:s'),
            $message,
            $e->getMessage(),
            $e->getTraceAsString()
        ));
        throw new DatabaseException($message, '', [], 0, $e);
    }

    private function startQueryTimer(): void
    {
        $this->queryStartTime = microtime(true);
    }

    private function logQuery(string $sql, array $params): void
    {
        $duration = microtime(true) - $this->queryStartTime;
        $this->queryLog[] = [
            'sql' => $sql,
            'params' => $params,
            'duration' => $duration,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Keep log file size manageable
        if (count($this->queryLog) > 1000) {
            array_shift($this->queryLog);
        }
    }

    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    public function vacuum(): void
    {
        $this->db->exec('VACUUM');
    }

    public function optimize(): void
    {
        $this->db->exec('PRAGMA optimize');
    }

    public function getDatabaseSize(): int
    {
        return filesize(DB_PATH);
    }

    public function isConnected(): bool
    {
        return isset($this->db) && $this->db instanceof SQLite3;
    }

    abstract protected function initTables(): void;
    abstract protected function getVersion(): int;
    abstract protected function migrate(int $fromVersion): void;

    public function __destruct()
    {
        foreach ($this->preparedStatements as $stmt) {
            $stmt->close();
        }
        if (isset($this->db)) {
            $this->db->close();
        }
    }
}
