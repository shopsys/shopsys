<?php

namespace Shopsys\FrameworkBundle\Component\Image;

class ImageFactory implements ImageFactoryInterface
{
    public function create(
        string $entityName,
        int $entityId,
        ?string $type,
        ?string $temporaryFilename
    ): Image {
        return new Image($entityName, $entityId, $type, $temporaryFilename);
    }
}
