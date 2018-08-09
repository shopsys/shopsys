<?php

namespace Shopsys\FrameworkBundle\Component\Image;

interface ImageFactoryInterface
{

    /**
     * @param string|null $type
     * @param string|null $temporaryFilename
     */
    public function create(
        string $entityName,
        int $entityId,
        ?string $type,
        ?string $temporaryFilename
    ): Image;
}
