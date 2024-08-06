<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\ConsumerUploadedFile;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileConfig;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Component\CustomerUploadedFile\CustomerUploadedFileApiFacade;
use Shopsys\FrontendApiBundle\Component\CustomerUploadedFile\CustomerUploadedFileBatchLoadData;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class CustomerUploadedFileQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade $customerUploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileConfig $customerUploadedFileConfig
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Component\CustomerUploadedFile\CustomerUploadedFileApiFacade $customerUploadedFileApiFacade
     * @param \Overblog\DataLoader\DataLoaderInterface $customerUploadedFilesBatchLoader
     */
    public function __construct(
        protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade,
        protected readonly CustomerUploadedFileConfig $customerUploadedFileConfig,
        protected readonly Domain $domain,
        protected readonly CustomerUploadedFileApiFacade $customerUploadedFileApiFacade,
        protected readonly DataLoaderInterface $customerUploadedFilesBatchLoader,
    ) {
    }

    /**
     * @param object $entity
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function customerFilesByEntityPromiseQuery(object $entity, ?string $type): Promise
    {
        $entityConfig = $this->customerUploadedFileConfig->getCustomerUploadedFileEntityConfig($entity);

        return $this->resolveByEntityIdPromise($entity->getId(), $entityConfig->getEntityName(), $type);
    }

    /**
     * @param int $entityId
     * @param string $entityName
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
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
