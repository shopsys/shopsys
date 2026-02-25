<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CombinedArticle;

use Elasticsearch\Client;
use InvalidArgumentException;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Search\SearchSetting;
use Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleIndex;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleIndex;

class CombinedArticleElasticsearchRepository
{
    public const string TYPE_ARTICLE = 'article';
    public const string TYPE_BLOG_ARTICLE = 'blog_article';

    public function __construct(
        protected readonly Client $client,
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function getArticlesBySearchText(string $searchText, int $domainId, ?int $limit = null): array
    {
        $result = $this->client->search($this->getSearchQuery($searchText, $domainId, $limit));

        return $this->extractHits($result, $domainId);
    }

    public function getArticlesByDomainId(int $domainId, int $from, int $limit): array
    {
        $result = $this->client->search($this->getArticlesByDomainIdQuery($domainId, $from, $limit));

        return $this->extractHits($result, $domainId);
    }

    protected function extractHits(array $result, int $domainId): array
    {
        return array_map(function ($value) use ($domainId) {
            $data = $value['_source'];
            $data['index'] = $this->getIndexNameFromIndexVersion($value['_index'], $domainId);
            $data['id'] = (int)$value['_id'];

            return $this->fillEmptyFields($data);
        }, $result['hits']['hits']);
    }

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

    public function fillEmptyFields(array $article): array
    {
        $result = $article;

        $result['name'] = $article['name'] ?? '';
        $result['text'] = $article['text'] ?? '';
        $result['url'] = $article['url'] ?? '';
        $result['seoTitle'] = $article['seoTitle'] ?? null;
        $result['seoMetaDescription'] = $article['seoMetaDescription'] ?? null;
        $result['seoH1'] = $article['seoH1'] ?? null;

        return $result;
    }

    protected function getCombinedArticleIndex(int $domainId): string
    {
        $articleIndexName = $this->getArticleIndex($domainId)->getIndexAlias();
        $blogArticleIndexName = $this->getBlogArticleIndex($domainId)->getIndexAlias();

        return implode(',', [$articleIndexName, $blogArticleIndexName]);
    }

    protected function getArticleIndex(int $domainId): IndexDefinition
    {
        return $this->indexDefinitionLoader->getIndexDefinition(
            ArticleIndex::getName(),
            $domainId,
        );
    }

    protected function getBlogArticleIndex(int $domainId): IndexDefinition
    {
        return $this->indexDefinitionLoader->getIndexDefinition(
            BlogArticleIndex::getName(),
            $domainId,
        );
    }

    protected function getSimpleSearchQuery(string $searchText, int $domainId, ?int $limit = null): array
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
                                'match_phrase_prefix' => [
                                    'name.full_without_diacritic' => [
                                        'query' => $searchText,
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

    protected function getSearchQuery(string $searchText, int $domainId, ?int $limit = null): array
    {
        if (mb_strlen($searchText) < SearchSetting::SIMPLE_SEARCH_THRESHOLD) {
            return $this->getSimpleSearchQuery($searchText, $domainId, $limit);
        }

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
                                        'seoH1.full_with_diacritic^30',
                                        'seoH1.full_without_diacritic^28',
                                        'seoH1^26',
                                        'seoH1.edge_ngram_with_diacritic^24',
                                        'seoH1.edge_ngram_without_diacritic^22',
                                        'seoTitle.full_with_diacritic^20',
                                        'seoTitle.full_without_diacritic^18',
                                        'seoTitle^16',
                                        'seoTitle.edge_ngram_with_diacritic^14',
                                        'seoTitle.edge_ngram_without_diacritic^12',
                                        'seoMetaDescription^10',
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

    protected function getCombinedArticlesCondition(): array
    {
        $now = $this->clock->now()->format('Y-m-d H:i:s');

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
                            'must' => [
                                [
                                    'term' => [
                                        'status' => 'published',
                                    ],
                                ],
                                [
                                    'range' => [
                                        'publishDate' => [
                                            'lte' => $now,
                                        ],
                                    ],
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
     */
    public function getArticlesByIds(array $idsByType, int $domainId, int $limit): array
    {
        $result = $this->client->search($this->getArticlesByIdsQuery($idsByType, $domainId, $limit));

        return $this->extractHits($result, $domainId);
    }

    /**
     * @param array<string, array<int, string>> $idsByType
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
