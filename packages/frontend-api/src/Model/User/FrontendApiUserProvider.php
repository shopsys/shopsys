<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\User;

use Lcobucci\JWT\UnencryptedToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FrontendApiUserProvider implements UserProviderInterface
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
     * @param \Lcobucci\JWT\UnencryptedToken $token
     * @return \Shopsys\FrontendApiBundle\Model\User\FrontendApiUser
     */
    public function loadUserByToken(UnencryptedToken $token): FrontendApiUser
    {
        return $this->frontendApiUserFactory->createFromToken($token);
    }

    /**
     * @param mixed $username
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function loadUserByUsername($username): UserInterface
    {
        throw new NotImplementedException(
            'Method "loadUserByUsername" is not implement. Use method  "loadUserByToken"'
        );
    }

    /**
     * @param string $identifier
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->loadUserByUsername($identifier);
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        throw new NotImplementedException('Method "refreshUser" is not implement.');
    }

    /**
     * @param mixed $frontendApiUser
     * @return bool
     */
    public function supportsClass($frontendApiUser): bool
    {
        return $frontendApiUser instanceof FrontendApiUser;
    }
}
