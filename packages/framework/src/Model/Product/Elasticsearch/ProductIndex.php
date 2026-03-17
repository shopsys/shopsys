<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;

class ProductIndex extends AbstractIndex
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ProductExportRepository $productExportRepository,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTotalCount(int $domainId): int
    {
        return $this->productExportRepository->getProductTotalCountForDomain($domainId);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getExportDataForIds(
        int $domainId,
        array $restrictToIds,
        array $fields = self::ALL_FIELDS,
    ): array {
        return $this->productExportRepository->getProductsDataForIds(
            $domainId,
            $this->domain->getDomainConfigById($domainId)->getLocale(),
            $restrictToIds,
            $fields,
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getExportDataForBatch(
        int $domainId,
        int $lastProcessedId,
        int $batchSize,
        array $fields = self::ALL_FIELDS,
    ): array {
        return $this->productExportRepository->getProductsData(
            $domainId,
            $this->domain->getDomainConfigById($domainId)->getLocale(),
            $lastProcessedId,
            $batchSize,
            $fields,
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getName(): string
    {
        return 'product';
    }
}
