<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Error\InvalidTokenUserError;
use Shopsys\FrontendApiBundle\Model\Order\OrderFacade;

class OrdersResolver implements ResolverInterface, AliasedInterface
{
    protected const DEFAULT_FIRST_LIMIT = 10;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Order\OrderFacade
     */
    protected $orderFacade;

    /**
     * @var \Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder
     */
    protected $connectionBuilder;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderFacade $orderFacade
     */
    public function __construct(
        CurrentCustomerUser $currentCustomerUser,
        OrderFacade $orderFacade
    ) {
        $this->currentCustomerUser = $currentCustomerUser;
        $this->orderFacade = $orderFacade;
        $this->connectionBuilder = new ConnectionBuilder();
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function resolve(Argument $argument)
    {
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        if (!$customerUser) {
            throw new InvalidTokenUserError('Token is not valid.');
        }

        $paginator = new Paginator(function ($offset, $limit) use ($customerUser) {
            return $this->orderFacade->getCustomerUserOrderLimitedList($customerUser, $limit, $offset);
        });

        return $paginator->auto($argument, $this->orderFacade->getCustomerUserOrderCount($customerUser));
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     */
    protected function setDefaultFirstOffsetIfNecessary(Argument $argument): void
    {
        if ($argument->offsetExists('first') === false
            && $argument->offsetExists('last') === false
        ) {
            $argument->offsetSet('first', static::DEFAULT_FIRST_LIMIT);
        }
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolve' => 'orders',
        ];
    }
}
