<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;

class MultipleSearchQueryFactory
{
    public function __construct(
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery[] $filterQueries
     */
    public function create(string $indexName, array $filterQueries): MultipleSearchQuery
    {
        return new MultipleSearchQuery($this->getIndexAlias($indexName), $filterQueries);
    }

    protected function getIndexAlias(string $indexName): string
    {
        return $this->indexDefinitionLoader->getIndexDefinition(
            $indexName,
            $this->domain->getId(),
        )->getIndexAlias();
    }
}
