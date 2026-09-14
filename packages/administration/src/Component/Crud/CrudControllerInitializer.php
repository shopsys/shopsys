<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud;

use Override;
use Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sets Definition on the controller handling a CRUD route (the CRUD controller itself, its extension or any other
 * controller of a custom action) and on all extensions of the CRUD controller before action execution.
 * Runs at request time (after locale is set) to ensure correct translations.
 */
final class CrudControllerInitializer implements EventSubscriberInterface
{
    public function __construct(
        private readonly CrudControllerRegistry $crudControllerRegistry,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 1000],
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (is_array($controller)) {
            $controller = $controller[0];
        }

        $crudControllerClass = $event->getRequest()->attributes->get(CrudRouteProvider::CRUD_CONTROLLER_CLASS);

        if (!is_string($crudControllerClass)) {
            // methods of a CRUD controller routed by a plain Route attribute are not CRUD routes but still work with the Definition
            if (!($controller instanceof AbstractCrudController)) {
                return;
            }

            $crudControllerClass = $controller::class;
        }

        $definition = $this->crudControllerRegistry->getDefinition($crudControllerClass);

        if ($controller instanceof CrudDefinitionAwareInterface) {
            $controller->setDefinition($definition);
        }

        foreach ($definition->getExtensions() as $extension) {
            $extension->setDefinition($definition);
        }
    }
}
