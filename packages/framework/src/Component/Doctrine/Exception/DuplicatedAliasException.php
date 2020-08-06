<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine\Exception;

use LogicException;
use Throwable;

class DuplicatedAliasException extends LogicException
{
    /**
     * @param string $alias
     * @param \Throwable|null $previous
     */
    public function __construct(string $alias, ?Throwable $previous = null)
    {
        $message = sprintf(
            'Alias "%s" is already assigned to different entity.',
            $alias
        );
        parent::__construct(
            $message,
            0,
            $previous
        );
    }
}
