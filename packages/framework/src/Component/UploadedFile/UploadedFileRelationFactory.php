<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;

class UploadedFileRelationFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

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
