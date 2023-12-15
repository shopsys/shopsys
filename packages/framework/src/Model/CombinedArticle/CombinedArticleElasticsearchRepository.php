<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CombinedArticle;

use Elasticsearch\Client;
use InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleIndex;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleIndex;

class CombinedArticleElasticsearchRepository
{
    /**
     * @param \Elasticsearch\Client $client
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     */
    public function __construct(
        protected readonly Client $client,
        protected readonly Domain $domain,
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
    ) {
    }

    /**
     * @param string $searchText
     * @param int|null $limit
     * @return array
     */
    public function getArticlesBySearchText(string $searchText, ?int $limit = null): array
    {
        $result = $this->client->search($this->getSearchQuery($searchText, $limit));

        return $this->extractHits($result);
    }

    /**
     * @param int $domainId
     * @param int $from
     * @param int $maxResults
     * @return array
     */
    public function getArticlesByDomainId(int $domainId, int $from, int $maxResults): array
    {
        $result = $this->client->search($this->getArticlesByDomainIdQuery($domainId, $from, $maxResults));

        return $this->extractHits($result, $domainId);
    }

    /**
     * @param array $result
     * @param int|null $domainId
     * @return array
     */
    protected function extractHits(array $result, ?int $domainId = null): array
    {
        return array_map(function ($value) use ($domainId) {
            $data = $value['_source'];
            $data['index'] = $this->getIndexNameFromIndexVersion($value['_index'], $domainId);
            $data['id'] = (int)$value['_id'];

            return $this->fillEmptyFields($data);
        }, $result['hits']['hits']);
    }

    /**
     * @param string $indexVersion
     * @param int|null $domainId
     * @return string
     */
    protected function getIndexNameFromIndexVersion(string $indexVersion, ?int $domainId = null): string
    {
        $blogArticleVersionedIndexName = $this->indexDefinitionLoader->getIndexDefinition(BlogArticleIndex::getName(), $domainId ?? $this->domain->getId())->getVersionedIndexName();

        if ($indexVersion === $blogArticleVersionedIndexName) {
            return BlogArticleIndex::getName();
        }

        $articleVersionedIndexName = $this->indexDefinitionLoader->getIndexDefinition(ArticleIndex::getName(), $domainId ?? $this->domain->getId())->getVersionedIndexName();

        if ($indexVersion === $articleVersionedIndexName) {
            return ArticleIndex::getName();
        }

        throw new InvalidArgumentException(sprintf('Unsupported index version "%s"', $indexVersion));
    }

    /**
     * @param array $article
     * @return array
     */
    public function fillEmptyFields(array $article): array
    {
        $result = $article;

        $result['name'] = $article['name'] ?? '';
        $result['text'] = $article['text'] ?? '';
        $result['url'] = $article['url'] ?? '';

        return $result;
    }

    /**
     * @param int|null $domainId
     * @return string
     */
    protected function getCombinedArticleIndex(?int $domainId = null): string
    {
        $domainId = $domainId ?? $this->domain->getId();
        $articleIndexName = $this->indexDefinitionLoader->getIndexDefinition(
            ArticleIndex::getName(),
            $domainId,
        )->getIndexAlias();
        $blogArticleIndexName = $this->indexDefinitionLoader->getIndexDefinition(
            BlogArticleIndex::getName(),
            $domainId,
        )->getIndexAlias();

        return implode(',', [$articleIndexName, $blogArticleIndexName]);
    }

    /**
     * @param string $searchText
     * @param int|null $limit
     * @return array
     */
    protected function getSearchQuery(string $searchText, ?int $limit = null): array
    {
        $query = [
            'index' => $this->getCombinedArticleIndex(),
            'body' => [
                'from' => 0,
                'query' => [
                    'bool' => [
                        'must' => [
                            $this->getCombinedArticlesCondition(),
                            [
                                'multi_match' => [
                                    'query' => $searchText,
                                    'fields' => [
                                        'name.full_with_diacritic^60',
                                        'name.full_without_diacritic^50',
                                        'name^45',
                                        'name.edge_ngram_with_diacritic^40',
                                        'name.edge_ngram_without_diacritic^35',
                                        'text^50',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if ($limit !== null) {
            $query['body']['size'] = $limit;
        }

        return $query;
    }

    /**
     * @param int $domainId
     * @param int $from
     * @param int $maxResults
     * @return array
     */
    protected function getArticlesByDomainIdQuery(int $domainId, int $from, int $maxResults): array
    {
        return [
            'index' => $this->getCombinedArticleIndex($domainId),
            'body' => [
                'from' => $from,
                'size' => $maxResults,
                'query' => [
                    'bool' => [
                        'must' => [$this->getCombinedArticlesCondition()],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getCombinedArticlesCondition(): array
    {
        return [
            'bool' => [
                'should' => [
                    [
                        'match' => [
                            'type' => 'site',
                        ],
                    ],
                    [
                        'bool' => [
                            'must_not' => [
                                'exists' => [
                                    'field' => 'type',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
