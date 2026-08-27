<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Closure;
use Doctrine\ORM\QueryBuilder;
use LogicException;
use Psr\Log\LoggerInterface;
use Shopsys\AdministrationBundle\Component\Config\ActionsConfig;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudListDomainControl;
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
use Shopsys\AdministrationBundle\Component\Search\AdvancedSearchApplier;
use Shopsys\AdministrationBundle\Component\Search\AdvancedSearchFormFactory;
use Shopsys\AdministrationBundle\Component\Search\Operator;
use Shopsys\AdministrationBundle\Component\Search\QuickSearchApplier;
use Shopsys\AdministrationBundle\Component\Search\SearchConfig;
use Shopsys\AdministrationBundle\Component\Search\SearchConfigFactory;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface;
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
use Symfony\Component\HttpFoundation\RequestStack;
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

    #[Required]
    public AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade;

    #[Required]
    public AdminDomainTabsFacade $adminDomainTabsFacade;

    #[Required]
    public Domain $domain;

    #[Required]
    public RequestStack $requestStack;

    #[Required]
    public QuickSearchApplier $quickSearchApplier;

    #[Required]
    public SearchConfigFactory $searchConfigFactory;

    #[Required]
    public AdvancedSearchFormFactory $advancedSearchFormFactory;

    #[Required]
    public AdvancedSearchApplier $advancedSearchApplier;

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
     * Configure searching on the list page (quick search and advanced search).
     */
    public function configureSearch(SearchConfig $search): void
    {
    }

    protected function getSelectedListDomainId(): ?int
    {
        return match ($this->definition->getConfig()->getListDomainControl()) {
            CrudListDomainControl::QUICK_FILTER => $this->adminDomainFilterTabsFacade->getSelectedDomainId(
                $this->getListDomainFilterNamespace(),
                $this->getListDomainIds(),
            ),
            CrudListDomainControl::SWITCHER => $this->adminDomainTabsFacade->getSelectedDomainId(),
            null => throw new LogicException('List domain control is not configured.'),
        };
    }

    /**
     * Returns the namespace the quick domain filter stores its selection under, generated from the controller name.
     */
    protected function getListDomainFilterNamespace(): string
    {
        return 'crud_' . CrudTransformationHelper::transformToRouteName($this->definition->controllerName);
    }

    /**
     * @return int[]
     */
    protected function getListDomainIds(): array
    {
        $allowedDomainIds = $this->definition->getConfig()->getListAllowedDomainIds();
        $adminEnabledDomainIds = $this->domain->getAdminEnabledDomainIds();

        if ($allowedDomainIds === null) {
            return $adminEnabledDomainIds;
        }

        return array_values(array_intersect($adminEnabledDomainIds, $allowedDomainIds));
    }

    /**
     * Returns the domain IDs the list is allowed to show: the selected domain,
     * or all domains available to the list when "All domains" is selected.
     *
     * @return int[]
     */
    protected function getEffectiveListDomainIds(): array
    {
        $selectedDomainId = $this->getSelectedListDomainId();

        return $selectedDomainId !== null ? [$selectedDomainId] : $this->getListDomainIds();
    }

    /**
     * Adds the list domain condition on the given DQL field; matches nothing when no domain is available.
     */
    protected function addListDomainIdsCondition(QueryBuilder $queryBuilder, string $domainIdField): void
    {
        $domainIds = $this->getEffectiveListDomainIds();

        if ($domainIds === []) {
            $queryBuilder->andWhere('1 = 0');

            return;
        }

        $queryBuilder
            ->andWhere($domainIdField . ' IN (:listDomainFilterDomainIds)')
            ->setParameter('listDomainFilterDomainIds', $domainIds);
    }

    /**
     * Applies the configured domain condition to the list query of a domain-separated entity.
     *
     * Override with an empty body to opt out, or with custom logic when the domain relation
     * cannot be expressed as a condition on the root entity's `domainId` field.
     */
    protected function applyListDomainFilter(QueryBuilder $queryBuilder): void
    {
        if ($this->definition->getConfig()->getListDomainControl() === null
            || !is_a($this->definition->entityClass, DomainSeparatedEntityInterface::class, true)
        ) {
            return;
        }

        $this->addListDomainIdsCondition($queryBuilder, $queryBuilder->getRootAliases()[0] . '.domainId');
    }

    /**
     * @param object|null $entity Null for create action, the existing entity for edit action
     */
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
    {
    }

    protected function getEditTemplate(): string
    {
        return '@ShopsysAdministration/crud/edit.html.twig';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getEditViewData(object $entity): array
    {
        return [];
    }

    public function listAction(): Response
    {
        $listDomainControl = $this->definition->getConfig()->getListDomainControl();
        $searchConfig = $this->getSearchConfig();
        $quickSearchText = $this->getQuickSearchText();
        $advancedSearchForm = null;
        $isAdvancedSearchSubmitted = false;

        if ($searchConfig->hasAdvancedSearch()) {
            $request = $this->requestStack->getCurrentRequest();
            $advancedSearchForm = $this->advancedSearchFormFactory->createForm($searchConfig, $request);
            $isAdvancedSearchSubmitted = $this->advancedSearchFormFactory->isSubmitted($request);
        }
        $adapter = $this->ormAdapterFactory->create($this->definition->entityClass, function (QueryBuilder $queryBuilder): void {
            $this->applyListDomainFilter($queryBuilder);
            $this->configureQuery($queryBuilder);
            $this->executeExtensions(fn (AbstractCrudControllerExtension $extension) => $extension->configureQuery($queryBuilder));
        });

        if ($isAdvancedSearchSubmitted) {
            $this->advancedSearchApplier->apply($searchConfig, $adapter->getProxyQuery()->getQueryBuilder(), $advancedSearchForm);
        } elseif ($searchConfig->isQuickSearchEnabled() && $quickSearchText !== null) {
            $this->quickSearchApplier->apply($searchConfig->getQuickSearchDefinition(), $adapter->getProxyQuery(), $quickSearchText);
        }
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
            'listDomainControl' => $listDomainControl,
            'listDomainFilterNamespace' => $listDomainControl === CrudListDomainControl::QUICK_FILTER ? $this->getListDomainFilterNamespace() : null,
            'listDomainIds' => $listDomainControl === CrudListDomainControl::QUICK_FILTER ? $this->getListDomainIds() : [],
            'quickSearchDefinition' => $searchConfig->getQuickSearchDefinition(),
            'quickSearchText' => $quickSearchText,
            'advancedSearchForm' => $advancedSearchForm?->createView(),
            'isAdvancedSearchSubmitted' => $isAdvancedSearchSubmitted,
            'advancedSearchRuleFormUrl' => $searchConfig->hasAdvancedSearch() ? $this->generateUrl('admin_crud_search_rule_form', [
                'crudControllerName' => CrudTransformationHelper::transformToRouteName($this->definition->controllerName),
            ]) : null,
            'advancedSearchResetUrl' => $searchConfig->hasAdvancedSearch() ? $this->generateUrl(
                CrudTransformationHelper::generateRouteName($this->definition->controllerName, ActionType::LIST),
                [SearchConfig::ADVANCED_SEARCH_FLAG_QUERY_PARAMETER => 1],
            ) : null,
            'advancedSearchValuelessOperators' => Operator::getValuelessOperatorValues(),
        ]);
    }

    final protected function getSearchConfig(): SearchConfig
    {
        return $this->searchConfigFactory->create($this, $this->definition);
    }

    protected function getQuickSearchText(): ?string
    {
        $searchText = trim($this->requestStack->getCurrentRequest()?->query->getString(SearchConfig::QUICK_SEARCH_QUERY_PARAMETER) ?? '');

        return $searchText === '' ? null : $searchText;
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

        return $this->render($this->getEditTemplate(), [
            'title' => $config->getTitle(ActionType::EDIT, $recordName),
            'topActions' => $this->getConfiguredActions(ActionType::EDIT),
            'form' => $form->createView(),
            ...$this->getEditViewData($entity),
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
