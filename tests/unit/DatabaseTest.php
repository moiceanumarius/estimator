<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Core\Database;

class DatabaseTest extends TestCase
{
    private Database $database;
    private string $testSessionDir;
    
    protected function setUp(): void
    {
        $this->testSessionDir = sys_get_temp_dir() . '/test_session_' . uniqid();
        $this->database = new Database($this->testSessionDir);
    }
    
    protected function tearDown(): void
    {
        // Clean up test files
        if (is_dir($this->testSessionDir)) {
            $files = glob($this->testSessionDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->testSessionDir);
        }
    }
    
    public function testConstructorCreatesSessionDirectory(): void
    {
        $this->assertDirectoryExists($this->testSessionDir);
    }
    
    public function testConstructorWithDefaultDirectory(): void
    {
        $database = new Database();
        $defaultDir = __DIR__ . '/../../session';
        
        // Should create default directory if it doesn't exist
        if (!is_dir($defaultDir)) {
            $this->assertDirectoryExists($defaultDir);
            rmdir($defaultDir); // Clean up
        }
    }
    
    public function testGetUsersFile(): void
    {
        $roomId = 'test-room';
        $expectedPath = $this->testSessionDir . '/users_' . $roomId . '.json';
        
        $filePath = $this->database->getUsersFile($roomId);
        $this->assertEquals($expectedPath, $filePath);
    }
    
    public function testGetFlipFile(): void
    {
        $roomId = 'test-room';
        $expectedPath = $this->testSessionDir . '/flip_' . $roomId . '.json';
        
        $filePath = $this->database->getFlipFile($roomId);
        $this->assertEquals($expectedPath, $filePath);
    }
    
    public function testReadJsonWithExistingFile(): void
    {
        $testData = ['test' => 'data', 'number' => 123];
        $filePath = $this->testSessionDir . '/test.json';
        
        file_put_contents($filePath, json_encode($testData));
        
        $result = $this->database->readJson($filePath);
        $this->assertEquals($testData, $result);
    }
    
    public function testReadJsonWithNonExistentFile(): void
    {
        $filePath = $this->testSessionDir . '/nonexistent.json';
        
        $result = $this->database->readJson($filePath);
        $this->assertEquals([], $result);
    }
    
    public function testReadJsonWithInvalidJson(): void
    {
        $filePath = $this->testSessionDir . '/invalid.json';
        file_put_contents($filePath, 'invalid json content');
        
        $result = $this->database->readJson($filePath);
        $this->assertEquals([], $result);
    }
    
    public function testWriteJson(): void
    {
        $testData = ['key' => 'value', 'array' => [1, 2, 3]];
        $filePath = $this->testSessionDir . '/write_test.json';
        
        $result = $this->database->writeJson($filePath, $testData);
        $this->assertTrue($result);
        
        // Verify file was created and contains correct data
        $this->assertFileExists($filePath);
        $writtenData = json_decode(file_get_contents($filePath), true);
        $this->assertEquals($testData, $writtenData);
    }
    
    public function testWriteJsonCreatesDirectoryIfNeeded(): void
    {
        $nestedDir = $this->testSessionDir . '/nested/directory';
        $filePath = $nestedDir . '/test.json';
        $testData = ['test' => 'data'];
        
        $result = $this->database->writeJson($filePath, $testData);
        $this->assertTrue($result);
        $this->assertDirectoryExists($nestedDir);
        $this->assertFileExists($filePath);
    }
    
    public function testWriteJsonWithComplexData(): void
    {
        $complexData = [
            'users' => [
                ['id' => '1', 'name' => 'John', 'isAdmin' => true],
                ['id' => '2', 'name' => 'Jane', 'isAdmin' => false]
            ],
            'settings' => [
                'revealed' => false,
                'timestamp' => time()
            ]
        ];
        
        $filePath = $this->testSessionDir . '/complex_test.json';
        $result = $this->database->writeJson($filePath, $complexData);
        
        $this->assertTrue($result);
        $writtenData = json_decode(file_get_contents($filePath), true);
        $this->assertEquals($complexData, $writtenData);
    }
    
    public function testWriteJsonWithSpecialCharacters(): void
    {
        $specialData = [
            'name' => 'Marius',
            'message' => 'Hello, world! 🌍',
            'unicode' => '测试文本'
        ];
        
        $filePath = $this->testSessionDir . '/special_chars.json';
        $result = $this->database->writeJson($filePath, $specialData);
        
        $this->assertTrue($result);
        $writtenData = json_decode(file_get_contents($filePath), true);
        $this->assertEquals($specialData, $writtenData);
    }
    
    public function testWriteJsonOverwritesExistingFile(): void
    {
        $filePath = $this->testSessionDir . '/overwrite_test.json';
        $initialData = ['old' => 'data'];
        $newData = ['new' => 'data'];
        
        // Write initial data
        $this->database->writeJson($filePath, $initialData);
        
        // Overwrite with new data
        $result = $this->database->writeJson($filePath, $newData);
        $this->assertTrue($result);
        
        // Verify only new data exists
        $writtenData = json_decode(file_get_contents($filePath), true);
        $this->assertEquals($newData, $writtenData);
        $this->assertArrayNotHasKey('old', $writtenData);
    }
    
    public function testFilePermissions(): void
    {
        $filePath = $this->testSessionDir . '/permissions_test.json';
        $testData = ['test' => 'data'];
        
        $this->database->writeJson($filePath, $testData);
        
        // Check file permissions (should be readable and writable)
        $this->assertFileIsReadable($filePath);
        $this->assertFileIsWritable($filePath);
    }
}
