<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Article;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetArticlesTest extends GraphQlTestCase
{
    public function testGetArticles(): void
    {
        foreach ($this->getArticlesDataProvider() as $dataSet) {
            [$query, $expectedArticlesData] = $dataSet;

            $graphQlType = 'articles';
            $response = $this->getResponseContentForQuery($query);
            $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
            $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

            $this->assertArrayHasKey('edges', $responseData);
            $this->assertCount(count($expectedArticlesData), $responseData['edges']);

            foreach ($responseData['edges'] as $edge) {
                $this->assertArrayHasKey('node', $edge);

                $this->assertArrayHasKey('uuid', $edge['node']);
                $this->assertTrue(Uuid::isValid($edge['node']['uuid']));

                $this->assertKeysAreSameAsExpected(
                    [
                        'name',
                        'placement',
                        'text',
                        'seoH1',
                        'seoTitle',
                        'seoMetaDescription',
                    ],
                    $edge['node'],
                    array_shift($expectedArticlesData)
                );
            }
        }
    }

    /**
     * @param array $keys
     * @param array $actual
     * @param array $expected
     */
    private function assertKeysAreSameAsExpected(array $keys, array $actual, array $expected): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $actual);
            $this->assertSame($expected[$key], $actual[$key]);
        }
    }

    /**
     * @return array
     */
    private function getArticlesDataProvider(): array
    {
        return [
            [
                $this->getAllArticlesQuery(),
                $this->getExpectedArticles(),
            ],
            [
                $this->getFirstArticlesQuery(2),
                array_slice($this->getExpectedArticles(), 0, 2),
            ],
            [
                $this->getFirstArticlesQuery(1),
                array_slice($this->getExpectedArticles(), 0, 1),
            ],
            [
                $this->getLastArticlesQuery(1),
                array_slice($this->getExpectedArticles(), 4, 1),
            ],
            [
                $this->getLastArticlesQuery(2),
                array_slice($this->getExpectedArticles(), 3, 2),
            ],
            [
                $this->getFirstArticlesQuery(1, Article::PLACEMENT_TOP_MENU),
                array_slice($this->getExpectedArticles(), 3, 1),
            ],
            [
                $this->getLastArticlesQuery(1, Article::PLACEMENT_TOP_MENU),
                array_slice($this->getExpectedArticles(), 4, 1),
            ],
            [
                $this->getAllArticlesQuery(Article::PLACEMENT_TOP_MENU),
                array_slice($this->getExpectedArticles(), 3, 2),
            ],
            [
                $this->getAllArticlesQuery('non-existing-placement'),
                [],
            ],
        ];
    }

    /**
     * @param string|null $placement
     * @return string
     */
    private function getAllArticlesQuery(?string $placement = null): string
    {
        if ($placement !== null) {
            $graphQlTypeWithFilters = 'articles (placement:"' . $placement . '")';
        } else {
            $graphQlTypeWithFilters = 'articles';
        }
        return '
            {
                ' . $graphQlTypeWithFilters . ' {
                    edges {
                        node {
                            uuid
                            name
                            placement
                            text
                            seoH1
                            seoTitle
                            seoMetaDescription
                        }
                    }
                }
            }
        ';
    }

    /**
     * @param int $numberOfArticles
     * @param string|null $placement
     * @return string
     */
    private function getFirstArticlesQuery(int $numberOfArticles, ?string $placement = null): string
    {
        if ($placement !== null) {
            $graphQlTypeWithFilters = 'articles (first:' . $numberOfArticles . ', placement: "' . $placement . '")';
        } else {
            $graphQlTypeWithFilters = 'articles (first:' . $numberOfArticles . ')';
        }

        return '
            {
                ' . $graphQlTypeWithFilters . ' {
                    edges {
                        node {
                            uuid
                            name
                            placement
                            text
                            seoH1
                            seoTitle
                            seoMetaDescription
                        }
                    }
                }
            }
        ';
    }

    /**
     * @param int $numberOfArticles
     * @param string|null $placement
     * @return string
     */
    private function getLastArticlesQuery(int $numberOfArticles, ?string $placement = null): string
    {
        if ($placement !== null) {
            $graphQlTypeWithFilters = 'articles (last:' . $numberOfArticles . ', placement: "' . $placement . '")';
        } else {
            $graphQlTypeWithFilters = 'articles (last:' . $numberOfArticles . ')';
        }

        return '
            {
                ' . $graphQlTypeWithFilters . ' {
                    edges {
                        node {
                            uuid
                            name
                            placement
                            text
                            seoH1
                            seoTitle
                            seoMetaDescription
                        }
                    }
                }
            }
        ';
    }

    /**
     * @return array
     */
    private function getExpectedArticles(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        return [
            [
                'name' => t('Terms and conditions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER,
                'text' => t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale
                ),
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Privacy policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_NONE,
                'text' => t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale
                ),
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Information about cookies', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_NONE,
                'text' => t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale
                ),
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('News', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_TOP_MENU,
                'text' => t(
                    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale
                ),
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Shopping guide', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_TOP_MENU,
                'text' => t(
                    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale
                ),
                'seoH1' => t(
                    'Shopping guide to improve your shopping experience',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale
                ),
                'seoTitle' => t('Shopping guide for quick shopping', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'seoMetaDescription' => t(
                    'Shopping guide - Tips and tricks how to quickly find what you are looking for',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale
                ),
            ],
        ];
    }
}
