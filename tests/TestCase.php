<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'App\\')) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
}, true, true);

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}

