<?php
spl_autoload_register(function ($className) {
    $namespace = 'lyhiving\\guanyierp';
    if (strpos($className, $namespace) === 0) {
        $className = str_replace($namespace, '', $className);
        $fileName = __DIR__ . '/src/' . str_replace('\\', '/', $className) . '.php';
        if (file_exists($fileName)) {
            require_once($fileName);
        }
    }
});