<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\ConsumerUploadedFile;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileConfig;
use Shopsys\FrontendApiBundle\Component\CustomerUploadedFile\CustomerUploadedFileBatchLoadData;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class CustomerUploadedFileQuery extends AbstractQuery
{
    public function __construct(
        protected readonly CustomerUploadedFileConfig $customerUploadedFileConfig,
        protected readonly DataLoaderInterface $customerUploadedFilesBatchLoader,
    ) {
    }

    public function customerFilesByEntityPromiseQuery(object $entity, ?string $type): Promise
    {
        $entityConfig = $this->customerUploadedFileConfig->getUploadedFileEntityConfig($entity);

        return $this->resolveByEntityIdPromise($entity->getId(), $entityConfig->getEntityName(), $type);
    }

    protected function resolveByEntityIdPromise(
        int $entityId,
        string $entityName,
        ?string $type,
    ): Promise {
        return $this->customerUploadedFilesBatchLoader->load(
            new CustomerUploadedFileBatchLoadData(
                $entityId,
                $entityName,
                $type,
            ),
        );
    }
}
