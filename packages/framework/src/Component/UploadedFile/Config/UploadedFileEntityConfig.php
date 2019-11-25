<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config;

class UploadedFileEntityConfig
{
    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @param string $entityName
     * @param string $entityClass
     */
    public function __construct(string $entityName, string $entityClass)
    {
        $this->entityName = $entityName;
        $this->entityClass = $entityClass;
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}
