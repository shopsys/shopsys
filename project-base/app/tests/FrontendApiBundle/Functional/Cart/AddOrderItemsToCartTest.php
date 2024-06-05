<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Cart\CartFacade;
use App\Model\Product\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Tests\FrontendApiBundle\Functional\Order\OrderTestTrait;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class AddOrderItemsToCartTest extends GraphQlWithLoginTestCase
{
    use OrderTestTrait;

    /**
     * @inject
     */
    private CartFacade $cartFacade;

    /**
     * @inject
     */
    private CustomerUserIdentifierFactory $customerUserIdentifierFactory;

    /**
     * @inject
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @param bool $shouldMerge
     * @param array $expectedProducts
     */
    #[DataProvider('getAddOrderItemsToCartDataProvider')]
    public function testOrderItemsAreCorrectlyAddedToCart(bool $shouldMerge, array $expectedProducts): void
    {
        $orderUuid = $this->createMinimalOrderQuery();

        $addedProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '5', Product::class);
        $addedProductQuantity = 6;
        $this->addProductToCustomerCart($addedProduct, $addedProductQuantity);

        $addedProduct2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $addedProductQuantity2 = 1;
        $this->addProductToCustomerCart($addedProduct2, $addedProductQuantity2);

        $cart = $this->findCartOfCurrentCustomer();
        $cartUuid = $cart->getCartIdentifier() !== '' ? $cart->getCartIdentifier() : null;

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/AddOrderItemsToCart.graphql', [
            'orderUuid' => $orderUuid,
            'cartUuid' => $cartUuid,
            'shouldMerge' => $shouldMerge,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'AddOrderItemsToCart');

        foreach ($data['items'] as $key => $item) {
            self::assertEquals(
                $item['product']['name'],
                t($expectedProducts[$key]['name'], [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
            );

            self::assertEquals($item['quantity'], $expectedProducts[$key]['quantity']);
        }
    }

    /**
     * @return array[]
     */
    public static function getAddOrderItemsToCartDataProvider(): array
    {
        return [
            [
                'shouldMerge' => false,
                'expectedProducts' => [
                    [
                        'name' => '22" Sencor SLE 22F46DM4 HELLO KITTY',
                        'quantity' => 1,
                    ],
                ],
            ],
            [
                'shouldMerge' => true,
                'expectedProducts' => [
                    [
                        'name' => '22" Sencor SLE 22F46DM4 HELLO KITTY',
                        'quantity' => 2,
                    ],
                    [
                        'name' => 'Apple iPhone 5S 64GB, gold',
                        'quantity' => 6,
                    ],
                ],
            ],
        ];
    }

    public function testCannotAddMoreOrderItemsToCartThanOnStock(): void
    {
        $orderUuid = $this->createMinimalOrderQuery();

        $addedProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '5', Product::class);
        $addedProductQuantity = 6;
        $this->addProductToCustomerCart($addedProduct, $addedProductQuantity);

        $addedProduct2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $addedProductQuantity2 = 2800;
        $this->addProductToCustomerCart($addedProduct2, $addedProductQuantity2);

        $cart = $this->findCartOfCurrentCustomer();
        $cartUuid = $cart->getCartIdentifier();

        $addOrderItemsToCart = $this->getOrderRepeatMutation($orderUuid, $cartUuid);

        $response = $this->getResponseDataForGraphQlType(
            $this->getResponseContentForQuery($addOrderItemsToCart),
            'AddOrderItemsToCart',
        );

        $expectedProducts = [
            [
                'name' => '22" Sencor SLE 22F46DM4 HELLO KITTY',
                'quantity' => 2700,
            ],
            [
                'name' => 'Apple iPhone 5S 64GB, gold',
                'quantity' => 6,
            ],
        ];

        foreach ($response['items'] as $key => $item) {
            self::assertEquals(
                $item['product']['name'],
                t($expectedProducts[$key]['name'], [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
            );

            self::assertEquals($item['quantity'], $expectedProducts[$key]['quantity']);
        }

        $this->assertEquals(
            [
                'product' => [
                    'name' => t($expectedProducts[0]['name'], [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                ],
                'quantity' => 2700,
            ],
            $response['modifications']['itemModifications']['cartItemsWithChangedQuantity'][0],
        );

        $orderUuid = $this->createMinimalOrderQuery();

        $addOrderItemsToCart = $this->getOrderRepeatMutation($orderUuid);

        $response = $this->getResponseDataForGraphQlType(
            $this->getResponseContentForQuery($addOrderItemsToCart),
            'AddOrderItemsToCart',
        );

        $this->assertEmpty($response['modifications']['itemModifications']['cartItemsWithChangedQuantity']);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart|null
     */
    private function findCartOfCurrentCustomer(): ?Cart
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($customerUser);

        return $this->cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $productQuantity
     */
    private function addProductToCustomerCart(Product $product, int $productQuantity): void
    {
        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => $productQuantity,
        ]);
    }

    /**
     * @return string
     */
    public function createMinimalOrderQuery(): string
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'AddToCart');

        $cartUuid = $data['cart']['uuid'];

        $this->addPplTransportToCart($cartUuid);
        $this->addCardPaymentToCart($cartUuid);

        $response = $this->getResponseContentForQuery($this->getMutation($cartUuid));
        $data = $this->getResponseDataForGraphQlType($response, 'CreateOrder');

        return $data['order']['uuid'];
    }

    /**
     * @param string|null $cartUuid
     * @return string
     */
    private function getMutation(?string $cartUuid): string
    {
        return 'mutation {
                        CreateOrder(
                            input: {
                                ' . ($cartUuid !== null ? 'cartUuid: "' . $cartUuid . '"' : '') . '
                                firstName: "firstName"
                                lastName: "lastName"
                                email: "user@example.com"
                                telephone: "+53 123456789"
                                onCompanyBehalf: false
                                street: "123 Fake Street"
                                city: "Springfield"
                                postcode: "12345"
                                country: "CZ"
                                isDeliveryAddressDifferentFromBilling: false
                            }
                        ) {
                            order {
                                uuid
                            }
                        }
                    }';
    }

    /**
     * @param string $orderUuid
     * @param string $cartUuid
     * @return string
     */
    private function getOrderRepeatMutation(string $orderUuid, string $cartUuid = ''): string
    {
        return 'mutation {
                AddOrderItemsToCart(input: {
                    orderUuid: "' . $orderUuid . '"
                    ' . ($cartUuid !== '' ? 'cartUuid: "' . $cartUuid . '"' : '') . '
                    shouldMerge: true
                }) {
                    items {
                        product {
                            name
                        }
                        quantity
                    }
                    modifications {
                        itemModifications {
                            cartItemsWithChangedQuantity {
                                product {
                                    name
                                }
                                quantity
                            }
                        }
                    }
                }
            }
        ';
    }
}
