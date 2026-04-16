<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Model\OAuth;

use PHPUnit\Framework\TestCase;
use Shopsys\McpBundle\Model\OAuth\McpOAuthAuthorizationCodeFacade;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class McpOAuthAuthorizationCodeFacadeTest extends TestCase
{
    public function testConsumeAuthorizationCodeReturnsDataOnlyOnce(): void
    {
        $authorizationCodeFacade = new McpOAuthAuthorizationCodeFacade(new ArrayAdapter());
        $authorizationCode = $authorizationCodeFacade->createAuthorizationCode(
            123,
            'oauth_client_id',
            'http://localhost:8765/callback',
            'challenge',
        );

        $authorizationCodeData = $authorizationCodeFacade->consumeAuthorizationCode($authorizationCode);

        $this->assertSame(123, $authorizationCodeData['administrator_id']);
        $this->assertSame('oauth_client_id', $authorizationCodeData['client_id']);
        $this->assertNull($authorizationCodeFacade->consumeAuthorizationCode($authorizationCode));
    }
}
