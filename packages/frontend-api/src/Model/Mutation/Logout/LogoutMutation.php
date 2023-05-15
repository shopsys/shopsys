<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Logout;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\BaseTokenMutation;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogoutMutation extends BaseTokenMutation
{
    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        protected readonly CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade,
    ) {
        parent::__construct($tokenStorage);
    }

    /**
     * @return array
     */
    public function logoutMutation(): array
    {
        $user = $this->runCheckUserIsLogged();

        $this->customerUserRefreshTokenChainFacade->removeCustomerUserRefreshTokenChainsByDeviceId(
            $user->getDeviceId(),
        );

        return [
            true,
        ];
    }
}
