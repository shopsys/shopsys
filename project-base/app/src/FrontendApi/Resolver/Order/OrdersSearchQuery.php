<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Order;

use App\FrontendApi\Model\Order\OrderApiFacade;
use App\Model\Customer\User\CurrentCustomerUser;
use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;

class OrdersSearchQuery extends AbstractQuery
{
    /**
     * @param \App\FrontendApi\Model\Order\OrderApiFacade $orderApiFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        protected readonly OrderApiFacade $orderApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|\GraphQL\Executor\Promise\Promise
     */
    public function ordersSearchQuery(Argument $argument): ConnectionInterface|Promise
    {
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $searchInput = $argument['searchInput']['search'];

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if (!$customerUser) {
            throw new InvalidTokenUserMessageException();
        }

        $search = (string)$searchInput;
        $paginator = new Paginator(function ($offset, $limit) use ($customerUser, $search) {
            return $this->orderApiFacade->getCustomerUserOrderLimitedSearchList($search, $customerUser, $limit, $offset);
        });

        return $paginator->auto($argument, $this->orderApiFacade->getCustomerUserOrderLimitedSearchListCount($search, $customerUser));
    }
}
