<?php

declare(strict_types=1);

namespace App\Model\Blog\Article\Elasticsearch;

use App\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;

/**
 * Heavily inspired by @see \App\Model\Product\Search\FilterQueryFactory
 *
 * @see https://github.com/shopsys/shopsys/issues/2362
 */
class FilterQueryFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     */
    public function __construct(private Domain $domain, private IndexDefinitionLoader $indexDefinitionLoader)
    {
    }

    /**
     * @param int|null $offset
     * @param int|null $limit
     * @return \App\Model\Blog\Article\Elasticsearch\FilterQuery
     */
    public function create(?int $offset = null, ?int $limit = null): FilterQuery
    {
        $filterQuery = new FilterQuery($this->getIndexName());

        /** @var \App\Model\Blog\Article\Elasticsearch\FilterQuery $filterQueryIncludingOffsetAndLimit */
        $filterQueryIncludingOffsetAndLimit = $filterQuery->setFrom($offset)->setLimit($limit);

        return $filterQueryIncludingOffsetAndLimit;
    }

    /**
     * @param string $uuid
     * @return \App\Model\Blog\Article\Elasticsearch\FilterQuery
     */
    public function createFilteredByUuid(string $uuid): FilterQuery
    {
        return $this->create()->filterByUuid($uuid);
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @param int|null $offset
     * @param int|null $limit
     * @return \App\Model\Blog\Article\Elasticsearch\FilterQuery
     */
    public function createFilteredByBlogCategory(
        BlogCategory $blogCategory,
        ?int $offset = null,
        ?int $limit = null,
    ): FilterQuery {
        return $this->create($offset, $limit)->filterByCategory($blogCategory);
    }

    /**
     * @param string $urlSlug
     * @return \App\Model\Blog\Article\Elasticsearch\FilterQuery
     */
    public function createFilteredBySlug(string $urlSlug): FilterQuery
    {
        return $this->create()->filterBySlug($urlSlug);
    }

    /**
     * @return string
     */
    private function getIndexName(): string
    {
        return $this->indexDefinitionLoader->getIndexDefinition(BlogArticleIndex::getName(), $this->domain->getId())->getIndexAlias();
    }
}
