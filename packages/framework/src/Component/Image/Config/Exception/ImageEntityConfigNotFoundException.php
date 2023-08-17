<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageEntityConfigNotFoundException extends NotFoundHttpException implements ImageConfigException
{
    /**
     * @var string
     */
    protected $entityClassOrName;

    /**
     * @param string $entityClassOrName
     * @param \Exception|null $previous
     */
    public function __construct($entityClassOrName, ?Exception $previous = null)
    {
        $this->entityClassOrName = $entityClassOrName;

        parent::__construct('Not found image config for entity "' . $entityClassOrName . '".', $previous);
    }

    /**
     * @return string
     */
    public function getEntityClassOrName()
    {
        return $this->entityClassOrName;
    }
}
