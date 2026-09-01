<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Article\Elasticsearch;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\Elasticsearch\FilterQuery;
use stdClass;

class FilterQueryTest extends TestCase
{
    public function testFilterBySlugAndType(): void
    {
        $filterQuery = new FilterQuery('article');

        $actualQuery = $filterQuery->filterBySlug('article-slug')->filterByType(Article::TYPE_SITE)->getQuery();

        $expectedQuery = [
            'index' => 'article',
            'body' => [
                'from' => 0,
                'size' => 1000,
                'sort' => [
                    'placement' => 'asc',
                    'position' => 'asc',
                ],
                'query' => [
                    'bool' => [
                        'must' => [
                            'match_all' => new stdClass(),
                        ],
                        'filter' => [
                            [
                                'term' => [
                                    'slug' => 'article-slug',
                                ],
                            ],
                            [
                                'match' => [
                                    'type' => Article::TYPE_SITE,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedQuery, $actualQuery);
    }
}
