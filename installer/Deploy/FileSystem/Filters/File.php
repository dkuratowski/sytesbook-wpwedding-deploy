<?php

namespace Sytesbook\WPWedding\Deploy\FileSystem\Filters;

use Sytesbook\WPWedding\Deploy\Utils\StringTemplate;

class File
{
    private StringTemplate $name;

    public function __construct(string $name)
    {
        $this->name = new StringTemplate($name);
    }
}
