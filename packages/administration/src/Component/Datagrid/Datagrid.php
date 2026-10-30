<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use LogicException;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use Shopsys\AdministrationBundle\Component\Action\RowAction;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\EntityClassAwareAdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\DatagridDataSource;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridState;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\Exception\EntityIsNotOrderableException;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use SortDirection;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

/**
 * @phpstan-import-type DatagridOptions from \Shopsys\AdministrationBundle\Component\Datagrid\DatagridFactory
 * @phpstan-import-type FieldOptions from \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor
 */
final class Datagrid
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<string, \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor>
     */
    private ArrayCollection $fields;

    private DatagridRowActions $actions;

    private FilterCollection $filters;

    private ?DatagridConfig $config = null;

    private string $identificationName = 'id';

    /**
     * @var DatagridOptions
     */
    private array $options;

    /**
     * @var string[]
     */
    private array $fieldsOrder = [];

    /**
     * @var array{field: string, order: \SortDirection}|null
     */
    private ?array $defaultOrder = null;

    /**
     * @var class-string|null
     */
    private ?string $dragAndDropEntityClass = null;

    /**
     * @param DatagridOptions $options
     */
    public function __construct(
        private readonly AdapterInterface $adapter,
        private readonly GridFactory $gridFactory,
        private readonly AccessCheckerInterface $accessChecker,
        private readonly AdministratorGridFacade $administratorGridFacade,
        private readonly Security $security,
        array $options,
    ) {
        $this->fields = new ArrayCollection();
        $this->actions = new DatagridRowActions();
        $this->filters = new FilterCollection();
        $this->options = $this->resolveOptions($options);

        $this->configureDefaultCrudActions();
    }

    /**
     * The configuration as the current administrator sees it, frozen on the first call — ask once the datagrid
     * is configured; the fields and the filters cannot be changed after that, the change would never reach
     * the state created from the configuration (see `DatagridStateFactory`).
     */
    public function getConfig(): DatagridConfig
    {
        return $this->config ??= new DatagridConfig(
            $this->options['name'],
            $this->adapter,
            $this->getAccessibleFields(),
            $this->getAccessibleFilters(),
            $this->getDomainControlConfig(),
        );
    }

    /**
     * @param DatagridOptions $options
     * @return DatagridOptions
     */
    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'name' => 'datagrid',
            'crudDefinition' => null,
            'pagination' => true,
            'domainControl' => null,
        ]);

        $resolver->setRequired('roleConstant');

        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('crudDefinition', [Definition::class, 'null']);
        $resolver->setAllowedTypes('pagination', 'bool');
        $resolver->setAllowedTypes('roleConstant', 'string');
        $resolver->setAllowedTypes('domainControl', [DomainControlConfig::class, 'null']);

        return $resolver->resolve($options);
    }

    /**
     * Set identification name that will be used as identifier in datagrid and in row actions.
     * Default identifier is 'id'
     */
    public function setIdentifier(string $identifier): self
    {
        $this->identificationName = $identifier;

        return $this;
    }

    /**
     * Enable or disable pagination in datagrid
     */
    public function setPagination(bool $pagination): self
    {
        $this->options['pagination'] = $pagination;

        return $this;
    }

    /**
     * Set default order of datagrid
     */
    public function setDefaultOrder(string $field, SortDirection $order): self
    {
        if ($this->dragAndDropEntityClass !== null) {
            throw new InvalidArgumentException(
                'Cannot set the default order while drag-and-drop is enabled, because in that mode the listing is always ordered by the field passed to "enableDragAndDrop()". Call "disableDragAndDrop()" first if you need a different default order.',
            );
        }

        $this->defaultOrder = [
            'field' => $field,
            'order' => $order,
        ];

        return $this;
    }

    /**
     * Enable drag-and-drop reordering of the rows, ordering the listing by the given field.
     */
    public function enableDragAndDrop(string $field): self
    {
        if (!$this->fields->containsKey($field)) {
            $this->add($field, [
                'visible' => false,
            ]);
        }

        if ($this->adapter instanceof EntityClassAwareAdapterInterface === false) {
            throw new InvalidArgumentException(sprintf(
                'Drag-and-drop ordering requires an adapter that knows its entity class (implementing "%s"), but "%s" was used.',
                EntityClassAwareAdapterInterface::class,
                $this->adapter::class,
            ));
        }

        $entityClass = $this->adapter->getEntityClass();

        if (is_subclass_of($entityClass, OrderableEntityInterface::class) === false) {
            throw new EntityIsNotOrderableException(sprintf(
                'Entity "%s" must implement "%s" to provide the "setPosition()" method used to persist the new order set by drag-and-drop.',
                $entityClass,
                OrderableEntityInterface::class,
            ));
        }

        $this->dragAndDropEntityClass = $entityClass;
        $this->defaultOrder = [
            'field' => $field,
            'order' => SortDirection::Ascending,
        ];

        return $this;
    }

    /**
     * Disable drag-and-drop reordering of the rows.
     */
    public function disableDragAndDrop(): self
    {
        $this->dragAndDropEntityClass = null;
        $this->defaultOrder = null;

        return $this;
    }

    /**
     * Array of field names in order they should be displayed. Field names not present in this array will be displayed at the end.
     *
     * @param string[] $columnIds
     */
    public function reorder(array $columnIds): self
    {
        $this->fieldsOrder = $columnIds;

        return $this;
    }

    /**
     * Define a new field in datagrid
     *
     * @param array{
     *       label?: string,
     *       visible?: bool,
     *       sortable?: bool,
     *       virtual?: bool,
     *       help?: string|null,
     *       template?: string|null,
     *       transform?: null|\Closure(mixed $value, mixed[] $row, mixed[][] $results): mixed,
     *       property?: string|string[]|null,
     *       role?: string|null,
     *       permission?: \Shopsys\FrameworkBundle\Component\Security\Role\Permission|null,
     *       class?: string|null,
     *       searchable?: bool,
     *       filterable?: bool
     *   } $options
     * @phpstan-param FieldOptions $options
     */
    public function add(string $name, array $options = []): self
    {
        $this->assertNotFrozen();

        if ($this->fields->containsKey($name)) {
            throw new InvalidArgumentException(sprintf('Field with name "%s" already exists.', $name));
        }

        $this->fields->set($name, new FieldDescriptor($name, $options));

        return $this;
    }

    /**
     * Update options of field in datagrid
     *
     * @param array{
     *      label?: string,
     *      visible?: bool,
     *      sortable?: bool,
     *      virtual?: bool,
     *      help?: string|null,
     *      template?: string|null,
     *      transform?: null|\Closure(mixed $value, mixed[] $row, mixed[][] $results): mixed,
     *      property?: string|string[]|null,
     *      role?: string|null,
     *      permission?: \Shopsys\FrameworkBundle\Component\Security\Role\Permission|null,
     *      class?: string|null,
     *      searchable?: bool,
     *      filterable?: bool
     *  } $options
     * @phpstan-param FieldOptions $options
     */
    public function update(string $name, array $options): self
    {
        $this->assertNotFrozen();

        if (!$this->fields->containsKey($name)) {
            throw new InvalidArgumentException(sprintf('Field with name "%s" does not exist.', $name));
        }

        $this->fields->get($name)->update($options);

        return $this;
    }

    /**
     * Remove field from datagrid
     */
    public function remove(string $name): self
    {
        $this->assertNotFrozen();

        if (!$this->fields->containsKey($name)) {
            throw new InvalidArgumentException(sprintf('Field with name "%s" does not exist.', $name));
        }

        if ($this->dragAndDropEntityClass !== null && $this->defaultOrder !== null && $this->defaultOrder['field'] === $name) {
            throw new InvalidArgumentException(sprintf(
                'Field "%s" cannot be removed because it is used as the ordering field for drag-and-drop. Call "disableDragAndDrop()" before removing it.',
                $name,
            ));
        }

        $this->fields->remove($name);

        return $this;
    }

    /**
     * The filters the administrator composes rules from, declared explicitly —
     * `$datagrid->filters()->add(TextFilter::new('name'))`.
     *
     * A field declared `'filterable' => true` gets a filter picked by the kind of value its path leads to; a filter
     * declared here under the name of that field (see `FieldDescriptor::getFilterName()`) replaces the picked one.
     * The filters are offered in the alphabetical order of their labels. Declare them while configuring the datagrid,
     * they are adapted to the adapter when the state of the datagrid is created (see `DatagridStateFactory`).
     */
    public function filters(): FilterCollection
    {
        $this->assertNotFrozen();

        return $this->filters;
    }

    /**
     * Class for managing row actions in datagrid
     */
    public function actions(): DatagridRowActions
    {
        return $this->actions;
    }

    /**
     * Lists the records the state narrows the datagrid to. The state is required, so that the domain control
     * of the datagrid is never left out by mistake — create it from the configuration by `DatagridStateFactory`.
     */
    public function createView(DatagridState $state): GridView
    {
        Assert::true($state->isCreatedFor($this->getConfig()), 'The state was created for the configuration of another datagrid.');

        // fields the administrator is not allowed to see are neither fetched nor displayed
        $accessibleFields = [...$this->getConfig()->fields, ...$this->getAdditionalFields($state)];

        $datasource = $this->adapter->getDatasource(new DatasourceRequest(
            $this->identificationName,
            $accessibleFields,
            $state->condition,
        ));

        if ($this->dragAndDropEntityClass !== null && $this->defaultOrder !== null) {
            $datasource = new PositionOrderedDataSource($datasource, $this->defaultOrder['field']);
        }
        $grid = $this->gridFactory->create($this->options['name'], $datasource, $this->options['roleConstant']);

        $visibleFields = array_filter($accessibleFields, static fn (FieldDescriptor $field) => $field->isVisible());

        if (count($visibleFields) === 0) {
            return $grid->createView();
        }

        $this->addColumns($grid, $visibleFields);

        if ($this->dragAndDropEntityClass !== null && $this->defaultOrder !== null) {
            // Pagination is intentionally left disabled - reordering and saving positions must work
            // over the whole list, not just the current page. Saving renumbers the listed records, so the records
            // narrowed by the administrator are only listed in their order, a part of them is never reordered.
            if ($state->isNarrowedByAdministrator() === false) {
                $grid->enableDragAndDrop($this->dragAndDropEntityClass, $this->defaultOrder['field']);
            }
        } elseif ($this->options['pagination'] === true) {
            $grid->enablePaging();
            $this->restoreAndRememberGridLimitOfCurrentAdministrator($grid);
        }

        if ($this->dragAndDropEntityClass === null && $this->defaultOrder !== null) {
            $grid->setDefaultOrder(
                $this->defaultOrder['field'],
                match ($this->defaultOrder['order']) {
                    SortDirection::Ascending => DataSourceInterface::ORDER_ASC,
                    SortDirection::Descending => DataSourceInterface::ORDER_DESC,
                },
            );
        }

        if (count($this->fieldsOrder) > 0) {
            $grid->reorderColumns($this->fieldsOrder);
        }

        foreach ($this->actions()->getRowActions() as $action) {
            $grid->addRowAction($action);
        }

        return $grid->createView();
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $fields
     */
    private function addColumns(Grid $grid, array $fields): void
    {
        foreach ($fields as $field) {
            $column = $grid->addColumn($field->getName(), $field->getMappingProperty() ?? $this->identificationName, $field->getLabel(), $this->dragAndDropEntityClass !== null ? false : $field->isSortable(), [
                'template' => $field->getTemplate(),
                'help' => $field->getHelp(),
            ]);

            if ($field->getClass() !== null) {
                $column->setClassAttribute($field->getClass());
            }

            if ($field->hasMultipleProperties()) {
                // the combined value is stored under the field name, so ordering must target the underlying properties instead
                $column->setOrderSourceColumnName(implode(DatagridDataSource::ORDER_PROPERTIES_SEPARATOR, $field->getProperties()));
            }
        }
    }

    private function isAccessible(FieldDescriptor $field): bool
    {
        if (!$field->isRestricted()) {
            return true;
        }

        return $this->accessChecker->hasPermission(
            $field->getRole() ?? $this->options['roleConstant'],
            $field->getPermission() ?? Permission::VIEW,
        );
    }

    private function restoreAndRememberGridLimitOfCurrentAdministrator(Grid $grid): void
    {
        $currentAdministrator = $this->security->getUser();

        if (!$currentAdministrator instanceof Administrator) {
            return;
        }

        $this->administratorGridFacade->restoreAndRememberGridLimit($currentAdministrator, $grid);
    }

    /**
     * The fields the current administrator is allowed to see — only those are fetched, displayed and narrowed by,
     * so that neither the quick search nor a filter can tell what a field hidden from the administrator holds.
     *
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[]
     */
    private function getAccessibleFields(): array
    {
        return array_values(array_filter($this->fields->getValues(), $this->isAccessible(...)));
    }

    /**
     * The declared filters the current administrator is allowed to use — a filter declared under the name of a field
     * hidden from the administrator, or over a path overlapping one of its properties (the property itself, a path
     * under it or the association it lies under), would tell what the field holds.
     */
    private function getAccessibleFilters(): FilterCollection
    {
        $hiddenFilterNames = [];
        $hiddenPaths = [];

        foreach ($this->fields as $field) {
            if ($this->isAccessible($field) === false) {
                $hiddenFilterNames[] = $field->getFilterName();
                array_push($hiddenPaths, ...$field->getProperties());
            }
        }

        $accessibleFilters = new FilterCollection();

        foreach ($this->filters as $filter) {
            if (in_array($filter->getName(), $hiddenFilterNames, true) === false && $this->overlapsAnyPath($filter->getPath(), $hiddenPaths) === false) {
                $accessibleFilters->add($filter);
            }
        }

        return $accessibleFilters;
    }

    /**
     * Whether the path is one of the paths, leads under one of them or lies above one of them, by whole segments.
     *
     * @param string[] $paths
     */
    private function overlapsAnyPath(string $path, array $paths): bool
    {
        foreach ($paths as $otherPath) {
            if ($path === $otherPath || str_starts_with($path, $otherPath . '.') || str_starts_with($otherPath, $path . '.')) {
                return true;
            }
        }

        return false;
    }

    /**
     * The fields displayed on behalf of the features narrowing the datagrid — a field declared on the datagrid takes
     * precedence, even one the administrator is not allowed to see, and a restricted field is displayed only to
     * the administrator allowed to see it.
     *
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[]
     */
    private function getAdditionalFields(DatagridState $state): array
    {
        return array_values(array_filter(
            $state->additionalFields,
            fn (FieldDescriptor $field): bool => $this->fields->containsKey($field->getName()) === false && $this->isAccessible($field),
        ));
    }

    /**
     * The domain control of a CRUD datagrid follows its configuration unless given explicitly; a datagrid outside
     * CRUD has none.
     */
    private function getDomainControlConfig(): ?DomainControlConfig
    {
        if ($this->options['domainControl'] !== null) {
            return $this->options['domainControl'];
        }

        /** @var \Shopsys\AdministrationBundle\Component\Crud\Definition|null $crudDefinition */
        $crudDefinition = $this->options['crudDefinition'];

        return $crudDefinition?->getConfig()->getDomainControlConfig();
    }

    private function assertNotFrozen(): void
    {
        if ($this->config !== null) {
            throw new LogicException('The configuration of the datagrid is already frozen (by getConfig()), so a change of it would never reach the state of the datagrid - configure the datagrid first.');
        }
    }

    private function configureDefaultCrudActions(): void
    {
        if ($this->options['crudDefinition'] === null) {
            return;
        }

        /** @var \Shopsys\AdministrationBundle\Component\Crud\Definition $crudDefinition */
        $crudDefinition = $this->options['crudDefinition'];

        $this->actions->add(
            RowAction::create('edit', t('Edit'), 'pencil')
                ->linkToCrud($crudDefinition->controllerClass, ActionType::EDIT, fn ($row) => (int)$row[$this->identificationName])
                ->displayIf(fn () => $crudDefinition->getConfig()->isActionEnabled(ActionType::EDIT)),
        );

        $this->actions->add(
            RowAction::create('delete', t('Delete'), 'trash')
                ->linkToCrud($crudDefinition->controllerClass, ActionType::DELETE, fn ($row) => (int)$row[$this->identificationName])
                ->displayIf(fn () => $crudDefinition->getConfig()->isActionEnabled(ActionType::DELETE))
                ->setConfirmMessage(t('Do you really want to delete this item?')),
        );
    }
}
