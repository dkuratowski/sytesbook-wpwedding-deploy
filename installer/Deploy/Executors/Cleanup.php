<?php

namespace Sytesbook\WPWedding\Deploy\Executors;

use Monolog\Logger;
use Sytesbook\WPWedding\Deploy\Utils\StringTemplate;
use Exception;

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
            if (!file_exists($path) || !is_dir($path))
            {
                $this->logger->error("     doesn't exist or is not a folder");
                return false;
            }

            foreach ($this->applyFilters($path, $filters, $context) as $pathToDelete)
            {
                $baseName = basename($pathToDelete);
                if (!is_link($pathToDelete) && is_dir($pathToDelete))
                {
                    $this->deleteFolder($pathToDelete);
                }
                else if (!is_link($pathToDelete) && is_file($pathToDelete))
                {
                    $this->deleteFile($pathToDelete);
                }
                else if (is_link($pathToDelete))
                {
                    $this->deleteSymlink($pathToDelete);
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
            array_push(...$filter->apply($path, $context));
        }
        return $result;
    }

    private function deleteFolder(string $path): void
    {
        // TODO
        $this->logger->info("     folder '{$baseName}' deleted");
    }

    private function deleteFile(string $path): void
    {
        // TODO
        $this->logger->info("     file '{$baseName}' deleted");
    }

    private function deleteSymlink(string $path): void
    {
        // TODO
        $this->logger->info("     symlink '{$baseName}' deleted");
    }
}
