<?php
class Helper
{
    public static function url($path)
    {
        return "/" . BASE_PATH . '/' . $path;
    }

    public static function html($text, $encoding = 'UTF-8', $doubleEncode = true)
    {
        if ($text === null) {
            return '';
        }

        // Convert arrays to strings
        if (is_array($text)) {
            return implode(', ', array_map([self::class, 'html'], $text));
        }

        // Convert objects with __toString() method
        if (is_object($text) && method_exists($text, '__toString')) {
            $text = (string) $text;
        }

        // Force conversion to string for non-string values
        if (!is_string($text)) {
            $text = (string) $text;
        }

        return htmlspecialchars(
            $text,
            ENT_QUOTES | ENT_HTML5, // Use HTML5 encoding with quotes
            $encoding,              // Support different character encodings
            $doubleEncode          // Allow control over double encoding
        );
    }

    public static function redirect($path)
    {
        header("Location: " . Helper::url($path));
        exit;
    }

    public static function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function stime($time, $defaultTime = null)
    {
        // Return current timestamp if input is empty
        if (empty($time)) {
            return $defaultTime ?? time();
        }

        // Handle DateTime objects
        if ($time instanceof DateTime) {
            return $time->getTimestamp();
        }

        // Handle DateTimeImmutable objects
        if ($time instanceof DateTimeImmutable) {
            return $time->getTimestamp();
        }

        // Handle timestamp integers
        if (is_numeric($time)) {
            return (int) $time;
        }

        // Try to parse the time string
        $timestamp = strtotime($time);

        // Return default time if parsing fails
        if ($timestamp === false) {
            return $defaultTime ?? time();
        }

        return $timestamp;
    }

    // ຟັງຊັນກວດສອບໂຟລເດີ
    public static function ensureDirectoryExists(string $path): void
    {
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }

    // ຟັງຊັນລຶບໄຟລ໌
    public static function deleteFile(string $path): bool
    {
        if (file_exists($path)) {
            return unlink($path);
        }
        return true;
    }

    // ຟັງຊັນຍ້າຍໄຟລ໌ອັບໂຫຼດ
    public static function moveUploadedFile(array $file, string $destination): bool
    {
        return move_uploaded_file(
            $file['tmp_name'],
            $destination
        );
    }

    public static function csrf()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return "<input type='hidden' name='csrf' value='" . $_SESSION['csrf'] . "'>";
    }

    public static function formatDate($date, $format)
    {
        return date($format, strtotime($date));
    }

    public static function int($value)
    {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    public static function truncate($text, $length)
    {
        if (strlen($text) > $length) {
            return substr($text, 0, $length) . '...';
        }
        return $text;
    }

    /**
     * ສະແດງ flash message ແບບ Bootstrap alert
     * @return string HTML ຂອງ flash message
     */
    public static function showFlash(): string
    {
        if (!isset($_SESSION['_flash_message'])) {
            return '';
        }

        $message = $_SESSION['_flash_message'];
        unset($_SESSION['_flash_message']);

        // ແປງປະເພດຂໍ້ຄວາມເປັນ Bootstrap classes
        $typeMap = [
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ];

        $type = $typeMap[$message['type']] ?? 'alert-info';
        $text = htmlspecialchars($message['text']);

        return <<<HTML
    <div class="alert {$type} alert-dismissible fade show" role="alert">
        {$text}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    HTML;
    }

    /**
     * ສະແດງຂໍ້ຜິດພາດຂອງ form ທັງໝົດ
     * @return string HTML ຂອງລາຍການຂໍ້ຜິດພາດ
     */
    function showErrors(): string
    {
        if (!isset($_SESSION['_flash_errors']) || empty($_SESSION['_flash_errors'])) {
            return '';
        }

        $errors = $_SESSION['_flash_errors'];
        unset($_SESSION['_flash_errors']);

        $errorList = '';
        foreach ($errors as $error) {
            $errorList .= "<li>" . htmlspecialchars($error) . "</li>";
        }

        return <<<HTML
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading">ກະລຸນາກວດສອບຂໍ້ມູນ</h4>
        <ul>
            {$errorList}
        </ul>
    </div>
    HTML;
    }

    /**
     * ດຶງຂໍ້ມູນເກົ່າຂອງ form field
     * @param string $key ຊື່ field
     * @param mixed $default ຄ່າເລີ່ມຕົ້ນ
     * @return mixed
     */
    function old(?string $key, mixed $default = null): mixed
    {
        if (!isset($_SESSION['_flash_input']) || !isset($_SESSION['_flash_input'][$key])) {
            return $default;
        }

        $value = $_SESSION['_flash_input'][$key];
        unset($_SESSION['_flash_input'][$key]);
        return $value;
    }

    /**
     * ກວດວ່າມີຂໍ້ຜິດພາດສຳລັບ field ນີ້ຫຼືບໍ່
     * @param string $field ຊື່ field
     * @return bool
     */
    function hasError(string $field): bool
    {
        return isset($_SESSION['_flash_errors']) &&
            isset($_SESSION['_flash_errors'][$field]);
    }

    /**
     * ສະແດງຂໍ້ຜິດພາດສຳລັບ field ສະເພາະ
     * @param string $field ຊື່ field
     * @return string HTML ຂອງຂໍ້ຜິດພາດ
     */
    function getFieldError(string $field): string
    {
        if (!$this->hasError($field)) {
            return '';
        }

        $error = htmlspecialchars($_SESSION['_flash_errors'][$field]);
        unset($_SESSION['_flash_errors'][$field]);

        return "<div class='invalid-feedback'>{$error}</div>";
    }
}
