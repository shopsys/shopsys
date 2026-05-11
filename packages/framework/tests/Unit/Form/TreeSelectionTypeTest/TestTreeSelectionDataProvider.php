<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form\TreeSelectionTypeTest;

use Override;
use Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionDataProviderInterface;
use Tests\FrameworkBundle\Unit\Form\TreeSelectionTypeTest;

final class TestTreeSelectionDataProvider implements TreeSelectionDataProviderInterface
{
    /**
     * @param array<int, \Tests\FrameworkBundle\Unit\Form\TreeSelectionTypeTest\TestTreeSelectionItem> $itemsById
     */
    public function __construct(private readonly array $itemsById)
    {
    }

    /**
     * @param int[] $ids
     * @return array<int, \Tests\FrameworkBundle\Unit\Form\TreeSelectionTypeTest\TestTreeSelectionItem>
     */
    #[Override]
    public function getByIds(array $ids): array
    {
        $selectedItems = [];

        foreach ($ids as $id) {
            if (array_key_exists($id, $this->itemsById)) {
                $selectedItems[] = $this->itemsById[$id];
            }
        }

        return $selectedItems;
    }

    /**
     * @param \Tests\FrameworkBundle\Unit\Form\TreeSelectionTypeTest\TestTreeSelectionItem[] $selectedEntities
     * @return \Tests\FrameworkBundle\Unit\Form\TreeSelectionTypeTest\TestTreeSelectionItem[]
     */
    #[Override]
    public function getCollapsedTree(array $selectedEntities): array
    {
        if ($this->hasSelectedSubitemOfFirstTopLevelItem($selectedEntities)) {
            return [
                $this->itemsById[TreeSelectionTypeTest::FIRST_LEVEL_A_ITEM_ID],
                $this->itemsById[TreeSelectionTypeTest::A_FIRST_SUBITEM_ID],
                $this->itemsById[TreeSelectionTypeTest::A_SECOND_SUBITEM_ID],
                $this->itemsById[TreeSelectionTypeTest::FIRST_LEVEL_B_ITEM_ID],
            ];
        }

        return [
            $this->itemsById[TreeSelectionTypeTest::FIRST_LEVEL_A_ITEM_ID],
            $this->itemsById[TreeSelectionTypeTest::FIRST_LEVEL_B_ITEM_ID],
        ];
    }

    /**
     * @param \Tests\FrameworkBundle\Unit\Form\TreeSelectionTypeTest\TestTreeSelectionItem[] $selectedEntities
     */
    private function hasSelectedSubitemOfFirstTopLevelItem(array $selectedEntities): bool
    {
        foreach ($selectedEntities as $selectedEntity) {
            if (in_array($selectedEntity->getId(), [
                TreeSelectionTypeTest::A_FIRST_SUBITEM_ID,
                TreeSelectionTypeTest::A_SECOND_SUBITEM_ID,
            ], true)) {
                return true;
            }
        }

        return false;
    }
}
