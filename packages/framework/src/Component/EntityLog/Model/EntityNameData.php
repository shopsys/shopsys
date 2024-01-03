<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Model;

class EntityNameData
{
    /**
     * @param string $fullyQualifiedName
     * @param string $shortName
     */
    public function __construct(
        protected readonly string $fullyQualifiedName,
        protected readonly string $shortName,
    ) {
    }

    /**
     * @return string
     */
    public function getFullyQualifiedName(): string
    {
        return $this->fullyQualifiedName;
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->shortName;
    }
}
