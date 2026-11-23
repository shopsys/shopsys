<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Blog\Article;

use App\DataFixtures\Demo\BlogArticleDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleDataFactory;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleVisibilityRepository;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryDataFactory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryVisibilityRepository;
use Tests\App\Test\TableRowTestHelper;
use Tests\App\Test\TransactionFunctionalTestCase;

final class BlogArticleVisibilityRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private BlogArticleVisibilityRepository $blogArticleVisibilityRepository;

    /**
     * @inject
     */
    private BlogCategoryVisibilityRepository $blogCategoryVisibilityRepository;

    /**
     * @inject
     */
    private BlogArticleFacade $blogArticleFacade;

    /**
     * @inject
     */
    private BlogArticleDataFactory $blogArticleDataFactory;

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

    public function testRefreshArticlesVisibilityFollowsVisibilityOfArticleBlogCategory(): void
    {
        $blogArticle = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_ARTICLE, BlogArticle::class);
        $blogCategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_SUBCATEGORY, BlogCategory::class);
        $blogArticleData = $this->blogArticleDataFactory->createFromBlogArticle($blogArticle);
        $blogArticleData->blogCategoriesByDomainId[Domain::FIRST_DOMAIN_ID] = [$blogCategory];
        $this->blogArticleFacade->edit($blogArticle->getId(), $blogArticleData);

        $this->setBlogCategoryEnabledOnFirstDomain($blogCategory->getId(), false);
        $this->refreshBlogCategoriesAndArticlesVisibility();
        $this->assertFalse($this->blogArticleFacade->getById($blogArticle->getId())->isVisible(Domain::FIRST_DOMAIN_ID));

        $this->setBlogCategoryEnabledOnFirstDomain($blogCategory->getId(), true);
        $this->refreshBlogCategoriesAndArticlesVisibility();
        $this->assertTrue($this->blogArticleFacade->getById($blogArticle->getId())->isVisible(Domain::FIRST_DOMAIN_ID));
    }

    public function testRefreshArticlesVisibilityDoesNotRewriteUnchangedRows(): void
    {
        $this->blogArticleVisibilityRepository->refreshArticlesVisibility();
        $blogArticleRowVersionsBeforeRecalculation = $this->getBlogArticleDomainRowVersions();

        $this->blogArticleVisibilityRepository->refreshArticlesVisibility();

        $this->assertSame($blogArticleRowVersionsBeforeRecalculation, $this->getBlogArticleDomainRowVersions());
    }

    private function setBlogCategoryEnabledOnFirstDomain(int $blogCategoryId, bool $enabled): void
    {
        $blogCategoryData = $this->blogCategoryDataFactory->createFromBlogCategory($this->blogCategoryFacade->getById($blogCategoryId));
        $blogCategoryData->enabled[Domain::FIRST_DOMAIN_ID] = $enabled;
        $this->blogCategoryFacade->edit($blogCategoryId, $blogCategoryData);
    }

    private function refreshBlogCategoriesAndArticlesVisibility(): void
    {
        $this->blogCategoryVisibilityRepository->refreshCategoriesVisibility();
        $this->blogArticleVisibilityRepository->refreshArticlesVisibility();
        $this->em->clear();
    }

    /**
     * @return array<string, string>
     */
    private function getBlogArticleDomainRowVersions(): array
    {
        return $this->tableRowTestHelper->getRowVersionsIndexedByPrimaryKey(
            'blog_article_domains',
            ['blog_article_id', 'domain_id'],
        );
    }
}
