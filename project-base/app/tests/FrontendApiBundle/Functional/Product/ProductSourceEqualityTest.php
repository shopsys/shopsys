<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\ProductDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductSourceEqualityTest extends GraphQlTestCase
{
    /**
     * @return array<array<int, int>>
     */
    public function getProductsIdsToTest(): array
    {
        return [
            [1], // regular product
            [148], // variant
        ];
    }

    /**
     * @param int $productId
     * @dataProvider getProductsIdsToTest
     */
    public function testProductReturnsTheSameData(int $productId): void
    {
        $productUuid = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productId)->getUuid();

        $productArrayQuery = 'query {
            product(uuid: "' . $productUuid . '") {
                ' . $this->getAllProductFields() . '
                ... on Variant {
                    mainVariant {
                        ' . $this->getAllProductFields() . '
                    } 
                }
            }
        }';
        $productArrayResponse = $this->getResponseContentForQuery($productArrayQuery);
        $productArrayData = $this->getResponseDataForGraphQlType($productArrayResponse, 'product');

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $productUuid,
            'quantity' => 1,
        ]);

        $productEntityData = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['items'][0]['product'];

        self::assertEquals($productArrayData, $productEntityData);
    }

    /**
     * @return string
     */
    public function getAllProductFields(): string
    {
        return '
            name
            slug
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
            storeAvailabilities {
                store {
                    name
                }
                availabilityInformation
                availabilityStatus
            }
            availableStoresCount
            breadcrumb {
                name
                slug
            }
        ';
    }
}
