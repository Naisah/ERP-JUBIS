<?php
// Force isolation before Laravel loads .env or any cached application configuration.
$qaEnvironment = [
    'APP_ENV' => 'testing', 'APP_DEBUG' => 'false',
    'APP_CONFIG_CACHE' => __DIR__.'/results/unused-config.php',
    'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'DB_URL' => '',
    'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'array', 'MAIL_URL' => '', 'MAIL_USERNAME' => '', 'MAIL_PASSWORD' => '',
    'PAYMONGO_SECRET_KEY' => '', 'PAYMONGO_WEBHOOK_SECRET' => '',
    'LALAMOVE_API_KEY' => '', 'LALAMOVE_API_SECRET' => '', 'SEMAPHORE_API_KEY' => '',
    'BCRYPT_ROUNDS' => '4', 'BROADCAST_CONNECTION' => 'null',
];
foreach ($qaEnvironment as $key => $value) {
    putenv("{$key}={$value}"); $_ENV[$key] = $value; $_SERVER[$key] = $value;
}
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/Support/IsolatedTestCase.php';
