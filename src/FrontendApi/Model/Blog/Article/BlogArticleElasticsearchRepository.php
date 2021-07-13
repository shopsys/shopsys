<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Blog\Article;

use App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchConverter;
use App\Model\Blog\Article\Elasticsearch\BlogArticleIndex;
use App\Model\Blog\Article\Elasticsearch\FilterQueryFactory;
use App\Model\Blog\Article\Exception\BlogArticleNotFoundException;
use App\Model\Blog\Category\BlogCategory;
use Elasticsearch\Client;
use Shopsys\Cdn\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;

class BlogArticleElasticsearchRepository
{
    /**
     * @var \Elasticsearch\Client
     */
    private Client $client;

    /**
     * @var \Shopsys\Cdn\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader
     */
    private IndexDefinitionLoader $indexDefinitionLoader;

    /**
     * @var \App\Model\Blog\Article\Elasticsearch\FilterQueryFactory
     */
    private FilterQueryFactory $filterQueryFactory;

    /**
     * @var \App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchConverter
     */
    private BlogArticleElasticsearchConverter $blogArticleElasticsearchConverter;

    /**
     * @param \Elasticsearch\Client $client
     * @param \Shopsys\Cdn\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \App\Model\Blog\Article\Elasticsearch\FilterQueryFactory $filterQueryFactory
     * @param \App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchConverter $blogArticleElasticsearchConverter
     */
    public function __construct(
        Client $client,
        Domain $domain,
        IndexDefinitionLoader $indexDefinitionLoader,
        FilterQueryFactory $filterQueryFactory,
        BlogArticleElasticsearchConverter $blogArticleElasticsearchConverter
    ) {
        $this->client = $client;
        $this->indexDefinitionLoader = $indexDefinitionLoader;
        $this->domain = $domain;
        $this->filterQueryFactory = $filterQueryFactory;
        $this->blogArticleElasticsearchConverter = $blogArticleElasticsearchConverter;
    }

    /**
     * @param string $uuid
     * @return array
     */
    public function getByUuid(string $uuid): array
    {
        $query = $this->filterQueryFactory
            ->create($this->getIndexName())
            ->filterByUuid($uuid)
            ->getQuery();

        return $this->getSingleResult($query, sprintf('Blog article not found by UUID "%s"', $uuid));
    }

    /**
     * @return int
     */
    public function getAllBlogArticlesTotalCount(): int
    {
        $result = $this->getAllBlogArticlesResult();

        return $this->extractTotalCount($result);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getAllBlogArticles(int $offset, int $limit): array
    {
        $result = $this->getAllBlogArticlesResult($offset, $limit);

        return $this->extractHits($result);
    }

    /**
     * @param string $slug
     * @return array
     */
    public function getBySlug(string $slug): array
    {
        $query = $this->filterQueryFactory
            ->create($this->getIndexName())
            ->filterBySlug($slug)
            ->getQuery();

        return $this->getSingleResult($query, sprintf('Blog article not found by slug "%s"', $slug));
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getByBlogCategory(BlogCategory $blogCategory, int $offset, int $limit): array
    {
        $result = $this->getByBlogCategoryResult($blogCategory, $offset, $limit);

        return $this->extractHits($result);
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @return int
     */
    public function getByBlogCategoryTotalCount(BlogCategory $blogCategory): int
    {
        $result = $this->getByBlogCategoryResult($blogCategory);

        return $this->extractTotalCount($result);
    }

    /**
     * @param array $result
     * @return array
     */
    private function extractHits(array $result): array
    {
        return array_map(function ($value) {
            $data = $value['_source'];
            $data['index'] = $this->getIndexName();
            $data['id'] = (int)$value['_id'];

            return $this->blogArticleElasticsearchConverter->fillEmptyFields($data);
        }, $result['hits']['hits']);
    }

    /**
     * @return string
     */
    private function getIndexName(): string
    {
        return $this->indexDefinitionLoader->getIndexDefinition(BlogArticleIndex::getName(), $this->domain->getId())->getIndexAlias();
    }

    /**
     * @param array $query
     * @param string $notFoundExceptionMessage
     * @return array
     */
    private function getSingleResult(array $query, string $notFoundExceptionMessage): array
    {
        $result = $this->client->search($query);
        $hits = $this->extractHits($result);
        if (count($hits) === 0) {
            throw new BlogArticleNotFoundException($notFoundExceptionMessage);
        }

        return array_shift($hits);
    }

    /**
     * @param int|null $offset
     * @param int|null $limit
     * @return array
     */
    private function getAllBlogArticlesResult(?int $offset = null, ?int $limit = null): array
    {
        $query = $this->filterQueryFactory->create($this->getIndexName());
        if ($offset !== null) {
            $query = $query->setFrom($offset);
        }
        if ($limit !== null) {
            $query = $query->setLimit($limit);
        }

        return $this->client->search($query->getQuery());
    }

    /**
     * @param array $result
     * @return int
     */
    private function extractTotalCount(array $result): int
    {
        return (int)$result['hits']['total']['value'];
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @param int|null $offset
     * @param int|null $limit
     * @return array
     */
    private function getByBlogCategoryResult(BlogCategory $blogCategory, ?int $offset = null, ?int $limit = null): array
    {
        $query = $this->filterQueryFactory->create($this->getIndexName())->filterByCategory($blogCategory);
        if ($offset !== null) {
            $query = $query->setFrom($offset);
        }
        if ($limit !== null) {
            $query = $query->setLimit($limit);
        }

        return $this->client->search($query->getQuery());
    }
}
