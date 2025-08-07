<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Core\Container;
use Core\Database;
use Models\RoomModel;
use Models\UserModel;

class ContainerTest extends TestCase
{
    private Container $container;
    
    protected function setUp(): void
    {
        $this->container = new Container();
    }
    
    public function testRegisterAndGetService(): void
    {
        $this->container->register('test', function(Container $container) {
            return 'test_value';
        });
        
        $result = $this->container->get('test');
        $this->assertEquals('test_value', $result);
    }
    
    public function testSingletonPattern(): void
    {
        $this->container->singleton('database', function(Container $container) {
            $mockDatabase = $this->createMock(Database::class);
            $mockDatabase->method('getUsersFile')->willReturn('/tmp/test_users.json');
            $mockDatabase->method('getFlipFile')->willReturn('/tmp/test_flip.json');
            $mockDatabase->method('readJson')->willReturn([]);
            $mockDatabase->method('writeJson')->willReturn(true);
            return $mockDatabase;
        });
        
        $db1 = $this->container->get('database');
        $db2 = $this->container->get('database');
        
        $this->assertSame($db1, $db2);
        $this->assertInstanceOf(Database::class, $db1);
    }
    
    public function testHasMethod(): void
    {
        $this->assertFalse($this->container->has('nonexistent'));
        
        $this->container->register('test', function(Container $container) {
            return 'value';
        });
        
        $this->assertTrue($this->container->has('test'));
    }
    
    public function testGetNonexistentService(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Service 'nonexistent' not registered");
        
        $this->container->get('nonexistent');
    }
    
    public function testResolveClassWithoutDependencies(): void
    {
        $user = $this->container->resolve(UserModel::class);
        $this->assertInstanceOf(UserModel::class, $user);
    }
    
    public function testResolveClassWithDependencies(): void
    {
        // Register database first
        $this->container->singleton('database', function(Container $container) {
            $mockDatabase = $this->createMock(Database::class);
            $mockDatabase->method('getUsersFile')->willReturn('/tmp/test_users.json');
            $mockDatabase->method('getFlipFile')->willReturn('/tmp/test_flip.json');
            $mockDatabase->method('readJson')->willReturn([]);
            $mockDatabase->method('writeJson')->willReturn(true);
            return $mockDatabase;
        });
        
        $room = $this->container->resolve(RoomModel::class);
        $this->assertInstanceOf(RoomModel::class, $room);
    }
    
    public function testResolveClassWithOptionalDependencies(): void
    {
        // Test resolving a class that has optional parameters
        $user = $this->container->resolve(UserModel::class);
        $this->assertInstanceOf(UserModel::class, $user);
        
        // Test with custom parameters
        $reflection = new \ReflectionClass(UserModel::class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();
        
        // Check that the third parameter (id) is optional
        $this->assertTrue($parameters[2]->isDefaultValueAvailable());
    }
    
    public function testFactoryPattern(): void
    {
        $this->container->register('roomFactory', function(Container $container) {
            return function(string $roomId) use ($container) {
                return new RoomModel($roomId, $container->get('database'));
            };
        });
        
        $this->container->singleton('database', function(Container $container) {
            $mockDatabase = $this->createMock(Database::class);
            $mockDatabase->method('getUsersFile')->willReturn('/tmp/test_users.json');
            $mockDatabase->method('getFlipFile')->willReturn('/tmp/test_flip.json');
            $mockDatabase->method('readJson')->willReturn([]);
            $mockDatabase->method('writeJson')->willReturn(true);
            return $mockDatabase;
        });
        
        $factory = $this->container->get('roomFactory');
        $this->assertIsCallable($factory);
        
        $room = $factory('test-room');
        $this->assertInstanceOf(RoomModel::class, $room);
    }
    
    public function testContainerInjection(): void
    {
        $this->container->register('service', function(Container $container) {
            $this->assertInstanceOf(Container::class, $container);
            return 'injected_value';
        });
        
        $result = $this->container->get('service');
        $this->assertEquals('injected_value', $result);
    }
    
    public function testMultipleRegistrations(): void
    {
        $this->container->register('service1', function(Container $container) {
            return 'value1';
        });
        
        $this->container->register('service2', function(Container $container) {
            return 'value2';
        });
        
        $this->assertTrue($this->container->has('service1'));
        $this->assertTrue($this->container->has('service2'));
        $this->assertEquals('value1', $this->container->get('service1'));
        $this->assertEquals('value2', $this->container->get('service2'));
    }
    
    public function testResolveWithComplexDependencies(): void
    {
        // Test resolving a class that depends on another class with dependencies
        $this->container->singleton('database', function(Container $container) {
            $mockDatabase = $this->createMock(Database::class);
            $mockDatabase->method('getUsersFile')->willReturn('/tmp/test_users.json');
            $mockDatabase->method('getFlipFile')->willReturn('/tmp/test_flip.json');
            $mockDatabase->method('readJson')->willReturn([]);
            $mockDatabase->method('writeJson')->willReturn(true);
            return $mockDatabase;
        });
        
        // This should work because RoomModel depends on Database, which is registered
        $room = $this->container->resolve(RoomModel::class);
        $this->assertInstanceOf(RoomModel::class, $room);
        
        // Verify that the database was injected
        $reflection = new \ReflectionClass($room);
        $property = $reflection->getProperty('database');
        $property->setAccessible(true);
        
        $database = $property->getValue($room);
        $this->assertInstanceOf(Database::class, $database);
    }
}
