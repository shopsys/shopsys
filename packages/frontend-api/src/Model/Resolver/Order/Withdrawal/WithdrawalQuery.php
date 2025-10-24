<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order\Withdrawal;

use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderNotFoundUserError;

class WithdrawalQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalFacade $withdrawalFacade
     */
    public function __construct(
        protected readonly WithdrawalFacade $withdrawalFacade,
    ) {
    }

    /**
     * @param string $orderUrlHash
     * @return string
     */
    public function withdrawalInstructionsQuery(string $orderUrlHash): string
    {
        try {
            return $this->withdrawalFacade->getWithdrawalInstructions($orderUrlHash);
        } catch (OrderNotFoundException) {
            throw new OrderNotFoundUserError();
        }
    }

    /**
     * @param string $orderUrlHash
     * @return bool
     */
    public function canRequestOrderWithdrawalQuery(string $orderUrlHash): bool
    {
        return $this->withdrawalFacade->canRequestOrderWithdrawal($orderUrlHash);
    }
}
