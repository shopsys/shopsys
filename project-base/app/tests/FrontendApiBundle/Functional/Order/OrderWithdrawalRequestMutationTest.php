<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use DateTime;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class OrderWithdrawalRequestMutationTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private OrderFacade $orderFacade;

    public function testSuccessfulWithdrawalRequest(): void
    {
        $validOrder = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_YESTERDAY, 1, Order::class);

        $inputData = [
            'orderUrlHash' => $validOrder->getUrlHash(),
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'email' => 'jane.smith@example.com',
            'telephone' => '+420777888999',
            'note' => 'Product does not match description, I want to return the entire order.',
        ];

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/OrderWithdrawalRequestMutation.graphql',
            $inputData,
        );

        $data = $this->getResponseDataForGraphQlType($response, 'OrderWithdrawalRequest');
        $this->assertTrue($data);

        $this->em->clear();
        $updatedOrder = $this->orderFacade->getById($validOrder->getId());

        $this->assertSame($inputData['firstName'], $updatedOrder->getWithdrawalFirstName());
        $this->assertSame($inputData['lastName'], $updatedOrder->getWithdrawalLastName());
        $this->assertSame($inputData['email'], $updatedOrder->getWithdrawalEmail());
        $this->assertSame($inputData['telephone'], $updatedOrder->getWithdrawalTelephone());
        $this->assertSame($inputData['note'], $updatedOrder->getWithdrawalNote());
        $this->assertInstanceOf(DateTime::class, $updatedOrder->getWithdrawalRequestedAt());
    }

    public function testAnonymousUserCannotRequestWithdrawalForRegisteredUserOrder(): void
    {
        $registeredUserOrder = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $inputData = [
            'orderUrlHash' => $registeredUserOrder->getUrlHash(),
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'telephone' => '+420777888999',
            'note' => 'I want to return this order.',
        ];

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/OrderWithdrawalRequestMutation.graphql',
            $inputData,
        );

        $this->assertAccessDeniedError($response);
    }
}
