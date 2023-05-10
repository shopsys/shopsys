<?php

declare(strict_types=1);

namespace App\Component\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;

class MultipleSearchQueryFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader
     */
    private IndexDefinitionLoader $indexDefinitionLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        IndexDefinitionLoader $indexDefinitionLoader,
        Domain $domain
    ) {
        $this->indexDefinitionLoader = $indexDefinitionLoader;
        $this->domain = $domain;
    }

    /**
     * @param string $indexName
     * @param \App\Model\Product\Search\FilterQuery[] $filterQueries
     * @return \App\Component\Elasticsearch\MultipleSearchQuery
     */
    public function create(string $indexName, array $filterQueries): MultipleSearchQuery
    {
        return new MultipleSearchQuery($this->getIndexAlias($indexName), $filterQueries);
    }

    /**
     * @param string $indexName
     * @return string
     */
    private function getIndexAlias(string $indexName): string
    {
        return $this->indexDefinitionLoader->getIndexDefinition(
            $indexName,
            $this->domain->getId()
        )->getIndexAlias();
    }
}
