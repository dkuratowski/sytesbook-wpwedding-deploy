<?php

namespace Sytesbook\WPWedding\Deploy\Utils;

use Exception;

class StringTemplate
{
    private array $variables;
    private string $templateString;

    public function __construct(string $templateString)
    {
        $templateStringRegexMatchResult = preg_match_all('/(?:{([\w-]+)})/', $templateString, $matches);

        $this->variables = $templateStringRegexMatchResult && count($matches) === 2 ? $matches[1] : [];
        $this->templateString = $templateString;

    }

    public function resolve(array $context): string
    {
        $resolvedString = $this->templateString;
        foreach ($this->variables as $variable)
        {
            if (!isset($context[$variable]))
            {
                throw new Exception("Variable '{$variable}' could not be resolved in template string '{$this->templateString}'");
            }

            $resolvedString = str_replace('{' . $variable . '}', $context[$variable], $resolvedString);
        }
        return $resolvedString;
    }
}
