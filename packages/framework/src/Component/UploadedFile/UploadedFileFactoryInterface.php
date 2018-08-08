<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

interface UploadedFileFactoryInterface
{
    public function create(
        string $entityName,
        int $entityId,
        ?string $temporaryFilename
    ): UploadedFile;
}
