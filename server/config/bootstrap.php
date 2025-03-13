<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (!isset($_SERVER['APP_ENV'])) {
    if (!class_exists(Dotenv::class)) {
        throw new \RuntimeException('The Dotenv component is not installed. Use "composer require symfony/dotenv" to install it.');
    }

    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}
