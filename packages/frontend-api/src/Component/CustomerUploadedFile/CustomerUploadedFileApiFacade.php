<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\CustomerUploadedFile;

use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;

class CustomerUploadedFileApiFacade
{
    public function __construct(
        protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade,
        protected readonly CustomerUploadedFileApiRepository $customerUploadedFileApiRepository,
    ) {
    }

    /**
     * @param int[] $entityIds
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile[][]
     */
    public function getAllCustomerUploadedFilesIndexedByEntityId(
        array $entityIds,
        string $entityName,
        ?string $type,
    ): array {
        return $this->customerUploadedFileApiRepository->getAllCustomerUploadedFilesIndexedByEntityId($entityIds, $entityName, $type);
    }
}
