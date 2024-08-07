<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use App\Model\Customer\User\CurrentCustomerUser;
use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilterFactory;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;

class OrderItemsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade $orderItemApiFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilterFactory $orderItemsFilterFactory
     */
    public function __construct(
        protected readonly OrderItemApiFacade $orderItemApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly OrderItemsFilterFactory $orderItemsFilterFactory,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|\GraphQL\Executor\Promise\Promise
     */
    public function orderItemsQuery(Argument $argument): ConnectionInterface|Promise
    {
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if (!$customerUser) {
            throw new InvalidTokenUserMessageException();
        }

        $filter = $this->orderItemsFilterFactory->createFromArgument($argument);
        $paginator = new Paginator(function ($offset, $limit) use ($customerUser, $filter) {
            return $this->orderItemApiFacade->getCustomerUserOrderItemsLimitedList(
                $customerUser,
                $limit,
                $offset,
                $filter,
            );
        });

        return $paginator->auto(
            $argument,
            $this->orderItemApiFacade->getCustomerUserOrderItemsLimitedListCount($customerUser, $filter),
        );
    }
}
