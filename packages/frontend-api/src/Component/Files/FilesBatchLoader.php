<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Files;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataExtractor;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Component\Utils\Utils;

class FilesBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly Domain $domain,
        protected readonly UploadedFileConfig $uploadedFileConfig,
        protected readonly UploadedFileDataExtractor $uploadedFileDataExtractor,
    ) {
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Files\FileBatchLoadData[] $filesBatchLoadData
     */
    public function loadByBatchData(array $filesBatchLoadData): Promise
    {
        $filesBatchLoadDataByEntityNameAndType = $this->getFileBatchLoadDataArrayByEntityAndType($filesBatchLoadData);

        $allFiles = [];

        foreach ($filesBatchLoadDataByEntityNameAndType as $entityName => $dataByTypes) {
            foreach ($dataByTypes as $type => $filesBatchLoadDataOfEntityAndType) {
                $allFiles = array_merge($allFiles, $this->getFilesByEntityNameAndTypeIndexedByDataId($filesBatchLoadDataOfEntityAndType, $entityName, $type));
            }
        }

        return $this->promiseAdapter->all($this->sortAllFilesByOriginalInputData($allFiles, $filesBatchLoadData, []));
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Files\FileBatchLoadData[] $filesBatchLoadData
     */
    public function loadFirstByBatchData(array $filesBatchLoadData): Promise
    {
        $filesBatchLoadDataByEntityNameAndType = $this->getFileBatchLoadDataArrayByEntityAndType($filesBatchLoadData);

        $allFiles = [];

        foreach ($filesBatchLoadDataByEntityNameAndType as $entityName => $dataByTypes) {
            foreach ($dataByTypes as $type => $filesBatchLoadDataOfEntityAndType) {
                $allFiles = array_merge($allFiles, $this->getFirstFileByEntityNameAndTypeIndexedByDataId($filesBatchLoadDataOfEntityAndType, $entityName, $type));
            }
        }

        return $this->promiseAdapter->all($this->sortAllFilesByOriginalInputData($allFiles, $filesBatchLoadData, null));
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Files\FileBatchLoadData[] $filesBatchLoadData
     * @return array<string, array<int, array{
     *     url: string,
     *     anchorText: string,
     *     viewUrl: string|null,
     *     filesize: int|null,
     *     extension: string,
     * }>>
     */
    protected function getFilesByEntityNameAndTypeIndexedByDataId(
        array $filesBatchLoadData,
        string $entityName,
        string $type,
    ): array {
        $filesIndexedByEntityId = $this->getFilesIndexedByEntityId($filesBatchLoadData, $entityName, $type);

        $files = [];

        foreach ($filesBatchLoadData as $fileBatchLoadData) {
            $entityFiles = $filesIndexedByEntityId[$fileBatchLoadData->getEntityId()] ?? [];
            $files[$fileBatchLoadData->getId()] = $this->getResolvedFiles($entityFiles);
        }

        return $files;
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Files\FileBatchLoadData[] $filesBatchLoadData
     * @return array<string, array{
     *     url: string,
     *     anchorText: string,
     *     viewUrl: string|null,
     *     filesize: int|null,
     *     extension: string,
     * }|null>
     */
    protected function getFirstFileByEntityNameAndTypeIndexedByDataId(
        array $filesBatchLoadData,
        string $entityName,
        string $type,
    ): array {
        $filesIndexedByEntityId = $this->getFilesIndexedByEntityId($filesBatchLoadData, $entityName, $type);

        $files = [];

        foreach ($filesBatchLoadData as $fileBatchLoadData) {
            $entityFiles = $filesIndexedByEntityId[$fileBatchLoadData->getEntityId()] ?? [];
            $firstFile = array_first($entityFiles);
            $files[$fileBatchLoadData->getId()] = $firstFile !== null ? $this->getResolvedFile($firstFile) : null;
        }

        return $files;
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Files\FileBatchLoadData[] $filesBatchLoadData
     * @return array<int, \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]>
     */
    protected function getFilesIndexedByEntityId(
        array $filesBatchLoadData,
        string $entityName,
        string $type,
    ): array {
        $entityIds = array_map(static fn (FileBatchLoadData $fileBatchLoadData) => $fileBatchLoadData->getEntityId(), $filesBatchLoadData);

        $locale = $this->uploadedFileConfig->isRequiredFriendlyName($entityName, $type) ? $this->domain->getLocale() : null;

        return $this->uploadedFileFacade->getAllFilesIndexedByEntityId(
            $entityIds,
            $entityName,
            $locale,
            $type,
        );
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Files\FileBatchLoadData[] $filesBatchLoadData
     * @return \Shopsys\FrontendApiBundle\Component\Files\FileBatchLoadData[][][]
     */
    protected function getFileBatchLoadDataArrayByEntityAndType(array $filesBatchLoadData): array
    {
        $result = [];

        foreach ($filesBatchLoadData as $fileBatchLoadData) {
            $entityName = $fileBatchLoadData->getEntityName();
            $type = Utils::ifNull($fileBatchLoadData->getType(), UploadedFileTypeConfig::DEFAULT_TYPE_NAME);
            $result[$entityName][$type][] = $fileBatchLoadData;
        }

        return $result;
    }

    /**
     * @param array<string, array|null> $allFilesIndexedByFileBatchLoadDataId
     * @param \Shopsys\FrontendApiBundle\Component\Files\FileBatchLoadData[] $filesBatchLoadData
     * @return array<int, array|null>
     */
    protected function sortAllFilesByOriginalInputData(
        array $allFilesIndexedByFileBatchLoadDataId,
        array $filesBatchLoadData,
        ?array $defaultValue,
    ): array {
        $sortedFiles = [];

        foreach ($filesBatchLoadData as $fileBatchLoadData) {
            $sortedFiles[] = $allFilesIndexedByFileBatchLoadDataId[$fileBatchLoadData->getId()] ?? $defaultValue;
        }

        return $sortedFiles;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $files
     * @return array<int, array{
     *     url: string,
     *     anchorText: string,
     *     viewUrl: string|null,
     *     filesize: int|null,
     *     extension: string,
     * }>
     */
    protected function getResolvedFiles(array $files): array
    {
        $resolvedFiles = [];

        foreach ($files as $file) {
            try {
                $resolvedFiles[] = $this->getResolvedFile($file);
            } catch (FileNotFoundException $exception) {
                continue;
            }
        }

        return $resolvedFiles;
    }

    /**
     * @return array{
     *     url: string,
     *     anchorText: string,
     *     viewUrl: string|null,
     *     filesize: int|null,
     *     extension: string,
     * }
     */
    protected function getResolvedFile(UploadedFile $file): array
    {
        return $this->uploadedFileDataExtractor->extractUploadedFileData($file, $this->domain->getCurrentDomainConfig());
    }
}
