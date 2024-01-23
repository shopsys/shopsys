<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Article;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ArticlesSearchTest extends GraphQlTestCase
{
    public function testSearchArticles()
    {
        $userIdentifier = Uuid::uuid4()->toString();

        $query = '
            query {
                articlesSearch(searchInput: { search: "Dina", userIdentifier: "' . $userIdentifier . '" }) {
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
