<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderNotFoundUserError;

class OrderWithdrawalRequestMutation extends AbstractMutation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade $withdrawalRequestFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly WithdrawalRequestFacade $withdrawalRequestFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return bool
     */
    public function orderWithdrawalRequestMutation(Argument $argument): bool
    {
        $input = $argument['input'];

        try {
            $order = $this->orderFacade->getByUrlHashAndDomain(
                $input['orderUrlHash'],
                $this->domain->getId(),
            );
        } catch (OrderNotFoundException) {
            throw new OrderNotFoundUserError('Order not found.');
        }

        $this->withdrawalRequestFacade->createWithdrawalRequest($order, $input);

        return true;
    }
}
