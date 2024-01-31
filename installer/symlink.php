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

$symlinkCreationResult = true;
$symlinkTargets = scandir("{$wwwRoot}/{$deploymentFolder}/wp/");
foreach ($symlinkTargets as $symlinkTarget)
{
    if ($symlinkTarget !== '.' && $symlinkTarget !== '..')
    {
        unlink("{$wwwRoot}/{$domainFolder}/{$symlinkTarget}");
        $success = symlink("{$wwwRoot}/{$deploymentFolder}/wp/{$symlinkTarget}", "{$wwwRoot}/{$domainFolder}/{$symlinkTarget}") === true;
        $symlinkCreationResult = $symlinkCreationResult && $success;
    }
}

unlink("{$wwwRoot}/{$domainFolder}/wp-content");
$success = symlink("{$wwwRoot}/{$deploymentFolder}/src/wp-content", "{$wwwRoot}/{$domainFolder}/wp-content") === true;
$symlinkCreationResult = $symlinkCreationResult && $success;

unlink("{$wwwRoot}/{$domainFolder}/wp-config.php");
$success = symlink("{$wwwRoot}/{$deploymentFolder}/src/wp-config.php", "{$wwwRoot}/{$domainFolder}/wp-config.php") === true;
$symlinkCreationResult = $symlinkCreationResult && $success;

unlink("{$wwwRoot}/{$domainFolder}/uploads");
$success = symlink("{$wwwRoot}/{$deploymentFolder}/uploads", "{$wwwRoot}/{$domainFolder}/uploads") === true;
$symlinkCreationResult = $symlinkCreationResult && $success;

if (!$symlinkCreationResult)
{
    http_response_code(500);
    echo("1 or more symbolic links could not be created");
    exit;
}

http_response_code(200);
echo("Symbolic links created successfully.");
exit;
