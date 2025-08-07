<?php

namespace Core;

class Database
{
    private string $sessionDir;
    
    public function __construct(?string $sessionDir = null)
    {
        $this->sessionDir = $sessionDir ?: __DIR__ . '/../../session';
        if (!is_dir($this->sessionDir)) {
            mkdir($this->sessionDir, 0755, true);
        }
    }
    
    public function getSessionDir(): string
    {
        return $this->sessionDir;
    }
    
    public function getUsersFile(string $roomId): string
    {
        return $this->sessionDir . "/users_{$roomId}.json";
    }
    
    public function getFlipFile(string $roomId): string
    {
        return $this->sessionDir . "/flip_{$roomId}.json";
    }
    
    public function readJson(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }
        return json_decode(file_get_contents($filePath), true) ?: [];
    }
    
    public function writeJson(string $filePath, array $data): bool
    {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT)) !== false;
    }
    
    public function deleteFile(string $filePath): bool
    {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return true; // File doesn't exist, consider it "deleted"
    }
}
