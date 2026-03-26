<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use DateTimeInterface;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage\OrderPaymentPageContentCache;
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
        protected readonly OrderPaymentPageContentCache $orderPaymentPageContentCache,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function orderPaymentPageContentQuery(string $orderUuid): PaymentContentPage
    {
        $order = $this->orderApiFacade->getByUuid($orderUuid);

        return $this->getOrderPaymentPageContentByOrder($order);
    }

    /**
     * Cache-aware variant used by UpdatePaymentStatus to include payment page content
     * in the same response without a redundant DB lookup.
     * Returns null instead of throwing when the status page time window has expired.
     */
    public function orderPaymentPageContentByOrderUuidQuery(string $orderUuid): ?PaymentContentPage
    {
        if ($this->orderPaymentPageContentCache->hasForOrderUuid($orderUuid)) {
            return $this->orderPaymentPageContentCache->getForOrderUuid($orderUuid);
        }

        try {
            $order = $this->orderApiFacade->getByUuid($orderUuid);

            return $this->getOrderPaymentPageContentByOrder($order);
        } catch (OrderSentPageNotAvailableUserError) {
            return null;
        }
    }

    public function getOrderPaymentPageContentByOrder(Order $order): PaymentContentPage
    {
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

    /**
     * Enforces a 5-minute window for accessing payment/order status page content.
     * After expiry, the user must use the order detail page instead.
     */
    public function assertDateTimeIsRecent(?DateTimeInterface $checkDateTime): void
    {
        $fiveMinutesAgo = $this->clock->now()->modify('-5 minutes');

        if ($checkDateTime === null || $checkDateTime < $fiveMinutesAgo) {
            throw new OrderSentPageNotAvailableUserError('You cannot request page content for order older than 5 minutes.');
        }
    }
}
