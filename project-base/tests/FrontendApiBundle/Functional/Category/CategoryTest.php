<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CategoryTest extends GraphQlTestCase
{
    protected Category $category;

    protected function setUp(): void
    {
        $categoryFacade = self::getContainer()->get(CategoryFacade::class);
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
                    'name' => t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                    'seoH1' => t('Electronic devices', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                    'seoTitle' => t('Electronic stuff', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                    'seoMetaDescription' => t(
                        'All kind of electronic devices.',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
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
                        ['name' => t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
                        ['name' => t('Cameras & Photo', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
                        ['name' => t('Printers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
                        ['name' => t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
                        ['name' => t('Mobile Phones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
                        ['name' => t('Coffee Machines', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
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
