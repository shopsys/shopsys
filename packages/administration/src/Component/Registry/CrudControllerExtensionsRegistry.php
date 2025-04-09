<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Registry;

use SplPriorityQueue;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class CrudControllerExtensionsRegistry
{
    public const string CRUD_CONTROLLERS_EXTENSIONS_PARAMETER = 'shopsys.admin.crud_controllers_extensions';

    /**
     * @var array<class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>, \Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension[]>
     */
    private array $crudControllerExtensions;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param array<int, array{extensionClass: string, controllerClass: string, priority: int}> $crudControllerExtensions
     */
    public function __construct(
        private readonly ContainerInterface $container,
        array $crudControllerExtensions,
    ) {
        $this->loadExtensions($crudControllerExtensions);
    }

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $crudController
     * @return \Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension[]
     */
    public function getExtensions(string $crudController): array
    {
        return $this->crudControllerExtensions[$crudController] ?? [];
    }

    /**
     * @param array<int, array{extensionClass: string, controllerClass: string, priority: int}> $extensions
     */
    private function loadExtensions(array $extensions): void
    {
        $queueByController = [];

        foreach ($extensions as $extension) {
            if (isset($queueByController[$extension['controllerClass']]) === false) {
                $queueByController[$extension['controllerClass']] = new SplPriorityQueue();
            }

            $queueByController[$extension['controllerClass']]->insert(
                $this->container->get($extension['extensionClass']),
                $extension['priority'],
            );
        }

        foreach ($queueByController as $controller => $queue) {
            $queue->top();
            $queue->setExtractFlags(SplPriorityQueue::EXTR_DATA);
            $this->crudControllerExtensions[$controller] = array_reverse(iterator_to_array($queue));
        }
    }
}
