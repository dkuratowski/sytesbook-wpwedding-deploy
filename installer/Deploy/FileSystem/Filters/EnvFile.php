<?php

namespace Sytesbook\WPWedding\Deploy\FileSystem\Filters;

class EnvFile extends FilterBase
{
    protected function check(string $path, array $context): bool
    {
        return !is_link($path) && is_file($path) && str_starts_with(basename($path), '.env');
    }
}
