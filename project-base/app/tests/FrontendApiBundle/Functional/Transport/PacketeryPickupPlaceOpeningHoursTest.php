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

final class PacketeryPickupPlaceOpeningHoursTest extends GraphQlTestCase
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

    public function testCartExpectedDeliveryDateIsPostponedToTheFirstDayThePickupPlaceIsOpen(): void
    {
        $packeteryTransport = $this->getReference(TransportDataFixture::TRANSPORT_PACKETERY, Transport::class);

        $this->addPacketeryTransportToDemoCart($packeteryTransport, null);
        $deliveryDate = $this->getExpectedDeliveryDateOfDemoCart($packeteryTransport);

        $this->addPacketeryTransportToDemoCart($packeteryTransport, $this->createOpeningHoursClosedOnDate($deliveryDate));

        $this->assertEquals(
            $this->getFollowingDay($deliveryDate),
            $this->getExpectedDeliveryDateOfDemoCart($packeteryTransport),
        );
    }

    public function testPickupPlaceOpeningHoursAreForgottenWhenThePickupPlaceIsSelectedWithoutThem(): void
    {
        $packeteryTransport = $this->getReference(TransportDataFixture::TRANSPORT_PACKETERY, Transport::class);

        $this->addPacketeryTransportToDemoCart($packeteryTransport, null);
        $deliveryDate = $this->getExpectedDeliveryDateOfDemoCart($packeteryTransport);

        $this->addPacketeryTransportToDemoCart($packeteryTransport, $this->createOpeningHoursClosedOnDate($deliveryDate));
        $this->addPacketeryTransportToDemoCart($packeteryTransport, null);

        $this->assertEquals($deliveryDate, $this->getExpectedDeliveryDateOfDemoCart($packeteryTransport));
    }

    public function testOrderExpectedDeliveryDateIsPostponedToTheFirstDayThePickupPlaceIsOpen(): void
    {
        $packeteryTransport = $this->getReference(TransportDataFixture::TRANSPORT_PACKETERY, Transport::class);

        $this->addPacketeryTransportToDemoCart($packeteryTransport, null);
        $deliveryDate = $this->getExpectedDeliveryDateOfDemoCart($packeteryTransport);

        $this->addPacketeryTransportToDemoCart($packeteryTransport, $this->createOpeningHoursClosedOnDate($deliveryDate));
        $this->addPaymentToCart(CartDataFixture::CART_UUID, $packeteryTransport->getPayments()[0]);

        $response = $this->getCreateOrderMutationResponseFromCart();
        $orderUuid = $this->getResponseDataForGraphQlType($response, 'CreateOrder')['order']['uuid'];

        $this->assertEquals(
            $this->getFollowingDay($deliveryDate),
            $this->orderApiFacade->getByUuid($orderUuid)->getExpectedDeliveryDate(),
        );
    }

    /**
     * @param array<string, mixed>|null $pickupPlaceOpeningHours
     */
    private function addPacketeryTransportToDemoCart(
        Transport $packeteryTransport,
        ?array $pickupPlaceOpeningHours,
    ): void {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'transportUuid' => $packeteryTransport->getUuid(),
            'pickupPlaceIdentifier' => self::PACKETERY_PICKUP_PLACE_IDENTIFIER,
            'pickupPlaceOpeningHours' => $pickupPlaceOpeningHours,
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
     * @return array<string, mixed>
     */
    private function createOpeningHoursClosedOnDate(DateTimeImmutable $date): array
    {
        $openingHoursOfDays = [];

        foreach (range(1, 7) as $dayOfWeek) {
            $openingHoursOfDays[] = [
                'dayOfWeek' => $dayOfWeek,
                'openingHoursRanges' => [['openingTime' => '00:00', 'closingTime' => '23:59']],
            ];
        }

        return [
            'openingHoursOfDays' => $openingHoursOfDays,
            'exceptionDays' => [
                [
                    'from' => $date->setTimezone($this->getDisplayTimeZone())->format('Y-m-d'),
                    'to' => null,
                    'openingHoursRanges' => [],
                ],
            ],
        ];
    }

    private function getFollowingDay(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date
            ->setTimezone($this->getDisplayTimeZone())
            ->modify('+1 day')
            ->setTimezone(new DateTimeZone('UTC'));
    }

    private function getDisplayTimeZone(): DateTimeZone
    {
        return $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($this->domain->getId());
    }
}
