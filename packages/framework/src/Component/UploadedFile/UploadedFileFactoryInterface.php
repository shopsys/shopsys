<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

interface UploadedFileFactoryInterface
{

    /**
     * @param string|null $temporaryFilename
     */
    public function create(
        string $entityName,
        int $entityId,
        ?string $temporaryFilename
    ): UploadedFile;
}
