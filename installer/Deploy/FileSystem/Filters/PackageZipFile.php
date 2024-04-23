<?php

namespace Sytesbook\WPWedding\Deploy\FileSystem\Filters;

use Sytesbook\WPWedding\Deploy\Utils\StringTemplate;

class PackageZipFile extends FilterBase
{
    private array $excludedPackages;

    public function __construct(array $excludedPackages = [])
    {
        $this->excludedPackages = array_map(fn (string $packageId) => new StringTemplate($packageId), $excludedPackages);
    }

    protected function check(string $path, array $context): bool
    {
        if (is_link($path) || !is_file($path))
        {
            return false;
        }

        $excludedPackages = array_map(
            fn (StringTemplate $packageId) => $packageId->resolve($context),
            $this->excludedPackages
        );

        $regexMatchResult = preg_match('/\Apackage_([a-zA-Z0-9]+)\.zip\Z/', basename($path), $matches);
        if (!$regexMatchResult || count($matches) !== 2)
        {
            return false;
        }

        $packageId = $matches[1];
        return !in_array($packageId, $excludedPackages, true);
    }
}
