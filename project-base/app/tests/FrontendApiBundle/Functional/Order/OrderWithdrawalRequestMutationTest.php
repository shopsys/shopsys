<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use DateTimeImmutable;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class OrderWithdrawalRequestMutationTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private WithdrawalRequestFacade $withdrawalRequestFacade;

    public function testSuccessfulWithdrawalRequest(): void
    {
        $validOrder = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_YESTERDAY, 1, Order::class);

        $inputData = [
            'orderUrlHash' => $validOrder->getUrlHash(),
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'email' => 'jane.smith@example.com',
            'telephone' => new PhoneData('CZ', '+420', '777888999'),
            'note' => 'Product does not match description, I want to return the entire order.',
        ];

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/OrderWithdrawalRequestMutation.graphql',
            $inputData,
        );

        $data = $this->getResponseDataForGraphQlType($response, 'OrderWithdrawalRequest');
        $this->assertTrue($data);

        $this->em->clear();
        $withdrawalRequest = $this->withdrawalRequestFacade->findByOrder($validOrder);

        $this->assertNotNull($withdrawalRequest);
        $this->assertSame($inputData['firstName'], $withdrawalRequest->getFirstName());
        $this->assertSame($inputData['lastName'], $withdrawalRequest->getLastName());
        $this->assertSame($inputData['email'], $withdrawalRequest->getEmail());
        $this->assertSame($inputData['telephone']->toPhoneNumber(), $withdrawalRequest->getTelephone());
        $this->assertSame($inputData['note'], $withdrawalRequest->getNote());
        $this->assertInstanceOf(DateTimeImmutable::class, $withdrawalRequest->getRequestedAt());
    }

    public function testAnonymousUserCannotRequestWithdrawalForRegisteredUserOrder(): void
    {
        $registeredUserOrder = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $inputData = [
            'orderUrlHash' => $registeredUserOrder->getUrlHash(),
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'telephone' => new PhoneData('CZ', '+420', '777888999'),
            'note' => 'I want to return this order.',
        ];

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/OrderWithdrawalRequestMutation.graphql',
            $inputData,
        );

        $this->assertAccessDeniedError($response);
    }
}
