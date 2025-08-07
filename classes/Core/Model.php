<?php

namespace Core;

abstract class Model
{
    protected Database $database;
    
    public function __construct(?Database $database = null)
    {
        $this->database = $database ?? new Database();
    }
    
    protected function sanitizeId(string $id): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $id);
    }
    
    protected function validateRequired(array $data, array $fields): void
    {
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Field '{$field}' is required");
            }
        }
    }
}


