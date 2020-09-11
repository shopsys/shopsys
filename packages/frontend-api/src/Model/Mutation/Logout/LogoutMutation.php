<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Logout;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\BaseTokenMutation;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogoutMutation extends BaseTokenMutation implements MutationInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade
     */
    protected $customerUserRefreshTokenChainFacade;

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
     */
    public function __construct(TokenStorageInterface $tokenStorage, CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade)
    {
        parent::__construct($tokenStorage);

        $this->customerUserRefreshTokenChainFacade = $customerUserRefreshTokenChainFacade;
    }

    /**
     * @return array
     */
    public function logout(): array
    {
        $user = $this->runCheckUserIsLogged();

        $this->customerUserRefreshTokenChainFacade->removeCustomerUserRefreshTokenChainsByDeviceId(
            $user->getDeviceId()
        );

        return [
            true,
        ];
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'logout' => 'user_logout',
        ];
    }
}
