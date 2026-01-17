<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use DateTimeInterface;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator;

class WithdrawalSettingFacade
{
    public const string VARIABLE_ORDER_DETAIL_URL = '{order_detail_url}';
    public const string VARIABLE_ORDER_NUMBER = '{order_number}';

    public function __construct(
        protected readonly WithdrawalSetting $withdrawalSetting,
        protected readonly OrderUrlGenerator $orderUrlGenerator,
        protected readonly WithdrawalDeadlineCalculation $withdrawalDeadlineCalculation,
    ) {
    }

    public function getWithdrawalInstructions(Order $order): string
    {
        $withdrawalInstructions = $this->withdrawalSetting->getInstructions($order->getDomainId());

        return $this->replaceVariables($order, $withdrawalInstructions);
    }

    public function getWithdrawalDeadline(Order $order): ?DateTimeInterface
    {
        return $this->withdrawalDeadlineCalculation->getWithdrawalDeadline($order);
    }

    protected function replaceVariables(Order $order, string $withdrawalInstructions): string
    {
        $orderDetailUrl = $this->orderUrlGenerator->getOrderDetailUrl($order);

        $variables = [
            self::VARIABLE_ORDER_DETAIL_URL => $orderDetailUrl,
            self::VARIABLE_ORDER_NUMBER => $order->getNumber(),
        ];

        return strtr($withdrawalInstructions, $variables);
    }
}
