<?php

namespace Sytesbook\WPWedding\Deploy\FileSystem\Filters;

abstract class FilterBase
{
    public function apply(string $path, array $context): array
    {
        $result = [];
        foreach (scandir($path) as $content)
        {
            if ($content !== '.' && $content !== '..' && $this->check("{$path}/{$content}", $context))
            {
                $result[] = "{$path}/{$content}";
            }
        }
        return $result;
    }

    protected abstract function check(string $path, array $context): bool;
}
