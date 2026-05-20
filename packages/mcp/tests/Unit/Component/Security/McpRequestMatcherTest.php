<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\Security;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\McpBundle\Component\Routing\McpRouteName;
use Shopsys\McpBundle\Component\Security\McpRequestMatcher;
use Symfony\Component\HttpFoundation\Request;

class McpRequestMatcherTest extends TestCase
{
    #[DataProvider('mcpRequestDataProvider')]
    public function testIsMcpRequest(
        string $routeName,
        bool $expectedIsMcpRequest,
    ): void {
        $request = $this->createRequest($routeName);

        $this->assertSame($expectedIsMcpRequest, McpRequestMatcher::isMcpRequest($request));
    }

    #[DataProvider('runtimeRequestDataProvider')]
    public function testIsMcpRuntimeRequest(
        string $routeName,
        bool $expectedIsMcpRuntimeRequest,
    ): void {
        $request = $this->createRequest($routeName);

        $this->assertSame($expectedIsMcpRuntimeRequest, McpRequestMatcher::isMcpRuntimeRequest($request));
    }

    #[DataProvider('oauthRequestDataProvider')]
    public function testIsMcpOauthRequest(
        string $routeName,
        bool $expectedIsMcpOauthRequest,
    ): void {
        $request = $this->createRequest($routeName);

        $this->assertSame($expectedIsMcpOauthRequest, McpRequestMatcher::isMcpOauthRequest($request));
    }

    #[DataProvider('adminRequestDataProvider')]
    public function testIsMcpAdminRequest(
        string $routeName,
        bool $expectedIsMcpAdminRequest,
    ): void {
        $request = $this->createRequest($routeName);

        $this->assertSame($expectedIsMcpAdminRequest, McpRequestMatcher::isMcpAdminRequest($request));
    }

    /**
     * @return iterable<string, array{routeName: string, expectedIsMcpRequest: bool}>
     */
    public static function mcpRequestDataProvider(): iterable
    {
        yield 'runtime route' => [
            'routeName' => McpRouteName::MCP_ENDPOINT,
            'expectedIsMcpRequest' => true,
        ];

        yield 'oauth register route' => [
            'routeName' => McpRouteName::MCP_OAUTH_REGISTER,
            'expectedIsMcpRequest' => true,
        ];

        yield 'admin route' => [
            'routeName' => McpRouteName::ADMIN_MCP_TOKEN,
            'expectedIsMcpRequest' => true,
        ];

        yield 'unrelated route' => [
            'routeName' => 'unrelated_route',
            'expectedIsMcpRequest' => false,
        ];
    }

    /**
     * @return iterable<string, array{routeName: string, expectedIsMcpRuntimeRequest: bool}>
     */
    public static function runtimeRequestDataProvider(): iterable
    {
        yield 'runtime route' => [
            'routeName' => McpRouteName::MCP_ENDPOINT,
            'expectedIsMcpRuntimeRequest' => true,
        ];

        yield 'unrelated route' => [
            'routeName' => 'unrelated_route',
            'expectedIsMcpRuntimeRequest' => false,
        ];
    }

    /**
     * @return iterable<string, array{routeName: string, expectedIsMcpOauthRequest: bool}>
     */
    public static function oauthRequestDataProvider(): iterable
    {
        yield 'oauth metadata route' => [
            'routeName' => McpRouteName::MCP_OAUTH_METADATA,
            'expectedIsMcpOauthRequest' => true,
        ];

        yield 'oauth register route' => [
            'routeName' => McpRouteName::MCP_OAUTH_REGISTER,
            'expectedIsMcpOauthRequest' => true,
        ];

        yield 'oauth token route' => [
            'routeName' => McpRouteName::MCP_OAUTH_TOKEN,
            'expectedIsMcpOauthRequest' => true,
        ];

        yield 'admin oauth authorize route' => [
            'routeName' => McpRouteName::ADMIN_MCP_OAUTH_AUTHORIZE,
            'expectedIsMcpOauthRequest' => true,
        ];

        yield 'unrelated route' => [
            'routeName' => 'unrelated_route',
            'expectedIsMcpOauthRequest' => false,
        ];
    }

    /**
     * @return iterable<string, array{routeName: string, expectedIsMcpAdminRequest: bool}>
     */
    public static function adminRequestDataProvider(): iterable
    {
        yield 'admin mcp token route' => [
            'routeName' => McpRouteName::ADMIN_MCP_TOKEN,
            'expectedIsMcpAdminRequest' => true,
        ];

        yield 'admin mcp manual token route' => [
            'routeName' => McpRouteName::ADMIN_MCP_TOKEN_MANUAL,
            'expectedIsMcpAdminRequest' => true,
        ];

        yield 'admin mcp token revoke route' => [
            'routeName' => McpRouteName::ADMIN_MCP_TOKEN_REVOKE,
            'expectedIsMcpAdminRequest' => true,
        ];

        yield 'admin mcp oauth authorize route' => [
            'routeName' => McpRouteName::ADMIN_MCP_OAUTH_AUTHORIZE,
            'expectedIsMcpAdminRequest' => false,
        ];

        yield 'admin non-mcp route' => [
            'routeName' => 'admin_default_dashboard',
            'expectedIsMcpAdminRequest' => false,
        ];
    }

    private function createRequest(string $routeName): Request
    {
        $request = new Request();
        $request->attributes->set('_route', $routeName);

        return $request;
    }
}
