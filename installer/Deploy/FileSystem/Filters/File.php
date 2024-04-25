<?php

namespace Sytesbook\WPWedding\Deploy\FileSystem\Filters;

use Sytesbook\WPWedding\Deploy\Utils\StringTemplate;

class File extends FilterBase
{
    private StringTemplate $name;

    public function __construct(string $name)
    {
        $this->name = new StringTemplate($name);
    }

    protected function check(string $path, array $context): bool
    {
        return !is_link($path) && is_file($path) && basename($path) === $this->name->resolve($context);
    }
}
