<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch;

use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;

/**
 * Heavily inspired by @see \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory
 *
 * @see https://github.com/shopsys/shopsys/issues/2362
 */
class FilterQueryFactory
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function create(?int $offset = null, ?int $limit = null): FilterQuery
    {
        $filterQuery = new FilterQuery($this->getIndexName(), $this->clock);

        /** @var \Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQuery $filterQueryIncludingOffsetAndLimit */
        $filterQueryIncludingOffsetAndLimit = $filterQuery->setFrom($offset)->setLimit($limit);

        return $filterQueryIncludingOffsetAndLimit;
    }

    public function createFilteredByUuid(string $uuid): FilterQuery
    {
        return $this->create()->filterByUuid($uuid);
    }

    public function createFilteredByBlogCategory(
        BlogCategory $blogCategory,
        ?int $offset = null,
        ?int $limit = null,
    ): FilterQuery {
        return $this->create($offset, $limit)->filterByCategory($blogCategory);
    }

    public function createFilteredBySlug(string $urlSlug): FilterQuery
    {
        return $this->create()->filterBySlug($urlSlug);
    }

    protected function getIndexName(): string
    {
        return $this->indexDefinitionLoader->getIndexDefinition(BlogArticleIndex::getName(), $this->domain->getId())->getIndexAlias();
    }
}
