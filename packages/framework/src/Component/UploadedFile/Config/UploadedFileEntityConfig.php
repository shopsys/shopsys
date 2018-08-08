<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config;

class UploadedFileEntityConfig
{
    /**
     * @var string
     */
    private $entityName;

    /**
     * @var string
     */
    private $entityClass;
    
    public function __construct(string $entityName, string $entityClass)
    {
        $this->entityName = $entityName;
        $this->entityClass = $entityClass;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}
