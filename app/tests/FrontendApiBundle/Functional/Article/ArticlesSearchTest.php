<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Article;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
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
                        'name' => t('Blog article for search testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                    [
                        '__typename' => 'ArticleSite',
                        'name' => t('Article for search testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
