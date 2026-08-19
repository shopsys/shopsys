<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Navigation;

use App\Component\FriendlyUrl\FriendlyUrlRouteEnum;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\Model\Category\Category;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItemTypeEnum;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class NavigationTest extends GraphQlTestCase
{
    public function testNavigation(): void
    {
        $expectedData = [
            [
                'name' => t('Catalog', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                'type' => NavigationItemTypeEnum::CATEGORIES,
                'link' => null,
                'routeName' => null,
                'categoriesByColumns' => [
                    [
                        'columnNumber' => 1,
                        'categories' => [
                            $this->getExpectedCategory('Electronics', [
                                'TV, audio',
                                'Cameras & Photo',
                                'Printers',
                                'Personal Computers & accessories',
                                'Mobile Phones',
                                'Coffee Machines',
                            ]),
                            $this->getExpectedCategory('Books', [
                                'Fiction',
                                'Non-fiction',
                                'Children\'s books',
                            ]),
                            $this->getExpectedCategory('Toys', [
                                'Building sets',
                                'Board games',
                                'Outdoor toys',
                            ]),
                        ],
                    ],
                    [
                        'columnNumber' => 2,
                        'categories' => [
                            $this->getExpectedCategory('Garden tools', [
                                'Hand tools',
                                'Power tools',
                                'Watering systems',
                            ]),
                        ],
                    ],
                    [
                        'columnNumber' => 3,
                        'categories' => [
                            $this->getExpectedCategory('Food', [
                                'Snacks',
                                'Coffee & tea',
                                'Pantry staples',
                            ]),
                        ],
                    ],
                ],
            ],
            [
                'name' => t('Gadgets', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                'type' => NavigationItemTypeEnum::CATEGORIES,
                'link' => null,
                'routeName' => null,
                'categoriesByColumns' => [
                    [
                        'columnNumber' => 1,
                        'categories' => [
                            $this->getExpectedCategory('Personal Computers & accessories', [
                                'Laptops',
                                'Desktop computers',
                                'Computer accessories',
                            ]),
                            $this->getExpectedCategory('Printers', [
                                'Inkjet printers',
                                'Laser printers',
                                'Printer supplies',
                            ]),
                        ],
                    ],
                    [
                        'columnNumber' => 2,
                        'categories' => [
                            $this->getExpectedCategory('TV, audio', [
                                'Televisions',
                                'Headphones',
                                'Home cinema systems',
                            ]),
                            $this->getExpectedCategory('Cameras & Photo', [
                                'Digital cameras',
                                'Camera lenses',
                                'Camera accessories',
                            ]),
                        ],
                    ],
                    [
                        'columnNumber' => 3,
                        'categories' => [
                            $this->getExpectedCategory('Mobile Phones', [
                                'Smartphones',
                                'Mobile phone accessories',
                                'Smartwatches',
                            ]),
                        ],
                    ],
                ],
            ],
            [
                'name' => t('Bookworm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                'type' => NavigationItemTypeEnum::CATEGORIES,
                'link' => null,
                'routeName' => null,
                'categoriesByColumns' => [
                    [
                        'columnNumber' => 1,
                        'categories' => [
                            $this->getExpectedCategory('Books', [
                                'Fiction',
                                'Non-fiction',
                                'Children\'s books',
                            ]),
                        ],
                    ],
                ],
            ],
            [
                'name' => t('Growing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                'type' => NavigationItemTypeEnum::LINK,
                'link' => $this->getLink(CategoryDataFixture::CATEGORY_GARDEN_TOOLS),
                'routeName' => FriendlyUrlRouteEnum::FRONT_PRODUCT_LIST->name,
                'categoriesByColumns' => [],
            ],
            [
                'name' => t('Snack', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                'type' => NavigationItemTypeEnum::CATEGORIES,
                'link' => null,
                'routeName' => null,
                'categoriesByColumns' => [
                    [
                        'columnNumber' => 1,
                        'categories' => [
                            $this->getExpectedCategory('Food', [
                                'Snacks',
                                'Coffee & tea',
                                'Pantry staples',
                            ]),
                        ],
                    ],
                    [
                        'columnNumber' => 2,
                        'categories' => [
                            $this->getExpectedCategory('Coffee Machines', [
                                'Automatic coffee machines',
                                'Capsule coffee machines',
                                'Coffee grinders',
                            ]),
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/NavigationQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'navigation');

        $this->assertSame($expectedData, $responseData);
    }

    /**
     * @param string[] $children
     * @return array{name: string, children: array<int, array{name: string}>}
     */
    private function getExpectedCategory(string $name, array $children): array
    {
        return [
            'name' => t($name, [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            'children' => array_map(
                fn (string $childName) => [
                    'name' => t(
                        $childName,
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $this->getFirstDomainLocale(),
                    ),
                ],
                $children,
            ),
        ];
    }

    private function getLink(string $categoryReferenceName): string
    {
        return $this->getLocalizedPathOnFirstDomainByRouteName(
            FriendlyUrlRouteEnum::FRONT_PRODUCT_LIST->value,
            [
                'id' => $this->getReference($categoryReferenceName, Category::class)->getId(),
            ],
            DomainRouter::SLUG,
        );
    }
}
