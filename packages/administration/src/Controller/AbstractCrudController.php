<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Closure;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Shopsys\AdministrationBundle\Component\Config\ActionsConfig;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Component\Crud\Extension\CrudDeleteHookExtensionInterface;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\OrmAdapterFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridFactory;
use Shopsys\FrameworkBundle\Component\HttpFoundation\SilencedExceptionEvent;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;
use Throwable;

abstract class AbstractCrudController extends AdminBaseController
{
    protected Definition $definition;

    #[Required]
    public DatagridFactory $datagridFactory;

    #[Required]
    public OrmAdapterFactory $ormAdapterFactory;

    #[Required]
    public LoggerInterface $logger;

    #[Required]
    public EventDispatcherInterface $eventDispatcher;

    public function setDefinition(Definition $definition): void
    {
        $this->definition = $definition;
    }

    public function configure(CrudConfig $config): void
    {
    }

    protected function configureActions(ActionsConfig $actions): void
    {
    }

    protected function configureDatagrid(Datagrid $datagrid): void
    {
    }

    protected function configureQuery(QueryBuilder $queryBuilder): void
    {
    }

    public function listAction(): Response
    {
        $adapter = $this->ormAdapterFactory->create($this->definition->entityClass, function (QueryBuilder $queryBuilder): void {
            $this->configureQuery($queryBuilder);
            $this->executeExtensions(fn (AbstractCrudControllerExtension $extension) => $extension->configureQuery($queryBuilder));
        });
        $datagrid = $this->datagridFactory->create($adapter, [
            'crudDefinition' => $this->definition,
            'name' => $this->definition->entityName,
            'roleConstant' => $this->definition->getRoleConstant(),
        ]);
        $this->configureDatagrid($datagrid);
        $this->executeExtensions(fn (AbstractCrudControllerExtension $extension) => $extension->configureDatagrid($datagrid));

        return $this->render('@ShopsysAdministration/crud/list.html.twig', [
            'title' => $this->definition->getConfig()->getTitle(ActionType::LIST),
            'grid' => $datagrid->createView(),
            'topActions' => $this->getConfiguredActions(ActionType::LIST),
        ]);
    }

    public function detailAction(int $id): Response
    {
        return $this->render('@ShopsysAdministration/crud/detail.html.twig', [
            'title' => $this->definition->getConfig()->getTitle(ActionType::DETAIL),
            'topActions' => $this->getConfiguredActions(ActionType::DETAIL),
        ]);
    }

    public function editAction(int $id): Response
    {
        return $this->render('@ShopsysAdministration/crud/edit.html.twig', [
            'title' => $this->definition->getConfig()->getTitle(ActionType::EDIT),
            'topActions' => $this->getConfiguredActions(ActionType::EDIT),
        ]);
    }

    public function createAction(): Response
    {
        return $this->render('@ShopsysAdministration/crud/new.html.twig', [
            'title' => $this->definition->getConfig()->getTitle(ActionType::CREATE),
            'topActions' => $this->getConfiguredActions(ActionType::CREATE),
        ]);
    }

    #[CsrfProtection]
    public function deleteAction(int $id): RedirectResponse
    {
        /** @var \Shopsys\AdministrationBundle\Component\Crud\Handler\DeleteHandlerInterface $handler */
        $handler = $this->definition->getHandlerForAction(ActionType::DELETE);
        $entity = $handler->getById($id);

        try {
            $this->executeExtensions(fn (CrudDeleteHookExtensionInterface $extension) => $extension->beforeDelete($entity), CrudDeleteHookExtensionInterface::class);
            $handler->delete($entity);
            $this->executeExtensions(fn (CrudDeleteHookExtensionInterface $extension) => $extension->afterDelete($entity), CrudDeleteHookExtensionInterface::class);

            if ($this->isFlashMessageBagEmpty()) {
                $this->addSuccessFlashTwig(
                    t('<strong>{{ objectName }}</strong> was deleted successfully.'),
                    [
                        'objectName' => $entity->toHumanReadable(),
                    ],
                );
            }
        } catch (Throwable $exception) {
            $this->executeExtensions(fn (CrudDeleteHookExtensionInterface $extension) => $extension->onDeleteError($entity, $exception), CrudDeleteHookExtensionInterface::class);
            $this->eventDispatcher->dispatch(new SilencedExceptionEvent());

            if ($this->hasErrorMessages() === false) {
                $this->addErrorFlashTwig(
                    t('An error occurred while deleting <strong>{{ objectName }}</strong>.'),
                    [
                        'objectName' => $entity->toHumanReadable(),
                    ],
                );
            }

            $this->logger->error(
                'Error from CrudController while running delete action',
                [
                    'message' => $exception->getMessage(),
                    'controllerClass' => static::class,
                    'action' => ActionType::DELETE,
                    'exception' => $exception,
                    'entityClass' => $this->definition->entityClass,
                    'entityId' => $id,
                    'entityName' => $entity->toHumanReadable(),
                ],
            );
        }

        return $this->redirect(
            $this->generateUrl(CrudTransformationHelper::generateRouteName($this->definition->controllerName, ActionType::LIST)),
        );
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Action\AbstractAction[]
     */
    final protected function getConfiguredActions(ActionType $actionType): array
    {
        $actionsConfig = new ActionsConfig(static::class, $this->definition->getConfig()->getActions());

        $this->configureActions($actionsConfig);
        $this->executeExtensions(fn (AbstractCrudControllerExtension $extension) => $extension->configureActions($actionsConfig));

        return $actionsConfig->getActions($actionType);
    }

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Component\Crud\Extension\CrudHookableExtensionInterface>|null $hookableInterface
     */
    private function executeExtensions(Closure $callback, ?string $hookableInterface = null): void
    {
        $extensions = $this->definition->getExtensions($hookableInterface);

        foreach ($extensions as $extension) {
            $callback($extension);
        }
    }
}
