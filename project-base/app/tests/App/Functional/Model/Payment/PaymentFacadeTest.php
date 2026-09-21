<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Payment;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Payment\Payment;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Payment\Payment as BasePayment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentRepository;
use Shopsys\FrameworkBundle\Model\Payment\PaymentVisibilityCalculation;
use Tests\App\Test\TransactionFunctionalTestCase;

final class PaymentFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private PaymentFacade $paymentFacade;

    /**
     * @inject
     */
    private PaymentRepository $paymentRepository;

    /**
     * @inject
     */
    private PaymentVisibilityCalculation $paymentVisibilityCalculation;

    /**
     * @inject
     */
    private PaymentDataFactory $paymentDataFactory;

    public function testVisiblePaymentsByTransportAreTheVisibleAssignedPayments(): void
    {
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST, Transport::class);
        $expectedPayments = $this->paymentVisibilityCalculation->filterVisible(
            $this->paymentRepository->getAllByTransport($transport),
            $this->domain->getId(),
        );

        $visiblePayments = $this->paymentFacade->getVisibleOnCurrentDomainByTransport($transport);

        $this->assertNotEmpty($visiblePayments);
        $this->assertSame($this->getIds($expectedPayments), $this->getIds($visiblePayments));
    }

    public function testPaymentIsNoLongerVisibleAndEnabledAfterItIsHidden(): void
    {
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD, Payment::class);
        $paymentData = $this->paymentDataFactory->createFromPayment($payment);
        $this->assertTrue($this->paymentFacade->isPaymentVisibleAndEnabledOnCurrentDomain($payment));

        $paymentData->hidden = true;
        $this->paymentFacade->edit($payment, $paymentData);
        $this->resetRequestScopedCache();

        $this->assertFalse($this->paymentFacade->isPaymentVisibleAndEnabledOnCurrentDomain($payment));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $payments
     * @return int[]
     */
    private function getIds(array $payments): array
    {
        return array_map(static fn (BasePayment $payment): int => $payment->getId(), $payments);
    }
}
