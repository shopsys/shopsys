<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CombinedArticle;

use Elasticsearch\Client;
use InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleIndex;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleIndex;

class CombinedArticleElasticsearchRepository
{
    public const string TYPE_ARTICLE = 'article';
    public const string TYPE_BLOG_ARTICLE = 'blog_article';

    /**
     * @param \Elasticsearch\Client $client
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     */
    public function __construct(
        protected readonly Client $client,
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
    ) {
    }

    /**
     * @param string $searchText
     * @param int $domainId
     * @param int|null $limit
     * @return array
     */
    public function getArticlesBySearchText(string $searchText, int $domainId, ?int $limit = null): array
    {
        $result = $this->client->search($this->getSearchQuery($searchText, $domainId, $limit));

        return $this->extractHits($result, $domainId);
    }

    /**
     * @param int $domainId
     * @param int $from
     * @param int $limit
     * @return array
     */
    public function getArticlesByDomainId(int $domainId, int $from, int $limit): array
    {
        $result = $this->client->search($this->getArticlesByDomainIdQuery($domainId, $from, $limit));

        return $this->extractHits($result, $domainId);
    }

    /**
     * @param array $result
     * @param int $domainId
     * @return array
     */
    protected function extractHits(array $result, int $domainId): array
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
     * @param int $domainId
     * @return string
     */
    protected function getIndexNameFromIndexVersion(string $indexVersion, int $domainId): string
    {
        $blogArticleVersionedIndexName = $this->getBlogArticleIndex($domainId)->getVersionedIndexName();

        if ($indexVersion === $blogArticleVersionedIndexName) {
            return BlogArticleIndex::getName();
        }

        $articleVersionedIndexName = $this->getArticleIndex($domainId)->getVersionedIndexName();

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
     * @param int $domainId
     * @return string
     */
    protected function getCombinedArticleIndex(int $domainId): string
    {
        $articleIndexName = $this->getArticleIndex($domainId)->getIndexAlias();
        $blogArticleIndexName = $this->getBlogArticleIndex($domainId)->getIndexAlias();

        return implode(',', [$articleIndexName, $blogArticleIndexName]);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition
     */
    protected function getArticleIndex(int $domainId): IndexDefinition
    {
        return $this->indexDefinitionLoader->getIndexDefinition(
            ArticleIndex::getName(),
            $domainId,
        );
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition
     */
    protected function getBlogArticleIndex(int $domainId): IndexDefinition
    {
        return $this->indexDefinitionLoader->getIndexDefinition(
            BlogArticleIndex::getName(),
            $domainId,
        );
    }

    /**
     * @param string $searchText
     * @param int $domainId
     * @param int|null $limit
     * @return array
     */
    protected function getSearchQuery(string $searchText, int $domainId, ?int $limit = null): array
    {
        $query = [
            'index' => $this->getCombinedArticleIndex($domainId),
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
     * @param int $limit
     * @return array
     */
    protected function getArticlesByDomainIdQuery(int $domainId, int $from, int $limit): array
    {
        return [
            'index' => $this->getCombinedArticleIndex($domainId),
            'body' => [
                'from' => $from,
                'size' => $limit,
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

    /**
     * @param array<string, array<int, string>> $idsByType
     * @param int $domainId
     * @param int $limit
     * @return array
     */
    public function getArticlesByIds(array $idsByType, int $domainId, int $limit): array
    {
        $result = $this->client->search($this->getArticlesByIdsQuery($idsByType, $domainId, $limit));

        return $this->extractHits($result, $domainId);
    }

    /**
     * @param array<string, array<int, string>> $idsByType
     * @param int $domainId
     * @param int $limit
     * @return array
     */
    protected function getArticlesByIdsQuery(array $idsByType, int $domainId, int $limit): array
    {
        if (count($idsByType) === 0) {
            return [];
        }

        $condition = [];

        $i = 0;

        foreach ($idsByType as $type => $ids) {
            if ($type === self::TYPE_ARTICLE) {
                $index = $this->getArticleIndex($domainId)->getVersionedIndexName();
            } else {
                $index = $this->getBlogArticleIndex($domainId)->getVersionedIndexName();
            }

            $condition['bool']['should'][$i] = [
                'bool' => [
                    'must' => [
                        [
                            'terms' => [
                                '_id' => $ids,
                            ],
                        ],
                        [
                            'match' => [
                                '_index' => $index,
                            ],
                        ],
                    ],
                ],
            ];

            $i++;
        }

        return [
            'index' => $this->getCombinedArticleIndex($domainId),
            'body' => [
                'from' => 0,
                'size' => $limit,
                'query' => $condition,
            ],
        ];
    }
}
