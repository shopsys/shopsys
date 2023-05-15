<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

interface UploadedFileFactoryInterface
{
    /**
     * @param string $entityName
     * @param int $entityId
     * @param string $type
     * @param string $temporaryFilename
     * @param string $uploadedFilename
     * @param int $position
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     */
    public function create(
        string $entityName,
        int $entityId,
        string $type,
        string $temporaryFilename,
        string $uploadedFilename,
        int $position = 0,
    ): UploadedFile;

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string $type
     * @param array $temporaryFilenames
     * @param array $uploadedFilenames
     * @param int $existingFilesCount
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public function createMultiple(
        string $entityName,
        int $entityId,
        string $type,
        array $temporaryFilenames,
        array $uploadedFilenames,
        int $existingFilesCount,
    ): array;
}
