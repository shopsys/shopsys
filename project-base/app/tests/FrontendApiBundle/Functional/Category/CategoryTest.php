<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category;

use App\DataFixtures\Demo\CategoryDataFixture;
use Shopsys\FrameworkBundle\Component\ArrayUtils\ArraySorter;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CategoryTest extends GraphQlTestCase
{
    protected Category $category;

    /**
     * @inject
     */
    protected UrlGeneratorInterface $urlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS);
    }

    public function testCategoryNameByUuid(): void
    {
        $query = '
            query {
                category(uuid: "' . $this->category->getUuid() . '") {
                    name
                    description
                    slug
                    seoH1
                    seoTitle
                    seoMetaDescription
                    bestsellers {
                        name
                    }
                    breadcrumb {
                        name
                        slug
                    }
                    readyCategorySeoMixLinks {
                        name
                        slug
                    }
                    linkedCategories {
                        name
                    }
                }
            }
        ';

        $readyCategorySeoMixLinks = [
            [
                'name' => t('Electronics from most expensive', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-od-nejdrazsiho',
            ],
            [
                'name' => t('Electronics in black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-barva-cerna',
            ],
            [
                'name' => t('Electronics in red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-barva-cervena',
            ],
            [
                'name' => t('Electronics with LED technology and size 30 inch in sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-led-uhlopricka-30-akce',
            ],
            [
                'name' => t('Electronics without HDMI in sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-bez-hdmi-akce',
            ],
            [
                'name' => t('Full HD Electronics with LED technology and USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-full-hd-led-usb',
            ],
        ];

        ArraySorter::sortArrayAlphabeticallyByValue('name', $readyCategorySeoMixLinks, $this->getLocaleForFirstDomain());

        $electronicsName = t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $electronicsSlug = '/' . TransformString::stringToFriendlyUrlSlug($electronicsName);

        $arrayExpected = [
            'data' => [
                'category' => [
                    'name' => $electronicsName,
                    'description' => t('Our electronics include devices used for entertainment (flat screen TVs, DVD players, DVD movies, iPods, video games, remote control cars, etc.), communications (telephones, cell phones, email-capable laptops, etc.) and home office activities (e.g., desktop computers, printers, paper shredders, etc.).', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                    'slug' => $electronicsSlug,
                    'seoH1' => t('Electronic devices', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                    'seoTitle' => t('Electronic stuff', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                    'seoMetaDescription' => t(
                        'All kind of electronic devices.',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $this->getLocaleForFirstDomain(),
                    ),
                    'bestsellers' => [
                        ['name' => t('47" LG 47LA790V (FHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                        ['name' => t('32" Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                        ['name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                        ['name' => t('A4tech mouse X-710BK, OSCAR Game, 2000DPI, black,', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ],
                    'breadcrumb' => [
                        [
                            'name' => $electronicsName,
                            'slug' => $this->urlGenerator->generate('front_product_list', ['id' => $this->category->getId()]),
                        ],
                    ],
                    'readyCategorySeoMixLinks' => $readyCategorySeoMixLinks,
                    'linkedCategories' => [
                        ['name' => t('Food', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                        ['name' => t('Garden tools', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testCategoryParentAndChildNameByUuid(): void
    {
        $query = '
            query {
                category(uuid: "' . $this->category->getUuid() . '") {
                    children {
                        name
                    }
                    parent {
                        name
                    }
                }
            }
        ';

        $locale = $this->getLocaleForFirstDomain();

        $arrayExpected = [
            'data' => [
                'category' => [
                    'children' => [
                        ['name' => t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
                        ['name' => t('Cameras & Photo', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
                        ['name' => t('Printers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
                        ['name' => t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
                        ['name' => t('Mobile Phones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
                        ['name' => t('Coffee Machines', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
                    ],
                    'parent' => null,
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testCategoryProductsByUuid(): void
    {
        $query = '
            query {
                category(uuid: "' . $this->category->getUuid() . '") {
                    products (first: 10) {
                        edges {
                            ... on ProductEdge {
                                node {
                                    name
                                }
                            }
                        }
                    }
                }
            }
        ';

        $locale = $this->getLocaleForFirstDomain();

        $arrayExpected = [
            'data' => [
                'category' => [
                    'products' => [
                        'edges' => [
                            ['node' => [
                                'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                            ]],
                            ['node' => [
                                'name' => t('32" Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                            ]],
                            ['node' => [
                                'name' => t('47" LG 47LA790V (FHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                            ]],
                            ['node' => [
                                'name' => t(
                                    'A4tech mouse X-710BK, OSCAR Game, 2000DPI, black,',
                                    [],
                                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                                    $locale,
                                ),
                            ]],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
