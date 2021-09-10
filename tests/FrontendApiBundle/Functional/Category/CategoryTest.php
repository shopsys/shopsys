<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category;

use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CategoryTest extends GraphQlTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category
     */
    protected $category;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     * @inject
     */
    protected UrlGeneratorInterface $urlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $categoryFacade = $this->getContainer()->get(CategoryFacade::class);
        $this->category = $categoryFacade->getById(2);
    }

    public function testCategoryNameByUuid(): void
    {
        $query = '
            query {
                category(uuid: "' . $this->category->getUuid() . '") {
                    name
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
                        ['name' => 'Elektro Akce - od nejlevnějšího - 47 - bez hdmi', 'slug' => 'elektro-akce-od-nejlevnejsiho-47-bez-hdmi'],
                        ['name' => 'Elektro bez HDMI', 'slug' => 'elektro-bez-hdmi'],
                        ['name' => 'Elektro nejprodávanější - A-Z - 27" - bez HDMI', 'slug' => 'elektro-nejprodavanejsi-a-z-27-bez-hdmi'],
                        ['name' => 'Elektro Novinky - TOP - 27" - HDMI', 'slug' => 'elektro-novinky-top-27-hdmi'],
                        ['name' => 'Elektro s HDMI', 'slug' => 'elektro-s-hdmi'],
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
