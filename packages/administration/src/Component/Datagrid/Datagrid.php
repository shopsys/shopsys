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
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\PathDescribingAdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Narrowing\DatagridNarrowing;
use Shopsys\AdministrationBundle\Component\Datagrid\Narrowing\DatagridNarrowingResolver;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\Exception\EntityIsNotOrderableException;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use SortDirection;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

    private ?DatagridNarrowing $narrowing = null;

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
        private readonly DatagridNarrowingResolver $narrowingResolver,
        private readonly DatagridRequestState $requestState,
        private readonly ?DomainControlScope $domainControlScope,
        array $options,
    ) {
        $this->fields = new ArrayCollection();
        $this->actions = new DatagridRowActions();
        $this->filters = new FilterCollection();
        $this->options = $this->resolveOptions($options);

        $this->configureDefaultCrudActions();
    }

    /**
     * The domains the datagrid works with, null when it has no domain control. Known already while the datagrid
     * is configured, so it can decide about the fields.
     */
    public function getDomainControlScope(): ?DomainControlScope
    {
        return $this->domainControlScope;
    }

    /**
     * What narrows the records on behalf of the administrator — the domain control, the quick search and the filter
     * panel. Resolved from the fields and the filters on the first call (`createView()` makes one), so ask once
     * the datagrid is configured; the fields and the filters cannot be changed after that, the change would never
     * reach the narrowing.
     *
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotSearchableException
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotApplicableException
     */
    public function getNarrowing(): DatagridNarrowing
    {
        return $this->narrowing ??= $this->narrowingResolver->resolve(
            $this->adapter,
            $this->getAccessibleFields(),
            $this->getAccessibleFilters(),
            $this->requestState,
            $this->domainControlScope,
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
        ]);

        $resolver->setRequired('roleConstant');

        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('crudDefinition', [Definition::class, 'null']);
        $resolver->setAllowedTypes('pagination', 'bool');
        $resolver->setAllowedTypes('roleConstant', 'string');

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
     *       searchable?: bool
     *   } $options
     * @phpstan-param FieldOptions $options
     */
    public function add(string $name, array $options = []): self
    {
        $this->assertNotNarrowedYet();

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
     *      searchable?: bool
     *  } $options
     * @phpstan-param FieldOptions $options
     */
    public function update(string $name, array $options): self
    {
        $this->assertNotNarrowedYet();

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
        $this->assertNotNarrowedYet();

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
     * The filters are offered in the alphabetical order of their labels. Declare them while configuring the datagrid,
     * they are adapted to the adapter once the narrowing is resolved (see `getNarrowing()`).
     */
    public function filters(): FilterCollection
    {
        $this->assertNotNarrowedYet();

        return $this->filters;
    }

    /**
     * Class for managing row actions in datagrid
     */
    public function actions(): DatagridRowActions
    {
        return $this->actions;
    }

    public function createView(): GridView
    {
        $this->addDomainFieldIfWorthDisplaying();

        // fields the administrator is not allowed to see are neither fetched nor displayed
        $accessibleFields = $this->getAccessibleFields();

        $datasource = $this->adapter->getDatasource(new DatasourceRequest(
            $this->identificationName,
            $accessibleFields,
            $this->getNarrowing()->condition,
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
            if ($this->getNarrowing()->isNarrowedByAdministrator() === false) {
                $grid->enableDragAndDrop($this->dragAndDropEntityClass, $this->defaultOrder['field']);
            }
        } elseif ($this->options['pagination'] === true) {
            $grid->enablePaging();
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
     * hidden from the administrator, or over one of its properties, would tell what the field holds.
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
            if (in_array($filter->getName(), $hiddenFilterNames, true) === false && in_array($filter->getPath(), $hiddenPaths, true) === false) {
                $accessibleFilters->add($filter);
            }
        }

        return $accessibleFilters;
    }

    /**
     * Displays the domain of every record when the domain control works with several domains.
     *
     * Displaying the domain and limiting the records to the domains of the control are independent —
     * the limit is a part of every request the datagrid sends to its adapter, so hiding or removing the
     * field never widens the listed records. Adding the field manually takes precedence. A record related
     * to several domains (the path leads through a collection) has no single domain to display.
     */
    private function addDomainFieldIfWorthDisplaying(): void
    {
        $domainControlScope = $this->getDomainControlScope();

        if ($domainControlScope === null || $domainControlScope->isDomainWorthDisplaying() === false) {
            return;
        }

        if ($this->fields->containsKey($domainControlScope->domainIdPath) || $this->leadsThroughCollection($domainControlScope->domainIdPath)) {
            return;
        }

        // the field narrows nothing, so it is added even once the narrowing is resolved
        $this->fields->set($domainControlScope->domainIdPath, new FieldDescriptor($domainControlScope->domainIdPath, [
            'label' => t('Domain'),
            'template' => '@ShopsysAdministration/datagrid/cell/domain_icon.html.twig',
        ]));
    }

    private function leadsThroughCollection(string $path): bool
    {
        return $this->adapter instanceof PathDescribingAdapterInterface && $this->adapter->describePath($path)->isToMany();
    }

    private function assertNotNarrowedYet(): void
    {
        if ($this->narrowing !== null) {
            throw new LogicException('The narrowing of the datagrid is already resolved (by getNarrowing() or createView()), so a change of its configuration would never reach it - configure the datagrid first.');
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
