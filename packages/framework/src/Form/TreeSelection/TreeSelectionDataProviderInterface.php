<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\TreeSelection;

interface TreeSelectionDataProviderInterface
{
    /**
     * @param int[] $ids
     * @return array<int, \Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionEntityInterface>
     */
    public function getByIds(array $ids): array;

    /**
     * @param \Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionEntityInterface[] $selectedEntities
     * @return \Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionEntityInterface[]
     */
    public function getCollapsedTree(array $selectedEntities): array;
}
