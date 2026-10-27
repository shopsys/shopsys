<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Category;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\Model\Category\Category;
use App\Model\Category\CategoryDataFactory;
use App\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRepository;
use Tests\App\Test\TableRowTestHelper;
use Tests\App\Test\TransactionFunctionalTestCase;

final class CategoryVisibilityRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private CategoryVisibilityRepository $categoryVisibilityRepository;

    /**
     * @inject
     */
    private CategoryFacade $categoryFacade;

    /**
     * @inject
     */
    private CategoryDataFactory $categoryDataFactory;

    /**
     * @inject
     */
    private TableRowTestHelper $tableRowTestHelper;

    public function testRefreshCategoriesVisibilityHidesSubcategoryOfDisabledCategory(): void
    {
        $parentCategory = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);
        $subcategory = $this->getReference(CategoryDataFixture::CATEGORY_TV, Category::class);

        $parentCategoryData = $this->categoryDataFactory->createFromCategory($parentCategory);
        $parentCategoryData->enabled[Domain::FIRST_DOMAIN_ID] = false;
        $this->categoryFacade->edit($parentCategory->getId(), $parentCategoryData);

        $this->categoryVisibilityRepository->refreshCategoriesVisibility();
        $this->em->clear();

        $this->assertFalse($this->categoryFacade->getById($subcategory->getId())->isVisible(Domain::FIRST_DOMAIN_ID));
    }

    public function testRefreshCategoriesVisibilityDoesNotRewriteUnchangedRows(): void
    {
        $this->categoryVisibilityRepository->refreshCategoriesVisibility();
        $rowVersionsBeforeRecalculation = $this->getCategoryDomainRowVersions();

        $this->categoryVisibilityRepository->refreshCategoriesVisibility();

        $this->assertSame($rowVersionsBeforeRecalculation, $this->getCategoryDomainRowVersions());
    }

    /**
     * @return array<string, string>
     */
    private function getCategoryDomainRowVersions(): array
    {
        return $this->tableRowTestHelper->getRowVersionsIndexedByPrimaryKey('category_domains', ['id']);
    }
}
