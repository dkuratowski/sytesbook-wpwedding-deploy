<?php

namespace Sytesbook\WPWedding\Deploy;

use Monolog\Logger;
use ReflectionClass;

class Installer
{
    private array $script;
    private Logger $logger;

    public function __construct(array $script, Logger $logger)
    {
        $this->script = $script;
        $this->logger = $logger;
    }

    public function execute(array $context): bool
    {
        $this->logger->info("Installer: {$this->script['title']}");
        foreach ($this->script['steps'] as $installerStep)
        {
            $this->logger->info("* {$installerStep['title']}");
            if (!class_exists($installerStep['executor']))
            {
                $this->logger->error("  -> Class '{$installerStep['executor']}' doesn't exist");
                return false;
            }

            $executorType = new ReflectionClass($installerStep['executor']);
            $executor = $executorType->newInstance($installerStep['params'] ?? [], $this->logger);
            $success = $executor->execute($context);
            if (!$success)
            {
                $this->logger->error("  -> Step failed");
                return false;
            }
        }

        return true;
    }
}
