<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\AbstractUploadedFile;

interface UploadedFileRepositoryInterface
{
    /**
     * @param int $uploadedFileId
     * @return \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface
     */
    public function getById(int $uploadedFileId): UploadedFileInterface;

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface[]
     */
    public function getUploadedFilesByEntity(string $entityName, int $entityId, string $type): array;
}
