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

// Delete files and folders of the already existing deployment from the deployment folder
unlink("{$wwwRoot}/{$deploymentFolder}/src");
unlink("{$wwwRoot}/{$deploymentFolder}/wp");
unlink("{$wwwRoot}/{$deploymentFolder}/vendor");
unlink("{$wwwRoot}/{$deploymentFolder}/.env");

// Delete the FTP sync-state file from the deployment folder
unlink("{$wwwRoot}/{$deploymentFolder}/.ftp-deploy-sync-state.json");

// Delete every earlier deployment ZIP files from the deployment folder
$deploymentFolderContent = scandir("{$wwwRoot}/{$deploymentFolder}/");
foreach ($deploymentFolderContent as $deploymentZipFile)
{
    $regexMatchResult = preg_match('/\Adeployment_([a-zA-Z0-9]+)\.zip\Z/', $deploymentZipFile, $matches);
    if ($regexMatchResult && count($matches) === 2 && $matches[1] !== $deploymentId)
    {
        unlink("{$wwwRoot}/{$deploymentFolder}/{$deploymentZipFile}");
    }
}

// Delete every symbolic link of the already existing deployment from the domain folder
$domainFolderContent = scandir("{$wwwRoot}/{$domainFolder}/");
foreach ($domainFolderContent as $symlink)
{
    if ($symlink !== '.' && $symlink !== '..' && $symlink !== 'installer' && is_link("{$wwwRoot}/{$domainFolder}/{$symlink}"))
    {
        unlink("{$wwwRoot}/{$domainFolder}/{$symlink}");
    }
}

http_response_code(200);
echo("Pre-install cleanup executed successfully.");
exit;
