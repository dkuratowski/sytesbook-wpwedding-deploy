<?php

// Load Composer's autoloader
require_once(__DIR__ . '/vendor/autoload.php');

$logger = new Monolog\Logger(
    'wpwedding-deploy',
    [new Monolog\Handler\StreamHandler('php://output', Monolog\Level::Debug)]
);

try
{
    $query = Sytesbook\WPWedding\Deploy\Utils\Query::check(
        $_GET,    
        // Constraints
        [
            'script' => '/\A[a-z_\-\.A-Z0-9]+\Z/',
            'deployment_folder' => '/\A[a-z_\-\.A-Z0-9]+\Z/',
            'domain_folder' => '/\A[a-z_\-\.A-Z0-9]+\Z/'
        ],
    );

    $logger->info("Loading installer script '{$query['script']}'");

    if (!file_exists(__DIR__ . "/scripts/{$query['script']}.php"))
    {
        http_response_code(500);
        $logger->error("Installer script '{$query['script']}' not found");
        exit;
    }

    $script = require __DIR__ . "/scripts/{$query['script']}.php";
    $logger->info("Installer script '{$query['script']}' loaded");

    $wwwRoot = dirname(__DIR__, 3);
    $packageId = basename(__DIR__);

    $installer = new Sytesbook\WPWedding\Deploy\Installer($script, $logger);
    $success = $installer->execute([
        'package_id' => $packageId,
        'deployment_folder' => "{$wwwRoot}/{$query['deployment_folder']}",
        'domain_folder' => "{$wwwRoot}/{$query['domain_folder']}",
    ]);

    if (!$success)
    {
        http_response_code(500);
        $logger->error("Installer script '{$query['script']}' failed");
        exit;
    }

    $logger->info("Installer script '{$query['script']}' executed successfully");
    http_response_code(200);
    exit;
}
catch (Throwable $ex)
{
    http_response_code(500);
    $logger->error($ex->getMessage(), [
        'file' => $ex->getFile(),
        'line' => $ex->getLine(),
        'trace' => $ex->getTrace()
    ]);
    exit;
}
