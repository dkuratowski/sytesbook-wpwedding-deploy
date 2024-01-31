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

$wwwRoot = dirname(dirname(dirname(__DIR__)));

$deploymentZipFile = "{$wwwRoot}/{$deploymentFolder}/deploy.zip";
if (!file_exists($deploymentZipFile))
{
    http_response_code(404);
    echo("Deployment ZIP file not found");
    exit;
}

$deploymentZipArchive = new ZipArchive();
$openResult = $deploymentZipArchive->open($deploymentZipFile);
if (!$openResult)
{
    http_response_code(500);
    echo("Deployment ZIP archive could not be opened");
    exit;
}

$extractResult = $deploymentZipArchive->extractTo("{$wwwRoot}/{$deploymentFolder}/");
if (!$extractResult)
{
    $deploymentZipArchive->close();
    http_response_code(500);
    echo("Deployment ZIP archive could not be extracted");
    exit;
}

$deploymentZipArchive->close();
unlink($deploymentZipFile);

http_response_code(200);
echo("Deployment ZIP archive extracted successfully.");
exit;
