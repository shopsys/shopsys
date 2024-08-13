<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\CustomerUploadedFile;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Exception\CustomerFileNotFoundException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\Utils\Utils;

class CustomerUploadedFilesBatchLoader
{
    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrontendApiBundle\Component\CustomerUploadedFile\CustomerUploadedFileApiFacade $customerUploadedFileApiFacade
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade $customerUploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly CustomerUploadedFileApiFacade $customerUploadedFileApiFacade,
        protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\CustomerUploadedFile\CustomerUploadedFileBatchLoadData[] $customerUploadedFileBatchLoadData
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByBatchData(array $customerUploadedFileBatchLoadData): Promise
    {
        $customerUploadedFileBatchLoadDataByEntityNameAndType = $this->getCustomerUploadedFileLoadDataArrayByEntityAndType($customerUploadedFileBatchLoadData);

        $allCustomerUploadedFiles = [];

        foreach ($customerUploadedFileBatchLoadDataByEntityNameAndType as $entityName => $dataByTypes) {
            foreach ($dataByTypes as $type => $customerUploadedFileBatchLoadDataOfEntityAndType) {
                $allCustomerUploadedFiles = array_merge(
                    $allCustomerUploadedFiles,
                    $this->getCustomerUploadedFilesByEntityNameAndTypeIndexedByDataId(
                        $customerUploadedFileBatchLoadDataOfEntityAndType,
                        $entityName,
                        $type,
                    ),
                );
            }
        }

        return $this->promiseAdapter->all($this->sortAllByOriginalInputData($allCustomerUploadedFiles, $customerUploadedFileBatchLoadData));
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\CustomerUploadedFile\CustomerUploadedFileBatchLoadData[] $customerUploadedFileBatchLoadData
     * @param string $entityName
     * @param string $type
     * @return array<string, array|null>
     */
    protected function getCustomerUploadedFilesByEntityNameAndTypeIndexedByDataId(
        array $customerUploadedFileBatchLoadData,
        string $entityName,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): array {
        $entityIds = array_map(fn (CustomerUploadedFileBatchLoadData $customerUploadedFileBatchLoadData) => $customerUploadedFileBatchLoadData->getEntityId(), $customerUploadedFileBatchLoadData);
        $customerUploadedFilesIndexedByEntityId = $this->customerUploadedFileApiFacade->getAllCustomerUploadedFilesIndexedByEntityId($entityIds, $entityName, $type);

        $customerUploadedFiles = [];

        foreach ($customerUploadedFileBatchLoadData as $fileBatchLoadData) {
            if (!isset($customerUploadedFilesIndexedByEntityId[$fileBatchLoadData->getEntityId()])) {
                $customerUploadedFiles[$fileBatchLoadData->getId()] = [];

                continue;
            }
            $entityResolvedCustomerUploadedFiles = $this->getResolvedFiles($customerUploadedFilesIndexedByEntityId[$fileBatchLoadData->getEntityId()]);
            $customerUploadedFiles[$fileBatchLoadData->getId()] = $entityResolvedCustomerUploadedFiles;
        }

        return $customerUploadedFiles;
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\CustomerUploadedFile\CustomerUploadedFileBatchLoadData[] $customerUploadedFileBatchLoadData
     * @return \Shopsys\FrontendApiBundle\Component\CustomerUploadedFile\CustomerUploadedFileBatchLoadData[][][]
     */
    protected function getCustomerUploadedFileLoadDataArrayByEntityAndType(
        array $customerUploadedFileBatchLoadData,
    ): array {
        $result = [];

        foreach ($customerUploadedFileBatchLoadData as $fileBatchLoadData) {
            $entityName = $fileBatchLoadData->getEntityName();
            $type = Utils::ifNull($fileBatchLoadData->getType(), UploadedFileTypeConfig::DEFAULT_TYPE_NAME);
            $result[$entityName][$type][] = $fileBatchLoadData;
        }

        return $result;
    }

    /**
     * @param array<string, array|null> $allCustomerUploadedFileIndexedByBatchLoadDataId
     * @param \Shopsys\FrontendApiBundle\Component\CustomerUploadedFile\CustomerUploadedFileBatchLoadData[] $customerUploadedFileBatchLoadData
     * @return array<int, array|null>
     */
    protected function sortAllByOriginalInputData(
        array $allCustomerUploadedFileIndexedByBatchLoadDataId,
        array $customerUploadedFileBatchLoadData,
    ): array {
        $sortedCustomerUploadedFiles = [];

        foreach ($customerUploadedFileBatchLoadData as $fileBatchLoadData) {
            if (array_key_exists($fileBatchLoadData->getId(), $allCustomerUploadedFileIndexedByBatchLoadDataId) === false) {
                $sortedCustomerUploadedFiles[] = [];

                continue;
            }

            $sortedCustomerUploadedFiles[] = $allCustomerUploadedFileIndexedByBatchLoadDataId[$fileBatchLoadData->getId()];
        }

        return array_values($sortedCustomerUploadedFiles);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile[] $customerUploadedFiles
     * @return array<int, array{url: string, anchorText: string|null}>
     */
    protected function getResolvedFiles(array $customerUploadedFiles): array
    {
        $resolvedCustomerUploadedFiles = [];

        foreach ($customerUploadedFiles as $customerUploadedFile) {
            try {
                $resolvedCustomerUploadedFiles[] = $this->getResolvedFile($customerUploadedFile);
            } catch (CustomerFileNotFoundException $exception) {
                continue;
            }
        }

        return $resolvedCustomerUploadedFiles;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile $customerUploadedFile
     * @return array{url: string, anchorText: string|null}
     */
    protected function getResolvedFile(CustomerUploadedFile $customerUploadedFile): array
    {
        return [
            'url' => $this->customerUploadedFileFacade->getCustomerUploadedFileViewUrl(
                $this->domain->getCurrentDomainConfig(),
                $customerUploadedFile,
            ),
            'anchorText' => $customerUploadedFile->getNameWithExtension(),
        ];
    }
}
