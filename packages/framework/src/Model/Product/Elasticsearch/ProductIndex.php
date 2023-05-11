<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexSupportChangesOnlyInterface;

class ProductIndex extends AbstractIndex implements IndexSupportChangesOnlyInterface
{
    protected Domain $domain;

    protected ProductExportRepository $productExportRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository $productExportRepository
     */
    public function __construct(
        Domain $domain,
        ProductExportRepository $productExportRepository
    ) {
        $this->domain = $domain;
        $this->productExportRepository = $productExportRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount(int $domainId): int
    {
        return $this->productExportRepository->getProductTotalCountForDomain($domainId);
    }

    /**
     * {@inheritdoc}
     */
    public function getChangedCount(int $domainId): int
    {
        return $this->productExportRepository->getProductChangedCount();
    }

    /**
     * {@inheritdoc}
     */
    public function getChangedIdsForBatch(int $domainId, int $lastProcessedId, int $batchSize): array
    {
        return $this->productExportRepository->getProductIdsForChanged($lastProcessedId, $batchSize);
    }

    /**
     * {@inheritdoc}
     */
    public function getExportDataForIds(int $domainId, array $restrictToIds): array
    {
        return $this->productExportRepository->getProductsDataForIds(
            $domainId,
            $this->domain->getDomainConfigById($domainId)->getLocale(),
            $restrictToIds
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getExportDataForBatch(int $domainId, int $lastProcessedId, int $batchSize): array
    {
        return $this->productExportRepository->getProductsData(
            $domainId,
            $this->domain->getDomainConfigById($domainId)->getLocale(),
            $lastProcessedId,
            $batchSize
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getName(): string
    {
        return 'product';
    }
}
