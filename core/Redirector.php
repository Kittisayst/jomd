<?php

trait Redirector
{
    protected function redirect(string $url, int $status = 302): never
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $redirectUrl = '/' . BASE_PATH . '/' . ltrim($url, '/');
        header("Location: {$redirectUrl}", true, $status);
        exit;
    }

    protected function redirectWith(
        string $url, 
        array $data = [], 
        ?string $message = null, 
        string $messageType = 'info',
        int $status = 302
    ): never {
        // ຮັບປະກັນວ່າ session ໄດ້ເລີ່ມຕົ້ນແລ້ວ
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // ເກັບຂໍ້ມູນຊົ່ວຄາວໃນ session
        if (!empty($data)) {
            $_SESSION['_flash_data'] = $data;
        }

        // ເກັບຂໍ້ຄວາມແຈ້ງເຕືອນ
        if ($message !== null) {
            $_SESSION['_flash_message'] = [
                'text' => $message,
                'type' => $messageType
            ];
        }

        // ບັນທຶກ URL ທີ່ມາຈາກ
        $_SESSION['_previous_url'] = $_SERVER['REQUEST_URI'] ?? '/';

        // ເຮັດການ redirect
        $this->redirect($url, $status);
    }

    protected function withErrors(array $errors): never 
    {
        $_SESSION['_flash_errors'] = $errors;
        $previousUrl = $_SESSION['_previous_url'] ?? '/';
        $this->redirect($previousUrl);
    }

    protected function withInput(array $input = null): never 
    {
        $input = $input ?? $_POST;
        $_SESSION['_flash_input'] = $input;
        $previousUrl = $_SESSION['_previous_url'] ?? '/';
        $this->redirect($previousUrl);
    }

    protected function getFlashData(?string $key = null) 
    {
        if ($key === null) {
            $data = $_SESSION['_flash_data'] ?? [];
            unset($_SESSION['_flash_data']);
            return $data;
        }

        $value = $_SESSION['_flash_data'][$key] ?? null;
        unset($_SESSION['_flash_data'][$key]);
        return $value;
    }

    protected function getFlashMessage(): ?array 
    {
        $message = $_SESSION['_flash_message'] ?? null;
        unset($_SESSION['_flash_message']);
        return $message;
    }

    protected function getErrors(): array 
    {
        $errors = $_SESSION['_flash_errors'] ?? [];
        unset($_SESSION['_flash_errors']);
        return $errors;
    }

    protected function getOldInput(?string $key = null) 
    {
        if ($key === null) {
            $input = $_SESSION['_flash_input'] ?? [];
            unset($_SESSION['_flash_input']);
            return $input;
        }

        $input = $_SESSION['_flash_input'] ?? [];
        unset($_SESSION['_flash_input']);
        return $input[$key] ?? null;
    }

    protected function back(int $status = 302): never 
    {
        $previousUrl = $_SESSION['_previous_url'] ?? '/';
        $this->redirect($previousUrl, $status);
    }
}