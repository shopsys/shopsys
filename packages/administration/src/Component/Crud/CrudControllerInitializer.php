<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud;

use Override;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sets Definition on the current CRUD controller before action execution.
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

        if ($controller instanceof AbstractCrudController) {
            $controller->setDefinition(
                $this->crudControllerRegistry->getDefinition($controller::class),
            );
        }
    }
}
