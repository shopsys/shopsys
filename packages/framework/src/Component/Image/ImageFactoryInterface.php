<?php

namespace Shopsys\FrameworkBundle\Component\Image;

interface ImageFactoryInterface
{

    public function create(
        string $entityName,
        int $entityId,
        ?string $type,
        ?string $temporaryFilename
    ): Image;
}
