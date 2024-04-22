<?php

namespace Sytesbook\WPWedding\Deploy\Scripts;

use Monolog\Logger;

class Cleanup
{
    private array $params;
    private Logger $logger;

    public function __construct(array $params, Logger $logger)
    {
        $this->params = $params;
        $this->logger = $logger;
    }

    public function execute(): bool
    {
        $this->logger->info("  -> Hello Cleanup", $this->params);
        return true;
    }
}
