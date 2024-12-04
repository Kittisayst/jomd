<?php
class FileDatabase extends Database
{
    protected function initTables(): void
    {
        // ສ້າງຕາຕະລາງຫຼັກສຳລັບເກັບໄຟລ໌ markdown
        $this->execute("
            CREATE TABLE IF NOT EXISTS md_files (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                file_name TEXT NOT NULL,
                content TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ", []);

        // ສ້າງ index ສຳລັບການຊອກຫາ
        $this->execute("
            CREATE INDEX IF NOT EXISTS idx_md_files_filename 
            ON md_files(file_name)
        ", []);

        // ສ້າງ trigger ສຳລັບອັບເດດເວລາ
        $this->execute("
            CREATE TRIGGER IF NOT EXISTS update_md_files_timestamp 
            AFTER UPDATE ON md_files
            BEGIN
                UPDATE md_files 
                SET updated_at = CURRENT_TIMESTAMP 
                WHERE id = NEW.id;
            END
        ", []);
    }

    protected function getVersion(): int
    {
        return 1;
    }

    protected function migrate(int $fromVersion): void
    {
        if ($fromVersion < 1) {
            $this->execute("
                ALTER TABLE md_files 
                ADD COLUMN folder TEXT DEFAULT '/'
            ", []);
        }
    }

    // ບັນທຶກໄຟລ໌
    public function saveFile(array $data): ?int
    {
        try {
            $this->beginTransaction();

            if (isset($data['id'])) {
                // ອັບເດດ
                $result = $this->updateFileRecord($data);
                $this->commit();
                return $result ? (int)$data['id'] : null;
            }

            // ສ້າງໃໝ່
            $id = $this->createFileRecord($data);
            $this->commit();
            return $id;
        } catch (Exception $e) {
            $this->rollback();
            $this->handleError($e, "ບໍ່ສາມາດບັນທຶກໄຟລ໌ໄດ້". $e->getMessage());
            return null;
        }
    }

    private function createFileRecord(array $data): int
    {
        $stmt = $this->prepare("
            INSERT INTO md_files (
                title, 
                file_name,
                content
            ) VALUES (
                :title,
                :file_name,
                :content
            )
        ");

        $this->bindFileParams($stmt, $data);

        if (!$stmt->execute()) {
            throw new DatabaseException("ບໍ່ສາມາດສ້າງບັນທຶກໄຟລ໌ໄດ້");
        }

        return $this->lastInsertId();
    }

    private function updateFileRecord(array $data): bool
    {
        $stmt = $this->prepare("
            UPDATE md_files 
            SET title = :title,
                file_name = :file_name,
                content = :content
            WHERE id = :id
        ");

        $this->bindFileParams($stmt, $data);
        $stmt->bindValue(':id', $data['id'], SQLITE3_INTEGER);

        return $stmt->execute() !== false;
    }

    private function bindFileParams(SQLite3Stmt $stmt, array $data): void
    {
        $stmt->bindValue(':title', $data['title'], SQLITE3_TEXT);
        $stmt->bindValue(':file_name', $data['file_name'], SQLITE3_TEXT);
        $stmt->bindValue(':content', $data['content'], SQLITE3_TEXT);
    }

    // ດຶງຂໍ້ມູນໄຟລ໌
    public function getFile(int $id): ?array
    {
        try {
            return $this->queryOne(
                "SELECT * FROM md_files WHERE id = :id",
                ['id' => $id]
            );
        } catch (Exception $e) {
            $this->handleError($e, "ບໍ່ສາມາດດຶງຂໍ້ມູນໄຟລ໌ໄດ້");
            return null;
        }
    }

    // ດຶງໄຟລ໌ຕາມຊື່
    public function getFileByName(string $fileName): ?array
    {
        try {
            return $this->queryOne(
                "SELECT * FROM md_files WHERE file_name = :file_name",
                ['file_name' => $fileName]
            );
        } catch (Exception $e) {
            $this->handleError($e, "ບໍ່ສາມາດດຶງຂໍ້ມູນໄຟລ໌ໄດ້". $e->getMessage());
            return null;
        }
    }

    // ດຶງທຸກໄຟລ໌
    public function getAllFiles(int $limit = 100, int $offset = 0): array
    {
        try {
            return $this->query(
                "SELECT * FROM md_files 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset",
                ['limit' => $limit, 'offset' => $offset]
            );
        } catch (Exception $e) {
            $this->handleError($e, "ບໍ່ສາມາດດຶງຂໍ້ມູນໄຟລ໌ທັງໝົດໄດ້". $e->getMessage());
            return [];
        }
    }

    // ຄົ້ນຫາໄຟລ໌
    public function searchFiles(array $criteria, int $limit = 50, int $offset = 0): array
    {
        try {
            $where = [];
            $params = [];

            if (!empty($criteria['title'])) {
                $where[] = 'title LIKE :title';
                $params['title'] = '%' . $criteria['title'] . '%';
            }

            if (!empty($criteria['content'])) {
                $where[] = 'content LIKE :content';
                $params['content'] = '%' . $criteria['content'] . '%';
            }

            if (!empty($criteria['file_name'])) {
                $where[] = 'file_name LIKE :file_name';
                $params['file_name'] = '%' . $criteria['file_name'] . '%';
            }

            $params['limit'] = $limit;
            $params['offset'] = $offset;

            $sql = 'SELECT * FROM md_files WHERE ' .
                implode(' AND ', $where) .
                ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';

            return $this->query($sql, $params);
        } catch (Exception $e) {
            $this->handleError($e, "ບໍ່ສາມາດຄົ້ນຫາໄຟລ໌ໄດ້");
            return [];
        }
    }

    // ລຶບໄຟລ໌
    public function deleteFile(int $id): bool
    {
        try {
            return $this->execute(
                "DELETE FROM md_files WHERE id = :id",
                ['id' => $id]
            );
        } catch (Exception $e) {
            $this->handleError($e, "ບໍ່ສາມາດລຶບໄຟລ໌ໄດ້");
            return false;
        }
    }

    // ກູ້ຄືນໄຟລ໌ທີ່ຖືກລຶບ
    public function restoreFile(int $id): bool
    {
        try {
            return $this->execute(
                "UPDATE md_files 
                SET updated_at = CURRENT_TIMESTAMP 
                WHERE id = :id",
                ['id' => $id]
            );
        } catch (Exception $e) {
            $this->handleError($e, "ບໍ່ສາມາດກູ້ຄືນໄຟລ໌ໄດ້");
            return false;
        }
    }
}
