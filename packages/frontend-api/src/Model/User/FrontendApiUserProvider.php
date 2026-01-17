<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\User;

use Lcobucci\JWT\UnencryptedToken;
use Override;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FrontendApiUserProvider implements UserProviderInterface
{
    public function __construct(
        protected readonly FrontendApiUserFactory $frontendApiUserFactory,
    ) {
    }

    public function loadUserByToken(UnencryptedToken $token): FrontendApiUser
    {
        return $this->frontendApiUserFactory->createFromToken($token);
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        throw new NotImplementedException(
            'Method "loadUserByUsername" is not implement. Use method  "loadUserByToken"',
        );
    }

    #[Override]
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        throw new NotImplementedException(
            'Method "loadUserByIdentifier" is not implement. Use method  "loadUserByToken"',
        );
    }

    #[Override]
    public function refreshUser(UserInterface $user): UserInterface
    {
        throw new NotImplementedException('Method "refreshUser" is not implement.');
    }

    /**
     * @param mixed $frontendApiUser
     */
    #[Override]
    public function supportsClass($frontendApiUser): bool
    {
        return $frontendApiUser instanceof FrontendApiUser;
    }
}
