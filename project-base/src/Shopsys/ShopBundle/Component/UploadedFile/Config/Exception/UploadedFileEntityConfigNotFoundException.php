<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception;

use Exception;

class UploadedFileEntityConfigNotFoundException extends Exception implements UploadedFileConfigException
{
    /**
     * @var string
     */
    private $entityClassOrName;

    /**
     * @param string $entityClassOrName
     * @param \Exception|null $previous
     */
    public function __construct($entityClassOrName, Exception $previous = null)
    {
        $this->entityClassOrName = $entityClassOrName;

        parent::__construct('Not found uploaded file config for entity "' . $entityClassOrName . '".', 0, $previous);
    }

    /**
     * @return string
     */
    public function getEntityClassOrName()
    {
        return $this->entityClassOrName;
    }
}
