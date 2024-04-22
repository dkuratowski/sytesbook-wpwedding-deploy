<?php

// Load Composer's autoloader
require_once(__DIR__ . '/vendor/autoload.php');

$logger = new Monolog\Logger(
    'wpwedding-deploy',
    [new Monolog\Handler\StreamHandler('php://output', Monolog\Level::Debug)]
);

$logger->info('Loading installer descriptor file');

if (!file_exists(__DIR__ . '/installer.json'))
{
    http_response_code(500);
    $logger->error('Installer descriptor file not found');
    exit;
}

$descriptorContent = file_get_contents(__DIR__ . '/installer.json');
if ($descriptorContent === false)
{
    http_response_code(500);
    $logger->error('Unable to read installer descriptor file');
    exit;
}

$descriptor = json_decode($descriptorContent, true);
if (!isset($descriptor))
{
    http_response_code(500);
    $logger->error('Unable to parse JSON content from the installer descriptor file');
    exit;
}

$logger->info('Installer descriptor file loaded');

try
{
    $installer = new Sytesbook\WPWedding\Deploy\Installer($descriptor, $logger);
    $success = $installer->execute();

    if (!$success)
    {
        http_response_code(500);
        $logger->error('Installer failed');
        exit;
    }

    $logger->info('Installer executed successfully');
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
