<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (!isset($_SERVER['APP_ENV']) && !isset($_ENV['APP_ENV'])) {
    if (class_exists(Dotenv::class)) {
        (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
    } else {
        echo 'APP_ENV not set and .env file not found.';
 
        exit(1);
    }
} else {
    echo 'APP_ENV: ' . ($_ENV['APP_ENV'] ?? 'not set');
    echo 'APP_DEBUG: ' . ($_ENV['APP_DEBUG'] ?? 'not set');
}
