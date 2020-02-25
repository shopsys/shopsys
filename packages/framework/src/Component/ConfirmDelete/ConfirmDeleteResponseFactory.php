<?php

namespace Shopsys\FrameworkBundle\Component\ConfirmDelete;

use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ConfirmDeleteResponseFactory
{
    /**
     * @var \Twig\Environment
     */
    protected $twigEnvironment;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector
     */
    protected $routeCsrfProtector;

    /**
     * @param \Twig\Environment $twigEnvironment
     * @param \Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector $routeCsrfProtector
     */
    public function __construct(
        Environment $twigEnvironment,
        RouteCsrfProtector $routeCsrfProtector
    ) {
        $this->twigEnvironment = $twigEnvironment;
        $this->routeCsrfProtector = $routeCsrfProtector;
    }

    /**
     * @param string $message
     * @param string $route
     * @param mixed $entityId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createDeleteResponse($message, $route, $entityId)
    {
        $renderedTemplate = $this->twigEnvironment->render('@ShopsysFramework/Components/ConfirmDelete/directDelete.html.twig', [
            'message' => $message,
            'route' => $route,
            'routeParams' => [
                'id' => $entityId,
                RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER => $this->routeCsrfProtector->getCsrfTokenByRoute($route),
            ],
        ]);

        return new Response($renderedTemplate);
    }

    /**
     * @param string $message
     * @param string $route
     * @param mixed $entityId
     * @param object[] $possibleReplacements
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createSetNewAndDeleteResponse($message, $route, $entityId, array $possibleReplacements)
    {
        foreach ($possibleReplacements as $object) {
            if (!is_object($object) || !method_exists($object, 'getName') || !method_exists($object, 'getId')) {
                $message = 'All items in argument 4 passed to ' . __METHOD__ . ' must be objects with methods getId and getName.';

                throw new \Shopsys\FrameworkBundle\Component\ConfirmDelete\Exception\InvalidEntityPassedException($message);
            }
        }

        $renderedResponse = $this->twigEnvironment->render('@ShopsysFramework/Components/ConfirmDelete/setNewAndDelete.html.twig', [
            'message' => $message,
            'route' => $route,
            'entityId' => $entityId,
            'routeCsrfToken' => $this->routeCsrfProtector->getCsrfTokenByRoute($route),
            'possibleReplacements' => $possibleReplacements,
            'CSRF_TOKEN_REQUEST_PARAMETER' => RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER,
        ]);

        return new Response($renderedResponse);
    }
}
