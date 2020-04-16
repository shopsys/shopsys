<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Logout;

use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogoutMutation implements MutationInterface, AliasedInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $tokenStorage;

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
        $this->tokenStorage = $tokenStorage;
        $this->customerUserRefreshTokenChainFacade = $customerUserRefreshTokenChainFacade;
    }

    /**
     * @return array
     */
    public function logout(): array
    {
        $token = $this->tokenStorage->getToken();

        if ($token === null) {
            throw new UserError('Unlogged user');
        }

        /** @var \Shopsys\FrontendApiBundle\Model\User\FrontendApiUser $user */
        $user = $token->getUser();

        if (!($user instanceof FrontendApiUser)) {
            throw new UserError('Unlogged user');
        }

        $this->customerUserRefreshTokenChainFacade->removeCustomerUserRefreshTokenChainsByDeviceId($user->getDeviceId());

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
