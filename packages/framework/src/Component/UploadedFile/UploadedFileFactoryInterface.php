<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

interface UploadedFileFactoryInterface
{
    /**
     * @param string $entityName
     * @param int $entityId
     * @param string $type
     * @param array $temporaryFilenames
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     */
    public function create(
        string $entityName,
        int $entityId,
        string $type,
        array $temporaryFilenames
    ): UploadedFile;
}
