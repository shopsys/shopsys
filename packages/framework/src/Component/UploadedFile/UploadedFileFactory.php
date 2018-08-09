<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

class UploadedFileFactory implements UploadedFileFactoryInterface
{

    /**
     * @param string|null $temporaryFilename
     */
    public function create(
        string $entityName,
        int $entityId,
        ?string $temporaryFilename
    ): UploadedFile {
        return new UploadedFile($entityName, $entityId, $temporaryFilename);
    }
}
