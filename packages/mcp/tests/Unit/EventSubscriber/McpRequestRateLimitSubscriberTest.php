<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\EventSubscriber;

use Nette\Utils\Json;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\McpBundle\EventSubscriber\McpRequestRateLimitSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

class McpRequestRateLimitSubscriberTest extends TestCase
{
    private const int RATE_LIMIT = 2;
    private const string CLIENT_IP = '127.0.0.1';
    private const string SECOND_CLIENT_IP = '127.0.0.2';
    private const string THIRD_CLIENT_IP = '127.0.0.3';
    private const string VALID_PUBLIC_TOKEN_ID_A = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
    private const string VALID_PUBLIC_TOKEN_ID_B = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb';
    private const string VALID_PUBLIC_TOKEN_ID_C = 'cccccccccccccccccccccccccccccccc';
    private const string VALID_TOKEN_SECRET = 'dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd';
    private const string MALFORMED_BEARER_TOKEN = 'not-a-generated-mcp-token';

    private McpRequestRateLimitSubscriber $subscriber;

    #[Override]
    protected function setUp(): void
    {
        $this->subscriber = $this->createSubscriber();
    }

    public function testOauthRegisterEndpointIsLimitedByClientIp(): void
    {
        $this->assertRequestIsAllowed($this->createAndLimitRequest(McpRequestRateLimitSubscriber::ROUTE_MCP_OAUTH_REGISTER, '/mcp/oauth/register'));
        $this->assertRequestIsAllowed($this->createAndLimitRequest(McpRequestRateLimitSubscriber::ROUTE_MCP_OAUTH_REGISTER, '/mcp/oauth/register'));

        $this->assertTooManyRequestsResponse(
            $this->createAndLimitRequest(McpRequestRateLimitSubscriber::ROUTE_MCP_OAUTH_REGISTER, '/mcp/oauth/register'),
        );
    }

    public function testOauthTokenEndpointIsLimitedByClientIp(): void
    {
        $this->assertRequestIsAllowed($this->createAndLimitRequest(McpRequestRateLimitSubscriber::ROUTE_MCP_OAUTH_TOKEN, '/mcp/oauth/token'));
        $this->assertRequestIsAllowed($this->createAndLimitRequest(McpRequestRateLimitSubscriber::ROUTE_MCP_OAUTH_TOKEN, '/mcp/oauth/token'));

        $this->assertTooManyRequestsResponse(
            $this->createAndLimitRequest(McpRequestRateLimitSubscriber::ROUTE_MCP_OAUTH_TOKEN, '/mcp/oauth/token'),
        );
    }

    public function testMcpRuntimeEndpointIsLimitedByClientIpEvenWhenBearerTokenChanges(): void
    {
        $this->assertRequestIsAllowed($this->createAndLimitMcpRuntimeRequest(self::VALID_PUBLIC_TOKEN_ID_A));
        $this->assertRequestIsAllowed($this->createAndLimitMcpRuntimeRequest(self::VALID_PUBLIC_TOKEN_ID_B));

        $this->assertTooManyRequestsResponse(
            $this->createAndLimitMcpRuntimeRequest(self::VALID_PUBLIC_TOKEN_ID_C),
        );
    }

    public function testMcpRuntimeEndpointIsLimitedByBearerTokenPublicId(): void
    {
        $this->assertRequestIsAllowed($this->createAndLimitMcpRuntimeRequest(self::VALID_PUBLIC_TOKEN_ID_A));
        $this->assertRequestIsAllowed($this->createAndLimitMcpRuntimeRequest(self::VALID_PUBLIC_TOKEN_ID_A, self::SECOND_CLIENT_IP));

        $this->assertTooManyRequestsResponse(
            $this->createAndLimitMcpRuntimeRequest(self::VALID_PUBLIC_TOKEN_ID_A, self::THIRD_CLIENT_IP),
        );
    }

    public function testMcpRuntimeEndpointWithMalformedBearerTokenIsNotLimitedByTokenPublicId(): void
    {
        $this->assertRequestIsAllowed($this->createAndLimitMcpRuntimeRequestWithAuthorizationHeader(self::MALFORMED_BEARER_TOKEN));
        $this->assertRequestIsAllowed($this->createAndLimitMcpRuntimeRequestWithAuthorizationHeader(self::MALFORMED_BEARER_TOKEN, self::SECOND_CLIENT_IP));
        $this->assertRequestIsAllowed($this->createAndLimitMcpRuntimeRequestWithAuthorizationHeader(self::MALFORMED_BEARER_TOKEN, self::THIRD_CLIENT_IP));
    }

