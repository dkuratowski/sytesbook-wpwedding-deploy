<?php

namespace Sytesbook\WPWedding\Deploy\Executors;

use Monolog\Logger;
use Sytesbook\WPWedding\Deploy\Utils\StringTemplate;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Cleanup
{
    private array $params;
    private Logger $logger;

    public function __construct(array $params, Logger $logger)
    {
        if (!isset($params['delete']))
        {
            throw new Exception("Parameter 'delete' is missing");
        }

        $this->params = $params;
        $this->logger = $logger;
    }

    public function execute(array $context): bool
    {
        foreach ($this->params['delete'] as $pathTemplate => $filters)
        {
            $path = (new StringTemplate($pathTemplate))->resolve($context);
            $this->logger->info("  -> Cleanup {$path}");
            if (!file_exists($path) || is_link($path) || !is_dir($path))
            {
                $this->logger->error("     doesn't exist or is not a folder");
                return false;
            }

            foreach ($this->applyFilters($path, $filters, $context) as $pathToDelete)
            {
                $success = $this->delete($pathToDelete);
                if (!$success)
                {
                    return false;
                }
            }
        }

        return true;
    }

    private function applyFilters(string $path, array $filters, array $context): array
    {
        $result = [];
        foreach ($filters as $filter)
        {
            array_push($result, ...$filter->apply($path, $context));
        }
        return $result;
    }

    private function delete(string $path): bool
    {
        if (is_link($path))
        {
            return $this->deleteSymlink($path);
        }
        else
        {
            if (is_dir($path))
            {
                return $this->deleteFolder($path);
            }
            else if (is_file($path))
            {
                return $this->deleteFile($path);
            }
            else
            {
                $this->logger->error("     unknown file system object type: {$path}");
                return false;
            }
        }
    }

    private function deleteFile(string $path): bool
    {
        $success = unlink($path);
        if (!$success)
        {
            $errorInfo = error_get_last();
            $this->logger->error("     file could not be deleted: {$path}", $errorInfo ?? []);
            return false;
        }

        $this->logger->info("     file deleted: {$path}");
        return true;
    }

    private function deleteSymlink(string $path): bool
    {
        $success = unlink($path);
        if (!$success)
        {
            $errorInfo = error_get_last();
            $this->logger->error("     symlink could not be deleted: {$path}", $errorInfo ?? []);
            return false;
        }

        $this->logger->info("     symlink deleted: {$path}");
        return true;
    }

    private function deleteFolder(string $path): bool
    {
        foreach (scandir($path) as $child)
        {
            if ($child !== '.' && $child !== '..')
            {
                $success = $this->delete("{$path}/{$child}");
                if (!$success)
                {
                    return false;
                }
            }
        }

        $success = unlink($path);
        if (!$success)
        {
            $errorInfo = error_get_last();
            $this->logger->error("     folder could not be deleted: {$path}", $errorInfo ?? []);
            return false;
        }

        $this->logger->info("     folder deleted: {$path}");
        return true;
    }
}
