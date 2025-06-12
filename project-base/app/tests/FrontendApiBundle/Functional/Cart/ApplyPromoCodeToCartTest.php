<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Cart\CartFacade;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use App\Model\Transport\Transport;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrontendApiBundle\Component\Constraints\PromoCode as PromoCodeConstraint;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;
use Tests\FrontendApiBundle\Test\PromoCodeAssertionTrait;

class ApplyPromoCodeToCartTest extends GraphQlTestCase
{
    use PromoCodeAssertionTrait;

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

        $data = $this->applyPromoCodeToCartAndGetResponseData($promoCode->getCode());

        self::assertEquals(CartDataFixture::CART_UUID, $data['uuid']);
        self::assertPromoCode($promoCode, $data['promoCodes'][0]);
    }

    public function testApplyPromoCodeForFreeTransport(): void
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::PROMO_CODE_FOR_FREE_TRANSPORT_PAYMENT, 1, PromoCode::class);
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $this->domain->getId(), Vat::class);
        $this->addCzechPostToCart();
        $this->addCashOnDeliveryPaymentToCart();

        $data = $this->applyPromoCodeToCartAndGetResponseData($promoCode->getCode());

        self::assertPromoCode($promoCode, $data['promoCodes'][0]);
        self::assertSame($this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero), $data['transport']['price']);
        self::assertSame($this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero), $data['payment']['price']);
        self::assertSame($this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('0'), $data['remainingAmountForFreeTransport']);
    }

    public function testApplyNominalPromoCode(): void
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE_NOMINAL, 1, PromoCode::class);

        $data = $this->applyPromoCodeToCartAndGetResponseData($promoCode->getCode());

        self::assertEquals(CartDataFixture::CART_UUID, $data['uuid']);
        self::assertPromoCode($promoCode, $data['promoCodes'][0]);
    }

    public function testApplyPromoCodeMultipleTimes(): void
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);

        $data = $this->applyPromoCodeToCartAndGetResponseData($promoCode->getCode());

        self::assertEquals(CartDataFixture::CART_UUID, $data['uuid']);
        self::assertPromoCode($promoCode, $data['promoCodes'][0]);

        // apply promo code again
        $response = $this->applyPromoCodeToCart($promoCode->getCode());

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

        $response = $this->applyPromoCodeToCart($promoCode->getCode(), $invalidCartUuid);
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        self::assertEquals('validation', $errors[0]['message']);
        self::assertEquals(t('The promo code is not applicable to any of the products in your cart. Check it, please.', [], Translator::CUSTOMER_VALIDATOR_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()), $errors[0]['extensions']['validation']['input.promoCode'][0]['message']);
    }

    public function testApplyPromoCodeWithoutCart(): void
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ApplyPromoCodeToCart.graphql', [
            'promoCode' => $promoCode->getCode(),
        ]);
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        self::assertEquals('validation', $errors[0]['message']);
        self::assertEquals(t('The promo code is not applicable to any of the products in your cart. Check it, please.', [], Translator::CUSTOMER_VALIDATOR_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()), $errors[0]['extensions']['validation']['input.promoCode'][0]['message']);
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

        $data = $this->applyPromoCodeToCartAndGetResponseData($promoCode->getCode(), $cartUuid);
        self::assertPromoCode($promoCode, $data['promoCodes'][0]);

        // product has to be re-fetched due to identity map clearing to prevent "A new entity was found through the relationship" error
        $productInCart = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);

        $this->hideProduct($productInCart);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetCart.graphql', [
            'cartUuid' => $cartUuid,
        ]);
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

        $data = $this->applyPromoCodeToCartAndGetResponseData($validPromoCode->getCode());

        self::assertPromoCode($validPromoCode, $data['promoCodes'][0]);

        $promoCodeData = $this->promoCodeDataFactory->createFromPromoCode($validPromoCode);
        $promoCodeData->remainingUses = 0;
        $this->promoCodeFacade->edit($validPromoCode->getId(), $promoCodeData);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetCart.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
        ]);
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

        $this->applyPromoCodeToCart($promoCode->getCode());

        $response = $this->getResponseContentForGql(__DIR__ . '/../Login/graphql/LoginMutation.graphql', [
            'email' => 'no-reply@shopsys.com',
            'password' => 'user123',
            'cartUuid' => $testCartUuid,
        ]);
        $this->getResponseDataForGraphQlType($response, 'Login');

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

        $response = $this->applyPromoCodeToCart($promoCodeCode);

        self::assertArrayHasKey('errors', $response);

        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertArrayHasKey('input.promoCode', $violations);
        self::assertEquals($expectedError, $violations['input.promoCode'][0]['code']);
    }

    public function testApplyDisabledPromoCode(): void
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::DISABLED_PROMO_CODE_NOMINAL, 1, PromoCode::class);
        $promoCodeCode = $promoCode->getCode();

        $response = $this->applyPromoCodeToCart($promoCodeCode);

        self::assertArrayHasKey('errors', $response);

        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertArrayHasKey('input.promoCode', $violations);
        self::assertEquals(PromoCodeConstraint::INVALID_ERROR, $violations['input.promoCode'][0]['code']);
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

    private function addCzechPostToCart(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'transportUuid' => $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST, Transport::class)->getUuid(),
        ]);
        $this->getResponseDataForGraphQlType($response, 'ChangeTransportInCart');
    }

    private function addCashOnDeliveryPaymentToCart(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangePaymentInCartMutation.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'paymentUuid' => $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY, Payment::class)->getUuid(),
        ]);
        $this->getResponseDataForGraphQlType($response, 'ChangePaymentInCart');
    }

    /**
     * @param string $promoCode
     * @param string $cartUuid
     * @return array
     */
    private function applyPromoCodeToCartAndGetResponseData(
        string $promoCode,
        string $cartUuid = CartDataFixture::CART_UUID,
    ): array {
        $response = $this->applyPromoCodeToCart($promoCode, $cartUuid);

        return $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');
    }

    /**
     * @param string $promoCode
     * @param string $cartUuid
     * @return array
     */
    private function applyPromoCodeToCart(string $promoCode, string $cartUuid = CartDataFixture::CART_UUID): array
    {
        return $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ApplyPromoCodeToCart.graphql', [
            'cartUuid' => $cartUuid,
            'promoCode' => $promoCode,
        ]);
    }
}
