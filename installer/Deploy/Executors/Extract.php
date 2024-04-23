<?php

namespace Sytesbook\WPWedding\Deploy\Executors;

use Monolog\Logger;
use Sytesbook\WPWedding\Deploy\Utils\StringTemplate;
use Exception;
use ZipArchive;

class Extract
{
    private array $params;
    private Logger $logger;

    public function __construct(array $params, Logger $logger)
    {
        if (!isset($params['extract']))
        {
            throw new Exception("Parameter 'extract' is missing");
        }

        $this->params = $params;
        $this->logger = $logger;
    }

    public function execute(array $context): bool
    {
        foreach ($this->params['extract'] as $zipPathTemplate => $targetFolderPathTemplate)
        {
            $zipPath = (new StringTemplate($zipPathTemplate))->resolve($context);
            $targetFolderPath = (new StringTemplate($targetFolderPathTemplate))->resolve($context);
            $this->logger->info("  -> Extract {$zipPath}");
            $this->logger->info("     Target {$targetFolderPath}");

            $zipArchive = new ZipArchive();
            $openResult = $zipArchive->open($zipPath);
            if (!$openResult)
            {
                $this->logger->error("     ZIP file could not be opened");
                return false;
            }

            $extractResult = $zipArchive->extractTo($targetFolderPath);
            if (!$extractResult)
            {
                $zipArchive->close();
                $this->logger->error("     ZIP file could not be extracted");
                return false;
            }
            
            $zipArchive->close();
        }

        return true;
    }
}
