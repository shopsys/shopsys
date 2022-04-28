<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension\Exception;

class DocBlockParserAmbiguousTagException extends DocBlockParserException
{
    /**
     * @param string $tagName
     * @param string $propertyPath
     */
    public function __construct(string $tagName, string $propertyPath)
    {
        parent::__construct(
            "Doc block should have only 1 ${tagName} tag.\nProperty: ${propertyPath}\n",
        );
    }
}
