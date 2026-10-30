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
use Shopsys\AdministrationBundle\Component\Crud\Template\CrudTemplateParameters;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\OrmAdapterFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridStateFactory;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfigFactory;
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
    public DatagridStateFactory $datagridStateFactory;

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

    #[Required]
    public LoggableEntityConfigFactory $loggableEntityConfigFactory;

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

    /**
     * Shapes the query of the list statically — a fixed scope, a default join, an ordering. It runs once when
     * the adapter is created, before the datagrid reads what the administrator searched for or filtered by, so
     * a condition driven by the administrator never belongs here: it belongs to a searchable field or a filter
     * of the datagrid, see `configureDatagrid()`.
     */
    protected function configureQuery(QueryBuilder $queryBuilder): void
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Utils\Presentable|null $entity Null for create action, the existing entity for edit action
     */
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?Presentable $entity = null): void
    {
    }

    /**
     * The edit page shows the change history of every entity with entity logging enabled by the Loggable attribute
     */
    protected function isEntityLogShown(): bool
    {
        return $this->loggableEntityConfigFactory->getLoggableSetupByEntity($this->definition->entityClass)->isLoggable();
    }

    protected function configureTemplateParameters(CrudTemplateParameters $templateParameters): void
    {
    }

    final public function listAction(): Response
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

        $datagridState = $this->datagridStateFactory->create($datagrid->getConfig());

        $response = $this->renderAction(ActionType::LIST, [
            'title' => $this->definition->getConfig()->getTitle(ActionType::LIST),
            'grid' => $datagrid->createView($datagridState),
            'datagridState' => $datagridState,
            'topActions' => $this->getConfiguredActions(ActionType::LIST),
        ]);

        // the forms of the datagrid are rendered by its components, so the status is not set by render() itself
        if ($datagridState->isInvalid() && $response->getStatusCode() === Response::HTTP_OK) {
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $response;
    }

    final public function detailAction(int $id): Response
    {
        return $this->renderAction(ActionType::DETAIL, [
            'title' => $this->definition->getConfig()->getTitle(ActionType::DETAIL),
            'topActions' => $this->getConfiguredActions(ActionType::DETAIL),
        ]);
    }

    final public function editAction(Request $request, int $id): Response
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

                return $this->redirectAfterSave(
                    $form,
                    CrudTransformationHelper::generateRouteName($this->definition->controllerName, ActionType::LIST),
                    CrudTransformationHelper::generateRouteName($this->definition->controllerName, ActionType::EDIT),
                    ['id' => $id],
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

        return $this->renderAction(ActionType::EDIT, [
            'title' => $config->getTitle(ActionType::EDIT, $recordName),
            'topActions' => $this->getConfiguredActions(ActionType::EDIT),
            'entity' => $entity,
            'form' => $form->createView(),
            'showEntityLog' => $this->isEntityLogShown(),
        ], $entity);
    }

    final public function createAction(Request $request): Response
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

                return $this->redirectAfterSave(
                    $form,
                    CrudTransformationHelper::generateRouteName($this->definition->controllerName, ActionType::LIST),
                    CrudTransformationHelper::generateRouteName($this->definition->controllerName, ActionType::EDIT),
                    ['id' => $this->crudEntityIdentifierExtractor->getId($entity)],
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

        return $this->renderAction(ActionType::CREATE, [
            'title' => $this->definition->getConfig()->getTitle(ActionType::CREATE),
            'topActions' => $this->getConfiguredActions(ActionType::CREATE),
            'form' => $form->createView(),
        ]);
    }

    #[CsrfProtection]
    final public function deleteAction(int $id): RedirectResponse
    {
        /** @var \Shopsys\AdministrationBundle\Component\Crud\Handler\DeleteHandlerInterface $handler */
        $handler = $this->definition->getHandlerForAction(ActionType::DELETE);
        $entity = $handler->getById($id);
        $objectName = $entity->toHumanReadable();

        try {
            $this->executeExtensions(fn (CrudDeleteHookExtensionInterface $extension) => $extension->beforeDelete($entity), CrudDeleteHookExtensionInterface::class);
            $handler->delete($entity);
            $this->executeExtensions(fn (CrudDeleteHookExtensionInterface $extension) => $extension->afterDelete($entity), CrudDeleteHookExtensionInterface::class);

            if ($this->isFlashMessageBagEmpty()) {
                $this->addSuccessFlashTwig(
                    t('<strong>{{ objectName }}</strong> was deleted successfully.'),
                    [
                        'objectName' => $objectName,
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
                        'objectName' => $objectName,
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
     * Renders the configured template of the given action with the base parameters extended
     * by `configureTemplateParameters()` of the controller and of its extensions.
     *
     * @param array<string, mixed> $parameters
     * @param \Shopsys\FrameworkBundle\Component\Utils\Presentable|null $entity Null for the list and create actions, the displayed entity otherwise
     */
    final protected function renderAction(
        ActionType $actionType,
        array $parameters,
        ?Presentable $entity = null,
    ): Response {
        $templateParameters = new CrudTemplateParameters($actionType, $parameters, $entity);
        $this->configureTemplateParameters($templateParameters);
        $this->executeExtensions(fn (AbstractCrudControllerExtension $extension) => $extension->configureTemplateParameters($templateParameters));

        return $this->render(
            $this->definition->getConfig()->getTemplate($actionType),
            $templateParameters->toArray(),
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
