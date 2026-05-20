<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\EventSubscriber;

use Override;
use Shopsys\McpBundle\Component\Routing\McpRouteName;
use Shopsys\McpBundle\Component\Security\McpBearerToken;
use Shopsys\McpBundle\Component\Security\McpRequestMatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

class McpRequestRateLimitSubscriber implements EventSubscriberInterface
{
    public const string ERROR_CODE_RATE_LIMIT_EXCEEDED = 'mcp-rate-limit-exceeded';
    public const string ERROR_MESSAGE_RATE_LIMIT_EXCEEDED = 'Too many MCP requests. Please retry later.';
    public const string HEADER_RETRY_AFTER = 'Retry-After';
    public const string HEADER_RATE_LIMIT_LIMIT = 'X-RateLimit-Limit';
    public const string HEADER_RATE_LIMIT_REMAINING = 'X-RateLimit-Remaining';

    protected const string KEY_PREFIX_OAUTH_REGISTER = 'oauth-register';
    protected const string KEY_PREFIX_OAUTH_TOKEN = 'oauth-token';
    protected const string KEY_PREFIX_MCP_RUNTIME = 'mcp-runtime';
    protected const string KEY_PREFIX_MCP_TOKEN = 'mcp-token';

    public function __construct(
        protected readonly RateLimiterFactoryInterface $oauthRegisterRateLimiter,
        protected readonly RateLimiterFactoryInterface $oauthTokenRateLimiter,
        protected readonly RateLimiterFactoryInterface $runtimeRateLimiter,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            // Must run after RouterListener (32) so _route is available and before Firewall (8)
            // so unauthenticated MCP requests are still throttled.
            KernelEvents::REQUEST => ['limitMcpRequest', 16],
        ];
    }

    public function limitMcpRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($request->isMethod(Request::METHOD_OPTIONS)) {
            return;
        }

        if (McpRequestMatcher::isMcpRuntimeRequest($request)) {
            $rateLimits = $this->consumeRuntimeRateLimits($request);
        } else {
            $route = $request->attributes->getString('_route');

            $rateLimits = match ($route) {
                McpRouteName::MCP_OAUTH_REGISTER => [
                    $this->oauthRegisterRateLimiter
                        ->create($this->getIpBasedKey($request, self::KEY_PREFIX_OAUTH_REGISTER))
                        ->consume(),
                ],
                McpRouteName::MCP_OAUTH_TOKEN => [
                    $this->oauthTokenRateLimiter
                        ->create($this->getIpBasedKey($request, self::KEY_PREFIX_OAUTH_TOKEN))
                        ->consume(),
                ],
                default => [],
            };
        }

        foreach ($rateLimits as $rateLimit) {
            if (!$rateLimit->isAccepted()) {
                $event->setResponse($this->createTooManyRequestsResponse($rateLimit));

                return;
            }
        }
    }

    protected function getIpBasedKey(Request $request, string $prefix): string
    {
        return $this->getKey($prefix, $request->getClientIp() ?? 'unknown');
    }

    /**
     * @return array<\Symfony\Component\RateLimiter\RateLimit>
     */
    protected function consumeRuntimeRateLimits(Request $request): array
    {
        $ipRateLimit = $this->runtimeRateLimiter
            ->create($this->getIpBasedKey($request, self::KEY_PREFIX_MCP_RUNTIME))
            ->consume();

        if (!$ipRateLimit->isAccepted()) {
            return [$ipRateLimit];
        }

        $rateLimits = [$ipRateLimit];

        $authorizationHeader = $request->headers->get(McpBearerToken::HEADER_AUTHORIZATION);

        if ($authorizationHeader === null || !McpBearerToken::hasBearerScheme($authorizationHeader)) {
            return $rateLimits;
        }

        $tokenParts = McpBearerToken::parseTokenString(McpBearerToken::extractTokenString($authorizationHeader));

        if ($tokenParts !== null) {
            $rateLimits[] = $this->runtimeRateLimiter
                ->create($this->getKey(self::KEY_PREFIX_MCP_TOKEN, $tokenParts['publicTokenId']))
                ->consume();
        }

        return $rateLimits;
    }

    protected function getKey(string $prefix, string $value): string
    {
        return $prefix . ':' . $value;
    }

    protected function createTooManyRequestsResponse(RateLimit $rateLimit): JsonResponse
    {
        $retryAfterSeconds = max(1, $rateLimit->getRetryAfter()->getTimestamp() - time());

        return new JsonResponse(
            [
                'error' => [
                    'code' => self::ERROR_CODE_RATE_LIMIT_EXCEEDED,
                    'message' => self::ERROR_MESSAGE_RATE_LIMIT_EXCEEDED,
                ],
            ],
            Response::HTTP_TOO_MANY_REQUESTS,
            [
                self::HEADER_RETRY_AFTER => (string)$retryAfterSeconds,
                self::HEADER_RATE_LIMIT_LIMIT => (string)$rateLimit->getLimit(),
                self::HEADER_RATE_LIMIT_REMAINING => (string)$rateLimit->getRemainingTokens(),
            ],
        );
    }
}
