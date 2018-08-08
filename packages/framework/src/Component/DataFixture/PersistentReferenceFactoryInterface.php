<?php

namespace Shopsys\FrameworkBundle\Component\DataFixture;

interface PersistentReferenceFactoryInterface
{
    public function create(
        string $referenceName,
        string $entityName,
        int $entityId
    ): PersistentReference;
}
