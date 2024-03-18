<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\FrontendApi\Model\Component\Constraints\PaymentInOrder;
use App\Model\Payment\Payment;
use App\Model\Payment\PaymentDataFactory;
use App\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentInOrderValidationTest extends GraphQlTestCase
{
    use OrderTestTrait;

    /**
     * @inject
     */
    private PaymentFacade $paymentFacade;

    /**
     * @inject
     */
    private PaymentDataFactory $paymentDataFactory;

    public function testPaymentNotSet(): void
    {
        $this->addPplTransportToCart(CartDataFixture::CART_UUID);
        $mutation = $this->getCreateOrderMutationFromDemoCart();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(PaymentInOrder::PAYMENT_NOT_SET_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testHiddenPaymentUnavailable(): void
    {
        $this->addCardPaymentToDemoCart();
        $this->addPplTransportToCart(CartDataFixture::CART_UUID);
        $this->hideCardPayment();
        $mutation = $this->getCreateOrderMutationFromDemoCart();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(PaymentInOrder::UNAVAILABLE_PAYMENT_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testDisabledPaymentUnavailable(): void
    {
        $this->addCardPaymentToDemoCart();
        $this->addPplTransportToCart(CartDataFixture::CART_UUID);
        $this->disableCardPaymentOnFirstDomain();
        $mutation = $this->getCreateOrderMutationFromDemoCart();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(PaymentInOrder::UNAVAILABLE_PAYMENT_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testPaymentPriceChanged(): void
    {
        $this->addCardPaymentToDemoCart();
        $this->addPplTransportToCart(CartDataFixture::CART_UUID);
        $this->changeCardPaymentPriceOnFirstDomain();
        $mutation = $this->getCreateOrderMutationFromDemoCart();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('CreateOrder', $response['data']);
        $this->assertArrayHasKey('cart', $response['data']['CreateOrder']);
        $this->assertArrayHasKey('modifications', $response['data']['CreateOrder']['cart']);
        $this->assertArrayHasKey('paymentModifications', $response['data']['CreateOrder']['cart']['modifications']);
        $this->assertArrayHasKey('paymentPriceChanged', $response['data']['CreateOrder']['cart']['modifications']['paymentModifications']);
        $this->assertTrue($response['data']['CreateOrder']['cart']['modifications']['paymentModifications']['paymentPriceChanged']);
    }

    private function hideCardPayment(): void
    {
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD, Payment::class);
        $paymentData = $this->paymentDataFactory->createFromPayment($payment);
        $paymentData->hidden = true;
        $this->paymentFacade->edit($payment, $paymentData);
    }

    private function disableCardPaymentOnFirstDomain(): void
    {
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD, Payment::class);
        $paymentData = $this->paymentDataFactory->createFromPayment($payment);
        $paymentData->enabled[1] = false;
        $this->paymentFacade->edit($payment, $paymentData);
    }

    private function changeCardPaymentPriceOnFirstDomain(): void
    {
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD, Payment::class);
        $paymentData = $this->paymentDataFactory->createFromPayment($payment);
        $paymentData->pricesIndexedByDomainId[1] = $payment->getPrice(1)->getPrice()->add(Money::create(10));
        $this->paymentFacade->edit($payment, $paymentData);
    }
}
