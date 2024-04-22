<?php

namespace Sytesbook\WPWedding\Deploy\Scripts;

use Monolog\Logger;

class SymlinkSetup
{
    private array $params;
    private Logger $logger;

    public function __construct(array $params, Logger $logger)
    {
        $this->params = $params;
        $this->logger = $logger;
    }

    public function execute(array $context): bool
    {
        $this->logger->info("  -> Hello SymlinkSetup", $this->params);
        return true;
    }
}
