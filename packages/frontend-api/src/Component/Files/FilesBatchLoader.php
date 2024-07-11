<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Files;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Component\Utils\Utils;

class FilesBatchLoader
{
    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrontendApiBundle\Component\Files\FileApiFacade $fileApiFacade
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly FileApiFacade $fileApiFacade,
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Files\FileBatchLoadData[] $filesBatchLoadData
     * @return \GraphQL\Executor\Promise\Promise
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

        return $this->promiseAdapter->all($this->sortAllFilesByOriginalInputData($allFiles, $filesBatchLoadData));
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Files\FileBatchLoadData[] $filesBatchLoadData
     * @param string $entityName
     * @param string $type
     * @return array<string, array|null>
     */
    protected function getFilesByEntityNameAndTypeIndexedByDataId(
        array $filesBatchLoadData,
        string $entityName,
        string $type,
    ): array {
        $entityIds = array_map(fn (FileBatchLoadData $fileBatchLoadData) => $fileBatchLoadData->getEntityId(), $filesBatchLoadData);
        $filesIndexedByEntityId = $this->fileApiFacade->getAllFilesIndexedByEntityId(
            $entityIds,
            $entityName,
            $this->domain->getLocale(),
            $type,
        );

        $files = [];

        foreach ($filesBatchLoadData as $fileBatchLoadData) {
            if (!isset($filesIndexedByEntityId[$fileBatchLoadData->getEntityId()])) {
                $files[$fileBatchLoadData->getId()] = [];

                continue;
            }
            $entityResolvedFiles = $this->getResolvedFiles($filesIndexedByEntityId[$fileBatchLoadData->getEntityId()]);
            $files[$fileBatchLoadData->getId()] = $entityResolvedFiles;
        }

        return $files;
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
    ): array {
        $sortedFiles = [];

        foreach ($filesBatchLoadData as $fileBatchLoadData) {
            if (array_key_exists($fileBatchLoadData->getId(), $allFilesIndexedByFileBatchLoadDataId) === false) {
                $sortedFiles[] = [];

                continue;
            }
            $sortedFiles[] = $allFilesIndexedByFileBatchLoadDataId[$fileBatchLoadData->getId()];
        }

        return array_values($sortedFiles);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $files
     * @return array<int, array{url: string, anchorText: string}>
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
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $file
     * @return array{url: string, anchorText: string}
     */
    protected function getResolvedFile(UploadedFile $file): array
    {
        return [
            'url' => $this->uploadedFileFacade->getUploadedFileUrl(
                $this->domain->getCurrentDomainConfig(),
                $file,
            ),
            'anchorText' => $file->getTranslatedName($this->domain->getLocale()),
        ];
    }
}
