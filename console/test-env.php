<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "APP_ENV: " . getenv('APP_ENV') . PHP_EOL;
echo "APP_DEBUG: " . getenv('APP_DEBUG') . PHP_EOL;
