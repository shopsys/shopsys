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
     * @param \Exception|null $previous
     */
    public function __construct(string $entityClassOrName, Exception $previous = null)
    {
        $this->entityClassOrName = $entityClassOrName;

        parent::__construct('Not found uploaded file config for entity "' . $entityClassOrName . '".', 0, $previous);
    }

    public function getEntityClassOrName(): string
    {
        return $this->entityClassOrName;
    }
}
