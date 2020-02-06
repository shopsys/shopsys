<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;
use Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportWithFilterRepository;

class ProductIndex extends AbstractIndex
{
    protected const INDEX_NAME = 'product';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportWithFilterRepository
     */
    protected $productSearchExportWithFilterRepository;


    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportWithFilterRepository $productSearchExportWithFilterRepository
     */
    public function __construct(
        Domain $domain,
        ProductSearchExportWithFilterRepository $productSearchExportWithFilterRepository
    ) {
        $this->domain = $domain;
        $this->productSearchExportWithFilterRepository = $productSearchExportWithFilterRepository;
    }

    /**
     * @inheritDoc
     */
    public function getTotalCount(int $domainId): int
    {
        return $this->productSearchExportWithFilterRepository->getProductTotalCountForDomain($domainId);
    }

    /**
     * @inheritDoc
     */
    public function getExportDataForIds(int $domainId, array $restrictToIds): array
    {
        return $this->productSearchExportWithFilterRepository->getProductsDataForIds(
            $domainId,
            $this->domain->getDomainConfigById($domainId)->getLocale(),
            $restrictToIds
        );
    }

    /**
     * @inheritDoc
     */
    public function getExportDataForBatch(int $domainId, int $lastProcessedId, int $batchSize): array
    {
        return $this->productSearchExportWithFilterRepository->getProductsData(
            $domainId,
            $this->domain->getDomainConfigById($domainId)->getLocale(),
            $lastProcessedId,
            $batchSize
        );
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return static::INDEX_NAME;
    }
}
