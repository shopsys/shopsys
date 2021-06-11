<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Blog\Article;

use App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchConverter;
use App\Model\Blog\Article\Elasticsearch\BlogArticleIndex;
use App\Model\Blog\Article\Elasticsearch\FilterQueryFactory;
use App\Model\Blog\Article\Exception\BlogArticleNotFoundException;
use Elasticsearch\Client;
use Shopsys\Cdn\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;

class BlogArticleElasticRepository
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
        $filterQuery = $this->filterQueryFactory
            ->create($this->getIndexName())
            ->filterByUuid($uuid)
            ->getQuery();
        $result = $this->client->search($filterQuery);
        $hits = $this->extractHits($result);
        if (count($hits) === 0) {
            throw new BlogArticleNotFoundException(sprintf('Blog article not found by UUID "%s"', $uuid));
        }

        return array_shift($hits);
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
}
