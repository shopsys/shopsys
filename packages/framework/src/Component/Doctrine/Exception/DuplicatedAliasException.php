<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine\Exception;

use LogicException;
use Throwable;

class DuplicatedAliasException extends LogicException
{
    /**
     * @param string $alias
     * @param string $aliasingClass
     * @param string $aliasedClass
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $alias,
        string $aliasingClass,
        string $aliasedClass,
        ?Throwable $previous = null,
    ) {
        $message = sprintf(
            'You cannot use "%s" as "%s" because this alias is already assigned to "%s" in the same QueryBuilder instance.',
            $aliasingClass,
            $alias,
            $aliasedClass,
        );

        parent::__construct(
            $message,
            0,
            $previous,
        );
    }
}
