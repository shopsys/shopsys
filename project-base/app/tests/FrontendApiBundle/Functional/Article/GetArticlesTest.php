<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Article;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\Model\Article\Article;
use App\Model\Category\Category;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetArticlesTest extends GraphQlTestCase
{
    private const ARTICLES_TOTAL_COUNT = 20;
    private const QUERY_PATH = __DIR__ . '/../_graphql/query/ArticlesQuery.graphql';

    /**
     * @inject
     */
    private DomainRouterFactory $domainRouterFactory;

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
                        'text',
                        'seoH1',
                        'seoTitle',
                        'seoMetaDescription',
                    ],
                    $edge['node'],
                    array_shift($expectedArticlesData),
                    '#' . $index,
                );
            }
        }
    }

    /**
     * @param array $keys
     * @param array $actual
     * @param array $expected
     * @param string $message
     */
    private function assertKeysAreSameAsExpected(array $keys, array $actual, array $expected, string $message): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $actual, $message);
            $this->assertSame($expected[$key], $actual[$key], $message);
        }
    }

    /**
     * @return array
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
                array_slice($this->getExpectedArticles(), 16, 1),
            ],
            [
                $this->getLastCountOfArticlesResponse(2),
                array_slice($this->getExpectedArticles(), 15, 2),
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
     * @param int $articlesCount
     * @param string[] $placements
     * @return array
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
     * @return array
     */
    private function getLastCountOfArticlesResponse(int $articlesCount, array $placements = []): array
    {
        return $this->getResponseContentForGql(self::QUERY_PATH, [
            'last' => $articlesCount,
            'placement' => $placements,
        ]);
    }

    /**
     * @return array
     */
    private function getExpectedArticles(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $firstDomainId = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getId();
        $homepageUrl = $this->generateUrlForHomepageOnDomain($firstDomainId);
        $categoryElectronicsUrl = $this->generateUrlForCategoryOnDomain(CategoryDataFixture::CATEGORY_ELECTRONICS, $firstDomainId);

        return [
            [
                'name' => t('About us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_1,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
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
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
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
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
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
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '
                    |||[gjc-comp-ProductList&#61;9177759,9176508,5965879P,532564,1532564,5960453]|||
                    <div class="row">
                        <div class="column">
                            <div class="gjs-text-ckeditor" id="ic192p" style="text-align: center">
                                Eius distinctio numquam doloremque quas debitis. Nam unde, et quos nesciunt mollitia nostrum molestiae
                                incidunt offic.
                            </div>
                        </div>
                        <div class="column">
                            <div class="gjs-text-ckeditor" id="itv9uf" style="text-align: center">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Libero, esse! Eius distinctio numquam doloremque
                                quas debitis. Nam unde, et quos nesciunt mollitia nostrum molestiae incidunt officiis dolorum similique ab
                                nihil?
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div id="iv009r" class="column" style="text-align: center">
                            <div class="gjs-text-ckeditor">Integer id pretium quam, id placerat nulla.</div>
                        </div>
                        <div id="idyvqa" class="column" style="text-align: center">
                            <div class="gjs-text-ckeditor">Nam auctor neque quis tincidunt tempus</div>
                        </div>
                        <div id="i0updc" class="column" style="text-align: center">
                            <div class="gjs-text-ckeditor">Praesent tristique lorem mi, eget varius quam aliquam eget.</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="column">
                            <div class="gjs-text-ckeditor">
                                <h2 id="i75nl3" draggable="true">H2 Integer id pretium quam, id placerat nulla.</h2>
                            </div>
                            <div class="gjs-text-with-image gjs-text-with-image-float-left">
                                <img src="' . $homepageUrl . 'content/images/blogArticle/default/600.jpg" class="image" />
                                <div class="gjs-text-ckeditor text">
                                    Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed
                                    placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a
                                    arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor
                                    neque quis tincidunt tempus
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="column">
                            <div class="gjs-text-ckeditor">
                                <h3 id="i41lfa" draggable="true" style="margin: 0px 0px 0px 0px">H3 Id placerat nulla.</h3>
                            </div>
                            <div class="gjs-table-custom" rows="2" id="ifvoxk" style="margin: 20px 0px 20px 0px">
                                <table class="default">
                                    <tbody>
                                        <tr>
                                            <td><div class="gjs-text-ckeditor text">1st row 1st column</div></td>
                                            <td><div class="gjs-text-ckeditor text">1st row 2th column</div></td>
                                        </tr>
                                        <tr>
                                            <td><div class="gjs-text-ckeditor text">2nd row 1st column</div></td>
                                            <td><div class="gjs-text-ckeditor text">2nd row 2nd columns</div></td>
                                        </tr>
                                        <tr>
                                            <td><div class="gjs-text-ckeditor text">3rd row 1st column</div></td>
                                            <td><div class="gjs-text-ckeditor text">3nd row 2nd columns</div></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="column">
                            <div class="gjs-text-ckeditor">
                                <h4 id="ioawsm" draggable="true">H4 Vidson</h4>
                            </div>
                            <video allowfullscreen="allowfullscreen" src="data:image/svg&#43;xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIHZpZXdCb3g9IjAgMCAyNCAyNCIgc3R5bGU9ImZpbGw6IHJnYmEoMCwwLDAsMC4xNSk7IHRyYW5zZm9ybTogc2NhbGUoMC43NSkiPgogICAgICAgIDxwYXRoIGQ9Ik04LjUgMTMuNWwyLjUgMyAzLjUtNC41IDQuNSA2SDVtMTYgMVY1YTIgMiAwIDAgMC0yLTJINWMtMS4xIDAtMiAuOS0yIDJ2MTRjMCAxLjEuOSAyIDIgMmgxNGMxLjEgMCAyLS45IDItMnoiPjwvcGF0aD4KICAgICAgPC9zdmc&#43;" controls="controls" id="ijwm64" style="margin: 0px 0px 0 0px"></video>
                        </div>
                    </div>
                    <div class="row">
                        <div class="column">
                            <div class="gjs-text-ckeditor">
                                <h5 id="i4utlb" draggable="true">H5 Mapson</h5>
                            </div>
                            <iframe frameborder="0" id="in1zyi" src="https://maps.google.com/maps?&amp;z&#61;1&amp;t&#61;q&amp;output&#61;embed" style="height: 350px; width: 100%"></iframe>
                        </div>
                    </div>
                    <div class="row">
                        <div class="column">
                            <div class="gjs-text-ckeditor">
                                <h6 id="i1dz5h" draggable="true">H6 Imegson</h6>
                            </div>
                            <img src="' . $homepageUrl . 'content/images/blogArticle/default/601.jpg" class="image-position-left" />
                        </div>
                    </div>
                    <a class="gjs-button-link button-link-position-center" title="More products" href="' . $categoryElectronicsUrl . '">
                        <div class="gjs-text-ckeditor text">More products</div>
                    </a>
            </div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('Goods care', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_FOOTER_2,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
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
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
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
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
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
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
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
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
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
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
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
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
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
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
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
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
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
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
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
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
            [
                'name' => t('User consent policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'placement' => Article::PLACEMENT_NONE,
                'text' => '<div class="gjs-text-ckeditor">' . t(
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
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
                'text' => '<div class="gjs-text-ckeditor">' . t('<p>Article text for search testing, the search phrase is &#34;Dina&#34;.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale) . '</div>',
                'seoH1' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
            ],
        ];
    }

    /**
     * @param int $domainId
     * @return string
     */
    private function generateUrlForHomepageOnDomain(int $domainId): string
    {
        $router = $this->domainRouterFactory->getRouter($domainId);

        return $router->generate('front_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param string $categoryReferenceName
     * @param int $domainId
     * @return string
     */
    private function generateUrlForCategoryOnDomain(string $categoryReferenceName, int $domainId): string
    {
        $router = $this->domainRouterFactory->getRouter($domainId);
        $categoryReference = $this->getReference($categoryReferenceName, Category::class);

        return $router->generate(
            'front_product_list',
            ['id' => $categoryReference->getId()],
            UrlGeneratorInterface::RELATIVE_PATH,
        );
    }
}
