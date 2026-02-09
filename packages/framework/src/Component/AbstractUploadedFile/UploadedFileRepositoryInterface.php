<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\AbstractUploadedFile;

interface UploadedFileRepositoryInterface
{
    public function getById(int $uploadedFileId): UploadedFileInterface;

    /**
     * @return \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface[]
     */
    public function getUploadedFilesByEntity(string $entityName, int $entityId, string $type): array;
}
