<?php

class Router 
{
    private string $basePath;
    private array $routes = [];
    
    public function __construct(string $basePath = '/jomd/') 
    {
        $this->basePath = $basePath;
    }
    
    public function route(): mixed 
    {
        // Get and clean the request URI
        $requestUri = $_SERVER['REQUEST_URI'];
         // Ignore requests for static files
         if (preg_match('/\.(js|css|png|jpg|gif|ico)$/', $requestUri)) {
            return false;
        }
        
        $path = $this->cleanPath($requestUri);
        
        // Split the path into segments
        $segments = $this->parseSegments($path);
        
        // Extract route components
        $controller = $this->getController($segments);
        $action = $this->getAction($segments);
        $id = $this->getId($segments);
        
        // Handle empty path redirect
        if ($this->shouldRedirectHome($segments)) {
            $this->redirectToHome();
        }
        
        try {
            return $this->executeRoute($controller, $action, $id);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }
    
    private function cleanPath(string $requestUri): string 
    {
        return str_replace($this->basePath, '', $requestUri);
    }
    
    private function parseSegments(string $path): array 
    {
        return explode('/', trim($path, '/'));
    }
    
    private function getController(array $segments): string 
    {
        return ucfirst($segments[0] ?? 'Home') . 'Controller';
    }
    
    private function getAction(array $segments): string 
    {
        return $segments[1] ?? 'index';
    }
    
    private function getId(array $segments): ?string 
    {
        return $segments[2] ?? null;
    }
    
    private function shouldRedirectHome(array $segments): bool 
    {
        return empty($segments[0]);
    }
    
    private function redirectToHome(): never 
    {
        header('Location: ' . $this->basePath . 'home');
        exit;
    }
    
    private function executeRoute(string $controller, string $action, ?string $id): mixed 
    {
        $controllerPath = "controllers/{$controller}.php";
        
        if (!file_exists($controllerPath)) {
            throw new Exception("Controller not found: {$controller}");
        }
        
        require_once $controllerPath;
        
        if (!class_exists($controller)) {
            throw new Exception("Controller class not found: {$controller}");
        }
        
        $controllerInstance = new $controller();
        
        if (!method_exists($controllerInstance, $action)) {
            throw new Exception("Method {$action} not found in controller {$controller}");
        }
        
        return $controllerInstance->$action($id);
    }
    
    private function handleError(Exception $e): void 
    {
        if ($e->getMessage() === "Controller not found: {$e->getMessage()}") {
            require_once "views/page404.php";
            return;
        }
        
        // Log error for debugging
        error_log($e->getMessage());
        
        // Display user-friendly error message
        echo "<div class='error'>";
        echo "<h2>Something went wrong</h2>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
}