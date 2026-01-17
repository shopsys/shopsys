<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use DateTimeInterface;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage\PaymentContentPage;
use Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage\PaymentContentPageFactory;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderSentPageNotAvailableUserError;

class OrderSentPageContentQuery extends AbstractQuery
{
    public function __construct(
        protected readonly OrderApiFacade $orderApiFacade,
        protected readonly OrderContentPageFacade $orderContentPageFacade,
        protected readonly PaymentContentPageFactory $paymentContentPageFactory,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function orderPaymentPageContentQuery(string $orderUuid): PaymentContentPage
    {
        $order = $this->orderApiFacade->getByUuid($orderUuid);

        $this->assertDateTimeIsRecent($order->getOrderPaymentStatusPageValidFrom());

        if ($order->isPaid()) {
            return $this->paymentContentPageFactory->createSuccessful(
                $this->orderContentPageFacade->getPaymentSuccessfulPageContent($order),
            );
        }

        if ($order->hasPaymentInProcess()) {
            return $this->paymentContentPageFactory->createInProcess(
                $this->orderContentPageFacade->getPaymentInProcessPageContent($order),
            );
        }

        return $this->paymentContentPageFactory->createFailed(
            $this->orderContentPageFacade->getPaymentFailedPageContent($order),
        );
    }

    public function orderSentPageContentQuery(string $orderUuid): string
    {
        $order = $this->orderApiFacade->getByUuid($orderUuid);

        $this->assertDateTimeIsRecent($order->getCreatedAt());

        return $this->orderContentPageFacade->getOrderSentPageContent($order);
    }

    public function assertDateTimeIsRecent(?DateTimeInterface $checkDateTime): void
    {
        $fiveMinutesAgo = $this->clock->now()->modify('-5 minutes');

        if ($checkDateTime === null || $checkDateTime < $fiveMinutesAgo) {
            throw new OrderSentPageNotAvailableUserError('You cannot request page content for order older than 5 minutes.');
        }
    }
}
