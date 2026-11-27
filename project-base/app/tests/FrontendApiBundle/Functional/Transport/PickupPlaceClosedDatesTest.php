<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Transport;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Transport\Transport;
use DateTimeImmutable;
use DateTimeZone;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Tests\FrontendApiBundle\Functional\Order\OrderTestTrait;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class PickupPlaceClosedDatesTest extends GraphQlTestCase
{
    use OrderTestTrait;

    private const string PACKETERY_PICKUP_PLACE_IDENTIFIER = '26145';

    /**
     * @inject
     */
    private DisplayTimeZoneProviderInterface $displayTimeZoneProvider;

    /**
     * @inject
     */
    private OrderApiFacade $orderApiFacade;

    public function testCartExpectedDeliveryDateIsPostponedBeyondTheClosedDatesOfThePickupPlace(): void
    {
        $packeteryTransport = $this->getReference(TransportDataFixture::TRANSPORT_PACKETERY, Transport::class);

        $this->addPacketeryTransportToDemoCart($packeteryTransport, []);
        $deliveryDate = $this->getExpectedDeliveryDateOfDemoCart($packeteryTransport);

        $this->addPacketeryTransportToDemoCart($packeteryTransport, [
            $this->formatDate($deliveryDate),
            $this->formatDate($this->getFollowingDay($deliveryDate)),
        ]);

        $this->assertEquals(
            $this->getFollowingDay($this->getFollowingDay($deliveryDate)),
            $this->getExpectedDeliveryDateOfDemoCart($packeteryTransport),
        );
    }

    public function testClosedDatesAreForgottenWhenThePickupPlaceIsSelectedWithoutThem(): void
    {
        $packeteryTransport = $this->getReference(TransportDataFixture::TRANSPORT_PACKETERY, Transport::class);

        $this->addPacketeryTransportToDemoCart($packeteryTransport, []);
        $deliveryDate = $this->getExpectedDeliveryDateOfDemoCart($packeteryTransport);

        $this->addPacketeryTransportToDemoCart($packeteryTransport, [$this->formatDate($deliveryDate)]);
        $this->addPacketeryTransportToDemoCart($packeteryTransport, []);

        $this->assertEquals($deliveryDate, $this->getExpectedDeliveryDateOfDemoCart($packeteryTransport));
    }

    public function testOrderExpectedDeliveryDateIsPostponedBeyondTheClosedDatesOfThePickupPlace(): void
    {
        $packeteryTransport = $this->getReference(TransportDataFixture::TRANSPORT_PACKETERY, Transport::class);

        $this->addPacketeryTransportToDemoCart($packeteryTransport, []);
        $deliveryDate = $this->getExpectedDeliveryDateOfDemoCart($packeteryTransport);

        $this->addPacketeryTransportToDemoCart($packeteryTransport, [$this->formatDate($deliveryDate)]);
        $this->addPaymentToCart(CartDataFixture::CART_UUID, $packeteryTransport->getPayments()[0]);

        $response = $this->getCreateOrderMutationResponseFromCart();
        $orderUuid = $this->getResponseDataForGraphQlType($response, 'CreateOrder')['order']['uuid'];

        $this->assertEquals(
            $this->getFollowingDay($deliveryDate),
            $this->orderApiFacade->getByUuid($orderUuid)->getExpectedDeliveryDate(),
        );
    }

    public function testClosedDateInAnInvalidFormatIsRejected(): void
    {
        $packeteryTransport = $this->getReference(TransportDataFixture::TRANSPORT_PACKETERY, Transport::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'transportUuid' => $packeteryTransport->getUuid(),
            'pickupPlaceIdentifier' => self::PACKETERY_PICKUP_PLACE_IDENTIFIER,
            'pickupPlaceClosedDates' => ['tomorrow'],
        ]);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $this->assertArrayHasKey('input.pickupPlaceClosedDates[0]', $this->getErrorsExtensionValidationFromResponse($response));
    }

    /**
     * @param string[] $pickupPlaceClosedDates
     */
    private function addPacketeryTransportToDemoCart(
        Transport $packeteryTransport,
        array $pickupPlaceClosedDates,
    ): void {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'transportUuid' => $packeteryTransport->getUuid(),
            'pickupPlaceIdentifier' => self::PACKETERY_PICKUP_PLACE_IDENTIFIER,
            'pickupPlaceClosedDates' => $pickupPlaceClosedDates,
        ]);

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'ChangeTransportInCart');
    }

    private function getExpectedDeliveryDateOfDemoCart(Transport $transport): DateTimeImmutable
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/TransportQuery.graphql', [
            'uuid' => $transport->getUuid(),
            'cartUuid' => CartDataFixture::CART_UUID,
        ]);

        return new DateTimeImmutable($this->getResponseDataForGraphQlType($response, 'transport')['expectedDeliveryDate']);
    }

    /**
     * The demo Packeta transport delivers on any day, so only the closed dates postpone the delivery date
     */
    private function getFollowingDay(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date
            ->setTimezone($this->getDisplayTimeZone())
            ->modify('+1 day')
            ->setTimezone(new DateTimeZone('UTC'));
    }

    private function formatDate(DateTimeImmutable $date): string
    {
        return $date->setTimezone($this->getDisplayTimeZone())->format('Y-m-d');
    }

    private function getDisplayTimeZone(): DateTimeZone
    {
        return $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($this->domain->getId());
    }
}
