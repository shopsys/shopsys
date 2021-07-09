<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PromotedProductsTest extends GraphQlTestCase
{
    public function testPromotedProductsWithName(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $query = '
            query {
                promotedProducts {
                    name
                }
            }
        ';

        $productsExpected = [
            ['name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Genius repro SP-M120 black', [], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Canon MG3550', [], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Philips 32PFL4308', [], 'dataFixtures', $firstDomainLocale)],
        ];

        $graphQlType = 'promotedProducts';
        $response = $this->getResponseContentForQuery($query);

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        self::assertEquals($productsExpected, $responseData, json_encode($responseData));
    }

    public function testPromotedProductsReturnsSameProductAsProductDetail(): void
    {
        $queryPromotedProducts = '
            query {
                promotedProducts {
                    uuid
                    name
                    shortDescription
                    seoH1
                    seoTitle
                    seoMetaDescription
                    link
                    unit {
                        name
                    }
                    availability {
                        name
                        status
                    }
                    stockQuantity
                    categories {
                        name
                    }
                    flags {
                        name
                        rgbColor
                    }
                    price {
                        priceWithVat
                        priceWithoutVat
                        vatAmount
                    },
                    brand {
                        name
                    }
                    accessories {
                        name
                    }
                    isSellingDenied
                    description
                    orderingPriority
                    parameters {
                        name
                        group
                        unit {
                            name
                        }
                        values {
                            text
                        }
                    }
                    isUsingStock
                    namePrefix
                    nameSuffix
                    fullName
                    catalogNumber
                    partNumber
                    ean
                    usps
                    isInSale
                    hasPreorder
                    hasSaleExclusion
                    files {
                      anchorText
                      url
                    }
                }
            }
        ';

        $graphQlType = 'promotedProducts';
        $responsePromotedProducts = $this->getResponseContentForQuery($queryPromotedProducts);

        $this->assertResponseContainsArrayOfDataForGraphQlType($responsePromotedProducts, $graphQlType);
        $responseDataPromotedProducts = $this->getResponseDataForGraphQlType($responsePromotedProducts, $graphQlType);

        self::assertArrayHasKey(0, $responseDataPromotedProducts, 'Response does not contain expected data');
        self::assertArrayHasKey('uuid', $responseDataPromotedProducts[0], 'Response does not contain expected data');

        $productUuid = $responseDataPromotedProducts[0]['uuid'];

        $queryProductDetail = '
            query {
                product(uuid: "' . $productUuid . '") {
                    uuid
                    name
                    shortDescription
                    seoH1
                    seoTitle
                    seoMetaDescription
                    link
                    unit {
                        name
                    }
                    availability {
                        name
                        status
                    }
                    stockQuantity
                    categories {
                        name
                    }
                    flags {
                        name
                        rgbColor
                    }
                    price {
                        priceWithVat
                        priceWithoutVat
                        vatAmount
                    },
                    brand {
                        name
                    }
                    accessories {
                        name
                    }
                    isSellingDenied
                    description
                    orderingPriority
                    parameters {
                        name
                        group
                        unit {
                            name
                        }
                        values {
                            text
                        }
                    }
                    isUsingStock
                    namePrefix
                    nameSuffix
                    fullName
                    catalogNumber
                    partNumber
                    ean
                    usps
                    isInSale
                    hasPreorder
                    hasSaleExclusion
                    files {
                      anchorText
                      url
                    }
                }
            }
        ';

        $graphQlType = 'product';
        $responseProductDetail = $this->getResponseContentForQuery($queryProductDetail);

        $this->assertResponseContainsArrayOfDataForGraphQlType($responseProductDetail, $graphQlType);
        $responseDataProductDetail = $this->getResponseDataForGraphQlType($responseProductDetail, $graphQlType);

        self::assertArrayHasKey(0, $responseDataPromotedProducts, 'Response does not contain expected data');
        self::assertEquals($responseDataPromotedProducts[0], $responseDataProductDetail);
    }
}
