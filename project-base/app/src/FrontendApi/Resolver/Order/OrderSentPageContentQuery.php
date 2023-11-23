<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Order;

use App\FrontendApi\Model\Order\Exception\OrderSentPageNotAvailableUserError;
use App\FrontendApi\Model\Order\OrderApiFacade;
use DateTimeImmutable;
use DateTimeInterface;
use Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

final class OrderSentPageContentQuery extends AbstractQuery
{
    /**
     * @param \App\FrontendApi\Model\Order\OrderApiFacade $orderApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageFacade $orderContentPageFacade
     */
    public function __construct(
        private readonly OrderApiFacade $orderApiFacade,
        private readonly OrderContentPageFacade $orderContentPageFacade,
    ) {
    }

    /**
     * @param string $orderUuid
     * @return string
     */
    public function orderSentPageContentQuery(string $orderUuid): string
    {
        $order = $this->orderApiFacade->getByUuid($orderUuid);

        $this->assertDateTimeIsRecent($order->getCreatedAt());

        return $this->orderContentPageFacade->getOrderSentPageContent($order);
    }

    /**
     * @param string $orderUuid
     * @return string
     */
    public function orderPaymentSuccessfulContentQuery(string $orderUuid): string
    {
        $order = $this->orderApiFacade->getByUuid($orderUuid);

        $this->assertDateTimeIsRecent($order->getOrderPaymentStatusPageValidFrom());

        return $this->orderContentPageFacade->getPaymentSuccessfulPageContent($order);
    }

    /**
     * @param string $orderUuid
     * @return string
     */
    public function orderPaymentFailedContentQuery(string $orderUuid): string
    {
        $order = $this->orderApiFacade->getByUuid($orderUuid);

        $this->assertDateTimeIsRecent($order->getOrderPaymentStatusPageValidFrom());

        return $this->orderContentPageFacade->getPaymentFailedPageContent($order);
    }

    /**
     * @param \DateTimeInterface|null $checkDateTime
     */
    public function assertDateTimeIsRecent(?DateTimeInterface $checkDateTime): void
    {
        $fiveMinutesAgo = new DateTimeImmutable('-5 minutes');

        if ($checkDateTime === null || $checkDateTime < $fiveMinutesAgo) {
            throw new OrderSentPageNotAvailableUserError('You cannot request page content for order older than 5 minutes.');
        }
    }
}
