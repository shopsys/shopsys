<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Navigation;

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

        $jsonExpected = '{
            "data": {
                "navigation": [
                    {
                        "name": "' . t('Catalog', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
                        "link": "' . $this->getLink(CategoryDataFixture::CATEGORY_ELECTRONICS) . '",
                        "categoriesByColumns": [
                            {
                                "columnNumber": 1,
                                "categories": [
                                    {
                                        "name": "' . t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                                    },
                                    {
                                        "name": "' . t('Books', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                                    },
                                    {
                                        "name": "' . t('Toys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                                    }
                                ]
                            },
                            {
                                "columnNumber": 2,
                                "categories": [
                                    {
                                        "name": "' . t('Garden tools', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                                    }
                                ]
                            },
                            {
                                "columnNumber": 3,
                                "categories": [
                                    {
                                        "name": "' . t('Food', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                                    }
                                ]
                            }
                        ]
                    },
                    {
                        "name": "' . t('Gadgets', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
                        "link": "' . $this->getLink(CategoryDataFixture::CATEGORY_ELECTRONICS) . '",
                        "categoriesByColumns": [
                            {
                                "columnNumber": 1,
                                "categories": [
                                    {
                                        "name": "' . t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                                    },
                                    {
                                        "name": "' . t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                                    }
                                ]
                            },
                            {
                                "columnNumber": 2,
                                "categories": [
                                    {
                                        "name": "' . t('Printers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                                    },
                                    {
                                        "name": "' . t('Cameras & Photo', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                                    }
                                ]
                            },
                            {
                                "columnNumber": 3,
                                "categories": [
                                    {
                                        "name": "' . t('Coffee Machines', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                                    },
                                    {
                                        "name": "' . t('Mobile Phones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                                    }
                                ]
                            }
                        ]
                    },
                    {
                        "name": "' . t('Bookworm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
                        "link": "' . $this->getLink(CategoryDataFixture::CATEGORY_BOOKS) . '",
                        "categoriesByColumns": [
                            {
                                "columnNumber": 1,
                                "categories": [
                                    {
                                        "name": "' . t('Books', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                                    }
                                ]
                            },
                            {
                                "columnNumber": 2,
                                "categories": [
                                    {
                                        "name": "' . t('Printers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                                    }
                                ]
                            }
                        ]
                    },
                    {
                        "name": "' . t('Growing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
                        "link": "' . $this->getLink(CategoryDataFixture::CATEGORY_GARDEN_TOOLS) . '",
                        "categoriesByColumns": []
                    },
                    {
                        "name": "' . t('Snack', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
                        "link": "' . $this->getLink(CategoryDataFixture::CATEGORY_FOOD) . '",
                        "categoriesByColumns": [
                            {
                                "columnNumber": 1,
                                "categories": [
                                    {
                                        "name": "' . t('Food', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                                    }
                                ]
                            },
                            {
                                "columnNumber": 2,
                                "categories": [
                                    {
                                        "name": "' . t('Coffee Machines', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                                    }
                                ]
                            }]
                    }
                ]
            }
        }';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }

    /**
     * @param string $categoryReferenceName
     * @return string
     */
    private function getLink(string $categoryReferenceName): string
    {
        return $this->getLocalizedPathOnFirstDomainByRouteName(
            'front_product_list',
            [
                'id' => $this->getReference($categoryReferenceName, Category::class)->getId(),
            ],
            UrlGeneratorInterface::ABSOLUTE_PATH,
        );
    }
}
