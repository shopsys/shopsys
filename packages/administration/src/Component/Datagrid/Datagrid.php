<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfigData;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
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

    private DatagridActions $actions;

    private string $identificationName;

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
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface $adapter
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\DatagridManager $datagridManager
     * @param DatagridOptions $options
     */
    public function __construct(
        private readonly AdapterInterface $adapter,
        private readonly DatagridManager $datagridManager,
        array $options,
    ) {
        $this->fields = new ArrayCollection();
        $this->actions = new DatagridActions();
        $this->options = $this->resolveOptions($options);

        $this->addIdentifier('id');
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
            'crudConfig' => null,
            'pagination' => true,
        ]);

        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('crudConfig', [CrudConfigData::class, 'null']);
        $resolver->setAllowedTypes('pagination', 'bool');

        return $resolver->resolve($options);
    }

    /**
     * @param string $name
     * @return self
     */
    public function addIdentifier($name): self
    {
        $field = new FieldDescriptor($name, [
            'label' => t('ID'),
            'sortable' => true,
        ]);

        $this->fields->set($name, $field);
        $this->identificationName = $name;

        return $this;
    }

    /**
     * Enable or disable pagination in datagrid
     *
     * @param bool $pagination
     * @return self
     */
    public function setPagination(bool $pagination): self
    {
        $this->options['pagination'] = $pagination;

        return $this;
    }

    /**
     * Set default order of datagrid
     *
     * @param string $field
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\OrderingEnum $order
     * @return self
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
     * @return self
     */
    public function reorder(array $columnIds): self
    {
        $this->fieldsOrder = $columnIds;

        return $this;
    }

    /**
     * Define a new field in datagrid
     *
     * @param string $name
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
     * @return self
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
     * @param string $name
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
     * @return self
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
     *
     * @param string $name
     * @return self
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
     * Class for managing actions in datagrid
     *
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\DatagridActions
     */
    public function actions(): DatagridActions
    {
        return $this->actions;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\GridView
     */
    public function createView(): GridView
    {
        $query = $this->adapter->getDatasource($this->identificationName, $this->fields->toArray());
        $grid = $this->datagridManager->createGrid($this->options['name'], $query);

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

        foreach ($this->actions->getActions() as $action) {
            $actionColumn = $grid->addActionColumn($action['icon'], $action['label'], $action['routeName'], ['id' => 'id'], $action['additionalParameters']);

            if ($action['confirmMessage'] !== null) {
                $actionColumn->setConfirmMessage($action['confirmMessage']);
            }
        }

        return $grid->createView();
    }

    private function configureDefaultCrudActions(): void
    {
        if ($this->options['crudConfig'] === null) {
            return;
        }

        /** @var \Shopsys\AdministrationBundle\Component\Config\CrudConfigData $crudConfig */
        $crudConfig = $this->options['crudConfig'];

        if ($crudConfig->isActionEnabled(ActionType::EDIT) && $this->fields->containsKey('edit') === false) {
            $this->actions->add('edit', [
                'label' => t('Edit'),
                'icon' => 'edit',
                'routeName' => $this->datagridManager->getCrudRouteProvider()->generateCrudRoute($crudConfig->getCrudController(), ActionType::EDIT),
            ]);
        }

        if ($crudConfig->isActionEnabled(ActionType::DELETE) && $this->fields->containsKey('delete') === false) {
            $this->actions->add('delete', [
                'label' => t('Delete'),
                'icon' => 'delete',
                'routeName' => $this->datagridManager->getCrudRouteProvider()->generateCrudRoute($crudConfig->getCrudController(), ActionType::DELETE),
            ]);
        }
    }
}
