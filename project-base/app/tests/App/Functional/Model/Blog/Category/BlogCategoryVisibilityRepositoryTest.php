<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Blog\Category;

use App\DataFixtures\Demo\BlogArticleDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryDataFactory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryVisibilityRepository;
use Tests\App\Test\TableRowTestHelper;
use Tests\App\Test\TransactionFunctionalTestCase;

final class BlogCategoryVisibilityRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private BlogCategoryVisibilityRepository $blogCategoryVisibilityRepository;

    /**
     * @inject
     */
    private BlogCategoryFacade $blogCategoryFacade;

    /**
     * @inject
     */
    private BlogCategoryDataFactory $blogCategoryDataFactory;

    /**
     * @inject
     */
    private TableRowTestHelper $tableRowTestHelper;

    public function testRefreshBlogCategoriesVisibilityHidesSubcategoriesOfDisabledBlogCategory(): void
    {
        $parentBlogCategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_SUBCATEGORY, BlogCategory::class);
        $nestedBlogCategory = $this->getReference(BlogArticleDataFixture::BLOG_CATEGORY_SCREEN_TECHNOLOGIES, BlogCategory::class);

        $parentBlogCategoryData = $this->blogCategoryDataFactory->createFromBlogCategory($parentBlogCategory);
        $parentBlogCategoryData->enabled[Domain::FIRST_DOMAIN_ID] = false;
        $this->blogCategoryFacade->edit($parentBlogCategory->getId(), $parentBlogCategoryData);

        $this->blogCategoryVisibilityRepository->refreshCategoriesVisibility();
        $this->em->clear();

        $this->assertFalse($this->blogCategoryFacade->getById($nestedBlogCategory->getId())->isVisible(Domain::FIRST_DOMAIN_ID));
    }

    public function testRefreshBlogCategoriesVisibilityDoesNotRewriteUnchangedRows(): void
    {
        $this->blogCategoryVisibilityRepository->refreshCategoriesVisibility();
        $blogCategoryRowVersionsBeforeRecalculation = $this->getBlogCategoryDomainRowVersions();

        $this->blogCategoryVisibilityRepository->refreshCategoriesVisibility();

        $this->assertSame($blogCategoryRowVersionsBeforeRecalculation, $this->getBlogCategoryDomainRowVersions());
    }

    /**
     * @return array<string, string>
     */
    private function getBlogCategoryDomainRowVersions(): array
    {
        return $this->tableRowTestHelper->getRowVersionsIndexedByPrimaryKey(
            'blog_category_domains',
            ['blog_category_id', 'domain_id'],
        );
    }
}
