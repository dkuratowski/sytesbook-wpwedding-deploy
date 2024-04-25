<?php

namespace Sytesbook\WPWedding\Deploy\Utils;

use Exception;

class Query
{
    public static function check(array $query, array $constraints): array
    {
        foreach ($constraints as $paramName => $paramRegex)
        {
            if (!isset($query[$paramName]))
            {
                throw new Exception("Mandatory query parameter '{$paramName}' is missing");
            }

            if (isset($paramRegex))
            {
                $regexMatchResult = preg_match($paramRegex, $query[$paramName], $matches);
                if (!$regexMatchResult || count($matches) !== 1)
                {
                    throw new Exception("Mandatory query parameter '{$paramName}' has unexpected format");
                }                
            }
        }

        return $query;
    }
}
