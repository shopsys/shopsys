<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\Security;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\McpBundle\Component\Security\McpAdministratorUserProvider;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class McpAdministratorUserProviderTest extends TestCase
{
    public function testLoadUserByIdentifierReturnsAdministrator(): void
    {
        $administrator = $this->createStub(Administrator::class);
        $administratorFacade = $this->createMock(AdministratorFacade::class);
        $administratorFacade->method('getById')->with(123)->willReturn($administrator);
        $provider = new McpAdministratorUserProvider($administratorFacade);

        $loadedAdministrator = $provider->loadUserByIdentifier('123');

        $this->assertSame($administrator, $loadedAdministrator);
    }

    public function testLoadUserByIdentifierThrowsExceptionForMalformedIdentifier(): void
    {
        $provider = new McpAdministratorUserProvider($this->createStub(AdministratorFacade::class));

        $this->expectException(UserNotFoundException::class);
        $provider->loadUserByIdentifier('invalid-identifier');
    }

    public function testRefreshUserThrowsExceptionForUnsupportedUser(): void
    {
        $provider = new McpAdministratorUserProvider($this->createStub(AdministratorFacade::class));

        $this->expectException(UnsupportedUserException::class);
        $provider->refreshUser(new class() implements UserInterface {
            public function getRoles(): array
            {
                return [];
            }

            public function getUserIdentifier(): string
            {
                return 'unsupported';
            }
        });
    }
}
