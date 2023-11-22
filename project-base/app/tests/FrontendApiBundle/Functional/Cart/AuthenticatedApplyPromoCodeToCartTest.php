<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\FrontendApi\Model\Component\Constraints\PromoCode;
use App\Model\Cart\Cart;
use App\Model\Cart\CartFacade;
use App\Model\Customer\User\CustomerUserIdentifierFactory;
use App\Model\Order\PromoCode\PromoCodeDataFactory;
use App\Model\Order\PromoCode\PromoCodeFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class AuthenticatedApplyPromoCodeToCartTest extends GraphQlWithLoginTestCase
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
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @inject
     */
    private CustomerUserIdentifierFactory $customerUserIdentifierFactory;

    /**
     * @dataProvider UsablePromoCodeDataProvider
     * @param string $promoCodeCode
     */
    public function testApplyPromoCode(string $promoCodeCode): void
    {
        /** @var \App\Model\Order\PromoCode\PromoCode $promoCode */
        $promoCode = $this->getReferenceForDomain($promoCodeCode, 1);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatHigh */
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $this->domain->getId());

        $cartResponseData = $this->createUserCartWithHelloKittyProduct();

        // 10% discount promo code
        $discountedTotalPriceWithoutVat = $cartResponseData['cart']['totalPrice']['priceWithoutVat'] * 0.9;
        $expectedPrice = $this->getSerializedPriceConvertedToDomainDefaultCurrency((string)$discountedTotalPriceWithoutVat, $vatHigh);

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                promoCode: "' . $promoCode->getCode() . '"
            }) {
                uuid
                promoCode
                totalPrice {
                    priceWithVat
                    priceWithoutVat
                    vatAmount
                }
            }
        }';

        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);
        $data = $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');

        self::assertNull($data['uuid']);
        self::assertEquals($promoCode->getCode(), $data['promoCode']);

        $actualPrice = $this->getSerializedPriceConvertedToDomainDefaultCurrency(
            $data['totalPrice']['priceWithoutVat'],
            $vatHigh,
        );

        self::assertEquals(
            $expectedPrice,
            $actualPrice,
        );
    }

    /**
     * @return iterable
     */
    public function usablePromoCodeDataProvider(): iterable
    {
        yield [PromoCodeDataFixture::VALID_PROMO_CODE];

        yield [PromoCodeDataFixture::PROMO_CODE_FOR_REGISTERED_ONLY];
    }

    public function testApplyPromoCodeMultipleTimes(): void
    {
        /** @var \App\Model\Order\PromoCode\PromoCode $promoCode */
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1);

        $this->createUserCartWithHelloKittyProduct();

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                promoCode: "' . $promoCode->getCode() . '"
            }) {
                uuid
                promoCode
            }
        }';

        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);
        $data = $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');

        self::assertNull($data['uuid']);
        self::assertEquals($promoCode->getCode(), $data['promoCode']);

        // apply promo code again
        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertEquals(PromoCode::ALREADY_APPLIED_PROMO_CODE_ERROR, $violations['input.promoCode'][0]['code']);

        // test promo code is applied only once in DB
        $cart = $this->findCartOfCurrentCustomer();
        self::assertCount(1, $cart->getAllAppliedPromoCodes());
    }

    public function testApplyPromoCodeBeforeCartIsCreatedForUser(): void
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

        $this->createUserCartWithHelloKittyProduct();

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                promoCode: "' . $promoCode->getCode() . '"
            }) {
                uuid
                promoCode
            }
        }';

        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);
        $data = $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');
        self::assertEquals($promoCode->getCode(), $data['promoCode']);

        /** @var \App\Model\Product\Product $productInCart */
        $productInCart = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $this->hideProduct($productInCart);

        $getCartQuery = '{
            cart {
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

        $this->createUserCartWithHelloKittyProduct();

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
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
            cart {
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

        $this->createUserCartWithHelloKittyProduct();

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
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
     * @return mixed[]
     */
    private function createUserCartWithHelloKittyProduct(): array
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 2,
        ]);

        return $this->getResponseDataForGraphQlType($response, 'AddToCart');
    }

    /**
     * @return \App\Model\Cart\Cart|null
     */
    private function findCartOfCurrentCustomer(): ?Cart
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($customerUser);

        return $this->cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
    }
}
