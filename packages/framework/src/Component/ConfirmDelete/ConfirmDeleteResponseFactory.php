<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ConfirmDelete;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\Exception\InvalidEntityPassedException;
use Shopsys\FrameworkBundle\Component\Html\EscapedMessageInterpolator;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Markup;

class ConfirmDeleteResponseFactory
{
    public function __construct(
        protected readonly Environment $twigEnvironment,
        protected readonly RouteCsrfProtector $routeCsrfProtector,
        protected readonly EscapedMessageInterpolator $escapedMessageInterpolator,
    ) {
    }

    /**
     * @param string|\Twig\Markup $message translated message, a string is rendered as plain text and \Twig\Markup as HTML, keep dynamic values out of it and pass them in $messageParameters
     * @param array<string, string|int|\Twig\Markup|null> $messageParameters placeholders replaced in the message by escaped values
     */
    public function createDeleteResponse(
        string|Markup $message,
        string $route,
        int|string $entityId,
        array $messageParameters = [],
    ): Response {
        $renderedTemplate = $this->twigEnvironment->render(
            '@ShopsysAdministration/partial/confirm_delete/direct_delete.html.twig',
            [
                'message' => $this->escapedMessageInterpolator->createHtml($message, $messageParameters),
                'route' => $route,
                'routeParams' => [
                    'id' => $entityId,
                    RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER => $this->routeCsrfProtector->getCsrfTokenByRoute(
                        $route,
                    ),
                ],
            ],
        );

        return new Response($renderedTemplate);
    }

    /**
     * @param string|\Twig\Markup $message translated message, a string is rendered as plain text and \Twig\Markup as HTML, keep dynamic values out of it and pass them in $messageParameters
     * @param object[] $possibleReplacements
     * @param array<string, string|int|\Twig\Markup|null> $messageParameters placeholders replaced in the message by escaped values
     */
    public function createSetNewAndDeleteResponse(
        string|Markup $message,
        string $route,
        int|string $entityId,
        array $possibleReplacements,
        array $messageParameters = [],
    ): Response {
        foreach ($possibleReplacements as $object) {
            if (!is_object($object) || !method_exists($object, 'getName') || !method_exists($object, 'getId')) {
                $message = 'All items in argument 4 passed to ' . __METHOD__ . ' must be objects with methods getId and getName.';

                throw new InvalidEntityPassedException($message);
            }
        }

        $renderedResponse = $this->twigEnvironment->render(
            '@ShopsysAdministration/partial/confirm_delete/set_new_and_delete.html.twig',
            [
                'message' => $this->escapedMessageInterpolator->createHtml($message, $messageParameters),
                'route' => $route,
                'entityId' => $entityId,
                'routeCsrfToken' => $this->routeCsrfProtector->getCsrfTokenByRoute($route),
                'possibleReplacements' => $possibleReplacements,
                'CSRF_TOKEN_REQUEST_PARAMETER' => RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER,
            ],
        );

        return new Response($renderedResponse);
    }
}
