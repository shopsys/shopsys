<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;

class ImageTypeNotFoundException extends ImageNotFoundException implements ImageConfigException
{
    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var string
     */
    protected $imageType;

    /**
     * @param string $entityClass
     * @param string $imageType
     * @param \Exception|null $previous
     */
    public function __construct($entityClass, $imageType, ?Exception $previous = null)
    {
        $this->entityClass = $entityClass;
        $this->imageType = $imageType;

        parent::__construct(
            'Image type "' . $imageType . '" not found for entity "' . $entityClass . '".',
            $previous
        );
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @return string
     */
    public function getImageType()
    {
        return $this->imageType;
    }
}
