<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\User;

use Lcobucci\JWT\Token;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FrontendApiCustomerUserProvider implements UserProviderInterface
{
    /**
     * @var \Shopsys\FrontendApiBundle\Model\User\FrontendApiUserFactoryInterface
     */
    protected $frontendApiUserFactory;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\User\FrontendApiUserFactoryInterface $frontendApiUserFactory
     */
    public function __construct(FrontendApiUserFactoryInterface $frontendApiUserFactory)
    {
        $this->frontendApiUserFactory = $frontendApiUserFactory;
    }

    /**
     * @param \Lcobucci\JWT\Token $token
     * @return \Shopsys\FrontendApiBundle\Model\User\FrontendApiUser
     */
    public function loadUserFromToken(Token $token): FrontendApiUser
    {
        return $this->frontendApiUserFactory->createFromToken($token);
    }

    /**
     * @param mixed $username
     */
    public function loadUserByUsername($username)
    {
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     */
    public function refreshUser(UserInterface $user)
    {
    }

    /**
     * @param mixed $class
     */
    public function supportsClass($class)
    {
        return $class === FrontendApiUser::class || is_subclass_of($class, FrontendApiUser::class);
    }
}
