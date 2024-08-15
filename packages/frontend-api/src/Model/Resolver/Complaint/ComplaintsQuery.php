<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Complaint;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;

class ComplaintsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade $complaintApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        protected readonly ComplaintApiFacade $complaintApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|\GraphQL\Executor\Promise\Promise
     */
    public function complaintsQuery(Argument $argument): ConnectionInterface|Promise
    {
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if (!$customerUser) {
            throw new InvalidTokenUserMessageException();
        }

        $paginator = new Paginator(function (int $offset, int $limit) use ($customerUser) {
            return $this->complaintApiFacade->getCustomerUserComplaintsLimitedList(
                $customerUser,
                $limit,
                $offset,
            );
        });

        return $paginator->auto(
            $argument,
            $this->complaintApiFacade->getCustomerUserComplaintsLimitedListCount($customerUser),
        );
    }
}
