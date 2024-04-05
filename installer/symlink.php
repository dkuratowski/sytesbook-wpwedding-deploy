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

if (!file_exists("{$wwwRoot}/{$deploymentFolder}/wp"))
{
    http_response_code(500);
    echo("Preceding intaller script not executed");
    exit;
}

$symlinkCreationResult = true;

// Create symbolic links into the deployment folder
unlink("{$wwwRoot}/{$deploymentFolder}/wp/migrations");
unlink("{$wwwRoot}/{$deploymentFolder}/wp/uploads");
unlink("{$wwwRoot}/{$deploymentFolder}/wp/wp-content");
unlink("{$wwwRoot}/{$deploymentFolder}/wp/wp-config.php");
$success = symlink("{$wwwRoot}/{$deploymentFolder}/migrations", "{$wwwRoot}/{$deploymentFolder}/wp/migrations") === true;
$symlinkCreationResult = $symlinkCreationResult && $success;
$success = symlink("{$wwwRoot}/{$deploymentFolder}/uploads", "{$wwwRoot}/{$deploymentFolder}/wp/uploads") === true;
$symlinkCreationResult = $symlinkCreationResult && $success;
$success = symlink("{$wwwRoot}/{$deploymentFolder}/src/wp-content", "{$wwwRoot}/{$deploymentFolder}/wp/wp-content") === true;
$symlinkCreationResult = $symlinkCreationResult && $success;
$success = symlink("{$wwwRoot}/{$deploymentFolder}/src/wp-config.php", "{$wwwRoot}/{$deploymentFolder}/wp/wp-config.php") === true;
$symlinkCreationResult = $symlinkCreationResult && $success;

// Create symbolic links into the domain folder
unlink("{$wwwRoot}/{$domainFolder}/uploads");
unlink("{$wwwRoot}/{$domainFolder}/wp-admin");
unlink("{$wwwRoot}/{$domainFolder}/index.php");
$success = symlink("{$wwwRoot}/{$deploymentFolder}/uploads", "{$wwwRoot}/{$domainFolder}/uploads") === true;
$symlinkCreationResult = $symlinkCreationResult && $success;
$success = symlink("{$wwwRoot}/{$deploymentFolder}/wp/wp-admin", "{$wwwRoot}/{$domainFolder}/wp-admin") === true;
$symlinkCreationResult = $symlinkCreationResult && $success;
$success = symlink("{$wwwRoot}/{$deploymentFolder}/wp/wp-content", "{$wwwRoot}/{$domainFolder}/wp-content") === true;
$symlinkCreationResult = $symlinkCreationResult && $success;
$success = symlink("{$wwwRoot}/{$deploymentFolder}/wp/wp-includes", "{$wwwRoot}/{$domainFolder}/wp-includes") === true;
$symlinkCreationResult = $symlinkCreationResult && $success;
$success = symlink("{$wwwRoot}/{$deploymentFolder}/wp/wp-login.php", "{$wwwRoot}/{$domainFolder}/wp-login.php") === true;
$symlinkCreationResult = $symlinkCreationResult && $success;
$success = symlink("{$wwwRoot}/{$deploymentFolder}/wp/index.php", "{$wwwRoot}/{$domainFolder}/index.php") === true;
$symlinkCreationResult = $symlinkCreationResult && $success;
$success = symlink("{$wwwRoot}/{$deploymentFolder}/wp/.htaccess", "{$wwwRoot}/{$domainFolder}/.htaccess") === true;
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
