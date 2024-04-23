<?php

namespace Sytesbook\WPWedding\Deploy\FileSystem\Filters;

class SymbolicLink extends FilterBase
{
    protected function check(string $path, array $context): bool
    {
        return is_link($path);
    }
}
