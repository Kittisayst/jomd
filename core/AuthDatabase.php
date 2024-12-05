<?php

class AuthDatabase extends Database
{
    protected function initTables(): void
    {
        // ຕາຕະລາງຜູ້ໃຊ້
        $this->execute("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            is_active INTEGER DEFAULT 1,
            last_login DATETIME,
            failed_attempts INTEGER DEFAULT 0,
            locked_until DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // ຕາຕະລາງ sessions
        $this->execute("CREATE TABLE IF NOT EXISTS sessions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token TEXT NOT NULL UNIQUE,
            ip_address TEXT,
            user_agent TEXT,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");

        // ຕາຕະລາງ password reset
        $this->execute("CREATE TABLE IF NOT EXISTS password_resets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token TEXT NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
    }

    protected function getVersion(): int
    {
        return 1;
    }

    protected function migrate(int $fromVersion): void
    {
        // ຈະເພີ່ມ migrations ໃນອະນາຄົດຕາມຄວາມຕ້ອງການ
    }

    public function createUser(string $username, string $email, string $password): bool
    {
        try {
            $this->beginTransaction();

            // ກວດສອບວ່າມີ username ຫຼື email ນີ້ແລ້ວຫຼືບໍ່
            $existingUser = $this->queryOne(
                "SELECT id FROM users WHERE username = :username OR email = :email",
                ['username' => $username, 'email' => $email]
            );

            if ($existingUser) {
                throw new DatabaseException("Username or email already exists");
            }

            // ສ້າງ password hash
            $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

            $success = $this->execute(
                "INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password)",
                [
                    'username' => $username,
                    'email' => $email,
                    'password' => $passwordHash
                ]
            );

            $this->commit();
            return $success;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function verifyLogin(string $username, string $password): ?array
    {
        $user = $this->queryOne(
            "SELECT id, username, password_hash, failed_attempts, locked_until, is_active 
             FROM users 
             WHERE username = :username OR email = :username",
            ['username' => $username]
        );

        if (!$user) {
            return null;
        }

        // ກວດສອບວ່າບັນຊີຖືກລ໋ອກຫຼືບໍ່
        if ($user['locked_until'] && new DateTime($user['locked_until']) > new DateTime()) {
            throw new DatabaseException("Account is temporarily locked");
        }

        if (!$user['is_active']) {
            throw new DatabaseException("Account is deactivated");
        }

        if (!password_verify($password, $user['password_hash'])) {
            // ເພີ່ມການນັບຄວາມພະຍາຍາມເຂົ້າສູ່ລະບົບທີ່ລົ້ມເຫຼວ
            $this->incrementFailedAttempts($user['id']);
            return null;
        }

        // ລ້າງການນັບຄວາມພະຍາຍາມທີ່ລົ້ມເຫຼວ
        $this->resetFailedAttempts($user['id']);

        // ອັບເດດເວລາເຂົ້າສູ່ລະບົບຄັ້ງຫຼ້າສຸດ
        $this->execute(
            "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id",
            ['id' => $user['id']]
        );

        return $user;
    }

    private function incrementFailedAttempts(int $userId): void
    {
        $attempts = $this->queryValue(
            "SELECT failed_attempts FROM users WHERE id = :id",
            ['id' => $userId]
        );

        $attempts++;
        $lockedUntil = null;

        // ລ໋ອກບັນຊີຫຼັງຈາກ 5 ຄັ້ງ
        if ($attempts >= 5) {
            $lockedUntil = (new DateTime())->modify('+30 minutes')->format('Y-m-d H:i:s');
        }

        $this->execute(
            "UPDATE users 
             SET failed_attempts = :attempts, locked_until = :locked_until 
             WHERE id = :id",
            [
                'attempts' => $attempts,
                'locked_until' => $lockedUntil,
                'id' => $userId
            ]
        );
    }

    private function resetFailedAttempts(int $userId): void
    {
        $this->execute(
            "UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = :id",
            ['id' => $userId]
        );
    }

    public function createSession(int $userId, string $ipAddress, string $userAgent): string
    {
        // ສ້າງ session token
        $token = bin2hex(random_bytes(32));

        // ຕັ້ງເວລາໝົດອາຍຸ (24 ຊົ່ວໂມງ)
        $expiresAt = (new DateTime())->modify('+24 hours')->format('Y-m-d H:i:s');

        $this->execute(
            "INSERT INTO sessions (user_id, token, ip_address, user_agent, expires_at)
             VALUES (:user_id, :token, :ip_address, :user_agent, :expires_at)",
            [
                'user_id' => $userId,
                'token' => $token,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'expires_at' => $expiresAt
            ]
        );

        return $token;
    }

    public function validateSession(string $token): ?array
    {
        // ລຶບ sessions ທີ່ໝົດອາຍຸ
        $this->execute(
            "DELETE FROM sessions WHERE expires_at < CURRENT_TIMESTAMP"
        );

        return $this->queryOne(
            "SELECT s.*, u.username, u.email 
             FROM sessions s 
             JOIN users u ON s.user_id = u.id 
             WHERE s.token = :token AND s.expires_at > CURRENT_TIMESTAMP",
            ['token' => $token]
        );
    }

    public function createPasswordReset(int $userId): string
    {
        // ລຶບ tokens ເກົ່າ
        $this->execute(
            "DELETE FROM password_resets WHERE user_id = :user_id",
            ['user_id' => $userId]
        );

        $token = bin2hex(random_bytes(32));
        $expiresAt = (new DateTime())->modify('+1 hour')->format('Y-m-d H:i:s');

        $this->execute(
            "INSERT INTO password_resets (user_id, token, expires_at)
             VALUES (:user_id, :token, :expires_at)",
            [
                'user_id' => $userId,
                'token' => $token,
                'expires_at' => $expiresAt
            ]
        );

        return $token;
    }

    public function validatePasswordReset(string $token): ?array
    {
        // ລຶບ tokens ທີ່ໝົດອາຍຸ
        $this->execute(
            "DELETE FROM password_resets WHERE expires_at < CURRENT_TIMESTAMP"
        );

        return $this->queryOne(
            "SELECT pr.*, u.email 
             FROM password_resets pr 
             JOIN users u ON pr.user_id = u.id 
             WHERE pr.token = :token AND pr.expires_at > CURRENT_TIMESTAMP",
            ['token' => $token]
        );
    }

    public function updatePassword(int $userId, string $newPassword): bool
    {
        $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);

        // ອັບເດດລະຫັດຜ່ານ ແລະ ລຶບ sessions ທັງໝົດ
        try {
            $this->beginTransaction();

            $this->execute(
                "UPDATE users SET password_hash = :password WHERE id = :id",
                ['password' => $passwordHash, 'id' => $userId]
            );

            $this->execute(
                "DELETE FROM sessions WHERE user_id = :user_id",
                ['user_id' => $userId]
            );

            $this->execute(
                "DELETE FROM password_resets WHERE user_id = :user_id",
                ['user_id' => $userId]
            );

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
