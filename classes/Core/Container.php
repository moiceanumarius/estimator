<?php

namespace Core;

class Container
{
    private array $services = [];
    private array $singletons = [];
    
    public function register(string $name, callable $factory): void
    {
        $this->services[$name] = $factory;
    }
    
    public function singleton(string $name, callable $factory): void
    {
        $this->services[$name] = $factory;
        $this->singletons[$name] = true;
    }
    
    public function get(string $name)
    {
        if (!isset($this->services[$name])) {
            throw new \Exception("Service '{$name}' not registered");
        }
        
        // Return singleton instance if already created
        if (isset($this->singletons[$name]) && isset($this->singletons["{$name}_instance"])) {
            return $this->singletons["{$name}_instance"];
        }
        
        $instance = $this->services[$name]($this);
        
        // Store singleton instance
        if (isset($this->singletons[$name])) {
            $this->singletons["{$name}_instance"] = $instance;
        }
        
        return $instance;
    }
    
    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }
    
    public function resolve(string $className)
    {
        $reflection = new \ReflectionClass($className);
        $constructor = $reflection->getConstructor();
        
        if (!$constructor) {
            return new $className();
        }
        
        $parameters = $constructor->getParameters();
        $dependencies = [];
        
        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            
            if (!$type || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("Cannot resolve parameter '{$parameter->getName()}' for class '{$className}'");
                }
            } else {
                $typeName = $type->getName();
                // Check if we have this service registered
                if ($this->has($typeName)) {
                    $dependencies[] = $this->get($typeName);
                } else {
                    // Try to resolve it recursively
                    $dependencies[] = $this->resolve($typeName);
                }
            }
        }
        
        return $reflection->newInstanceArgs($dependencies);
    }
}
