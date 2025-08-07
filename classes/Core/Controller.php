<?php

namespace Core;

abstract class Controller
{
    protected Database $database;
    
    public function __construct(?Database $database = null)
    {
        $this->database = $database ?? new Database();
    }
    
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function errorResponse(string $message, int $statusCode = 400): void
    {
        $this->jsonResponse(['error' => $message], $statusCode);
    }
    
    protected function successResponse(array $data = []): void
    {
        $this->jsonResponse(array_merge(['success' => true], $data));
    }
    
    protected function getRequestData(): array
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return json_decode(file_get_contents('php://input'), true) ?: [];
        }
        return $_GET;
    }
    
    protected function getRoomId(): string
    {
        $data = $this->getRequestData();
        $roomId = $_GET['room'] ?? $data['room'] ?? null;
        
        if (!$roomId) {
            $this->errorResponse('Room ID required', 400);
        }
        
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $roomId);
    }
    
    protected function validateMethod(string $allowedMethod): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== $allowedMethod) {
            $this->errorResponse('Method not allowed', 405);
        }
    }

    protected function render(string $viewName, array $replacements = []): void
    {
        $viewPath = __DIR__ . "/../Views/{$viewName}.html";
        if (!file_exists($viewPath)) {
            throw new \Exception("View file {$viewName}.html not found at {$viewPath}");
        }
        $output = file_get_contents($viewPath);
        foreach ($replacements as $key => $value) {
            $output = str_replace("{{{$key}}}", $value, $output);
        }
        echo $output;
        exit;
    }
}


