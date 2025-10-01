<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Article;

use App\DataFixtures\Demo\BlogArticleDataFixture;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
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

        $this->assertEquals($arrayExpected, $response);
    }

    public function testSearchWorksForShortSearchTerms(): void
    {
        $firstDomainLocale = $this->getFirstDomainLocale();
        $userIdentifier = Uuid::uuid4()->toString();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ArticlesSearch.graphql', [
            'search' => '1',
            'userIdentifier' => $userIdentifier,
        ]);

        $expectedFirstArticle = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_ARTICLE, BlogArticle::class);

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'articlesSearch');
        $responseData = $this->getResponseDataForGraphQlType($response, 'articlesSearch');
        $this->assertSame($expectedFirstArticle->getName($firstDomainLocale), $responseData[0]['name']);
    }
}
