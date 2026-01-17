<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;

/**
 * Heavily inspired by @see \Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQueryFactory
 *
 * @see https://github.com/shopsys/shopsys/issues/2362
 */
class FilterQueryFactory
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
    ) {
    }

    public function create(?int $offset = null, ?int $limit = null): FilterQuery
    {
        $filterQuery = new FilterQuery($this->getIndexName());

        /** @var \Shopsys\FrameworkBundle\Model\Article\Elasticsearch\FilterQuery $filterQueryIncludingOffsetAndLimit */
        $filterQueryIncludingOffsetAndLimit = $filterQuery->setFrom($offset)->setLimit($limit);

        return $filterQueryIncludingOffsetAndLimit;
    }

    public function createFilteredByUuid(string $uuid): FilterQuery
    {
        return $this->create()->filterByUuid($uuid);
    }

    public function createFilteredBySlug(string $urlSlug): FilterQuery
    {
        return $this->create()->filterBySlug($urlSlug);
    }

    public function createFilteredById(int $articleId): FilterQuery
    {
        return $this->create()->filterById($articleId);
    }

    protected function getIndexName(): string
    {
        return $this->indexDefinitionLoader->getIndexDefinition(ArticleIndex::getName(), $this->domain->getId())->getIndexAlias();
    }
}
