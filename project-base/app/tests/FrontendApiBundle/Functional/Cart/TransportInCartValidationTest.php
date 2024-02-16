<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\FrontendApi\Model\Component\Constraints\TransportInCart;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportDataFactory;
use App\Model\Transport\TransportFacade;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class TransportInCartValidationTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private TransportDataFactory $transportDataFactory;

    /**
     * @inject
     */
    private TransportFacade $transportFacade;

    public function testUnavailableTransport(): void
    {
        $response = $this->addNonExistingTransportToDemoCart();

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInCart::UNAVAILABLE_TRANSPORT_ERROR, $validationErrors['input.transportUuid'][0]['code']);
    }

    public function testHiddenTransport(): void
    {
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $this->hideTransport($transport);
        $response = $this->addTransportToDemoCart($transport->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInCart::UNAVAILABLE_TRANSPORT_ERROR, $validationErrors['input.transportUuid'][0]['code']);
    }

    public function testDeletedTransport(): void
    {
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $this->transportFacade->deleteById($transport->getId());
        $response = $this->addTransportToDemoCart($transport->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInCart::UNAVAILABLE_TRANSPORT_ERROR, $validationErrors['input.transportUuid'][0]['code']);
    }

    public function testDisabledTransport(): void
    {
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $this->disableTransportOnFirstDomain($transport);
        $response = $this->addTransportToDemoCart($transport->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInCart::UNAVAILABLE_TRANSPORT_ERROR, $validationErrors['input.transportUuid'][0]['code']);
    }

    public function testUnavailablePickupPlace(): void
    {
        $response = $this->addTransportWithNonExistingPickupPlaceToDemoCart();

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInCart::UNAVAILABLE_PICKUP_PLACE_ERROR, $validationErrors['input.pickupPlaceIdentifier'][0]['code']);
    }

    public function testTransportWeightLimitExceeded(): void
    {
        $response = $this->addTransportWithExceededWeightLimitToDemoCart();

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInCart::WEIGHT_LIMIT_EXCEEDED_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testRequiredPickupPlaceIdentifier(): void
    {
        /** @var \App\Model\Transport\Transport $personalPickupTransport */
        $personalPickupTransport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $response = $this->addTransportToDemoCart($personalPickupTransport->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInCart::MISSING_PICKUP_PLACE_IDENTIFIER_ERROR, $validationErrors['input.pickupPlaceIdentifier'][0]['code']);
    }

    public function testInvalidTransportPaymentCombination(): void
    {
        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY_DOMAIN . Domain::FIRST_DOMAIN_ID);
        $this->addPaymentToDemoCart($payment->getUuid());
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_DRONE);
        $response = $this->addTransportToDemoCart($transport->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInCart::INVALID_TRANSPORT_PAYMENT_COMBINATION_ERROR, $validationErrors['input'][0]['code']);
    }

    /**
     * @return array
     */
    private function addNonExistingTransportToDemoCart(): array
    {
        return $this->addTransportToDemoCart(Uuid::uuid4()->toString());
    }

    /**
     * @param string $transportUuid
     * @param string|null $pickupPlaceIdentifier
     * @return array
     */
    private function addTransportToDemoCart(string $transportUuid, ?string $pickupPlaceIdentifier = null): array
    {
        $pickupPlaceIdentifierLine = '';

        if ($pickupPlaceIdentifier !== null) {
            $pickupPlaceIdentifierLine = 'pickupPlaceIdentifier: "' . $pickupPlaceIdentifier . '"';
        }
        $changeTransportInCartMutation = '
            mutation {
                ChangeTransportInCart(input:{
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                    transportUuid: "' . $transportUuid . '"
                    ' . $pickupPlaceIdentifierLine . '
                }) {
                    uuid
                }
            }
        ';

        return $this->getResponseContentForQuery($changeTransportInCartMutation);
    }

    /**
     * @return array
     */
    private function addTransportWithNonExistingPickupPlaceToDemoCart(): array
    {
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);

        return $this->addTransportToDemoCart($transport->getUuid(), Uuid::uuid4()->toString());
    }

    /**
     * @return array
     */
    private function addTransportWithExceededWeightLimitToDemoCart(): array
    {
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST);

        return $this->addTransportToDemoCart($transport->getUuid());
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     */
    private function hideTransport(Transport $transport): void
    {
        $transportData = $this->transportDataFactory->createFromTransport($transport);
        $transportData->hidden = true;
        $this->transportFacade->edit($transport, $transportData);
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     */
    private function disableTransportOnFirstDomain(Transport $transport): void
    {
        $transportData = $this->transportDataFactory->createFromTransport($transport);
        $transportData->enabled[1] = false;
        $this->transportFacade->edit($transport, $transportData);
    }

    /**
     * @param string $paymentUuid
     */
    private function addPaymentToDemoCart(string $paymentUuid): void
    {
        $changeTransportInCartMutation = '
            mutation {
                ChangePaymentInCart(input:{
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                    paymentUuid: "' . $paymentUuid . '"
                }) {
                    uuid
                }
            }
        ';

        $this->getResponseContentForQuery($changeTransportInCartMutation);
    }
}
