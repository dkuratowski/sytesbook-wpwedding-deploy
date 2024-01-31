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

$domainFolder = $_GET['domain_folder'] ?? null;
if (!isset($domainFolder))
{
    http_response_code(400);
    echo("Parameter 'domain_folder' is missing");
    exit;
}

$regexMatchResult = preg_match('/\A[a-z_\-\.A-Z0-9]+\Z/', $domainFolder, $matches);
if (!$regexMatchResult || count($matches) !== 1)
{
    http_response_code(400);
    echo("Parameter 'domain_folder' has unexpected format");
    exit;
}

$wwwRoot = dirname(__DIR__, 3);
$deploymentId = basename(__DIR__);

// Delete the FTP sync-state file from the deployment folder
unlink("{$wwwRoot}/{$deploymentFolder}/.ftp-deploy-sync-state.json");

// Delete every deployment ZIP files from the deployment folder (including the current one)
$deploymentFolderContent = scandir("{$wwwRoot}/{$deploymentFolder}/");
foreach ($deploymentFolderContent as $deploymentZipFile)
{
    $regexMatchResult = preg_match('/\Adeployment_([a-zA-Z0-9]+)\.zip\Z/', $deploymentZipFile, $matches);
    if ($regexMatchResult && count($matches) === 2)
    {
        unlink("{$wwwRoot}/{$deploymentFolder}/{$deploymentZipFile}");
    }
}

// Delete the installer scripts from the domain folder
unlink("{$wwwRoot}/{$domainFolder}/installer");

http_response_code(200);
echo("Post-install cleanup executed successfully.");
exit;
