<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Files;

use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRepository;

class FileApiFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRepository $fileRepository
     * @param \Shopsys\FrontendApiBundle\Component\Files\FileApiRepository $fileApiRepository
     */
    public function __construct(
        protected readonly UploadedFileRepository $fileRepository,
        protected readonly FileApiRepository $fileApiRepository,
    ) {
    }

    /**
     * @param int[] $entityIds
     * @param string $entityName
     * @param string $locale
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[][]
     */
    public function getAllFilesIndexedByEntityId(
        array $entityIds,
        string $entityName,
        string $locale,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): array {
        return $this->fileApiRepository->getAllFilesIndexedByEntityId($entityIds, $entityName, $locale, $type);
    }
}
