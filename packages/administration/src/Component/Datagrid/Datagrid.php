<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use Shopsys\AdministrationBundle\Component\Action\RowAction;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
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
     * @var array{field: string, order: \Shopsys\AdministrationBundle\Component\Datagrid\OrderingEnum}|null
     */
    private ?array $defaultOrder = null;

    /**
     * @param DatagridOptions $options
     */
    public function __construct(
        private readonly AdapterInterface $adapter,
        private readonly GridFactory $gridFactory,
        array $options,
    ) {
        $this->fields = new ArrayCollection();
        $this->actions = new DatagridRowActions();
        $this->options = $this->resolveOptions($options);

        $this->configureDefaultCrudActions();
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
    public function setDefaultOrder(string $field, OrderingEnum $order): self
    {
        $this->defaultOrder = [
            'field' => $field,
            'order' => $order,
        ];

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
     *       property?: string|null
     *   } $options
     * @phpstan-param FieldOptions $options
     */
    public function add(string $name, array $options = []): self
    {
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
     *      property?: string|null
     *  } $options
     * @phpstan-param FieldOptions $options
     */
    public function update(string $name, array $options): self
    {
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
        if (!$this->fields->containsKey($name)) {
            throw new InvalidArgumentException(sprintf('Field with name "%s" does not exist.', $name));
        }

        $this->fields->remove($name);

        return $this;
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
        $datasource = $this->adapter->getDatasource($this->identificationName, $this->fields->getValues());
        $grid = $this->gridFactory->create($this->options['name'], $datasource, $this->options['roleConstant']);

        if ($this->fields->isEmpty() || $this->fields->forAll(fn ($key, FieldDescriptor $field) => $field->isVisible() === false)) {
            return $grid->createView();
        }

        foreach ($this->fields as $field) {
            if ($field->isVisible() === false) {
                continue;
            }

            $grid->addColumn($field->getName(), $field->getMappingProperty() ?? $this->identificationName, $field->getLabel(), $field->isSortable(), [
                'template' => $field->getTemplate(),
                'help' => $field->getHelp(),
            ]);
        }

        if ($this->options['pagination'] === true) {
            $grid->enablePaging();
        }

        if ($this->defaultOrder !== null) {
            $grid->setDefaultOrder($this->defaultOrder['field'], $this->defaultOrder['order']->value);
        }

        if (count($this->fieldsOrder) > 0) {
            $grid->reorderColumns($this->fieldsOrder);
        }

        foreach ($this->actions()->getRowActions() as $action) {
            $grid->addRowAction($action);
        }

        return $grid->createView();
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
