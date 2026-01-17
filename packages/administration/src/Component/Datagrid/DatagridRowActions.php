<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\Grid\GridRowActionInterface;

final class DatagridRowActions
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<string, \Shopsys\FrameworkBundle\Component\Grid\GridRowActionInterface>
     */
    private ArrayCollection $rowActions;

    public function __construct()
    {
        $this->rowActions = new ArrayCollection();
    }

    public function add(GridRowActionInterface $rowAction): self
    {
        $name = $rowAction->getName();

        if ($this->rowActions->containsKey($name)) {
            throw new InvalidArgumentException(sprintf('Row action with name "%s" already exists.', $name));
        }

        $this->rowActions->set($name, $rowAction);

        return $this;
    }

    /**
     * @template T of \Shopsys\FrameworkBundle\Component\Grid\GridRowActionInterface
     * @param \Closure(T): T $rowAction
     */
    public function update(string $name, Closure $rowAction): self
    {
        if (!$this->rowActions->containsKey($name)) {
            throw new InvalidArgumentException(sprintf('Row action with name "%s" does not exist.', $name));
        }

        $updatedRowAction = $rowAction($this->rowActions->get($name));

        if (!$updatedRowAction instanceof GridRowActionInterface) {
            throw new InvalidArgumentException(sprintf('Closure must return instance of "%s".', GridRowActionInterface::class));
        }

        $this->rowActions->set($name, $updatedRowAction);

        return $this;
    }

    public function delete(string $name): self
    {
        if (!$this->rowActions->containsKey($name)) {
            throw new InvalidArgumentException(sprintf('Row action with name "%s" does not exist.', $name));
        }

        $this->rowActions->remove($name);

        return $this;
    }

    /**
     * Reorder row actions by given order.
     *
     * @param string[] $orderedRowActionNames
     */
    public function reorder(array $orderedRowActionNames): self
    {
        $orderedRowActions = [];

        foreach ($orderedRowActionNames as $name) {
            $rowAction = $this->rowActions->get($name);

            if ($rowAction === null) {
                throw new InvalidArgumentException(sprintf('Row action with name "%s" does not exist.', $name));
            }

            $orderedRowActions[$name] = $rowAction;
        }

        $this->rowActions = new ArrayCollection([...$orderedRowActions, ...$this->rowActions->toArray()]);

        return $this;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\GridRowActionInterface[]
     */
    public function getRowActions(): array
    {
        return $this->rowActions->getValues();
    }
}
