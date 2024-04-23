<?php

namespace Sytesbook\WPWedding\Deploy\Executors;

use Monolog\Logger;

class Extract
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
        $this->logger->info("  -> Hello Extract", $this->params);
        return true;
    }
}
