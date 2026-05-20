<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\EventSubscriber;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\McpBundle\Component\Availability\McpAvailabilityChecker;
use Shopsys\McpBundle\Component\Routing\McpRouteName;
use Shopsys\McpBundle\EventSubscriber\McpAvailabilitySubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class McpAvailabilitySubscriberTest extends TestCase
{
    private McpAvailabilitySubscriber $disabledSubscriber;

    private McpAvailabilitySubscriber $enabledSubscriber;

    #[Override]
    protected function setUp(): void
    {
        $this->disabledSubscriber = $this->createSubscriber(false);
        $this->enabledSubscriber = $this->createSubscriber(true);
    }

    public function testUnavailableMcpBlocksMcpRuntimeRequest(): void
    {
        $event = $this->createRequestEvent(McpRouteName::MCP_ENDPOINT);

        $this->disabledSubscriber->onKernelRequest($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame([
            'error' => [
                'code' => McpAvailabilitySubscriber::DISABLED_RESPONSE_CODE,
                'message' => McpAvailabilitySubscriber::DISABLED_RESPONSE_MESSAGE,
            ],
        ], json_decode((string)$response->getContent(), true, flags: JSON_THROW_ON_ERROR));
    }

    #[DataProvider('mcpOauthRequestDataProvider')]
    public function testUnavailableMcpBlocksMcpOauthRequests(
        string $routeName,
    ): void {
        $event = $this->createRequestEvent($routeName);

        $this->disabledSubscriber->onKernelRequest($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame([
            'error' => McpAvailabilitySubscriber::DISABLED_OAUTH_ERROR,
            'error_description' => McpAvailabilitySubscriber::DISABLED_RESPONSE_MESSAGE,
        ], json_decode((string)$response->getContent(), true, flags: JSON_THROW_ON_ERROR));
    }

    #[DataProvider('mcpAdminRequestDataProvider')]
    public function testUnavailableMcpThrowsNotFoundForMcpAdminRequests(
        string $routeName,
    ): void {
        $event = $this->createRequestEvent($routeName);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(McpAvailabilitySubscriber::DISABLED_RESPONSE_MESSAGE);

        $this->disabledSubscriber->onKernelRequest($event);
    }

    #[DataProvider('mcpRequestDataProvider')]
    public function testAvailableMcpDoesNotBlockMcpRequests(
        string $routeName,
    ): void {
        $event = $this->createRequestEvent($routeName);

        $this->enabledSubscriber->onKernelRequest($event);

        $this->assertFalse($event->hasResponse());
    }

    #[DataProvider('nonMcpRequestDataProvider')]
    public function testUnavailableMcpDoesNotBlockNonMcpRequests(
        string $routeName,
    ): void {
        $event = $this->createRequestEvent($routeName);

        $this->disabledSubscriber->onKernelRequest($event);

        $this->assertFalse($event->hasResponse());
    }

    public function testUnavailableMcpDoesNotBlockSubRequest(): void
    {
        $event = $this->createRequestEvent(
            McpRouteName::MCP_ENDPOINT,
            HttpKernelInterface::SUB_REQUEST,
        );

        $this->disabledSubscriber->onKernelRequest($event);

        $this->assertFalse($event->hasResponse());
    }

    /**
     * @return iterable<string, array{routeName: string}>
     */
    public static function mcpRequestDataProvider(): iterable
    {
        yield 'mcp runtime endpoint' => [
            'routeName' => McpRouteName::MCP_ENDPOINT,
        ];

        yield from self::mcpOauthRequestDataProvider();
        yield from self::mcpAdminRequestDataProvider();
    }

    /**
     * @return iterable<string, array{routeName: string}>
     */
    public static function mcpOauthRequestDataProvider(): iterable
    {
        yield 'mcp oauth register path' => [
            'routeName' => McpRouteName::MCP_OAUTH_REGISTER,
        ];

        yield 'mcp oauth token path' => [
            'routeName' => McpRouteName::MCP_OAUTH_TOKEN,
        ];

        yield 'mcp oauth metadata route' => [
            'routeName' => McpRouteName::MCP_OAUTH_METADATA,
        ];

        yield 'mcp oauth admin authorize route' => [
            'routeName' => McpRouteName::ADMIN_MCP_OAUTH_AUTHORIZE,
        ];
    }

    /**
     * @return iterable<string, array{routeName: string}>
     */
    public static function mcpAdminRequestDataProvider(): iterable
    {
        yield 'mcp administration route' => [
            'routeName' => McpRouteName::ADMIN_MCP_TOKEN,
        ];

        yield 'mcp administration manual token route' => [
            'routeName' => McpRouteName::ADMIN_MCP_TOKEN_MANUAL,
        ];

        yield 'mcp administration token revoke route' => [
            'routeName' => McpRouteName::ADMIN_MCP_TOKEN_REVOKE,
        ];
    }

    /**
     * @return iterable<string, array{routeName: string}>
     */
    public static function nonMcpRequestDataProvider(): iterable
    {
        yield 'administration dashboard' => [
            'routeName' => 'admin_default_dashboard',
        ];

        yield 'unrelated route' => [
            'routeName' => 'unrelated_route',
        ];
    }

    private function createSubscriber(bool $isEnabled): McpAvailabilitySubscriber
    {
        return new McpAvailabilitySubscriber(
            new McpAvailabilityChecker(
                $isEnabled ? 'shopsys_mcp' : '',
                $isEnabled ? 'secret' : '',
            ),
        );
    }

    private function createRequestEvent(
        string $routeName,
        int $requestType = HttpKernelInterface::MAIN_REQUEST,
    ): RequestEvent {
        $request = new Request();
        $request->attributes->set('_route', $routeName);

        return new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            $requestType,
        );
    }
}
