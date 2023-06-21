<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PromotedCategoriesTest extends GraphQlTestCase
{
    public function testPromotedCategoriesWithName(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $query = '
            query {
                promotedCategories {
                    name
                }
            }
        ';

        $expectedCategories = [
            ['name' => t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('Books', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('Toys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
        ];

        $graphQlType = 'promotedCategories';
        $response = $this->getResponseContentForQuery($query);

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        self::assertEquals($expectedCategories, $responseData, json_encode($responseData));
    }

    public function testPromotedCategoriesReturnsSameCategoryAsCategoryDetail(): void
    {
        $queryPromotedCategories = '
            query {
                promotedCategories {
                    uuid
                    name
                    children {
                        name
                    }
                    parent {
                        name
                    }
                    products(first: 1) {
                        edges {
                            node {
                                name
                            }
                        }
                    }
                    images {
                        position
                        sizes {
                            url
                        }
                    }
                    seoH1
                    seoTitle
                    seoMetaDescription
                }
            }
        ';

        $graphQlType = 'promotedCategories';
        $responsePromotedCategories = $this->getResponseContentForQuery($queryPromotedCategories);

        $this->assertResponseContainsArrayOfDataForGraphQlType($responsePromotedCategories, $graphQlType);
        $responseDataPromotedCategories = $this->getResponseDataForGraphQlType($responsePromotedCategories, $graphQlType);

        self::assertArrayHasKey(0, $responseDataPromotedCategories, 'Response does not contain expected data');
        self::assertArrayHasKey('uuid', $responseDataPromotedCategories[0], 'Response does not contain expected data');

        $categoryUuid = $responseDataPromotedCategories[0]['uuid'];

        $queryCategoryDetail = '
            query {
                category(uuid: "' . $categoryUuid . '") {
                    uuid
                    name
                    children {
                        name
                    }
                    parent {
                        name
                    }
                    products(first: 1) {
                        edges {
                            node {
                                name
                            }
                        }
                    }
                    images {
                        position
                        sizes {
                            url
                        }
                    }
                    seoH1
                    seoTitle
                    seoMetaDescription
                }
            }
        ';

        $graphQlType = 'category';
        $responseCategoryDetail = $this->getResponseContentForQuery($queryCategoryDetail);

        $this->assertResponseContainsArrayOfDataForGraphQlType($responseCategoryDetail, $graphQlType);
        $responseDataCategoryDetail = $this->getResponseDataForGraphQlType($responseCategoryDetail, $graphQlType);

        self::assertArrayHasKey(0, $responseDataPromotedCategories, 'Response does not contain expected data');
        self::assertEquals($responseDataPromotedCategories[0], $responseDataCategoryDetail);
    }
}
