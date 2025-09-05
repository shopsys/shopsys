<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Attribute;

use Attribute;

/**
 * Sets the default role for all security attributes in the class
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ForRole
{
    /**
     * @param string $role
     */
    public function __construct(
        public string $role,
    ) {
    }
}
