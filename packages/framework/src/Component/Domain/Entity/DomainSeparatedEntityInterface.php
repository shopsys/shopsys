<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain\Entity;

/**
 * Marks an entity whose every row belongs to exactly one domain.
 *
 * The implementing entity must have an integer `domainId` field mapped by Doctrine
 * (queryable as `<alias>.domainId` in DQL).
 */
interface DomainSeparatedEntityInterface
{
    /**
     * @return int
     */
    public function getDomainId();
}
