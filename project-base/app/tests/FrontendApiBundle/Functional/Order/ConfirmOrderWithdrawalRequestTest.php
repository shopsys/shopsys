<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Group;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestData;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestRepository;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class ConfirmOrderWithdrawalRequestTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private WithdrawalRequestFacade $withdrawalRequestFacade;

    /**
     * @inject
     */
    private WithdrawalRequestRepository $withdrawalRequestRepository;

    /**
     * @inject
     */
    private WithdrawalRequestDataFactory $withdrawalRequestDataFactory;

    public function testUnknownConfirmationHashReturnsError(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/ConfirmOrderWithdrawalRequestMutation.graphql',
            ['confirmationHash' => str_repeat('0', 64)],
        );

        $this->assertUserError($response, 'order-withdrawal-confirmation-invalid');
    }

    public function testExpiredConfirmationHashReturnsError(): void
    {
        $expiredHash = str_pad('expired-withdrawal-confirmation-hash', 64, '0');
        $withdrawalRequestData = $this->createWithdrawalRequestData();
        $withdrawalRequestData->confirmed = false;
        $withdrawalRequestData->confirmationHash = $expiredHash;
        $withdrawalRequestData->requestedAt = new DateTimeImmutable('-25 hours');

        $order = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_YESTERDAY, 1, Order::class);
        $this->withdrawalRequestFacade->createOnly($order, $withdrawalRequestData);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/ConfirmOrderWithdrawalRequestMutation.graphql',
            ['confirmationHash' => $expiredHash],
        );

        $this->assertUserError($response, 'order-withdrawal-confirmation-invalid');
    }

    #[Group('multidomain')]
    public function testConfirmationHashOfOrderFromAnotherDomainReturnsError(): void
    {
        $orderOnSecondDomain = $this->getReference(OrderDataFixture::ORDER_PREFIX . 24, Order::class);

        $this->assertNotSame(1, $orderOnSecondDomain->getDomainId(), 'Test setup error: the order should not belong to the first domain');

        $unconfirmedWithdrawalRequest = $this->createUnconfirmedWithdrawalRequest($orderOnSecondDomain);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/ConfirmOrderWithdrawalRequestMutation.graphql',
            ['confirmationHash' => $unconfirmedWithdrawalRequest->getConfirmationHash()],
        );

        $this->assertUserError($response, 'order-withdrawal-confirmation-invalid');
    }

    public function testWithdrawalDeadlinePassedAtConfirmationTimeReturnsError(): void
    {
        $order = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_MONTH_AGO, 1, Order::class);
        $unconfirmedWithdrawalRequest = $this->createUnconfirmedWithdrawalRequest($order);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/ConfirmOrderWithdrawalRequestMutation.graphql',
            ['confirmationHash' => $unconfirmedWithdrawalRequest->getConfirmationHash()],
        );

        $this->assertUserError($response, 'order-withdrawal-deadline-passed');
    }

    public function testSecondRequestWhileValidUnconfirmedOneExistsIsRejected(): void
    {
        $order = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_YESTERDAY, 1, Order::class);
        $existingHash = $this->createUnconfirmedWithdrawalRequest($order)->getConfirmationHash();

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/OrderWithdrawalRequestMutation.graphql',
            $this->createOrderWithdrawalRequestInputData($order),
        );

        $this->assertUserError($response, 'order-withdrawal-already-requested');
        $this->assertTrue(
            $this->withdrawalRequestRepository->existsByConfirmationHash($existingHash),
            'The valid unconfirmed withdrawal request must stay untouched by the rejected submission',
        );
    }

    public function testExpiredUnconfirmedRequestIsReplacedByNewRequest(): void
    {
        $order = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_YESTERDAY, 1, Order::class);

        $expiredHash = str_pad('expired-withdrawal-confirmation-hash-to-replace', 64, '0');
        $expiredWithdrawalRequestData = $this->createWithdrawalRequestData();
        $expiredWithdrawalRequestData->confirmed = false;
        $expiredWithdrawalRequestData->confirmationHash = $expiredHash;
        $expiredWithdrawalRequestData->requestedAt = new DateTimeImmutable('-25 hours');
        $this->withdrawalRequestFacade->createOnly($order, $expiredWithdrawalRequestData);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/OrderWithdrawalRequestMutation.graphql',
            $this->createOrderWithdrawalRequestInputData($order),
        );

        $this->assertTrue($this->getResponseDataForGraphQlType($response, 'OrderWithdrawalRequest'));

        $this->em->clear();
        $this->assertFalse($this->withdrawalRequestRepository->existsByConfirmationHash($expiredHash));

        $replacedWithdrawalRequest = $this->withdrawalRequestFacade->findIncludingUnconfirmedByOrder($order);
        $this->assertNotNull($replacedWithdrawalRequest);
        $this->assertFalse($replacedWithdrawalRequest->isConfirmed());
        $this->assertGreaterThan(new DateTimeImmutable('-1 hour'), $replacedWithdrawalRequest->getRequestedAt());
    }

    private function createUnconfirmedWithdrawalRequest(Order $order): WithdrawalRequest
    {
        return $this->withdrawalRequestFacade->createUnconfirmed(
            $order,
            $this->createWithdrawalRequestData(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function createOrderWithdrawalRequestInputData(Order $order): array
    {
        return [
            'orderUrlHash' => $order->getUrlHash(),
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'email' => 'jane.smith@example.com',
            'note' => 'Product does not match description, I want to return the entire order.',
        ];
    }

    private function createWithdrawalRequestData(): WithdrawalRequestData
    {
        return $this->withdrawalRequestDataFactory->createFromArray([
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'email' => 'jane.smith@example.com',
            'note' => 'Product does not match description, I want to return the entire order.',
        ]);
    }
}
