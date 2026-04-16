<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Model\OAuth;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpToken;
use Shopsys\McpBundle\Model\OAuth\McpOAuthClientRegistrationFacade;
use Shopsys\McpBundle\Model\OAuth\McpOAuthClientRegistrationStorage;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class McpOAuthClientRegistrationFacadeTest extends TestCase
{
    public function testRegisterClientPersistsClaudeRegistration(): void
    {
        $registrationFacade = $this->createRegistrationFacade();

        $registration = $registrationFacade->registerClient(
            ['http://localhost:8765/callback'],
            'Claude Code',
        );

        $this->assertSame(32, strlen($registration->clientId));
        $this->assertSame('Claude Code', $registration->clientName);
        $this->assertSame(
            $registration->toArray(),
            $registrationFacade->findClientRegistrationDataByClientId($registration->clientId)?->toArray(),
        );
    }

    public function testRegisterClientRejectsUnsupportedRedirectUri(): void
    {
        $registrationFacade = $this->createRegistrationFacade();

        $this->expectException(InvalidArgumentException::class);
        $registrationFacade->registerClient(
            ['http://evil.example.com/callback'],
            'Claude Code',
        );
    }

    public function testRegisterClientAcceptsIpv6LoopbackRedirectUri(): void
    {
        $registrationFacade = $this->createRegistrationFacade();

        $registration = $registrationFacade->registerClient(
            ['http://[::1]:8765/callback'],
            'Claude Code',
        );

        $this->assertSame(['http://[::1]:8765/callback'], $registration->redirectUris);
    }

    public function testRegisterClientTruncatesOverlongClientName(): void
    {
        $registrationFacade = $this->createRegistrationFacade();
        $overlongClientName = str_repeat('a', AdministratorMcpToken::CLIENT_NAME_MAX_LENGTH + 1);

        $registration = $registrationFacade->registerClient(
            ['http://localhost:8765/callback'],
            $overlongClientName,
        );

        $this->assertSame(
            str_repeat('a', AdministratorMcpToken::CLIENT_NAME_MAX_LENGTH),
            $registration->clientName,
        );
    }

    public function testFindClientRegistrationByClientIdAndRedirectUriReturnsRegistrationOnlyForMatchingRedirectUri(): void
    {
        $registrationFacade = $this->createRegistrationFacade();
        $registration = $registrationFacade->registerClient(
            ['http://localhost:8765/callback'],
            'Claude Code',
        );

        $this->assertSame(
            $registration->toArray(),
            $registrationFacade->findClientRegistrationByClientIdAndRedirectUri(
                $registration->clientId,
                'http://localhost:8765/callback',
            )?->toArray(),
        );
        $this->assertNull(
            $registrationFacade->findClientRegistrationByClientIdAndRedirectUri(
                $registration->clientId,
                'http://localhost:8765/other-callback',
            ),
        );
        $this->assertNull($registrationFacade->findClientRegistrationByClientIdAndRedirectUri(null, null));
    }

    public function testFindClientRegistrationByClientIdAndRedirectUriReturnsRegistrationForLoopbackRedirectUriWithDifferentHostAndPort(): void
    {
        $registrationFacade = $this->createRegistrationFacade();
        $registration = $registrationFacade->registerClient(
            ['http://localhost:8765/callback'],
            'Claude Code',
        );

        $this->assertSame(
            $registration->toArray(),
            $registrationFacade->findClientRegistrationByClientIdAndRedirectUri(
                $registration->clientId,
                'http://127.0.0.1:51582/callback',
            )?->toArray(),
        );
        $this->assertSame(
            $registration->toArray(),
            $registrationFacade->findClientRegistrationByClientIdAndRedirectUri(
                $registration->clientId,
                'http://[::1]:41234/callback',
            )?->toArray(),
        );
    }

    public function testFindClientRegistrationByClientIdAndRedirectUriDoesNotReturnRegistrationForDifferentLoopbackPath(): void
    {
        $registrationFacade = $this->createRegistrationFacade();
        $registration = $registrationFacade->registerClient(
            ['http://localhost:8765/callback'],
            'Claude Code',
        );

        $this->assertNull(
            $registrationFacade->findClientRegistrationByClientIdAndRedirectUri(
                $registration->clientId,
                'http://127.0.0.1:51582/other-callback',
            ),
        );
    }

    protected function createRegistrationFacade(): McpOAuthClientRegistrationFacade
    {
        return new McpOAuthClientRegistrationFacade(
            new McpOAuthClientRegistrationStorage(new ArrayAdapter()),
        );
    }
}
