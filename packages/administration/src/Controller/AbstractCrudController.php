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
use Shopsys\AdministrationBundle\Component\Crud\Extension\CrudCreateHookExtensionInterface;
use Shopsys\AdministrationBundle\Component\Crud\Extension\CrudDeleteHookExtensionInterface;
use Shopsys\AdministrationBundle\Component\Crud\Extension\CrudEditHookExtensionInterface;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudEntityIdentifierExtractor;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\OrmAdapterFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridFactory;
use Shopsys\FrameworkBundle\Component\HttpFoundation\SilencedExceptionEvent;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;
use Throwable;

#[AutoconfigureTag('shopsys.admin.crud_controllers')]
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

    #[Required]
    public FormFactoryInterface $formFactory;

    #[Required]
    public BreadcrumbOverrider $breadcrumbOverrider;

    #[Required]
    public CrudEntityIdentifierExtractor $crudEntityIdentifierExtractor;

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

    /**
     * @param object|null $entity Null for create action, the existing entity for edit action
     */
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
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

    public function editAction(Request $request, int $id): Response
    {
        /** @var \Shopsys\AdministrationBundle\Component\Crud\Handler\EditHandlerInterface $handler */
        $handler = $this->definition->getHandlerForAction(ActionType::EDIT);
        $entity = $handler->getById($id);
        $data = $handler->createDataFromEntity($entity);

        $formConfigurator = new CrudFormConfigurator($this->formFactory, $data, ActionType::EDIT);
        $this->configureForm($formConfigurator, $entity);
        $this->executeExtensions(fn (AbstractCrudControllerExtension $extension) => $extension->configureForm($formConfigurator, $entity));

        $form = $formConfigurator->buildForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->executeExtensions(fn (CrudEditHookExtensionInterface $extension) => $extension->beforeEdit($entity, $data), CrudEditHookExtensionInterface::class);
                $handler->edit($entity, $data);
                $this->executeExtensions(fn (CrudEditHookExtensionInterface $extension) => $extension->afterEdit($entity, $data), CrudEditHookExtensionInterface::class);

                if ($this->isFlashMessageBagEmpty()) {
                    $this->addEditSuccessFlash($entity, $id);
                }

                return $this->redirect(
                    $this->generateUrl(CrudTransformationHelper::generateRouteName($this->definition->controllerName, ActionType::LIST)),
                );
            } catch (Throwable $exception) {
                $this->executeExtensions(fn (CrudEditHookExtensionInterface $extension) => $extension->onEditError($entity, $data, $exception), CrudEditHookExtensionInterface::class);
                $this->eventDispatcher->dispatch(new SilencedExceptionEvent());

                if ($this->hasErrorMessages() === false) {
                    $this->addErrorFlashTwig(
                        t('An error occurred while saving <strong>{{ objectName }}</strong>.'),
                        [
                            'objectName' => $entity->toHumanReadable(),
                        ],
                    );
                }

                $this->logger->error(
                    'Error from CrudController while running edit action',
                    [
                        'message' => $exception->getMessage(),
                        'controllerClass' => static::class,
                        'action' => ActionType::EDIT,
                        'exception' => $exception,
                        'entityClass' => $this->definition->entityClass,
                        'entityId' => $id,
                    ],
                );
            }
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $config = $this->definition->getConfig();
        $recordName = $entity->toHumanReadable();
        $this->breadcrumbOverrider->overrideLastItem($config->getBreadcrumbTitle(ActionType::EDIT) . ' - ' . $recordName);

        return $this->render('@ShopsysAdministration/crud/edit.html.twig', [
            'title' => $config->getTitle(ActionType::EDIT, $recordName),
            'topActions' => $this->getConfiguredActions(ActionType::EDIT),
            'form' => $form->createView(),
        ]);
    }

    public function createAction(Request $request): Response
    {
        /** @var \Shopsys\AdministrationBundle\Component\Crud\Handler\CreateHandlerInterface $handler */
        $handler = $this->definition->getHandlerForAction(ActionType::CREATE);
        $data = $handler->createData();

        $formConfigurator = new CrudFormConfigurator($this->formFactory, $data, ActionType::CREATE);
        $this->configureForm($formConfigurator, null);
        $this->executeExtensions(fn (AbstractCrudControllerExtension $extension) => $extension->configureForm($formConfigurator, null));

        $form = $formConfigurator->buildForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->executeExtensions(fn (CrudCreateHookExtensionInterface $extension) => $extension->beforeCreate($data), CrudCreateHookExtensionInterface::class);
                $entity = $handler->create($data);
                $this->executeExtensions(fn (CrudCreateHookExtensionInterface $extension) => $extension->afterCreate($entity, $data), CrudCreateHookExtensionInterface::class);

                if ($this->isFlashMessageBagEmpty()) {
                    $this->addCreateSuccessFlash($entity);
                }

                return $this->redirect(
                    $this->generateUrl(CrudTransformationHelper::generateRouteName($this->definition->controllerName, ActionType::LIST)),
                );
            } catch (Throwable $exception) {
                $this->executeExtensions(fn (CrudCreateHookExtensionInterface $extension) => $extension->onCreateError($data, $exception), CrudCreateHookExtensionInterface::class);
                $this->eventDispatcher->dispatch(new SilencedExceptionEvent());

                if ($this->hasErrorMessages() === false) {
                    $this->addErrorFlashTwig(t('An error occurred while creating.'));
                }

                $this->logger->error(
                    'Error from CrudController while running create action',
                    [
                        'message' => $exception->getMessage(),
                        'controllerClass' => static::class,
                        'action' => ActionType::CREATE,
                        'exception' => $exception,
                        'entityClass' => $this->definition->entityClass,
                    ],
                );
            }
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/crud/new.html.twig', [
            'title' => $this->definition->getConfig()->getTitle(ActionType::CREATE),
            'topActions' => $this->getConfiguredActions(ActionType::CREATE),
            'form' => $form->createView(),
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

    private function addEditSuccessFlash(Presentable $entity, int $id): void
    {
        $this->addSuccessFlashTwig(
            t('<strong><a href="{{ url }}">{{ objectName }}</a></strong> was saved successfully.'),
            [
                'objectName' => $entity->toHumanReadable(),
                'url' => $this->generateEditUrl($id),
            ],
        );
    }

    private function addCreateSuccessFlash(Presentable $entity): void
    {
        $this->addSuccessFlashTwig(
            t('<strong><a href="{{ url }}">{{ objectName }}</a></strong> was created successfully.'),
            [
                'objectName' => $entity->toHumanReadable(),
                'url' => $this->generateEditUrl($this->crudEntityIdentifierExtractor->getId($entity)),
            ],
        );
    }

    private function generateEditUrl(int $id): string
    {
        return $this->generateUrl(
            CrudTransformationHelper::generateRouteName(
                $this->definition->controllerName,
                ActionType::EDIT,
            ),
            [
                'id' => $id,
            ],
        );
    }
}
