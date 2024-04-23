<?php

namespace Sytesbook\WPWedding\Deploy\Executors;

use Monolog\Logger;
use Sytesbook\WPWedding\Deploy\Utils\StringTemplate;
use Exception;

class SymlinkSetup
{
    private array $params;
    private Logger $logger;

    public function __construct(array $params, Logger $logger)
    {
        if (!isset($params['symlinks']))
        {
            throw new Exception("Parameter 'symlinks' is missing");
        }

        $this->params = $params;
        $this->logger = $logger;
    }

    public function execute(array $context): bool
    {
        foreach ($this->params['symlinks'] as $symlinkSourcePathTemplate => $symlinkTargetPathTemplate)
        {
            $symlinkSourcePath = (new StringTemplate($symlinkSourcePathTemplate))->resolve($context);
            $symlinkTargetPath = (new StringTemplate($symlinkTargetPathTemplate))->resolve($context);
            $this->logger->info("  -> Symlink {$symlinkSourcePath}");
            $this->logger->info("     Target {$symlinkTargetPath}");

            $success = symlink($symlinkTargetPath, $symlinkSourcePath);
            if (!$success)
            {
                $this->logger->error("     Symlink could not be created");
                return false;
            }
        }

        return true;
    }
}
