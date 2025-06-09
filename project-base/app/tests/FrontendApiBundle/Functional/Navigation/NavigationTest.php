<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Navigation;

use App\Component\FriendlyUrl\FriendlyUrlRouteEnum;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\Model\Category\Category;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class NavigationTest extends GraphQlTestCase
{
    public function testNavigation(): void
    {
        $query = '
            query {
                navigation {
                    name
                    link
                    categoriesByColumns {
                        columnNumber
                        categories {
                            name
                        }
                    }
                }
            }
        ';

        $expectedData = [
            [
                'name' => t('Catalog', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                'link' => $this->getLink(CategoryDataFixture::CATEGORY_ELECTRONICS),
                'routeName' => FriendlyUrlRouteEnum::FRONT_PRODUCT_LIST->name,
                'categoriesByColumns' => [
                    [
                        'columnNumber' => 1,
                        'categories' => [
                            [
                                'name' => t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                            [
                                'name' => t('Books', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                            [
                                'name' => t('Newest toys in stock', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                    ],
                    [
                        'columnNumber' => 2,
                        'categories' => [
                            [
                                'name' => t('Garden tools', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                    ],
                    [
                        'columnNumber' => 3,
                        'categories' => [
                            [
                                'name' => t('Food', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => t('Gadgets', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                'link' => $this->getLink(CategoryDataFixture::CATEGORY_ELECTRONICS),
                'routeName' => FriendlyUrlRouteEnum::FRONT_PRODUCT_LIST->name,
                'categoriesByColumns' => [
                    [
                        'columnNumber' => 1,
                        'categories' => [
                            [
                                'name' => t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                            [
                                'name' => t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                    ],
                    [
                        'columnNumber' => 2,
                        'categories' => [
                            [
                                'name' => t('Printers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                            [
                                'name' => t('Cameras & Photo', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                    ],
                    [
                        'columnNumber' => 3,
                        'categories' => [
                            [
                                'name' => t('Coffee Machines', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                            [
                                'name' => t('Mobile Phones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => t('Bookworm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                'link' => $this->getLink(CategoryDataFixture::CATEGORY_BOOKS),
                'routeName' => FriendlyUrlRouteEnum::FRONT_PRODUCT_LIST->name,
                'categoriesByColumns' => [
                    [
                        'columnNumber' => 1,
                        'categories' => [
                            [
                                'name' => t('Books', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                    ],
                    [
                        'columnNumber' => 2,
                        'categories' => [
                            [
                                'name' => t('Printers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => t('Growing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                'link' => $this->getLink(CategoryDataFixture::CATEGORY_GARDEN_TOOLS),
                'routeName' => FriendlyUrlRouteEnum::FRONT_PRODUCT_LIST->name,
                'categoriesByColumns' => [],
            ],
            [
                'name' => t('Snack', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                'link' => $this->getLink(CategoryDataFixture::CATEGORY_FOOD),
                'routeName' => FriendlyUrlRouteEnum::FRONT_PRODUCT_LIST->name,
                'categoriesByColumns' => [
                    [
                        'columnNumber' => 1,
                        'categories' => [
                            [
                                'name' => t('Food', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                    ],
                    [
                        'columnNumber' => 2,
                        'categories' => [
                            [
                                'name' => t('Coffee Machines', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
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
     * @param string $categoryReferenceName
     * @return string
     */
    private function getLink(string $categoryReferenceName): string
    {
        return $this->getLocalizedPathOnFirstDomainByRouteName(
            FriendlyUrlRouteEnum::FRONT_PRODUCT_LIST->value,
            [
                'id' => $this->getReference($categoryReferenceName, Category::class)->getId(),
            ],
            UrlGeneratorInterface::ABSOLUTE_PATH,
        );
    }
}
