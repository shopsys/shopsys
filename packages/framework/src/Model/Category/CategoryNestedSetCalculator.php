<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

class CategoryNestedSetCalculator
{
    /**
     * Calculates nested set values in the form usable in CategoryFacade::reorderByNestedSetValues() method
     *
     * @param array<int, int|null> $parentIdByCategoryId
     * @return array<int, array{id: int, parent_id: int|null, depth: int, left: int, right: int}>
     */
    public static function calculateNestedSetFromAdjacencyList(array $parentIdByCategoryId): array
    {
        $output = [];
        $count = 0;
        $lvl = -1;

        self::calculate(null, $count, $lvl, null, $parentIdByCategoryId, $output);

        return $output;
    }

    /**
     * @param int|null $root
     * @param int $count
     * @param int $lvl
     * @param int|null $parent
     * @param array<int, int|null> $parentIdByCategoryId
     * @param array<int, array{id: int|null, parent_id: int|null, depth: int, left: int, right: int}> $output
     */
    protected static function calculate(
        ?int $root,
        int &$count,
        int $lvl,
        ?int $parent,
        array $parentIdByCategoryId,
        array &$output,
    ): void {
        $lft = $count++;

        foreach (static::getChildren($parentIdByCategoryId, $root) as $id => $parentId) {
            $depth = $lvl + 1;
            self::calculate($id, $count, $depth, $parentId, $parentIdByCategoryId, $output);
        }

        $rgt = $count++;

        if ($root !== null) {
            $output[] = [
                'id' => $root,
                'parent_id' => $parent,
                'depth' => $lvl,
                'left' => $lft,
                'right' => $rgt,
            ];
        }
    }

    /**
     * @param array<int, int|null> $parentIdByCategoryId
     * @param int|null $parentId
     * @return array<int, int|null>
     */
    protected static function getChildren(array $parentIdByCategoryId, ?int $parentId): array
    {
        return array_filter($parentIdByCategoryId, static function ($value) use ($parentId) {
            return $value === $parentId;
        });
    }
}
