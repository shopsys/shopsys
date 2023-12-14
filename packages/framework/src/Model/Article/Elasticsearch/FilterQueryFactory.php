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
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
    ) {
    }

    /**
     * @param int|null $offset
     * @param int|null $limit
     * @return \Shopsys\FrameworkBundle\Model\Article\Elasticsearch\FilterQuery
     */
    public function create(?int $offset = null, ?int $limit = null): FilterQuery
    {
        $filterQuery = new FilterQuery($this->getIndexName());

        /** @var \Shopsys\FrameworkBundle\Model\Article\Elasticsearch\FilterQuery $filterQueryIncludingOffsetAndLimit */
        $filterQueryIncludingOffsetAndLimit = $filterQuery->setFrom($offset)->setLimit($limit);

        return $filterQueryIncludingOffsetAndLimit;
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Article\Elasticsearch\FilterQuery
     */
    public function createFilteredByUuid(string $uuid): FilterQuery
    {
        return $this->create()->filterByUuid($uuid);
    }

    /**
     * @param string $urlSlug
     * @return \Shopsys\FrameworkBundle\Model\Article\Elasticsearch\FilterQuery
     */
    public function createFilteredBySlug(string $urlSlug): FilterQuery
    {
        return $this->create()->filterBySlug($urlSlug);
    }

    /**
     * @param int $articleId
     * @return \Shopsys\FrameworkBundle\Model\Article\Elasticsearch\FilterQuery
     */
    public function createFilteredById(int $articleId): FilterQuery
    {
        return $this->create()->filterById($articleId);
    }

    /**
     * @return string
     */
    protected function getIndexName(): string
    {
        return $this->indexDefinitionLoader->getIndexDefinition(ArticleIndex::getName(), $this->domain->getId())->getIndexAlias();
    }
}
