<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Order\ConfirmationPageContent\ConfirmationPageContent;

class UpdatePaymentStatusResult
{
    public function __construct(
        protected readonly Order $order,
        protected readonly ConfirmationPageContent $confirmationPageContent,
    ) {
    }

    public function isPaid(): bool
    {
        return $this->order->isPaid();
    }

    public function getLastPaymentStatus(): ?string
    {
        return $this->order->getLastExternalPaymentStatus();
    }

    public function hasPaymentInProcess(): bool
    {
        return $this->order->hasPaymentInProcess();
    }

    public function getLastExternalPaymentUrl(): ?string
    {
        return $this->order->getLastExternalPaymentUrl();
    }

    public function getPaymentTransactionsCount(): int
    {
        return $this->order->getPaymentTransactionsCount();
    }

    public function getOrderNumber(): string
    {
        return $this->order->getNumber();
    }

    public function getPaymentName(): string
    {
        return $this->order->getPaymentItem()->getName();
    }

    public function getConfirmationPageContent(): ConfirmationPageContent
    {
        return $this->confirmationPageContent;
    }
}
