<?php

namespace Core;

class Router
{
    private array $routes = [];
    private Container $container;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    public function addRoute(string $method, string $action, string $controllerClass, string $methodName): void
    {
        $this->routes[] = [
            'method' => $method,
            'action' => $action,
            'controller' => $controllerClass,
            'controllerMethod' => $methodName
        ];
    }
    
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';
        error_log('Router debug: method=' . $method . ', action=' . var_export($action, true));
        
        foreach ($this->routes as $route) {
            error_log('Checking route: method=' . $route['method'] . ', action=' . var_export($route['action'], true));
            if ($route['method'] === $method && $route['action'] === $action) {
                $controllerClass = $route['controller'];
                
                // Check if class exists
                if (!class_exists($controllerClass)) {
                    throw new \Exception("Controller class {$controllerClass} not found");
                }
                
                // Try to resolve controller through container first
                try {
                    $controller = $this->container->resolve($controllerClass);
                } catch (\Exception $e) {
                    // Fallback to direct instantiation
                    $controller = new $controllerClass();
                }
                
                $methodName = $route['controllerMethod'];
                
                if (!method_exists($controller, $methodName)) {
                    throw new \Exception("Method {$methodName} not found in {$controllerClass}");
                }
                
                $controller->$methodName();
                return;
            }
        }
        error_log('No route matched for method=' . $method . ', action=' . var_export($action, true));
        // No route found
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Route not found']);
        exit;
    }
}
