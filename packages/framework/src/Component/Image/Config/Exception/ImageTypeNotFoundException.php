<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class ImageTypeNotFoundException extends Exception implements ImageConfigException
{
    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var string
     */
    private $imageType;

    /**
     * @param string $entityClass
     * @param string $imageType
     * @param \Exception|null $previous
     */
    public function __construct($entityClass, $imageType, Exception $previous = null)
    {
        $this->entityClass = $entityClass;
        $this->imageType = $imageType;

        parent::__construct('Image type "' . $imageType . '" not found for entity "' . $entityClass . '".', 0, $previous);
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
