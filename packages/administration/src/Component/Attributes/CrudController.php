<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class CrudController
{
    /**
     * @param string $entityClass
     * @param string|null $parent
     */
    public function __construct(
        public string $entityClass,
        public ?string $parent = null,
    ) {
    }
}
