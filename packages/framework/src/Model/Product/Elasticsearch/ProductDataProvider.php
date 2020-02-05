<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\DataProviderInterface;
use Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportWithFilterRepository;

class ProductDataProvider implements DataProviderInterface
{
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
     * @param int $domainId
     * @return int
     */
    public function getTotalCount(int $domainId): int
    {
        return $this->productSearchExportWithFilterRepository->getProductTotalCountForDomain($domainId);
    }

    /**
     * @param int $domainId
     * @param int $lastProcessedId
     * @param array $restrictToIds
     * @return array
     */
    public function getDataForBatch(int $domainId, int $lastProcessedId, array $restrictToIds = []): array
    {
        $locale = $this->domain->getDomainConfigById($domainId)->getLocale();
        if (!empty($restrictToIds)) {
            return $this->productSearchExportWithFilterRepository->getProductsDataForIds(
                $domainId,
                $locale,
                $restrictToIds
            );
        }

        return $this->productSearchExportWithFilterRepository->getProductsData(
            $domainId,
            $locale,
            $lastProcessedId,
            DataProviderInterface::BATCH_SIZE
        );
    }
}
