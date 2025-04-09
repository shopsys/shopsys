<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class CrudController
{
    /**
     * @param string $entityClass
     */
    public function __construct(
        public string $entityClass,
    ) {
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}
