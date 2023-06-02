<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\FrontendApi\Model\Component\Constraints\PaymentInCart;
use App\Model\Payment\Payment;
use App\Model\Payment\PaymentDataFactory;
use App\Model\Payment\PaymentFacade;
use Ramsey\Uuid\Uuid;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentInCartValidationTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\Payment\PaymentFacade
     * @inject
     */
    private PaymentFacade $paymentFacade;

    /**
     * @var \App\Model\Payment\PaymentDataFactory
     * @inject
     */
    private PaymentDataFactory $paymentDataFactory;

    public function testUnavailablePayment(): void
    {
        $response = $this->addNonExistingPaymentToDemoCart();

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(PaymentInCart::UNAVAILABLE_PAYMENT_ERROR, $validationErrors['input.paymentUuid'][0]['code']);
    }

    public function testHiddenPayment(): void
    {
        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $this->hidePayment($payment);
        $response = $this->addPaymentToDemoCart($payment->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(PaymentInCart::UNAVAILABLE_PAYMENT_ERROR, $validationErrors['input.paymentUuid'][0]['code']);
    }

    public function testDeletedPayment(): void
    {
        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $this->paymentFacade->deleteById($payment->getId());
        $response = $this->addPaymentToDemoCart($payment->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(PaymentInCart::UNAVAILABLE_PAYMENT_ERROR, $validationErrors['input.paymentUuid'][0]['code']);
    }

    public function testDisabledPayment(): void
    {
        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $this->disablePaymentOnFirstDomain($payment);
        $response = $this->addPaymentToDemoCart($payment->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(PaymentInCart::UNAVAILABLE_PAYMENT_ERROR, $validationErrors['input.paymentUuid'][0]['code']);
    }

    public function testInvalidPaymentTransportCombination(): void
    {
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_DRONE);
        $this->addTransportToDemoCart($transport->getUuid());
        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY);
        $response = $this->addPaymentToDemoCart($payment->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(PaymentInCart::INVALID_PAYMENT_TRANSPORT_COMBINATION_ERROR, $validationErrors['input'][0]['code']);
    }

    /**
     * @param string $paymentUuid
     * @return array
     */
    private function addPaymentToDemoCart(string $paymentUuid): array
    {
        $changePaymentInCartMutation = '
            mutation {
                ChangePaymentInCart(input:{
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                    paymentUuid: "' . $paymentUuid . '"
                }) {
                    uuid
                }
            }
        ';

        return $this->getResponseContentForQuery($changePaymentInCartMutation);
    }

    /**
     * @return array
     */
    private function addNonExistingPaymentToDemoCart(): array
    {
        return $this->addPaymentToDemoCart(Uuid::uuid4()->toString());
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     */
    private function hidePayment(Payment $payment): void
    {
        $paymentData = $this->paymentDataFactory->createFromPayment($payment);
        $paymentData->hidden = true;
        $this->paymentFacade->edit($payment, $paymentData);
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     */
    private function disablePaymentOnFirstDomain(Payment $payment): void
    {
        $paymentData = $this->paymentDataFactory->createFromPayment($payment);
        $paymentData->enabled[1] = false;
        $this->paymentFacade->edit($payment, $paymentData);
    }

    /**
     * @param string $transportUuid
     */
    private function addTransportToDemoCart(string $transportUuid): void
    {
        $changeTransportInCartMutation = '
            mutation {
                ChangeTransportInCart(input:{
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                    transportUuid: "' . $transportUuid . '"
                }) {
                    uuid
                }
            }
        ';

        $this->getResponseContentForQuery($changeTransportInCartMutation);
    }
}
