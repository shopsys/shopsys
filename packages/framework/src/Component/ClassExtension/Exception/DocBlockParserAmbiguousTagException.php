<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension\Exception;

class DocBlockParserAmbiguousTagException extends DocBlockParserException
{
    public function __construct(string $tagName, string $propertyPath)
    {
        parent::__construct(
            "Doc block should have only 1 {$tagName} tag.\nProperty: {$propertyPath}\n",
        );
    }
}
