<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ConfirmDelete;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\Exception\InvalidEntityPassedException;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Markup;
use Twig\Runtime\EscaperRuntime;

class ConfirmDeleteResponseFactory
{
    public function __construct(
        protected readonly Environment $twigEnvironment,
        protected readonly RouteCsrfProtector $routeCsrfProtector,
    ) {
    }

    /**
     * @param array<string, string|int|string[]|null> $messageParameters
     */
    public function createDeleteResponse(
        string $message,
        string $route,
        int|string $entityId,
        array $messageParameters = [],
    ): Response {
        $renderedTemplate = $this->twigEnvironment->render(
            '@ShopsysAdministration/partial/confirm_delete/direct_delete.html.twig',
            [
                'message' => $this->interpolateEscapedParameters($message, $messageParameters),
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
     * @param object[] $possibleReplacements
     * @param array<string, string|int|string[]|null> $messageParameters
     */
    public function createSetNewAndDeleteResponse(
        string $message,
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

        $messageHtml = $this->interpolateEscapedParameters($this->escape($message), $messageParameters);

        $renderedResponse = $this->twigEnvironment->render(
            '@ShopsysAdministration/partial/confirm_delete/set_new_and_delete.html.twig',
            [
                'message' => new Markup($messageHtml, 'UTF-8'),
                'route' => $route,
                'entityId' => $entityId,
                'routeCsrfToken' => $this->routeCsrfProtector->getCsrfTokenByRoute($route),
                'possibleReplacements' => $possibleReplacements,
                'CSRF_TOKEN_REQUEST_PARAMETER' => RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER,
            ],
        );

        return new Response($renderedResponse);
    }

    /**
     * @param array<string, string|int|string[]|null> $messageParameters
     */
    protected function interpolateEscapedParameters(string $messageHtml, array $messageParameters): string
    {
        $escapedParameters = array_map(
            fn (string|int|array|null $value): string => is_array($value) ? $this->escapeList($value) : $this->escape($value),
            $messageParameters,
        );

        return strtr($messageHtml, $escapedParameters);
    }

    /**
     * @param string[] $values
     */
    protected function escapeList(array $values): string
    {
        return implode('<br>', array_map($this->escape(...), $values));
    }

    protected function escape(string|int|null $value): string
    {
        return (string)$this->twigEnvironment->getRuntime(EscaperRuntime::class)->escape($value);
    }
}
