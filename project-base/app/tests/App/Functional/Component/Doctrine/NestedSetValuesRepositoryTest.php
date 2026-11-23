<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Doctrine;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\Model\Category\Category;
use Shopsys\FrameworkBundle\Component\Doctrine\NestedSetValuesRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

final class NestedSetValuesRepositoryTest extends TransactionFunctionalTestCase
{
    public function testUpdateNestedSetValuesInChunksRewritesOnlyChangedRows(): void
    {
        $movedCategory = $this->getReference(CategoryDataFixture::CATEGORY_TV, Category::class);
        $shiftedCategory = $this->getReference(CategoryDataFixture::CATEGORY_PHOTO, Category::class);
        $nestedSetValuesIndexedByCategoryId = $this->getNestedSetValuesIndexedByCategoryId();
        $nestedSetValuesIndexedByCategoryId[$movedCategory->getId()]['parent'] = $shiftedCategory->getId();
        $nestedSetValuesIndexedByCategoryId[$shiftedCategory->getId()]['lft'] += 1000;
        $nestedSetValuesIndexedByCategoryId[$shiftedCategory->getId()]['rgt'] += 1000;

        $changedCategoriesCount = $this->createNestedSetValuesRepositoryWithTwoRowsBatches()
            ->updateNestedSetValues(Category::class, $nestedSetValuesIndexedByCategoryId);

        $this->assertSame(2, $changedCategoriesCount);
        $this->assertSame($nestedSetValuesIndexedByCategoryId, $this->getNestedSetValuesIndexedByCategoryId());
    }

    private function createNestedSetValuesRepositoryWithTwoRowsBatches(): NestedSetValuesRepository
    {
        return new class($this->em) extends NestedSetValuesRepository {
            protected const int NESTED_SET_UPDATE_BATCH_SIZE = 2;
        };
    }

    /**
     * @return array<int, array{parent: int, level: int, lft: int, rgt: int}>
     */
    private function getNestedSetValuesIndexedByCategoryId(): array
    {
        return $this->em->getConnection()->fetchAllAssociativeIndexed(
            'SELECT id, parent_id AS parent, level, lft, rgt FROM categories WHERE parent_id IS NOT NULL ORDER BY id',
        );
    }
}
