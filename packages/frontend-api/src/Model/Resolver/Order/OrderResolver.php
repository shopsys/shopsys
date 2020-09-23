<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderFacade as FrontendApiOrderFacade;

class OrderResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderFacade
     */
    protected $orderFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Order\OrderFacade
     */
    protected $frontendApiOrderFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderFacade $frontendApiOrderFacade
     */
    public function __construct(
        CurrentCustomerUser $currentCustomerUser,
        OrderFacade $orderFacade,
        Domain $domain,
        FrontendApiOrderFacade $frontendApiOrderFacade
    ) {
        $this->orderFacade = $orderFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->domain = $domain;
        $this->frontendApiOrderFacade = $frontendApiOrderFacade;
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlHash
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function resolver(?string $uuid = null, ?string $urlHash = null): Order
    {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        try {
            if ($uuid !== null && $customerUser !== null) {
                return $this->getOrderForCustomerUserByUuid($customerUser, $uuid);
            }

            if ($urlHash !== null) {
                return $this->orderFacade->getByUrlHashAndDomain($urlHash, $this->domain->getId());
            }
        } catch (OrderNotFoundException $orderNotFoundException) {
            throw new UserError($orderNotFoundException->getMessage());
        }

        throw new UserError('You need to be logged in or provide argument \'urlHash\'.');
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolver' => 'order',
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    protected function getOrderForCustomerUserByUuid(
        CustomerUser $customerUser,
        string $uuid
    ): Order {
        if (Uuid::isValid($uuid) === false) {
            throw new UserError('Provided argument \'uuid\' is not valid.');
        }

        return $this->frontendApiOrderFacade->getByUuidAndCustomerUser($uuid, $customerUser);
    }
}
