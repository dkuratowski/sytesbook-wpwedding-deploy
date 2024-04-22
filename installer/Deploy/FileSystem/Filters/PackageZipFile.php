<?php

namespace Sytesbook\WPWedding\Deploy\FileSystem\Filters;

use Sytesbook\WPWedding\Deploy\Utils\StringTemplate;

class PackageZipFile
{
    private array $includedPackages;
    private array $excludedPackages;

    public function __construct(array $includedPackages, array $excludedPackages)
    {
        $this->includedPackages = array_map(fn (string $packageId) => new StringTemplate($packageId), $includedPackages);
        $this->excludedPackages = array_map(fn (string $packageId) => new StringTemplate($packageId), $excludedPackages);
    }
}
