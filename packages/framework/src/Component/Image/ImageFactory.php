<?php

namespace Shopsys\FrameworkBundle\Component\Image;

class ImageFactory implements ImageFactoryInterface
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
    ): Image {
        return new Image($entityName, $entityId, $type, $temporaryFilename);
    }
}
