<?php

require __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'App\\')) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    } elseif (str_starts_with($class, 'Database\\Seeders\\')) {
        $file = __DIR__ . '/../database/seeders/' . str_replace('\\', '/', substr($class, 17)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    } elseif (str_starts_with($class, 'Database\\Factories\\')) {
        $file = __DIR__ . '/../database/factories/' . str_replace('\\', '/', substr($class, 19)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    } elseif (str_starts_with($class, 'Tests\\')) {
        $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, 6)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
}, true, true);
