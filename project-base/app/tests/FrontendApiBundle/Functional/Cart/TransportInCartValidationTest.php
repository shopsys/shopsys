<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportDataFactory;
use App\Model\Transport\TransportFacade;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Shopsys\FrontendApiBundle\Component\Constraints\TransportInCart;
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
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class);
        $this->hideTransport($transport);
        $response = $this->addTransportToDemoCart($transport->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInCart::UNAVAILABLE_TRANSPORT_ERROR, $validationErrors['input.transportUuid'][0]['code']);
    }

    public function testDeletedTransport(): void
    {
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class);
        $this->transportFacade->deleteById($transport->getId());
        $response = $this->addTransportToDemoCart($transport->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInCart::UNAVAILABLE_TRANSPORT_ERROR, $validationErrors['input.transportUuid'][0]['code']);
    }

    public function testDisabledTransport(): void
    {
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class);
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
        $this->addProductsToDemoCartSoCzechPostWeightLimitIsExceeded();
        $response = $this->addCzechPostTransportToDemoCart();

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInCart::WEIGHT_LIMIT_EXCEEDED_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testRequiredPickupPlaceIdentifier(): void
    {
        $personalPickupTransport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class);
        $response = $this->addTransportToDemoCart($personalPickupTransport->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInCart::MISSING_PICKUP_PLACE_IDENTIFIER_ERROR, $validationErrors['input.pickupPlaceIdentifier'][0]['code']);
    }

    public function testInvalidTransportPaymentCombination(): void
    {
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY_CARD, Payment::class);
        $this->addPaymentToDemoCart($payment->getUuid());
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_DRONE, Transport::class);
        $response = $this->addTransportToDemoCart($transport->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInCart::INVALID_TRANSPORT_PAYMENT_COMBINATION_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testExcludedTransport(): void
    {
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_DRONE, Transport::class);
        $response = $this->addTransportToDemoCart($transport->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInCart::UNAVAILABLE_TRANSPORT_ERROR, $validationErrors['input'][0]['code']);
    }

    /**
     * @return iterable<string, array{pickupPlaceOpeningHours: array<string, mixed>, violationPath: string}>
     */
    public static function getInvalidPickupPlaceOpeningHoursData(): iterable
    {
        $openingHoursRanges = [['openingTime' => '08:00', 'closingTime' => '18:00']];

        yield 'closing time in an unknown format' => [
            'pickupPlaceOpeningHours' => [
                'openingHoursOfDays' => [
                    ['dayOfWeek' => 1, 'openingHoursRanges' => [['openingTime' => '08:00', 'closingTime' => '6 pm']]],
                ],
                'exceptionDays' => [],
            ],
            'violationPath' => 'input.pickupPlaceOpeningHours.openingHoursOfDays[0].openingHoursRanges[0].closingTime',
        ];

        yield 'day of the week out of range' => [
            'pickupPlaceOpeningHours' => [
                'openingHoursOfDays' => [['dayOfWeek' => 8, 'openingHoursRanges' => $openingHoursRanges]],
                'exceptionDays' => [],
            ],
            'violationPath' => 'input.pickupPlaceOpeningHours.openingHoursOfDays[0].dayOfWeek',
        ];

        yield 'more days than a week has' => [
            'pickupPlaceOpeningHours' => [
                'openingHoursOfDays' => array_fill(0, 8, ['dayOfWeek' => 1, 'openingHoursRanges' => $openingHoursRanges]),
                'exceptionDays' => [],
            ],
            'violationPath' => 'input.pickupPlaceOpeningHours.openingHoursOfDays',
        ];

        yield 'more exception days than the limit' => [
            'pickupPlaceOpeningHours' => [
                'openingHoursOfDays' => [],
                'exceptionDays' => array_fill(0, 101, ['from' => '2026-12-24', 'to' => null, 'openingHoursRanges' => []]),
            ],
            'violationPath' => 'input.pickupPlaceOpeningHours.exceptionDays',
        ];

        yield 'more opening hours ranges of a day than the limit' => [
            'pickupPlaceOpeningHours' => [
                'openingHoursOfDays' => [['dayOfWeek' => 1, 'openingHoursRanges' => array_fill(0, 11, $openingHoursRanges[0])]],
                'exceptionDays' => [],
            ],
            'violationPath' => 'input.pickupPlaceOpeningHours.openingHoursOfDays[0].openingHoursRanges',
        ];

        yield 'exception day that is not a date' => [
            'pickupPlaceOpeningHours' => [
                'openingHoursOfDays' => [],
                'exceptionDays' => [['from' => 'tomorrow', 'to' => null, 'openingHoursRanges' => []]],
            ],
            'violationPath' => 'input.pickupPlaceOpeningHours.exceptionDays[0].from',
        ];
    }

    /**
     * @param array<string, mixed> $pickupPlaceOpeningHours
     */
    #[DataProvider('getInvalidPickupPlaceOpeningHoursData')]
    public function testInvalidPickupPlaceOpeningHours(array $pickupPlaceOpeningHours, string $violationPath): void
    {
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PACKETERY, Transport::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'transportUuid' => $transport->getUuid(),
            'pickupPlaceIdentifier' => '26145',
            'pickupPlaceOpeningHours' => $pickupPlaceOpeningHours,
        ]);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $this->assertArrayHasKey($violationPath, $this->getErrorsExtensionValidationFromResponse($response));
    }

    private function addNonExistingTransportToDemoCart(): array
    {
        return $this->addTransportToDemoCart(Uuid::uuid4()->toString());
    }

    private function addTransportToDemoCart(string $transportUuid, ?string $pickupPlaceIdentifier = null): array
    {
        return $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'transportUuid' => $transportUuid,
            'pickupPlaceIdentifier' => $pickupPlaceIdentifier,
        ]);
    }

    private function addTransportWithNonExistingPickupPlaceToDemoCart(): array
    {
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class);

        return $this->addTransportToDemoCart($transport->getUuid(), Uuid::uuid4()->toString());
    }

    private function addCzechPostTransportToDemoCart(): array
    {
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST, Transport::class);

        return $this->addTransportToDemoCart($transport->getUuid());
    }

    private function hideTransport(Transport $transport): void
    {
        $transportData = $this->transportDataFactory->createFromTransport($transport);
        $transportData->hidden = true;
        $this->transportFacade->edit($transport, $transportData);
    }

    private function disableTransportOnFirstDomain(Transport $transport): void
    {
        $transportData = $this->transportDataFactory->createFromTransport($transport);
        $transportData->enabled[1] = false;
        $this->transportFacade->edit($transport, $transportData);
    }

    private function addPaymentToDemoCart(string $paymentUuid): void
    {
        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangePaymentInCartMutation.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'paymentUuid' => $paymentUuid,
        ]);
    }

    private function addProductsToDemoCartSoCzechPostWeightLimitIsExceeded(): void
    {
        $helloKittyProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'productUuid' => $helloKittyProduct->getUuid(),
            'quantity' => 10,
        ]);
    }
}
