<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Article;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ArticlesSearchTest extends GraphQlTestCase
{
    public function testSearchArticles()
    {
        $query = '
            query {
                articlesSearch(search: "Dina") {
                    __typename
                    name
                }
            }';

        $firstDomainLocale = $this->getFirstDomainLocale();
        $arrayExpected = [
            'data' => [
                'articlesSearch' => [
                    [
                        '__typename' => 'BlogArticle',
                        'name' => t('Blog article for search testing', [], 'dataFixtures', $firstDomainLocale),
                    ],
                    [
                        '__typename' => 'Article',
                        'name' => t('Article for search testing', [], 'dataFixtures', $firstDomainLocale),
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
