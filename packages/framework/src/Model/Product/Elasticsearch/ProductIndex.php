<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;

class ProductIndex extends AbstractIndex
{
    protected const INDEX_NAME = 'product';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ExportWithFilterRepository
     */
    protected $exportWithFilterRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ExportWithFilterRepository $exportWithFilterRepository
     */
    public function __construct(
        Domain $domain,
        ExportWithFilterRepository $exportWithFilterRepository
    ) {
        $this->domain = $domain;
        $this->exportWithFilterRepository = $exportWithFilterRepository;
    }

    /**
     * @inheritDoc
     */
    public function getTotalCount(int $domainId): int
    {
        return $this->exportWithFilterRepository->getProductTotalCountForDomain($domainId);
    }

    /**
     * @inheritDoc
     */
    public function getExportDataForIds(int $domainId, array $restrictToIds): array
    {
        return $this->exportWithFilterRepository->getProductsDataForIds(
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
        return $this->exportWithFilterRepository->getProductsData(
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
