<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

abstract class AbstractChangeSetFormatter
{
    protected function formatCode(mixed $value): string
    {
        return sprintf(
            '<code>%s</code>',
            htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        );
    }
}
