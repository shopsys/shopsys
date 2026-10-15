<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Document\Exception;

use Exception;

class ContentBlockTypeConflictException extends Exception
{
    public function __construct(string $type, string $firstClass, string $secondClass)
    {
        parent::__construct(sprintf(
            'Content block type "%s" is provided by both "%s" and "%s", a block replacing another one has to be registered instead of it, not next to it',
            $type,
            $firstClass,
            $secondClass,
        ));
    }
}
