<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage;

/**
 * In-memory (per-request) cache for payment page content resolved during UpdatePaymentStatus.
 * Prevents duplicate DB/API calls when the same order's payment content is needed
 * by both UpdatePaymentStatus and the order detail query within a single GraphQL request.
 */
class OrderPaymentPageContentCache
{
    /**
     * @var array<string, \Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage\PaymentContentPage|null>
     */
    protected array $paymentContentPagesByOrderUuid = [];

    public function setForOrderUuid(string $orderUuid, ?PaymentContentPage $paymentContentPage): void
    {
        $this->paymentContentPagesByOrderUuid[$orderUuid] = $paymentContentPage;
    }

    public function hasForOrderUuid(string $orderUuid): bool
    {
        return array_key_exists($orderUuid, $this->paymentContentPagesByOrderUuid);
    }

    public function getForOrderUuid(string $orderUuid): ?PaymentContentPage
    {
        return $this->paymentContentPagesByOrderUuid[$orderUuid] ?? null;
    }
}
