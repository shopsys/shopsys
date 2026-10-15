<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Document\Exception;

use Exception;

class ContentBlockTypeConflictException extends Exception
{
    public function __construct(string $type, int $priority, string $firstClass, string $secondClass)
    {
        parent::__construct(sprintf(
            'Content block type "%s" is provided by "%s" and "%s" with the same priority %d, raise the priority of the one that should win',
            $type,
            $firstClass,
            $secondClass,
            $priority,
        ));
    }
}
