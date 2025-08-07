<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Core\Container;
use Core\Database;
use Models\RoomModel;
use Models\UserModel;
use Controllers\ApiController;

class DependencyInjectionTest extends TestCase
{
    private Container $container;
    
    protected function setUp(): void
    {
        $this->container = new Container();
        
        // Register a mock database for testing
        $this->container->singleton('database', function(Container $container) {
            $mockDatabase = $this->createMock(Database::class);
            $mockDatabase->method('getUsersFile')->willReturn('/tmp/test_users.json');
            $mockDatabase->method('getFlipFile')->willReturn('/tmp/test_flip.json');
            $mockDatabase->method('readJson')->willReturn([]);
            $mockDatabase->method('writeJson')->willReturn(true);
            return $mockDatabase;
        });
        
        // Register RoomModel factory
        $this->container->register('roomModel', function(Container $container) {
            return function(string $roomId) use ($container) {
                return new RoomModel($roomId, $container->get('database'));
            };
        });
        
        // Register UserModel factory
        $this->container->register('userModel', function(Container $container) {
            return function(string $name, bool $isAdmin = false, ?string $id = null) {
                return new UserModel($name, $isAdmin, $id);
            };
        });
    }
    
    public function testContainerCanResolveDependencies(): void
    {
        $database = $this->container->get('database');
        $this->assertInstanceOf(Database::class, $database);
        
        $roomModelFactory = $this->container->get('roomModel');
        $roomModel = $roomModelFactory('test-room');
        $this->assertInstanceOf(RoomModel::class, $roomModel);
    }
    
    public function testApiControllerCanUseInjectedContainer(): void
    {
        $apiController = new ApiController($this->container);
        
        // Test that the controller can create models through the container
        $reflection = new \ReflectionClass($apiController);
        $method = $reflection->getMethod('createRoomModel');
        $method->setAccessible(true);
        
        $roomModel = $method->invoke($apiController, 'test-room');
        $this->assertInstanceOf(RoomModel::class, $roomModel);
    }
    
    public function testSingletonPatternWorks(): void
    {
        $database1 = $this->container->get('database');
        $database2 = $this->container->get('database');
        
        $this->assertSame($database1, $database2);
    }
    
    public function testContainerCanResolveClassWithDependencies(): void
    {
        // Test automatic dependency resolution
        // This should work because we have database registered as singleton
        $roomModel = $this->container->resolve(RoomModel::class);
        $this->assertInstanceOf(RoomModel::class, $roomModel);
    }
}
