<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Utils;

/**
 * Implement this interface to provide a human-readable representation of class.
 * Useful for displaying class identifiers in UI, logs, or admin panels (e.g. "Order #222").
 */
interface Presentable
{
    public function toHumanReadable(): string;
}
