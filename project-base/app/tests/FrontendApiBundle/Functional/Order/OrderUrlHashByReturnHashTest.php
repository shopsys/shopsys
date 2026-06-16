<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use DateTimeImmutable;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Payment\ReturnHash\PaymentReturnHashFacade;
use Shopsys\FrameworkBundle\Model\Payment\ReturnHash\PaymentReturnHashFactory;
use Shopsys\FrameworkBundle\Model\Payment\ReturnHash\PaymentReturnHashRepository;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class OrderUrlHashByReturnHashTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private PaymentReturnHashFacade $paymentReturnHashFacade;

    /**
     * @inject
     */
    private PaymentReturnHashFactory $paymentReturnHashFactory;

    public function testValidReturnHashResolvesOrderUrlHash(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $returnHash = $this->paymentReturnHashFacade->createForOrderAndGetHash($order);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/OrderUrlHashByReturnHashQuery.graphql', [
            'returnHash' => $returnHash,
        ]);
        $this->assertSame($order->getUrlHash(), $response['data']['orderUrlHashByReturnHash']);
    }

    public function testUnknownReturnHashReturnsNull(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/OrderUrlHashByReturnHashQuery.graphql', [
            'returnHash' => 'unknown-return-hash',
        ]);

        $this->assertArrayNotHasKey('errors', $response);
        $this->assertNull($response['data']['orderUrlHashByReturnHash']);
    }

    public function testExpiredReturnHashReturnsNull(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $returnHash = 'expired-payment-return-hash';
        $paymentReturnHash = $this->paymentReturnHashFactory->create($returnHash, $order, new DateTimeImmutable('-1 minute'));
        $this->em->persist($paymentReturnHash);
        $this->em->flush();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/OrderUrlHashByReturnHashQuery.graphql', [
            'returnHash' => $returnHash,
        ]);

        $this->assertArrayNotHasKey('errors', $response);
        $this->assertNull($response['data']['orderUrlHashByReturnHash']);
    }

    public function testExpiredReturnHashesAreDeleted(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $validReturnHash = $this->paymentReturnHashFacade->createForOrderAndGetHash($order);

        $expiredReturnHash = 'expired-payment-return-hash-at-boundary';
        $now = new DateTimeImmutable('+1 minute');
        $paymentReturnHash = $this->paymentReturnHashFactory->create($expiredReturnHash, $order, $now);
        $this->em->persist($paymentReturnHash);
        $this->em->flush();

        $clock = $this->createStub(ClockInterface::class);
        $clock->method('now')->willReturn($now);
        $paymentReturnHashRepository = new PaymentReturnHashRepository($this->em, $clock);

        $this->assertTrue($paymentReturnHashRepository->existsByHash($validReturnHash));
        $this->assertTrue($paymentReturnHashRepository->existsByHash($expiredReturnHash));

        $paymentReturnHashRepository->deleteAllExpired();

        $this->assertTrue($paymentReturnHashRepository->existsByHash($validReturnHash));
        $this->assertFalse($paymentReturnHashRepository->existsByHash($expiredReturnHash));
    }
}
