<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Navigation;

use App\DataFixtures\Demo\CategoryDataFixture;
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
                        "name": "' . t('Catalog', [], 'dataFixtures', $this->getFirstDomainLocale()) . '",
                        "link": "/#",
                        "categoriesByColumns": [
                            {
                                "columnNumber": 1,
                                "categories": [
                                    {
                                        "name": "' . t('Electronics', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                                    },
                                    {
                                        "name": "' . t('Books', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                                    },
                                    {
                                        "name": "' . t('Toys', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                                    }
                                ]
                            },
                            {
                                "columnNumber": 2,
                                "categories": [
                                    {
                                        "name": "' . t('Garden tools', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                                    }
                                ]
                            },
                            {
                                "columnNumber": 3,
                                "categories": [
                                    {
                                        "name": "' . t('Food', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                                    }
                                ]
                            }
                        ]
                    },
                    {
                        "name": "' . t('Electronics', [], 'dataFixtures', $this->getFirstDomainLocale()) . '",
                        "link": "' . $this->getLink(CategoryDataFixture::CATEGORY_ELECTRONICS) . '",
                        "categoriesByColumns": []
                    },
                    {
                        "name": "' . t('Books', [], 'dataFixtures', $this->getFirstDomainLocale()) . '",
                        "link": "' . $this->getLink(CategoryDataFixture::CATEGORY_BOOKS) . '",
                        "categoriesByColumns": []
                    },
                    {
                        "name": "' . t('Garden tools', [], 'dataFixtures', $this->getFirstDomainLocale()) . '",
                        "link": "' . $this->getLink(CategoryDataFixture::CATEGORY_GARDEN_TOOLS) . '",
                        "categoriesByColumns": []
                    },
                    {
                        "name": "' . t('Food', [], 'dataFixtures', $this->getFirstDomainLocale()) . '",
                        "link": "' . $this->getLink(CategoryDataFixture::CATEGORY_FOOD) . '",
                        "categoriesByColumns": []
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
                'id' => $this->getReference($categoryReferenceName)->getId(),
            ],
            UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }
}
