<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;

class ImageTypeNotFoundException extends ImageNotFoundException
{
    public function __construct(
        protected string $entityClass,
        protected string $imageType,
        ?Exception $previous = null,
    ) {
        parent::__construct(
            'Image type "' . $imageType . '" not found for entity "' . $entityClass . '".',
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
