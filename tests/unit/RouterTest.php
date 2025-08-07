<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Core\Router;
use Core\Container;
use Controllers\ApiController;

class RouterTest extends TestCase
{
    private Router $router;
    private Container $container;
    
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->router = new Router($this->container);
    }
    
    public function testAddRoute(): void
    {
        $this->router->addRoute('GET', 'test', 'Controllers\\ApiController', 'testMethod');
        
        // Use reflection to check if route was added
        $reflection = new \ReflectionClass($this->router);
        $property = $reflection->getProperty('routes');
        $property->setAccessible(true);
        
        $routes = $property->getValue($this->router);
        $this->assertCount(1, $routes);
        $this->assertEquals('GET', $routes[0]['method']);
        $this->assertEquals('test', $routes[0]['action']);
        $this->assertEquals('Controllers\\ApiController', $routes[0]['controller']);
        $this->assertEquals('testMethod', $routes[0]['controllerMethod']);
    }
    
    public function testRouterHasContainer(): void
    {
        $reflection = new \ReflectionClass($this->router);
        $property = $reflection->getProperty('container');
        $property->setAccessible(true);
        
        $container = $property->getValue($this->router);
        $this->assertInstanceOf(Container::class, $container);
    }
    
    public function testMultipleRoutes(): void
    {
        $this->router->addRoute('GET', 'route1', 'Controllers\\ApiController', 'method1');
        $this->router->addRoute('POST', 'route2', 'Controllers\\ApiController', 'method2');
        $this->router->addRoute('PUT', 'route3', 'Controllers\\ApiController', 'method3');
        
        $reflection = new \ReflectionClass($this->router);
        $property = $reflection->getProperty('routes');
        $property->setAccessible(true);
        
        $routes = $property->getValue($this->router);
        $this->assertCount(3, $routes);
        
        $this->assertEquals('GET', $routes[0]['method']);
        $this->assertEquals('POST', $routes[1]['method']);
        $this->assertEquals('PUT', $routes[2]['method']);
    }
    
    public function testDispatchWithFallbackInstantiation(): void
    {
        $this->router->addRoute('GET', 'test', 'Controllers\\ApiController', 'testMethod');
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['action'] = 'test';
        
        // Capture output to prevent it from being displayed
        ob_start();
        
        // This should work because ApiController has a default constructor
        // that doesn't require parameters
        $this->router->dispatch();
        
        $output = ob_get_clean();
        // If we get here without error, the test passes
        $this->assertTrue(true);
    }
    
    public function testDispatchNoRouteFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['action'] = 'nonexistent';
        
        // Capture output to prevent it from being displayed
        ob_start();
        
        $this->router->dispatch();
        
        $output = ob_get_clean();
        $this->assertStringContainsString('Route not found', $output);
    }
    
    public function testDispatchWithDifferentMethods(): void
    {
        $this->router->addRoute('POST', 'test', 'Controllers\\ApiController', 'testMethod');
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['action'] = 'test';
        
        // Should not find route because method doesn't match
        ob_start();
        
        $this->router->dispatch();
        
        $output = ob_get_clean();
        $this->assertStringContainsString('Route not found', $output);
    }
}
