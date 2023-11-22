<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Article;

use App\Model\Article\Article;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetArticlesTest extends GraphQlTestCase
{
    private const ARTICLES_TOTAL_COUNT = 21;
    private const QUERY_PATH = __DIR__ . '/../_graphql/query/ArticlesQuery.graphql';

    public function testGetArticles(): void
    {
        foreach ($this->getArticlesDataProvider() as $index => $dataSet) {
            [$response, $expectedArticlesData] = $dataSet;

            $graphQlType = 'articles';
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
                    array_shift($expectedArticlesData),
                );
            }
        }
    }

    /**
     * @param mixed[] $keys
     * @param mixed[] $actual
     * @param mixed[] $expected
     */
    private function assertKeysAreSameAsExpected(array $keys, array $actual, array $expected): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $actual);
            $this->assertSame($expected[$key], $actual[$key]);
        }
    }

    /**
     * @return mixed[][][]
     */
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
                array_slice($this->getExpectedArticles(), 18, 1),
            ],
            [
                $this->getLastCountOfArticlesResponse(2),
                array_slice($this->getExpectedArticles(), 17, 2),
            ],
            [
                $this->getFirstArticlesCountResponse(1, [Article::PLACEMENT_FOOTER_4]),
                array_slice($this->getExpectedArticles(), 13, 1),
            ],
            [
                $this->getLastCountOfArticlesResponse(1, [Article::PLACEMENT_FOOTER_4]),
                array_slice($this->getExpectedArticles(), 15, 1),
            ],
            [
                $this->getFirstArticlesCountResponse(self::ARTICLES_TOTAL_COUNT, [Article::PLACEMENT_FOOTER_4]),
                array_slice($this->getExpectedArticles(), 13, 3),
            ],
            [
                $this->getFirstArticlesCountResponse(self::ARTICLES_TOTAL_COUNT, [Article::PLACEMENT_FOOTER_1, Article::PLACEMENT_FOOTER_4]),
                [
                    ...array_slice($this->getExpectedArticles(), 0, 5),
                    ...array_slice($this->getExpectedArticles(), 13, 3),
                ],
            ],
        ];
    }

    /**
     * @param int $articlesCount
     * @param string[] $placements
     * @return array<string, mixed>
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
     * @param int $articlesCount
     * @param string[] $placements
     * @return array<string, mixed>
     */
    private function getLastCountOfArticlesResponse(int $articlesCount, array $placements = []): array
    {
        return $this->getResponseContentForGql(self::QUERY_PATH, [
            'last' => $articlesCount,
            'placement' => $placements,
        ]);
    }

    /**
     * @return array<int, array<string|null>>
     */
    private function getExpectedArticles(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();

        return [
            [
                'name' => t('About us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_1,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Job at Shopsys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_1,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Cooperation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_1,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('For press', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_1,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Contacts', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_1,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Goods care', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_2,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Installment plan', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_2,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Complaint', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_2,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Frequently Asked Questions FAQ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_3,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Frequently Asked Questions FAQ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_3,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Transport and payment', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_3,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Withdrawal from contract', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_3,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Terms and conditions of eshop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_3,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Where to find us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_4,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Department stores services', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_4,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Terms and conditions of department stores', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_4,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Privacy policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_NONE,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Information about cookies', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_NONE,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Article for search testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_NONE,
                'text' => '<div class="gjs-text-ckeditor">' . t('Article text for search testing, the search phrase is &#34;Dina&#34;.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
        ];
    }
}
