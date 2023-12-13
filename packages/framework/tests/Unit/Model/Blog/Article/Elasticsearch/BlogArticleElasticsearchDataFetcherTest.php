<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Blog\Article\Elasticsearch;

use Elasticsearch\Client;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchNoResultException;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchDataFetcher;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQuery;

class BlogArticleElasticsearchDataFetcherTest extends TestCase
{
    public function testGetTotalCount(): void
    {
        $expectedTotalCount = 10;
        $mockedResultArray = $this->getMockedResultArray($expectedTotalCount, []);
        $articleElasticsearchDataFetcher = $this->getBlogArticleElasticsearchDataFetcherWithMockedClient($mockedResultArray);
        $actualTotalCount = $articleElasticsearchDataFetcher->getTotalCount(new FilterQuery('blog_articles'));

        $this->assertEquals($expectedTotalCount, $actualTotalCount);
    }

    public function testGetSingleResult(): void
    {
        $expectedBlogArticleData = $this->getBlogArticleDataDefaultValues();
        $expectedBlogArticleData['id'] = '1';

        $mockedHits = $this->getMockedHits($expectedBlogArticleData);
        $mockedResultArray = $this->getMockedResultArray(1, $mockedHits);

        $articleElasticsearchDataFetcher = $this->getBlogArticleElasticsearchDataFetcherWithMockedClient($mockedResultArray);
        $actualResults = $articleElasticsearchDataFetcher->getSingleResult(new FilterQuery('blog_articles'));

        $this->assertEquals($expectedBlogArticleData, $actualResults);
    }

    public function testGetSingleResultThrowsExceptionWhenNoResultIsReturnedFromElasticsearch(): void
    {
        $mockedResultArray = $this->getMockedResultArray(0, []);

        $articleElasticsearchDataFetcher = $this->getBlogArticleElasticsearchDataFetcherWithMockedClient($mockedResultArray);

        $this->expectException(ElasticsearchNoResultException::class);
        $articleElasticsearchDataFetcher->getSingleResult(new FilterQuery('blog_articles'));
    }

    public function testGetAllResults(): void
    {
        $expectedBlogArticleData1 = $this->getBlogArticleDataDefaultValues();
        $expectedBlogArticleData1['id'] = '1';

        $expectedBlogArticleData2 = $this->getBlogArticleDataDefaultValues();
        $expectedBlogArticleData2['id'] = '2';

        $mockedHits = $this->getMockedHits($expectedBlogArticleData1, $expectedBlogArticleData2);
        $mockedResultArray = $this->getMockedResultArray(2, $mockedHits);

        $articleElasticsearchDataFetcher = $this->getBlogArticleElasticsearchDataFetcherWithMockedClient($mockedResultArray);
        $actualResults = $articleElasticsearchDataFetcher->getAllResults(new FilterQuery('blog_articles'));

        $this->assertEquals([$expectedBlogArticleData1, $expectedBlogArticleData2], $actualResults);
    }

    /**
     * @param array $mockedResultArray
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchDataFetcher
     */
    private function getBlogArticleElasticsearchDataFetcherWithMockedClient(
        array $mockedResultArray,
    ): BlogArticleElasticsearchDataFetcher {
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('search')->willReturn($mockedResultArray);

        return new BlogArticleElasticsearchDataFetcher($clientMock);
    }

    /**
     * @return array
     */
    private function getBlogArticleDataDefaultValues(): array
    {
        return [
            'name' => '',
            'text' => null,
            'url' => '',
            'uuid' => '',
            'createdAt' => '1970-01-01 00:00:00',
            'visibleOnHomepage' => false,
            'publishedAt' => '1970-01-01',
            'perex' => null,
            'seoTitle' => null,
            'seoMetaDescription' => null,
            'seoH1' => null,
            'categories' => [],
            'mainSlug' => '',
            'products' => [],
        ];
    }

    /**
     * @param int $totalCount
     * @param array $hits
     * @return array
     */
    private function getMockedResultArray(int $totalCount, array $hits): array
    {
        return [
            'took' => 76,
            'timed_out' => false,
            '_shards' => [],
            'hits' => [
                'total' => [
                    'value' => $totalCount,
                    'relation' => 'eq',
                ],
                'max_score' => null,
                'hits' => $hits,
            ],
        ];
    }

    /**
     * @param mixed ...$blogArticlesData
     * @return array[]
     */
    private function getMockedHits(...$blogArticlesData): array
    {
        $mockedHits = [];

        foreach ($blogArticlesData as $blogArticleData) {
            $mockedHits[] = [
                '_index' => 'blog_article_1_index_hash',
                '_type' => '_doc',
                '_id' => $blogArticleData['id'],
                '_score' => null,
                '_source' => $blogArticleData,
                'sort' => [],
            ];
        }

        return $mockedHits;
    }
}
