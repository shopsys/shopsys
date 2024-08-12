<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;

class UploadedFileRelationFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @param int $position
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRelation
     */
    public function create(
        string $entityName,
        int $entityId,
        UploadedFile $uploadedFile,
        int $position = 0,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): UploadedFileRelation {
        $entityClassName = $this->entityNameResolver->resolve(UploadedFileRelation::class);

        return new $entityClassName($entityName, $entityId, $uploadedFile, $position, $type);
    }
}
