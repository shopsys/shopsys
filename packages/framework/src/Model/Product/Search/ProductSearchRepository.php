<?php

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Microservice\MicroserviceClient;

class ProductSearchRepository
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Microservice\MicroserviceClient
     */
    protected $microserviceProductSearchClient;

    /**
     * @var int[][][]
     */
    protected $foundProductIdsCache = [];

    public function __construct(MicroserviceClient $microserviceProductSearchClient)
    {
        $this->microserviceProductSearchClient = $microserviceProductSearchClient;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $productQueryBuilder
     * @param string|null $searchText
     */
    public function filterBySearchText(QueryBuilder $productQueryBuilder, $searchText)
    {
        $productIds = $this->getFoundProductIds($productQueryBuilder, $searchText);

        if (count($productIds) > 0) {
            $productQueryBuilder->andWhere('p.id IN (:productIds)')->setParameter('productIds', $productIds);
        } else {
            $productQueryBuilder->andWhere('TRUE = FALSE');
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $productQueryBuilder
     * @param string|null $searchText
     */
    public function addRelevance(QueryBuilder $productQueryBuilder, $searchText)
    {
        $productIds = $this->getFoundProductIds($productQueryBuilder, $searchText);

        if (count($productIds) > 0) {
            $sqlCases = [];
            foreach ($productIds as $index => $productId) {
                $sqlCases[] = sprintf('WHEN p.id = %d THEN %d', $productId, $index);
            }
            $sqlCases[] = sprintf('ELSE %d', count($productIds));
            $relevanceSql = 'CASE ' . implode(' ', $sqlCases) . ' END';
        } else {
            $relevanceSql = '0';
        }

        $productQueryBuilder->addSelect($relevanceSql . ' AS HIDDEN relevance');
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $productQueryBuilder
     * @param $searchText
     * @return int[]
     */
    protected function getFoundProductIds(QueryBuilder $productQueryBuilder, $searchText)
    {
        $domainId = $productQueryBuilder->getParameter('domainId')->getValue();

        if (!isset($this->foundProductIdsCache[$domainId][$searchText])) {
            $searchResult = $this->microserviceProductSearchClient->search($domainId, $searchText);
            $foundProductIds = $searchResult->productIds;

            $this->foundProductIdsCache[$domainId][$searchText] = $foundProductIds;
        }

        return $this->foundProductIdsCache[$domainId][$searchText];
    }
}
