<?php

namespace Sytesbook\WPWedding\Deploy;

use Monolog\Logger;
use ReflectionClass;

class Installer
{
    private array $descriptor;
    private Logger $logger;

    public function __construct(array $descriptor, Logger $logger)
    {
        $this->descriptor = $descriptor;
        $this->logger = $logger;
    }

    public function execute(): bool
    {
        $this->logger->info("Installer: {$this->descriptor['title']}");
        foreach ($this->descriptor['steps'] as $installerStep)
        {
            $this->logger->info("* {$installerStep['title']}");
            if (!class_exists($installerStep['script']))
            {
                $this->logger->error("  -> Class '{$installerStep['script']}' doesn't exist");
                return false;
            }

            $scriptType = new ReflectionClass($installerStep['script']);
            $script = $scriptType->newInstance($installerStep['params'] ?? [], $this->logger);
            $success = $script->execute();
            if (!$success)
            {
                $this->logger->error("  -> Step failed");
                return false;
            }
        }

        return true;
    }
}
