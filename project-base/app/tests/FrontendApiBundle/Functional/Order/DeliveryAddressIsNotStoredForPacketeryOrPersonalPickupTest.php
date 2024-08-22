<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class DeliveryAddressIsNotStoredForPacketeryOrPersonalPickupTest extends GraphQlWithLoginTestCase
{
    /**
     * @param string $transportDataFixtureReference
     */
    #[DataProvider('getTransportDataFixtureReferences')]
    public function testDeliveryAddressIsNotDuplicatedForLoggedInClient(
        string $transportDataFixtureReference,
    ): void {
        $deliveryAddressesBeforeOrder = $this->getCustomersDeliveryAddresses();

        $pickupPlaceIdentifier = $this->getPickupPlaceIdentifierForTransportReference($transportDataFixtureReference);

        $this->initializeCart($transportDataFixtureReference, $pickupPlaceIdentifier);

        $orderVariables = [
            'cartUuid' => null,
            'city' => 'Ostrava',
            'companyName' => 'Shopsys',
            'companyNumber' => '12345678',
            'companyTaxNumber' => 'CZ65432123',
            'country' => 'CZ',
            'deliveryAddressUuid' => null,
            'deliveryCity' => 'Litovel',
            'deliveryCompanyName' => null,
            'deliveryCountry' => 'CZ',
            'deliveryFirstName' => 'Jaromír',
            'deliveryLastName' => 'Jágr',
            'deliveryPostcode' => '78401',
            'deliveryStreet' => 'Staroměstské náměstí 93',
            'deliveryTelephone' => '605000123',
            'email' => 'no-reply@shopsys.com',
            'firstName' => 'Jaromír',
            'lastName' => 'Jágr',
            'heurekaAgreement' => true,
            'isDeliveryAddressDifferentFromBilling' => true,
            'newsletterSubscription' => false,
            'note' => '',
            'onCompanyBehalf' => true,
            'postcode' => '70200',
            'street' => 'Hlubinská 10',
            'telephone' => '605000123',
        ];

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/CreateOrderMutation.graphql', $orderVariables);
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'CreateOrder');

        $deliveryAddressesAfterOrder = $this->getCustomersDeliveryAddresses();

        $this->assertSameSize($deliveryAddressesBeforeOrder, $deliveryAddressesAfterOrder);
        $this->assertSame($deliveryAddressesBeforeOrder, $deliveryAddressesAfterOrder);
    }

    /**
     * @param string $transportDataFixtureReference
     * @return string
     */
    private function getPickupPlaceIdentifierForTransportReference(string $transportDataFixtureReference): string
    {
        return match ($transportDataFixtureReference) {
            TransportDataFixture::TRANSPORT_PACKETERY => '26145',
            TransportDataFixture::TRANSPORT_PERSONAL => $this->getReference(StoreDataFixture::STORE_PREFIX . '1', Store::class)->getUuid(),
            default => throw new InvalidArgumentException('Invalid transport data fixture reference'),
        };
    }

    /**
     * @return iterable
     */
    public static function getTransportDataFixtureReferences(): iterable
    {
        yield [TransportDataFixture::TRANSPORT_PACKETERY];

        yield [TransportDataFixture::TRANSPORT_PERSONAL];
    }

    /**
     * @param string $transportDataFixtureReference
     * @param string $pickupPlaceIdentifier
     */
    private function initializeCart(string $transportDataFixtureReference, string $pickupPlaceIdentifier): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $transport = $this->getReference($transportDataFixtureReference, Transport::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'AddToCart');

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'transportUuid' => $transport->getUuid(),
            'pickupPlaceIdentifier' => $pickupPlaceIdentifier,
        ]);
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'ChangeTransportInCart');

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangePaymentInCartMutation.graphql', [
            'paymentUuid' => $transport->getPayments()[0]->getUuid(),
        ]);
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'ChangePaymentInCart');
    }

    /**
     * @return array<int, array{uuid: string}>
     */
    private function getCustomersDeliveryAddresses(): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/CurrentCustomerUserQuery.graphql');

        return $this->getResponseDataForGraphQlType(
            $response,
            'currentCustomerUser',
        )['deliveryAddresses'];
    }
}
