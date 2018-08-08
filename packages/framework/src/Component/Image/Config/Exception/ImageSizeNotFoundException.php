<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class ImageSizeNotFoundException extends Exception implements ImageConfigException
{
    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var string
     */
    private $sizeName;

    /**
     * @param string $entityClass
     * @param string $sizeName
     * @param \Exception|null $previous
     */
    public function __construct($entityClass, $sizeName, Exception $previous = null)
    {
        $this->entityClass = $entityClass;
        $this->sizeName = $sizeName;

        parent::__construct('Image size "' . $sizeName . '" not found for entity "' . $entityClass . '".', 0, $previous);
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getSizeName(): string
    {
        return $this->sizeName;
    }
}