    public function testOptionsRequestsAreNotRateLimited(): void
    {
        $this->assertRequestIsAllowed($this->createAndLimitRequest(
            McpRequestRateLimitSubscriber::ROUTE_MCP_ENDPOINT,
            '/_mcp',
            Request::METHOD_OPTIONS,
        ));
        $this->assertRequestIsAllowed($this->createAndLimitRequest(
            McpRequestRateLimitSubscriber::ROUTE_MCP_ENDPOINT,
            '/_mcp',
            Request::METHOD_OPTIONS,
        ));
        $this->assertRequestIsAllowed($this->createAndLimitRequest(
            McpRequestRateLimitSubscriber::ROUTE_MCP_ENDPOINT,
            '/_mcp',
            Request::METHOD_OPTIONS,
        ));
    }

    private function createAndLimitMcpRuntimeRequest(
        string $publicTokenId,
        string $clientIp = self::CLIENT_IP,
    ): RequestEvent {
        return $this->createAndLimitMcpRuntimeRequestWithAuthorizationHeader(
            sprintf('Bearer %s.%s', $publicTokenId, self::VALID_TOKEN_SECRET),
            $clientIp,
        );
    }

    private function createAndLimitMcpRuntimeRequestWithAuthorizationHeader(
        string $authorizationHeader,
        string $clientIp = self::CLIENT_IP,
    ): RequestEvent {
        $event = $this->createRequestEvent(McpRequestRateLimitSubscriber::ROUTE_MCP_ENDPOINT, '/_mcp', $clientIp);
        $event->getRequest()->headers->set('Authorization', $authorizationHeader);

        $this->subscriber->limitMcpRequest($event);

        return $event;
    }

    private function createAndLimitRequest(
        string $routeName,
        string $path,
        string $method = Request::METHOD_GET,
    ): RequestEvent {
        $event = $this->createRequestEvent($routeName, $path, self::CLIENT_IP, $method);

        $this->subscriber->limitMcpRequest($event);

        return $event;
    }

    private function createRequestEvent(
        string $routeName,
        string $path,
        string $clientIp,
        string $method = Request::METHOD_GET,
    ): RequestEvent {
        $request = Request::create($path, $method, server: ['REMOTE_ADDR' => $clientIp]);
        $request->attributes->set('_route', $routeName);

        return new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );
    }

    private function createSubscriber(): McpRequestRateLimitSubscriber
    {
        return new McpRequestRateLimitSubscriber(
            $this->createRateLimiterFactory('oauth-register'),
            $this->createRateLimiterFactory('oauth-token'),
            $this->createRateLimiterFactory('mcp-runtime'),
        );
    }

    private function createRateLimiterFactory(string $id): RateLimiterFactory
    {
        return new RateLimiterFactory([
            'id' => $id,
            'policy' => 'fixed_window',
            'limit' => self::RATE_LIMIT,
            'interval' => '1 minute',
        ], new InMemoryStorage());
    }

    private function assertTooManyRequestsResponse(RequestEvent $event): void
    {
        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
        $this->assertSame((string)self::RATE_LIMIT, $response->headers->get(McpRequestRateLimitSubscriber::HEADER_RATE_LIMIT_LIMIT));
        $this->assertSame('0', $response->headers->get(McpRequestRateLimitSubscriber::HEADER_RATE_LIMIT_REMAINING));
        $this->assertNotSame('', $response->headers->get(McpRequestRateLimitSubscriber::HEADER_RETRY_AFTER));
        $this->assertSame(
            [
                'error' => [
                    'code' => McpRequestRateLimitSubscriber::ERROR_CODE_RATE_LIMIT_EXCEEDED,
                    'message' => McpRequestRateLimitSubscriber::ERROR_MESSAGE_RATE_LIMIT_EXCEEDED,
                ],
            ],
            Json::decode((string)$response->getContent(), true),
        );
    }

    private function assertRequestIsAllowed(RequestEvent $event): void
    {
        $this->assertFalse(
            $event->hasResponse(),
            'Rate limiter should allow the request to continue without setting an early response.',
        );
    }
}
