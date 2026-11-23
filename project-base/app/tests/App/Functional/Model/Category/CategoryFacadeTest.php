<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Category;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\Model\Category\Category;
use App\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope\DelayedEnvelopesCollector;
use Shopsys\FrameworkBundle\Model\Category\CategoryNestedSetCalculatorHelper;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\DispatchAllProductsMessage;
use Tests\App\Performance\Page\QueryCountingMiddleware;
use Tests\App\Test\TableRowTestHelper;
use Tests\App\Test\TransactionFunctionalTestCase;

final class CategoryFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private CategoryFacade $categoryFacade;

    /**
     * @inject
     */
    private DelayedEnvelopesCollector $delayedEnvelopesCollector;

    /**
     * @inject
     */
    private TableRowTestHelper $tableRowTestHelper;

    public function testReorderByUnchangedNestedSetValuesDoesNotRewriteCategoriesNorRecalculateProducts(): void
    {
        $categoriesOrderingData = new CategoryNestedSetCalculatorHelper()->calculateNestedSetFromAdjacencyList(
            $this->getCurrentParentIdIndexedByCategoryId(),
        );
        $rowVersionsBeforeReorder = $this->getCategoryRowVersions();
        $this->delayedEnvelopesCollector->resetEnvelopes();

        $this->categoryFacade->reorderByNestedSetValues($categoriesOrderingData);

        $this->assertSame($rowVersionsBeforeReorder, $this->getCategoryRowVersions());
        $this->assertFalse($this->isAllProductsRecalculationDispatched());
    }

    public function testReorderByChangedNestedSetValuesRecalculatesAllProducts(): void
    {
        $movedCategory = $this->getReference(CategoryDataFixture::CATEGORY_TV, Category::class);
        $newParentCategory = $this->getReference(CategoryDataFixture::CATEGORY_PHOTO, Category::class);
        $parentIdIndexedByCategoryId = $this->getCurrentParentIdIndexedByCategoryId();
        $parentIdIndexedByCategoryId[$movedCategory->getId()] = $newParentCategory->getId();
        $this->delayedEnvelopesCollector->resetEnvelopes();
        $queryCountingMiddleware = QueryCountingMiddleware::createInjectedInto($this->em->getConnection());

        $this->categoryFacade->reorderByNestedSetValues(
            new CategoryNestedSetCalculatorHelper()->calculateNestedSetFromAdjacencyList($parentIdIndexedByCategoryId),
        );

        $this->assertTrue($this->isAllProductsRecalculationDispatched());
        $this->assertCount(1, $queryCountingMiddleware->getExecutedQueriesMatching('~^\s*UPDATE categories\b~'));
    }

    /**
     * @return array<int, int|null>
     */
    private function getCurrentParentIdIndexedByCategoryId(): array
    {
        $rootCategoryId = $this->categoryFacade->getRootCategory()->getId();
        $categories = $this->em->getRepository(Category::class)->findBy([], ['lft' => 'ASC']);
        $parentIdIndexedByCategoryId = [];

        foreach ($categories as $category) {
            if ($category->getId() === $rootCategoryId) {
                continue;
            }

            $parentId = $category->getParent()->getId();
            $parentIdIndexedByCategoryId[$category->getId()] = $parentId === $rootCategoryId ? null : $parentId;
        }

        return $parentIdIndexedByCategoryId;
    }

    private function isAllProductsRecalculationDispatched(): bool
    {
        $envelopes = $this->delayedEnvelopesCollector->popEnvelopes();

        foreach ($envelopes as $envelope) {
            if ($envelope->getMessage() instanceof DispatchAllProductsMessage) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    private function getCategoryRowVersions(): array
    {
        return $this->tableRowTestHelper->getRowVersionsIndexedByPrimaryKey('categories', ['id']);
    }
}
