<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CategoryTest extends GraphQlTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category
     */
    protected Category $category;

    protected function setUp(): void
    {
        $categoryFacade = $this->getContainer()->get(CategoryFacade::class);
        $this->category = $categoryFacade->getById(2);

        parent::setUp();
    }

    public function testCategoryNameByUuid(): void
    {
        $query = '
            query {
                category(uuid: "' . $this->category->getUuid() . '") {
                    name
                    seoH1
                    seoTitle
                    seoMetaDescription
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'category' => [
                    'name' => t('Electronics', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                    'seoH1' => t('Electronic devices', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                    'seoTitle' => t('Electronic stuff', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                    'seoMetaDescription' => t(
                        'All kind of electronic devices.',
                        [],
                        'dataFixtures',
                        $this->getLocaleForFirstDomain()
                    ),
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
                    'parent' => [
                        'name' => null,
                    ],
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
