<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CartCurrencyTest extends GraphQlTestCase
{
    private const string ADD_TO_CART_MUTATION = '
        mutation ($productUuid: Uuid!) {
            AddToCart(input: { productUuid: $productUuid, quantity: 1 }) {
                cart {
                    uuid
                    totalItemsPrice {
                        priceWithVat
                        currencyCode
                    }
                }
            }
        }
    ';

    private const string CART_QUERY = '
        query ($cartUuid: Uuid) {
            cart(cartInput: { cartUuid: $cartUuid }) {
                totalItemsPrice {
                    priceWithVat
                    currencyCode
                }
            }
        }
    ';

    public function testCartPricesFollowTheCurrencyHeaderStatelessly(): void
    {
        $this->setCurrencyHeader('CZK');

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $response = $this->getResponseContentForQuery(self::ADD_TO_CART_MUTATION, [
            'productUuid' => $product->getUuid(),
        ]);
        $cartData = $response['data']['AddToCart']['cart'];

        self::assertSame('CZK', $cartData['totalItemsPrice']['currencyCode']);

        $czkTotalPriceWithVat = $cartData['totalItemsPrice']['priceWithVat'];

        $this->setCurrencyHeader(null);
        $cartResponse = $this->getResponseContentForQuery(self::CART_QUERY, ['cartUuid' => $cartData['uuid']]);
        $cartQueryData = $this->getResponseDataForGraphQlType($cartResponse, 'cart');

        $defaultCurrencyCode = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getDefaultCurrencyCode();

        self::assertSame($defaultCurrencyCode, $cartQueryData['totalItemsPrice']['currencyCode']);
        self::assertNotSame($czkTotalPriceWithVat, $cartQueryData['totalItemsPrice']['priceWithVat']);
    }

    private function setCurrencyHeader(?string $currencyCode): void
    {
        $clientOptions = ['CONTENT_TYPE' => 'application/graphql'];

        if ($currencyCode !== null) {
            $clientOptions['HTTP_X_CURRENCY_CODE'] = $currencyCode;
        }

        $this->configureCurrentClient(null, null, $clientOptions);
    }
}
