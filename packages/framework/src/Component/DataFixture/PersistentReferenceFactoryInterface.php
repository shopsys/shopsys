<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DataFixture;

interface PersistentReferenceFactoryInterface
{
    /**
     * @param string $referenceName
     * @param string $entityName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReference
     */
    public function create(
        string $referenceName,
        string $entityName,
        int $entityId,
    ): PersistentReference;
}
