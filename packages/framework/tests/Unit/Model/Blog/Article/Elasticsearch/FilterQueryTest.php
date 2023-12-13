<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Blog\Article\Elasticsearch;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQuery;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryData;
use stdClass;

class FilterQueryTest extends TestCase
{
    public function testFilterByUuid(): void
    {
        $filterQuery = new FilterQuery('blog_article');
        $uuid = '123e4567-e89b-12d3-a456-426614174000';
        $actualQuery = $filterQuery->filterByUuid($uuid)->getQuery();

        $expectedQuery = [
            'index' => 'blog_article',
            'body' => [
                'from' => 0,
                'size' => 1000,
                'sort' => [
                    'publishedAt' => 'desc',
                    'createdAt' => 'desc',
                    'name.keyword' => 'asc',
                ],
                'query' => [
                    'bool' => [
                        'must' => [
                            'match_all' => new stdClass(),
                        ],
                        'filter' => [
                            [
                                'term' => [
                                    'uuid' => $uuid,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedQuery, $actualQuery);
    }

    public function testFilterBySlug(): void
    {
        $filterQuery = new FilterQuery('blog_article');
        $slug = 'blog-article-slug';
        $actualQuery = $filterQuery->filterBySlug($slug)->getQuery();

        $expectedQuery = [
            'index' => 'blog_article',
            'body' => [
                'from' => 0,
                'size' => 1000,
                'sort' => [
                    'publishedAt' => 'desc',
                    'createdAt' => 'desc',
                    'name.keyword' => 'asc',
                ],
                'query' => [
                    'bool' => [
                        'must' => [
                            'match_all' => new stdClass(),
                        ],
                        'filter' => [
                            [
                                'term' => [
                                    'slug' => $slug,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedQuery, $actualQuery);
    }

    public function testFilterByCategory(): void
    {
        $filterQuery = new FilterQuery('blog_article');
        $blogCategory = $this->getBlogCategoryWithId1();
        $actualQuery = $filterQuery->filterByCategory($blogCategory)->getQuery();

        $expectedQuery = [
            'index' => 'blog_article',
            'body' => [
                'from' => 0,
                'size' => 1000,
                'sort' => [
                    'publishedAt' => 'desc',
                    'createdAt' => 'desc',
                    'name.keyword' => 'asc',
                ],
                'query' => [
                    'bool' => [
                        'must' => [
                            'match_all' => new stdClass(),
                        ],
                        'filter' => [
                            [
                                'term' => [
                                    'categories' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedQuery, $actualQuery);
    }

    /**
     * @dataProvider offsetAndLimitDataProvider
     * @param int|null $limit
     * @param int|null $offset
     * @param int $expectedSize
     * @param int $expectedFrom
     */
    public function testFilterWithOffsetAndLimit(?int $limit, ?int $offset, int $expectedSize, int $expectedFrom): void
    {
        $filterQuery = new FilterQuery('blog_article');
        $filterQueryWithOffsetAndLimit = $filterQuery->setLimit($limit)->setFrom($offset);
        $actualQuery = $filterQueryWithOffsetAndLimit->getQuery();

        $expectedQuery = [
            'index' => 'blog_article',
            'body' => [
                'from' => $expectedFrom,
                'size' => $expectedSize,
                'sort' => [
                    'publishedAt' => 'desc',
                    'createdAt' => 'desc',
                    'name.keyword' => 'asc',
                ],
                'query' => [
                    'bool' => [
                        'must' => [
                            'match_all' => new stdClass(),
                        ],
                        'filter' => [],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedQuery, $actualQuery);
    }

    /**
     * @return array[]
     */
    public function offsetAndLimitDataProvider(): array
    {
        return [
            [null, null, 1000, 0],
            [1, 2, 1, 2],
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory
     */
    private function getBlogCategoryWithId1(): BlogCategory
    {
        $blogCategoryData = new BlogCategoryData();
        $blogCategory = new BlogCategory($blogCategoryData);
        $reflection = new ReflectionClass($blogCategory);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($blogCategory, 1);

        return $blogCategory;
    }
}
