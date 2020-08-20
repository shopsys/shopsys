<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Article;

use Ramsey\Uuid\Uuid;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetArticlesTest extends GraphQlTestCase
{
    /**
     * @param string $query
     * @param array $expectedArticlesData
     * @dataProvider getArticlesDataProvider
     */
    public function testGetArticles(string $query, array $expectedArticlesData): void
    {
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

    /**
     * @param array $keys
     * @param array $actual
     * @param array $expected
     */
    private function assertKeysAreSameAsExpected(array $keys, array $actual, array $expected): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey('placement', $actual);
            $this->assertSame($expected[$key], $actual[$key]);
        }
    }

    /**
     * @return array
     */
    public function getArticlesDataProvider(): array
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
                $this->getFirstArticlesQuery(1, 'topMenu'),
                array_slice($this->getExpectedArticles(), 3, 1),
            ],
            [
                $this->getLastArticlesQuery(1, 'topMenu'),
                array_slice($this->getExpectedArticles(), 4, 1),
            ],
            [
                $this->getAllArticlesQuery('topMenu'),
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
        return [
            [
                'name' => 'Terms and conditions',
                'placement' => 'footer',
                'text' => 'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => 'Privacy policy',
                'placement' => 'none',
                'text' => 'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => 'Information about cookies',
                'placement' => 'none',
                'text' => 'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => 'News',
                'placement' => 'topMenu',
                'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => 'Shopping guide',
                'placement' => 'topMenu',
                'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.',
                'seoH1' => 'Shopping guide to improve your shopping experience',
                'seoTitle' => 'Shopping guide for quick shopping',
                'seoMetaDescription' => 'Shopping guide - Tips and tricks how to quickly find what you are looking for',
            ],
        ];
    }
}
