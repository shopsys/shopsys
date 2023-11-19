<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class ImageTypeNotFoundException extends Exception implements ImageConfigException
{
    protected string $entityClass;

    protected string $imageType;

    /**
     * @param string $entityClass
     * @param string $imageType
     * @param \Exception|null $previous
     */
    public function __construct(string $entityClass, string $imageType, ?Exception $previous = null)
    {
        $this->entityClass = $entityClass;
        $this->imageType = $imageType;

        parent::__construct(
            'Image type "' . $imageType . '" not found for entity "' . $entityClass . '".',
            0,
            $previous,
        );
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @return string
     */
    public function getImageType(): string
    {
        return $this->imageType;
    }
}
