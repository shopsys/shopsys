<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class ImageTypeNotFoundException extends Exception
{
    public function __construct(
        protected string $entityClass,
        protected string $imageType,
        ?Exception $previous = null,
    ) {
        parent::__construct(
            'Image type "' . $imageType . '" not found for entity "' . $entityClass . '".',
            0,
            $previous,
        );
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getImageType(): string
    {
        return $this->imageType;
    }
}
