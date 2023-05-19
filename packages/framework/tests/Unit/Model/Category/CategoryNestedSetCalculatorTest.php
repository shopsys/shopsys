<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Category;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Category\CategoryNestedSetCalculator;

class CategoryNestedSetCalculatorTest extends TestCase
{
    public function testCalculateNestedSet(): void
    {
        /*
        Tested tree structure is following

            ├── 2
            │   ├── 3
            │   ├── 4
            │   │   ├── 5
            │   │   └── 6
            │   └── 7
            │       └── 8
            ├── 9
            │   └── 11
            ├── 10
            └── 12
        */

        $parentIdByCategoryId = [
            2 => null,
            3 => 2,
            4 => 2,
            5 => 4,
            6 => 4,
            7 => 2,
            8 => 7,
            9 => null,
            11 => 9,
            10 => null,
            12 => null,
        ];

        $actual = CategoryNestedSetCalculator::calculateNestedSetFromAdjacencyList($parentIdByCategoryId);

        // results are not ordered by ID by default
        usort($actual, fn ($a, $b) => $a['id'] <=> $b['id']);

        self::assertEquals($this->getExpectedData(), $actual);
    }

    /**
     * @return array<int, array{id: int, parent_id: int|null, depth: int, left: int, right: int}>
     */
    private function getExpectedData(): array
    {
        return [
            [
                'id' => 2,
                'parent_id' => null,
                'depth' => 0,
                'left' => 1,
                'right' => 14,
            ],
            [
                'id' => 3,
                'parent_id' => 2,
                'depth' => 1,
                'left' => 2,
                'right' => 3,
            ],
            [
                'id' => 4,
                'parent_id' => 2,
                'depth' => 1,
                'left' => 4,
                'right' => 9,
            ],
            [
                'id' => 5,
                'parent_id' => 4,
                'depth' => 2,
                'left' => 5,
                'right' => 6,
            ],
            [
                'id' => 6,
                'parent_id' => 4,
                'depth' => 2,
                'left' => 7,
                'right' => 8,
            ],
            [
                'id' => 7,
                'parent_id' => 2,
                'depth' => 1,
                'left' => 10,
                'right' => 13,
            ],
            [
                'id' => 8,
                'parent_id' => 7,
                'depth' => 2,
                'left' => 11,
                'right' => 12,
            ],
            [
                'id' => 9,
                'parent_id' => null,
                'depth' => 0,
                'left' => 15,
                'right' => 18,
            ],
            [
                'id' => 10,
                'parent_id' => null,
                'depth' => 0,
                'left' => 19,
                'right' => 20,
            ],
            [
                'id' => 11,
                'parent_id' => 9,
                'depth' => 1,
                'left' => 16,
                'right' => 17,
            ],
            [
                'id' => 12,
                'parent_id' => null,
                'depth' => 0,
                'left' => 21,
                'right' => 22,
            ],
        ];
    }
}
