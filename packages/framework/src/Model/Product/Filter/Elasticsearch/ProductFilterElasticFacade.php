<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter\Elasticsearch;

use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;

class ProductFilterElasticFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory
     */
    protected $filterQueryFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader
     */
    protected $indexDefinitionLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Elasticsearch\Client
     */
    protected $client;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Elasticsearch\Client $client
     */
    public function __construct(
        FilterQueryFactory $filterQueryFactory,
        IndexDefinitionLoader $indexDefinitionLoader,
        Domain $domain,
        Client $client
    ) {
        $this->filterQueryFactory = $filterQueryFactory;
        $this->indexDefinitionLoader = $indexDefinitionLoader;
        $this->domain = $domain;
        $this->client = $client;
    }

    /**
     * @return string
     */
    protected function getIndexName(): string
    {
        return $this->indexDefinitionLoader->getIndexDefinition(
            ProductIndex::getName(),
            $this->domain->getId()
        )->getIndexAlias();
    }

    /**
     * @param int $categoryId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return array
     */
    public function getProductFilterDataInCategory(int $categoryId, PricingGroup $pricingGroup): array
    {
        $baseFilterQuery = $this->filterQueryFactory->create($this->getIndexName())
            ->filterOnlyVisible($pricingGroup)
            ->filterOnlySellable()
            ->filterByCategory([$categoryId]);

        return $this->client->search($baseFilterQuery->getFilterQuery($pricingGroup->getId()));
    }

    /**
     * @param string|null $searchText
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return array
     */
    public function getProductFilterDataForSearch(?string $searchText, PricingGroup $pricingGroup): array
    {
        $searchText = $searchText ?? '';

        $baseFilterQuery = $this->filterQueryFactory->create($this->getIndexName())
            ->filterOnlyVisible($pricingGroup)
            ->filterOnlySellable()
            ->search($searchText);

        $filterQuery = $baseFilterQuery->getFilterQuery($pricingGroup->getId());

        // Remove parameters from filter on search page
        unset($filterQuery['body']['aggs']['parameters']);

        return $this->client->search($filterQuery);
    }
}
