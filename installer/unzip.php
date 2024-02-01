<?php

$deploymentFolder = $_GET['deployment_folder'] ?? null;
if (!isset($deploymentFolder))
{
    http_response_code(400);
    echo("Parameter 'deployment_folder' is missing");
    exit;
}

$regexMatchResult = preg_match('/\A[a-z_\-\.A-Z0-9]+\Z/', $deploymentFolder, $matches);
if (!$regexMatchResult || count($matches) !== 1)
{
    http_response_code(400);
    echo("Parameter 'deployment_folder' has unexpected format");
    exit;
}

$wwwRoot = dirname(__DIR__, 3);
$packageId = basename(__DIR__);

$packageZipFile = "{$wwwRoot}/{$deploymentFolder}/package_{$packageId}.zip";
if (!file_exists($packageZipFile))
{
    http_response_code(404);
    echo("Package ZIP file not found");
    exit;
}

$packageZipArchive = new ZipArchive();
$openResult = $packageZipArchive->open($packageZipFile);
if (!$openResult)
{
    http_response_code(500);
    echo("Package ZIP file could not be opened");
    exit;
}

$extractResult = $packageZipArchive->extractTo("{$wwwRoot}/{$deploymentFolder}/");
if (!$extractResult)
{
    $packageZipArchive->close();
    http_response_code(500);
    echo("Package ZIP file could not be extracted");
    exit;
}

$packageZipArchive->close();

http_response_code(200);
echo("Package ZIP file extracted successfully.");
exit;
