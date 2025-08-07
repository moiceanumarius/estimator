<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Controllers\ApiController;
use Core\Container;
use Core\Database;
use Models\RoomModel;
use Models\UserModel;

class ApiControllerTest extends TestCase
{
    private ApiController $controller;
    private Container $container;
    private Database $mockDatabase;
    
    protected function setUp(): void
    {
        $this->container = new Container();
        
        // Create mock database
        $this->mockDatabase = $this->createMock(Database::class);
        $this->mockDatabase->method('getUsersFile')->willReturn('/tmp/test_users.json');
        $this->mockDatabase->method('getFlipFile')->willReturn('/tmp/test_flip.json');
        $this->mockDatabase->method('readJson')->willReturn([]);
        $this->mockDatabase->method('writeJson')->willReturn(true);
        
        // Register services in container
        $this->container->singleton('database', function(Container $container) {
            return $this->mockDatabase;
        });
        
        $this->container->register('roomModel', function(Container $container) {
            return function(string $roomId) use ($container) {
                return new RoomModel($roomId, $container->get('database'));
            };
        });
        
        $this->container->register('userModel', function(Container $container) {
            return function(string $name, bool $isAdmin = false, ?string $id = null) {
                return new UserModel($name, $isAdmin, $id);
            };
        });
        
        $this->controller = new ApiController($this->container);
    }
    
    public function testCreateRoomModelWithContainer(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('createRoomModel');
        $method->setAccessible(true);
        
        $roomModel = $method->invoke($this->controller, 'test-room');
        $this->assertInstanceOf(RoomModel::class, $roomModel);
    }
    
    public function testCreateUserModelWithContainer(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('createUserModel');
        $method->setAccessible(true);
        
        $userModel = $method->invoke($this->controller, 'marius', true);
        $this->assertInstanceOf(UserModel::class, $userModel);
        $this->assertEquals('marius', $userModel->getName());
        $this->assertTrue($userModel->isAdmin());
    }
    
    public function testControllerHasDatabaseInjected(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $property = $reflection->getProperty('database');
        $property->setAccessible(true);
        
        $database = $property->getValue($this->controller);
        $this->assertInstanceOf(Database::class, $database);
    }
    
    public function testControllerHasContainerInjected(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $property = $reflection->getProperty('container');
        $property->setAccessible(true);
        
        $container = $property->getValue($this->controller);
        $this->assertInstanceOf(Container::class, $container);
    }
    
    public function testControllerFallbackWithoutContainer(): void
    {
        // Test controller without container (fallback behavior)
        $controllerWithoutContainer = new ApiController();
        
        $reflection = new \ReflectionClass($controllerWithoutContainer);
        $method = $reflection->getMethod('createRoomModel');
        $method->setAccessible(true);
        
        $roomModel = $method->invoke($controllerWithoutContainer, 'test-room');
        $this->assertInstanceOf(RoomModel::class, $roomModel);
    }
    
    public function testGetRequestDataWithGet(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('getRequestData');
        $method->setAccessible(true);
        
        // Test with GET data
        $_GET = ['test' => 'value'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        $data = $method->invoke($this->controller);
        $this->assertEquals(['test' => 'value'], $data);
    }
    
    public function testValidateMethodSuccess(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('validateMethod');
        $method->setAccessible(true);
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Should not throw exception for correct method
        $method->invoke($this->controller, 'POST');
        $this->assertTrue(true); // If we get here, no exception was thrown
    }
    
    public function testValidateMethodFailure(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('validateMethod');
        $method->setAccessible(true);
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Capture output to prevent it from being displayed
        ob_start();
        
        // This should exit with error response, not throw exception
        $method->invoke($this->controller, 'GET');
        
        $output = ob_get_clean();
        $this->assertStringContainsString('Method not allowed', $output);
    }
}
