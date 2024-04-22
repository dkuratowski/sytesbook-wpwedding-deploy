<?php

namespace Sytesbook\WPWedding\Deploy\Utils;

use Exception;

class StringTemplate
{
    public function __construct(string $templateString)
    {
        // TODO: parse template string
    }

    public function resolve(array $context): string
    {
        throw new Exception('not implemented');
    }
}
