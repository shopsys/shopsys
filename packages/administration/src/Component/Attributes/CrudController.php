<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class CrudController
{
    public function __construct(
        public string $entityClass,
    ) {
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}
