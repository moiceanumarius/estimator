<?php

namespace Core;

abstract class View {
    protected $data = [];
    
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    public function setData($key, $value) {
        $this->data[$key] = $value;
    }
    
    public function getData($key, $default = null) {
        return $this->data[$key] ?? $default;
    }
    
    protected function renderPartial($viewName, $data = []) {
        $viewPath = __DIR__ . "/../Views/{$viewName}.php";
        if (file_exists($viewPath)) {
            extract($data);
            include $viewPath;
        }
    }
    
    public function renderHtmlView($file, $replacements = []) {
        $html = file_get_contents($file);
        foreach ($replacements as $key => $value) {
            $html = str_replace($key, $value, $html);
        }
        echo $html;
    }
    
    abstract public function render();
}


