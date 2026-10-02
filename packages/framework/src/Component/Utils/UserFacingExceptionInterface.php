<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Utils;

/**
 * Implement this interface on an exception whose reason can be shown to the user as it is,
 * e.g. a facade refusing an operation ("Default store cannot be removed").
 */
interface UserFacingExceptionInterface
{
    /**
     * Translated message safe to display in the UI
     */
    public function getUserFacingMessage(): string;
}
