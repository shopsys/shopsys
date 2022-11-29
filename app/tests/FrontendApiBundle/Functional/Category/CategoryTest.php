<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category;

use App\DataFixtures\Demo\CategoryDataFixture;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CategoryTest extends GraphQlTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category
     */
    protected Category $category;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
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

        $arrayExpected = [
            'data' => [
                'category' => [
                    'name' => t('Electronics', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                    'description' => t('Our electronics include devices used for entertainment (flat screen TVs, DVD players, DVD movies, iPods, video games, remote control cars, etc.), communications (telephones, cell phones, email-capable laptops, etc.) and home office activities (e.g., desktop computers, printers, paper shredders, etc.).', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                    'slug' => '/elektro',
                    'seoH1' => t('Electronic devices', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                    'seoTitle' => t('Electronic stuff', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                    'seoMetaDescription' => t(
                        'All kind of electronic devices.',
                        [],
                        'dataFixtures',
                        $this->getLocaleForFirstDomain()
                    ),
                    'bestsellers' => [
                        ['name' => t('47" LG 47LA790V (FHD)', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ['name' => t('32" Philips 32PFL4308', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ['name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ['name' => t('A4tech mouse X-710BK, OSCAR Game, 2000DPI, black,', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                    ],
                    'breadcrumb' => [
                        [
                            'name' => t('Electronics', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                            'slug' => $this->urlGenerator->generate('front_product_list', ['id' => $this->category->getId()]),
                        ],
                    ],
                    'readyCategorySeoMixLinks' => [
                        [
                            'name' => t('Electronics without HDMI in sale', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                            'slug' => 'elektro-bez-hdmi-akce',
                        ],
                        [
                            'name' => t('Electronics from most expensive', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                            'slug' => 'elektro-od-nejdrazsiho',
                        ],
                        [
                            'name' => t('Electronics with LED technology and size 30 inch in sale', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                            'slug' => 'elektro-led-uhlopricka-30-akce',
                        ],
                        [
                            'name' => t('Electronics in black', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                            'slug' => 'elektro-barva-cerna',
                        ],
                        [
                            'name' => t('Electronics in red', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                            'slug' => 'elektro-barva-cervena',
                        ],
                        [
                            'name' => t('Full HD Electronics with LED technology and USB', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                            'slug' => 'elektro-full-hd-led-usb',
                        ],
                    ],
                    'linkedCategories' => [
                        ['name' => t('Food', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ['name' => t('Garden tools', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
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
                        ['name' => t('TV, audio', [], 'dataFixtures', $locale)],
                        ['name' => t('Cameras & Photo', [], 'dataFixtures', $locale)],
                        ['name' => t('Printers', [], 'dataFixtures', $locale)],
                        ['name' => t('Personal Computers & accessories', [], 'dataFixtures', $locale)],
                        ['name' => t('Mobile Phones', [], 'dataFixtures', $locale)],
                        ['name' => t('Coffee Machines', [], 'dataFixtures', $locale)],
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
                                'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $locale),
                            ]],
                            ['node' => [
                                'name' => t('32" Philips 32PFL4308', [], 'dataFixtures', $locale),
                            ]],
                            ['node' => [
                                'name' => t('47" LG 47LA790V (FHD)', [], 'dataFixtures', $locale),
                            ]],
                            ['node' => [
                                'name' => t(
                                    'A4tech mouse X-710BK, OSCAR Game, 2000DPI, black,',
                                    [],
                                    'dataFixtures',
                                    $locale
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
