<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use App\Model\Cart\CartFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrontendApiBundle\Component\Constraints\PromoCode as PromoCodeConstraint;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ApplyPromoCodeToCartTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private PromoCodeFacade $promoCodeFacade;

    /**
     * @inject
     */
    private PromoCodeDataFactory $promoCodeDataFactory;

    /**
     * @inject
     */
    private CartFacade $cartFacade;

    /**
     * @inject
     */
    private FrontendCustomerUserProvider $frontendCustomerUserProvider;

    /**
     * @inject
     */
    private CustomerUserIdentifierFactory $customerUserIdentifierFactory;

    public function testApplyPercentagePromoCode(): void
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                cartUuid: "' . CartDataFixture::CART_UUID . '"
                promoCode: "' . $promoCode->getCode() . '"
            }) {
                uuid
                promoCodes {
                    code
                    type
                    discount {
                        priceWithVat
                        priceWithoutVat
                        vatAmount
                    }
                }
            }
        }';

        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);
        $data = $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');

        self::assertEquals(CartDataFixture::CART_UUID, $data['uuid']);
        self::assertEquals(
            [
                'code' => $promoCode->getCode(),
                'type' => 'percent',
                'discount' => [
                    'priceWithVat' => $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('250.000000'),
                    'priceWithoutVat' => $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('250.000000'),
                    'vatAmount' => '0.000000',
                ],
            ],
            $data['promoCodes'][0],
        );
    }

    public function testApplyNominalPromoCode(): void
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE_NOMINAL, 1, PromoCode::class);

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                cartUuid: "' . CartDataFixture::CART_UUID . '"
                promoCode: "' . $promoCode->getCode() . '"
            }) {
                uuid
                promoCodes {
                    code
                    type
                    discount {
                        priceWithVat
                        priceWithoutVat
                        vatAmount
                    }
                }
            }
        }';

        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);
        $data = $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');

        self::assertEquals(CartDataFixture::CART_UUID, $data['uuid']);
        self::assertEquals(
            [
                'code' => $promoCode->getCode(),
                'type' => 'nominal',
                'discount' => [
                    'priceWithVat' => $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('-2500.000000'),
                    'priceWithoutVat' => $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('-2066.000000'),
                    'vatAmount' => $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('-434.000000'),
                ],
            ],
            $data['promoCodes'][0],
        );
    }

    public function testApplyPromoCodeMultipleTimes(): void
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                cartUuid: "' . CartDataFixture::CART_UUID . '"
                promoCode: "' . $promoCode->getCode() . '"
            }) {
                uuid
                promoCodes {
                    code
                }
            }
        }';

        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);
        $data = $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');

        self::assertEquals(CartDataFixture::CART_UUID, $data['uuid']);
        self::assertEquals($promoCode->getCode(), $data['promoCodes'][0]['code']);

        // apply promo code again
        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertEquals(PromoCodeConstraint::ALREADY_APPLIED_PROMO_CODE_ERROR, $violations['input.promoCode'][0]['code']);

        // test promo code is applied only once in DB
        $cart = $this->cartFacade->findCartByCartIdentifier(CartDataFixture::CART_UUID);
        self::assertCount(1, $cart->getAllAppliedPromoCodes());
    }

    public function testApplyPromoCodeWithInvalidCart(): void
    {
        $invalidCartUuid = '24c11eca-a3f8-45cb-b843-861bcde847c6';

        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                cartUuid: "' . $invalidCartUuid . '"
                promoCode: "' . $promoCode->getCode() . '"
            }) {
                uuid
                promoCodes {
                    code
                }
            }
        }';

        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        self::assertEquals('validation', $errors[0]['message']);
        self::assertEquals(t('The promo code is not applicable to any of the products in your cart. Check it, please.', [], 'validators', $this->getFirstDomainLocale()), $errors[0]['extensions']['validation']['input.promoCode'][0]['message']);
    }

    public function testApplyPromoCodeWithoutCart(): void
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                promoCode: "' . $promoCode->getCode() . '"
            }) {
                uuid
                promoCodes {
                    code
                }
            }
        }';

        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        self::assertEquals('validation', $errors[0]['message']);
        self::assertEquals(t('The promo code is not applicable to any of the products in your cart. Check it, please.', [], 'validators', $this->getFirstDomainLocale()), $errors[0]['extensions']['validation']['input.promoCode'][0]['message']);
    }

    public function testModificationAfterProductIsRemoved(): void
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);
        $productInCart = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $productInCart->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['uuid'];

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                cartUuid: "' . $cartUuid . '"
                promoCode: "' . $promoCode->getCode() . '"
            }) {
                uuid
                promoCodes {
                    code
                }
            }
        }';

        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);
        $data = $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');
        self::assertEquals($promoCode->getCode(), $data['promoCodes'][0]['code']);

        // product has to be re-fetched due to identity map clearing to prevent "A new entity was found through the relationship" error
        $productInCart = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);

        $this->hideProduct($productInCart);

        $getCartQuery = '{
            cart(cartInput: {cartUuid: "' . $cartUuid . '"}) {
                promoCodes {
                    code
                }
                modifications {
                    itemModifications {
                        noLongerListableCartItems {
                            product {
                                uuid
                            }
                        }
                    }
                    promoCodeModifications {
                        noLongerApplicablePromoCode
                    }
                }
            }
        }';

        $response = $this->getResponseContentForQuery($getCartQuery);
        $data = $this->getResponseDataForGraphQlType($response, 'cart');
        $itemModifications = $data['modifications']['itemModifications'];
        $promoCodeModifications = $data['modifications']['promoCodeModifications'];

        self::assertCount(0, $data['promoCodes']);

        self::assertNotEmpty($itemModifications['noLongerListableCartItems']);
        self::assertEquals($productInCart->getUuid(), $itemModifications['noLongerListableCartItems'][0]['product']['uuid']);

        self::assertNotEmpty($promoCodeModifications['noLongerApplicablePromoCode']);
        self::assertEquals($promoCode->getCode(), $promoCodeModifications['noLongerApplicablePromoCode'][0]);
    }

    public function testModificationAfterPromoCodeEdited(): void
    {
        $validPromoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                cartUuid: "' . CartDataFixture::CART_UUID . '"
                promoCode: "' . $validPromoCode->getCode() . '"
            }) {
                uuid
                promoCodes {
                    code
                }
            }
        }';

        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);
        $data = $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');

        self::assertEquals($validPromoCode->getCode(), $data['promoCodes'][0]['code']);

        $promoCodeData = $this->promoCodeDataFactory->createFromPromoCode($validPromoCode);
        $promoCodeData->remainingUses = 0;
        $this->promoCodeFacade->edit($validPromoCode->getId(), $promoCodeData);

        $getCartQuery = '{
            cart(cartInput: {cartUuid: "' . CartDataFixture::CART_UUID . '"}) {
                promoCodes {
                    code
                }
                modifications {
                    itemModifications {
                        noLongerListableCartItems {
                            product {
                                uuid
                            }
                        }
                    }
                    promoCodeModifications {
                        noLongerApplicablePromoCode
                    }
                }
            }
        }';

        $response = $this->getResponseContentForQuery($getCartQuery);
        $data = $this->getResponseDataForGraphQlType($response, 'cart');

        $promoCodeModifications = $data['modifications']['promoCodeModifications'];

        self::assertCount(0, $data['promoCodes']);

        self::assertNotEmpty($promoCodeModifications['noLongerApplicablePromoCode']);
        self::assertEquals($validPromoCode->getCode(), $promoCodeModifications['noLongerApplicablePromoCode'][0]);
    }

    public function testPromoCodeIsStillAppliedAfterMergingCart(): void
    {
        $testCartUuid = CartDataFixture::CART_UUID;

        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                cartUuid: "' . $testCartUuid . '"
                promoCode: "' . $promoCode->getCode() . '"
            }) {
                uuid
                promoCodes {
                    code
                }
            }
        }';
        $this->getResponseContentForQuery($applyPromoCodeMutation);

        $loginMutationWithCartUuid = 'mutation {
                Login(input: {
                    email: "no-reply@shopsys.com"
                    password: "user123"
                    cartUuid: "' . $testCartUuid . '"
                }) {
                    tokens {
                        accessToken
                        refreshToken
                    }
                }
            }
        ';

        $this->getResponseContentForQuery($loginMutationWithCartUuid);

        $cart = $this->findCartOfCustomerByEmail('no-reply@shopsys.com');

        self::assertNotNull($cart);
        self::assertTrue($cart->isPromoCodeApplied($promoCode->getCode()), 'Promo code have to be applied after merging cart after login');
    }

    /**
     * @param string|null $promoCodeReferenceName
     * @param string $expectedError
     */
    #[DataProvider('getInvalidPromoCodesDataProvider')]
    public function testApplyInvalidPromoCode(?string $promoCodeReferenceName, string $expectedError): void
    {
        $promoCodeCode = 'non-existing-promo-code';

        if ($promoCodeReferenceName !== null) {
            $promoCode = $this->getReferenceForDomain($promoCodeReferenceName, 1, PromoCode::class);
            $promoCodeCode = $promoCode->getCode();
        }

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                cartUuid: "' . CartDataFixture::CART_UUID . '"
                promoCode: "' . $promoCodeCode . '"
            }) {
                uuid
                promoCodes {
                    code
                }
            }
        }';

        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);

        self::assertArrayHasKey('errors', $response);

        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertArrayHasKey('input.promoCode', $violations);
        self::assertEquals($expectedError, $violations['input.promoCode'][0]['code']);
    }

    /**
     * @return iterable
     */
    public static function getInvalidPromoCodesDataProvider(): iterable
    {
        yield [null, PromoCodeConstraint::INVALID_ERROR];

        yield [PromoCodeDataFixture::PROMO_CODE_FOR_PRODUCT_ID_2, PromoCodeConstraint::NO_RELATION_TO_PRODUCTS_IN_CART_ERROR];

        yield [PromoCodeDataFixture::NOT_YET_VALID_PROMO_CODE, PromoCodeConstraint::NOT_YET_VALID_ERROR];

        yield [PromoCodeDataFixture::NO_LONGER_VALID_PROMO_CODE, PromoCodeConstraint::NO_LONGER_VALID_ERROR];

        yield [PromoCodeDataFixture::PROMO_CODE_FOR_REGISTERED_ONLY, PromoCodeConstraint::FOR_REGISTERED_CUSTOMER_USERS_ONLY_ERROR];

        yield [PromoCodeDataFixture::PROMO_CODE_FOR_VIP_PRICING_GROUP, PromoCodeConstraint::NOT_AVAILABLE_FOR_CUSTOMER_USER_PRICING_GROUP_ERROR];
    }

    /**
     * @param \App\Model\Product\Product $product
     */
    private function hideProduct(Product $product): void
    {
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->sellingDenied = true;

        $this->productFacade->edit($product->getId(), $productData);
        $this->handleDispatchedRecalculationMessages();
    }

    /**
     * @param string $email
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart|null
     */
    private function findCartOfCustomerByEmail(string $email): ?Cart
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->frontendCustomerUserProvider->loadUserByUsername($email);

        $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($customerUser);

        return $this->cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
    }
}
