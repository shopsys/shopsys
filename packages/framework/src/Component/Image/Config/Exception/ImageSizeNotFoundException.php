<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class ImageSizeNotFoundException extends Exception implements ImageConfigException
{
    /**
     * @var class-string
     */
    protected $entityClass;

    /**
     * @var string
     */
    protected $sizeName;

    /**
     * @param class-string $entityClass
     * @param string $sizeName
     * @param \Exception|null $previous
     */
    public function __construct(string $entityClass, string $sizeName, ?Exception $previous = null)
    {
        $this->entityClass = $entityClass;
        $this->sizeName = $sizeName;

        parent::__construct(
            'Image size "' . $sizeName . '" not found for entity "' . $entityClass . '".',
            0,
            $previous
        );
    }

    /**
     * @return class-string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @return string
     */
    public function getSizeName(): string
    {
        return $this->sizeName;
    }
}
