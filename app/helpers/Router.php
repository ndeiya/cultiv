<?php
/**
 * Simple Router
 * Registers routes, matches URIs, extracts params, and dispatches to controllers.
 */

class Router
{
    private array $routes = [];
    private string $prefix = '';

    /**
     * Set a prefix for a group of routes.
     */
    public function group(string $prefix, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $this->prefix = $previousPrefix . $prefix;
        $callback($this);
        $this->prefix = $previousPrefix;
    }

    /**
     * Register a GET route.
     */
    public function get(string $uri, string|Closure $controller, ?string $method = null): void
    {
        $this->addRoute('GET', $uri, $controller, $method);
    }

    /**
     * Register a POST route.
     */
    public function post(string $uri, string|Closure $controller, ?string $method = null): void
    {
        $this->addRoute('POST', $uri, $controller, $method);
    }

    /**
     * Register a PUT route.
     */
    public function put(string $uri, string|Closure $controller, ?string $method = null): void
    {
        $this->addRoute('PUT', $uri, $controller, $method);
    }

    /**
     * Register a DELETE route.
     */
    public function delete(string $uri, string|Closure $controller, ?string $method = null): void
    {
        $this->addRoute('DELETE', $uri, $controller, $method);
    }

    /**
     * Add a route to the registry.
     */
    private function addRoute(string $httpMethod, string $uri, string|Closure $controller, ?string $method = null): void
    {
        $fullUri = $this->prefix . $uri;

        // Convert route params like {id} to named regex groups
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $fullUri);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method'     => $httpMethod,
            'pattern'    => $pattern,
            'controller' => $controller,
            'action'     => $method,
        ];
    }

    /**
     * Dispatch the current request to the matching route.
     */
    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri    = $this->getRequestUri();

        // Support method override via POST hidden field (for PUT/DELETE in forms)
        if ($requestMethod === 'POST' && isset($_POST['_method'])) {
            $requestMethod = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            if (preg_match($route['pattern'], $requestUri, $matches)) {
                // Extract only named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $controller = $route['controller'];

                // Handle closure routes
                if ($controller instanceof Closure) {
                    call_user_func_array($controller, array_values($params));
                    return;
                }

                $controllerName = $controller;
                $actionName     = $route['action'];

                // Load and instantiate the controller
                $controllerFile = BASE_PATH . '/app/controllers/' . $controllerName . '.php';
                if (!file_exists($controllerFile)) {
                    $this->abort(500, "Controller file not found: {$controllerName}");
                    return;
                }

                require_once $controllerFile;

                if (!class_exists($controllerName)) {
                    $this->abort(500, "Controller class not found: {$controllerName}");
                    return;
                }

                $controllerInstance = new $controllerName();

                if (!method_exists($controllerInstance, $actionName)) {
                    $this->abort(500, "Method not found: {$controllerName}::{$actionName}");
                    return;
                }

                // Call the controller method with extracted params
                call_user_func_array([$controllerInstance, $actionName], array_values($params));
                return;
            }
        }

        // No route matched
        $this->abort(404, 'Page not found');
    }

    /**
     * Get the cleaned request URI (without query string).
     */
    private function getRequestUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');

        // Normalize trailing slash (except root)
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        return $uri;
    }

    /**
     * Abort with an HTTP error code.
     */
    private function abort(int $code, string $message = ''): void
    {
        http_response_code($code);

        // If this is an API request, return JSON
        if (str_starts_with($this->getRequestUri(), '/api/')) {
            header('Content-Type: application/json');
            echo json_encode([
                'error'   => true,
                'message' => $message ?: 'An error occurred',
                'code'    => $code,
            ]);
            return;
        }

        // For web requests, show a simple error page
        $title = match ($code) {
            404 => 'Page Not Found',
            403 => 'Access Denied',
            500 => 'Server Error',
            default => 'Error',
        };

        echo "<!DOCTYPE html><html><head><title>{$title} — " . APP_NAME . "</title></head>";
        echo "<body style='font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f6f8f6;'>";
        echo "<div style='text-align:center;'>";
        echo "<h1 style='font-size:4rem;color:#13ec13;margin:0;'>{$code}</h1>";
        echo "<p style='color:#64748b;font-size:1.1rem;'>{$title}</p>";
        if (APP_DEBUG && $message) {
            echo "<p style='color:#94a3b8;font-size:0.85rem;'>{$message}</p>";
        }
        echo "<a href='/' style='color:#13ec13;text-decoration:none;font-weight:600;'>← Back to Home</a>";
        echo "</div></body></html>";
    }
}
