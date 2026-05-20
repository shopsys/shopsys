<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\EventSubscriber;

use Override;
use Shopsys\McpBundle\Component\Availability\McpAvailabilityChecker;
use Shopsys\McpBundle\Component\Security\McpRequestMatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class McpAvailabilitySubscriber implements EventSubscriberInterface
{
    public const string DISABLED_RESPONSE_MESSAGE = 'MCP server is not configured.';
    public const string DISABLED_RESPONSE_CODE = 'mcp-not-configured';
    public const string DISABLED_OAUTH_ERROR = 'server_error';

    public function __construct(
        protected readonly McpAvailabilityChecker $mcpAvailabilityChecker,
    ) {
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            // Must run after RouterListener (32) so _route is available and before rate limiting (16) and firewall (8)
            // so disabled MCP requests do not consume rate-limit quota and never reach authentication.
            KernelEvents::REQUEST => ['onKernelRequest', 24],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (
            !$event->isMainRequest()
            || $this->mcpAvailabilityChecker->isAvailable()
            || !McpRequestMatcher::isMcpRequest($event->getRequest())
        ) {
            return;
        }

        if (McpRequestMatcher::isMcpOauthRequest($event->getRequest())) {
            $event->setResponse($this->createDisabledOauthJsonResponse());

            return;
        }

        if (McpRequestMatcher::isMcpAdminRequest($event->getRequest())) {
            throw new NotFoundHttpException(static::DISABLED_RESPONSE_MESSAGE);
        }

        $event->setResponse($this->createDisabledMcpJsonResponse());
    }

    protected function createDisabledMcpJsonResponse(): Response
    {
        return new JsonResponse([
            'error' => [
                'code' => static::DISABLED_RESPONSE_CODE,
                'message' => static::DISABLED_RESPONSE_MESSAGE,
            ],
        ], Response::HTTP_NOT_FOUND);
    }

    protected function createDisabledOauthJsonResponse(): Response
    {
        return new JsonResponse([
            'error' => static::DISABLED_OAUTH_ERROR,
            'error_description' => static::DISABLED_RESPONSE_MESSAGE,
        ], Response::HTTP_NOT_FOUND);
    }
}
