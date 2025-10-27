<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use DateTimeInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalException;

class WithdrawalFacade
{
    public const string VARIABLE_ORDER_DETAIL_URL = '{order_detail_url}';
    public const string VARIABLE_ORDER_NUMBER = '{order_number}';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator $orderUrlGenerator
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalChecker $withdrawalChecker
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalDeadlineCalculation $withdrawalDeadlineCalculation
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly Setting $setting,
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderUrlGenerator $orderUrlGenerator,
        protected readonly WithdrawalChecker $withdrawalChecker,
        protected readonly WithdrawalDeadlineCalculation $withdrawalDeadlineCalculation,
    ) {
    }

    /**
     * @param string $orderUrlHash
     * @return string
     */
    public function getWithdrawalInstructions(string $orderUrlHash): string
    {
        $order = $this->orderFacade->getByUrlHashAndDomain($orderUrlHash, $this->domain->getId());
        $withdrawalInstructions = $this->setting->getForDomain(
            Setting::WITHDRAWAL_INSTRUCTIONS,
            $this->domain->getId(),
        );

        return $this->replaceVariables($order, $withdrawalInstructions);
    }

    /**
     * @param string $orderUrlHash
     * @return bool
     */
    public function canRequestOrderWithdrawal(string $orderUrlHash): bool
    {
        try {
            $this->withdrawalChecker->checkOrderWithdrawal($orderUrlHash);

            return true;
        } catch (WithdrawalException) {
            return false;
        }
    }

    /**
     * @param string $orderUrlHash
     * @return \DateTimeInterface|null
     */
    public function getWithdrawalDeadline(string $orderUrlHash): ?DateTimeInterface
    {
        $order = $this->orderFacade->getByUrlHashAndDomain($orderUrlHash, $this->domain->getId());

        return $this->withdrawalDeadlineCalculation->getWithdrawalDeadline($order);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param string $withdrawalInstructions
     * @return string
     */
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
