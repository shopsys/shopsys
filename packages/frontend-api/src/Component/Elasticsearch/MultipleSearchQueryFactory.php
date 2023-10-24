<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;

class MultipleSearchQueryFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param string $indexName
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery[] $filterQueries
     * @return \Shopsys\FrontendApiBundle\Component\Elasticsearch\MultipleSearchQuery
     */
    public function create(string $indexName, array $filterQueries): MultipleSearchQuery
    {
        return new MultipleSearchQuery($this->getIndexAlias($indexName), $filterQueries);
    }

    /**
     * @param string $indexName
     * @return string
     */
    protected function getIndexAlias(string $indexName): string
    {
        return $this->indexDefinitionLoader->getIndexDefinition(
            $indexName,
            $this->domain->getId(),
        )->getIndexAlias();
    }
}
