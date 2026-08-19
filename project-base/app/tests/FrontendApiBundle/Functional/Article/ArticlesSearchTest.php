<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Article;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ArticlesSearchTest extends GraphQlTestCase
{
    public function testSearchArticles(): void
    {
        $userIdentifier = Uuid::uuid4()->toString();
        $firstDomainLocale = $this->getFirstDomainLocale();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ArticlesSearch.graphql', [
            'search' => 'Dina',
            'userIdentifier' => $userIdentifier,
        ]);

        $arrayExpected = [
            'data' => [
                'articlesSearch' => [
                    [
                        '__typename' => 'ArticleSite',
                        'name' => t('How Dina chooses reliable electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                    [
                        '__typename' => 'BlogArticle',
                        'name' => t('How to choose the right TV for your living room', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                ],
            ],
        ];

        $this->assertEquals($arrayExpected, $response);
    }

    public function testSearchWorksForShortSearchTerms(): void
    {
        $userIdentifier = Uuid::uuid4()->toString();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ArticlesSearch.graphql', [
            'search' => 'D',
            'userIdentifier' => $userIdentifier,
        ]);

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'articlesSearch');
        $responseData = $this->getResponseDataForGraphQlType($response, 'articlesSearch');
        $this->assertNotEmpty($responseData);
    }
}
