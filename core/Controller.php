<?php

class Controller
{
    protected const VIEWS_PATH = 'views/';
    protected const LAYOUTS_PATH = 'views/layouts/';
    protected const DEFAULT_LAYOUT = 'main';
    use Redirector;

    /**
     * Renders a view with optional data
     * 
     * @param string $view View name
     * @param array $data Data to pass to the view
     * @param string|null $layout Layout name (null for no layout)
     * @throws Exception If view file not found
     */
    protected function render(string $view, array $data = [], ?string $layout = self::DEFAULT_LAYOUT): void
    {
        try {
            // Sanitize all data before passing to view
            $data = $this->sanitizeData($data);

            // Get view content
            $content = $this->getViewContent($view, $data);

            // Handle layout
            if ($layout !== null) {
                $this->renderWithLayout($content, $layout);
            } else {
                echo $content;
            }
        } catch (Exception $e) {
            // Log error
            error_log($e->getMessage());
            throw $e;
        }
    }

    /**
     * Renders JSON response with proper headers
     * 
     * @param mixed $data Data to encode as JSON
     * @param int $status HTTP status code
     */
    protected function renderJson(mixed $data, int $status = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * Performs a safe redirect
     * 
     * @param string $url URL to redirect to
     * @param int $status HTTP status code
     */
    protected function redirect(string $url, int $status = 302): never
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $redirectUrl = '/' . BASE_PATH . '/' . ltrim($url, '/');
        header("Location: {$redirectUrl}", true, $status);
        exit;
    }

    /**
     * Gets POST data with optional filtering
     * 
     * @param string|null $key Specific key to get
     * @param mixed $default Default value if key not found
     * @return mixed Filtered POST data
     */
    protected function getPost(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        }

        return filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS) ?? $default;
    }

    /**
     * Gets GET data with optional filtering
     * 
     * @param string|null $key Specific key to get
     * @param mixed $default Default value if key not found
     * @return mixed Filtered GET data
     */
    protected function getQuery(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS);
        }

        return filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS) ?? $default;
    }

    /**
     * Checks if request method is POST
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Checks if request is AJAX
     */
    protected function isAjax(): bool
    {
        return (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );
    }

    /**
     * Gets CSRF token
     */
    protected function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Validates CSRF token
     */
    protected function validateCsrfToken(?string $token): bool
    {
        return isset($_SESSION['csrf_token']) &&
            hash_equals($_SESSION['csrf_token'], $token ?? '');
    }

    /**
     * Gets view content
     */
    private function getViewContent(string $view, array $data): string
    {
        $viewFile = self::VIEWS_PATH . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: {$viewFile}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $viewFile;
        return ob_get_clean();
    }

    /**
     * Renders content within layout
     */
    private function renderWithLayout(string $content, string $layout): void
    {
        $layoutFile = self::LAYOUTS_PATH . $layout . '.php';

        if (!file_exists($layoutFile)) {
            throw new Exception("Layout file not found: {$layoutFile}");
        }

        require $layoutFile;
    }

    /**
     * Sanitizes data recursively
     */
    private function sanitizeData(array $data): array
    {
        return array_map(function ($value) {
            if (is_array($value)) {
                return $this->sanitizeData($value);
            }
            if (is_string($value)) {
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
            return $value;
        }, $data);
    }
}
