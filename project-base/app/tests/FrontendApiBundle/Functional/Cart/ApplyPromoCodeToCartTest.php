<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use App\FrontendApi\Model\Component\Constraints\PromoCode;
use App\Model\Cart\Cart;
use App\Model\Cart\CartFacade;
use App\Model\Customer\User\CustomerUserIdentifierFactory;
use App\Model\Order\PromoCode\PromoCodeDataFactory;
use App\Model\Order\PromoCode\PromoCodeFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ApplyPromoCodeToCartTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\Product\ProductDataFactory
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    /**
     * @var \App\Model\Product\ProductFacade
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeFacade
     * @inject
     */
    private PromoCodeFacade $promoCodeFacade;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeDataFactory
     * @inject
     */
    private PromoCodeDataFactory $promoCodeDataFactory;

    /**
     * @var \App\Model\Cart\CartFacade
     * @inject
     */
    private CartFacade $cartFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider
     * @inject
     */
    private FrontendCustomerUserProvider $frontendCustomerUserProvider;

    /**
     * @var \App\Model\Customer\User\CustomerUserIdentifierFactory
     * @inject
     */
    private CustomerUserIdentifierFactory $customerUserIdentifierFactory;

    public function testApplyPromoCode(): void
    {
        /** @var \App\Model\Order\PromoCode\PromoCode $promoCode */
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1);

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                cartUuid: "' . CartDataFixture::CART_UUID . '"
                promoCode: "' . $promoCode->getCode() . '"
            }) {
                uuid
                promoCode
            }
        }';

        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);
        $data = $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');

        self::assertEquals(CartDataFixture::CART_UUID, $data['uuid']);
        self::assertEquals($promoCode->getCode(), $data['promoCode']);
    }

    public function testApplyPromoCodeMultipleTimes(): void
    {
        /** @var \App\Model\Order\PromoCode\PromoCode $promoCode */
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1);

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                cartUuid: "' . CartDataFixture::CART_UUID . '"
                promoCode: "' . $promoCode->getCode() . '"
            }) {
                uuid
                promoCode
            }
        }';

        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);
        $data = $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');

        self::assertEquals(CartDataFixture::CART_UUID, $data['uuid']);
        self::assertEquals($promoCode->getCode(), $data['promoCode']);

        // apply promo code again
        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertEquals(PromoCode::ALREADY_APPLIED_PROMO_CODE_ERROR, $violations['input.promoCode'][0]['code']);

        // test promo code is applied only once in DB
        $cart = $this->cartFacade->findCartByCartIdentifier(CartDataFixture::CART_UUID);
        self::assertCount(1, $cart->getAllAppliedPromoCodes());
    }

    public function testApplyPromoCodeWithInvalidCart(): void
    {
        $invalidCartUuid = '24c11eca-a3f8-45cb-b843-861bcde847c6';

        /** @var \App\Model\Order\PromoCode\PromoCode $promoCode */
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1);

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                cartUuid: "' . $invalidCartUuid . '"
                promoCode: "' . $promoCode->getCode() . '"
            }) {
                uuid
                promoCode
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
        /** @var \App\Model\Order\PromoCode\PromoCode $promoCode */
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1);

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                promoCode: "' . $promoCode->getCode() . '"
            }) {
                uuid
                promoCode
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
        /** @var \App\Model\Order\PromoCode\PromoCode $promoCode */
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1);
        /** @var \App\Model\Product\Product $productInCart */
        $productInCart = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

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
                promoCode
            }
        }';

        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);
        $data = $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');
        self::assertEquals($promoCode->getCode(), $data['promoCode']);

        // product has to be re-fetched due to identity map clearing to prevent "A new entity was found through the relationship" error
        /** @var \App\Model\Product\Product $productInCart */
        $productInCart = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

        $this->hideProduct($productInCart);

        $getCartQuery = '{
            cart(cartInput: {cartUuid: "' . $cartUuid . '"}) {
                promoCode
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

        self::assertNull($data['promoCode']);

        self::assertNotEmpty($itemModifications['noLongerListableCartItems']);
        self::assertEquals($productInCart->getUuid(), $itemModifications['noLongerListableCartItems'][0]['product']['uuid']);

        self::assertNotEmpty($promoCodeModifications['noLongerApplicablePromoCode']);
        self::assertEquals($promoCode->getCode(), $promoCodeModifications['noLongerApplicablePromoCode'][0]);
    }

    public function testModificationAfterPromoCodeEdited(): void
    {
        /** @var \App\Model\Order\PromoCode\PromoCode $validPromoCode */
        $validPromoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1);

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                cartUuid: "' . CartDataFixture::CART_UUID . '"
                promoCode: "' . $validPromoCode->getCode() . '"
            }) {
                uuid
                promoCode
            }
        }';

        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);
        $data = $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');

        self::assertEquals($validPromoCode->getCode(), $data['promoCode']);

        $promoCodeData = $this->promoCodeDataFactory->createFromPromoCode($validPromoCode);
        $promoCodeData->remainingUses = 0;
        $this->promoCodeFacade->edit($validPromoCode->getId(), $promoCodeData);

        $getCartQuery = '{
            cart(cartInput: {cartUuid: "' . CartDataFixture::CART_UUID . '"}) {
                promoCode
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

        self::assertNull($data['promoCode']);

        self::assertNotEmpty($promoCodeModifications['noLongerApplicablePromoCode']);
        self::assertEquals($validPromoCode->getCode(), $promoCodeModifications['noLongerApplicablePromoCode'][0]);
    }

    public function testPromoCodeIsStillAppliedAfterMergingCart(): void
    {
        $testCartUuid = CartDataFixture::CART_UUID;

        /** @var \App\Model\Order\PromoCode\PromoCode $promoCode */
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1);

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                cartUuid: "' . $testCartUuid . '"
                promoCode: "' . $promoCode->getCode() . '"
            }) {
                uuid
                promoCode
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
     * @dataProvider getInvalidPromoCodesDataProvider
     * @param string|null $promoCodeReferenceName
     * @param string $expectedError
     */
    public function testApplyInvalidPromoCode(?string $promoCodeReferenceName, string $expectedError): void
    {
        $promoCodeCode = 'non-existing-promo-code';
        if ($promoCodeReferenceName !== null) {
            /** @var \App\Model\Order\PromoCode\PromoCode $promoCode */
            $promoCode = $this->getReferenceForDomain($promoCodeReferenceName, 1);
            $promoCodeCode = $promoCode->getCode();
        }

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                cartUuid: "' . CartDataFixture::CART_UUID . '"
                promoCode: "' . $promoCodeCode . '"
            }) {
                uuid
                promoCode
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
    public function getInvalidPromoCodesDataProvider(): iterable
    {
        yield [null, PromoCode::INVALID_ERROR];
        yield [PromoCodeDataFixture::PROMO_CODE_FOR_PRODUCT_ID_2, PromoCode::NO_RELATION_TO_PRODUCTS_IN_CART_ERROR];
        yield [PromoCodeDataFixture::NOT_YET_VALID_PROMO_CODE, PromoCode::NOT_YET_VALID_ERROR];
        yield [PromoCodeDataFixture::NO_LONGER_VALID_PROMO_CODE, PromoCode::NO_LONGER_VALID_ERROR];
        yield [PromoCodeDataFixture::PROMO_CODE_FOR_REGISTERED_ONLY, PromoCode::FOR_REGISTERED_CUSTOMER_USERS_ONLY_ERROR];
        yield [PromoCodeDataFixture::PROMO_CODE_FOR_VIP_PRICING_GROUP, PromoCode::NOT_AVAILABLE_FOR_CUSTOMER_USER_PRICING_GROUP_ERROR];
    }

    /**
     * @param \App\Model\Product\Product $product
     */
    private function hideProduct(Product $product): void
    {
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->sellingDenied = true;

        $this->productFacade->edit($product->getId(), $productData);
        $this->dispatchFakeKernelResponseEventToTriggerImmediateRecalculations();
    }

    /**
     * @param string $email
     * @return \App\Model\Cart\Cart|null
     */
    private function findCartOfCustomerByEmail(string $email): ?Cart
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->frontendCustomerUserProvider->loadUserByUsername($email);

        $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($customerUser);

        return $this->cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
    }
}
