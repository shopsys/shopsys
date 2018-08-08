<?php

namespace Shopsys\FrameworkBundle\Component\ConfirmDelete;

use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Symfony\Component\Templating\EngineInterface;

class ConfirmDeleteResponseFactory
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     */
    private $templating;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector
     */
    private $routeCsrfProtector;

    public function __construct(
        EngineInterface $templating,
        RouteCsrfProtector $routeCsrfProtector
    ) {
        $this->templating = $templating;
        $this->routeCsrfProtector = $routeCsrfProtector;
    }

    /**
     * @param mixed $entityId
     */
    public function createDeleteResponse(string $message, string $route, $entityId): \Symfony\Component\HttpFoundation\Response
    {
        return $this->templating->renderResponse('@ShopsysFramework/Components/ConfirmDelete/directDelete.html.twig', [
            'message' => $message,
            'route' => $route,
            'routeParams' => [
                'id' => $entityId,
                RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER => $this->routeCsrfProtector->getCsrfTokenByRoute($route),
            ],
        ]);
    }

    /**
     * @param mixed $entityId
     * @param object[] $possibleReplacements
     */
    public function createSetNewAndDeleteResponse(string $message, string $route, $entityId, array $possibleReplacements): \Symfony\Component\HttpFoundation\Response
    {
        foreach ($possibleReplacements as $object) {
            if (!is_object($object) || !method_exists($object, 'getName') || !method_exists($object, 'getId')) {
                $message = 'All items in argument 4 passed to ' . __METHOD__ . ' must be objects with methods getId and getName.';

                throw new \Shopsys\FrameworkBundle\Component\ConfirmDelete\Exception\InvalidEntityPassedException($message);
            }
        }

        return $this->templating->renderResponse('@ShopsysFramework/Components/ConfirmDelete/setNewAndDelete.html.twig', [
            'message' => $message,
            'route' => $route,
            'entityId' => $entityId,
            'routeCsrfToken' => $this->routeCsrfProtector->getCsrfTokenByRoute($route),
            'possibleReplacements' => $possibleReplacements,
            'CSRF_TOKEN_REQUEST_PARAMETER' => RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER,
        ]);
    }
}
