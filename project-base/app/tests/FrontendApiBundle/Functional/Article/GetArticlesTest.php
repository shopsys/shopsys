<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Article;

use App\Model\Article\Article;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetArticlesTest extends GraphQlTestCase
{
    private const ARTICLES_TOTAL_COUNT = 20;
    private const QUERY_PATH = __DIR__ . '/../_graphql/query/ArticlesQuery.graphql';

    public function testGetArticles(): void
    {
        foreach ($this->getArticlesDataProvider() as $index => $dataSet) {
            [$response, $expectedArticlesData] = $dataSet;

            $graphQlType = 'articles';
            $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
            $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

            $this->assertArrayHasKey('edges', $responseData);
            $this->assertCount(count($expectedArticlesData), $responseData['edges'], '#' . $index);

            foreach ($responseData['edges'] as $edge) {
                $this->assertArrayHasKey('node', $edge, '#' . $index);

                $this->assertArrayHasKey('uuid', $edge['node'], '#' . $index);
                $this->assertTrue(Uuid::isValid($edge['node']['uuid']), '#' . $index);

                $this->assertKeysAreSameAsExpected(
                    [
                        'name',
                        'placement',
                    ],
                    $edge['node'],
                    array_shift($expectedArticlesData),
                    '#' . $index,
                );

                $this->assertSame(1, $edge['node']['content']['version'], '#' . $index);
                $this->assertSame('web', $edge['node']['content']['profile'], '#' . $index);
                $this->assertNotEmpty($edge['node']['content']['blocks'], '#' . $index);
                $this->assertSame($edge['node']['name'], $edge['node']['seo']['h1'], '#' . $index);
                $this->assertSame(
                    t('%articleTitle% | Demo shop', ['%articleTitle%' => $edge['node']['name']], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                    $edge['node']['seo']['title'],
                    '#' . $index,
                );
                $this->assertNotEmpty($edge['node']['seo']['metaDescription'], '#' . $index);
            }
        }
    }

    private function assertKeysAreSameAsExpected(array $keys, array $actual, array $expected, string $message): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $actual, $message);
            $this->assertSame($expected[$key], $actual[$key], $message);
        }
    }

    private function getArticlesDataProvider(): array
    {
        return [
            [
                $this->getFirstArticlesCountResponse(),
                $this->getExpectedArticles(),
            ],
            [
                $this->getFirstArticlesCountResponse(2),
                array_slice($this->getExpectedArticles(), 0, 2),
            ],
            [
                $this->getFirstArticlesCountResponse(1),
                array_slice($this->getExpectedArticles(), 0, 1),
            ],
            [
                $this->getLastCountOfArticlesResponse(1),
                array_slice($this->getExpectedArticles(), 17, 1),
            ],
            [
                $this->getLastCountOfArticlesResponse(2),
                array_slice($this->getExpectedArticles(), 16, 2),
            ],
            [
                $this->getFirstArticlesCountResponse(1, [Article::PLACEMENT_FOOTER_4]),
                array_slice($this->getExpectedArticles(), 11, 1),
            ],
            [
                $this->getLastCountOfArticlesResponse(1, [Article::PLACEMENT_FOOTER_4]),
                array_slice($this->getExpectedArticles(), 13, 1),
            ],
            [
                $this->getFirstArticlesCountResponse(self::ARTICLES_TOTAL_COUNT, [Article::PLACEMENT_FOOTER_4]),
                array_slice($this->getExpectedArticles(), 11, 3),
            ],
            [
                $this->getFirstArticlesCountResponse(self::ARTICLES_TOTAL_COUNT, [Article::PLACEMENT_FOOTER_1, Article::PLACEMENT_FOOTER_4]),
                [
                    ...array_slice($this->getExpectedArticles(), 0, 4),
                    ...array_slice($this->getExpectedArticles(), 11, 3),
                ],
            ],
        ];
    }

    /**
     * @param string[] $placements
     */
    private function getFirstArticlesCountResponse(
        int $articlesCount = self::ARTICLES_TOTAL_COUNT,
        array $placements = [],
    ): array {
        return $this->getResponseContentForGql(self::QUERY_PATH, [
            'first' => $articlesCount,
            'placement' => $placements,
        ]);
    }

    /**
     * @param string[] $placements
     */
    private function getLastCountOfArticlesResponse(int $articlesCount, array $placements = []): array
    {
        return $this->getResponseContentForGql(self::QUERY_PATH, [
            'last' => $articlesCount,
            'placement' => $placements,
        ]);
    }

    private function getExpectedArticles(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();

        return [
            [
                'name' => t('About us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_1,
            ],
            [
                'name' => t('Job at Shopsys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_1,
            ],
            [
                'name' => t('Cooperation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_1,
            ],
            [
                'name' => t('For press', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_1,
            ],
            [
                'name' => t('Goods care', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_2,
            ],
            [
                'name' => t('Installment plan', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_2,
            ],
            [
                'name' => t('Complaint', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_2,
            ],
            [
                'name' => t('Frequently Asked Questions FAQ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_3,
            ],
            [
                'name' => t('Transport and payment', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_3,
            ],
            [
                'name' => t('Withdrawal from contract', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_3,
            ],
            [
                'name' => t('Terms and conditions of eshop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_3,
            ],
            [
                'name' => t('Where to find us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_4,
            ],
            [
                'name' => t('Department stores services', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_4,
            ],
            [
                'name' => t('Terms and conditions of department stores', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_4,
            ],
            [
                'name' => t('Privacy policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_NONE,
            ],
            [
                'name' => t('User consent policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_NONE,
            ],
            [
                'name' => t('How we work with reviews', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_NONE,
            ],
            [
                'name' => t('How Dina chooses reliable electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_NONE,
            ],
        ];
    }
}
