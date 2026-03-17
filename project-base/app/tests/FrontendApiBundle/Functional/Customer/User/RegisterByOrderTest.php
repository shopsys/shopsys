<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Customer\User\CustomerUserFacade;
use App\Model\Order\Order;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class RegisterByOrderTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private CustomerUserFacade $customerUserFacade;

    public function testRegisterByOrder(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '19', Order::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/mutation/RegistrationByOrderMutation.graphql', [
            'orderUrlHash' => $order->getUrlHash(),
            'password' => 'user123',
        ]);

        $graphQlType = 'RegisterByOrder';
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $this->assertArrayHasKey('tokens', $responseData);
        $this->assertIsString($responseData['tokens']['accessToken']);
        $this->assertIsString($responseData['tokens']['refreshToken']);

        $this->assertCustomerUserIsRegisteredByOrder($order);
    }

    public function testRegisterByOrderIsNotPossibleWithInvalidHash(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/mutation/RegistrationByOrderMutation.graphql', [
            'orderUrlHash' => 'invalid-hash',
            'password' => 'user123',
        ]);

        $this->assertOrderCannotBePairedUserErrorIsReturned($response);
    }

    #[DataProvider('registerByOrderIsNotPossibleDataProvider')]
    public function testRegisterByOrderIsNotPossible(int $orderId): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . $orderId, Order::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/mutation/RegistrationByOrderMutation.graphql', [
            'orderUrlHash' => $order->getUrlHash(),
            'password' => 'user123',
        ]);

        $this->assertOrderCannotBePairedUserErrorIsReturned($response);
    }

    public static function registerByOrderIsNotPossibleDataProvider(): iterable
    {
        yield 'order of registered customer user' => [
            'orderId' => 1,
        ];

        yield 'order older than one hour' => [
            'orderId' => 7,
        ];
    }

    private function assertCustomerUserIsRegisteredByOrder(Order $order): void
    {
        $registeredCustomerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($order->getEmail(), $order->getDomainId());
        $this->assertNotNull($registeredCustomerUser);
        $this->assertSame($order->getCustomerUser()->getId(), $registeredCustomerUser->getId());
        $this->assertSame($order->getCustomer()->getId(), $registeredCustomerUser->getCustomer()->getId());
        $this->assertSame($order->getEmail(), $registeredCustomerUser->getEmail());
        $this->assertSame($registeredCustomerUser->getFirstName(), $order->getFirstName());
        $this->assertSame($registeredCustomerUser->getLastName(), $order->getLastName());
        $this->assertSame($registeredCustomerUser->getTelephone(), $order->getTelephone());

        $registeredCustomerUserBillingAddress = $registeredCustomerUser->getCustomer()->getBillingAddress();
        $this->assertSame($registeredCustomerUserBillingAddress->isCompanyCustomer(), $order->isCompanyCustomer());
        $this->assertSame($registeredCustomerUserBillingAddress->getCompanyName(), $order->getCompanyName());
        $this->assertSame($registeredCustomerUserBillingAddress->getCompanyNumber(), $order->getCompanyNumber());
        $this->assertSame($registeredCustomerUserBillingAddress->getCompanyTaxNumber(), $order->getCompanyTaxNumber());
        $this->assertSame($registeredCustomerUserBillingAddress->getStreet(), $order->getStreet());
        $this->assertSame($registeredCustomerUserBillingAddress->getCity(), $order->getCity());
        $this->assertSame($registeredCustomerUserBillingAddress->getPostcode(), $order->getPostcode());
        $this->assertSame($registeredCustomerUserBillingAddress->getCountry()->getId(), $order->getCountry()->getId());

        $registeredCustomerUserDeliveryAddress = $registeredCustomerUser->getDefaultDeliveryAddress();
        $this->assertSame($registeredCustomerUserDeliveryAddress->getFirstName(), $order->getDeliveryFirstName());
        $this->assertSame($registeredCustomerUserDeliveryAddress->getLastName(), $order->getDeliveryLastName());
        $this->assertSame($registeredCustomerUserDeliveryAddress->getCompanyName(), $order->getDeliveryCompanyName());
        $this->assertSame($registeredCustomerUserDeliveryAddress->getTelephoneData()?->number, $order->getDeliveryTelephoneData()?->number);
        $this->assertSame($registeredCustomerUserDeliveryAddress->getStreet(), $order->getDeliveryStreet());
        $this->assertSame($registeredCustomerUserDeliveryAddress->getCity(), $order->getDeliveryCity());
        $this->assertSame($registeredCustomerUserDeliveryAddress->getPostcode(), $order->getDeliveryPostcode());
        $this->assertSame($registeredCustomerUserDeliveryAddress->getCountry()->getId(), $order->getDeliveryCountry()->getId());
    }

    private function assertOrderCannotBePairedUserErrorIsReturned(array $response): void
    {
        $this->assertUserError($response, 'register-by-order-is-not-possible');
    }
}
