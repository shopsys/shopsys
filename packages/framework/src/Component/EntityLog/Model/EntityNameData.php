<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Model;

class EntityNameData
{
    public function __construct(
        protected readonly string $fullyQualifiedName,
        protected readonly string $shortName,
    ) {
    }

    public function getFullyQualifiedName(): string
    {
        return $this->fullyQualifiedName;
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }
}
