<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Document\Exception;

use Exception;

class UnknownContentBlockTypeException extends Exception
{
    public function __construct(string $type)
    {
        parent::__construct(sprintf('Content block of type "%s" is not registered', $type));
    }
}
