<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;

class OrderItemsSearchQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade $orderItemApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        protected readonly OrderItemApiFacade $orderItemApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|\GraphQL\Executor\Promise\Promise
     */
    public function orderItemsSearchQuery(Argument $argument): ConnectionInterface|Promise
    {
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $searchInput = $argument['searchInput']['search'];

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if (!$customerUser) {
            throw new InvalidTokenUserMessageException();
        }

        $search = (string)$searchInput;
        $paginator = new Paginator(function ($offset, $limit) use ($customerUser, $search) {
            return $this->orderItemApiFacade->getCustomerUserOrderItemsLimitedSearchList($search, $customerUser, $limit, $offset);
        });

        return $paginator->auto($argument, $this->orderItemApiFacade->getCustomerUserOrderItemsLimitedSearchListCount($search, $customerUser));
    }
}
