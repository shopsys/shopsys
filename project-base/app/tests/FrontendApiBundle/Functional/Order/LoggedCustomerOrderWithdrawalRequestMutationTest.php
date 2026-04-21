<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class LoggedCustomerOrderWithdrawalRequestMutationTest extends GraphQlWithLoginTestCase
{
    public function testLoggedCustomerCanRequestWithdrawalForAnonymousOrder(): void
    {
        $anonymousOrder = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_YESTERDAY, 1, Order::class);

        $this->assertNull($anonymousOrder->getCustomerUser(), 'Test setup error: ORDER_DELIVERED_YESTERDAY should be an anonymous order');

        $inputData = [
            'orderUrlHash' => $anonymousOrder->getUrlHash(),
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'email' => 'jane.smith@example.com',
            'telephone' => new PhoneData('CZ', '+420', '777888999'),
            'note' => 'I found this order and want to return it as a logged customer.',
        ];

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/OrderWithdrawalRequestMutation.graphql',
            $inputData,
        );

        $data = $this->getResponseDataForGraphQlType($response, 'OrderWithdrawalRequest');
        $this->assertTrue($data);
    }

    public function testLoggedCustomerCannotRequestWithdrawalForAnotherUserOrder(): void
    {
        $anotherUserOrder = $this->getReferenceForDomain(OrderDataFixture::ORDER_CREATED_BY_VITEK, 1, Order::class);

        $this->assertNotSame(self::DEFAULT_USER_EMAIL, $anotherUserOrder->getCustomerUser()->getEmail(), 'Test setup error: ORDER_CREATED_BY_VITEK should not be created by the currently logged customer');

        $inputData = [
            'orderUrlHash' => $anotherUserOrder->getUrlHash(),
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'email' => 'jane.smith@example.com',
            'telephone' => new PhoneData('CZ', '+420', '777888999'),
            'note' => 'I found this order and want to return it as a logged customer.',
        ];

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/OrderWithdrawalRequestMutation.graphql',
            $inputData,
        );

        $this->assertAccessDeniedError($response);
    }
}
