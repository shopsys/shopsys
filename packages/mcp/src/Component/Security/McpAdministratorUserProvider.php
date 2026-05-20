<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Security;

use Override;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\AdministratorNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class McpAdministratorUserProvider implements UserProviderInterface
{
    public function __construct(
        protected readonly AdministratorFacade $administratorFacade,
    ) {
    }

    #[Override]
    public function loadUserByIdentifier(string $identifier): Administrator
    {
        $administratorId = $this->getAdministratorIdFromIdentifier($identifier);

        try {
            return $this->administratorFacade->getById($administratorId);
        } catch (AdministratorNotFoundException) {
            throw new UserNotFoundException(sprintf('Unable to find an active admin identified by "%s".', $identifier));
        }
    }

    #[Override]
    public function refreshUser(UserInterface $user): UserInterface
    {
        $class = get_class($user);

        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
        }

        /** @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator */
        $administrator = $user;

        return $this->loadUserByIdentifier((string)$administrator->getId());
    }

    #[Override]
    public function supportsClass(string $class): bool
    {
        return $class === Administrator::class || is_subclass_of($class, Administrator::class);
    }

    protected function getAdministratorIdFromIdentifier(string $identifier): int
    {
        if ($identifier === '' || ctype_digit($identifier) === false) {
            throw new UserNotFoundException(sprintf('Unsupported MCP administrator identifier "%s".', $identifier));
        }

        return (int)$identifier;
    }
}
