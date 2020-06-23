<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Customer\User;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserWarning;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class CurrentCustomerUserResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(CurrentCustomerUser $currentCustomerUser)
    {
        $this->currentCustomerUser = $currentCustomerUser;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function resolver(): CustomerUser
    {
        $currentCustomerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        if ($currentCustomerUser === null) {
            throw new UserWarning('No customer user is currently logged in.');
        }

        return $currentCustomerUser;
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolver' => 'currentCustomerUser',
        ];
    }
}
