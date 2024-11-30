<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\FrameworkBundle\Component\Grid\GridView;

/**
 * @phpstan-import-type FieldOptions from \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor
 */
final class Datagrid
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<string, \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor>
     */
    private ArrayCollection $fields;

    private string $identificationName;

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface $adapter
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     */
    public function __construct(
        private readonly AdapterInterface $adapter,
        private readonly GridFactory $gridFactory,
    ) {
        $this->fields = new ArrayCollection();

        $this->addIdentifier('id');
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
     * @return \Shopsys\FrameworkBundle\Component\Grid\GridView
     */
    public function createView(): GridView
    {
        $query = $this->adapter->getDatasource($this->identificationName, $this->fields->toArray());
        $grid = $this->gridFactory->create('$entityClass', $query);

        foreach ($this->fields as $field) {
            if ($field->isVisible() === false) {
                continue;
            }

            $grid->addColumn($field->getName(), $field->getMappingProperty() ?? $this->identificationName, $field->getLabel(), $field->isSortable(), [
                'template' => $field->getTemplate(),
                'help' => $field->getHelp(),
            ]);
        }

        $grid->enablePaging();

        return $grid->createView();
    }
}
