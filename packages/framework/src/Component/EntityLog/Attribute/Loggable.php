<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Loggable
{
    public const STRATEGY_EXCLUDE_ALL = 'exclude_all';
    public const STRATEGY_INCLUDE_ALL = 'include_all';

    /**
     * @param string $strategy
     */
    public function __construct(
        protected readonly string $strategy = self::STRATEGY_INCLUDE_ALL,
    ) {
    }

    /**
     * @return string
     */
    public function getStrategy(): string
    {
        return $this->strategy;
    }
}
