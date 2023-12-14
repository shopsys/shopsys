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
     * @param array $result
     * @return array
     */
    protected function extractHits(array $result): array
    {
        return array_map(function ($value) {
            $data = $value['_source'];
            $data['index'] = $this->getIndexNameFromIndexVersion($value['_index']);
            $data['id'] = (int)$value['_id'];

            return $this->fillEmptyFields($data);
        }, $result['hits']['hits']);
    }

    /**
     * @param string $indexVersion
     * @return string
     */
    protected function getIndexNameFromIndexVersion(string $indexVersion): string
    {
        $blogArticleVersionedIndexName = $this->indexDefinitionLoader->getIndexDefinition(BlogArticleIndex::getName(), $this->domain->getId())->getVersionedIndexName();

        if ($indexVersion === $blogArticleVersionedIndexName) {
            return BlogArticleIndex::getName();
        }

        $articleVersionedIndexName = $this->indexDefinitionLoader->getIndexDefinition(ArticleIndex::getName(), $this->domain->getId())->getVersionedIndexName();

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
     * @return string
     */
    protected function getCombinedArticleIndex(): string
    {
        $domainId = $this->domain->getId();
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
                            [
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
                            ],
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
}
